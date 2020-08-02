<?php

require_once 'function/sqllink.php';
start_session();
invalidateSession();
$reason = '';
if (isset($_GET['reason'])) {
    $reason .= '?reason='.urlencode($_GET['reason']);
}
header('Location: ./'.$reason);
