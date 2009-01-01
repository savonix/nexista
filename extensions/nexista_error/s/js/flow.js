function getCookie(name)
{
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1)
    {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else
    {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1)
    {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}
function setCookie(name, value, expires, path, domain, secure)
{
    document.cookie= name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires.toGMTString() : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}

var visible = 'block';
function divExpand(layer, set) {
    if(!set)
          visible =  getCookie("visibility-"+layer);

    if(visible == 'block') {
        document.getElementById(layer).style.display = "block";
        if(set)
            setCookie("visibility-"+layer,  visible);
        visible = 'none';
    }
    else {
        document.getElementById(layer).style.display = "none";
        if(set)
            setCookie("visibility-"+layer,  visible);
        visible = 'block';
    }
}