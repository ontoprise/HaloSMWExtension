var CREATENEWARTICLE = {
	EMPTY_IN_WIKITEXT : 'Empty Article in WikiText editor',
	EMPTY_IN_WYSIWYG : 'Empty Article in WYSIWYG editor',
	ADD_AND_EDIT_CATEGORY : 'Add and edit Category',
	ADD_AND_EDIT_PROPERTY : 'Add and edit Property',
	ADD_AND_EDIT_TEMPLATE : 'Add and edit Template',
	ADD_AND_EDIT_FORM : 'Add and edit Form',
	CATEGORY_STR : '(Category)',
	FORM_STR : '(Form)',
	BIND_CONTROL_ID : '#createNewArticleCtrl',
	imgPath : wgScriptPath + '/extensions/SMWHalo/skins/CreateNewArticle/',
	lastTitleValue : '',
	fancyBoxContent : function(){
		return '<form action=\"\" method=\"get\">\
			<table id=\"fancyboxTable\">\<tr>\<td colspan=\"2\" class=\"fancyboxTitleTd\"><span>Create New Article</span><img src=\"' + this.imgPath + '_delete.png\"></img></td></tr>\
		<tr><td colspan=\"2\" class=\"userInstructionTd\"><span>Enter the name for the new article:</span></td></tr>\
		<tr><td colspan=\"2\"><input type=\"text\" id=\"newArticleName\" class=\"articleNameInput\"/></td></tr>\
		<tr><td id=\"articleExistTableTd\"><table id=\"articleExistTable\"><tr>\
			<td id=\"errorImgTd\"></td><td class=\"articleExistsErrorTd\"><span id=\"errorMsg\"></span><span id=\"errorLink\"></span></td>\
		</tr></table></td></tr>\
		<tr><td colspan=\"2\" class=\"userInstructionTd\"><span>Select the layout of the new article:</span></td></tr>\
		<tr><td colspan=\"2\"><select id=\"listOfTemplatesAndCategories\" size=\"10\" class=\"templatesAndCategoriesSelect\">\
					<option>' + this.EMPTY_IN_WYSIWYG + '</option>\
					<option>' + this.EMPTY_IN_WIKITEXT + '</option>\
					</select></td>\
		</tr>\
		<tr>\
			<td colspan=\"2\" class=\"layoutDescriptionTd\">\
			<table><tr>\
			<td rowspan=\"2\" id=\"selectedDescImgTd\"></td><td id=\"selectedTitleTd\"></td></tr>\
			<tr><td id=\"selectedDescTd\" class=\"layoutDescriptionSpan\"></td>\
			</tr></table>\
			</td>\
		</tr>\
		<tr>\
			<td colspan=\"2\" class=\"btnTableTd\">\
				<table id=\"btnTable\" class=\"btnTable\">\
					<tr>\
						<td><input type=\"submit\" value=\"OK\" id=\"cna_submitBtn\"/></td>\
						<td>|</td>\
						<td><a id=\"cna_cancelBtn\">Cancel</a></td>\
					</tr>\
				</table>\
			</td>\
		</tr>\
	</table>\
	</form>';
	},
	
	initForm : function(form, articleTitle, creationMathod){
		var actionUrl;
		form.remove(':hidden');
		switch(creationMathod){
			case this.EMPTY_IN_WIKITEXT:
				form.attr('action', wgServer + wgScriptPath + '/index.php');
				form.append('<input type="hidden" name="title" value="' + articleTitle + '">');
				form.append('<input type="hidden" name="action" value="edit">');
				break;
				
			case this.EMPTY_IN_WYSIWYG:
				form.attr('action', wgServer + wgScriptPath + '/index.php');
				form.append('<input type="hidden" name="title" value="' + articleTitle + '">');
				form.append('<input type="hidden" name="action" value="edit">');
				form.append('<input type="hidden" name="mode" value="wysiwyg">');
				break;
				
			default: 
				if(creationMathod.indexOf(this.CATEGORY_STR) > 0){
					var category = jQuery("#listOfTemplatesAndCategories option:selected").val();
					category = category.substring(0, category.indexOf(this.CATEGORY_STR));
					form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit');
					form.append('<input type="hidden" name="target" value="' + articleTitle + '">');
					form.append('<input type="hidden" name="categories" value="' + category + '">');
				}
				else if(creationMathod.indexOf(this.FORM_STR) > 0){
					var formName = jQuery("#listOfTemplatesAndCategories option:selected").val();
					formName = formName.substring(0, formName.indexOf(this.FORM_STR));
					form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormStart');
					form.append('<input type="hidden" name="page_name" value="' + articleTitle + '">');
					form.append('<input type="hidden" name="form" value="' + formName + '">');
				}
				break;
		}
	},
	
	setRationaleDescription : function(selectedValue){
		switch(selectedValue){
			case this.EMPTY_IN_WIKITEXT:
				jQuery('#selectedTitleTd').html(selectedValue + ':');
				jQuery('#selectedDescTd').text('Create an empty article in WikiText editor');
				jQuery('#selectedDescImgTd').html('<img src="' + this.imgPath + 'info.png"/>');
//				jQuery.fancybox.resize();
				jQuery('#listOfTemplatesAndCategories').focus();
				jQuery.fancybox.hideActivity();
				break;
				
			case this.EMPTY_IN_WYSIWYG:
				jQuery('#selectedTitleTd').html(selectedValue + ':');
				jQuery('#selectedDescTd').text('Create an empty article in WYSIWYG editor');
				jQuery('#selectedDescImgTd').html('<img src="' + this.imgPath + 'info.png"/>');
//				jQuery.fancybox.resize();
				jQuery('#listOfTemplatesAndCategories').focus();
				jQuery.fancybox.hideActivity();
				break;
				
			default:
				var titleString;
				var categoryIndex = selectedValue.indexOf(this.CATEGORY_STR);
				var formIndex = selectedValue.indexOf(this.FORM_STR);
				if(categoryIndex > 0){
					titleString = 'Category:' + selectedValue.substr(0, categoryIndex);
				}
				else if(formIndex > 0){
					titleString = 'Form:' + selectedValue.substr(0, formIndex);
				}
				jQuery.fancybox.showActivity();	
				sajax_do_call('cna_getPropertyValue', [titleString, 'Rationale'], function(request){
					var formStr = 'Form:';
					var categoryStr = 'Category:';
					var responseText = request.responseText.split(';');
					var title = responseText[1];
					var formIndex = title.indexOf(formStr);
					var categoryIndex = title.indexOf(categoryStr);
					if(formIndex === 0)
						title = title.substr(formStr.length, title.length - 1);
					else if(categoryIndex === 0)
						title = title.substr(categoryStr.length, title.length - 1);
					
					if(jQuery('#listOfTemplatesAndCategories option:selected').val().indexOf(title) == 0){
						jQuery('#selectedTitleTd').html(selectedValue + ':');
						jQuery('#selectedDescTd').html(request.responseText);
						jQuery('#selectedDescImgTd').html('<img src="' + CREATENEWARTICLE.imgPath + 'info.png"/>');
	//					jQuery.fancybox.resize();
						jQuery.fancybox.hideActivity();
					}
					jQuery('#listOfTemplatesAndCategories').focus();
					
				});
				break;
			}
	},
						
						
	buildListOfFormsAndCategories : function(){
		var forms;
		var categories;
		var listBox = jQuery('#listOfTemplatesAndCategories');
		
		//ajax call to get a list of forms
		jQuery.fancybox.showActivity();	
		sajax_do_call('cna_getForms', [''], function(request){
			forms = request.responseText;
			forms = forms.split(',');
			forms = jQuery.grep(forms, function(element, index){
				  return (element);
				});
			for(i = 0; forms && i < forms.length; i++){
				forms[i] += '  (Form)';
			}
			//ajax call to get a list of categories
			sajax_do_call('cna_getCategories', [''], function(request){
				categories = request.responseText;
				categories = categories.split('Category:');
				categories = jQuery.grep(categories, function(element, index){
					  return (element);
					});
				for(i = 0; categories && i < categories.length; i++){
					categories[i] += '  (Category)';
				}
				var mergedArray = jQuery.merge(forms, categories);
				mergedArray.sort(function(a, b) {
					   var compA = a.toUpperCase();
					   var compB = b.toUpperCase();
					   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
					});

				for(i = 0; mergedArray && i < mergedArray.length; i++){
					listBox.append('<option>' + mergedArray[i] + '</option>')
				}
				jQuery.fancybox.hideActivity();
			});
		});
		
		
	},
	
	shorterString : function(theString, numOfLetters){
		if(theString && jQuery.trim(theString).length > numOfLetters){
			theString = theString.substr(0, numOfLetters - 3);
			theString += '...';
		}
		return theString;
	},
	
	validate : function(){
		if(jQuery('#newArticleName').val() && jQuery('#listOfTemplatesAndCategories option:selected').val()){
			jQuery('#cna_submitBtn').removeAttr('disabled');
		}
		else{
			jQuery('#cna_submitBtn').attr('disabled', 'disabled');
		}
	}
}



jQuery(document).ready(function() {
	if(jQuery.query.get('todo').toLowerCase() === 'createnewarticle'){
		jQuery.fancybox({ 
			'content'  : CREATENEWARTICLE.fancyBoxContent(),
			'modal'  : true,
			'showCloseButton'	: true,
			'width'		: '75%',
			'height'	: '75%',
			'autoScale'	: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'overlayColor'  : '#222',
			'overlayOpacity' : '0.8',
			'scrolling' : 'no',
			'onCleanup'  : function(){
				document.location.search = jQuery.query.remove('todo');
			},
			'onComplete'  : function(){
				//build list of forms and categories
				CREATENEWARTICLE.buildListOfFormsAndCategories();
				
				var articleNameTextBox = jQuery('#newArticleName');
				var articleExistsErrorMsgSpan = jQuery('#errorMsg');
				var articleExistsErrorLinkSpan = jQuery('#errorLink');
				var articleExistsErrorImgTd = jQuery('#errorImgTd');
				var inputBox = jQuery('#newArticleName');
				articleNameTextBox.val(jQuery.query.get('newarticletitle'));
				articleNameTextBox.focus();
			
				articleNameTextBox.keyup(function(event){
//					var keycode = event.which;
					CREATENEWARTICLE.validate();
					var articleTitle = articleNameTextBox.val();
					if(!jQuery('#newArticleName').val()){
						articleExistsErrorImgTd.empty();
						articleExistsErrorMsgSpan.empty();
						articleExistsErrorLinkSpan.empty();
						inputBox.removeClass('redInputBox');
						inputBox.removeClass('greenInputBox');
						inputBox.addClass('whiteInputBox');						
					}
//					else if(keycode !== 37 && keycode !== 39){
					else{
						jQuery.fancybox.showActivity();	
						sajax_do_call('cna_articleExists', [articleTitle], function(request){
							var articleExists = request.responseText;
							articleExists = articleExists.split(';');
							if(jQuery('#newArticleName').val() === articleExists[1]){
								if(articleExists[0] !== 'false'){
									articleExistsErrorImgTd.html('<img src=\"' + CREATENEWARTICLE.imgPath + 'warning.png\"/>');
									articleExistsErrorMsgSpan.html('This page name already exists. You can enter a different article name or open this article: ');
									articleExistsErrorLinkSpan.html('<a href=\"' + wgServer + wgScriptPath + '/index.php/' + articleTitle + '\">' + CREATENEWARTICLE.shorterString(articleTitle, 20) + '</a>');
									inputBox.removeClass('greenInputBox');
									inputBox.removeClass('whiteInputBox');			
									inputBox.addClass('redInputBox');
									
								}
								else if(jQuery('#newArticleName').val()){
									articleExistsErrorImgTd.empty();
									articleExistsErrorMsgSpan.empty();
									articleExistsErrorLinkSpan.empty();
									inputBox.removeClass('redInputBox');
									inputBox.removeClass('whiteInputBox');	
									inputBox.addClass('greenInputBox');
								}
								else{
									articleExistsErrorImgTd.empty();
									articleExistsErrorMsgSpan.empty();
									articleExistsErrorLinkSpan.empty();
									inputBox.removeClass('redInputBox');
									inputBox.removeClass('greenInputBox');
									inputBox.addClass('whiteInputBox');		
								}
								jQuery.fancybox.hideActivity();
							}
						});
					}
				});
				
				articleNameTextBox.trigger('keyup');
				
				jQuery('#cna_cancelBtn').click(function() {
					jQuery.fancybox.close();
				});
				
				jQuery('#cna_submitBtn').click(function() {
					CREATENEWARTICLE.initForm(jQuery('form'), jQuery('#newArticleName').val(), jQuery('#listOfTemplatesAndCategories option:selected').val());
					return true;
				});
				
				jQuery('#listOfTemplatesAndCategories').change(function()
				{
					CREATENEWARTICLE.setRationaleDescription(jQuery('#listOfTemplatesAndCategories option:selected').val());
					CREATENEWARTICLE.validate();
				});
				
				jQuery('.fancyboxTitleTd img').click(function()
				{
					jQuery.fancybox.close();
				});
				
				jQuery.fancybox.resize();
				jQuery.fancybox.center();
				
			}
	}).trigger('click');
	}
		
	

	jQuery(CREATENEWARTICLE.BIND_CONTROL_ID).click(function(event) {
		document.location.search = jQuery.query.set('todo', 'createnewarticle');
		event.preventDefault();
	});
});