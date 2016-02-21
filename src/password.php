<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("function/sqllink.php");
require_once("function/basic.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();header("Location: ./");die();}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();header("Location: ./");die();}

//CHECK AGAIN, TO AVOID PASSWORD CHANGE IN ANOTHER BROWSER
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();header("Location: ./");die();}
echoheader();
?>
<style type="text/css">
@font-face {
	font-family: 'passwordshow';
	src:url('pw.ttf');
}
.theme-showcase
{
	margin-top:50px !important;
}
</style>
<script type="text/javascript" src="setlocalstorage.js"></script>
<script type="text/javascript">
var secretkey;
var fields={url:{colname: 'URL', hint: '', cls: ' hidden'}, 
            user:{colname: 'Username', hint: '', cls: ' ', position: 1}, 
            comment:{colname: 'Comment', hint: '', cls: ' hidden', type: "textarea"},
            tags:{colname: 'Tags', hint: 'Comma separated values', cls: ' hidden-xs'},
            };
var accountarray=new Array();
function quitpwd()
{
    delpwdstore(); window.location.href="./logout.php";
}
</script>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Password-Manager</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav" id="nav_links">
            <li id="nav-passwords" class="active"><a href="#pw">Passwords</a></li>
            <li id="nav-add"><a href="" data-toggle="modal" data-target="#add">Add Entry</a></li>
            <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Profile<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="javascript: alert('You will need your CURRENT login password to unlock the backup file even if you change login password later. Write your CURRENT login password down or remember to generate a new backup file after each time you change the login password.');window.location.href='backup.php'">Back Up</a></li>
              <li><a href="" data-toggle="modal" data-target="#import">Import</a></li>
              <li><a href="" data-toggle="modal" data-target="#changepwd">Change Password</a></li>
            </ul>
            </li>
          </ul>
          <div class="navbar-right">
            <p class="navbar-btn"><a href="" onClick="quitpwd();" class="btn btn-info"><strong>Log Out</strong></a></p>
          </div>
          <div class="col-sm-3 col-md-3 navbar-right">
            <form class="navbar-form" id="searchForm">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search" name="srch-term" id="srch-term">
                <div class="input-group-btn">
                    <button class="btn btn-default collapse" id="resetSearch" onClick="filterAccounts('')" type="button" title="reset search"><i class="glyphicon glyphicon-remove"></i></button>
                    <button class="btn btn-default" type="submit" title="search"><i class="glyphicon glyphicon-search"></i></button>
                </div>
              </div>
            </form>
          </div>
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
            <div class="pull-right-sm" id="tagCloud" style="display:none;"><p class="lead" style="margin-bottom:0">Tag-Overview</p><p class="visible-xs small" style
="margin-bottom:0;"><a href="javascript:$('#tags').toggleClass('hidden-xs');$('.tagsShow').toggleClass('hidden');"><span class="tagsShow">show</span><span class="tagsShow hidden">hide</span> tags</a></p><span class="hidden-xs" id="tags"></span><p class="small" style="display:none;" id="resetFilter"><a href="javascript:filterTags('');">reset filter</a></p></div>
        </div>
    </div>
    <!-- preload ttf -->
    <span style="display:none; font-family:passwordshow"><?php echo $DEFAULT_LETTER_USED; ?></span>
    <div id="waitsign">PLEASE WAIT WHILE WE ARE DECRYPTING YOUR PASSWORD...</div>
    <div id="pwdtable" style="display:none">
    <table class="table" id="pwdlist">
    <tr><th>Account</th><th>Password</th></tr>
    <?php
        $sql="SELECT * FROM `password` WHERE `userid`= ?";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
        $index=$i['index'];
		$name=$i['name'];
        $additionalFields=$i['other'];
		$kss=decrypt($i['pwd'],$i['key']);
		echo "<tr class='datarow' data-additional='".$additionalFields."' dataid='".$index."'><td class='namecell'><span class='accountname' onclick='edit(".$index.")' title='click to edit' dataid=".$index.">".$name.'</span></td><td><span passid="'.$index.'" enpassword="'.$kss.'" id="'.$index.'"><a href="javascript: clicktoshow(\''.$kss.'\',\''.$index.'\')">Click to see</a></span></td></tr>';
		}
    ?>
   </table> 
	<hr />
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
                                <button class="btn btn-warning" onclick="$('#edititeminputpw').val(getpwd('<?php echo $DEFAULT_LETTER_USED; ?>',<?php echo $DEFAULT_LENGTH; ?>)); $('#editAccountShowPassword').removeClass('collapse');" type="button" title="generate new password"><i class="glyphicon glyphicon-refresh"></i></button>
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
                    <form>
                        <div class="form-group">
                            <label for="pinxx" class="control-label">PIN:</label>
                            <input id="pinxx" class="form-control" type="password" />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                    <button type="button" onClick="delpinstore();alert('PIN deleted, use username/password to login next time');$('pin').modal('hide');" class="btn btn-danger" id="delpin">Delete PIN</button>
                    <button type="button" onClick="setpin($('#pinxx').val());" class="btn btn-primary" id="pinlogin">Set/Reset</button>
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
                            <label for="importc" class="control-label">Copy all contents in your RAW backup file/CSV file and paste them into the following box. You should open those files with plain text editor.</label>
                            <textarea class="form-control" id="importc"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Type of input&nbsp;&nbsp;</label>
                            <label class="radio-inline active"><input type="radio" name="importType" id="importTypeBackup" checked="checked">Backup</label>
                            <label class="radio-inline"><input type="radio" name="importType" id="importTypeCSV">CSV</label>
                            <label class="small" style="display:block; clear:both;">CSV file must contain a header line with columns called "name" and "password" - order is not important. You may edit your CSV with your password in Office so that the account field has a header called 'name' and the password field has a header called 'password'. Then you can save the CSV and open it again in plain text editor to copy contents.</label>
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
</div>
</div>
<script type="text/javascript">
var ALPHABET='<?php echo $DEFAULT_LETTER_USED;?>';
var PWsalt='<?php echo $GLOBAL_SALT_2; ?>';
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
function setpin(pin){
    var device=getcookie('device');
    var salt=getpwd('abcdefghijklmnopqrstuvwxyz1234567890',500);
    if(device=="")
    {
        device=getpwd('abcdefghijklmnopqrstuvwxyz1234567890',9)
        setCookie('device',device);
    }
    $.post("setpin.php",{user:getcookie('username'),device:device,sig:String(CryptoJS.SHA512(pin+salt))},function(msg){
        if(msg=='0'){
            alert('ERROR set PIN, try again later!');
            $('pin').modal('hide');
        }else{
            setPINstore(device,salt,encryptchar(getpwdstore(PWsalt),pin+msg),encryptchar(getconfkey(PWsalt),pin+msg));
            alert('PIN set, use PIN to login next time');
            $('pin').modal('hide');
        }
    });   
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
        alert("INVALID RAW FILE");
        return;
    }
    function add_acc(acc,pass,other){
        if(acc==''||pass=='') {
            alert("one of account or password empty! will continue to process other accounts, check back after this finished"); return;
        }
        add_account(acc, pass, other, function(msg) { if(msg!=1) alert("Fail to add "+acc+", please try again manually later."); });
    }
    function process(){
        var aeskey=json.KEY;
        var x;
        for(x in json.data){
            other = "";
            if (json.data[x].length > 2)
                other = decryptchar(json.data[x][2], aeskey);
            add_acc(decryptchar(json.data[x][0],aeskey),decryptchar(json.data[x][1],aeskey), other);
        }
        location.reload(true);
    }
    setTimeout(process,50);
}
function import_csv(csv){
    $.getScript( 'js/jquery.csv.js', function() {
        var accarray = $.csv.toObjects(csv);
        for (x in accarray) {
            var acc = accarray[x]["name"];
            var pass = accarray[x]["password"];
            if(acc==''||pass=='') {
                alert("one of account or password empty! will continue to process other accounts, check back after this finished"); continue;
            }
            var other = {};
            for (key in accarray[x]){
                if (key in fields){
                    other[key]=accarray[x][key];
                }
            }
            add_account(acc, pass, JSON.stringify(other), function(msg) { if(msg!=1) alert("Fail to add "+acc+", please try again manually later."); });
        }
        location.reload(true);
    });
}

function filterTags(tag){
    $("#pwdlist").find("tr").show();
    if (tag == ""){
        $("#resetFilter").hide();
        return;
    }
    var jo = $("#pwdlist").find("tr");
    jo.filter(function (i, v) {
        if ($(this).has("th").length > 0)
            return false;
        var $t = $(this).find(".accounttags");
        if ($t.is(":contains('" +tag + "')")) {
            return false;
        }
        return true;
    })
    .hide();
    $("#resetFilter").show();
}
$(document).ready(function(){
    for (x in fields) {
        fields[x]["count"] = 0;
        var header = '<th class="'+x+'cell'+fields[x]["cls"]+'">'+fields[x]["colname"]+'</th>';
        var cell = '<td class="'+x+'cell'+fields[x]["cls"]+'"><span class="account'+x+'"></td>';
        var input = "";
        var inputtype = "text";
        if ("type" in fields[x])
            inputtype = fields[x]["type"];
        if (inputtype == "textarea")
            input = '<textarea class="form-control" id="%NAME%iteminput'+x+'" placeholder="'+fields[x]["hint"]+'"></textarea>';
        else
            input = '<input class="form-control" id="%NAME%iteminput'+x+'" type="'+inputtype+'" placeholder="'+fields[x]["hint"]+'"/>';
        var form = '<div class="form-group"><label for="%NAME%iteminput'+x+'" class="control-label">'+fields[x]["colname"]+':</label>'+input+'</div>';
        if (("position" in fields[x]) && (fields[x]["position"] != 0)) {
            $('#pwdlist > tbody > tr:first > th:nth-child('+fields[x]["position"]+')').after(header)
            $('#pwdlist > tbody > tr > td:nth-child('+fields[x]["position"]+')').after(cell);
            $("#add").find('form > .form-group:nth-child('+fields[x]["position"]+')').after(form.replace(/%NAME%/g,"new"));
            $("#edit").find('form > .form-group:nth-child('+fields[x]["position"]+')').after(form.replace(/%NAME%/g,"edit"));
        }
        else {
            $("#pwdlist > tbody > tr:first").append(header);
            $("#pwdlist > tbody > tr").slice(1).each(function(){$(this).append(cell) });
            $("#add").find("form").append(form.replace(/%NAME%/g,"new"));
            $("#edit").find("form").append(form.replace(/%NAME%/g,"edit"));
        }
    }
    function getskey(callback)
    {
        var secretkey0=getpwdstore('<?php echo $GLOBAL_SALT_2; ?>');
        callback(secretkey0);
    }
    
    function gatherDistinctTags()
    {
        var tags = new Array();
        for (x in accountarray) {
            if (!("tags" in accountarray[x]["other"]))
                continue;
            if (accountarray[x]["other"]["tags"].length>0)
                tags = tags.concat(accountarray[x]["other"]["tags"].split(',').map(function (str){return str.trim();}));
        }
        var unique = [];
        for(var i = 0; i < tags.length; i++) {
            if($.inArray(tags[i], unique) < 0) {
                unique.push(tags[i]);
            }
        }
        return unique.sort();
    }
    
    function showtable(secretkey0)
    {
        secretkey=String(CryptoJS.SHA512(secretkey0+'<?php echo $GLOBAL_SALT_2; ?>'));
        if (secretkey0=="") quitpwd();
        else
        {
            var tempchar;
            function DecryptCell(element, allowEmpty){
                var tempchar=decryptchar(element.html(),secretkey);
                if ((tempchar=="") && (!allowEmpty)) tempchar="Oops, there's some errors!"
                element.html(tempchar)
                element.addClass("namedone");
                return tempchar;
            };
            $(".datarow").each( function() { 
                    accountarray[parseInt($(this).attr('dataid'))] = { other: {} };
                    if ($(this).data("additional") == "")
                        return;
                    //decrypt
                    var tempchar = decryptchar($(this).data("additional"), secretkey);
                    //extract json
                    var data = $.parseJSON(tempchar);
                    accountarray[parseInt($(this).attr('dataid'))]["other"] = data;
                    //set values
                    for (x in data) {
                        var col = $(this).find('.account'+x);
                        col.html(data[x]);
                        col.addClass("namedone");
                        if ((data[x].trim() != "") && (x in fields)) {
                            fields[x]["count"] += 1;
                        }
                    }
                } );
            $(".accountname").each(function() { accountarray[parseInt($(this).attr('dataid'))]["name"] = DecryptCell($(this),false); });
            $("#waitsign").hide();
            $("#pwdtable").show();
            var tags = gatherDistinctTags();
            for (x in tags){
                $("#tags").append("<a href=\"javascript:filterTags('"+tags[x]+"');\">" + tags[x] + "</a> ");
            }
            if (tags.length>0) {
                $("#tagCloud").show();
            }
            for (x in fields) {
                if (fields[x]["count"] == 0) {
                    $("."+x+"cell").remove();
                }
            }
        }
    }
    getskey(showtable);
$("#newbtn").click(function(){ 
	var newpwd;
	if($("#newiteminput").val()=="") {alert("Account entry can't be empty!"); return;}
	$("#newbtn").attr("disabled",true);
	$("#newiteminput").attr("readonly",true);
	$("#newiteminputpw").attr("readonly",true);
    for (x in fields)
        $("#newiteminput"+x).attr("readonly",true);
    function process(){
        if($("#newiteminputpw").val()=='') newpwd=getpwd('<?php echo $DEFAULT_LETTER_USED; ?>',<?php echo $DEFAULT_LENGTH; ?>); else newpwd=$("#newiteminputpw").val();
        var other = {};
        for (x in fields){
            other[x] = $("#newiteminput"+x).val().trim();
        }
        other = JSON.stringify(other);
        var name = $("#newiteminput").val();
        add_account(name, newpwd, other, function(msg){ 
            if(msg==1) {alert("Add "+name+" successfully!");location.reload(true)} else alert("Fail to add "+name+", please try again.");
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
	if($("#edititeminput").val()=="") {alert("Account entry can't be empty!"); return;}
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
            if(msg==1) {alert("Data for "+name+" updated!");location.reload(true)} else alert("Fail to update data for "+name+", please try again.");
            $("#edititeminput").attr("readonly",false);
            $("#editbtn").attr("disabled",false);
            $("#edititeminputpw").attr("readonly",false);
            for (x in fields)
                $("#edititeminput"+x).attr("readonly",false);
        });
    }
    setTimeout(process,50);
}); 
$("#editAccountShowPassword").click(function(){
    var id = parseInt($("#edit").data('id'));
    var thekey=decryptPassword(accountarray[id]["name"], $("#edititeminputpw").data('enpassword'));
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
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){alert("The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)"); return;}
        $("#changepw").attr("disabled",true);
        $("#changepw").attr("value", "Processing...");
        function process(){
        var login_sig=String(pbkdf2_enc(reducedinfo($("#oldpassword").val(),'<?php echo $DEFAULT_LETTER_USED; ?>'),'<?php  echo $GLOBAL_SALT_1; ?>',500));
        if(secretkey!=String(CryptoJS.SHA512(login_sig+'<?php echo $GLOBAL_SALT_2; ?>'))) {alert("Incorrect Old Password!"); location.reload(); return;}
        var newpass=$("#pwd").val();
        login_sig=String(pbkdf2_enc(reducedinfo(newpass,'<?php echo $DEFAULT_LETTER_USED; ?>'),'<?php  echo    $GLOBAL_SALT_1; ?>',500));
        var newsecretkey=String(CryptoJS.SHA512(login_sig+'<?php echo $GLOBAL_SALT_2; ?>'));
        var postnewpass=pbkdf2_enc(login_sig,'<?php  echo    $GLOBAL_SALT_1; ?>',500);
        //NOTE: login_sig here is the secret_key generated when login.
        var newconfkey=pbkdf2_enc(String(CryptoJS.SHA512(newpass+login_sig)),'<?php  echo $GLOBAL_SALT_1; ?>',500); 
        var x,raw_pass;
        var temps;
        var passarray=new Array();
        var accarray=new Array();
        for (x in accountarray)
        {
            accarray[x]={"name": encryptchar(accountarray[x]["name"],newsecretkey), "other": encryptchar(JSON.stringify(accountarray[x]["other"]),newsecretkey)};
            raw_pass=decryptPassword(accountarray[x]["name"],$("[passid="+x+"]").attr("enpassword"));
            if (raw_pass=="") {
                alert("FATAL ERROR WHEN TRYING TO DECRYPT ALL PASSWORDS");
                return;
            }
            passarray[x]=encryptchar(raw_pass,newsecretkey);
        }
        $.post("changeuserpw.php",{newpass:postnewpass, passarray:JSON.stringify(passarray), accarray:JSON.stringify(accarray)},function(msg){ 
            if(msg==1) {alert("Change Password Successfully! Please login with your new password again.");quitpwd();} else {alert("Fail to change your password, please try again."); location.reload();}
        });
        }
        setTimeout(process,50);
	}
});
$("#importbtn").click(function(){ 
    $("#importbtn").attr("disabled",true);
    $("#importbtn").attr("value", "Processing...");
    $("#importc").attr("readOnly",true);
    if ($('#importTypeBackup').is(':checked'))
        import_raw($('#importc').val()); 
    else if ($('#importTypeCSV').is(':checked'))
        import_csv($('#importc').val());
    $("#importbtn").attr("disabled",false);
    $("#importbtn").attr("value", "Submit");
    $("#importc").attr("readOnly",false);
});
$('#add').on('shown.bs.modal', function () { $('#newiteminput').focus(); });
$('#edit').on('shown.bs.modal', function () {
    var id = $("#edit").data('id');
    $("#editAccountShowPassword").removeClass("collapse");
    $("#edititeminput").val(accountarray[id]['name']);//name
    $("#edititeminputpw").attr('placeholder',"Hidden");
    $("#edititeminputpw").val('');
    $("#edititeminputpw").data('enpassword', $("[passid="+id+"]").attr("enpassword"));
    for (x in fields){
        $("#edititeminput"+x).val(accountarray[id]['other'][x]);
    }
    $('#edititeminput').focus(); 
});
$('#import').on('shown.bs.modal', function () { $('#importc').focus(); });
$('#changepwd').on('shown.bs.modal', function () { $('#oldpassword').focus(); });
$('#searchForm').submit(function () {
    filterAccounts($('#srch-term').val())
    return false;
    });
});
function edit(row){
    var id = row; //row.find("")
    $("#edit").data("id", id);
    $("#edit").modal("show");
}
function clicktoshow(kss,id){ 
    if(kss=="") return;
    var thekey = decryptPassword(accountarray[parseInt(id)]["name"],kss);
    if (thekey==""){
        $("#"+id).html("Oops, some error occurs!");
        return;
    }
    $("#"+id).html('<span style="font-family:passwordshow"">'+thekey+'</span><a title="Hide" class="cellOptionButton" href="javascript: clicktohide(\''+kss+'\',\''+id+'\')"><span class="glyphicon glyphicon-eye-close"></span></a>');
} 
function clicktohide(kss,id){
    $("#"+id).html('<a href="javascript: clicktoshow(\''+kss+'\',\''+id+'\')">Click to see</a>'); 
}
function delepw(index)
{   
    var name=accountarray[parseInt(index)]["name"];
	if(confirm("Are you sure you want to delete password for "+name+"? (ATTENTION: this is irreversible)"))
	{
		$.post("delete.php",{index:index},function(msg){ 
         if(msg==1) {alert("delete "+name+" successfully");location.reload(true);} else alert("Fail to delete "+name+", please try again.");
	 }); 
	 }
}
function filterAccounts(text) {
    $("tr.datarow").show();
    if (text==""){
        $("#resetSearch").hide();
        $("#srch-term").val('');
        return;
    }
    $("#resetSearch").show();
    $("tr.datarow").filter(function() {
        return $(this).find("td > span.namedone").filter(function(){
            return $(this).text().indexOf(text) > -1; })
        .length == 0;
    }).hide();
}
</script>
<?php echofooter();?>
