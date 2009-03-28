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
<xsl:include href="flow.ul.xsl"/>
<xsl:output method="xml"
	encoding="UTF-8"
	omit-xml-declaration="yes"/>
  <xsl:param name="show_ns"/>
  <xsl:param name="rootme">/_R_/*</xsl:param>
  <xsl:variable name="apos">'</xsl:variable>
  <xsl:strip-space elements="*"/>

<xsl:template match="/">
	<xsl:call-template name="viewflow"/>
</xsl:template>
</xsl:stylesheet>
