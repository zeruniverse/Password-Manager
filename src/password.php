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
<script type="text/javascript">
//everything is going to be loaded later
var secretkey;
var default_timeout;
var timeout;
var default_letter_used;
var default_length;
var salt1;
var salt2;
var user;
var fields;
var accountarray=new Array();
function quitpwd()
{
    delpwdstore(); window.location.href="./logout.php";
}
function quitpwd_untrust()
{
    delpwdstore();
    delpinstore();
    deleteCookie('username');
    window.location.href="./logout.php";
}
function countdown()
{
    if(timeout < Math.floor(Date.now() / 1000)) quitpwd();
}
function checksessionalive()
{
    $.post("session_alive.php",{u:"1"},function(msg){if(msg=='0') quitpwd();});
}
</script>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
   <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
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
              <li><a href="" data-toggle="modal" data-target="#changefields">Customize Fields</a></li>
              <li><a href="./history.php" target="_blank">Account Activity</a></li>
            </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
<div class="container theme-showcase">
          <div class="page-header">
            <h1>Password Manager</h1>
          </div>
    <div id="message" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span id="messageText"></span></div>
    <div id="waitsign">PLEASE WAIT WHILE WE ARE DECRYPTING YOUR PASSWORD...</div>
    <div id="pwdtable" style="display:none">
    <br />
    <table class="table" id="pwdlist">
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
            <p>Please edit the fields parameter according to the default one shown below. It should be JSON format.</p>
                <textarea class="form-control" id="fieldsz" style="height:300px"></textarea>
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
                        <input id="pinxx" class="form-control" type="password" />
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
                        <input id="oldpassword" class="form-control" type="password" />
                    </div>
                    <div class="form-group">
                        <label for="pwd" class="control-label">New Password:</label>
                        <input id="pwd" class="form-control" type="password" />
                    </div>
                    <div class="form-group">
                        <label for="pwd1" class="control-label">New Password Again:</label>
                        <input id="pwd1" class="form-control" type="password" />
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
<script type="text/javascript">
var ALPHABET;
var PWsalt;
var datatablestatus=null;
function sanitize_json(s){
    var t=s;
    t=t.replace(/\n/g,'')
    return t.replace(/\r/g,'');
}
function decryptPassword(name, kss){
    var thekey=decryptchar(kss,secretkey);
    if (thekey==""){
        return "";
    }
    return get_orig_pwd(getconfkey(PWsalt),PWsalt,String(CryptoJS.SHA512(name)),ALPHABET,thekey);
}
function encryptPassword(name, pass){
    pass=gen_temp_pwd(getconfkey(PWsalt),PWsalt,String(CryptoJS.SHA512(name)),ALPHABET,pass);
    return encryptchar(pass,secretkey);
}
function add_account(acc, pass, other, callback){
    var sk=secretkey;
    pass=encryptPassword(acc, pass);
    other=encryptchar(other, sk);
    acc=encryptchar(acc,sk);
    $.post("insert.php",{name:acc,newpwd:pass,other:other},callback);
}
function import_raw(json){
    json=JSON.parse(sanitize_json(json));
    if(json.status!="RAW_OK") {
        showMessage("warning", "INVALID RAW FILE", true);
        return;
    }
    function add_acc(acc,pass,other){
        if(acc==''||pass=='') {
            showMessage('warning', "one of account or password empty! will continue to process other accounts, check back after this finished", true); return;
        }
        add_account(acc, pass, other, function(msg) { if(msg!=1) showMessage('warning',"Fail to add "+acc+", please try again manually later.", true); });
    }
    function onsucc(){
    	showMessage('success','IMPORT FINISHED!');
        $('#import').modal('hide');
        reloadAccounts();
    }
    function process(){
        var aeskey=json.KEY;
        var x;
        timeout=1000000+Math.floor(Date.now() / 1000);
        for(x in json.data){
            other = JSON.stringify({});
            if (json.data[x].length > 2)
                other = decryptchar(json.data[x][2], aeskey);
            add_acc(decryptchar(json.data[x][0],aeskey),decryptchar(json.data[x][1],aeskey), other);
        }
    }
    process();
    setTimeout(onsucc,1000);
    
}
function import_csv(csv){
    $.getScript( 'js/jquery.csv.js', function() {
        var accarray = $.csv.toObjects(csv);
        timeout=1000000+Math.floor(Date.now() / 1000);
        for (x in accarray) {
            var acc = accarray[x]["name"];
            var pass = accarray[x]["password"];
            if(acc==''||pass=='') {
                showMessage('danger',"one of account or password empty! will continue to process other accounts, check back after this finished", true); continue;
            }
            var other = {};
            for (key in accarray[x]){
                if (key in fields){
                    other[key]=accarray[x][key];
                }
            }
            add_account(acc, pass, JSON.stringify(other), function(msg) { if(msg!=1) showMessage('warning', "Fail to add "+acc+", please try again manually later.", true); });
        }
        function onsucc(){
            showMessage('success', 'IMPORT FINISHED!');
            $('#import').modal('hide');
            reloadAccounts();
        }
        setTimeout(onsucc,1000);
    });
}
//type: any of "success", "info", "warning", "danger"
//message: text
//modal: if true shows a modal window
function showMessage(type, message, modal){
	modal = (typeof modal !== 'undefined') ? modal : false;
    if (modal==false) {
        $("#messageText").html(message);
        $("#message").removeClass("alert-success alert-info alert-warning alert-danger");
        $("#message").addClass("alert-"+type);
        $("#message").fadeIn();
    }
    else {
        $("#messageDialogText").html(message);
        $("#messageDialogText").removeClass("alert-success alert-info alert-warning alert-danger");
        $("#messageDialogText").addClass("alert-"+type);
        $("#messageDialog").modal('show');
    }
}
function dataReady(data){
    data = $.parseJSON(data);
    if (data["status"]=="error") {
        window.location.href = './?reason='+encodeURIComponent(data["message"]);
        return;
    }
    default_timeout = data["default_timeout"];
    timeout = default_timeout+Math.floor(Date.now() / 1000);
    default_letter_used = data["default_letter_used"];
    default_length = data["default_length"];
    salt1 = data["global_salt_1"];
    salt2 = data["global_salt_2"];
    user = data["user"];
    fields = $.parseJSON(data["fields"]);
    var accounts = data["accounts"];
    setInterval(countdown, 1000);
    setInterval(checksessionalive,30000); 
    ALPHABET = default_letter_used;
    PWsalt = salt2;

    var secretkey0=getpwdstore(salt2);
    if (secretkey0==""){
        quitpwd();
        return;
    }
    secretkey=String(CryptoJS.SHA512(secretkey0+salt2));

    for(var i = 0; i<accounts.length; i++) {
        index = accounts[i]["index"];
        accountarray[index] = { other: {} };
        accountarray[index]["name"] = decryptchar(accounts[i]["name"],secretkey);
        accountarray[index]["enpassword"] = accounts[i]["kss"];
        if (accounts[i]["additional"] != "")
        {//decrypt
            var tempchar = decryptchar(accounts[i]["additional"], secretkey);
            //extract json
            var data = $.parseJSON(tempchar);
            accountarray[index]["other"] = data;

        }
    }
    initFields();
    showtable(accountarray);
	datatablestatus=$("#pwdlist").DataTable({ordering:false, info:true});
}
function initFields() {
    $("textarea#fieldsz").val(JSON.stringify(fields));
    for (x in fields) {
        fields[x]["count"] = 0;
        var header = '<th class="'+x+'cell'+fields[x]["cls"]+' field">'+fields[x]["colname"]+'</th>';
        var input = "";
        var inputtype = "text";
        if ("type" in fields[x])
            inputtype = fields[x]["type"];
        if (inputtype == "textarea")
            input = '<textarea class="form-control" id="%NAME%iteminput'+x+'" placeholder="'+fields[x]["hint"]+'"></textarea>';
        else
            input = '<input class="form-control" id="%NAME%iteminput'+x+'" type="'+inputtype+'" placeholder="'+fields[x]["hint"]+'"/>';
        var form = '<div class="form-group field"><label for="%NAME%iteminput'+x+'" class="control-label">'+fields[x]["colname"]+':</label>'+input+'</div>';
        if (("position" in fields[x]) && (fields[x]["position"] != 0)) {
            $('#pwdlist > thead > tr:first > th:nth-child('+fields[x]["position"]+')').after(header)
            $("#add").find('form > .form-group:nth-child('+fields[x]["position"]+')').after(form.replace(/%NAME%/g,"new"));
            $("#edit").find('form > .form-group:nth-child('+fields[x]["position"]+')').after(form.replace(/%NAME%/g,"edit"));
        }
        else {
            $("#pwdlist > thead > tr:first").append(header);
            $("#add").find("form").append(form.replace(/%NAME%/g,"new"));
            $("#edit").find("form").append(form.replace(/%NAME%/g,"edit"));
        }
    }
}

// accounts as parameter to have the possibility to only show a subset i.e. for pagination
function showtable(accounts)
{
    var tempchar;
    for(index in accounts) {
        var cols = [
            "<td class='namecell'><span class='accountname' data-id='"+index+"'>"+accounts[index]["name"]+'</span><a title="Edit" class="cellOptionButton" href="javascript: edit('+index+')"><span class="glyphicon glyphicon-wrench"></span></a><a title="Details" class="cellOptionButton" style="margin-right:15px;" href="javascript: showdetail('+index+')"><span class="glyphicon glyphicon-eye-open"></span></a></td>',
            '<td><span passid="'+index+'" enpassword="'+accounts[index]["enpassword"]+'" id="'+index+'"><a title="Click to see" href="javascript: clicktoshow(\''+index+'\')"><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span></a></span></td>']
            //fill in other
            for (x in fields) {
                var value="";
                if (x in accounts[index]["other"]) {
                    value = accounts[index]["other"][x];
                    if (value!="")
                        fields[x]["count"] += 1;
                }
                var cell = '<td class="'+x+'cell'+fields[x]["cls"]+'"><span class="account'+x+'">'+value+'</span></td>';
                if (("position" in fields[x]) && (fields[x]["position"] != 0)) {
                    cols.splice(fields[x]["position"], 0, cell);
                }
                else
                    cols.push(cell);
            }
        row = "<tr class='datarow' data-id="+index+">" + cols.join("") + "</tr>";
        $('#pwdlist > tbody:last-child').append(row);
    }
    $("#waitsign").hide();
    $("#pwdtable").show();
    for (x in fields) {
        if (fields[x]["count"] == 0) {
            $("."+x+"cell").remove();
        }
    }
}
function emptyTable() {
    $("#pwdlist tr").not(':first').remove();
}
function cleanUp() {
	if(datatablestatus!=null) datatablestatus.destroy();
    accountarray = new Array();
    emptyTable();
    $(".field").remove();
}
function reloadAccounts() {
    cleanUp();
    $.ajax({url : "password_ajax.php"}).done(dataReady);
}

$(document).ready(function(){
    $.ajax({url : "password_ajax.php"}).done(dataReady);
$( window ).resize(function() {
  if(datatablestatus!=null){
	  datatablestatus.destroy();
	  datatablestatus=$("#pwdlist").DataTable({ordering:false, info:true});
  }
});
$("#pinloginform").on('submit',function(e){
    e.preventDefault();
    var pin=$("#pinxx").val();
    var device=getcookie('device');
    var salt=getpwd('abcdefghijklmnopqrstuvwxyz1234567890',500);
    timeout=default_timeout+Math.floor(Date.now() / 1000);
    function process()
    {
        $.post("setpin.php",{user:getcookie('username'),device:device,sig:String(CryptoJS.SHA512(pin+salt))},function(msg){
            if(msg=='0'){
                showMessage('warning', 'ERROR set PIN, try again later!', true);
                $('#pin').modal('hide');
            }else{
                setPINstore(device,salt,encryptchar(getpwdstore(PWsalt),pin+msg),encryptchar(getconfkey(PWsalt),pin+msg));
                showMessage('success', 'PIN set, use PIN to login next time');
                $('#pin').modal('hide');
            }
        });
    }
    if(pin.length<4) {showMessage('warning', 'For security reason, PIN should be at least of length 4.', true); return;}
    if(device=="")
    {
        function rand_device()
        {
            var status=1;
            device=getpwd('abcdefghijklmnopqrstuvwxyz1234567890',9)
            setCookie('device',device);
            $.post("getpinpk.php",{user:getcookie('username'),device:device,sig:'1'},function(msg){
                status=parseInt(msg);
                if(status == 0) process();
                else rand_device();
            });
        }
        rand_device();
    } else process();  
});
$("#changefieldsbtn").click(function(){
    var a=$('#fieldsz').val();
    var p=a.replace(/\r\n/g,'');
    p=p.replace(/\n/g,'');
    function isJson(str) {
    try {
        $.parseJSON(str);
    } catch (e) {
        return false;
    }
    return true;
    }
    if(!isJson(p)) {showMessage('warning', 'illegal format!', true);return;}
    $.post("changefields.php",{fields:a},function(msg){ 
        if(msg==1) {
            showMessage('success','<strong>Successfully</strong> changed fields!'); 
            $('#changefields').modal('hide');
            reloadAccounts();
        }
        else {showMessage('warning', "Oops, there's some error. Try again!", true);}
    });
});
$("#newbtn").click(function(){ 
	var newpwd;
	if($("#newiteminput").val()=="") {showMessage("warning", "Account entry can't be empty!", true); return;}
	$("#newbtn").attr("disabled",true);
	$("#newiteminput").attr("readonly",true);
	$("#newiteminputpw").attr("readonly",true);
    for (x in fields)
        $("#newiteminput"+x).attr("readonly",true);
    function process(){
        if($("#newiteminputpw").val()=='') newpwd=getpwd(default_letter_used, default_length); else newpwd=$("#newiteminputpw").val();
        var other = {};
        for (x in fields){
            other[x] = $("#newiteminput"+x).val().trim();
        }
        other = JSON.stringify(other);
        var name = $("#newiteminput").val();
        add_account(name, newpwd, other, function(msg){ 
            if(msg==1) {
                showMessage('success', "Add "+name+" successfully!");
                $('#add').modal('hide');
                reloadAccounts();
            } 
            else showMessage('warning',"Fail to add "+name+", please try again.", true);
            $("#newiteminput").attr("readonly",false);
            $("#newbtn").attr("disabled",false);
            $("#newiteminputpw").attr("readonly",false);
            for (x in fields)
                $("#newiteminput"+x).attr("readonly",false);
        });
    }
    setTimeout(process,50);
});
$("#editbtn").click(function(){ 
	var newpwd;
	if($("#edititeminput").val()=="") {showMessage('warning',"Account entry can't be empty!", true); return;}
	$("#editbtn").attr("disabled",true);
	$("#edititeminput").attr("readonly",true);
	$("#edititeminputpw").attr("readonly",true);
    for (x in fields)
        $("#edititeminput"+x).attr("readonly",true);
    function process(){
        var id = $("#edit").data('id');
        var oldname=accountarray[id]["name"];
        if($("#edititeminputpw").val()=='')
            newpwd=decryptPassword(oldname, $("#edititeminputpw").data('enpassword'));
        else
            newpwd=$("#edititeminputpw").val();
        var other = {};
        for (x in fields){
            other[x] = $("#edititeminput"+x).val().trim();
        }
        other = JSON.stringify(other);
        var name = $("#edititeminput").val();
        newpwd=encryptPassword(name, newpwd);
        other=encryptchar(other, secretkey);
        var enname=encryptchar(name,secretkey);
        $.post("change.php",{name:enname,newpwd:newpwd,index:id,other:other},function(msg){ 
            if(msg==1) {
                showMessage('success',"Data for "+name+" updated!");
                $('#edit').modal('hide');
                reloadAccounts();
            } 
            else showMessage('warning',"Fail to update data for "+name+", please try again.", true);
            $("#edititeminput").attr("readonly",false);
            $("#editbtn").attr("disabled",false);
            $("#edititeminputpw").attr("readonly",false);
            for (x in fields)
                $("#edititeminput"+x).attr("readonly",false);
        });
    }
    setTimeout(process,50);
}); 
$("#backuppwdbtn").click(function(){
    $("#backuppwdbtn").attr('disabled',true);
    $("#backuppwdpb").attr('aria-valuenow',0);
    $("#backuppwdpb").attr('style','width:0%');
    $.post("backup.php",{a:'a'},function(msg){
        var a,i,count,p;
        function progressbarchange(x)
        {
            $("#backuppwdpb").attr('aria-valuenow',x);
            $("#backuppwdpb").attr('style','width:'+x+'%');
        }
        function cback()
        {
            if(count<30) pbkdf2_enc_1(cback); else process();
        }
        function pbkdf2_enc_1(callback)
        {
            progressbarchange(6+count*3);
            a=pbkdf2_enc(a,PWsalt,500);
            count=count+1;
            setTimeout(callback,1);
        }
        function process()
        {
            p.data=encryptchar(JSON.stringify(p.data),pbkdf2_enc(a,PWsalt,500));
            $("#backuppwdpb").attr('aria-valuenow',99);
            $("#backuppwdpb").attr('style','width:99%');
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(JSON.stringify(p)));
            element.setAttribute('download', 'backup.txt');
            element.style.display = 'none';
            document.body.appendChild(element);
            $("#backuppwdpb").attr('aria-valuenow',100);
            $("#backuppwdpb").attr('style','width:100%');
            element.click();
            document.body.removeChild(element);
            $("#backuppwdbtn").attr('disabled',false);
            timeout=default_timeout+Math.floor(Date.now() / 1000);
        }
        function first(callback)
        {
            timeout=1000000+Math.floor(Date.now() / 1000);
            a=pbkdf2_enc(secretkey,PWsalt,500);
            callback(cback);
        }
        count=0;
        try {
            p=JSON.parse(msg);
            if(p.status!="OK") {
                showMessage('warning',"FAIL TO GENERATE BACKUP FILE, TRY AGAIN", true);
                $("#backuppwdbtn").attr('disabled',false);
                return;
            }
        } catch (e) {
            showMessage('warning',"FAIL TO GENERATE BACKUP FILE, TRY AGAIN", true);
            $("#backuppwdbtn").attr('disabled',false);
            return;
        }
        first(pbkdf2_enc_1);
        
    });
});
$("#editAccountShowPassword").click(function(){
    $("#editAccountShowPassword").popover('hide');
    var id = parseInt($("#edit").data('id'));
    var thekey=decryptPassword(accountarray[id]["name"], accountarray[id]['enpassword']);
    if (thekey==""){
        $("#edititeminputpw").val("Oops, some error occurs!");
        return;
    }
    $("#edititeminputpw").val(thekey);
    $("#editAccountShowPassword").addClass("collapse");
});
$("#delbtn").click(function(){
    delepw($("#edit").data('id'));
});
$("#changepw").click(function(){ 
    if(confirm("Your request will be processed on your browser, so it takes some time (up to #of_your_accounts * 10seconds). Do not close your window or some error might happen.\nPlease note we won't have neither your old password nor your new password. \nClick OK to confirm password change request."))
    {
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){showMessage('warning',"The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)", true); return;}
        $("#changepw").attr("disabled",true);
        $("#changepw").attr("value", "Processing...");
        function process(){
        var login_sig=String(pbkdf2_enc(reducedinfo($("#oldpassword").val(),default_letter_used), salt1, 500));
        if(secretkey!=String(CryptoJS.SHA512(login_sig+salt2))) {showMessage('warning',"Incorrect Old Password!", true); return;}
        var newpass=$("#pwd").val();
        login_sig=String(pbkdf2_enc(reducedinfo(newpass, default_letter_used), salt1, 500));
        var newsecretkey=String(CryptoJS.SHA512(login_sig+salt2));
        var postnewpass=pbkdf2_enc(login_sig, salt1, 500);
        //NOTE: login_sig here is the secret_key generated when login.
        var newconfkey=pbkdf2_enc(String(CryptoJS.SHA512(newpass+login_sig)), salt1, 500); 
        var x,raw_pass;
        var temps;
        var passarray=new Array();
        var accarray=new Array();
        for (x in accountarray)
        {
            accarray[x]={"name": encryptchar(accountarray[x]["name"],newsecretkey), "other": encryptchar(JSON.stringify(accountarray[x]["other"]),newsecretkey)};
            raw_pass=decryptPassword(accountarray[x]["name"],accountarray[x]["enpassword"]);
            if (raw_pass=="") {
                showMessage('danger',"FATAL ERROR WHEN TRYING TO DECRYPT ALL PASSWORDS", true);
                return;
            }
            raw_pass=gen_temp_pwd(newconfkey,PWsalt,String(CryptoJS.SHA512(accountarray[x]["name"])),ALPHABET,raw_pass);
            passarray[x]=encryptchar(raw_pass,newsecretkey);
        }
        $.post("changeuserpw.php",{newpass:String(CryptoJS.SHA512(postnewpass+user)), passarray:JSON.stringify(passarray), accarray:JSON.stringify(accarray)},function(msg){ 
            if(msg==1) {
                alert("Change Password Successfully! Please login with your new password again.");
                quitpwd();
            } else {showMessage('warning',"Fail to change your password, please try again.", true); }
        });
        }
        setTimeout(process,50);
	}
});
$("#importbtn").click(function(){ 
    $("#importbtn").attr("disabled",true);
    $("#importbtn").html("Processing...");
    $("#importc").attr("disabled",true);
    function bk(){
    	$("#importbtn").attr("disabled",false);
        $("#importbtn").html("Submit");
        $("#importc").attr("disabled",false);
    }
    function process(){
        if (window.FileReader) {
		// FileReader are supported.
        var reader = new FileReader();
        var a=$("#importc")[0].files;
        var t = 0;
        if (a && a[0]){
            reader.onload = function (e) {
                var txt = e.target.result;
                try{
                    if(t==0) import_raw(txt); else import_csv(txt);
                }catch (error) { showMessage('warning','Some error occurs!', true); reloadAccounts();}
            }
            reader.onerror = function (e) {
                showMessage('warning','Error reading file!', true);
                bk();
            }
            var extension = a[0].name.split('.').pop().toLowerCase();
            if(extension=='csv') t=1;
            reader.readAsText(a[0]);          
        } else {showMessage('warning','NO FILE SELECTED', true); bk();}
	} else {
		showMessage('warning','FileReader are not supported in this browser.', true);
	}
    }
    setTimeout(process,10);
});
$('#edit').on('shown.bs.modal', function () {
    var id = $("#edit").data('id');
    $("#editAccountShowPassword").removeClass("collapse");
    $("#edititeminput").val(accountarray[id]['name']);//name
    $("#edititeminputpw").attr('placeholder',"Hidden");
    $("#edititeminputpw").val('');
    $("#edititeminputpw").data('enpassword', accountarray[id]["enpassword"]);
    for (x in fields){
        $("#edititeminput"+x).val(accountarray[id]['other'][x]);
    } 
});
$('#edit').on('hide.bs.modal', function() {
    $(".popover").popover('hide');
});
});
function edit(row){
    var id = row; //row.find("")
    $("#edit").data("id", id);
    $("#edit").modal("show");
}
function clicktoshow(id){ 
    timeout=default_timeout+Math.floor(Date.now() / 1000);
    id=parseInt(id);
    var thekey = decryptPassword(accountarray[id]["name"],accountarray[id]["enpassword"]);
    if (thekey==""){
        $("#"+id).html("Oops, some error occurs!");
        return;
    }
    $("#"+id).html('<span style="font-family:passwordshow"">'+thekey+'</span><a title="Hide" class="cellOptionButton" href="javascript: clicktohide(\''+id+'\')"><span class="glyphicon glyphicon-eye-close"></span></a>');
} 
function clicktohide(id){
    timeout=default_timeout+Math.floor(Date.now() / 1000);
    $("#"+id).html('<a title="Click to see" href="javascript: clicktoshow(\''+id+'\')"><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span><span class="glyphicon glyphicon-barcode"></span></a>'); 
}
function delepw(index)
{   
    var name=accountarray[parseInt(index)]["name"];
	if(confirm("Are you sure you want to delete password for "+name+"? (ATTENTION: this is irreversible)"))
	{
		$.post("delete.php",{index:index},function(msg){ 
            if(msg==1) {
                showMessage('success',"delete "+name+" successfully");
                $('#edit').modal('hide');
                reloadAccounts();
            } else showMessage('warning',"Fail to delete "+name+", please try again.", true);
	 }); 
	 }
}
function exportcsv()
{
    var obj=new Array();
    timeout=100000+Math.floor(Date.now() / 1000);
    showMessage('info','CSV file contains all your information in plain text format. It\'s dangerous to keep it as a backup. Only use it for transferring your data. Delete it immediately after you\'ve done. Please note the encoding for the csv file is UTF-8. You might need to specify this encoding in order to open this CSV properly in some software that uses ANSI as default encoding such as Microsoft Office.', true);
    var t,x,i;
    for (x in accountarray){
        tmp={};
        tmp['name']=accountarray[x]["name"];
        t=accountarray[x]["other"];
        for (i in t){
            tmp[i] = t[i];
        }
        tmp['password']=decryptPassword(accountarray[x]["name"],accountarray[x]["enpassword"]);
        obj.push(tmp);
    }
    $.getScript( 'js/jquery.csv.js', function() {
        var csv = $.csv.fromObjects(obj);
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(csv));
        element.setAttribute('download', 'export.csv');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    });
    timeout=default_timeout+Math.floor(Date.now() / 1000);
}
function showdetail(index){
    var i=parseInt(index);
    var x,s;
    s='<b>'+accountarray[i]["name"]+'</b><br /><br />';
	s=s+'<table style="width: 100%" font color="#ff0000">';
	s=s+'<colgroup><col width="90"><col width="auto"></colgroup>';
    for (x in accountarray[i]["other"]) {
        if(x in fields){
            s=s+'<tr><td><font color="#afafaf"><style="font-weight: normal;">'+fields[x]['colname']+'</td><td><font color="#6d6d6d"><b>'+accountarray[i]["other"][x]+'<b></td></tr>';
        }
    }
    s=s+'</table>';
	$('#details').html(s);
    $("#showdetails").modal("show");
}
</script>
<?php echofooter();?>
