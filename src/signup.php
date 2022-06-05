<?php
require_once 'function/basic.php';
echoheader();
?>
<script src="js/common/crypto.js"></script>
<script src="js/common/cryptowrapper.js"></script>
<script src="js/common/backend.js"></script>
<script src="js/signup.js"></script>
<div class="container theme-showcase">
    <div class="page-header">
        <h1>Password Manager</h1>
    </div>
    <h3>New User</h3>
    <p>Only numbers and letters are allowed for username</p>
    <div id="message" class="alert alert-info">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
        <span id="messageText">Signup is not allowed.</span>
    </div>
    <form id="signupform" class="collapse" method="post">
        <div class="form-group">
            <label for="user" class="control-label">User Name: </label>
            <input type="text" class="form-control" name="user" id="user" />
        </div>
        <div class="form-group">
            <label for="pwd" class="control-label">Password: </label>
            <input type="password" autocomplete="off" class="form-control" name="pwd" id="pwd" />
        </div>
        <div class="form-group">
            <label for="pwd1" class="control-label">Input Password Again: </label>
            <input type="password" autocomplete="off" class="form-control" name="pwd1" id="pwd1" />
        </div>
        <div class="form-group">
            <label for="email" class="control-label">Email:</label>
            <input type="text" class="form-control" name="email" id="email" />
        </div>
        <input type="button" class="btn btn-md btn-success" id="chk" value="Submit" />
    </form>
</div>
<?php echofooter(); ?>
