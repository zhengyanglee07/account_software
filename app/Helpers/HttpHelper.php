<?php

if(!function_exists('conflictAbort')) {
    function conflictAbort(string $msg) {
        abort(409, $msg);
    }
}
