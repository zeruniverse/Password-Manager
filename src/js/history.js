var usr = null;
$.ajaxPrefilter(function(options, originalOptions, jqXHR){
    if (options.type.toLowerCase() === "post") {
        options.data = options.data || "";
        options.data += options.data?"&":"";
        options.data += "session_token=" + localStorage.session_token;
    }
});
$(document).ready(function(){
    $.post("rest/history.php", function(msg){dataReady(msg);});
});
function dataReady(data){
    if (data["status"] != "success"){
        window.location = "./";
    }
    for (var kpin in data["pins"]){
        pin = data["pins"][kpin];
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
    for (var kip in data["ips"]){
        var ip = data["ips"][kip];
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
       $(this).html(parser.getBrowser().name+' '+parser.getBrowser().version+'; '+parser.getOS().name+' '+parser.getOS().version+'; '+parser.getDevice().model+' '+parser.getCPU().architecture);
    });
    $( ".timestampcell" ).each(function(){
       nowtime=timeConverter($(this).attr('atttimestamp'));
       $(this).html(nowtime);
    });
    $("#placeholder").hide();
	$("#loginhistorytable").DataTable({ordering:false, searching:false});
    $("#maindiv").show();
}
function unsetpin(devicex){
    $.post("rest/deletepin.php",{user:usr,device:devicex},function(msg){location.reload(true);});
}
