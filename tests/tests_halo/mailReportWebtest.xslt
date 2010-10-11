<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" indent="yes" />
    <xsl:param name="package" select="'GeneralTests'"/>
    <xsl:template match="/overview">
Webtests for: <xsl:value-of select="$package" /> (success: <xsl:value-of select="count(//summary[@successful='yes'])" />, errors: <xsl:value-of select="count(//summary[@successful='no'])" />)	
        <xsl:apply-templates select=".//folder" />
    </xsl:template>
    <xsl:template match="folder">
        <xsl:if test="./summary/failingstep">
  Error in "<xsl:value-of select="summary/@name" />" step "<xsl:value-of select="summary/failingstep/@description" />"
        </xsl:if>
	</xsl:template>
</xsl:stylesheet>