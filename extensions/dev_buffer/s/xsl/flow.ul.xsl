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
<xsl:param name="ignore">i18n</xsl:param>
<xsl:param name="link_prefix"/>
  <xsl:output method="html"
    encoding="UTF-8"
    indent="yes"
    omit-xml-declaration="yes"
    doctype-public="-//W3C//DTD XHTML 4.0 Strict//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>


  <xsl:strip-space elements="*"/>


  <xsl:param name="show_ns"/>
  <xsl:param name="rootme">/_R_/*</xsl:param>
  <xsl:variable name="apos">'</xsl:variable>

<xsl:template match="/">
<script src="http://dev-48.savonix.com/a/sand/treeview/lib/jquery.js" type="text/javascript"></script>
<script src="http://dev-48.savonix.com/a/sand/treeview/lib/jquery.cookie.js" type="text/javascript"></script>
<script src="http://dev-48.savonix.com/a/sand/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="http://dev-48.savonix.com/a/sand/treeview/jquery.treeview.async.js" type="text/javascript"></script>
<link rel="stylesheet" href="http://dev-48.savonix.com/a/sand/treeview/jquery.treeview.css" />

<script type="text/javascript">
$(document).ready(function(){
	
	$("#black, #gray").treeview({
		control: "#treecontrol"
	});

});
</script>
	<div id="treecontrol">
		<a title="Collapse the entire tree below" href="#"><img src="../images/minus.gif" /> Collapse All</a>
		<a title="Expand the entire tree below" href="#"><img src="../images/plus.gif" /> Expand All</a>
		<a title="Toggle the tree below, opening closed branches, closing open branches" href="#">Toggle All</a>
	</div>

    <ul id="black" class="treeview-black"><xsl:apply-templates select="." mode="render"/></ul>
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