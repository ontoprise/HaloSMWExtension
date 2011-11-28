/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

CKEDITOR.dialog.add( 'SMWtoolbar', function( editor ) {

	return {
        title: 'Semantic Toolbar',
        minWidth : 390,
		minHeight : 230,
        buttons: [
            CKEDITOR.dialog.okButton
        ],
		contents : [
			{
				id : 'tab1',
				label : '',
				title : '',
				expand : true,
				padding : 0,
				elements : [
					{
						type: 'html',
                        html:
                            '<style type="text/css">' +
								'.cke_stb_container' +
								'{' +
									'color:#000 !important;' +
									'padding:10px 10px 0;' +
									'margin-top:5px' +
								'}' +
								'.cke_stb_container p' +
								'{' +
									'margin: 0 0 10px;' +
								'}' +
								'.cke_stb_container a' +
								'{' +
									'cursor:pointer !important;' +
									'color:blue !important;' +
									'text-decoration:underline !important;' +
								'}' +
							'</style>' +
							'<div class="cke_stb_container">' +
                                '<p>' +
                                    'You have requested to open the Semantic Toolbar, which is helpful if you want<br/>' +
                                    'to tag data in your articles. Once tagged, the data can be retrieved using the<br/>'+
                                    'QueryInterface' +
                                '</p>' +
                                '<p>' +
                                    'In order to use this feature you require the "Halo extension" which you<br/>'+
                                    'can download here for free: click here to download<br/>'+
                                    '(<a href="http://smwforum.ontoprise.com/smwforum/index.php/HaloExtension_Product_Information">'+
                                    'http://smwforum.ontoprise.com/smwforum/index.php/HaloExtension_Product_Information</a>)' +
                                '</p>' +
                            '</div>'
					}
				 ]
			}
		 ]

	};

} );
