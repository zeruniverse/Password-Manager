(function (window) {
  "use strict";

  function trimSlashRight(value) {
    return String(value || "").replace(/\/+$/, "");
  }

  function normalizeDirectoryUrl(value) {
    value = String(value || "").trim();
    if (value !== "" && value.slice(-1) !== "/") {
      value += "/";
    }
    return value;
  }

  window.pmConfig = function pmConfig() {
    var cfg = window.PASSWORD_MANAGER_CONFIG || {};
    return {
      apiBaseUrl: normalizeDirectoryUrl(cfg.apiBaseUrl || cfg.API_BASE_URL || ""),
      frontendBaseUrl: normalizeDirectoryUrl(cfg.frontendBaseUrl || cfg.FRONTEND_BASE_URL || ""),
      globalSalt1: cfg.globalSalt1 || cfg.GLOBAL_SALT_1 || "",
      globalSalt2: cfg.globalSalt2 || cfg.GLOBAL_SALT_2 || "",
      browserTimeout: Number(cfg.browserTimeout || cfg.BROWSER_TIMEOUT || 360),
      defaultPasswordLength: Number(cfg.defaultPasswordLength || cfg.DEFAULT_PASSWORD_LENGTH || 13),
      defaultLetters: cfg.defaultLetters || cfg.DEFAULT_LETTERS || "*+-0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz~",
    };
  };

  window.pmFrontendRoot = function pmFrontendRoot() {
    var configured = pmConfig().frontendBaseUrl;
    if (configured) {
      return configured;
    }
    var path = window.location.pathname.replace(/[^\/]*$/, "");
    return window.location.origin + path;
  };

  window.pmPageUrl = function pmPageUrl(pageName, params) {
    var root = pmFrontendRoot();
    var url = root + pageName.replace(/^\/+/, "");
    if (params) {
      var q = new URLSearchParams(params).toString();
      if (q) {
        url += "?" + q;
      }
    }
    return url;
  };

  window.pmRedirect = function pmRedirect(pageName, params) {
    window.location.href = pmPageUrl(pageName, params);
  };

  window.pmCheckFrontendLocation = function pmCheckFrontendLocation() {
    var expected = trimSlashRight(pmConfig().frontendBaseUrl).toLowerCase();
    if (!expected) {
      return true;
    }
    var current = trimSlashRight(window.location.href.split(/[?#]/)[0]).toLowerCase();
    return current.indexOf(expected) === 0;
  };
})(window);
