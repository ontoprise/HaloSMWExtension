<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" indent="yes" />
    <xsl:param name="package" select="'GeneralTests'"/>

	<xsl:template match="testsuites/testsuite">
Unittests for: <xsl:value-of select="$package" />
  tests: <xsl:value-of select="@tests" />
  assertions: <xsl:value-of select="@assertions" />
  failures: <xsl:value-of select="@failures" />
  errors: <xsl:value-of select="@errors" />
	</xsl:template>

    <xsl:template match="error">
  Error "<xsl:value-of select="@type" />" in <xsl:value-of select="node()/../@name" />
        <xsl:value-of select="."/>
	</xsl:template>

</xsl:stylesheet>