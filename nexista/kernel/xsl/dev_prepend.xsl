<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" indent="yes" encoding="UTF-8" 
	omit-xml-declaration="yes" 
	doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN"
    doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>
<xsl:template match="/">
  <html>
  <head>
	<script type="text/javascript">
	var began_loading = (new Date()).getTime();
	function done_loading() {
		var total = (((new Date()).getTime() - began_loading) / 1000);
		document.write(total);
	}
	</script>
        
  </head>
  <body style="border: 0px; padding: 0px; margin: 0px; font-family: arial; font-size: 12px;">
  <table width='100%' bgcolor='#e3b6ec' cellpadding='2'><tr><td>
  MODE <b><xsl:value-of select="//mode"/></b>:  
    <!--
		[ <a href='/inform/'>PHP Info</a> ] 
		<xsl:if test="//cache='on'">
		[ <a href='/admn/'>xCache</a> ] 
		[ <a href='/acc/nxwiki/delete_cache/'>Delete File Cache</a> ] 
		</xsl:if>

		[ <a href="{//request_uri}&amp;rebuild=yes&amp;top_level_domain={//top_level_domain}">Rebuild</a> ] 
       -->    
       
		<xsl:if test="//_get/view_flow">
		[ <a href='{substring-before(//request_uri,"&amp;view_flow")}'>Hide Flow</a> ]
		</xsl:if>
		<xsl:if test="not(//_get/view_flow)">
		[ <a href='{//request_uri}&amp;view_flow=true'>View Flow</a> ]
		</xsl:if>
	</td>
	</tr></table>
	</body>
	</html>

</xsl:template>
</xsl:stylesheet>