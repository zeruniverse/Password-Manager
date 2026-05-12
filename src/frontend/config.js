(function (window) {
  "use strict";

  window.PASSWORD_MANAGER_CONFIG = {
    // Backend deployment root. It must point to the directory that contains rest/ and function/.
    // Example: https://api.example.com/passwordmanager/backend/
    apiBaseUrl: "https://api.example.com/passwordmanager/backend/",

    // Public frontend URL. Example: https://abc.github.io/passwordmanager/
    frontendBaseUrl: "https://abc.github.io/passwordmanager/",

    // Copied from the previous function/config.php. Keep these exact values for existing data.
    globalSalt1: "iunin19dnu9ismcj9IUNuia,cne9e389]{}{}[]*@key",
    globalSalt2: "ncew8d7*(e8fyh2inc osd2)wefcsBIUsdfq2as;dqw[;[]]",

    browserTimeout: 360,
    defaultPasswordLength: 13,
    defaultLetters: "*+-0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz~",
  };
})(window);
