//Plugin that stores the password history for this account

registerPlugin("showDetails", function(data){
    // just an example from password age
    let account = data["account"];
    if ("_system_passwordLastChangeTime" in account.availableOthers) {
        data["out"].append('<br />').append($('<p>').addClass('textred').text('Password last changed at ' + timeConverter(account.getOther("_system_passwordLastChangeTime"))));
    }
});
registerPlugin("updateAccountPreSend", function(data){
    if (data["newPassword"] != "") {
        var account = data["account"];
        // create passwordHistory array for account if it doesn't exist
        if (!"_passwordHistory" in account.other) {
            account.setOther("_passwordHistory", []);
        }
        var newHistoryEntry = {"password": data["oldData"]["password"]};
        if ("_system_passwordLastChangeTime" in data["oldData"]["other"]) {
            newHistoryEntry["setTime"] = data["oldData"]["other"]["_system_passwordLastChangeTime"];
        }
        account.other["_passwordHistory"].push(newHistoryEntry);
    }
});
