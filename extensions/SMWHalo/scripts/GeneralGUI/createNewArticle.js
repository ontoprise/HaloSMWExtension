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
	fancyBoxContent : function(){
		return '<form action=\"\" method=\"get\">\
			<table>\<tr>\<td class=\"fancyboxTitleTd\"><span>Create New Article</span></td></tr>\
		<tr><td class=\"userInstructionTd\"><span>Enter the name for the new article:</span></td></tr>\
		<tr><td><input type=\"text\" id=\"newArticleName\" class=\"articleNameInput\"/></td>\
		</tr><tr><td class=\"articleExistsErrorTd\"><span id=\"errorMsg\"></span><span id=\"errorLink\"></span></td></tr>\
		<tr><td class=\"userInstructionTd\"><span>Select the layout of the new article:</span></td></tr>\
		<tr><td><select id=\"listOfTemplatesAndCategories\" size=\"10\" class=\"templatesAndCategoriesSelect\">\
					<option>' + this.EMPTY_IN_WYSIWYG + '</option><option>' + this.EMPTY_IN_WIKITEXT + '</option><option>' + this.ADD_AND_EDIT_CATEGORY + '</option><option>' + this.ADD_AND_EDIT_PROPERTY + '</option><option>' + this.ADD_AND_EDIT_TEMPLATE + '</option><option>' + this.ADD_AND_EDIT_FORM + '</option></select></td>\
		</tr>\
		<tr>\
			<td class=\"layoutDescriptionTd\"><table><tr>\
			<td rowspan=\"2\"></td><td>Empty article:</td>\
					</tr><tr>\
					<td class=\"layoutDescriptionSpan\">Create an empty new article without any formating. You can use Richtext or Wikitext to enter data.</td>\
					</tr></table></td>\
		</tr>\
		<tr>\
			<td class=\"btnTableTd\">\
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
				
			case this.ADD_AND_EDIT_CATEGORY:
				form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit/Add_and_edit_Category/' + articleTitle);
				break;
				
			case this.ADD_AND_EDIT_PROPERTY:
				form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit/Add_and_edit_Property/' + articleTitle);
				break;
				
			case this.ADD_AND_EDIT_TEMPLATE:
				form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit/Add_and_edit_Template/' + articleTitle);
				break;
				
			case this.ADD_AND_EDIT_FORM:
				form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit/Add_and_edit_Form/' + articleTitle);
				break;
			
			default: 
				if(creationMathod.indexOf(CREATENEWARTICLE.CATEGORY_STR) > 0){
					var category = jQuery("#listOfTemplatesAndCategories option:selected").val();
					category = category.substring(0, category.indexOf(CREATENEWARTICLE.CATEGORY_STR));
					form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit');
					form.append('<input type="hidden" name="target" value="' + articleTitle + '">');
					form.append('<input type="hidden" name="categories" value="' + category + '">');
				}
				else if(creationMathod.indexOf(CREATENEWARTICLE.FORM_STR) > 0){
					var formName = jQuery("#listOfTemplatesAndCategories option:selected").val();
					formName = formName.substring(0, formName.indexOf(CREATENEWARTICLE.FORM_STR));
					form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormStart');
					form.append('<input type="hidden" name="page_name" value="' + articleTitle + '">');
					form.append('<input type="hidden" name="form" value="' + formName + '">');
				}
				break;
		}
	},
						
						
	buildListOfFormsAndCategories : function(){
		//get list of forms
		var forms;
		var categories;
		var listBox = jQuery('#listOfTemplatesAndCategories');
		sajax_do_call('cna_getForms', [''], function(request){
			forms = request.responseText;
			forms = forms.split(',');
			for(i = 0; forms && i < forms.length; i++){
				if(forms[i]){
					listBox.append('<option>' + forms[i] + ' (Form)</option>')
				}
			}
			//get list of categories
			sajax_do_call('cna_getCategories', [''], function(request){
				categories = request.responseText;
				categories = categories.split(',');
				for(i = 0; categories && i < categories.length; i++){
					var category = categories[i].split(':')[1];
					listBox.append('<option>' + category + ' (Category)</option>')
				}
			});
		});
		
		
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
			'scrolling' : 'auto',
			'onCleanup'  : function(){
				document.location.search = jQuery.query.remove('todo');
			},
			'onComplete'  : function(){
				//build list of forms and categories
				CREATENEWARTICLE.buildListOfFormsAndCategories();
				
				var articleNameTextBox = jQuery('#newArticleName');
				var articleExistsErrorMsgSpan = jQuery('#errorMsg');
				var articleExistsErrorLinkSpan = jQuery('#errorLink');
				var submitBtn = jQuery("#cna_submitBtn");
				articleNameTextBox.val(jQuery.query.get('title'));
				articleNameTextBox.focus();
			
				articleNameTextBox.keyup(function(){
					
					var articleTitle = articleNameTextBox.val();
						sajax_do_call('smwf_om_ExistsArticle', [articleTitle], function(request){
							var articleExists = request.responseText;
							if(articleTitle !== '' && articleExists !== 'false'){
								articleExistsErrorMsgSpan.removeAttr('display');
								articleExistsErrorLinkSpan.removeAttr('display');
								articleExistsErrorMsgSpan.html('This page name already exists. You can enter a different article name or open this article: ');
								articleExistsErrorLinkSpan.html('<a href=\"' + wgServer + wgScriptPath + '/index.php/' + articleTitle + '\">' + articleTitle + '</a>');
								submitBtn.attr('disabled', 'disabled');
								jQuery.fancybox.resize();
								articleNameTextBox.focus();
							}
							else{
								articleExistsErrorMsgSpan.attr('display', 'none');
								articleExistsErrorLinkSpan.attr('display', 'none');
								articleExistsErrorMsgSpan.empty();
								articleExistsErrorLinkSpan.empty();
								submitBtn.removeAttr('disabled');
								jQuery.fancybox.resize();
								articleNameTextBox.focus();
							}
						});
				});
				
				articleNameTextBox.trigger('keyup');
				
				jQuery("#cna_cancelBtn").live('click', function() {
					jQuery.fancybox.close();
				});
				
				jQuery("#cna_submitBtn").live('click', function() {
					CREATENEWARTICLE.initForm(jQuery('form'), jQuery('#newArticleName').val(), jQuery("#listOfTemplatesAndCategories option:selected").val());
					return true;
				});
			}
	}).trigger('click');
	}
		
	

	jQuery(CREATENEWARTICLE.BIND_CONTROL_ID).live('click', function(event) {
		document.location.search = jQuery.query.set('todo', 'createnewarticle');
		event.preventDefault();
	});
});