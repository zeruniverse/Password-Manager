const thisIsThePasswordManager = "d8180864-4596-43a0-9701-99840e5c4259";
//everything is going to be loaded later
var seenLoginInformation = false;
var backend;
var visibleAccounts;

function eventLogout(data) {
    var reason = data["reason"];
    reason = reason || "";
    if (reason != "")
        reason = "?reason=" + encodeURIComponent(reason);
    window.location.href = "./logout.php" + reason;
}
var datatablestatus=null;
var fileid=-1;
var preDrawCallback = function( api, settings ) {};
var preShowPreparation = function (accounts){ return accounts; };// if you change the array make a copy before sorting! So indexes stay the same in the original array
function import_add_acc(acc, pass, other, file) {
    file = (typeof file !== 'undefined') ? file : null;
    if(acc==''||pass=='') {
        showMessage('warning', "one of account or password empty! will continue to process other accounts, check back after this finished", true);
        return;
    }
    if(file != null && file.name == ""){
        showMessage('warning', "Filename empty! will continue to process other accounts, check back after this finished", true);
        return;
    }
    return backend.addAccount(acc, pass, other)
        .then(function(msg) {
            if (file) {
                return backend.uploadFile(msg["nid"], file["name"], file["data"]);
            }
            return msg;
        })
        .catch(function(msg) {
            showMessage('warning',"Fail to add " + acc + " (or corresponding file), please try again manually later.", true);
        });
}
function importOnSuccess() {
    showMessage('success', 'IMPORT FINISHED!');
    $('#import').modal('hide');
    $("#importbtn").attr("disabled", false);
    $("#importbtn").text("Submit");
    $("#importc").attr("disabled", false);
    reloadAccounts();
}
function import_raw(json){
    json = JSON.parse(sanitize_json(json));
    if(json.status!="RAW_OK") {
        showMessage("warning", "INVALID RAW FILE", true);
        return;
    }
    backend.extendedTimeout();
    var promises = [];
    for(let x in json.data){
        var other = JSON.parse(sanitize_json(json.data[x].other));
        if(typeof json.data[x].fname != 'undefined'){
            promises.push(import_add_acc(json.data[x].account, json.data[x].password, other, {name: json.data[x].fname, data: json.data[x].filedata}));
        }
        else {
            promises.push(import_add_acc(json.data[x].account, json.data[x].password, other));
        }
    }
    Promise.all(promises)
        .then(importOnSuccess);
}
function import_csv(csv){
    var accarray = $.csv.toObjects(csv);
    backend.extendedTimeout();
    var promises = [];
    for (var x in accarray) {
        var acc = accarray[x]["name"];
        var pass = accarray[x]["password"];
        var other = {};
        for (var key in accarray[x]){
            if (key in backend.fields){
                other[key] = accarray[x][key];
            }
        }
        promises.push(import_add_acc(acc, pass, other));
    }
    Promise.all(promises)
        .then(importOnSuccess);
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
            showMessage(loginMsgType, 'Your last login was on ' + timeConverter(lastLogin) + '. ' + failedMsg + ' Click for more information.')
                .on('click', function(event){
                    $(this).alert('close');
                    window.location.href="./history.php";
                });
        }
        seenLoginInformation = true;
    }
}
//alles in backend klasse machen
async function dataReady(){

    $("#fileincludeckbp").toggle(backend.fileEnabled);
    $("#changefieldsnav").toggle(backend.allowFieldChange);

    showLastLoginInformation(backend.loginInformation["failedCount"], backend.loginInformation["lastLogin"]);

    await initFields(backend.fields, backend.fields_key);

    datatablestatus = await $("#pwdlist").DataTable(
        {ordering:false, info:true,autoWidth:false, "deferRender": true,
         drawCallback: function(settings) { preDrawCallback( this.api(), settings);},
         "lengthMenu": [ [10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"] ] });

    callPlugins("fieldsReady", {"fields":backend.fields, "accounts":backend.accounts});
    showTable(backend.accounts);
}
function initFields(fields, fields_order) {
    $("textarea#fieldsz").val(JSON.stringify(fields));
    for (var x of fields_order) {
        var header = $('<th>')
                        .attr('class', x + 'cell ' + fields[x]["cls"] + ' field')
                        .text(fields[x]["colname"]);
        var forms = {};
        var val = "edit";
        var input;
        var inputtype = "text";
        if ("type" in fields[x])
            inputtype = fields[x]["type"];
        if (inputtype == "textarea")
            input = $('<textarea>');
        else
            input = $('<input>').attr('type', inputtype);
        input.attr('class', 'form-control')
            .attr('id', val + 'iteminput' + x)
            .attr('placeholder', fields[x]["hint"]);
        var form = $('<div>').attr('class', 'form-group field')
            .append($('<label>')
                .attr('for', val + 'iteminput' + x)
                .attr('class', 'control-label').text(fields[x]["colname"]))
            .append(input);
        forms[val] = form;
        $("#pwdlist > thead > tr:first").append(header);
        $("#edit").find("form").append(forms["edit"]);
        callPlugins("readField", {"field":fields[x]});
    }
}
// accounts as parameter to have the possibility to only show a subset i.e. for pagination
function showTable(accounts) {
    accounts = preShowPreparation(accounts);
    visibleAccounts = accounts;
    var asterisk = $('<span>').attr('class', 'glyphicon glyphicon-asterisk');
    var pwdLink = $('<a>').attr('title', 'Click to see')
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk.clone())
            .append(asterisk);
    for(var index in accounts) {
        var cols = [];
        cols.push($("<td>")
            .attr('class', 'namecell')
            .append($("<span>")
                .attr('class', 'accountname')
                .data('id', accounts[index].index)
                .text(accounts[index].accountName))
            .append($('<a>')
                .attr('title', "Edit")
                .attr('class', 'cellOptionButton')
                .on('click', {"index":accounts[index].index}, function(event){edit(event.data.index);})
                .append($('<span></span>')
                    .attr('class', 'glyphicon glyphicon-wrench')))
            .append($('<a>')
                .attr('title', 'Details')
                .attr('class', 'cellOptionButton')
                .on('click', {"index":accounts[index]["index"]}, function(event){showdetail(event.data.index);})
                .append($('<span class="glyphicon glyphicon-eye-open"></span>')))
        );
        // 密码列：显示/隐藏切换 + 常驻复制按钮
        var id = accounts[index].index;
        var passCell = $('<td>').attr('class', 'passcell');
        // 显示/隐藏区域
        var pwdElem = $('<span>').attr('passid', id).attr('id', id)
            .append(pwdLink.clone()
                .on('click', {"index": id}, function(event){ clicktoshow(event.data.index); })
            );
        passCell.append(pwdElem);
        // 常驻复制按钮
        var copyBtn = $('<a>')
            .attr('title', 'Copy password to clipboard')
            .attr('class', 'cellOptionButton copytoClipboard')
            .append($('<span>').attr('class','glyphicon glyphicon-copy'))
            .on('click', {"index": id}, function(event){
                try {
                    // 刷新会话超时
                    backend.resetTimeout();
                    var idx = event.data.index;
                    var $span = $('#' + idx);
                    // 如果已解密，则直接复制文本
                    if ($span.find('.pwdshowbox').length > 0) {
                        var pwd = $span.find('.pwdshowbox').text();
                        navigator.clipboard.writeText(pwd)
                            .then(function(){ showMessage('success','Your password is now available in the clipboard.'); })
                            .catch(function(){ showMessage('warning','Could not write to clipboard'); });
                    }
                    // 隐藏状态下使用 ClipboardItem 同步写入以兼容 Safari
                    else if (window.ClipboardItem && navigator.clipboard && navigator.clipboard.write) {
                        try {
                            var blobPromise = backend.accounts[idx].getPassword()
                                .then(function(p){ 
                                    return new Blob([p], { type: 'text/plain' }); 
                                })
                                .catch(function(err){ 
                                    console.error('getPassword failed:', err);
                                    showMessage('warning','Failed to decrypt password');
                                    // 返回空blob防止ClipboardItem出错
                                    return new Blob([''], { type: 'text/plain' });
                                });
                            var item = new ClipboardItem({ 'text/plain': blobPromise });
                            navigator.clipboard.write([item])
                                .then(function(){ showMessage('success','Your password is now available in the clipboard.'); })
                                .catch(function(err){ 
                                    console.error('Clipboard write failed:', err);
                                    showMessage('warning','Could not write to clipboard'); 
                                });
                        } catch (err) {
                            console.error('ClipboardItem creation failed:', err);
                            showMessage('warning','Clipboard operation failed');
                        }
                    }
                    // 其他平台回退到异步写入
                    else {
                        backend.accounts[idx].getPassword()
                            .then(function(pwd){
                                navigator.clipboard.writeText(pwd)
                                    .then(function(){ showMessage('success','Your password is now available in the clipboard.'); })
                                    .catch(function(){ showMessage('warning','Could not write to clipboard'); });
                            })
                            .catch(function(){ showMessage('warning','Oops, some error occurs!'); });
                    }
                } catch (globalErr) {
                    console.error('Copy button handler failed:', globalErr);
                    showMessage('warning','Copy operation failed');
                }
            });
        passCell.append(copyBtn);
        cols.push(passCell);
        // fill in other
        let fields = backend.fields;
        let fields_key = backend.fields_key;
        for (var x of fields_key) {
            var value="";
            if (x in accounts[index]["other"])
                value = accounts[index]["other"][x];
            var cell = $('<td>').attr('class',  x+'cell '+fields[x]["cls"])
                .append($('<span>').attr('class', 'account'+x).text(value));
            cols.push(cell);
        }
        // create row for datatable
        var row = $("<tr>").attr('class', 'datarow').data('id', accounts[index].index).append(cols);
        callPlugins("drawAccount", {"account": accounts[index], "row":row});
        datatablestatus.row.add(row);
    }

    datatablestatus.draw();

    $("#waitsign").hide();
    $("#pwdtable").show();

}
function downloadf(id){
    $("#messagewait").modal("show");
    backend.downloadFile(id)
        .then(function(file){
            saveAs(file["data"], file["name"]);
        })
        .catch(function(msg){
            showMessage('danger', 'ERROR! ' + msg, false);
        })
        .then(function(){
            $("#messagewait").modal("hide");
        });
}

function deletef(index){
    var name = backend.accounts[parseInt(index)].accountName;
    if(confirm("Are you sure you want to delete file for " + name + "? (ATTENTION: this is irreversible)"))
    {
        backend.deleteFile(index)
            .then(function(){
                showMessage('success',"delete file for " + name + " successfully");
                $("#showdetails").modal("hide");
                reloadAccounts();
            })
            .catch(function(){
                showMessage('warning',"Fail to delete file for " + name + ", please try again.", true);
                $("#showdetails").modal("hide");
                reloadAccounts();
            });
     }
}

function emptyTable() {
    datatablestatus.clear();
}
function cleanUp() {
    backend.cleanUp();
    emptyTable();
    $(".field").remove();
}
function reloadAccounts() {
    if(datatablestatus!==null) {
        cleanUp();
        datatablestatus.destroy();
    }
    backend.loadAccounts()
        .then(dataReady);
}
$(document).ready(function(){
    backend = new AccountBackend();
    backend.registerEvent("logout", eventLogout);
    reloadAccounts();

    // Define event handlers
    $("#pinloginform").on('submit',function(e){
        e.preventDefault();
        var pin = $("#pinxx").val();
        if (pin.length<4) {
            showMessage('warning', 'For security reason, PIN should be at least of length 4.', true);
            return;
        }
        backend.resetTimeout();
        backend.setPin(pin)
            .then( function() {
                showMessage('success', 'PIN set, use PIN to login next time');
                $('#pin').modal('hide');
            })
            .catch( function() {
                showMessage('warning', 'ERROR set PIN, try again later!', true);
                $('#pin').modal('hide');
            });
    });
    $("#changefieldsbtn").click(function(){
        var fields = $('#fieldsz').val();
        backend.updateFields(fields)
            .then(function(){
                showMessage('success','Successfully changed fields!');
                $('#changefields').modal('hide');
                reloadAccounts();
            })
            .catch(function(error){
                if (error == "parse") {
                    showMessage('warning', 'illegal format!', true);
                }
                else if (error == "illegalFields") {
                    showMessage('warning', 'illegal fields!', true);
                }
                else {
                    showMessage('warning', "Oops, there's some error. Try again!", true);
                }
            });
    });
    $("#editbtn").click(function(){
        // validate input
        if($("#edititeminput").val() == "") {
            showMessage('warning',"Account entry can't be empty!", true);
            return;
        }

        // lock form
        $("#editbtn").attr("disabled",true);
        $("#edititeminput").attr("readonly",true);
        $("#edititeminputpw").attr("readonly",true);
        for (let x in backend.fields)
            $("#edititeminput"+x).attr("readonly",true);

        // gather data
        var id = parseInt($("#edit").data('id'));
        var name = $("#edititeminput").val();
        var newpwd = $("#edititeminputpw").val();
        var other = {};
        for (let x in backend.fields)
            other[x] = $("#edititeminput"+x).val().trim();

        // send to backend
        var result;
        if (id == -1) {
            result = backend.addAccount(name, newpwd, other);
        }
        else {
            result = backend.updateAccount(id, name, newpwd, other);
        }
        result.then(function(){
                showMessage('success',"Account " + name + " saved!");
                $('#edit').data('id','-1');
                $('#edit').modal('hide');
                $('#edit').find('form')[0].reset();
                reloadAccounts();
            })
            .catch(function(){
                showMessage('warning',"Fail to store data for " + name + ", please try again.", true);
            })
            .then(function(){
                $("#edititeminput").attr("readonly",false);
                $("#editbtn").attr("disabled",false);
                $("#edititeminputpw").attr("readonly",false);
                for (let x in backend.fields)
                    $("#edititeminput" + x).attr("readonly",false);
            });
    });
    $("#backuppwdbtn").click(function(){
        $("#backuppwdbtn").attr('disabled',true);
        $("#backuppwdpb").attr('aria-valuenow',0);
        $("#backuppwdpb").css('width','0%');
        $("#fileincludeckb").attr('disabled',true);
        function progressbarchange(x) {
            $("#backuppwdpb").attr('aria-valuenow',x);
            $("#backuppwdpb").css('width',x+'%');
        }
        backend.backup($("#fileincludeckb").is(':checked'), progressbarchange)
            .then(function(backup) {
                progressbarchange(100);
                saveAs(backup, "backup.txt");
                $("#backuppwdbtn").attr('disabled',false);
                $("#fileincludeckb").attr('disabled',false);
            })
            .catch(function(){
                showMessage('warning',"FAIL TO GENERATE BACKUP FILE, TRY AGAIN", true);
            })
            .then(function(){
                $("#backuppwdbtn").attr('disabled',false);
                $("#fileincludeckb").attr('disabled',false);
            });
    });
    $("#editAccountShowPassword").click(function(){
        $("#editDismiss").popover('hide');
        if ($("#edititeminputpw").val() == "") {
            $("#edititeminputpw").attr("type", "text");
            var id = parseInt($("#edit").data('id'));
            $("#editAccountShowPassword > i").removeClass("glyphicon-eye-open");
            $("#editAccountShowPassword > i").addClass("glyphicon-eye-close");
            if (id > -1) {
                backend.accounts[id].getPassword()
                    .then(function(pwd) {
                        $("#edititeminputpw").val(pwd);
                    })
                    .catch(function() {
                        $("#edititeminputpw").val("Oops, some error occurs!");
                    });
            }
        }
        else {
            if ($("#edititeminputpw").attr("type") == "password") {
                $("#edititeminputpw").attr("type", "text");
            }
            else {
                $("#edititeminputpw").attr("type", "password");
            }
            $("#editAccountShowPassword > i").toggleClass("glyphicon-eye-open");
            $("#editAccountShowPassword > i").toggleClass("glyphicon-eye-close");
        }
    });
    $("#delbtn").click(function(){
        delepw($("#edit").data('id'));
    });
    $("#changepw").click(function(){
        if(confirm("Your request will be processed on your browser, so it takes some time. Do not close your window or some error might happen.\nPlease note we won't have neither your old password nor your new password. \nClick OK to confirm password change request."))
        {
            if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){
                showMessage('warning',"The second password of your input doesn't match the first one. Or your password is too weak (length should be at least 7)", true);
                return;
            }
            $("#changepw").attr("disabled",true);
            $("#changepw").attr("value", "Processing...");

            //check old password
            backend.changePassword($("#oldpassword").val(), $("#pwd").val())
                .then(function(){
                    alert("Change Password Successfully! Please login with your new password again.");
                    backend.logout("Password changed, please relogin");
                })
                .catch(function(message) {
                    showMessage('warning', "Fail to change your password: " + message + " Please try again.", true);
                });
        }
    });
    $("#importbtn").click(function(){
        $("#importbtn").attr("disabled", true);
        $("#importbtn").text("Processing...");
        $("#importc").attr("disabled", true);
        function bk(){
            $("#importbtn").attr("disabled", false);
            $("#importbtn").text("Submit");
            $("#importc").attr("disabled", false);
        }
        function process(){
            if (window.FileReader) {
                // FileReader are supported.
                var reader = new FileReader();
                var a = $("#importc")[0].files;
                var t = 0;
                if (a && a[0]){
                    reader.onload = function (e) {
                        var txt = e.target.result;
                        try{
                            if(t==0) {
                                import_raw(txt);
                            }
                            else {
                                import_csv(txt);
                            }
                        }
                        catch (error) {
                            showMessage('warning','Some error occurs!', true);
                            bk();
                            reloadAccounts();
                        }
                    }
                    reader.onerror = function (e) {
                        showMessage('warning','Error reading file!', true);
                        bk();
                    }
                    var extension = a[0].name.split('.').pop().toLowerCase();
                    if(extension=='csv') {
                        t=1;
                    }
                    reader.readAsText(a[0]);
                }
                else {
                    showMessage('warning','NO FILE SELECTED', true);
                    bk();
                }
            }
            else {
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
                var a = $("#uploadf")[0].files;
                var fname = '';
                if (a && a[0]){
                    reader.onload = function (e) {
                        var data = e.target.result;
                        backend.uploadFile(fileid, fname, data)
                            .then(function(msg){
                                showMessage('success','File uploaded!', false);
                            })
                            .catch(function(msg) {
                                showMessage('danger','ERROR! Try again!', false);
                            })
                            .then(function(){
                                $('#uploadfiledlg').modal("hide");
                                $("#showdetails").modal("hide");
                                reloadAccounts();
                            });
                    }
                    reader.onerror = function (e) {
                        showMessage('warning','Error reading file!', true);
                        bk();
                    }
                    fname = a[0].name;
                    if(fname == ''){
                        showMessage('warning','File selected doesn\'t have a name!', true);
                        bk();
                        return;
                    }
                    reader.readAsDataURL(a[0]);
                }
                else {
                    showMessage('warning','NO FILE SELECTED', true);
                    bk();
                }
            }
            else {
                showMessage('warning','FileReader are not supported in this browser.', true);
            }
        }
        setTimeout(process,10);
    });

    $('#edit').on('shown.bs.modal', function () {
        $(this).find('form')[0].reset();
        backend.resetTimeout();
        var id = parseInt($(this).data('id'));
        $(this).toggleClass('editOnly', id > -1);
        $(this).toggleClass('addOnly', id == -1);
        if (id > -1) {
            $("#edititeminputpw").attr('placeholder',"Hidden");
        }
        else {
            $("#edititeminputpw").attr('placeholder',"Leave blank to generate one");
        }
        $("#edititeminputpw").val('');
        $("#edititeminputpw").attr('type', "password");
        $("#editAccountShowPassword > i").removeClass("glyphicon-eye-close");
        $("#editAccountShowPassword > i").addClass("glyphicon-eye-open");
        // handle existing accounts
        if (id > -1) {
            $("#edititeminput").val(backend.accounts[id].accountName);
            for (let x in backend.fields){
                $("#edititeminput"+x).val(backend.accounts[id].getOther(x));
            }
            callPlugins("editAccountDialog",{"account": backend.accounts[id]});
        }
    });
    $('#edit').on('hide.bs.modal', function() {
        $(".popover").popover('hide');
        $(this).data('id', -1);
    });
    $('#editPasswordInput').on('click', function() {
        $('#edititeminputpw').val(backend.encryptionWrapper.generatePassphrase(backend.default_length));
        $('#editAccountShowPassword').removeClass('collapse');
        $('#editDismiss').popover({
            'placement':'bottom',
            'title':'',
            'container':'body',
            'content':'Click here to get your old password back.',
            'trigger':'manual' })
            .on('shown.bs.popover', function(){
                $('.popover').on('click',function(){
                    $("#editDismiss").popover("hide");
                });
                $('.popover-title').hide();
            })
            .popover('show');
    });
    $('#pinBtnDel').on('click',function(){
        backend.delPin()
            .then(function() {
                showMessage('info', 'PIN deleted, use username/password to login next time', true);
            })
            .catch(function(msg) {
                showMessage('warning', 'Failed to delete PIN: '+ msg);
            })
            .then(function() {
                $('#pin').modal('hide');
            });
    });
    $('#navBtnLogout').on('click',function(){ backend.logout(); });
    $('#navBtnUntrust').on('click',function(){ backend.untrustAndLogout(); });
    $('#navBtnExport').on('click',function(){ exportcsv(); });
    $('#navBtnActivity').on('click',function(){
        window.location.href="./history.php";
    });
    callPlugins("layoutReady");
});
function edit(row){
    var id = row; //row.find("")
    $("#edit").data("id", id);
    $("#edit").modal("show");
}
function clicktoshow(id){
    backend.resetTimeout();
    id = parseInt(id);
    backend.accounts[id].getPassword()
        .then(function(pwd){
            // 清空并插入明文显示区域
            $("#"+id).empty()
                .append($('<span class="pwdshowbox passwordText"></span>'))
                // 隐藏按钮
                .after($('<a title="Hide" class="cellOptionButton hidePassword"></a>')
                    .on('click',{"index":id},function(event){ clicktohide(event.data.index); })
                    .append($('<span class="glyphicon glyphicon-eye-close"></span>')));
            // 填充解密后的密码
            $("#"+id+" > .pwdshowbox").text(pwd);
        })
        .catch(function(){
            $("#"+id).text("Oops, some error occurs!");
        });
}
function clicktohide(id){
    backend.resetTimeout();
    $("#" + id).empty().append($('<a title="Click to see"></a>')
                        .on('click', {"index":id}, function(event){ clicktoshow(event.data.index); })
                        .append('<span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span><span class="glyphicon glyphicon-asterisk"></span>') );
    // 移除隐藏按钮
    $("#" + id).parent().find(".hidePassword").remove();
}
function showuploadfiledlg(id){
    $("#uploadfiledlg").modal("hide");
    $("#uploadfitemlab1").text(backend.accounts[id].accountName);
    $("#uploadfitemlab2").text(backend.accounts[id].accountName);
    $("#uploadfilebtn").attr("disabled", false);
    $("#uploadfilebtn").text("Submit");
    $("#uploadf").attr("disabled", false);
    fileid = id;
    $("#uploadfiledlg").modal("show");
}
function delepw(index)
{
    var name = backend.accounts[parseInt(index)].accountName;
    if(confirm("Are you sure you want to delete password for " + name + "? (ATTENTION: this is irreversible)"))
    {
        backend.deleteAccount(index)
            .then(function(){
                showMessage('success',"delete " + name + " successfully");
                $('#edit').modal('hide');
                reloadAccounts();
            })
            .catch(function(){
                showMessage('warning',"Fail to delete " + name + ", please try again.", true);
            });
     }
}
function exportcsv()
{
    alert('To discourage users from exporting CSV, we have moved this feature to the RECOVERY page. Please backup the passwords first and go to recovery page (link can be found at the login page).');
}
function showdetail(index){
    var i = parseInt(index);
    var account = backend.accounts[i];
    var s = $('#details');
    backend.resetTimeout();
    s.html('');
    s.append($('<b>').text(account.accountName))
     .append($('<br/>')).append($('<br/>'));
    var table = $('<table>').css('width',"100%").css('color',"#ff0000")
            .append($('<colgroup><col width="90"><col width="auto"></colgroup>'));
    for (let x of backend.fields_key){
        if(x in account.other && String(account.getOther(x)).length > 0){
            table.append($('<tr>')
                .attr("id","detailsTableOther" + x)
                .append($('<td>').css("color","#afafaf").css("font-weight","normal").text(backend.fields[x]['colname']))
                .append($('<td>').css("color","#6d6d6d").css("font-weight","bold").css("white-space", "pre").text(account.getOther(x))));
        }
    }
    if(backend.fileEnabled){
        if(account.file != null)
            table.append($('<tr>')
                .append($('<td>').css("color","#66ccff").css("font-weight","normal").text('File'))
                .append($('<td>').css("color","#0000ff").css("font-weight","bold")
                    .append($('<a>')
                        .attr('title',"Download File").text(account.file["name"])
                        .on('click',{"index":account.index},function(event){downloadf(event.data.index);})
                        )
                    .append('&nbsp;&nbsp;&nbsp;')
                    .append($('<a>').attr('title',"Upload file")
                        .on('click',{"index":account.index},function(event){showuploadfiledlg(event.data.index);})
                        .append($('<span>').attr('class',"glyphicon glyphicon-arrow-up"))
                        )
                    .append('&nbsp;&nbsp;&nbsp;')
                    .append($('<a>').attr('title',"Delete file")
                        .on('click',{"index":account.index},function(event){deletef(event.data.index);})
                        .append($('<span>').attr('class',"glyphicon glyphicon-remove"))
                        )
                    ));
        else table.append($('<tr>')
                    .append($('<td>')
                        .css("color","#66ccff").css("font-weight","normal")
                        .text('File'))
                    .append($('<td>')
                        .css("color","#0000ff").css("font-weight","bold")
                        .text('None').append('&nbsp;&nbsp;&nbsp;')
                        .append($('<a>').attr('title',"Upload file")
                            .on('click',{"index":account.index},function(event){showuploadfiledlg(event.data.index);})
                            .append($('<span>')
                                        .attr('class',"glyphicon glyphicon-arrow-up")))));
    }
    s.append(table);
    callPlugins("showDetails",{"account":account, "out":s});
    $("#showdetails").modal("show");
}
