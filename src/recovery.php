<?php
require_once('function/basic.php');
echoheader();
?>
<script type="text/javascript" src="js/crypto/aes.js"></script>
<script type="text/javascript" src="js/crypto/sha512.js"></script>
<script type="text/javascript" src="js/crypto/pbkdf2.js"></script>
<script type="text/javascript" src="js/crypto/password.js"></script>
<script type="text/javascript" src="js/lib/FileSaver.min.js"></script>
<script type="text/javascript" src="js/recovery.js"></script>
<div class="container theme-showcase">
    <div class="page-header">
        <h1>Before You Start...</h1>
    </div>
    1. Open your backup.txt in a plain text editor.<br />
    2. Find the backup version number. There should be "VERSION":"xxx" in the backup.txt and xxx is your version number.<br />
    3. If the version number of the backup file is the same with the version number of this password manager (shown in the login page), go to step 6.<br />
    4. Go <a href="https://github.com/zeruniverse/Password-Manager/releases">here</a> and find the password manager release with the same version number as your backup file.<br />
    5. Download the password manager with the right version, use its src/recovery.php instead of this one.<br />
    6. Scroll down and do the recovery.<br />
    <div class="page-header">
        <h1>Recovery</h1>
    </div>
    <p>The recovery process will be on your browser. It's safe!</p>
	<form>
      <p class="textred">Select backup.txt containing your backup data.</p>
    <input type="file" id="backupc" accept=".txt" />
    <p> </p>
    <p>Password: <input type="password" autocomplete="off" name="pwd" id="pwd" /></p><br />
    <p class="textred">Input the login password when you generate the backup file.</p>
    <p> </p>
    <p class="textred">Recovering takes long time. (No less than backup time) If your web browser asks you whether to kill the page due to no response, choose [wait]!</p>
    </form>
    <input type="button" class="btn btn-md btn-success" id="chk" value="RECOVER IT!" />
    <a href="./" class="btn btn-md btn-info">Go Back</a>
    <a href="javascript: export_raw();" class="btn btn-md btn-danger collapse" id="raw_button">Export Raw Data</a>
    <p> </p>
    <p><br /> </p>
    <div id="recover_result" class="collapse">
    <p>The following table shows your accounts and passwords if you enter the correct login password. If the data loss is caused by attack, please update your passwords anyway!</p>
    <table class="table" id="rtable"></table>
    </div>
</div>
<?php echofooter();?>
