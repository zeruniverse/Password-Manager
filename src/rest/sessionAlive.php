<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link, false)) {
    error('sessionUnauthenticated');
}
ajaxSuccess();
