var backend;
function isSupportFileApi() {
    if(window.File && window.FileList && window.FileReader && window.Blob) {
        return true;
    }
    return false;
}
function isAllHTML5Supports(){
    var test = 'test';
    try {
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        sessionStorage.setItem(test, test);
        sessionStorage.removeItem(test);
    } catch(e) {
        return false;
    }
    return isSupportFileApi();

}
if(!isAllHTML5Supports()) {
    window.location.href="./sorry_for_old_browser_update_hint.html";
}
$(function(){
    $("#signup").on('click',function(e){window.location.href="signup.php";});
    $("#recover").on('click',function(e){window.location.href="recovery.php";});
    $("#delpin").on('click',function(e){delpinstore();deleteCookie('username');});
    $("#usepin").on("hidden.bs.modal", function () {
        $("#user").focus();
    });
    $("#pinloginform").on('submit',function(e){
        var pin;
        e.preventDefault();
        $("#pinerrorhint").hide();
        $("#pinlogin").attr("disabled", true);
        $("#pinlogin").val("Wait");
        backend.doPinLogin($("#pin").val())
            .then(function(){
                    window.location.href="./password.php";
                })
            .catch(function(msg){
                if (msg == "No PIN available") {
                    $("#usepin").modal("hide");
                    $("#user").focus();
                }
                else {
                    $("#pin").val('');
                    $("#pinerrorhint").show();
                    $("#pinlogin").attr("disabled", false);
                    $("#pinlogin").val("Login");
                    return;
                }
            });
    });
    $("#loginform").on('submit',function(e){ 
        e.preventDefault();
        $("#chk").attr("disabled", true);
        $("#chk").attr("value", "Wait");
        $(".errorhint").hide();
        function process(){
            var user = $("#user").val(); 
            var pwd = $("#pwd").val();

            var secretkey='';
            var confkey='';
            var login_sig=String(pbkdf2_enc(reducedinfo(pwd,default_letter_used),JSsalt,500));
            secretkey=login_sig;
            login_sig=pbkdf2_enc(login_sig,JSsalt,500);
            $.post("rest/check.php",{pwd:String(CryptoJS.SHA512(login_sig+user)),  user: user},function(msg){
                $(".errorhint").hide();
                if(msg["status"] == "success"){
                    confkey=pbkdf2_enc(String(CryptoJS.SHA512(pwd+secretkey)),JSsalt,500);
                    setCookie("username",user);
                    setpwdstore(secretkey,confkey,PWsalt);                
                    window.location.href="./password.php";
                }
                else {
                    showMessage("warning", msg["message"]);
                    $("#chk").attr("value", "Login");
                    $("#chk").attr("disabled", false);
                }
            });
        }
        setTimeout(process,50);
    }); 

    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results) {
            return decodeURIComponent(results[1].replace(/\+/g, '%20'));
        }
        return null;
    }
    if($.urlParam("reason")) {
        showMessage("warning",$.urlParam("reason"));
    }
    backend = new LogonBackend();
    backend.loadInfo()
        .then(function(data) {
            if (backend.loggedIn){
                window.location = "./password.php";
                return;
            }
            if (backend.allowSignup) {
                $("#signup").show();
            }
            $("#version").text(backend.version);
            $("#banTime").text(backend.banTime);
            if(backend.pinActive) {
                $("#usepin").modal("show");
                $("#pin").focus();
            }
            else{
                $("#user").focus();
            }
        })
        .catch(function(msg) {
            showMessage("warning", msg);
        });
}); 
