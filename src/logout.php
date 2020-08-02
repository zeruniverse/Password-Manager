<?php

require_once 'function/sqllink.php';
if (session_status() === PHP_SESSION_ACTIVE) invalidateSession();
$reason = '';
if (isset($_GET['reason'])) {
    $reason .= '?reason='.urlencode($_GET['reason']);
}
header('Location: ./'.$reason);
