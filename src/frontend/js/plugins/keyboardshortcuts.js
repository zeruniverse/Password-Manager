// Plugin for Keyboard Shortcuts
// define Shortcuts (keycode: {action: action to do (modal, click, focus), element: element to focus, description: help text})

var keyboardShortcuts = {
    "97": {
        "action": "addEntry",
        "element": "#edit",
        "description": "Show add entry dialog"
    },

    // /: focus search
    "47": {
        "action": "focus",
        "element": "#pwdlist_filter > label > input",
        "description": "Focus search input"
    },

    // ?: show shortcut help
    "63": {
        "action": "modal",
        "element": "#shortcuts",
        "description": "Show keyboard shortcut help"
    },

    // l: next page
    "108": {
        "action": "click",
        "element": "#pwdlist_next > a",
        "description": "Got to next page"
    },

    // h: previous page
    "104": {
        "action": "click",
        "element": "#pwdlist_previous > a",
        "description": "Got to previous page"
    }
};

var actions = {
    "focus": function (element) {
        $(element).focus();
    },

    "click": function (element) {
        $(element).click();
    },

    "modal": function (element) {
        $(element).modal("show");
    },

    "addEntry": function (element) {
        var $modal = $(element);

        if ($modal.length === 0) {
            return;
        }

        // Force add mode before opening the shared add/edit dialog.
        // main.js treats id == -1 as "add new account".
        $modal.data("id", -1);
        $modal.attr("data-id", "-1");
        $modal.modal("show");
    }
};

function isKeyboardShortcutInputTarget(target) {
    var tagName = target && target.nodeName ? target.nodeName.toLowerCase() : "";

    if (tagName === "input" || tagName === "textarea" || tagName === "select") {
        return true;
    }

    return $(target).is("[contenteditable=true], [contenteditable=true] *");
}

function handleKeyboardShortcut(e) {
    if (e.ctrlKey || e.altKey || e.metaKey) {
        return;
    }

    if (isKeyboardShortcutInputTarget(e.target)) {
        return;
    }

    var key = e.which || e.keyCode;

    // Modern browsers may provide e.key instead of useful keypress codes.
    if (!key && e.key && e.key.length === 1) {
        key = e.key.charCodeAt(0);
    }

    key = String(key);

    if (!(key in keyboardShortcuts)) {
        return;
    }

    var shortcut = keyboardShortcuts[key];
    var action = shortcut["action"];

    if (!(action in actions)) {
        return;
    }

    actions[action](shortcut["element"]);

    e.preventDefault();
    e.stopPropagation();
}

registerPlugin("layoutReady", function (data) {
    // Avoid duplicated handlers if layoutReady is triggered more than once.
    $(document)
        .off("keypress.keyboardShortcuts")
        .on("keypress.keyboardShortcuts", handleKeyboardShortcut);

    var list = $('<ul class="list-group"></ul>');

    for (let key in keyboardShortcuts) {
        list.append(
            $('<li class="list-group-item"></li>')
                .text(keyboardShortcuts[key]["description"])
                .prepend(
                    $('<span class="badge"></span>')
                        .text(String.fromCharCode(parseInt(key, 10)))
                )
        );
    }

    var modal = $('<div class="modal" tabindex="-1" role="dialog" id="shortcuts"></div>')
        .append(
            $('<div class="modal-dialog"></div>')
                .append(
                    $('<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>')
                )
                .append(
                    $('<div class="modal-content"></div>')
                        .append($('<div class="modal-header"><h4>Keyboard Shortcuts</h4></div>'))
                        .append(
                            $('<div class="modal-body"></div>')
                                .append(list)
                        )
                        .append(
                            $('<div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> </div>')
                        )
                )
        );

    $("#shortcuts").remove();
    $("body").append(modal);
});
