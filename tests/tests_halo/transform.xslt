<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" indent="yes" />
    <xsl:param name="package" select="'GeneralTests'"/>

	<xsl:template match="testsuites/testsuite">

		<xsl:copy>

			<xsl:attribute name="name"><xsl:value-of select="@name" />
            </xsl:attribute>
			<xsl:attribute name="tests"><xsl:value-of select="@tests" />
            </xsl:attribute>

			<xsl:attribute name="assertions"><xsl:value-of
					select="@assertions" />
            </xsl:attribute>
			<xsl:attribute name="failures"><xsl:value-of
					select="@failures" />
            </xsl:attribute>
			<xsl:attribute name="errors"><xsl:value-of select="@errors" />
            </xsl:attribute>
			<xsl:attribute name="time"><xsl:value-of select="@time" />
            </xsl:attribute>
			<xsl:apply-templates select="//testcase" />
		</xsl:copy>

	</xsl:template>
	<xsl:template match="testcase">
		<xsl:copy>
		    <xsl:variable name="parentName" select="parent::testsuite/@name"/>
			<xsl:attribute name="classname"><xsl:value-of
					select="concat($package,'.',$parentName)" />
            </xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="@name" />
            </xsl:attribute>
			<xsl:attribute name="file"><xsl:value-of select="@file" />
            </xsl:attribute>
			<xsl:attribute name="class"><xsl:value-of select="@class" />
            </xsl:attribute>
			<xsl:attribute name="tests"><xsl:value-of select="@tests" />
            </xsl:attribute>
			<xsl:attribute name="assertions"><xsl:value-of
					select="@assertions" />
            </xsl:attribute>
			<xsl:attribute name="failures"><xsl:value-of
					select="@failures" />
            </xsl:attribute>
			<xsl:attribute name="errors"><xsl:value-of select="@errors" />
            </xsl:attribute>
			<xsl:attribute name="time"><xsl:value-of select="@time" />
            </xsl:attribute>
			<xsl:apply-templates select="failure" />
			<xsl:apply-templates select="error" />
		</xsl:copy>

	</xsl:template>
	<xsl:template match="failure">
		<xsl:copy>
			<xsl:attribute name="type"><xsl:value-of
					select="@type" />
            </xsl:attribute>
            <xsl:value-of select="."/>
		</xsl:copy>
	</xsl:template>
	
	<xsl:template match="error">
        <xsl:copy>
            <xsl:attribute name="type"><xsl:value-of
                    select="@type" />
            </xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:copy>
    </xsl:template>

</xsl:stylesheet>