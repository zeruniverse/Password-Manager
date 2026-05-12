var usr = null;
var backend;
$(document).ready(function(){
    backend = new HistoryBackend();
    backend.getHistory()
        .then(dataReady)
        .catch(function(){
            window.location = "./";
        });
});
function dataReady(data){
    for (var pin of data["pins"]){
        $("#pinTable")
            .append($("<tr></tr>")
                    .append($("<td></td>")
                        .addClass("uacell")
                        .text(pin["ua"]))
                    .append($("<td></td>")
                        .addClass("timestampcell")
                        .attr('atttimestamp',pin["ctime"]))
                    .append($("<td></td>")
                        .append($("<a></a>")
                            .text("Untrust this device")
                            .on('click',{"did":pin["did"]},function(event){
                                unsetpin(event.data.did);
                            }))));
    }
    for (var ip of data["ips"]){
        var row = $('<tr></tr>');
        if (ip["outcome"])
            row.addClass("textred");
        row.append($("<td></td>")
                .addClass("uacell")
                .text(ip["ua"]))
            .append($("<td></td>")
                    .text(ip["ip"]))
            .append($("<td></td>")
                    .addClass("timestampcell")
                    .attr("atttimestamp",ip["ctime"]));
        $("#loginhistorytable > tbody")
            .append(row);
    }
    usr = data["usr"];
    var parser = new UAParser();
    var uastring;
    var nowtime;
    $( ".uacell" ).each(function() {
       uastring=$(this).html();
       parser.setUA(uastring);
       $(this).text(parser.getBrowser().name+' '+parser.getBrowser().version+'; '+parser.getOS().name+' '+parser.getOS().version+'; '+parser.getDevice().model+' '+parser.getCPU().architecture);
    });
    $( ".timestampcell" ).each(function(){
       nowtime=timeConverter($(this).attr('atttimestamp'));
       $(this).text(nowtime);
    });
    $("#placeholder").hide();
    $("#loginhistorytable").DataTable({ordering:false, searching:false});
    $("#maindiv").show();
}
function unsetpin(devicex){
    backend.unSetPin(devicex)
        .then(function(){
            location.reload(true);
        })
        .catch(function(msg){
            showMessage('warning', "Failed to remove Pin: " + msg);
        });
}
