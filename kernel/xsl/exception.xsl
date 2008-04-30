<?xml version="1.0" encoding="utf-8"?>
<!--
XSL to present exception trace
Derived from work by Mike J. Brown and Jeni Tennison.

By default, this stylesheet will not show namespace nodes. If the XSLT processor
supports the namespace axis and you want to see namespace nodes, just pass a
non-empty "show_ns" parameter to the stylesheet. Example using Instant Saxon:

    saxon somefile.xml tree-view.xsl show_ns=yes

If you want to ignore whitespace-only text nodes, uncomment the xsl:strip-space
instruction below. This is recommended if you are a beginner.
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html"  
    encoding="iso-8859-1" 
    indent="yes" 
    omit-xml-declaration="yes"
    doctype-public="-//W3C//DTD XHTML 4.0 Strict//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

  <xsl:strip-space elements="*"/>

  <xsl:param name="show_ns"/>
  <xsl:variable name="apos">'</xsl:variable>

  <xsl:template match="/">
<script type="text/javascript" language="javascript">
<![CDATA[
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
        document.all[layer].style.display = "block";
        if(set)
            setCookie("visibility-"+layer,  visible);
        visible = 'none';
    }
    else {
        document.all[layer].style.display = "none";
        if(set)
            setCookie("visibility-"+layer,  visible);
        visible = 'block';
    }
}
]]>
</script>
<style>
#exception {
    font-family:arial;
    font-weight:normal;
    font-size:x-small;
    line-height:20px;
    padding:0px;
    margin:5px;
    z-index:20;
    position:relative;
}
#exception h1 {
    font-size:14px;
    font-weight:bold;
    color:white;
    background-color:#AE3E40;
    text-indent:5px;
    margin-bottom:2px;
}
#exception .content {
    background-color:#FFF0F0;
    border:1px solid #AE3E40;
    padding:5px;
    margin:0px;
}
#exception .trace {
    padding-left:20px;
}
#exception .name {

    font-weight:bold;

    padding-left: 3px; 
     padding-right: 3px;
    margin-right: 5px;
}
</style>


<div id="exception">
<h1 onclick="divExpand('exceptionContent', true)" title="Click to expand/contract">Exception Trace</h1>
    <div id="exceptionContent" class="content"> 
    <xsl:if test="//gate">
    <div ><span class="name">Gate:</span> <xsl:value-of select="//gate/name"/></div>
    </xsl:if>

    <xsl:if test="//message">
    <div ><span class="name">Message:</span> <xsl:value-of select="//message"/></div>
    </xsl:if>
    <div><span class="name">Traceback:</span>
     <xsl:apply-templates select="//traces/trace" />
     </div>
    </div>
</div>
<script type="text/javascript" language="javascript">
<![CDATA[
window.onload = divExpand('exceptionContent');
]]>
</script>
</xsl:template>


<xsl:template match="trace" >
<div class="trace">
<xsl:value-of select="class"/><xsl:value-of select="function"/> in 
<xsl:value-of select="file"/> in line <xsl:value-of select="line"/>
</div>
</xsl:template>
</xsl:stylesheet>