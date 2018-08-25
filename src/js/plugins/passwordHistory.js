//Plugin that stores the password history for this account

function passwordHistoryDetailsClicked(data) {
    $("#passwordHistoryDetails").text('');
    var i = 0;
    for (let item of data["history"]) {
        i+=1;
        let text = str(i) + ". ";
        if ("setTime" in item) {
            text += "Since " + item["setTime"] + ": ";
        }
        text += item["password"];

        let text = document.createTextNode(item);
        $("#passwordHistoryDetails").append(text);
    }
}

registerPlugin("showDetails", function(data){
    let account = data["account"];
    if ("_passwordHistory" in account.availableOthers) {
        data["out"].append('<br />').append($('<p>').attr('id','passwordHistoryDetails').text('show password history').on('click', passwordHistoryDetailsClicked, {"history": account.other["_passwordHistory"]}));
    }
});
registerPlugin("updateAccountPreSend", function(data){
    if (data["newPassword"] != "") {
        var account = data["account"];
        // create passwordHistory array for account if it doesn't exist
        if (!("_passwordHistory" in account.other)) {
            account.setOther("_passwordHistory", []);
        }
        var newHistoryEntry = {"password": data["oldData"]["password"]};
        if ("_system_passwordLastChangeTime" in data["oldData"]["other"]) {
            newHistoryEntry["setTime"] = data["oldData"]["other"]["_system_passwordLastChangeTime"];
        }
        account.other["_passwordHistory"].push(newHistoryEntry);
    }
});
