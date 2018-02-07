<?php
require_once 'function/basic.php';
echoheader();
?>
<link rel="stylesheet" type="text/css" href="css/dataTables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/responsive.dataTables.min.css">
<div class="container theme-showcase">
    <p id="placeholder">PLEASE WAIT...</p>
    <div id="maindiv" class="collapse">
    <div class="page-header">
        <h1>Trusted Devices</h1>
    </div>
    <table class="table" id="pinTable">
    <tr><th>Device Type</th><th>Set Time</th><th>Untrust (Disable PIN)</th></tr>
    </table>
    <div class="page-header">
        <h1>Login History</h1>
    </div>
    <p>Red entries indicate password error (i.e. error try)</p>
    <table class="table" id="loginhistorytable">
	<thead>
    <tr><th>Device Type</th><th>Login IP</th><th>Login Time</th></tr>
	</thead>
	<tbody>
	</tbody>
    </table>
    </div>
</div>
<script src="js/lib/ua-parser.min.js"></script>
<script src="js/lib/jquery.dataTables.min.js"></script>
<script src="js/lib/dataTables.bootstrap.min.js"></script>
<script src="js/lib/dataTables.responsive.min.js"></script>
<script src="js/history.js"></script>
<?php echofooter(); ?>
