<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
session_start();
logout();
//set the cookie so that other open pages get logged out too
ajaxSuccess();
