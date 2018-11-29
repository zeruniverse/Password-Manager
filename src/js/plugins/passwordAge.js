//Plugin that shows the password age on the edit dialog

registerPlugin("editAccountDialog",function(data){
    var account = data["account"];
    if ("_system_passwordLastChangeTime" in account["other"]) {
        $("#edititempasswordlastchanged").text(timeConverter(account["other"]["_system_passwordLastChangeTime"]));
    }
    else
        $("#edititempasswordlastchanged").empty();
});
registerPlugin("showDetails", function(data){
    let account = data["account"];
    if ("_system_passwordLastChangeTime" in account.availableOthers) {
        data["out"].append('<br />').append($('<p>').addClass('textred').text('Password last changed at ' + timeConverter(account.getOther("_system_passwordLastChangeTime"))));
    }
});
registerPlugin("layoutReady",function(data){
    $("label[for='edititeminputpw']").after($("<span>")
            .attr("class","small editOnly")
            .text(" last changed on ")
            .append($("<span>")
                .attr("id","edititempasswordlastchanged")
                .attr("class","textred")));
});
registerPlugin("updateAccountPreSend", function(data){
    if (data["newPassword"] != "") {
        data["account"].setOther("_system_passwordLastChangeTime", Math.floor(Date.now() / 1000));
    }
});
registerPlugin("addAccountPreSend", function(data){
    data["account"].setOther("_system_passwordLastChangeTime", Math.floor(Date.now() / 1000));
});
