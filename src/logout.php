<?php

require_once 'function/sqllink.php';
if (isset($_SESSION)) invalidateSession();
$reason = '';
if (isset($_GET['reason'])) {
    $reason .= '?reason='.urlencode($_GET['reason']);
}
header('Location: ./'.$reason);
