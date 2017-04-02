// This is for the plugin architecture
var runPlugins = {};
const hooks = ["layoutReady", "readField", "readAccount", "drawAccount", "accountsReady", "fieldsReady", "editAccountDialog", "showDetails"];
function initPlugins() {
    for (hook in hooks){
        runPlugins[hooks[hook]] = [];
    }
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
