// This is for the plugin architecture
var runPlugins = {};
function initPlugins() {
    runPlugins["layoutReady"] = [];
    runPlugins["readField"] = [];
    runPlugins["readAccount"] = [];
    runPlugins["drawAccount"] = [];
    runPlugins["accountsReady"] = [];
    //runPlugins["addAccount"] = [];
}
function callPlugins(entry, data){
    for (var call in runPlugins[entry]){
        runPlugins[entry][call](data);
    }
}
function registerPlugin(entry, call){
    runPlugins[entry].push(call);
}
initPlugins();
