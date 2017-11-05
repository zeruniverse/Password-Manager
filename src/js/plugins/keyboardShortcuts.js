// Plugin for Keyboard Shortcuts

// define Shortcuts (keycode: {action: action to do (toggle, click, focus), element: element to focus, description: help text})
var keyboardShortcuts = {
    'a': {"action":"toggle", "element":"#add", "description": "show add entry dialog"},
    's': {"action":"focus", "element":"#pwdlist_filter > label > input", "description": "focus search input"},
    '?': {"action":"toggle", "element":"#shortcutHelp", "description": "show/hide keyboard shortcut help"},
}

var effectiveKeyboardShortcuts = {}

// generate keycodes
function prepareKeyboardShortcuts(){
    for (var key in keyboardShortcuts){
        effectiveKeyboardShortcuts[key.toUpperCase().charCodeAt(0)] = keyboardShortcuts[key];
    }
}

registerPlugin("layoutReady", function(data){
    prepareKeyboardShortcuts();
    $(document).delegate(':not(input)', 'keyup', function(e) {
        var key = e.keyCode;
        if (! key in effectiveKeyboardShortcuts) {
            return;
        }
        var shortcut = effectiveKeyboardShortcuts[key];
        if (shortcut["action"] == "focus") {
            $(shortcut["element"]).focus();
        }
        else if (shortcut["action"] == "toggle") {
            $(shortcut["element"]).toggle();
        }
        else if (shortcut["action"] == "click") {
            $(shortcut["element"]).click();
        }
        e.preventDefault();
    });
    var list = $('<ul class="list-group"></ul>');
    for (key in keyboardShortcuts) {
        list.append($('<li class="list-group-item"></li>')
            .text(keyboardShortcuts["description"})
            .prepend($('<span class="badge"></span>')
                .text(key)));
    }
    var modal = $('<div class="modal" tabindex="-1" role="dialog" id="shortcuts"></div>')
                    .append($('<div class="modal-dialog"></div>')
                        .append($('<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'))
                        .append($('<div class="modal-content"></div')
                            .append($('<div class="modal-header"><h4>Add a new account</h4></div>'))
                            .append($('<div class="modal-body"></div>')
                                .append(list))
                            .append($('<div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button> <button type="button" class="btn btn-primary" id="newbtn">Add</button> </div>'))
                        )
                    );
    $( "body" ).append(modal);
});
