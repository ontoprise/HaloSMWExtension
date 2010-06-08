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
    <xsl:param name="param-wiki-path" select="''"/>
    <xsl:param name="param-ns-concept" select="''"/>
    <xsl:param name="param-ns-property" select="''"/>
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
                                        
                    <xsl:apply-templates select="ruleTreeElement">
                        <xsl:with-param name="rek_depth" select="1"/>
                    </xsl:apply-templates>
                    
              
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
    <xsl:template match="ruleTreeElement">
        <xsl:param name="rek_depth" select="1"/>
        
                        
        <table class="ruleTreeColors" border="0" cellspacing="0" cellpadding="0">
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
                        <xsl:with-param name="actionListener" select="'ruleActionListener'"/>
                        <xsl:with-param name="typeOfEntity" select="'rule'"/>
                        <xsl:with-param name="rek_depth" select="$rek_depth"/>
                    </xsl:call-template>
                    
                    <!-- Shall we expand all the leaves of the treeview ? no by default-->
                    <div>
                        
                        
                        <xsl:call-template name="setExpansionState"/>           
                            
                        <!-- Thanks to the magic of reccursive calls, all the descendants of the present folder are gonna be built -->
                        <xsl:apply-templates select="ruleTreeElement">
                            <xsl:with-param name="rek_depth" select="$rek_depth+1"/>
                        </xsl:apply-templates>
                        
                      
                    </div>
                </td>
                
            </tr>
        </table>
    </xsl:template>
    
    <xsl:template match="ruleMetadata">
     <span id="ruleList-id">  <xsl:if test="@title">
                               <xsl:value-of select="@title"/>
                            </xsl:if> </span>
     <span id="ruleList-ruletext"/>
     <span id="ruleList-native"/>
     <span id="ruleList-active"/>
     <span id="ruleList-type"/>
     <span id="ruleList-stylized"/>
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
                          
                            <xsl:if test="@id">
                                        <xsl:attribute name="onclick"><xsl:value-of select="$actionListener"/>.select(event, this,'<xsl:value-of select="@id"/>','<xsl:call-template name="replace-string"><xsl:with-param name="text" select="@title_url"/><xsl:with-param name="from" select="$var-simple-quote"/><xsl:with-param name="to" select="$var-slash-quote"/></xsl:call-template>')</xsl:attribute>
                                        <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
                            </xsl:if>
                            <xsl:if test="@title">
                                <xsl:attribute name="title"><xsl:value-of select="@title"/></xsl:attribute>
                            </xsl:if>
                            <xsl:if test="@title_url">
                                <xsl:attribute name="title"><xsl:value-of select="@title_url"/></xsl:attribute>
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
                 
                    <a class="navigationLink" title="Goto to {$title}" style="margin-left:5px;">
                        <xsl:choose>
                        <xsl:when test="$typeOfEntity='concept'">
                            <xsl:attribute name="href"><xsl:value-of select="substring-before($param-wiki-path,'$1')"/><xsl:value-of select="$param-ns-concept"/>:<xsl:value-of select="@title_url"/></xsl:attribute> 
                        </xsl:when>
                        <xsl:when test="$typeOfEntity='property'">
                            <xsl:attribute name="href"><xsl:value-of select="substring-before($param-wiki-path,'$1')"/><xsl:value-of select="$param-ns-property"/>:<xsl:value-of select="@title_url"/></xsl:attribute> 
                        </xsl:when>
                        </xsl:choose>
                        {{SMW_OB_OPEN}} 
                    </a>
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
