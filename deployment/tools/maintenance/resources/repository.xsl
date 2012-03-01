<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!-- Style sheet to display a rendered version of repository.xml. Just copy into the repository root folder. -->
	<xsl:template match="extensions">
		<html>
			<head>
				<title>A Deployment Framework Repository</title>
			</head>
			<body>
  <!-- Add custom icon here:
  <div style="float: right; padding: 1ex; margin: 0ex;"><img src="http://www.ontoprise.de/fileadmin/templates/img/logo.png"/></div>
  -->
				<h2>A Deployment Framework Repository</h2>
  <!-- Add custom help here:  
  The <a href="http://smwforum.ontoprise.com/smwforum/index.php/Help:SMW%2B_Administration_tool">deployment framework</a> 
  allows you to easily add more extensions to your SMW and SMW+ installation and keep them up-to-date. 
  The extensions listed below are included in this repository for the deployment framework. 
  <br/><br/>
  The versions provided here are development versions of the extensions right from 
  <a href="http://smwforum.ontoprise.com/websvn/listing.php?repname=repos+1&amp;path=%2Ftrunk%2F#_trunk_">SVN trunk</a>.
  This might mean that you will experience some problems. You can find out more about this repository in the 
  <a href="http://smwforum.ontoprise.com/smwforum/index.php/Help:SMW%2B_Administration_tool">SMWForum</a>. 
  If you would like to use only stable released versions please try the <a href="http://dailywikibuilds.ontoprise.com/repository/repository.xml">stable repository</a>.
  <br/><br/>
  -->
				<table border="1" cellpadding="5" style="background-color:#FFFFFF;" width="70%">
					<tr bgcolor="#9acd32">
						<th>Title (ID)</th>
						<th>Version</th>
						<th>Patch Level</th>
						<th>Maintainer</th>
						<th>Description</th>
						<th>Dependencies</th>
						<th>License</th>
						<th>Download</th>
					</tr>
					<xsl:for-each select="extension">
						<xsl:sort select="@id"/>
						<xsl:variable name="path">extensions/<xsl:value-of select="@id"/>/deploy.xml</xsl:variable>
						<!-- color -->
						<xsl:variable name="color">
							<xsl:choose>
								<xsl:when test="position() mod 2 = 0">#E6E6FA</xsl:when>
								<xsl:otherwise>#FFFACD</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<tr bgcolor="{$color}">
						<!-- Title -->
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:value-of select="document($path)/deploydescriptor/global/helpurl"/>
									</xsl:attribute>
									<xsl:value-of select="concat( @title, ' (', @id, ')')" />
								</a>
							</td>
						  <!-- Version -->
							<td>
								<xsl:value-of select="version/@version"/>
               <!-- 1.5.6, 1.5.3: <td><xsl:value-of select="version/@ver"/></td> -->
							</td>
							<!-- Patch Level -->
							<td>
								<xsl:value-of select="version/@patchlevel"/>
							</td>
							<!-- Maintainer -->
							<td>
								<xsl:for-each select="document($path)/deploydescriptor/global/maintainer">
									<xsl:value-of select="."/>
								</xsl:for-each>
							</td>
							<!-- Description -->
							<td>
								<xsl:for-each select="document($path)/deploydescriptor/global/description">
									<xsl:value-of select="."/>
								</xsl:for-each>
							</td>
							<!-- Dependencies -->
							<td>
								<xsl:for-each select="document($path)/deploydescriptor/global/dependencies/dependency">
									<xsl:value-of select="concat( ., ' [', @from, ' - ', @to, ']')" />
									<xsl:if test="@optional">
										<xsl:text>(optional)</xsl:text>
									</xsl:if>
									<br/>
								</xsl:for-each>
							</td>
							<!-- License -->
							<td>
								<xsl:value-of select="document($path)/deploydescriptor/global/license"/>
							</td>
							<!-- Download -->
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:value-of select="version/@url"/>
									</xsl:attribute>
									<xsl:text>Download</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
