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

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
  <xsl:template match="/">

<link rel="stylesheet" type="text/css" href="{$link_prefix}--css--dev"/>


<div id="exception">
<h1>Exception Trace</h1>
    <div id="exceptionContent" class="content">
        <xsl:if test="//gate">
        <div><span class="name">Gate:</span> <xsl:value-of select="//gate/name"/></div>
        </xsl:if>

        <xsl:if test="//message">
        <div><span class="name">Message:</span> <xsl:value-of select="//message"/></div>
        </xsl:if>

        <div><span class="name">Traceback:</span>
        <xsl:apply-templates select="//traces/trace" />
        </div>
    </div>
</div>
</xsl:template>

<xsl:template match="trace" >
<div class="trace">
    <xsl:value-of select="class"/><xsl:value-of select="function"/> in
    <xsl:value-of select="file"/> in line <xsl:value-of select="line"/>
</div>
</xsl:template>
</xsl:stylesheet>