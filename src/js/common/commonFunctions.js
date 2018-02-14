function timeConverter(utctime){		
    if(utctime==0) 		
        return 'unknown time';		
    var a = new Date(utctime * 1000);		
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; 		
    var year = String(a.getFullYear());		
    var month = months[a.getMonth()];		
    var date = String(a.getDate());		
    var hour = String(a.getHours());		
    var min = String(a.getMinutes());		
    var sec = String(a.getSeconds());		
    if(hour.length==1) 		
        hour = '0'+hour;		
    if(min.length==1) 		
        min = '0'+min;		
    if(sec.length==1) 		
        sec = '0'+sec;		
    var time = month + ' '+date + ', ' + year + ' ' + hour + ':' + min + ':' + sec ;		
    return time;		
}
/*
 * Shows a message in the header of the page
 * type: any of "success", "info", "warning", "danger"
 * message: text
 * modal: if true shows a modal window
 */
function showMessage(type, message, modal){
    modal = (typeof modal !== 'undefined') ? modal : false;
    if (modal==false) {
        var messageDialog = $("<div>")
                    .addClass("alert")
                    .addClass("alert-"+type)
                    .addClass("collapse")
                    .append($('<a href="#" class="close" aria-label="close">&times;</a>')
                            .click(function(e){
                                messageDialog.alert('close'); 
                                e.stopImmediatePropagation()
                            }))
                    .append($('<span>').text(message));
        $("#messageContainer").append(messageDialog);
        messageDialog.fadeIn();
        if(type == "success" || type == "info"){
            messageDialog.fadeTo(6000, 500).slideUp(500, function(){ // 6000 ms
                messageDialog.alert('close');
            });
        }
        return messageDialog;
    }
    else {
        $("#messageDialogText").text(message);
        $("#messageDialogText").removeClass("alert-success alert-info alert-warning alert-danger");
        $("#messageDialogText").addClass("alert-"+type);
        $("#messageDialog").modal('show');
    }
}
function base64toBlob(base64Data, contentType) {
    contentType = contentType || '';
    var sliceSize = 1024;		
    var byteCharacters = atob(base64Data);		
    var bytesLength = byteCharacters.length;		
    var slicesCount = Math.ceil(bytesLength / sliceSize);		
    var byteArrays = new Array(slicesCount);		

    for (var sliceIndex = 0; sliceIndex < slicesCount; ++sliceIndex) {		
        var begin = sliceIndex * sliceSize;		
        var end = Math.min(begin + sliceSize, bytesLength);		

        var bytes = new Array(end - begin);		
        for (var offset = begin, i = 0 ; offset < end; ++i, ++offset) {		
            bytes[i] = byteCharacters[offset].charCodeAt(0);		
        }		
        byteArrays[sliceIndex] = new Uint8Array(bytes);		
    }		
    return new Blob(byteArrays, { type: contentType });		
}
