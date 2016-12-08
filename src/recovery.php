<?php
require_once('function/basic.php');
echoheader();
?>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<script type="text/javascript" src="js/FileSaver.min.js"></script>
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
      <p style="color:red">Select backup.txt containing your backup data.</p>
    <input type="file" id="backupc" accept=".txt" />
    <p> </p>
    <p>Password: <input type="password" autocomplete="off" name="pwd" id="pwd" /></p><br />
    <p style="color:red">Input the login password when you generate the backup file.</p>
    <p> </p>
    <p style="color:red">Recovering takes long time. (No less than backup time) If your web browser asks you whether to kill the page due to no response, choose [wait]!</p>
    </form>
    <input type="button" class="btn btn-md btn-success" onClick="readfile();" id="chk" value="RECOVER IT!" />
    <a href="./" class="btn btn-md btn-info">Go Back</a>
    <a href="javascript: export_raw();" style="display:none" class="btn btn-md btn-danger" id="raw_button">Export Raw Data</a>
    <p> </p>
    <p><br /> </p>
    <div id="recover_result" style="display:none">
    <p>The following table shows your accounts and passwords if you enter the correct login password. If the data loss is caused by attack, please update your passwords anyway!</p>
    <table class="table" id="rtable"></table>
    </div>
<script type="text/javascript">
var acc_array,pass_array,other_array;
var JSsalt='';
var PWsalt='';
var ALPHABET='';
var secretkey='';
var confkey='';
var dkey='';
var has_file=0;
var fname_array;
var fkey_array;
var fdata_array;
function download(filename, text) {
    var blob = new Blob([text], {type: "text/plain;charset=utf-8"});
    saveAs(blob, filename);
}
function export_raw(){
    if(!confirm("Confirm: This function is used ONLY to TRANSFER your password to another password manager! DON'T BACK UP this version, it's DANGEROUS!")) return;
    if(!confirm("You agree you will delete the generated content IMMEDIATELY after you finish transferring your passwords")) return;
    var result = { };

    result.status="RAW_OK";

    var x;
    result.data={ };
    for (x in acc_array)
    {
        result.data[x]={
            'account': acc_array[x],
            'password': pass_array[x],
            'other': other_array[x]
        };
        if(has_file==1 && x in fname_array){
            result.data[x].fname=fname_array[x];
            result.data[x].filedata=fdata_array[x];
        }
    }
    download("raw_pass.raw",JSON.stringify(result));
}
function sanitize_json(s){
    var t=s;
    t=t.replace(/\n/g,'');
    return t.replace(/\r/g,'');
}
function gen_key()
{
    var i;
    var pass=$("#pwd").val();
	secretkey=String(pbkdf2_enc(reducedinfo(pass,ALPHABET),JSsalt,500));
    confkey=pbkdf2_enc(String(CryptoJS.SHA512(pass+secretkey)),JSsalt,500);
    secretkey=String(CryptoJS.SHA512(secretkey+PWsalt));
    dkey=pbkdf2_enc(secretkey,PWsalt,500);
    for(i=0;i<=30;i++) dkey=pbkdf2_enc(dkey,PWsalt,500);
}
function gen_account_array(enc_account_array)
{
    var tempchar,x;
    var account_array=new Array();
    for (x in enc_account_array){
        try {
            tempchar=decryptchar(enc_account_array[x],secretkey);
        } catch (e) {
            tempchar='';
        }
        
        if (tempchar=="") tempchar="Oops, there's some errors!"
        account_array[x]=tempchar;
    }
    return account_array;
}
function gen_fname_array(enc_fname_array)
{
    var tempchar,x;
    var fname_array=new Array();
    for (x in enc_fname_array){
        try {
            tempchar=decryptchar(enc_fname_array[x],secretkey);
        } catch (e) {
            tempchar='';
        }
        
        if (tempchar=="") tempchar="Oops, there's some errors!"
        fname_array[x]=tempchar;
    }
    return fname_array;
}
function gen_fdata_array(fkey_array,enc_fdata_array)
{
    var tempchar,x;
    var fdata_array=new Array();
    for (x in enc_fdata_array){
        try {
            tempchar=decryptchar(enc_fdata_array[x],fkey_array[x]);
        } catch (e) {
            tempchar='';
        }
        
        if (tempchar=="") tempchar="Oops, there's some errors!"
        fdata_array[x]=tempchar;
    }
    return fdata_array;
}
function gen_other_array(enc_other_array)
{
    var tempchar,x;
    var other_array=new Array();
    for (x in enc_other_array){
        try {
            tempchar=decryptchar(enc_other_array[x],secretkey);
        } catch (e) {
            tempchar='';
        }
        if (tempchar=="") tempchar="Oops, there's some errors!"
        other_array[x]=tempchar;
    }
    return other_array;    
}
function gen_pass_array(account_array,enc_pass_array)
{
    var tempchar,x,name;
    var pass_array=new Array();
    for (x in enc_pass_array){
        try {
            tempchar=decryptchar(enc_pass_array[x],secretkey);
        } catch (e) {
            tempchar='';
        }
        if (tempchar=="") {
            tempchar="Oops, there's some errors!";
        }else{
            name=account_array[x];
            tempchar=get_orig_pwd(confkey,PWsalt,String(CryptoJS.SHA512(name)),ALPHABET,tempchar);
        }
        pass_array[x]=tempchar;
    }
    return pass_array;
}
function gen_fkey_array(fname_array,enc_fkey_array)
{
    var tempchar,x,name;
    var pass_array=new Array();
    for (x in enc_fkey_array){
        try {
            tempchar=decryptchar(enc_fkey_array[x],secretkey);
        } catch (e) {
            tempchar='';
        }
        if (tempchar=="") {
            tempchar="Oops, there's some errors!";
        }else{
            name=fname_array[x];
            tempchar=get_orig_pwd(confkey,PWsalt,String(CryptoJS.SHA512(name)),ALPHABET,tempchar);
        }
        pass_array[x]=tempchar;
    }
    return pass_array;
}
function readfile(){
    if (window.FileReader) {
        // FileReader are supported.
        var reader = new FileReader();
        var a=$("#backupc")[0].files;
        if (a && a[0]){
            reader.onload = function (e) {
                var txt = e.target.result;
                rec(txt);
            }
            reader.onerror = function (e) {
                alert('Error reading file!');
            }
            reader.readAsText(a[0]);          
        } else {alert('NO FILE SELECTED');}
    } else {
        alert('FileReader are not supported in this browser.');
    }
}
function downloada(x){
    function base64toBlob(base64Data, contentType) {
        contentType = contentType || '';
        var sliceSize = 1024;
        var byteCharacters = atob(base64Data);
        var bytesLength = byteCharacters.length;
        var slicesCount = Math.ceil(bytesLength / sliceSize);
        var byteArrays = new Array(slicesCount);

        for (var sliceIndex = 0; sliceIndex < slicesCount; ++sliceIndex) {
            var begin = sliceIndex * sliceSize;
            var end = Math.min(begin + sliceSize, bytesLength);

            var bytes = new Array(end - begin);
            for (var offset = begin, i = 0 ; offset < end; ++i, ++offset) {
                bytes[i] = byteCharacters[offset].charCodeAt(0);
            }
            byteArrays[sliceIndex] = new Uint8Array(bytes);
        }
        return new Blob(byteArrays, { type: contentType });
    }
    var data=fdata_array[x];
    var typedata = data.substring(5,data.search(";"));
    data = data.substring(data.search(",")+1);
    saveAs(base64toBlob(data,typedata),fname_array[x]);
}
function rec(txt){
    if($("#pwd").val()==''){
        alert("EMPTY PASSWORD IS NOT ALLOWED");
        return;
    }
    var json=JSON.parse(sanitize_json(txt));
    if(json.status!="OK") {
        alert("INVALID BACKUP FILE");
        return;
    }
    $("#recover_result").hide();
    $("#chk").attr("disabled",true);
    $("#chk").attr("value", "Processing...");
    $("#raw_button").hide();
    JSsalt = json.JSsalt;
    PWsalt = json.PWsalt;
    ALPHABET = json.ALPHABET;
    function process(){       
    gen_key();
    try{
        json.data=JSON.parse(decryptchar(json.data,dkey));
        if(typeof json.fdata != 'undefined'){
            json.fdata=JSON.parse(decryptchar(json.fdata,dkey));
            if(json.fdata.status=='OK'){
                json.fdata=json.fdata.data;
                has_file=1;
            }else
            {
                has_file=0;
            }
        } else has_file=0;
        
    }catch (e) {
            alert("Wrong password, try again!");
            $("#chk").removeAttr("disabled");
            $("#chk").attr("value", "RECOVER IT!");
            return;
    }
    var enc_pass=new Array();
    var enc_acc=new Array();
    var enc_other=new Array();
    var x;
    for(x in json.data){
        enc_acc[x]=json.data[x][0];
        enc_pass[x]=json.data[x][1];
        enc_other[x]=json.data[x][2];
    }
    acc_array=gen_account_array(enc_acc);
    other_array=gen_other_array(enc_other)
    pass_array=gen_pass_array(acc_array,enc_pass);
    
    if(has_file==1) {
        var enc_fname=new Array();
        var enc_fkey=new Array();
        var enc_fdata=new Array();
        for(x in json.fdata){
            enc_fname[x]=json.fdata[x][0];
            enc_fkey[x]=json.fdata[x][1];
            enc_fdata[x]=json.fdata[x][2];
        }
        fname_array=gen_fname_array(enc_fname);
        fkey_array=gen_fkey_array(fname_array,enc_fkey);
        fdata_array=gen_fdata_array(fkey_array,enc_fdata);
    }
    


    var html='<tr><th>Account</th><th>Password</th><th>Other Info</th></tr>';
    if(has_file==1) html='<tr><th>Account</th><th>Password</th><th>Other Info</th><th>Files</th></tr>';
    for(x in acc_array){
        html=html+'<tr><td>'+acc_array[x]+'</td><td>'+pass_array[x]+'</td><td>'+other_array[x]+'</td>'
        if(has_file==1){
            html=html+'<td>'
            if(x in fname_array) html=html+'<a href="javascript: downloada('+x+')">'+fname_array[x]+'</a>';
            html=html+'</td>';
        }
        html=html+'</tr>';
    }
    $("#rtable").html(html);
    $("#recover_result").show();
    $("#chk").removeAttr("disabled");
    $("#chk").attr("value", "RECOVER IT!");
    $("#raw_button").show();
    }
    setTimeout(process,50);
}
</script>
</div>
<?php echofooter();?>
