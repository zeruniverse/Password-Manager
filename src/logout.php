<?php

require_once 'function/sqllink.php';
session_start();
logout();
$reason = '';
if (isset($_GET['reason'])) {
    $reason .= '?reason='.urlencode($_GET['reason']);
}
header('Location: ./'.$reason);
