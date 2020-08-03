// This is for the plugin architecture
var runPlugins = {};
const hooks = ["preDataReady", "dataReady", "layoutReady", "readField", "readAccount",
"drawAccount", "accountsReady", "fieldsReady", "editAccountDialog", "showDetails",
"addAccountPreSend", "updateAccountPreSend", "logout", "preLogout"];
function initPlugins() {
    for (let hook in hooks){
        runPlugins[hooks[hook]] = [];
    }
}
function callPlugins(entry, data){
    var resultset = [];
    for (var callback of runPlugins[entry]){
        resultset.push(callback(data));
    }
    return Promise.all(resultset);
}
function registerPlugin(entry, call){
    runPlugins[entry].push(call);
}
initPlugins();
