<?php

function echofooter()
{
    echo '<footer class="footer">
<p>&copy;2015 Jeffery<br /><br />ALL RIGHTS RESERVED</p>
</footer>
</body>
</html>';
}
function echoheader()
{
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: default-src 'self';");
    header('Pragma: public');
    $expires = 86400;
    header('Cache-Control: max-age='.$expires.', must-revalidate');
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires).' GMT');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Password Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Password Manager">
    <meta name="author" content="Jeffery">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="js/lib/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <!-- Fav and touch icons -->
    <link rel="shortcut icon" href="favicon.ico">
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/common/commonfunctions.js"></script>
</head>
<body>';
}
