// Plugin for Keyboard Shortcuts

// define Shortcuts (keycode: {action: action to do (modal, click, focus), element: element to focus, description: help text})
var keyboardShortcuts = {
    '97': {"action":"modal", "element":"#add", "description": "Show add entry dialog"},
    '47': {"action":"focus", "element":"#pwdlist_filter > label > input", "description": "Focus search input"},
    '63': {"action":"modal", "element":"#shortcuts", "description": "Show keyboard shortcut help"},
    '108': {"action":"click", "element":"#pwdlist_next > a", "description": "Got to next page"},
    '104': {"action":"click", "element":"#pwdlist_previous > a", "description": "Got to previous page"},
    // todo n, p for navigating table
}

var actions = {
    "focus":function(element){$(element).focus();},
    "click":function(element){$(element).click();},
    "modal":function(element){$(element).modal();},
}

registerPlugin("layoutReady", function(data){
    $(document).delegate(':not(input)', 'keypress', function(e) {
        if (e.target.nodeName.toLowerCase() == 'input') {
            return;
        }
        var key = e.which;
        if (! (key in keyboardShortcuts)) {
            return;
        }
        var shortcut = keyboardShortcuts[key];
        actions[shortcut["action"]](shortcut["element"]);
        e.preventDefault();
        e.stopPropagation();
    });
    var list = $('<ul class="list-group"></ul>');
    for (key in keyboardShortcuts) {
        list.append($('<li class="list-group-item"></li>')
            .text(keyboardShortcuts[key]["description"])
            .prepend($('<span class="badge"></span>')
                .text(String.fromCharCode(key))));
    }
    var modal = $('<div class="modal" tabindex="-1" role="dialog" id="shortcuts"></div>')
                    .append($('<div class="modal-dialog"></div>')
                        .append($('<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'))
                        .append($('<div class="modal-content"></div')
                            .append($('<div class="modal-header"><h4>Keyboard Shortcuts</h4></div>'))
                            .append($('<div class="modal-body"></div>')
                                .append(list))
                            .append($('<div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button> <button type="button" class="btn btn-primary" id="newbtn">Add</button> </div>'))
                        )
                    );
    $( "body" ).append(modal);
});
