<?php
require_once("function/basic.php");
echoheader();
?>
<style type="text/css">
@font-face {
    font-family: 'passwordshow';
    src:url('pw.ttf');
}
.theme-showcase
{
    margin-top:10px !important;
}
</style>
<link rel="stylesheet" type="text/css" href="css/dataTables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/responsive.dataTables.min.css">
<script type="text/javascript" src="setlocalstorage.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="js/FileSaver.min.js"></script>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<script type="text/javascript" src="js/main.js"></script>
   <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div style="display:none">
        <form id="historyform" action="history.php" method="post">
        <input type="hidden" id="historyformsesstoken" name="session_token" />
        </form>
        </div>
        <div class="navbar-header pull-left">
          <a class="navbar-brand" href="#">Password-Manager</a>
        </div>
        <div class="navbar-header pull-right">
          <div class="pull-left">
              <a href="#" class="btn btn-info navbar-btn" onClick="quitpwd();"><i class="glyphicon glyphicon-log-out"></i> <strong class="hidden-xs">Log Out</strong></a>
              <a href="#" class="btn btn-danger navbar-btn" onClick="quitpwd_untrust();" title="Delete all cookies"><i class="glyphicon glyphicon-fire"></i> <strong class="hidden-xs">Untrust</strong></a>
          </div>
          <!-- Required bootstrap placeholder for the collapsed menu -->
          <button type="button" data-toggle="collapse" data-target=".navbar-collapse" class="navbar-toggle" style="margin-left:10px"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
        </div>
        <div id="navbar" class="collapse navbar-collapse navbar-left" style="min-width:100px;">
          <ul class="nav navbar-nav" id="nav_links">
            <li id="nav-add"><a href="" data-toggle="modal" data-target="#add">Add Entry</a></li>
            <li id="nav-pin"><a href="" data-toggle="modal" data-target="#pin">Set PIN</a></li>
            <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Settings<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="" data-toggle="modal" data-target="#backuppw">Back Up</a></li>
              <li><a href="" data-toggle="modal" data-target="#import">Import</a></li>
              <li><a href="javascript: exportcsv();">Export CSV</a></li>
              <li><a href="" data-toggle="modal" data-target="#changepwd">Change Password</a></li>
              <li id="changefieldsnav"><a href="" data-toggle="modal" data-target="#changefields">Customize Fields</a></li>
              <li><a href="javascript: $('#historyformsesstoken').val(localStorage.session_token); $('#historyform').submit();">Account Activity</a></li>
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
                <!--<form id="searchForm">
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" name="srch-term" id="srch-term">
                    <div class="input-group-btn">
                        <button class="btn btn-default collapse" id="resetSearch" onClick="filterAccounts('')" type="button" title="reset search"><i class="glyphicon glyphicon-remove"></i></button>
                        <button class="btn btn-default" type="submit" title="search"><i class="glyphicon glyphicon-search"></i></button>
                    </div>
                  </div>
                </form>-->
                <div id="tagCloud" style="display:none;">
                    <p class="lead" style="margin-bottom:0">Tag-Overview<a href="javascript:enableGrouping();" id="orderTags" name="enable grouping" class="small" style="padding-left:10px"><span class="glyphicon glyphicon-sort-by-attributes"></span></a><a href="javascript:disableGrouping();" id="orderTagsDisable" name="disable grouping" class="small" style="padding-left:10px; display:none;"><span class="glyphicon glyphicon-remove"></span></a></p>
                    <p class="visible-xs small" style ="margin-bottom:0;">
                        <a href="javascript:$('#tags').toggleClass('hidden-xs');$('.tagsShow').toggleClass('hidden');"><span class="tagsShow">show</span><span class="tagsShow hidden">hide</span> tags</a>
                    </p>
                    <span class="hidden-xs" id="tags"></span><p class="small" style="display:none;" id="resetFilter"><a href="javascript:filterTags('');">reset filter</a></p>
                </div>
            </div>
        </div>
    </div>
    <div id="message" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span id="messageText"></span></div>
    <div id="waitsign">PLEASE WAIT WHILE WE ARE DECRYPTING YOUR PASSWORD...</div>
    <div id="pwdtable" style="display:none">
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
            <form>
                <div class="form-group">
                    <label for="newiteminput" class="control-label">Account (Item):</label>
                    <input class="form-control" id="newiteminput" type="text" />
                </div>
                <div class="form-group">
                    <label for="newiteminputpw" class="control-label">Password:</label>
                    <input class="form-control" id="newiteminputpw" type="text" placeholder="Leave blank to generate one"/>
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
            <form>
                <p id="fileincludeckbp" style="display:none"><input type="checkbox" id="fileincludeckb" />Include Files<br /></p>
                <p>You will need your CURRENT login password to unlock the backup file even if you change login password later. Write your CURRENT login password down or remember to generate a new backup file after each time you change the login password.</p>
                <p style="color:red">Generating backup file is time consuming...</p>
                <div class="progress"><div class="progress-bar" role="progressbar"  id="backuppwdpb" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
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
            <form>
            <p>Please edit the fields string according to the default one shown below. It should be JSON format. Usually, you don't need to edit this.</p>
            <p>A field entry should be like: <span style="font-weight:bold">"<span style="color:red">label</span>":{"colname":"<span style="color:red">screen name</span>","hint":"<span style="color:red">input hint</span>","cls":" <span style="color:red">style</span>"}</span>, red parts are for you to customize. Entries should be seperated with comma, and the final fields string should look like <span style="font-weight:bold">{entry1, entry2, entry3}</span>. <span style="font-weight:bold">{}</span> fields string means you don't want any additional fields</p>
            <p><span style="color:red">label</span> is a system label for this entry, which should be UNIQUE and won't show up in user screen. <span style="color:red">screen name</span> is the name for this entry that the user see on their screen, need not to be unique. <span style="color:red">input hint</span> is the hint string that will show up when you try to edit values for this field. <span style="color:red">style</span> is the display style for this field. If <span style="color:red">style</span> string is empty, this field will always show up. If <span style="color:red">style = hidden</span>, this field will not show up in main table. You can only access it by clicking [view details]. If <span style="color:red">style = hidden-xs</span>, this field will not show up in main table on small screens such as smart phone screens, but will show up on computer screens. NO OTHER VALUES ALLOWED for <span style="color:red">style</span>. There should always be a space before <span style="color:red">style</span> string, don't delete it.</p>
            <textarea class="form-control" id="fieldsz" style="height:100px"></textarea>
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
            <form>
            <div class="form-control" id="details" style="height:230px; background:#efefef; overflow:auto" ></div>
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
            <form>
                <div class="form-group">
                    <label for="edititeminput" class="control-label">Account (Item):</label>
                    <input class="form-control" id="edititeminput" type="text" />
                </div>
                <div class="form-group">
                    <label for="edititeminputpw" class="control-label">Password:</label>
                    <div class="input-group">
                        <input class="form-control" id="edititeminputpw" type="text" placeholder="Leave blank to generate one"/>
                        <span class="input-group-btn">
                            <button class="btn btn-warning" onclick="$('#edititeminputpw').val(getpwd(default_letter_used, default_length)); $('#editAccountShowPassword').removeClass('collapse');$('#editAccountShowPassword').popover({ 'placement':'bottom', 'title':'', 'container':'body', 'template':'<div class=\'popover\' role=\'tooltip\' onclick=\'$(&quot;#editAccountShowPassword&quot;).popover(&quot;hide&quot;);\'><div class=\'arrow\'></div><h3 class=\'popover-title hidden\'></h3><div class=\'popover-content\'></div></div>', 'content':'Click here to get your old password back.', 'trigger':'manual' }).popover('show');" type="button" title="generate new password"><i class="glyphicon glyphicon-refresh"></i></button>
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
                <form id="pinloginform">
                    <div class="form-group">
                        <label for="pinxx" class="control-label">PIN:</label>
                        <input id="pinxx" autocomplete="off" class="form-control" type="password" />
                        <label class="small" style="display:block; clear:both; color:red">Only set PIN in your trusted devices!</label>
                        <label class="small" style="display:block; clear:both;">PIN can be set on your trusted devices to give you convenience while login. If you set PIN, you can use PIN instead of username and password to login next time. PIN is safe, you only have 3 chances to input a PIN before it's disabled automatically.</label>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                <button type="button" onClick="delpinstore();showMessage('info', 'PIN deleted, use username/password to login next time', true);$('#pin').modal('hide');" class="btn btn-danger" id="delpin">Delete PIN</button>
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
                <form>
                    <div class="form-group">
                        <label for="importc" class="control-label">You can import passwords from CSV file or raw backup file. Select a .csv file or .raw file to start.</label>
                        <input type="file" id="importc" accept=".csv,.raw" />
                        <label class="small" style="display:block; clear:both;">CSV file must contain a header line with columns including "name" and "password" - order is not important. You may edit your CSV with your password in Office so that the account field has a header called 'name' and the password field has a header called 'password'. Other columns will only be imported if they have the same header name as one of your additional fields. Note your CSV file must be in UTF-8 encoding. If not, open your CSV in some plaintext editor and change the encoding to UTF-8 before importing.</label>
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
                <form>
                    <div class="form-group">
                        <label for="uploadf" class="control-label">You can upload one attachment for <span id="uploadfitemlab1" style="font-weight: bold;"></span>.</label>
                        <input type="file" id="uploadf" />
                        <label class="small" style="display:block; clear:both; color:red">Warning: If you already have an attachment for <span id="uploadfitemlab2" style="font-weight: bold;"></span>, the old attachment will be overwritten. Maximal file size allowed is 3MB.</label>
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
                <form>
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
<?php echofooter();?>
