<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" indent="yes" />
    <xsl:param name="package" select="'GeneralTests'"/>
    <xsl:param name="build_server" select="'http://halo-build-serv:8080'"/>
    <xsl:param name="build_number" select="'lastCompletedBuild'"/>
    <xsl:param name="build_project" select="'Internal__SMWHalo%20Tests'"/>
    <xsl:template match="/overview">
Webtests for: <xsl:value-of select="$package" /> (success: <xsl:value-of select="count(//summary[@successful='yes'])" />, errors: <xsl:value-of select="count(//summary[@successful='no'])" />)
&lt;<xsl:value-of select="$build_server" />/job/<xsl:value-of select="$build_project" />/<xsl:value-of select="$build_number" />/webtestResults_<xsl:value-of select="$package" />&gt;
       <!-- <xsl:apply-templates select=".//folder" />-->
    </xsl:template>
    <xsl:template match="folder">
        <xsl:if test="./summary/failingstep">
  Error in "<xsl:value-of select="summary/@name" />" step "<xsl:value-of select="summary/failingstep/@description" />"
        </xsl:if>
	</xsl:template>
</xsl:stylesheet>