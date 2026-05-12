<?php

require_once dirname(__FILE__).'/../function/common.php';
require_once dirname(__FILE__).'/../function/ajax.php';
start_session();
invalidateSession();
//set the cookie so that other open pages get logged out too
ajaxSuccess();
