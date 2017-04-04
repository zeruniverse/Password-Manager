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
function export_csv(){
    if(!confirm('CSV file contains all your information in plain text format. It\'s dangerous to keep it as a backup. Only use it for transferring your data. Delete it immediately after you\'ve done. Please note the encoding for the csv file is UTF-8. You might need to specify this encoding in order to open this CSV properly in some software that uses ANSI as default encoding such as Microsoft Office.')) return;
    var obj= [];
    timeout=100000+Math.floor(Date.now() / 1000);
    var t,x,i;
    for (x in acc_array){
        tmp={};
        tmp['name']=acc_array[x];
        t=other_array[x];
        for (i in t){
            tmp[i] = t[i];
        }
        tmp['password']=pass_array[x];
        obj.push(tmp);
    }
    var csv = $.csv.fromObjects(obj);
    var blob = new Blob([csv], {type: "text/plain;charset=utf-8"});
    saveAs(blob, "export.csv");
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
    $("#csv_button").hide();
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

    var rows = [$('<tr><th>Account</th><th>Password</th><th>Other Info</th></tr>')];
    if(has_file==1) rows.push($('<tr><th>Account</th><th>Password</th><th>Other Info</th><th>Files</th></tr>'));
    for(x in acc_array){
        var row = $('<tr></tr>')
                    .append($('<td></td>').text(acc_array[x]))
                    .append($('<td></td>').text(pass_array[x]))
                    .append($('<td></td>').text(other_array[x]));
        if(has_file==1 && x in fname_array ){
            row.push($('<td></td>')
                        .append($('<a></a>').on('click',{"x":x},function(e){downloada(event.data.x);}).text(fname_array[x])));
        }
        rows.push(row);
    }
    $("#rtable").empty();
    $("#rtable").append(rows);
    $("#recover_result").show();
    $("#chk").removeAttr("disabled");
    $("#chk").attr("value", "RECOVER IT!");
    $("#raw_button").show();
    $("#csv_button").show();
    }
    setTimeout(process,50);
}
$(function(){
    $("#chk").on('click',function(e){readfile();});
});
