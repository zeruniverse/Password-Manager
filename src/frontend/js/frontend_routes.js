(function (window) {
  "use strict";

  function normalizeDirectoryUrl(value) {
    value = String(value || "").trim();

    if (value !== "" && value.slice(-1) !== "/") {
      value += "/";
    }

    return value;
  }

  function numberOrDefault(value, fallback) {
    value = Number(value);
    return isFinite(value) ? value : fallback;
  }

  function boolOrDefault(value, fallback) {
    if (typeof value === "boolean") {
      return value;
    }

    if (typeof value === "string") {
      if (value.toLowerCase() === "true") {
        return true;
      }

      if (value.toLowerCase() === "false") {
        return false;
      }
    }

    return fallback;
  }

  function legacyOrModern(cfg, modernName, legacyName, fallback) {
    if (typeof cfg[modernName] !== "undefined") {
      return cfg[modernName];
    }

    if (typeof cfg[legacyName] !== "undefined") {
      return cfg[legacyName];
    }

    return fallback;
  }

  window.pmConfig = function pmConfig() {
    var cfg = window.PASSWORD_MANAGER_CONFIG || {};

    return {
      apiBaseUrl: normalizeDirectoryUrl(
        legacyOrModern(cfg, "apiBaseUrl", "API_BASE_URL", "")
      ),

      frontendBaseUrl: normalizeDirectoryUrl(
        legacyOrModern(cfg, "frontendBaseUrl", "FRONTEND_BASE_URL", "")
      ),

      globalSalt1: legacyOrModern(cfg, "globalSalt1", "GLOBAL_SALT_1", ""),
      globalSalt2: legacyOrModern(cfg, "globalSalt2", "GLOBAL_SALT_2", ""),

      browserTimeout: numberOrDefault(
        legacyOrModern(cfg, "browserTimeout", "BROWSER_TIMEOUT", 360),
        360
      ),

      defaultPasswordLength: numberOrDefault(
        legacyOrModern(cfg, "defaultPasswordLength", "DEFAULT_PASSWORD_LENGTH", 13),
        13
      ),

      defaultLetters: legacyOrModern(
        cfg,
        "defaultLetters",
        "DEFAULT_LETTERS",
        "*+-0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz~"
      ),

      backupKeyIterations: numberOrDefault(
        legacyOrModern(cfg, "backupKeyIterations", "BACKUP_KEY_ITERATIONS", 10),
        10
      ),

      minPasswordLength: numberOrDefault(
        legacyOrModern(cfg, "minPasswordLength", "MINIMAL_PASSWORD_LENGTH", 7),
        7
      ),

      minNameLength: numberOrDefault(
        legacyOrModern(cfg, "minNameLength", "MINIMAL_NAME_LENGTH", 5),
        5
      )
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
    var configured = pmConfig().frontendBaseUrl;

    if (!configured) {
      return true;
    }

    try {
      var expected = new URL(configured, window.location.href);
      var current = new URL(window.location.href);

      var expectedPath = expected.pathname;
      if (expectedPath.slice(-1) !== "/") {
        expectedPath += "/";
      }

      return current.origin.toLowerCase() === expected.origin.toLowerCase() &&
        current.pathname.indexOf(expectedPath) === 0;
    } catch (e) {
      var expectedText = String(configured).replace(/\/+$/, "").toLowerCase();
      var currentText = String(window.location.href).split(/[?#]/)[0].replace(/\/+$/, "").toLowerCase();

      return currentText.indexOf(expectedText) === 0;
    }
  };
})(window);