<?php
require_once 'function/basic.php';
echoheader();
?>
<link rel="stylesheet" type="text/css" href="css/dataTables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/responsive.dataTables.min.css">
<script type="text/javascript" src="js/setlocalstorage.js"></script>
<script type="text/javascript" src="js/lib/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/lib/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/lib/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="js/lib/FileSaver.min.js"></script>
<script type="text/javascript" src="js/crypto/aes.js"></script>
<script type="text/javascript" src="js/crypto/sha512.js"></script>
<script type="text/javascript" src="js/crypto/pbkdf2.js"></script>
<script type="text/javascript" src="js/crypto/password.js"></script>
<script type="text/javascript" src="js/lib/jquery.csv.js"></script>
<script type="text/javascript" src="js/common/account.js"></script>
<script type="text/javascript" src="js/main.js"></script>
<script type="text/javascript" src="js/plugin.js"></script>
<script type="text/javascript" src="js/plugins/linkButton.js"></script>
<script type="text/javascript" src="js/plugins/tags.js"></script>
<script type="text/javascript" src="js/plugins/showPasswordAge.js"></script>
<script type="text/javascript" src="js/plugins/keyboardShortcuts.js"></script>
   <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="collapse">
        <form id="historyform" action="history.php" method="post">
        <input type="hidden" id="historyformsesstoken" name="session_token" />
        </form>
        </div>
        <div class="navbar-header pull-left">
          <a class="navbar-brand" href="#">Password-Manager</a>
        </div>
        <div class="navbar-header pull-right">
          <div class="pull-left">
              <a href="#" id="navBtnLogout" class="btn btn-info navbar-btn"><i class="glyphicon glyphicon-log-out"></i> <strong class="hidden-xs">Log Out</strong></a>
              <a href="#" id="navBtnUntrust" class="btn btn-danger navbar-btn" title="Delete all cookies"><i class="glyphicon glyphicon-fire"></i> <strong class="hidden-xs">Untrust</strong></a>
          </div>
          <!-- Required bootstrap placeholder for the collapsed menu -->
          <button type="button" data-toggle="collapse" data-target=".navbar-collapse" class="navbar-toggle"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
        </div>
        <div id="navbar" class="collapse navbar-collapse navbar-left">
          <ul class="nav navbar-nav" id="nav_links">
            <li id="nav-add"><a href="" data-toggle="modal" data-target="#add">Add Entry</a></li>
            <li id="nav-pin"><a href="" data-toggle="modal" data-target="#pin">Set PIN</a></li>
            <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Settings<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="" data-toggle="modal" data-target="#backuppw">Back Up</a></li>
              <li><a href="" data-toggle="modal" data-target="#import">Import</a></li>
              <li><a id="navBtnExport">Export CSV</a></li>
              <li><a href="" data-toggle="modal" data-target="#changepwd">Change Password</a></li>
              <li id="changefieldsnav"><a href="" data-toggle="modal" data-target="#changefields">Customize Fields</a></li>
              <li><a id="navBtnActivity">Account Activity</a></li>
            </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
<div class="container theme-showcase">
    <div class="row">
        <div class="col-md-8">
          <div class="page-header">
            <h1>Password Manager</h1>
          </div>
        </div>
        <div class="col-md-4">
            <div class="pull-right-sm" id="rightHandBox">
            </div>
        </div>
    </div>
    <div id="messageContainer"></div>
    <div id="waitsign">PLEASE WAIT WHILE WE ARE DECRYPTING YOUR PASSWORD...</div>
    <div id="pwdtable" class="collapse">
    <br />
    <table class="table table-striped table-bordered" id="pwdlist">
    <thead>
    <tr><th>Account</th><th>Password</th></tr>
    </thead>
    <tbody></tbody>
    </table> 
    <hr />
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="add">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Add a new account</h4>
            </div>
            <div class="modal-body">
            <form method="post">
                <div class="form-group">
                    <label for="newiteminput" class="control-label">Account (Item):</label>
                    <input class="form-control" id="newiteminput" type="text" />
                </div>
                <div class="form-group">
                    <label for="newiteminputpw" class="control-label">Password:</label>
                    <input class="form-control" id="newiteminputpw" type="text" autocomplete="off" placeholder="Leave blank to generate one"/>
                </div>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-primary" id="newbtn">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="backuppw">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Backup Passwords</h4>
            </div>
            <div class="modal-body">
            <form method="post">
                <p id="fileincludeckbp" class="collapse"><input type="checkbox" id="fileincludeckb" />Include Files<br /></p>
                <p>You will need your CURRENT login password to unlock the backup file even if you change login password later. Write your CURRENT login password down or remember to generate a new backup file after each time you change the login password.</p>
                <p class="textred">Generating backup file is time consuming...</p>
                <div class="progress"><div class="progress-bar" role="progressbar"  id="backuppwdpb" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-primary" id="backuppwdbtn">Start Backup</button>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="changefields">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Customize additional fields</h4>
            </div>
            <div class="modal-body">
            <form method="post">
            <p>Please edit the fields string according to the default one shown below. It should be JSON format. Usually, you don't need to edit this.</p>
            <p>A field entry should be like: <span class="textbold">"<span class="textred">label</span>":{"colname":"<span class="textred">screen name</span>","hint":"<span class="textred">input hint</span>","cls":" <span class="textred">style</span>"}</span>, red parts are for you to customize. Entries should be seperated with comma, and the final fields string should look like <span class="textbold">{entry1, entry2, entry3}</span>. <span class="textbold">{}</span> fields string means you don't want any additional fields</p>
            <p><span class="textred">label</span> is a system label for this entry, which should be UNIQUE and won't show up in user screen. <span class="textred">screen name</span> is the name for this entry that the user see on their screen, need not to be unique. <span class="textred">input hint</span> is the hint string that will show up when you try to edit values for this field. <span class="textred">style</span> is the display style for this field. If <span class="textred">style</span> string is empty, this field will always show up. If <span class="textred">style = hidden</span>, this field will not show up in main table. You can only access it by clicking [view details]. If <span class="textred">style = hidden-xs</span>, this field will not show up in main table on small screens such as smart phone screens, but will show up on computer screens. NO OTHER VALUES ALLOWED for <span class="textred">style</span>. There should always be a space before <span class="textred">style</span> string, don't delete it.</p>
            <textarea class="form-control" id="fieldsz"></textarea>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-primary" id="changefieldsbtn">Change</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="showdetails">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Detail Information</h4>
            </div>
            <div class="modal-body">
            <form method="post">
            <div class="form-control" id="details"></div>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="edit" data-id="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Edit account information</h4>
            </div>
            <div class="modal-body">
            <form method="post">
                <div class="form-group">
                    <label for="edititeminput" class="control-label">Account (Item):</label>
                    <input class="form-control" id="edititeminput" type="text" />
                </div>
                <div class="form-group">
                    <label for="edititeminputpw" class="control-label">Password:</label>
                    <div class="input-group">
                        <input class="form-control" id="edititeminputpw" type="text" autocomplete="off" placeholder="Leave blank to generate one"/>
                        <span class="input-group-btn">
                            <button id="editPasswordInput" class="btn btn-warning" type="button" title="generate new password"><i class="glyphicon glyphicon-refresh"></i></button>
                            <button class="btn btn-default" type="button" id="editAccountShowPassword" title="show current password"><i class="glyphicon glyphicon-eye-open"></i></button>
                        </span>
                    </div>
                </div>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="delbtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-primary" id="editbtn">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="pin">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Set PIN to login</h4>
            </div>
            <div class="modal-body">
                <form id="pinloginform" method="post">
                    <div class="form-group">
                        <label for="pinxx" class="control-label">PIN:</label>
                        <input id="pinxx" autocomplete="off" class="form-control" type="password" />
                        <label class="small textred blocklabel">Only set PIN in your trusted devices!</label>
                        <label class="small blocklabel">PIN can be set on your trusted devices to give you convenience while login. If you set PIN, you can use PIN instead of username and password to login next time. PIN is safe, you only have 3 chances to input a PIN before it's disabled automatically.</label>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                <button id="pinBtnDel" type="button" class="btn btn-danger" id="delpin">Delete PIN</button>
                <input type="submit" class="btn btn-primary" id="pinlogin" value="Set/Reset" /></form>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="import">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Import accounts</h4>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="form-group">
                        <label for="importc" class="control-label">You can import passwords from CSV file or raw backup file. Select a .csv file or .raw file to start.</label>
                        <input type="file" id="importc" accept=".csv,.raw" />
                        <label class="small blocklabel">CSV file must contain a header line with columns including "name" and "password" - order is not important. You may edit your CSV with your password in Office so that the account field has a header called 'name' and the password field has a header called 'password'. Other columns will only be imported if they have the same header name as one of your additional fields. Note your CSV file must be in UTF-8 encoding. If not, open your CSV in some plaintext editor and change the encoding to UTF-8 before importing.</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="importbtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="uploadfiledlg">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Upload attached file</h4>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="form-group">
                        <label for="uploadf" class="control-label">You can upload one attachment for <span id="uploadfitemlab1" class="textbold"></span>.</label>
                        <input type="file" id="uploadf" />
                        <label class="small textred blocklabel">Warning: If you already have an attachment for <span id="uploadfitemlab2" class="textbold"></span>, the old attachment will be overwritten. Maximal file size allowed is 3MB.</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="uploadfilebtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="changepwd">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>Change Password(Danger Area)</h4>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="form-group">
                        <label for="oldpassword" class="control-label">Old Password:</label>
                        <input id="oldpassword" autocomplete="off" class="form-control" type="password" />
                    </div>
                    <div class="form-group">
                        <label for="pwd" class="control-label">New Password:</label>
                        <input id="pwd" autocomplete="off" class="form-control" type="password" />
                    </div>
                    <div class="form-group">
                        <label for="pwd1" class="control-label">New Password Again:</label>
                        <input id="pwd1" autocomplete="off" class="form-control" type="password" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Abort</button>
                <button type="button" class="btn btn-primary" id="changepw">Save changes</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="messageDialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="alert" id="messageDialogText"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="messagewait">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                Please wait while we download and decrypt your file! Your download should start automatically.
            </div>
        </div>
    </div>
</div>
<?php echofooter(); ?>
