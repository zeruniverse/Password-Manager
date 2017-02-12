//Plugin for a link button

function addHttps(url){
    if (!/^(f|ht)tps?:\/\//i.test(url)) {
        url = "https://" + url;
    }
    return url;
}
registerPlugin("drawAccount",function(data){
    var account = data["account"];
    if (("url" in account["other"]) && (account["other"]["url"] != "")) {
        var row = data["row"];
        row.find(".namecell .cellOptionButton:last").before($('<a>')
            .attr('title',"Open")
            .attr('class','cellOptionButton')
            .attr('href',addHttps(account["other"]["url"]) 
            .append($('<span></span>')
                .attr('class','glyphicon glyphicon-globe')))
            );
    }
});
