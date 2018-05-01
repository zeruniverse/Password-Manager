//Plugin for a copy to clipboard button
// only works in chrome version >=66
registerPlugin("drawAccount",function(data){
    var account = data["account"];
    var row = data["row"];
    row.find(".namecell .cellOptionButton:last").before($('<a>')
        .attr('title',"Open")
        .attr('class','cellOptionButton')
        .append($('<span></span>')
            .attr('class','glyphicon glyphicon-copy'))
        .click(function() {
            account.getPassword()
                .then(function(password) {
                    return navigator.clipboard.writeText(password)
                })
                .then(function() {
                    showMessage('success', 'Your password is now available in the clipboard.');
                })
                .catch(function() {
                    showMessage('warning', 'Could not write to clipboard');
                });
        })
    );
});