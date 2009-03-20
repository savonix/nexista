function done_loading(server_total) {
    document.getElementById('server_time').firstChild.nodeValue = server_total + ' s';
}
function done_loading_js() {
    var total = (((new Date()).getTime() - began_loading)) / 1000;
    document.getElementById('client_time').firstChild.nodeValue = total + ' s';
}