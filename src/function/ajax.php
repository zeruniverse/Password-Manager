<?php
header('X-Content-Type-Options: nosniff');
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
function error($msg) {
    echo json_encode(array("status" => "error", "message" => $msg));
    die();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    error('Wrong method');
}
?>
