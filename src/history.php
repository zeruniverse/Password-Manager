<?php
require_once 'function/basic.php';
echoheader();
?>
<link rel="stylesheet" type="text/css" href="css/datatables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/responsive.datatables.min.css">
<script src="js/common/backend.js"></script>
   <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header pull-left">
          <a class="navbar-brand" href="password.php">&lt; Go Back</a>
        </div>
      </div>
    </nav>
<div class="container theme-showcase">
    <p id="placeholder">PLEASE WAIT...</p>
    <div id="maindiv" class="collapse">
    <div class="page-header">
        <h1>Trusted Devices</h1>
    </div>
    <div id="messageContainer"></div>
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
<script src="js/lib/jquery.datatables.min.js"></script>
<script src="js/lib/datatables.bootstrap.min.js"></script>
<script src="js/lib/datatables.responsive.min.js"></script>
<script src="js/history.js"></script>
<?php echofooter(); ?>
