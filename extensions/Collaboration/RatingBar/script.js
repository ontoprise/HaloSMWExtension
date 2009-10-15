/**********************************************************************
** This file is part of the Rating Bar extension for MediaWiki
** Copyright (C)2009
**                - PatheticCockroach <www.patheticcockroach.com>
**                - Franck Dernoncourt <www.francky.me>
**
** Home Page : http://www.wiki4games.com
**
** This program is free software; you can redistribute it and/or
** modify it under the terms of the GNU General Public License
** as published by the Free Software Foundation; either
** version 3 of the License, or (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
** <http://www.gnu.org/licenses/>
*********************************************************************/

/*******************************************************************
** query2page is a basic AJAX function mainly based on
** W3Schools ajax tutorial
** Source: http://www.w3schools.com/ajax/ajax_server.asp
********************************************************************/
function query2page(full_query,target_id,target_type,display_type)
{
var target_type = (target_type == null) ? 1 : target_type;
var display_type = (display_type == null) ? "gradbar" : display_type;
var xmlHttp;
try
	{
	// Firefox, Opera 8.0+, Safari
	xmlHttp=new XMLHttpRequest();
	}
catch (e)
	{
	// Internet Explorer
	try
		{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
		try
			{
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
		catch (e)
			{
			alert("Your browser does not support AJAX!");
			return false;
			}
		}
  }
  
xmlHttp.onreadystatechange=function()
	{
	if(xmlHttp.readyState==4)
		{
		if(target_type==1||target_type==2) document.getElementById(target_id).innerHTML=xmlHttp.responseText;
		if(target_type==2)
			{
			//user_rating=document.getElementById("w4g_rating_value").innerHTML;
			user_rating = (document.getElementById("w4g_rating_value").innerHTML == null) ? 0 : document.getElementById("w4g_rating_value").innerHTML;
			if(display_type=="gradbar") updatebox("rating_target",user_rating);
			else if(display_type=="stars") updateStars("w4g_rb_starbox1",user_rating);
			}
		if(target_type==9) document.getElementById(target_id).value=xmlHttp.responseText;
		}
	}
	
	xmlHttp.open("GET",full_query,true);
	xmlHttp.send(null);
}

/*******************************************************************
** This function fills a div ("parent_id") with 101 little subdivs
********************************************************************/
function loadbox(parent_id)
{
	var output="";
	for(var i=0;i<=100;i++)
	{
		output+="<div class=\"w4g_rb_global w4g_rb_col"+i+"\" id=\"w4g_rb_id"+i+"\" style=\"margin-left:"+i*2+"px;\" ";
		output+=" onmouseover=\"updatebox(\'"+parent_id+"\',"+i+")\" ";
		output+=" onclick=\"user_rating="+i+";query2page(query_url+\'&vote="+i+"\',\'w4g_rb_area\')\"></div>";
	}
	document.getElementById(parent_id).innerHTML=output;
}

/*******************************************************************
** This function changes the colors of the 101 little subdivs
** The parent_id parameter has currently no use
** rating_val: a number ranging from 0 to 100 indicating the last
** colored div
********************************************************************/
function updatebox(parent_id,rating_val)
{
	var rating_val = (rating_val == null) ? 50 : rating_val;
	for(var i=0;i<=100;i++)
	{
		var red=250-5*Math.max(0,i-50);
		var green=Math.min(250,i*5);
		var blue=0;
		if(i<=rating_val) document.getElementById("w4g_rb_id"+i).style.backgroundColor="rgb("+red+","+green+","+blue+")";
		else document.getElementById("w4g_rb_id"+i).style.backgroundColor="#555555";
	}
	document.getElementById("rating_text").innerHTML="&nbsp;"+rating_val+"%";
}

/*******************************************************************
** This function fills a div ("parent_id") with 5 starred subdivs
********************************************************************/
function loadStars(parent_id)
{
	var output="";
	for(var i=1;i<=5;i++)
	{
		output+="<div class=\"w4g_rb_star_unit\" id=\"w4g_rb_star_unit_1_"+i+"\" style=\"margin-left:"+(i-1)*30+"px;\" ";
		output+=" onmouseover=\"updateStars(\'"+parent_id+"\',"+i*20+")\" ";
		output+=" onclick=\"user_rating="+i*20+";query2page(query_url+\'&vote="+i*20+"\',\'w4g_rb_area\')\"></div>";
	}
	document.getElementById(parent_id).innerHTML=output;
}

/*******************************************************************
** This function changes the background of starred subdivs
** The parent_id parameter has currently no use
** rating_val: a number ranging from 0 to 100 indicating the last
** colored star (must be divided by 20 for 5 stars)
********************************************************************/
function updateStars(parent_id,rating_val)
{
	var rating_val = (rating_val == null) ? 0 : rating_val;
	max_star=Math.floor(rating_val/20);
	for(var i=1;i<=5;i++)
	{
		if(i<=max_star) document.getElementById("w4g_rb_star_unit_1_"+i).className="w4g_rb_star_hover";
		else document.getElementById("w4g_rb_star_unit_1_"+i).className="w4g_rb_star_unit";
	}
}