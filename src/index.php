<?php
require_once('function/basic.php');
require_once("function/sqllink.php");
session_start();
if(isset($_SESSION["loginok"])&& $_SESSION['loginok']==1) {header("Location: ./password.php"); die();}
if($DB_NAME=='') die('PLEASE CONFIG function/config.php before using this system!');
echoheader();
?>
<script type="text/javascript" src="js/crypto/aes.js"></script>
<script type="text/javascript" src="js/crypto/sha512.js"></script>
<script type="text/javascript" src="js/crypto/pbkdf2.js"></script>
<script type="text/javascript" src="js/crypto/password.js"></script>
<script type="text/javascript" src="js/setlocalstorage.js"></script>
<script type="text/javascript" src="js/index.js"></script>
    <div class="container theme-showcase">
        <div class="page-header">
            <h1>Password Manager</h1>
        </div>
<?php
if(isset($_GET["reason"])) {
    echo '<div id="message" class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span id="messageText">'.htmlspecialchars($_GET['reason'], ENT_QUOTES, 'UTF-8').'</span></div>';
}
?>
        <h3>Please Sign in</h3>
        <form id="loginform" method="post">
            <div class="form-group">
                <label for="user" class="control-label sr-only">User Name: </label>
                <input type="text" class="form-control" placeholder="User Name" name="user" id="user" />
                <label for="pwd" class="control-label sr-only">Password: </label>
                <input type="password" autocomplete="off" class="form-control" placeholder="Password" name="pwd" id="pwd" />
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-lg btn-success btn-block" id="chk"  value="Login" />
            </div>
        </form>
        <span id="nouser" class="errorhint collapse textred">Username password combination is unknown<br /></span>
        <span id="blockip" class="errorhint collapse textred">Your IP has been blocked due to malicious activity<br /></span>
        <span id="accountban" class="errorhint collapse textred">Your account has been protected due to continuous attack. Try again in <span id="banTime"></span> seconds<br /></span>
        <span id="othererror" class="errorhint collapse textred">Oops, our server run into some problems. Please refresh this page and try again.<br /></span>
        <hr />
        <button id="signup" class="btn btn-sm btn-default collapse" type="button">Sign Up</button>&nbsp; <button id="recover" class="btn btn-sm btn-warning" type="button">Password Recovery</button>
    <hr />
    <div>Version <span id="version"></span> (<a href="https://github.com/zeruniverse/Password-Manager/releases">DOWNLOAD</a>)</div>
    <div class="modal" tabindex="-1" role="dialog" id="usepin">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4>Use PIN to login</h4>
                </div>
                <div class="modal-body">
                    <form id="pinloginform" method="post">
                        <div class="form-group">
                            <label for="pin" class="control-label">PIN:</label>
                            <input id="pin" autocomplete="off" class="form-control" type="password" />
                            <label class="small blocklabel">You see this window because you or someone set an PIN in this device and choose it as default login method. To switch account or disable PIN, please press the red button below. To use username/password to login only this time, close this window by pressing the 'X' at top-right corner.</label>
                            <label class="small textred blocklabel">Closing this window only let you use username/password to login this time. PIN will still be chosen as default method in future. Press red button below if you want to disable current PIN.</label>
                        </div>
                </div>
                <div class="modal-footer">
                    <p class="collapse" id="pinerrorhint">PIN ERROR, try again.</p>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Delete PIN</button>
                    <input type="submit" class="btn btn-primary" id="pinlogin" value="Login" /></form>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php echofooter();?>
