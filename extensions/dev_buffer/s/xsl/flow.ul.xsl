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
<xsl:param name="ignore">i18n</xsl:param>
<xsl:param name="link_prefix"/>
<xsl:output method="html"
    encoding="UTF-8"
    omit-xml-declaration="yes"/>




  <xsl:param name="show_ns"/>
  <xsl:param name="rootme">/_R_/*</xsl:param>
  <xsl:variable name="apos">'</xsl:variable>

<xsl:template match="/">
<!-- Note: These &#160; spacers are needed to support both XHTML and HTML output. -->
<script src="/a/dev/pbooks/index.php?nid=x--dev--jquery.js" type="text/javascript">&#160;</script>
<script src="index.php?nid=x--dev--jquery.cookie.js" type="text/javascript">&#160;</script>
<script src="index.php?nid=x--dev--jquery.treeview.js" type="text/javascript">&#160;</script>
<script src="index.php?nid=x--dev--jquery.treeview.async.js" type="text/javascript">&#160;</script>
<link rel="stylesheet" href="index.php?nid=x--dev--jquery.treeview.css" />
<script type="text/javascript">
$(document).ready(function(){
	
	$("#black, #gray").treeview({
		control: "#treecontrol",
        collapsed: true
	});

});
</script>
<link rel="stylesheet" type="text/css" href="index.php?nid=x--dev--flow.css"/>
<script type="text/javascript" src="index.php?nid=x--dev--flow.js">&#160;</script>
<div id="flowDump">
    <div id="flowDumpContent" class="content">


	<div style="text-align: left;">
    <ul id="black" class="treeview-black"><xsl:apply-templates select="." mode="render"/></ul>
    </div>
    </div>
</div>
</xsl:template>


<xsl:template match="*" mode="render">
    <li>
        <xsl:value-of select="local-name()"/>
        <xsl:if test="text()">
            = <xsl:value-of select="text()"/>
        </xsl:if>
        <xsl:if test="not(text()) and not(.='')">
            <ul><xsl:apply-templates mode="render"/></ul>
        </xsl:if>
    </li>
</xsl:template>


</xsl:stylesheet>
