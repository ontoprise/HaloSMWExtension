<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!--
**
**   Description : STYLESHEET FOR GENERATION OF THE HTML Treeview
**
**   Date : 05/08/2003 - version : 1.3
**   Author : Jean-Michel Garnier, http://rollerjm.free.fr
**   Source is free but feel free (!) to send any comment / suggestion to garnierjm@ifrance.com
**   Updates :
**   - 07/01/2003 : add img-directory parameter
**   - 18/01/2003 : remove deploy-treeview parameter, add XSLT param-isMozilla, refactoring bc of DTD changes
**   - 05/08/2003 : fix bug when using the expanded parameter
** 
-->
	<!-- Change the encoding here if you need it, i.e. UTF-8 -->
	<xsl:output method="html" encoding="iso-8859-1" indent="yes"/>
	<!-- ************************************ Parameters ************************************ -->
	<!-- deploy-treeview, boolean - true if you want to deploy the tree-view at the first print -->
	<xsl:param name="param-deploy-treeview" select="'false'"/>
	<!-- is the client Netscape / Mozilla or Internet Explorer. Thanks to Bill, 90% of sheeps use Internet Explorer so it will the default value-->
	<xsl:param name="param-is-netscape" select="'false'"/>
	<!-- hozizontale distance in pixels between a folder and its leaves -->
	<xsl:param name="param-shift-width" select="15"/>
	<xsl:param name="startDepth" select="1"/>
	
	<!-- Maximum length of entites displayed -->
	<xsl:param name="maximumEntityLength" select="18"/>
	
	<!-- image source directory-->
	<xsl:param name="param-img-directory" select="''"/>
	<!-- ************************************ Variables ************************************ -->
	<xsl:variable name="var-simple-quote">'</xsl:variable>
	<xsl:variable name="var-slash-quote">\'</xsl:variable>
	<xsl:variable name="var-underscore">_</xsl:variable>
	<xsl:variable name="var-blank" select="string(' ')"></xsl:variable>
	<!--
**
**  Model "treeview"
** 
**  This model transforms an XML treeview into an html treeview
**  
-->
	<xsl:template match="result">
		<xsl:choose>
		<xsl:when test="not (@isEmpty)">
		<!-- -->
		<!--<link rel="stylesheet" href="treeview.css" type="text/css"/>-->
		<!-- Warning, if you use-->
		<!--<script src="treeview.js" language="javascript" type="text/javascript"/>-->
		
					<!-- Apply the template folder starting with a startDepth in the tree of 1-->
										
					<xsl:apply-templates select="conceptTreeElement">
						<xsl:with-param name="rek_depth" select="1"/>
					</xsl:apply-templates>
					
					<xsl:apply-templates select="propertyTreeElement">
						<xsl:with-param name="rek_depth" select="1"/>
					</xsl:apply-templates>
					
					<xsl:apply-templates select="categoryPartition"/>
					<xsl:apply-templates select="propertyPartition"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="@textToDisplay"></xsl:value-of>
		</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!--
**
**  Model "folder"
** 
**  This model transforms a folder element. Prints a plus (+) or minus (-)  image, the folder image and a title
**  
-->
	<xsl:template match="conceptTreeElement">
		<xsl:param name="rek_depth" select="1"/>
		
						
		<table class="categoryTreeColors" border="0" cellspacing="0" cellpadding="0">
		 	<xsl:if test="$startDepth=1 and $rek_depth=1">
				<xsl:attribute name="width">1000</xsl:attribute>
			</xsl:if> 
			<tr>
				<!-- If startDepth is on first level, do not shift of $param-shift-width-->
				<xsl:if test="$startDepth>1 and not ($rek_depth>1)">
					<td width="{$param-shift-width}"/>
				</xsl:if>
				<!-- For every level below, shift-->
				<xsl:if test="$rek_depth>1">
					<td width="{$param-shift-width}"/>
				</xsl:if>
				<!-- Shift if it is a leaf, because the plus/minus image is missing-->
				<!--  <xsl:if test="@isLeaf">
					<td width="16"/>
				</xsl:if>-->
				<td>
			
					<xsl:call-template name="createTreeNode">
						<xsl:with-param name="actionListener" select="'categoryActionListener'"/>
						<xsl:with-param name="typeOfEntity" select="'concept'"/>
						<xsl:with-param name="rek_depth" select="$rek_depth"/>
					</xsl:call-template>
					
					<!-- Shall we expand all the leaves of the treeview ? no by default-->
					<div>
						<xsl:apply-templates select="categoryPartition">
							<xsl:with-param name="rek_depth" select="$rek_depth+1"/>
						</xsl:apply-templates>
						
						<xsl:call-template name="setExpansionState"/>			
							
						<!-- Thanks to the magic of reccursive calls, all the descendants of the present folder are gonna be built -->
						<xsl:apply-templates select="conceptTreeElement">
							<xsl:with-param name="rek_depth" select="$rek_depth+1"/>
						</xsl:apply-templates>
						
					</div>
				</td>
				
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="propertyTreeElement">
		<xsl:param name="rek_depth" select="1"/>
		
						
		<table class="propertyTreeListColors" border="0" cellspacing="0" cellpadding="0">
			<xsl:if test="$startDepth=1 and $rek_depth=1">
				<xsl:attribute name="width">1000</xsl:attribute>
			</xsl:if> 
			<tr>
				<!-- If startDepth is on first level, do not shift of $param-shift-width-->
				<xsl:if test="$startDepth>1 and not ($rek_depth>1)">
					<td width="{$param-shift-width}"/>
				</xsl:if>
				<!-- For every level below, shift-->
				<xsl:if test="$rek_depth>1">
					<td width="{$param-shift-width}"/>
				</xsl:if>
				<!-- Shift if it is a leaf, because the plus/minus image is missing-->
				<!--  <xsl:if test="@isLeaf">
					<td width="16"/>
				</xsl:if>-->
								
				<td>
			
					<xsl:call-template name="createTreeNode">
						<xsl:with-param name="actionListener" select="'propertyActionListener'"/>
						<xsl:with-param name="typeOfEntity" select="'attribute'"/>
						<xsl:with-param name="rek_depth" select="$rek_depth"/>
					</xsl:call-template>
					
					<!-- Shall we expand all the leaves of the treeview ? no by default-->
					<div>
						<xsl:apply-templates select="propertyPartition">
							<xsl:with-param name="rek_depth" select="$rek_depth+1"/>
						</xsl:apply-templates>
						
						<xsl:call-template name="setExpansionState"/>			
							
						<!-- Thanks to the magic of reccursive calls, all the descendants of the present folder are gonna be built -->
						<xsl:apply-templates select="propertyTreeElement">
							<xsl:with-param name="rek_depth" select="$rek_depth+1"/>
						</xsl:apply-templates>
						
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
		
	
	<xsl:template match="categoryPartition">
	<xsl:param name="rek_depth" select="1"/>
		<xsl:call-template name="partitionNode">
			<xsl:with-param name="actionListener" select="'categoryActionListener'"/>  
			<xsl:with-param name="rek_depth" select="$rek_depth"/>
			<xsl:with-param name="classname" select="'categoryTreeColors'"/>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template match="propertyPartition">
	<xsl:param name="rek_depth" select="1"/>
		<xsl:call-template name="partitionNode">
			<xsl:with-param name="actionListener" select="'propertyActionListener'"/>  
			<xsl:with-param name="rek_depth" select="$rek_depth"/>
			<xsl:with-param name="classname" select="'propertyTreeListColors'"/>
		</xsl:call-template>
	</xsl:template>
	
	
	
	
	
	<xsl:template match="instanceList">
	<xsl:choose>
	 <xsl:when test="not (@isEmpty)">
		<table class="instanceListColors" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
			<xsl:apply-templates select="instance"/>
			<xsl:apply-templates select="instancePartition"/>
		</table>
	 </xsl:when>
	 <xsl:otherwise>
	 	<xsl:value-of select="@textToDisplay"></xsl:value-of>
	 </xsl:otherwise>
	</xsl:choose>
	</xsl:template>
	
	<xsl:template match="instancePartition">
		<xsl:call-template name="partitionNode">
			<xsl:with-param name="actionListener" select="'instanceActionListener'"/>
			<xsl:with-param name="classname" select="'instanceListColors'"/>  
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template match="instance">
		<tr><td><!-- <img src="{$param-img-directory}instance.gif"/> -->
		<a class="instance">
			<xsl:choose>
				<xsl:when test="gissues and @inherited">
					<xsl:attribute name="class">instance gardeningissue inherited</xsl:attribute>
				</xsl:when>
				<xsl:when test="gissues">
					<xsl:attribute name="class">instance gardeningissue</xsl:attribute>
				</xsl:when>
				<xsl:when test="@inherited">
					<xsl:attribute name="class">instance inherited</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:attribute name="onclick">instanceActionListener.selectInstance(event, this, '<xsl:value-of select="@id"/>', '<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
			<xsl:variable name="title" select="@title"/>
			<xsl:value-of select="translate($title, '_', ' ')"/>
			
		</a>
		<xsl:call-template name="gissues">
			<xsl:with-param name="title"><xsl:value-of select="@title"/></xsl:with-param>
			<xsl:with-param name="actionListener">instanceActionListener</xsl:with-param> 
		</xsl:call-template></td>
		<td>
		<a class="navigationLink" style="margin-left:5px;">
			<xsl:attribute name="onclick">instanceActionListener.navigateToEntity(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
			(browse)
		</a>
		
		</td>
		<td>
		 <xsl:if test="@superCat">
			 <a style="margin-left:5px;">
			 	<xsl:attribute name="onclick">instanceActionListener.showSuperCategory(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@superCat"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
			 	 <xsl:variable name="superCategory" select="@superCat"/>
				 &lt;<xsl:value-of select="translate($superCategory, '_', ' ')"></xsl:value-of>&gt;
			 </a>
		</xsl:if>
		</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="annotationsList">
	<xsl:choose>
	<xsl:when test="not (@isEmpty)">
		<table class="propertyTreeListColors" border="0" cellspacing="0" cellpadding="0">
			<xsl:apply-templates select="annotation"/>
			
		</table>
	</xsl:when>
	<xsl:otherwise>
		<xsl:value-of select="@textToDisplay"></xsl:value-of>
	</xsl:otherwise>
	</xsl:choose>
	</xsl:template>
	
	<xsl:template match="annotation">
		<tr>
			<td>
				<xsl:attribute name="rowspan"><xsl:value-of select="count(child::param)"/></xsl:attribute>
				<xsl:variable name="icon" select="@img"/>
				<!-- <img src="{$param-img-directory}../../{$icon}"/>  -->
				<xsl:variable name="title" select="@title"/>
				<a title="{$title}" class="annotation">
					<xsl:attribute name="onclick">annotationActionListener.selectProperty(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
					<xsl:if test="gissues">
						<xsl:attribute name="class">gardeningissue</xsl:attribute>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="string-length($title) > $maximumEntityLength">
							<xsl:value-of select="concat(substring(translate($title, '_', ' '),1,$maximumEntityLength),'...')"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="translate($title, '_', ' ')"/>
						</xsl:otherwise>
					</xsl:choose>
				
				</a>
				<xsl:call-template name="gissues">
					<xsl:with-param name="title"><xsl:value-of select="@title"/></xsl:with-param>
					<xsl:with-param name="actionListener">propertyActionListener</xsl:with-param> 
				</xsl:call-template>
				<a class="navigationLink" title="Goto to {$title}" style="margin-left:5px;">
					<xsl:attribute name="onclick">schemaActionPropertyListener.navigateToEntity(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
					(browse)
				</a>
			</td>
			<td>
				
				 <xsl:choose>
					<xsl:when test="child::param[1][@isLink]">
						<a class="annotation" style="margin-left:5px;">
							<xsl:attribute name="onclick">annotationActionListener.navigateToTarget(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="child::param[1]"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute> 
							<xsl:variable name="target" select="child::param[1]"/>
							<xsl:value-of select="translate($target, '_', ' ')"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="@chemFoEq">
							<xsl:attribute name="chemFoEq">true</xsl:attribute>
						</xsl:if>
						<xsl:value-of disable-output-escaping="yes" select="child::param[1]"/>
					</xsl:otherwise>
				</xsl:choose>
				
			</td>
		</tr>
		<xsl:for-each select="child::param">
			<xsl:if test="position()!=1">
				<tr>
					<td>
						
						<xsl:choose>
							<xsl:when test="@isLink">
								<a class="annotation" style="margin-left:5px;">
									<xsl:attribute name="onclick">annotationActionListener.navigateToTarget(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="."/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
									<xsl:variable name="target" select="."/>
									<xsl:value-of select="translate($target, '_', ' ')"/>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:if test="../@chemFoEq">
									<xsl:attribute name="chemFoEq">true</xsl:attribute>
								</xsl:if>
								<xsl:value-of disable-output-escaping="yes" select="."/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</xsl:if>
		</xsl:for-each> 
	</xsl:template>
	
		
	<xsl:template match="propertyList">
	<xsl:choose>
	<xsl:when test="not (@isEmpty)">
		<table class="propertyTreeListColors" border="0" cellspacing="0" cellpadding="0">
			<xsl:apply-templates select="property"/>
		
		</table>
	</xsl:when>
	<xsl:otherwise>
		<xsl:value-of select="@textToDisplay"></xsl:value-of>
	</xsl:otherwise>
	</xsl:choose>
	</xsl:template>
	
	<xsl:template match="property">
		<tr>
			<xsl:variable name="title" select="@title"/>
			<td>
				<xsl:attribute name="rowspan"><xsl:value-of select="count(child::rangeType)+1"/></xsl:attribute>
				<xsl:variable name="icon" select="@img"/>
				<!-- <img src="{$param-img-directory}../../{$icon}"/> -->
				<a title="{$title}" class="attribute">
					<xsl:attribute name="onclick">schemaActionPropertyListener.selectProperty(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
					<xsl:if test="gissues">
						<xsl:attribute name="class">attribute gardeningissue</xsl:attribute>
					</xsl:if>
					<xsl:if test="@inherited">
						<xsl:attribute name="class">attribute inherited</xsl:attribute>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="string-length($title) > $maximumEntityLength">
							<xsl:value-of select="concat(substring(translate($title, '_', ' '),1,$maximumEntityLength),'...')"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="translate($title, '_', ' ')"/>
						</xsl:otherwise>
					</xsl:choose>
				</a>
				<xsl:call-template name="gissues">
					<xsl:with-param name="title"><xsl:value-of select="@title"/></xsl:with-param>
					<xsl:with-param name="actionListener">propertyActionListener</xsl:with-param> 
				</xsl:call-template>	
			</td>
			<td>
				<xsl:attribute name="rowspan"><xsl:value-of select="count(child::rangeType)+1"/></xsl:attribute>
				<a class="navigationLink" title="Goto to {$title}" style="margin-left:5px;">
					<xsl:attribute name="onclick">schemaActionPropertyListener.navigateToEntity(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
					(browse)
				</a>
				
				
			</td>
			<td>
			<xsl:attribute name="rowspan"><xsl:value-of select="count(child::rangeType)+1"/></xsl:attribute>
			(<xsl:value-of select="@num"/>)
			</td>
			<td align="right">
				<xsl:choose>
					<xsl:when test="child::rangeType[1][@isLink]">
						<a class="category">
							<xsl:attribute name="onclick">categoryActionListener.navigateToEntity(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="."/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
							<xsl:value-of select="child::rangeType[1][@name]"/><xsl:value-of select="child::rangeType[1]"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="child::rangeType[1][@name]"/><xsl:value-of select="child::rangeType[1]"/>
					</xsl:otherwise>
				</xsl:choose>
				
			</td>
			
		</tr>
		<xsl:for-each select="child::rangeType">
				<xsl:if test="position()!=1">
					<tr>
						<td align="right">
							
							<xsl:choose>
								<xsl:when test="@isLink">
								<a class="category">
									<xsl:attribute name="onclick">categoryActionListener.navigateToEntity(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="."/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
									<xsl:value-of select="@name"/><xsl:value-of select="."/>
								</a>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="@name"/><xsl:value-of select="."/>
								</xsl:otherwise>
							</xsl:choose>
							
						</td>
					</tr>
				</xsl:if>
		</xsl:for-each>
				<tr>
					<td align="right">
						
						<xsl:value-of select="@minCard"/>
						...
						<xsl:value-of select="@maxCard"/>
				
						<xsl:choose>
							<xsl:when test="@isTransitive and not (@isSymetrical)">
					     	 , transitive
							</xsl:when>
							<xsl:when test="@isSymetrical and not (@isTransitive)">
						 	 , symetrical
							</xsl:when>
							<xsl:when test="@isSymetrical and @isTransitive">
						 	 , transitive/symetrical
							</xsl:when>
						</xsl:choose>
					</td>
				</tr>
			
	</xsl:template>
	
		
	<xsl:template name="gissues">
	<xsl:param name="title"/>
	<xsl:param name="actionListener"/>
		<xsl:if test="gissues">
			<span class="smwttpersist">
				<span class="smwtticon">warning.png</span>
				<span class="smwttcontent">
				
				<ul>
					<xsl:for-each select="gissues/gi">
					<li>
						<xsl:value-of select="."/>
					</li>
					</xsl:for-each>
				</ul>
			
				</span> 
			</span>
			<a class="navigationLink" title="Edit {$title}" style="margin-left:5px;">
				<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.navigateToEntity(event, this, '<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>', true)</xsl:attribute>
				(edit)
			</a>
		</xsl:if>
	</xsl:template>
	
	
	<xsl:template name="setExpansionState">
		<xsl:if test="@expanded">
							<xsl:if test="@expanded='true'">
								<xsl:attribute name="style">display:block;</xsl:attribute>
							</xsl:if>
							<!-- plus (+) otherwise-->
							<xsl:if test="@expanded='false'">
								<xsl:attribute name="style">display:none;</xsl:attribute>
							</xsl:if>
						</xsl:if>
						
						<xsl:if test="not(@expanded)">
							<xsl:if test="$param-deploy-treeview = 'true'">
								<xsl:attribute name="style">display:block;</xsl:attribute>
							</xsl:if>
							<xsl:if test="$param-deploy-treeview = 'false'">
								<xsl:attribute name="style">display:none;</xsl:attribute>
							</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="createTreeNode">
		<xsl:param name="actionListener"/>
		
		<xsl:param name="typeOfEntity"/>
		<xsl:param name="rek_depth"/>
		<a class="{$typeOfEntity}">
							<xsl:if test="gissues">
								<xsl:attribute name="class"><xsl:value-of select="$typeOfEntity"/> gardeningissue</xsl:attribute>
							</xsl:if>
							<xsl:if test="@id">
										<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.select(event, this,'<xsl:value-of select="@id"/>','<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
										<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
							</xsl:if>
							<xsl:if test="@title">
								<xsl:attribute name="title"><xsl:value-of select="@title"/></xsl:attribute>
							</xsl:if>
						<xsl:if test="$rek_depth=1">
							<xsl:if test="@hidden='true'">
									<xsl:attribute name="style">display: none;</xsl:attribute>
							</xsl:if>												
						</xsl:if>		
						<!-- If the treeview is unfold, the image minus (-) is displayed-->
						
						<xsl:if test="@expanded='true'">
							<xsl:choose>
								<xsl:when test="not (@isLeaf)">
									<img src="{$param-img-directory}minus.gif">
										<xsl:if test="@id">
											<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.toggleExpand(event, this.parentNode, '<xsl:value-of select="@id"/>')</xsl:attribute>
										</xsl:if>
										
									</img>
								</xsl:when>
								<xsl:otherwise>
									<img src="{$param-img-directory}minus.gif">
										<xsl:if test="@id">
											<xsl:attribute name="style">visibility: hidden;</xsl:attribute>
											<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.toggleExpand(event, this.parentNode, '<xsl:value-of select="@id"/>')</xsl:attribute>
										</xsl:if>
										
									</img>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
						<xsl:if test="@expanded='false' or not(@expanded)">
							<!-- plus (+) otherwise-->
							<xsl:choose>
								<xsl:when test="not (@isLeaf)">
									<img src="{$param-img-directory}plus.gif">
										<xsl:if test="@id">
											<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.toggleExpand(event, this.parentNode, '<xsl:value-of select="@id"/>')</xsl:attribute>
										</xsl:if>
										
									</img>
								</xsl:when>
								<xsl:otherwise>
									<img src="{$param-img-directory}plus.gif">
										<xsl:if test="@id">
											<xsl:attribute name="style">visibility: hidden;</xsl:attribute>
											<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.toggleExpand(event, this.parentNode, '<xsl:value-of select="@id"/>')</xsl:attribute>
										</xsl:if>
										
									</img>
								</xsl:otherwise>
							</xsl:choose>
							
						</xsl:if>
						<!-- <xsl:if test="not(@expanded)">
							<xsl:if test="$param-deploy-treeview = 'true'">
								<img src="{$param-img-directory}minus.gif">
									<xsl:if test="@id">
										<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.toggleExpand(event, this.parentNode, '<xsl:value-of select="@id"/>')</xsl:attribute>
									</xsl:if>
									
								</img>
							</xsl:if>
							<xsl:if test="$param-deploy-treeview = 'false' or not(@expanded)">
								<img src="{$param-img-directory}plus.gif">
									<xsl:if test="@id">
										<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.toggleExpand(event, this.parentNode, '<xsl:value-of select="@id"/>')</xsl:attribute>
									</xsl:if>
								
								</img>
							</xsl:if>
						</xsl:if>
						 -->
						<xsl:if test="$rek_depth>1">
							<xsl:choose>
								<xsl:when test="position()=last()">
									<img src="{$param-img-directory}lastlink.gif"/>
								</xsl:when>
								<xsl:otherwise>
									<img src="{$param-img-directory}link.gif"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
						<xsl:if test="$startDepth>1">
							<xsl:choose>
								<xsl:when test="position()=last()">
									<img src="{$param-img-directory}lastlink.gif"/>
								</xsl:when>
								<xsl:otherwise>
									<img src="{$param-img-directory}link.gif"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
						<!-- <img src="{$param-img-directory}{@img}">
							
							<xsl:if test="@alt">
								
								<xsl:if test="$param-is-netscape='true'">
									<xsl:attribute name="title"><xsl:value-of select="@alt"/></xsl:attribute>
								</xsl:if>
								
								<xsl:if test="$param-is-netscape='false'">
									<xsl:attribute name="alt"><xsl:value-of select="@alt"/></xsl:attribute>
								</xsl:if>
							</xsl:if>
						</img> -->
						<xsl:variable name="titleWithoutUnderscore" select="@title"/>
						<xsl:value-of select="translate($titleWithoutUnderscore, '_', ' ')"/>
					</a>
					
					<xsl:variable name="title" select="@title"/>
					<xsl:call-template name="gissues">
						<xsl:with-param name="title"><xsl:value-of select="@title"/></xsl:with-param>
						<xsl:with-param name="actionListener"><xsl:value-of select="$actionListener"/></xsl:with-param> 
					</xsl:call-template> 
					<a class="navigationLink" title="Goto to {$title}" style="margin-left:5px;">
						<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.navigateToEntity(event, this,'<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
						(browse)
					</a>
	</xsl:template>
	
	<xsl:template name="partitionNode">
	<xsl:param name="rek_depth"/>
	<xsl:param name="actionListener"/>
	<xsl:param name="classname"/>
	<table border="0" cellspacing="0" cellpadding="0" class="{$classname}">
			<tr>
				<xsl:if test="$startDepth>1 and not ($rek_depth>1)">
					<td width="{$param-shift-width}"/>
				</xsl:if>
				
				<xsl:if test="$rek_depth>1">
					<td width="{$param-shift-width}"/>
				</xsl:if>
				<td>
				<xsl:if test="not (@hidePreviousArrow)">
				<a>
					<xsl:attribute name="partitionNum"><xsl:value-of select="@partitionNum"/></xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:attribute name="length"><xsl:value-of select="@length"/></xsl:attribute>
					<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.selectPreviousPartition(event, this)</xsl:attribute>
					<img src="{$param-img-directory}pfeil_links.gif"/> 
					prev
				</a>
				</xsl:if>
				</td>
				<td width="5"/>
				<!-- <td><xsl:value-of select="@partitionNum"/></td> -->
				<td width="5"/>
				<td>
				<xsl:if test="not (@hideNextArrow)">
				<a>
					<xsl:attribute name="partitionNum"><xsl:value-of select="@partitionNum"/></xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:attribute name="length"><xsl:value-of select="@length"/></xsl:attribute>
					<xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.selectNextPartition(event, this)</xsl:attribute> 
					next
					<img src="{$param-img-directory}pfeil_rechts.gif"/>
				</a>	
				</xsl:if>
				</td>
			</tr>
		</table>
	</xsl:template>
	
    <xsl:template name="replace-string">
		<xsl:param name="text"/>
		<xsl:param name="from"/>
		<xsl:param name="to"/>
		<xsl:choose>
			<xsl:when test="contains($text, $from)">
				<xsl:variable name="before" select="substring-before($text, $from)"/>
				<xsl:variable name="after" select="substring-after($text, $from)"/>
				<xsl:variable name="prefix" select="concat($before, $to)"/>
				<xsl:value-of select="$before"/>
				<xsl:value-of select="$to"/>
				<xsl:call-template name="replace-string">
					<xsl:with-param name="text" select="$after"/>
					<xsl:with-param name="from" select="$from"/>
					<xsl:with-param name="to" select="$to"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$text"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template> 
	
</xsl:stylesheet>
