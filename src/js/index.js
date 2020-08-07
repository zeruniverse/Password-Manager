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
async function isCrypto(){
    try{
        if((await PBKDF2_SHA512('123', '456', 3)).substring(0, 5)!="e2d69") {
            return false;
        }
        var enc = await AESCBC256Encrypt('123', '456');
        if((await AESCBC256Decrypt(enc, '456')) != '123') return false;
    } catch(e) {
        return false;
    }
    return true;
}

isCrypto()
    .then(function(is_crypto){
        if(!isAllHTML5Supports() || !is_crypto) {
            window.location.href="./sorry_for_old_browser_update_hint.html";
        }
    });

$(function(){
    $("#signup").on('click',function(e){window.location.href="signup.php";});
    $("#recover").on('click',function(e){window.location.href="recovery.php";});
    $("#delpin").on('click',function(e){backend.delPin();});
    $("#usepin").on("hidden.bs.modal", function () {
        $("#user").focus();
    });
    $('#loginPasswordToggle').on('click', function(e){
        if ($('#pwd').attr('type') == 'text') {
            $('#pwd').attr('type', 'password');
        }
        else {
            $('#pwd').attr('type', 'text');
        }
        $('#loginPasswordToggleIcon').toggleClass('glyphicon-eye-open');
        $('#loginPasswordToggleIcon').toggleClass('glyphicon-eye-close');
    });
    $("#pinloginform").on('submit',function(e){
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
                }
            });
    });
    $("#loginform").on('submit', function(e) {
        e.preventDefault();
        $("#chk").attr("disabled", true);
        $("#chk").attr("value", "Wait");
        $(".errorhint").hide();
        backend.doLogin($("#user").val(), $("#pwd").val(), $("#emailcode").val())
            .then(function(){
                window.location.href="./password.php";
            })
            .catch(function(msg){
                // Show Email verification box if error message is EmailVerify
                if(msg.indexOf('sent an email to you') != -1) $('#email-div').show();
                showMessage("warning", msg);
                $("#chk").attr("value", "Login");
                $("#chk").attr("disabled", false);
            });
    });

    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results) {
            return decodeURIComponent(results[1].replace(/\+/g, '%20'));
        }
        return null;
    }
    if($.urlParam("reason")) {
        showMessage("warning", $.urlParam("reason"));
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
