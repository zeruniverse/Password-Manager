<?php

require_once dirname(__FILE__).'/../function/common.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
// Poll. Set refresh to true.
if (!checksession($link, true)) {
    error('sessionUnauthenticated');
}
ajaxSuccess();
