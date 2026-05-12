<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();

if (!checksession($link)) {
    ajaxError('session');
}

ajaxSuccess();