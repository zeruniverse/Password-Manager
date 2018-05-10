//Plugin for the tag
function showAllTags(accounts) {
    function gatherDistinctTags(accounts) {
        var tags = [];
        for (let x in accounts) {
            if (!("tags" in accounts[x]["other"]))
                continue;
            if (accounts[x]["other"]["tags"].length>0)
                tags = tags.concat(accounts[x]["other"]["tags"].split(',').map(function (str){return str.trim();}));
        }
        var unique = [];
        for(var i = 0; i < tags.length; i++) {
            if($.inArray(tags[i], unique) < 0) {
                unique.push(tags[i]);
            }
        }
        return unique.sort(function (a, b) { return a.toLowerCase().localeCompare(b.toLowerCase()); });
    }
    var tags = gatherDistinctTags(accounts);
    $('#tags').empty();
    for (x in tags){
        $("#tags").append($("<a>").attr('href','#').on('click',{"tag":tags[x]},function(event){
            $(this).addClass('activeTag');
            filterTags(event.data.tag);
        })
        .text(tags[x])).append(" ");
    }
    if (tags.length>0) {
        $("#tagCloud").show();
    }
}
function filterTags(tag){//replace by cleaning up and showing only accounts that fit
    emptyTable();
    if (tag == ""){
        $("#resetFilter").hide();
        $("#tags>a").removeClass('activeTag');
        showTable(backend.accounts);
        return;
    }
    function filter(account){
        if (!("tags" in account["other"]))
            return false;
        return account["other"]["tags"].split(',').map(function (item){ return item.trim(); }).indexOf(tag) > -1;
    }
    showTable(visibleAccounts.filter(filter));
    $("#resetFilter").show();
}
function enableGrouping(){
    // Global variable from main.js
    preDrawCallback = function( api, settings ) {
        var rows = api.rows( {page:'current'} ).nodes();
        var last = null;
        $(rows.to$()).each(
            function ( index, row ) {
                var dbentry = backend.accounts[$(row).data('id')];
                var firsttag = null;
                if ((! ('tags' in dbentry["other"])) || dbentry["other"]['tags'] == '')
                    firsttag = null;
                else
                    firsttag = dbentry["other"]["tags"].split(',')[0].trim();
                if (last !== firsttag) {
                    $(row).before( $('<tr>').attr('class',"group").append($('<td>').attr('colspan',"15").append($('<strong>').text(firsttag).prepend('&nbsp;&nbsp;'))));
                    last = firsttag;
                }
            });
    };
    // Global variable from main.js
    preShowPreparation=function(accounts) {
        ordering = function (a,b){
            if ((!("tags" in a["other"]))|| a["other"]["tags"]=='')
                return 1;
            if ((!("tags" in b["other"]))|| b["other"]["tags"]=='')
                return -1;
            let atags = a["other"]["tags"].toLowerCase();
            let btags = b["other"]["tags"].toLowerCase();
            if (atags < btags)
                return -1;
            if (atags > btags)
                return 1;
            return 0;
        };
        return accounts.concat().sort(ordering);
    }
    emptyTable();
    showTable(visibleAccounts);
    $('#orderTags').hide();
    $('#orderTagsDisable').show();
}
function disableGrouping(){
    preDrawCallback = function( api, settings ) {};
    preShowPreparation = function( accounts ) { return accounts; };
    emptyTable();
    showTable(visibleAccounts);
    $('#orderTags').show();
    $('#orderTagsDisable').hide();
}
registerPlugin("layoutReady",function(data){
    $("#rightHandBox").append($("<div>")
            .attr('id','tagCloud')
            .attr('class','collapse')
            .append($('<p>')
                .attr('class','lead')
                .text('Tag-Overview')
                .append($('<a>')
                    .attr('id',"orderTags")
                    .attr('name',"enable grouping")
                    .attr('class',"small")
                    .append($('<span>')
                        .attr('class',"glyphicon glyphicon-sort-by-attributes")))
                .append($('<a>')
                    .attr('id',"orderTagsDisable")
                    .attr('name',"disable grouping")
                    .attr('class',"small collapse")
                    .append($("<span>").attr('class',"glyphicon glyphicon-remove"))))
            .append($('<p>')
                .attr('class',"visible-xs small")
                .append($('<a>')
                    .attr('id',"tagsShow")
                    .append($('<span>')
                        .attr('class',"tagsShow")
                        .text('show'))
                    .append($('<span>')
                        .attr('class',"tagsShow hidden")
                        .text('hide'))
                    .append('tags')))
            .append($('<span>')
                    .attr('class', "hidden-xs")
                    .attr('id',"tags"))
            .append($('<p>')
                    .attr('class',"small collapse")
                    .attr('id',"resetFilter")
                    .append($('<a>')
                        .attr('id',"tagsFilter")
                        .text('reset filter'))))
    $('#orderTags').on('click',function(){
        enableGrouping();
    });
    $('#orderTagsDisable').on('click',function(){
        disableGrouping();
    });
    $('#tagsShow').on('click',function(){
        $('#tags').toggleClass('hidden-xs');
        $('.tagsShow').toggleClass('hidden');
    });
    $('#tagsFilter').on('click',function(){
        filterTags('');
    });
});
registerPlugin("fieldsReady", function(data){
    showAllTags(data["accounts"]);
});
