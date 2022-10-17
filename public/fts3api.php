<?php
require __DIR__ . '/../vendor/autoload.php';

use WebFTS\FTS3Client;
use WebFTS\FTS3Exception;

session_start();

$fts = new FTS3Client();

function opt(&$var, &$default = null) {
    return isset($var) ? $var : $default;
}

$REQUEST_ENDPOINT = $_GET['request'];
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $INPUT = $_GET;
        break;

    case 'POST':
    case 'DELETE':
        if (array_key_exists('CONTENT_TYPE', $_SERVER) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $INPUT = json_decode(file_get_contents('php://input'), true);
        } else {
            $INPUT = $_POST;
        }
        break;
}

$result;
$error;
try {
    switch ($REQUEST_ENDPOINT) {

        case 'userinfo':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $result = $fts->whoami();
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        case 'dir':
            $base = opt($INPUT['base'], $INPUT['surl']);
            $dir_name = opt($INPUT['dir_name']);
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $result = $fts->dm_list($base, $dir_name);
                    break;
                case 'POST':
                    $result = $fts->dm_mkdir($base, $dir_name);
                    break;
                case 'DELETE':
                    $result = $fts->dm_rmdir($base, $dir_name);
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        case 'cs':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $result = $fts->swift_list($INPUT['surl'], $INPUT['osprojectid'], $_SERVER['HTTP_X_AUTH_TOKEN']);
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        case 'jobs':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $result = $fts->jobs(opt($INPUT['dlg_id']),
                                         opt($INPUT['fields']),
                                         opt($INPUT['time_window']),
                                         opt($INPUT['limit']),
                                         opt($INPUT['dest_se']),
                                         opt($INPUT['source_se']),
                                         opt($INPUT['state_in']),
                                         opt($INPUT['vo_name']),
                                         opt($INPUT['user_dn']));
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        case 'job':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $result = $fts->job_info($INPUT['job_id'], $INPUT['field']);
                    break;
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $fts->submit_job($data);
                    break;
                case 'DELETE':
                    $result = $fts->delete_job($_SERVER['HTTP_JOBID']);
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        case 'rename':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $result = $fts->dm_rename($INPUT['base'], $INPUT['old'], $INPUT['new']);
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        case 'file':
            $base = opt($INPUT['base'], $INPUT['surl']);
            $dir_name = opt($INPUT['dir_name']);

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'DELETE':
                    $result = $fts->dm_unlink($base, $dir_name);
                    break;
                default:
                    $error = 405;
                    break;
            }
            break;

        default:
            $error = 404;
            break;
    }
}
catch (FTS3Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $error = $e->getResponseCode();
    $result = $e->getMessage();
}


if (isset($error)) {
    http_response_code($error);
}
if (isset($result)) {
    echo json_encode($result);
}
?>
