(function (window) {
  "use strict";

  window.PASSWORD_MANAGER_CONFIG = {
    /*
     * Backend root URL. It must point to the directory that contains rest/ and function/.
     * Must end with / or the frontend helper will add it automatically.
     */
    apiBaseUrl: "https://api.example.com/passwordmanager/backend/",
   

    /*
     * Client-side crypto salts.
     *
     * For an existing installation, copy these two values exactly from the old
     * src/function/config.php. Do not change them after data has been created.
     */
    globalSalt1: "iunin19dnu9ismcj9IUNuia,cne9e389]{}{}[]*@key",
    globalSalt2: "ncew8d7*(e8fyh2inc osd2)wefcsBIUsdfq2as;dqw[;[]]",

    /*
     * Frontend-only behavior.
     */
    browserTimeout: 360,
    defaultPasswordLength: 13,
    defaultLetters: "*+-0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz~",
    backupKeyIterations: 10,

    /*
     * Frontend registration form checks.
     * The historical backend did not enforce these; it only sent them to the frontend.
     */
    minPasswordLength: 7,
    minNameLength: 5
  };
})(window);
