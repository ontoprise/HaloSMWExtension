<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!-- Style sheet to display a rendered version of repository.xml. Just copy into the repository root folder. -->
<xsl:template match="extensions">
  <html>
  <head>
    <title>a DF repository</title>
  </head>
  <body>
  
  <h2>a DF repository</h2>
    
  <table border="0" cellpadding="5" style="background-color:#FFFFFF;" width="70%">
    <tr bgcolor="#9acd32">
      <th>ID</th>
      <th>Version</th>
      <th>Patch Level</th>
      <th>Maintainer</th>
      <th>Description</th>
    <th>Dependencies</th>
    <th>License</th>
    </tr>
    <xsl:for-each select="extension">
	    <xsl:sort select="@title"/>
	    <tr>
	      <td>
	        <a>
	          <xsl:attribute name="href"><xsl:value-of select="version/@helpurl"/></xsl:attribute>
	          <xsl:value-of select="@title"/> (<xsl:value-of select="@id"/>
	        </a>
	      </td>
	      <td><xsl:value-of select="version/@version"/></td>
	      <td><xsl:value-of select="version/@patchlevel"/></td>
	
	      <td><xsl:value-of select="version/@maintainer"/></td>
	      <td><xsl:value-of select="version/@description"/></td>
	      <td>
	        <xsl:variable name="path">extensions/<xsl:value-of select="@id"/>/deploy.xml</xsl:variable>
	        <xsl:for-each select="document($path)/deploydescriptor/global/dependencies/dependency">
	          <xsl:value-of select="."/> [<xsl:value-of select="@from"/> - <xsl:value-of select="@to"/>]
			<xsl:if test="@optional">(optional)</xsl:if>
			<br/>
	        </xsl:for-each>
	      </td>
	       <td>
	        <xsl:variable name="path">extensions/<xsl:value-of select="@id"/>/deploy.xml</xsl:variable>
	        <xsl:value-of select="document($path)/deploydescriptor/global/license"/>
	      </td>
	    </tr>
    </xsl:for-each>
  </table>
  </body>
  </html>
</xsl:template>

</xsl:stylesheet>
