//Plugin for a copy to clipboard button
// only works in chrome version >=66
registerPlugin("drawAccount", async function(data){
    if(!navigator.clipboard) {
        return;
    }
    var account = data["account"];
    var row = data["row"];
    // Put password generation here as clipboard API can only be called at user interaction time
    // getPassword().then(navigator.clipboard.writeText()) will fail on Safari.
    var password = await account.getPassword();
    row.find(".passcell>span").after($('<a>')
        .attr('title',"Copy password to clipboard")
        .attr('class','cellOptionButton')
        .append($('<span></span>')
            .attr('class','glyphicon glyphicon-copy'))
        .click(function() {return navigator.clipboard.writeText(password)
                .then(function() {
                    showMessage('success', 'Your password is now available in the clipboard.');
                })
                .catch(function() {
                    showMessage('warning', 'Could not write to clipboard');
                });
        })
    );
});
