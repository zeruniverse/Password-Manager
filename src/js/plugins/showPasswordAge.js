//Plugin that shows the password age on the edit dialog

registerPlugin("editAccountDialog",function(data){
    var account = data["account"];
    if ("_system_passwordLastChangeTime" in account["other"]) {
        $("#edititempasswordlastchanged").text(timeConverter(account["other"]["_system_passwordLastChangeTime"]));
    }
    else
        $("#edititempasswordlastchanged").empty();
});
registerPlugin("layoutReady",function(data){
    $("label[for='edititeminputpw']").after($("<span>")
            .attr("class","small")
            .text(" last changed on ")
            .append($("<span>")
                .attr("id","edititempasswordlastchanged")
                .attr("class","textred")));
});
