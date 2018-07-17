<?php namespace WebFTS;

use Exception;

class FTS3Exception extends Exception {
    public function getResponseCode() {
        $code = getCode();

        return $code != 0 ? $code : 500;
    }
}
?>
