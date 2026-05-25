(function () {
  "use strict";

  sessionStorage.removeItem("session_token");
  sessionStorage.removeItem("pm_api_session_id");
  sessionStorage.removeItem("pwdsk");
  sessionStorage.removeItem("confusion_key");

  var target = "index.html";

  if (window.location.search) {
    target += window.location.search;
  }

  window.location.replace(target);
})();