<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" indent="yes" />
    <xsl:param name="package" select="'(root)'"/>
    <xsl:param name="build_server" select="'http://halo-build-serv:8080'"/>
    <xsl:param name="build_number" select="'lastCompletedBuild'"/>
    <xsl:param name="build_project" select="'Internal__SMWHalo%20Tests'"/>

	<xsl:template match="testsuites/testsuite">
Unittests for: <xsl:value-of select="$package" />
  tests: <xsl:value-of select="@tests" />
  assertions: <xsl:value-of select="@assertions" />
  failures: <xsl:value-of select="@failures" />
  errors: <xsl:value-of select="@errors" />
  &lt;<xsl:value-of select="$build_server" />/job/<xsl:value-of select="$build_project" />/<xsl:value-of select="$build_number" />/testReport/<xsl:value-of select="$package" />&gt;
	</xsl:template>

<!--
    <xsl:template match="error">
  Error "<xsl:value-of select="@type" />" in <xsl:value-of select="node()/../@name" />
        <xsl:value-of select="."/>
	</xsl:template>
-->
</xsl:stylesheet>