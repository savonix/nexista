<?xml version="1.0" encoding="utf-8"?>
<!--
XSL to dump Flow
Dreived from work by Mike J. Brown and Jeni Tennison.


By default, this stylesheet will not show namespace nodes. If the XSLT processor
supports the namespace axis and you want to see namespace nodes, just pass a
non-empty "show_ns" parameter to the stylesheet. Example using Instant Saxon:

    saxon somefile.xml tree-view.xsl show_ns=yes

If you want to ignore whitespace-only text nodes, uncomment the xsl:strip-space
instruction below. This is recommended if you are a beginner.

-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html"
    encoding="UTF-8"
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
<!--
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
-->
]]>
</script><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<style>
#flowDump {
    font-family:arial;
    font-weight:normal;
    font-size:x-small;
    line-height:20px;
    padding:0px;
    margin:5px;
    z-index:20;
    position:relative;
}
#flowDump h1 {
    font-size:14px;
    font-weight:bold;
    color:white;
    background-color:#766789;
    text-indent:5px;
    margin-bottom:2px;
}
#flowDump .content {
    background-color:#E6EEFF;
    border:1px solid #766789;
    padding:5px;
    margin:0px;
}
#flowDump .spacer {
    margin-left:20px;
}
#flowDump .indent {
    border-bottom:1px solid blue;
    padding-left:15px;
    border-left:1px solid blue;
    position:relative;
    top:-5px;
}
#flowDump .connector {
    color:red;
    border-left:1px solid blue;
}
#flowDump .name {
    color: navy;
   border:1px solid #CCB2ED;
    background-color: #FFF;
    padding-left: 3px;
    padding-right: 3px;

}
#flowDump .value {
    color: #040;
    font-weight: bold;
    padding:1px 3px 1px 3px;
}

</style>


<div id="flowDump">
<h1 onclick="divExpand('flowDumpContent', true)" title="Click to expand/contract">Flow Dump</h1>
    <div id="flowDumpContent" class="content">
    <xsl:apply-templates select="." mode="render"/>
    </div>
</div>
<script type="text/javascript" language="javascript">
<![CDATA[
window.onload = divExpand('flowDumpContent');
]]>
</script>
</xsl:template>


<xsl:template match="*" mode="render">
    <br/>
    <xsl:call-template name="ascii-art-hierarchy"/>
    <span class="indent">&#160;</span>
    <span class="name">
        <xsl:value-of select="local-name()"/>
    </span>
    <xsl:apply-templates mode="render"/>
</xsl:template>

<xsl:template match="text()" mode="render">
    =
    <span class="value">
      <!-- make spaces be non-breaking spaces, since this is HTML -->
      <xsl:call-template name="escape-ws">
        <xsl:with-param name="text" select="translate(.,' ','&#160;')"/>
      </xsl:call-template>
    </span>

  </xsl:template>

  <xsl:template name="ascii-art-hierarchy">
    <xsl:for-each select="ancestor::*">
      <xsl:choose>
        <xsl:when test="following-sibling::node()">
      <span class="spacer">&#160;</span>
          <xsl:text>&#160;</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <span class="spacer">&#160;</span>

        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>

  <!-- recursive template to escape linefeeds, tabs -->
  <xsl:template name="escape-ws">
    <xsl:param name="text"/>
    <xsl:choose>
      <xsl:when test="contains($text, '&#xA;')">
        <xsl:call-template name="escape-ws">
          <xsl:with-param name="text" select="substring-before($text, '&#xA;')"/>
        </xsl:call-template>
        <span class="escape">\n</span>
        <xsl:call-template name="escape-ws">
          <xsl:with-param name="text" select="substring-after($text, '&#xA;')"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="contains($text, '&#x9;')">
        <xsl:value-of select="substring-before($text, '&#x9;')"/>
        <span class="escape">\t</span>
        <xsl:call-template name="escape-ws">
          <xsl:with-param name="text" select="substring-after($text, '&#x9;')"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise><xsl:value-of select="$text"/></xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>