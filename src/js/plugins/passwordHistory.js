//Plugin that stores the password history for this account

function passwordHistoryDetailsClicked(event) {
    $("#passwordHistoryDetails").empty();
    var list = $("<ol></ol>");
    for (let item of event.data.history) {
        let text = "";
        if ("setTime" in item) {
            text += "Until " + timeConverter(item["setTime"]) + ": ";
        }
        text += item["password"];
        list.append($('<li></li>').text(text));
    }
    $("#passwordHistoryDetails").append(list);
}

registerPlugin("showDetails", function(data){
    let account = data["account"];
    if ("_passwordHistory" in account.other) {
        data["out"].append('<br />').append($('<p>').attr('id','passwordHistoryDetails').append($('<a>').text('show password history').on('click', {"history": account.other["_passwordHistory"]}, passwordHistoryDetailsClicked)));
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

        // Only keep last 10 history
        if(account.other["_passwordHistory"].length > 10)
            account.setOther("_passwordHistory", account.other["_passwordHistory"].slice(-10));
    }
});
