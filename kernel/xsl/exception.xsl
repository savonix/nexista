<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
  <xsl:template match="/">
<style>
#exception {
    font-family:sans-serif;
    font-weight:normal;
    font-size:x-small;
    line-height:20px;
    padding:0px;
    margin:5px;
    z-index:20;
    position:relative;
    text-align: left;
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