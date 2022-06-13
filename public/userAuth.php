<?php
require __DIR__ . '/../vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;

session_start();
$config = include('../config.php');

$logged_in = false;
if (isset($_SESSION['oidc'])) {

    // Valid access token?
    $logged_in = $_SESSION['oidc']->getAccessToken() != null
              && ($_SESSION['user_info'] = $_SESSION['oidc']->requestUserInfo()) != null
              && property_exists( $_SESSION['user_info'],'sub')
              && $_SESSION['user_info']->sub != null;
 
    error_log($_SESSION['oidc']->getAccessToken());
    // No? -> Try Refresh!
    if (!$logged_in && $_SESSION['oidc']->getRefreshToken() != null) {
        error_log("OIDC: Refreshing token" );
        $rsp = $_SESSION['oidc']->refreshToken($_SESSION['oidc']->getRefreshToken());

        $_SESSION['oidc']->setAccessToken($rsp->access_token);
        // Now valid?
        $logged_in = $_SESSION['oidc']->getAccessToken() != null
                  && ($_SESSION['user_info'] = $_SESSION['oidc']->requestUserInfo()) != null
                  && property_exists( $_SESSION['user_info'],'sub')
                  && $_SESSION['user_info']->sub != null;
    }
}

if ($logged_in) {
    require '../include/userinfo.php';
} else {
    require '../include/loginbtn.php';
}
