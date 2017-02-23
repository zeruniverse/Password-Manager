//Plugin that shows the password age on the edit dialog

registerPlugin("editAccountDialog",function(data){
    function timeConverter(utctime){
        if(utctime==0) return 'unknown time';
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
    var account = data["account"];
    $("#edititempasswordlastchanged").text(timeConverter(lasttimechangearray[account["index"]]));
});
registerPlugin("layoutReady",function(data){
    $("label[for='edititeminputpw']").after($("<span>")
            .attr("class","small")
            .text("Password was last changed on ")
            .append($("<span>")
                .attr("id","edititempasswordlastchanged")
                .attr("class","textred")));
});
