const thisIsThePasswordManager = "21688ab4-8e22-43b0-a988-2ca2c98e5796";
//everything is going to be loaded later
var secretkey;
var default_timeout;
var server_timeout;
var default_server_timeout;
var timeout;
var default_letter_used;
var default_length;
var salt1;
var salt2;
var user;
var fields;
var accountarray= [];
var visibleAccounts;
var seenLoginInformation = false;

$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
    if (options.type.toLowerCase() === "post") {
        options.data = options.data || "";
        options.data += options.data?"&":"";
        options.data += "session_token=" + localStorage.session_token;
    }
});

function quitpwd(reason) {
    reason = reason || "";
    callPlugins("quitpwd",{"reason":reason});
    delpwdstore();
    if (reason != "")
        reason ="?reason="+encodeURIComponent(reason);
    window.location.href="./logout.php"+reason;
}
function quitpwd_untrust() {
    callPlugins("quitpwd_untrust");
    delpwdstore();
    delpinstore();
    deleteCookie('username');
    window.location.href="./logout.php";
}
function countdown() {
    if(timeout < Math.floor(Date.now() / 1000)) {
        quitpwd("Logged out due to inactivity");
    }
}
function checksessionalive()
{
    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length,c.length);
            }
        }
        return "-1";
    }
    function setCookie(cname, cvalue) {
        document.cookie = cname + "=" + cvalue + ";path=/ ";
    }
    var ck=getCookie("ServerRenew");
    if(ck=='1') server_timeout=default_server_timeout+Math.floor(Date.now() / 1000);
    if(ck=="-1"||server_timeout<Math.floor(Date.now() / 1000)) quitpwd("Session timed out");
    setCookie("ServerRenew",'0');
}
var ALPHABET;
var PWsalt;
var datatablestatus=null;
var fileid=-1;
var file_enabled;
var preDrawCallback = function( api, settings ) {};
var preShowPreparation = function (accounts){ return accounts; };// if you change the array make a copy before sorting! So indexes stay the same in the original array
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
    other=JSON.parse(other);
    if(!("_system_passwordLastChangeTime" in other)) 
        other["_system_passwordLastChangeTime"] = Math.floor(Date.now() / 1000);
    other=JSON.stringify(other);
    other=encryptchar(other, sk);
    acc=encryptchar(acc,sk);
    $.post("rest/insert.php",{name:acc,newpwd:pass,other:other},callback);
}
function upload_file(fileid, filename, filedata, callback) {
    var fkey = getpwd(default_letter_used, Math.floor(Math.random()*18)+19);
    var enfkey = encryptPassword(filename, fkey);
    var endata = encryptchar(filedata, fkey);
    var enfname = encryptchar(filename, secretkey);
    $("#showdetails").modal("hide");
    $.post('rest/uploadfile.php', {id:fileid, fkey:enfkey, fname:enfname, data:endata}, callback);
}
function import_raw(json){
    json=JSON.parse(sanitize_json(json));
    if(json.status!="RAW_OK") {
        showMessage("warning", "INVALID RAW FILE", true);
        return;
    }
    function bk(){
        $("#importbtn").attr("disabled",false);
        $("#importbtn").text("Submit");
        $("#importc").attr("disabled",false);
    }
    function add_acc(acc,pass,other){
        if(acc==''||pass=='') {
            showMessage('warning', "one of account or password empty! will continue to process other accounts, check back after this finished", true); return;
        }
        add_account(acc, pass, other, function(msg) { 
            if(msg["status"] != "success") {
                showMessage('warning',"Fail to add "+acc+", please try again manually later.", true); 
            }
        });
    }
    function add_acc_file(acc,pass,other,fname,fdata){
        function addfile(msg){
            if(msg["status"] != "success") {
                showMessage('warning', "Fail to add "+acc+",  please try again manually later.",  true); 
            }
            else{
                upload_file(msg["nid"], fname, fdata, function(msg){
                    if(msg["status"] != 'success') {
                        showMessage('warning',"Fail to add file for "+acc+", please try again manually later.", true);
                    }
                });
            }
        }
        if(acc=='' || pass=='' || fname=='') {
            showMessage('warning', "one of account, password or filename empty! will continue to process other accounts, check back after this finished", true); 
            return;
        }
        add_account(acc, pass, other, addfile);
    }
    function onsucc(){
        showMessage('success','IMPORT FINISHED!');
        $('#import').modal('hide');
        bk();
        reloadAccounts();
    }
    function process(){
        var x;
        timeout=1000000+Math.floor(Date.now() / 1000);
        for(x in json.data){
            if(typeof json.data[x].fname != 'undefined'){
                add_acc_file(json.data[x].account,json.data[x].password,json.data[x].other,json.data[x].fname,json.data[x].filedata);
            }
            else
                add_acc(json.data[x].account,json.data[x].password,json.data[x].other);
        }
    }
    process();
    setTimeout(onsucc,1000);
    
}
function import_csv(csv){
    function importError(msg){ 
        if(msg["status"] != "success") {
            showMessage('warning', "Fail to add " + acc + ", please try again manually later.", true); 
        }
    }
	var accarray = $.csv.toObjects(csv);
	timeout=1000000+Math.floor(Date.now() / 1000);
	for (var x in accarray) {
	    var acc = accarray[x]["name"];
	    var pass = accarray[x]["password"];
	    if(acc=='' || pass=='') {
	        showMessage('danger',"one of account or password empty! will continue to process other accounts, check back after this finished", true); continue;
	    }
	    var other = {};
	    for (var key in accarray[x]){
	        if (key in fields){
	            other[key]=accarray[x][key];
	        }
	    }
	    add_account(acc, pass, JSON.stringify(other), importError);
	}
    function bk(){
        $("#importbtn").attr("disabled",false);
        $("#importbtn").text("Submit");
        $("#importc").attr("disabled",false);
    }
	function onsucc(){
	    showMessage('success', 'IMPORT FINISHED!');
	    $('#import').modal('hide');
	    bk();
	    reloadAccounts();
	}
	setTimeout(onsucc,1000);
}
//type: any of "success", "info", "warning", "danger"
//message: text
//modal: if true shows a modal window
function showMessage(type, message, modal){
    modal = (typeof modal !== 'undefined') ? modal : false;
    if (modal==false) {
        var messageDialog = $("<div>")
                    .addClass("alert")
                    .addClass("alert-"+type)
                    .addClass("collapse")
                    .append($('<a href="#" class="close" aria-label="close">&times;</a>')
                            .click(function(e){
                                messageDialog.alert('close'); 
                                e.stopImmediatePropagation()
                            }))
                    .append($('<span>').text(message));
        $("#messageContainer").append(messageDialog);
        messageDialog.fadeIn();
        if(type == "success" || type == "info"){
            messageDialog.fadeTo(6000, 500).slideUp(500, function(){ // 6000 ms
                messageDialog.alert('close');
            });
        }
        return messageDialog;
    }
    else {
        $("#messageDialogText").text(message);
        $("#messageDialogText").removeClass("alert-success alert-info alert-warning alert-danger");
        $("#messageDialogText").addClass("alert-"+type);
        $("#messageDialog").modal('show');
    }
}
// show last succesfull Login
// changes the seenLoginInformation global variable
function showLastLoginInformation(failedCount, lastLogin){
    if (!seenLoginInformation) {
        var loginMsgType = 'info';
        var failedMsg = '';
        if (failedCount > 0){
            loginMsgType = 'danger';
            failedMsg = 'Since then there {0} ' + failedCount + ' failed login attempt{1}.';
            if (failedCount > 1){
                failedMsg = failedMsg.replace("\{0\}", "where").replace("\{1\}", "s");
            }
            else {
                failedMsg = failedMsg.replace("\{0\}", "was").replace("\{1\}", "");
            }
        }
        if((lastLogin > 0) || (failedCount > 0)) {
            showMessage(loginMsgType, 'Your last login was on ' + timeConverter(lastLogin)+'. ' + failedMsg + ' Click for more information.')
                .on('click',function(event){
                    $(this).alert('close');
                    $('#historyformsesstoken').val(localStorage.session_token);
                    $('#historyform').submit();
                });
        }
        seenLoginInformation = true;
    }
}
// decrypts an account entry
// changes the fields global variable
function decryptAccount(encryptedAccount) {
    var index = encryptedAccount["index"];
    var decryptedAccount = { "index":index, "other": {} };
    decryptedAccount["fname"]=''; 
    decryptedAccount["name"] = decryptchar(encryptedAccount["name"],secretkey);
    decryptedAccount["enpassword"] = encryptedAccount["kss"];
    if (encryptedAccount["additional"] != "") {
        //decrypt
        var tempchar = decryptchar(encryptedAccount["additional"], secretkey);
        //extract json
        var data = $.parseJSON(tempchar);
        decryptedAccount["other"] = data;
        for (var x in decryptedAccount["other"])
            if ( (decryptedAccount["other"][x] != "") && (x in fields) )
                fields[x]["count"] += 1;
    }
    return decryptedAccount;
}
function dataReady(data){
    callPlugins("dataReady",{"data":data});
    if (data["status"]=="error") {
        quitpwd("Login failed: " + data["message"]);
        return;
    }
    default_timeout = data["default_timeout"];
    default_server_timeout = data["server_timeout"];
    file_enabled=data['file_enabled'];
    server_timeout = default_server_timeout+Math.floor(Date.now() / 1000);
    timeout = default_timeout+Math.floor(Date.now() / 1000);
    default_letter_used = data["default_letter_used"];
    default_length = data["default_length"];
    salt1 = data["global_salt_1"];
    salt2 = data["global_salt_2"];
    user = data["user"];
    fields = $.parseJSON(data["fields"]);
    for (var x in fields) {
        fields[x]["count"] = 0;
    }
    var accounts = data["accounts"];
    var fdata=data["fdata"];
    setInterval(countdown, 1000);
    setInterval(checksessionalive,1000); 
    ALPHABET = default_letter_used;
    PWsalt = salt2;
    if(file_enabled==1) $("#fileincludeckbp").show(); else $("#fileincludeckbp").hide();
    if(!data["fields_allow_change"])
        $("#changefieldsnav").hide();
    var secretkey0=getpwdstore(salt2);
    if (secretkey0==""){
        quitpwd("Login failed, due to missing secretkey");
        return;
    }
    secretkey=String(CryptoJS.SHA512(secretkey0+salt2));
    
    showLastLoginInformation(data["loginInformation"]["failedCount"], data["loginInformation"]["lastLogin"]);

    // decrypt accounts
    for(var i = 0; i<accounts.length; i++) {
        var index = accounts[i]["index"];
        accountarray[index] = decryptAccount(accounts[i])
        callPlugins("readAccount",{"account":accountarray[index]});
    }

    // add files to accounts
    for(var i = 0; i<fdata.length; i++) {
        var index = fdata[i]["index"];
        accountarray[index]['fname'] = decryptchar(fdata[i]['fname'],secretkey);
        accountarray[index]['fkey'] = fdata[i]['fkey'];
    }

    callPlugins("accountsReady");
    initFields();
    callPlugins("fieldsReady", {"fields":fields, "accounts":accountarray});
    showTable(accountarray);
}
function initFields() {
    $("textarea#fieldsz").val(JSON.stringify(fields));
    for (var x in fields) {
        var header = "";
        if (fields[x]["count"]>0)
            header = $('<th>')
                        .attr('class',x+'cell'+fields[x]["cls"]+' field')
                        .text(fields[x]["colname"]);
        var forms = {};
        for (var val of ['new','edit']){
            var input;
            var inputtype = "text";
            if ("type" in fields[x])
                inputtype = fields[x]["type"];
            if (inputtype == "textarea")
                input = $('<textarea>');
            else
                input = $('<input>').attr('type',inputtype);
            input.attr('class','form-control')
                .attr('id',val+'iteminput'+x)
                .attr('placeholder',fields[x]["hint"]);
            var form = $('<div>').attr('class','form-group field')
                .append($('<label>')
                    .attr('for',val+'iteminput'+x)
                    .attr('class','control-label').text(fields[x]["colname"]))
                .append(input);
            forms[val] = form;
        }
        if (("position" in fields[x]) && (fields[x]["position"] != 0)) {
            $('#pwdlist > thead > tr:first > th:nth-child('+fields[x]["position"]+')').after(header)
            $("#add").find('form > .form-group:nth-child('+fields[x]["position"]+')').after(forms["new"]);
            $("#edit").find('form > .form-group:nth-child('+fields[x]["position"]+')').after(forms["edit"]);
        }
        else {
            $("#pwdlist > thead > tr:first").append(header);
            $("#add").find("form").append(forms["new"]);
            $("#edit").find("form").append(forms["edit"]);
        }
        callPlugins("readField", {"field":fields[x]});
    }
}
// accounts as parameter to have the possibility to only show a subset i.e. for pagination
function showTable(accounts)
{
    accounts=preShowPreparation(accounts);
    visibleAccounts=accounts;
    var asterisk = $('<span>').attr('class','glyphicon glyphicon-asterisk');
    var pwdLink = $('<a>').attr('title','Click to see')
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk);
    for(var index in accounts) {
        var cols = [];
        cols.push($("<td>")
            .attr('class','namecell')
            .append($("<span>")
                .attr('class','accountname')
                .data('id',accounts[index]["index"])
                .text(accounts[index]["name"]))
            .append($('<a>')
                .attr('title',"Edit")
                .attr('class','cellOptionButton')
                .on('click',{"index":accounts[index]["index"]},function(event){edit(event.data.index);}) 
                .append($('<span></span>')
                    .attr('class','glyphicon glyphicon-wrench')))
            .append($('<a>')
                .attr('title','Details')
                .attr('class','cellOptionButton')
                .on('click',{"index":accounts[index]["index"]},function(event){showdetail(event.data.index);}) 
                .append($('<span class="glyphicon glyphicon-eye-open"></span>')))
        );
        cols.push($('<td>')
            .append($('<span>')
                .attr('passid',accounts[index]["index"])
                .attr('enpassword',accounts[index]["enpassword"])
                .attr('id',accounts[index]["index"])
                .append(pwdLink.clone()
                            .on('click',{"index":accounts[index]["index"]},function(event){clicktoshow(event.data.index);}) 
                        )
            ));
        // fill in other
        for (x in fields) {
            if (fields[x]["count"]>0) { 
                var value="";
                if (x in accounts[index]["other"])
                    value = accounts[index]["other"][x];
                var cell = $('<td>').attr('class', x+'cell'+fields[x]["cls"])
                    .append($('<span>').attr('class','account'+x).text(value));
                if (("position" in fields[x]) && (fields[x]["position"] != 0)) {
                    cols.splice(fields[x]["position"], 0, cell);
                }
                else
                    cols.push(cell);
            }
        }
        // create row for datatable
        var row = $("<tr>").attr('class','datarow').data('id',accounts[index]["index"]).append(cols);
        callPlugins("drawAccount", {"account": accounts[index], "row":row});
        datatablestatus.row.add(row);
    }

    datatablestatus.draw();

    $("#waitsign").hide();
    $("#pwdtable").show();

}
function downloadf(id){ 
    $("#messagewait").modal("show");
    $.post('rest/downloadfile.php',{id:id},function(filedata){
        if(filedata['status'] != "success") {
            showMessage('danger','ERROR! '+filedata['message'], false);
        }
        else{
            var fname = accountarray[id]['fname'];
            if(fname=='') showMessage('danger','ERROR! '+filedata['message'], false);
            else{
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
                
                var fkey = decryptPassword(fname,filedata['key']);
                var data = decryptchar(filedata['data'],fkey);
                var typedata = data.substring(5,data.search(";"));
                data = data.substring(data.search(",")+1);
                saveAs(base64toBlob(data,typedata),fname);
            }
        }
        $("#messagewait").modal("hide");
    });
};
function emptyTable() {
    datatablestatus.clear();
}
function cleanUp() {
    accountarray = [];
    emptyTable();
    $(".field").remove();
}
function reloadAccounts() {
    cleanUp();
    $.post("rest/password.php",{},function(msg){dataReady(msg);});
}
$(document).ready(function(){
    datatablestatus=$("#pwdlist").DataTable({ordering:false, info:true,autoWidth:false, "deferRender": true, drawCallback: function(settings) { preDrawCallback( this.api(), settings);}, "lengthMenu": [ [10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"] ] });
    $.post("rest/password.php",{},function(msg){dataReady(msg);});
    $("#pinloginform").on('submit',function(e){
        e.preventDefault();
        var pin = $("#pinxx").val();
        var device = getcookie('device');
        var salt = getpwd('abcdefghijklmnopqrstuvwxyz1234567890',500);
        timeout = default_timeout+Math.floor(Date.now() / 1000);
        function process()
        {
            $.post("rest/setpin.php",{user:getcookie('username'),device:device,sig:String(CryptoJS.SHA512(pin+salt))},function(msg){
                if(msg["status"] != "success"){
                    showMessage('warning', 'ERROR set PIN, try again later!', true);
                    $('#pin').modal('hide');
                }
                else{
                    setPINstore(device, salt, encryptchar(getpwdstore(PWsalt), pin + msg["pinpk"]), encryptchar(getconfkey(PWsalt), pin + msg["pinpk"]));
                    showMessage('success', 'PIN set, use PIN to login next time');
                    $('#pin').modal('hide');
                }
            });
        }
        if (pin.length<4) {
            showMessage('warning', 'For security reason, PIN should be at least of length 4.', true); 
            return;
        }
        if (device == "") {
            function rand_device() {
                device = getpwd('abcdefghijklmnopqrstuvwxyz1234567890', 9);
                setCookie('device', device);
                $.post("rest/getpinpk.php", {user:getcookie('username'), device:device, sig:'1'}, function(msg){
                    // check if we somehow managed to get an existing PIN
                    if (msg["status"] == "success") {
                        rand_device();
                    }
                    else {
                        process();
                    }
                });
            }
            rand_device();
        } 
        else {
            process();  
        }
    });
    $("#changefieldsbtn").click(function(){
        var a = $('#fieldsz').val();
        var p = a.replace(/\r\n/g,'');
        p = p.replace(/\n/g,'');
        function isJson(str) {
            try {
                $.parseJSON(str);
            } catch (e) {
                return false;
            }
            return true;
        }
        if(!isJson(p)) {
            showMessage('warning', 'illegal format!', true);
            return;
        }
        var j = JSON.parse(p);
        for (x in j){
            if (x.substr(0,1) == '_'){
                showMessage('warning', 'illegal fields!', true);
                return;
            }
        }
        $.post("rest/changefields.php",{fields:a},function(msg){ 
            if(msg["status"]=="success") {
                showMessage('success','Successfully changed fields!'); 
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
                if(msg["status"] == "success") {
                    showMessage('success', "Add " + name + " successfully!");
                    $('#add').modal('hide');
                    reloadAccounts();
                } 
                else {
                    showMessage('warning',"Fail to add "+name+", please try again.", true);
                }
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
            var other = {};
            for (x in fields){
                other[x] = $("#edititeminput"+x).val().trim();
            }
            // get all _Fields from the original data 
            for (x in accountarray[id]){
                if (x.substring(0,1) == "_"){
                    other[x] = accountarray[id]["other"][x];
                }
            }
            if($("#edititeminputpw").val() == ''){
                newpwd=decryptPassword(oldname, $("#edititeminputpw").data('enpassword'));
            }
            else{
                newpwd=$("#edititeminputpw").val();
                other["_system_passwordLastChangeTime"] = Math.floor(Date.now() / 1000);
            }

            other = JSON.stringify(other);
            var name = $("#edititeminput").val();
            newpwd = encryptPassword(name, newpwd);
            other = encryptchar(other, secretkey);
            var enname = encryptchar(name,secretkey);
            $.post("rest/change.php", {name:enname, newpwd:newpwd, index:id, other:other}, function(msg){ 
                if(msg["status"] != "success") {
                    showMessage('success',"Data for " + name + " updated!");
                    $('#edit').modal('hide');
                    reloadAccounts();
                } 
                else {
                    showMessage('warning',"Fail to update data for " + name + ", please try again.", true);
                }
                $("#edititeminput").attr("readonly",false);
                $("#editbtn").attr("disabled",false);
                $("#edititeminputpw").attr("readonly",false);
                for (x in fields)
                    $("#edititeminput" + x).attr("readonly",false);
            });
        }
        setTimeout(process,50);
    }); 
    $("#backuppwdbtn").click(function(){
        $("#backuppwdbtn").attr('disabled',true);
        $("#backuppwdpb").attr('aria-valuenow',0);
        $("#backuppwdpb").css('width','0%');
        $("#fileincludeckb").attr('disabled',true);
        var fileinclude="a";
        if($("#fileincludeckb").is(':checked')) fileinclude="farray";
        $.post("rest/backup.php",{a:fileinclude},function(msg){
            var a,count,p;
            function progressbarchange(x) {
                $("#backuppwdpb").attr('aria-valuenow',x);
                $("#backuppwdpb").css('width',x+'%');
            }
            function cback() {
                if(count<30) pbkdf2_enc_1(cback); else process();
            }
            function pbkdf2_enc_1(callback) {
                progressbarchange(6+count*3);
                a=pbkdf2_enc(a,PWsalt,500);
                count=count+1;
                setTimeout(callback,1);
            }
            function process() {
                p.data=encryptchar(JSON.stringify(p.data),pbkdf2_enc(a,PWsalt,500));
                p.fdata=encryptchar(JSON.stringify(p.fdata),pbkdf2_enc(a,PWsalt,500));
                $("#backuppwdpb").attr('aria-valuenow',99);
                $("#backuppwdpb").css('width','99%');
                var blob = new Blob([JSON.stringify(p)], {type: "text/plain;charset=utf-8"});
                saveAs(blob, "backup.txt");

                $("#backuppwdbtn").attr('disabled',false);
                $("#fileincludeckb").attr('disabled',false);
                timeout=default_timeout+Math.floor(Date.now() / 1000);
            }
            function first(callback) {
                timeout=1000000+Math.floor(Date.now() / 1000);
                a=pbkdf2_enc(secretkey,PWsalt,500);
                callback(cback);
            }
            count=0;
            try {
                p = msg;
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
                var x,raw_pass,raw_fkey;
                var accarray= [];
                for (x in accountarray)
                {
                    var tmpother=accountarray[x]["other"];
                    accarray[x]={"name": encryptchar(accountarray[x]["name"],newsecretkey), "is_f":1, "fname": '',"other": encryptchar(JSON.stringify(tmpother),newsecretkey)};
                    if(accountarray[x]["fname"]=='') {
                        accarray[x]['is_f']=0;
                    } 
                    else {
                        accarray[x]["fname"]=encryptchar(accountarray[x]["fname"],newsecretkey);
                    }
                    raw_fkey='1';
                    raw_pass=decryptPassword(accountarray[x]["name"],accountarray[x]["enpassword"]);
                    if(accountarray[x]["fname"]!='') {
                        raw_fkey=decryptPassword(accountarray[x]['fname'],accountarray[x]['fkey']);
                    }
                    if (raw_pass==""||raw_fkey=='') {
                        showMessage('danger',"FATAL ERROR WHEN TRYING TO DECRYPT ALL PASSWORDS", true);
                        return;
                    }
                    raw_pass=gen_temp_pwd(newconfkey,PWsalt,String(CryptoJS.SHA512(accountarray[x]["name"])),ALPHABET,raw_pass);
                    raw_fkey=gen_temp_pwd(newconfkey,PWsalt,String(CryptoJS.SHA512(accountarray[x]["fname"])),ALPHABET,raw_fkey);
                    accarray[x]["newpwd"] = encryptchar(raw_pass,newsecretkey);
                    accarray[x]["fk"] = encryptchar(raw_fkey,newsecretkey);
                }
                $.post("rest/changeuserpw.php",{newpass:String(CryptoJS.SHA512(postnewpass+user)), accarray:JSON.stringify(accarray)},function(msg){ 
                    if(msg["status"] == "success") {
                        alert("Change Password Successfully! Please login with your new password again.");
                        quitpwd("Password changed, please relogin");
                    } 
                    else {
                        showMessage('warning',"Fail to change your password, please try again.", true); 
                    }
                });
            }
            setTimeout(process,50);
        }
    });
    $("#importbtn").click(function(){ 
        $("#importbtn").attr("disabled",true);
        $("#importbtn").text("Processing...");
        $("#importc").attr("disabled",true);
        function bk(){
            $("#importbtn").attr("disabled",false);
            $("#importbtn").text("Submit");
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
                        }catch (error) { showMessage('warning','Some error occurs!', true); bk(); reloadAccounts();}
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


    $("#uploadfilebtn").click(function(){ 
        $("#uploadfilebtn").attr("disabled",true);
        $("#uploadfilebtn").text("Processing...");
        $("#uploadf").attr("disabled",true);
        function bk(){
            $("#uploadfilebtn").attr("disabled",false);
            $("#uploadfilebtn").text("Submit");
            $("#uploadf").attr("disabled",false);
        }
        function process(){
            if (window.FileReader) {
                // FileReader are supported.
                var reader = new FileReader();
                var a=$("#uploadf")[0].files;
                var fname='';
                if (a && a[0]){
                    reader.onload = function (e) {
                        var data = e.target.result;
                        try{
                            upload_file(fileid, fname, data, function(msg){
                                if(msg["status"] == "success") {
                                    $('#uploadfiledlg').modal("hide"); 
                                    showMessage('success','File uploaded!', false); 
                                    reloadAccounts();
                                }
                                else {
                                    $('#uploadfiledlg').modal("hide"); 
                                    showMessage('danger','ERROR! Try again!', false); 
                                    reloadAccounts();
                                }
                            });
                        }
                        catch (error) {
                            $('#uploadfiledlg').modal("hide"); 
                            showMessage('warning','Some error occurs!', true); 
                            reloadAccounts();
                        }
                    }
                    reader.onerror = function (e) {
                        showMessage('warning','Error reading file!', true);
                        bk();
                    }
                    fname = a[0].name;
                    if(fname==''){
                        showMessage('warning','File selected doesn\'t have a name!', true); 
                        bk(); 
                        return;
                    }
                    reader.readAsDataURL(a[0]);          
                } else {showMessage('warning','NO FILE SELECTED', true); bk();}
            } else {
                showMessage('warning','FileReader are not supported in this browser.', true);
            }
        }
        setTimeout(process,10);
    });

    $('#add').on('show.bs.modal', function () {
        $(this).find('form')[0].reset();
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
        callPlugins("editAccountDialog",{"account": accountarray[id]});
    });
    $('#edit').on('hide.bs.modal', function() {
        $(".popover").popover('hide');
    });
    $('#editPasswordInput').on('click', function() {
        $('#edititeminputpw').val(getpwd(default_letter_used, default_length));
        $('#editAccountShowPassword').removeClass('collapse');
        $('#editAccountShowPassword').popover({ 
            'placement':'bottom',
            'title':'',
            'container':'body',
            'content':'Click here to get your old password back.',
            'trigger':'manual' })
            .on('shown.bs.popover', function(){
                $('.popover').on('click',function(){
                    $("#editAccountShowPassword").popover("hide");
                });
                $('.popover-title').hide();
            })
            .popover('show'); 
    });
    $('#pinBtnDel').on('click',function(){
        delpinstore();
        showMessage('info', 'PIN deleted, use username/password to login next time', true);
        $('#pin').modal('hide');
    });
    $('#navBtnLogout').on('click',function(){quitpwd();});
    $('#navBtnUntrust').on('click',function(){quitpwd_untrust();});
    $('#navBtnExport').on('click',function(){exportcsv()});
    $('#navBtnActivity').on('click',function(){
        $('#historyformsesstoken').val(localStorage.session_token);
        $('#historyform').submit();
    });
    callPlugins("layoutReady");
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
        $("#"+id).text("Oops, some error occurs!");
        return;
    }
    $("#"+id).empty()
        .append($('<span class="pwdshowbox"></span>').css('font-family','passwordshow'))
        .append($('<a title="Hide" class="cellOptionButton"></a>')
                .on('click',{"index":id},function(event){clicktohide(event.data.index);}) 
                .append($('<span class="glyphicon glyphicon-eye-close"></span>')));
    $("#"+id+" > .pwdshowbox").text(thekey);
} 
function showuploadfiledlg(id){
    $("#uploadfiledlg").modal("hide");
    $("#uploadfitemlab1").text(accountarray[id]["name"]);
    $("#uploadfitemlab2").text(accountarray[id]["name"]);
    $("#uploadfilebtn").attr("disabled",false);
    $("#uploadfilebtn").text("Submit");
    $("#uploadf").attr("disabled",false);
    fileid=id;
    $("#uploadfiledlg").modal("show");
}
function clicktohide(id){
    timeout=default_timeout+Math.floor(Date.now() / 1000);
    $("#"+id).empty().append($('<a title="Click to see"></a>')
                        .on('click',{"index":id},function(event){clicktoshow(event.data.index);}) 
                        .append('<span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span>') );
}
function delepw(index)
{   
    var name=accountarray[parseInt(index)]["name"];
    if(confirm("Are you sure you want to delete password for "+name+"? (ATTENTION: this is irreversible)"))
    {
        $.post("rest/delete.php",{index:index},function(msg){ 
            if(msg["status"] == "success") {
                showMessage('success',"delete "+name+" successfully");
                $('#edit').modal('hide');
                reloadAccounts();
            } 
            else 
                showMessage('warning',"Fail to delete "+name+", please try again.", true);
     }); 
     }
}
function exportcsv()
{
    alert('To discourage users from exporting CSV, we have moved this feature to the RECOVERY page. Please backup the passwords first and go to recovery page (link can be found at the login page).');
}
function showdetail(index){
    var i=parseInt(index);
    var x,s;
    s=$('#details');
    s.html('');
    s.append($('<b>').text(accountarray[i]["name"]))
     .append($('<br/>')).append($('<br/>'));
    var table=$('<table>').css('width',"100%").css('color',"#ff0000")
            .append($('<colgroup><col width="90"><col width="auto"></colgroup>'));
    for (x in accountarray[i]["other"]) {
        if(x in fields){
            table.append($('<tr>')
                .attr("id","detailsTableOther"+x)
                .append($('<td>').css("color","#afafaf").css("font-weight","normal").text(fields[x]['colname']))
                .append($('<td>').css("color","#6d6d6d").css("font-weight","bold").text(accountarray[i]["other"][x])));
        }
    }
    if(file_enabled==1){
        if(accountarray[i]["fname"]!='') 
            table.append($('<tr>')
                .append($('<td>').css("color","#66ccff").css("font-weight","normal").text('File'))
                .append($('<td>').css("color","#0000ff").css("font-weight","bold")
                    .append($('<a>')
                        .attr('title',"Download File").text(accountarray[i]["fname"])
                        .on('click',{"index":accountarray[i]["index"]},function(event){downloadf(event.data.index);}) 
                        )
                    .append('&nbsp;&nbsp;&nbsp;')
                    .append($('<a>').attr('title',"Upload file")
                        .on('click',{"index":accountarray[i]["index"]},function(event){showuploadfiledlg(event.data.index);}) 
                        .append($('<span>').attr('class',"glyphicon glyphicon-arrow-up")))));
        else table.append($('<tr>')
                    .append($('<td>')
                        .css("color","#66ccff").css("font-weight","normal")
                        .text('File'))
                    .append($('<td>')
                        .css("color","#0000ff").css("font-weight","bold")
                        .text('None').append('&nbsp;&nbsp;&nbsp;')
                        .append($('<a>').attr('title',"Upload file")
                            .on('click',{"index":accountarray[i]["index"]},function(event){showuploadfiledlg(event.data.index);}) 
                            .append($('<span>')
                                        .attr('class',"glyphicon glyphicon-arrow-up"))))); 
    }
    s.append(table);
    if ("_system_passwordLastChangeTime" in accountarray[i]["other"]) {
        s.append('<br />').append($('<p>').addClass('textred').text('Password last changed at '+timeConverter(accountarray[i]["other"]["_system_passwordLastChangeTime"])));
    }
    callPlugins("showDetails",{"account":accountarray[i], "out":s});
    $("#showdetails").modal("show");
}
function timeConverter(utctime){
    if(utctime==0) return 'unknown time';
    var a = new Date(utctime * 1000);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; 
    var year = String(a.getFullYear());
    var month = months[a.getMonth()];
    var date = String(a.getDate());
    var hour = String(a.getHours());
    var min = String(a.getMinutes());
    var sec = String(a.getSeconds());
    if(hour.length==1) hour = '0'+hour;
    if(min.length==1) min = '0'+min;
    if(sec.length==1) sec = '0'+sec;
    var time = month + ' '+date + ', ' + year + ' ' + hour + ':' + min + ':' + sec ;
    return time;
}
