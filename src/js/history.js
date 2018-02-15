var usr = null;
function timeConverter(utctime){
  var a = new Date(utctime * 1000);
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; 
  var year = String(a.getFullYear());
  var month = months[a.getMonth()];
  var date = String(a.getDate());
  var hour = String(a.getHours());
  var min = String(a.getMinutes());
  var sec = String(a.getSeconds());
  if(hour.length==1) hour = '0'+hour;
  if(min.length==1) min = '0'+min;
  if(sec.length==1) sec = '0'+sec;
  var time = month + ' '+date + ', ' + year + ' ' + hour + ':' + min + ':' + sec ;
  return time;
}
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
    $.post("rest/deletepin.php",{user:usr,device:devicex},function(msg){location.reload(true);});
}
