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
<xsl:param name="ignore" select="i18n"/>
<xsl:param name="link_prefix"/>
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
<html>
<head>
<link rel="stylesheet" type="text/css" href="{$link_prefix}--css--dev"/>
<script type="text/javascript" src="{$link_prefix}--js--dev"></script>
</head>
<body>
<div id="flowDump" style="display: none;">
<h1 onclick="divExpand('flowDumpContent', true)" title="Click to expand/contract">Flow Dump</h1>
    <div id="flowDumpContent" class="content">
    <xsl:apply-templates select="." mode="render"/>
    </div>
</div>
<script type="text/javascript" language="javascript">
<![CDATA[
window.onload = divExpand('flowDumpContent');
document.body.style.visibility = "visible";
document.getElementById('flowDump').style.display = "block";
]]>
</script>
</body>
</html>
</xsl:template>


<xsl:template match="*" mode="render">
    <xsl:if test="not(local-name()=$ignore)">
    <br/>
    <xsl:call-template name="ascii-art-hierarchy"/>
    <span class="indent">&#160;</span>
    <span class="name">
        <xsl:value-of select="local-name()"/>
    </span>
    <xsl:apply-templates mode="render"/>
    </xsl:if>
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