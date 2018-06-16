var backend;
var has_file = 0;
function download(filename, text) {
    var blob = new Blob([text], {type: "text/plain;charset=utf-8"});
    saveAs(blob, filename);
}
//ToDo: files
function export_raw() {
    if(!confirm("Confirm: This function is used ONLY to TRANSFER your password to another password manager! DON'T BACK UP this version, it's DANGEROUS!")) return;
    if(!confirm("You agree you will delete the generated content IMMEDIATELY after you finish transferring your passwords")) return;

    return backend.exportRaw()
        .then(function(data) {
            download("raw_pass.raw", data);
        })
        .catch(function(msg){
            showMessage("warning", msg);
        });
}
function export_csv() {
    if(!confirm('CSV file contains all your information in plain text format. It\'s dangerous to keep it as a backup. Only use it for transferring your data. Delete it immediately after you\'ve done. Please note the encoding for the csv file is UTF-8. You might need to specify this encoding in order to open this CSV properly in some software that uses ANSI as default encoding such as Microsoft Office.')) 
        return;
    return backend.exportCSV()
        .then(function(data) {
            saveAs(data, "export.csv");
        })
        .catch(function(msg){
            showMessage("warning", msg);
        });
}
function readfile() {
    if (window.FileReader) {
        // FileReader are supported.
        var reader = new FileReader();
        var a = $("#backupc")[0].files;
        if (a && a[0]) {
            reader.onload = function (e) {
                var txt = e.target.result;
                recover(txt);
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
function downloada(x) {
    var data = fdata_array[x];
    var typedata = data.substring(5, data.search(";"));
    data = data.substring(data.search(",") + 1);
    saveAs(base64toBlob(data, typedata), fname_array[x]);
}
function recover(data) {
    if($("#pwd").val() == '') {
        alert("EMPTY PASSWORD IS NOT ALLOWED");
        return;
    }
    $("#recover_result").hide();
    $("#chk").attr("disabled", true);
    $("#chk").attr("value", "Processing...");
    $("#raw_button").hide();
    $("#csv_button").hide();
    let password = $("#pwd").val();
    backend = new RecoveryBackend();
    backend.parseBackup(data, password)
        .then(function(accounts){
            var has_file = 0;
            var rows = [$('<tr><th>Account</th><th>Password</th><th>Other Info</th></tr>')];
            if(has_file == 1) rows = [$('<tr><th>Account</th><th>Password</th><th>Other Info</th><th>Files</th></tr>')];
            for(let account of accounts) {
                var row = $('<tr></tr>')
                    .append($('<td></td>').text(account.accountName))
                    .append($('<td></td>').attr('id', 'account_'+ account.index))
                    .append($('<td></td>').text(account.getOtherJSON()));
                account.getPassword()
                    .then(function(password) {
                        $("#account_" + account.index).text(password);
                    });
                if(has_file == 1) {
                    if (x in fname_array) {
                        row.append($('<td></td>')
                            .append($('<a></a>').on('click', {x:x}, function(e) {downloada(e.data.x);}).text(fname_array[x])));
                    } 
                    else {
                        row.append($('<td></td>'));
                    }
                }
                rows.push(row);
            }
            $("#rtable").empty();
            $("#rtable").append(rows);
            $("#recover_result").show();
            $("#raw_button").show();
            $("#csv_button").show();
        })
        .catch(function(msg){
            showMessage("warning", msg);
        })
        .then(function(){
            $("#chk").removeAttr("disabled");
            $("#chk").attr("value", "RECOVER IT!");
        });
}
$(function() {
    $("#chk").on('click', function(e) {readfile();});
	$("#raw_button").on('click', function(e) {export_raw();});
	$("#csv_button").on('click', function(e) {export_csv();});
});
