var JSsalt;
var PWsalt;
var session_token;
var usepin;
var randomLoginStamp;
var default_letter_used;
function isEmail(aEmail) {
    var bValidate = RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(aEmail);
    if (bValidate) {
        return true;
    }
    else return false;
}
function dataReady(data){
    JSsalt = data["global_salt_1"]; 
    default_letter_used = data["default_letter_used"];
    if (data["allowSignup"]) {
        $("#signupform").show();
        $("#message").hide();
    }
    $("#user").focus();
    $("#chk").click(function(){ 
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){
            alert("The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)"); 
            return;
        }
        if (!isEmail($("#email").val())){
            alert("This is not a valid Email"); 
            return;
        }
        if ($("#user").val().length<5) {
            alert("Length of username should be at least 5!"); 
            return;
        }
        $("#chk").attr("disabled", true);
        $("#chk").attr("value", "Wait");
        function process(){
            var login_sig=String(pbkdf2_enc(reducedinfo($("#pwd").val(),default_letter_used),JSsalt,500));
            login_sig=String(pbkdf2_enc(login_sig,JSsalt,500));
            $.post("rest/reg.php",{email:$("#email").val(), pwd:String(CryptoJS.SHA512(login_sig+$("#user").val())),  user: $("#user").val()},function(msg){ 
                if(msg==0){
                    alert("User name already occupied, please choose another user name.");
                }
                else if(msg==1){
                    alert("This E-mail has already been used.");
                }
                else if(msg==5){
                    alert("Invalid E-mail address.");
                    window.location.href="index.php";
                }
                else if(msg==9){
                    alert("Successfully signup, now please sign in!");
                    window.location.href="index.php";
                }
                else if(msg=="Method not allowed") {
                    alert("Signup is not allowed.");
                }
                else{
                    alert("There're some errors, please retry");
                }
                $("#chk").attr("value", "Submit");
                $("#chk").attr("disabled", false);
            }); 
        }
        setTimeout(process,50);
    }); 
}
$(function(){
    $.post("rest/info.php",{},function(msg){dataReady(msg);});
}); 
