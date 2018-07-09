var backend;
$(function(){
    $("#chk").click(function(){ 
        $("#chk").attr("disabled", true);
        $("#chk").attr("value", "Wait");
        backend.doRegister($("#user").val(), $("#email").val(), $("#pwd").val(), $("#pwd1").val())
            .then(function(){
                alert("Successfully signup, now please sign in!");
                window.location.href="index.php?reason=Successfully+signed+up.";
            })
            .catch(function(msg){
                if (msg == "PasswordMismatch") {
                    alert("The second password you input doesn't match the first one. "); 
                }
                else if (msg == "PasswordLength") {
                    alert("Password is not long enough (minimum length is " + backend.minPasswordLength + " characters)"); 
                }
                else if (msg == "EmailInvalid") {
                    alert("This is not a valid Email"); 
                }
                else if (msg == "UserLength") {
                    alert("Length of username should be at least " + backend.minNameLength + " characters!"); 
                }
                else {
                    alert(msg["message"]);
                }
            })
            .then(function(){
                $("#chk").attr("value", "Submit");
                $("#chk").attr("disabled", false);
            });
    }); 
    backend = new LogonBackend();
    backend.loadInfo()
        .then(function(data){
            if (backend.allowSignup) {
                $("#signupform").show();
                $("#message").hide();
            }
            $("#user").focus();
        });
}); 
