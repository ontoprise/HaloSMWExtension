(function($){
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
    INVALID_TITLE: 'invalid_title',
    TITLE_HELP_URL: 'http://meta.wikimedia.org/wiki/Help:Page_name#Restrictions',
    MOREINFO: 'more info',
    imgPath : mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/CreateNewArticle/',
    articleExists : false,
    articleTimeoutId : 0,
    categoryTimeoutId : 0,
    fancyBoxContent : function(){
      return '<form action="" method="get" id="createNewArticleForm">\
			<table id="fancyboxTable">\<tr>\<td colspan="2" class="fancyboxTitleTd">Create New Article</td></tr>\
		<tr><td colspan="2" class="userInstructionTd"><span>Enter the name for the new article:</span></td></tr>\
		<tr><td colspan="2"><input type="text" id="newArticleName" class="articleNameInput" autocomplete="OFF"></input></td></tr>\
		<tr><td id="articleExistTableTd"><table id="articleExistTable"><tr>\
			<td id="errorImgTd"></td><td class="articleExistsErrorTd"><span id="errorMsg"></span><span id="errorLink"></span></td>\
		</tr></table></td></tr>\
		<tr><td colspan="2" class="userInstructionTd"><span>Select the layout of the new article:</span></td></tr>\
		<tr><td colspan="2"><select id="listOfTemplatesAndCategories" size="10" class="templatesAndCategoriesSelect">\
					<option>' + this.EMPTY_IN_WYSIWYG + '</option>\
					<option>' + this.EMPTY_IN_WIKITEXT + '</option>\
					</select></td>\
		</tr>\
		<tr>\
			<td colspan="2" class="layoutDescriptionTd">\
			<table><tr>\
			<td rowspan="2" id="selectedDescImgTd"></td><td id="selectedTitleTd"></td></tr>\
			<tr><td id="selectedDescTd" class="layoutDescriptionTd"></td>\
			</tr></table>\
			</td>\
		</tr>\
		<tr>\
			<td colspan="2" class="btnTableTd">\
				<table id="btnTable" class="btnTable">\
					<tr>\
						<td><input type="submit" value="OK" id="cna_submitBtn"/></td>\
						<td>|</td>\
						<td><a id="cna_cancelBtn">Cancel</a></td>\
					</tr>\
				</table>\
			</td>\
		</tr>\
	</table>\
	</form>';
    },

    /**
     * Encode html entities e.g. & => &amp;
     * For some reason it skips double quots.
     */
    encodeHtmlEntities: function(string){
      var result = '';
      if(string){
        result = $('<div/>').text(string).html();
      }
      return result.replace(/\"/g, '&quot;');
    },

    initForm : function(form, articleTitle, creationMathod){
      form.remove(':hidden');
      switch(creationMathod){
        case this.EMPTY_IN_WIKITEXT:
          form.attr('action', wgServer + wgScriptPath + '/index.php');
          form.append('<input type="hidden" name="title" value="' + this.encodeHtmlEntities(articleTitle) + '"/>');
          form.append('<input type="hidden" name="action" value="edit"/>');
          break;

        case this.EMPTY_IN_WYSIWYG:
          form.attr('action', wgServer + wgScriptPath + '/index.php');
          form.append('<input type="hidden" name="title" value="' + this.encodeHtmlEntities(articleTitle) + '">');
          form.append('<input type="hidden" name="action" value="edit"/>');
          form.append('<input type="hidden" name="mode" value="wysiwyg"/>');
          break;

        default:
          if(creationMathod.indexOf(this.CATEGORY_STR) > 0){
            var category = $("#listOfTemplatesAndCategories option:selected").val();
            category = category.substring(0, category.indexOf(this.CATEGORY_STR));
            form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormEdit');
            form.append('<input type="hidden" name="target" value="' + this.encodeHtmlEntities(articleTitle) + '"/>');
            form.append('<input type="hidden" name="categories" value="' + category + '"/>');
          }
          else if(creationMathod.indexOf(this.FORM_STR) > 0){
            var formName = $("#listOfTemplatesAndCategories option:selected").val();
            formName = formName.substring(0, formName.indexOf(this.FORM_STR));
            form.attr('action', wgServer + wgScriptPath + '/index.php/Special:FormStart');
            form.append('<input type="hidden" name="page_name" value="' + this.encodeHtmlEntities(articleTitle) + '"/>');
            form.append('<input type="hidden" name="form" value="' + formName + '"/>');
          }
          break;
      }
    },

    articleTitleChange : function(){
      var articleExistsErrorMsgSpan = $('#errorMsg');
      var articleExistsErrorLinkSpan = $('#errorLink');
      var articleExistsErrorImgTd = $('#errorImgTd');
      var articleTitleTextBox = $('#newArticleName');
      
      CREATENEWARTICLE.validate();
      var articleTitle = articleTitleTextBox.val();
      if(!articleTitle){
        if(CREATENEWARTICLE.articleTimeoutId){
          window.clearTimeout(CREATENEWARTICLE.articleTimeoutId);
        }
        articleExistsErrorImgTd.empty();
        articleExistsErrorMsgSpan.empty();
        articleExistsErrorLinkSpan.empty();
        articleTitleTextBox.removeClass('redInputBox');
        articleTitleTextBox.removeClass('greenInputBox');
        articleTitleTextBox.addClass('whiteInputBox');

        CREATENEWARTICLE.hideActivity();
      }
      else{
        CREATENEWARTICLE.showActivity();
        if(CREATENEWARTICLE.articleTimeoutId){
          window.clearTimeout(CREATENEWARTICLE.articleTimeoutId);
        }
        CREATENEWARTICLE.articleTimeoutId = window.setTimeout(function(){
          sajax_do_call('smwf_na_articleExists', [articleTitle], function(request){
            //check if title input is not empty when ajax call returns
            if(articleTitleTextBox.val()){
              var response = request.responseText;
              //handle invalid title
              if(response === CREATENEWARTICLE.INVALID_TITLE){
                CREATENEWARTICLE.articleExists = true;
                articleExistsErrorImgTd.html('<img src="' + CREATENEWARTICLE.imgPath + 'warning.png"/>');
                articleExistsErrorMsgSpan.html('Invalid characters in title');
                articleExistsErrorLinkSpan.html(' [<a href="' + CREATENEWARTICLE.TITLE_HELP_URL + '">' + CREATENEWARTICLE.MOREINFO + '</a>]');
                articleTitleTextBox.removeClass('greenInputBox');
                articleTitleTextBox.removeClass('whiteInputBox');
                articleTitleTextBox.addClass('redInputBox');
              }
              else{
                var articleExists = response;
                articleExists = articleExists.split('}');
                if($('#newArticleName').val() === articleExists[1]){
                  //handle title already exists
                  if(articleExists[0] !== 'false'){
                    CREATENEWARTICLE.articleExists = true;
                    articleExistsErrorImgTd.html('<img src="' + CREATENEWARTICLE.imgPath + 'warning.png"/>');
                    articleExistsErrorMsgSpan.html('This page name already exists. You can enter a different article name or open this article: ');
                    articleExistsErrorLinkSpan.html('<a href="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php/' + articleTitle + '">' + CREATENEWARTICLE.shorterString(articleTitle, 20) + '</a>');
                    articleTitleTextBox.removeClass('greenInputBox');
                    articleTitleTextBox.removeClass('whiteInputBox');
                    articleTitleTextBox.addClass('redInputBox');
                  }
                  else{
                    //handle title doesn't exist
                    CREATENEWARTICLE.articleExists = false;
                    articleExistsErrorImgTd.empty();
                    articleExistsErrorMsgSpan.empty();
                    articleExistsErrorLinkSpan.empty();
                    articleTitleTextBox.removeClass('redInputBox');
                    articleTitleTextBox.removeClass('whiteInputBox');
                    articleTitleTextBox.addClass('greenInputBox');
                  }
                }
              }
            }
            CREATENEWARTICLE.hideActivity();
          })
        }, 500);
      }
    },

    setRationaleDescription : function(selectedValue){
      selectedValue = selectedValue || '';
      switch(selectedValue){
        case this.EMPTY_IN_WIKITEXT:
          $('#selectedTitleTd').html(selectedValue + ':');
          $('#selectedDescTd').text('Create an empty article in WikiText editor');
          $('#selectedDescImgTd').html('<img src="' + this.imgPath + 'info.png"/>');
          $('#listOfTemplatesAndCategories').focus();
          CREATENEWARTICLE.hideActivity();
          $('#newArticleName').removeAttr('disabled');
          break;

        case this.EMPTY_IN_WYSIWYG:
          $('#selectedTitleTd').html(selectedValue + ':');
          $('#selectedDescTd').text('Create an empty article in WYSIWYG editor');
          $('#selectedDescImgTd').html('<img src="' + this.imgPath + 'info.png"/>');
          $('#listOfTemplatesAndCategories').focus();
          CREATENEWARTICLE.hideActivity();
          $('#newArticleName').removeAttr('disabled');
          break;

        default:
          var titleString;
          var categoryIndex = selectedValue.indexOf(this.CATEGORY_STR);
          var formIndex = selectedValue.indexOf(this.FORM_STR);
          var formStr = 'Form:';
          var categoryStr = 'Category:';
          if(categoryIndex > 0){
            titleString = categoryStr + selectedValue.substr(0, categoryIndex);
          }
          else if(formIndex > 0){
            titleString = formStr + selectedValue.substr(0, formIndex);
          }
          CREATENEWARTICLE.showActivity();
          $('#newArticleName').attr('disabled', 'disabled');
          if(CREATENEWARTICLE.categoryTimeoutId){
            window.clearTimeout(CREATENEWARTICLE.categoryTimeoutId);
          }
          CREATENEWARTICLE.categoryTimeoutId = window.setTimeout(function(){
            sajax_do_call('smwf_na_getPropertyValue', [titleString, 'Rationale'], function(request){
              var responseText = request.responseText.split('}');
              var title = responseText[1];
              var formIndex = title.indexOf(formStr);
              var categoryIndex = title.indexOf(categoryStr);
              if(formIndex === 0)
                title = title.substr(formStr.length, title.length - 1);
              else if(categoryIndex === 0)
                title = title.substr(categoryStr.length, title.length - 1);
              else{
                $('#selectedTitleTd').html('Failed to get description from server');
                CREATENEWARTICLE.hideActivity();
                return;
              }
              if($('#listOfTemplatesAndCategories option:selected').val().indexOf(title) == 0){
                $('#selectedTitleTd').html(CREATENEWARTICLE.shorterString(selectedValue, 55) + ':');
                $('#selectedDescTd').html(responseText[0]);
                $('#selectedDescImgTd').html('<img src="' + CREATENEWARTICLE.imgPath + 'info.png"/>');

                CREATENEWARTICLE.hideActivity();
                $('#newArticleName').removeAttr('disabled');
              }
              CREATENEWARTICLE.validate();
              $('#listOfTemplatesAndCategories').focus();

            })
          }, 500);
          break;
      }
    },


    buildListOfFormsAndCategories : function(){
      var forms;
      var categories;
      var listBox = $('#listOfTemplatesAndCategories');

      //ajax call to get a list of forms
      CREATENEWARTICLE.showActivity();
      sajax_do_call('smwf_na_getForms', [''], function(request){
        forms = request.responseText;
        forms = forms.split(',');
        forms = $.grep(forms, function(element, index){
          return (element);
        });
        for(i = 0; forms && i < forms.length; i++){
          forms[i] += '  (Form)';
        }
        //ajax call to get a list of categories
        sajax_do_call('smwf_na_getCategories', [''], function(request){
          categories = request.responseText;
          categories = categories.split('Category:');
          categories = $.grep(categories, function(element, index){
            return (element);
          });
          for(i = 0; categories && i < categories.length; i++){
            categories[i] += '  (Category)';
          }
          var mergedArray = $.merge(forms, categories);
          mergedArray.sort(function(a, b) {
            var compA = a.toUpperCase();
            var compB = b.toUpperCase();
            return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
          });

          for(i = 0; mergedArray && i < mergedArray.length; i++){
            listBox.append('<option>' + mergedArray[i] + '</option>')
          }
          CREATENEWARTICLE.hideActivity();
          $('#newArticleName').focus();
        });
      });


    },

    shorterString : function(theString, numOfLetters){
      if(theString && $.trim(theString).length > numOfLetters){
        if(theString.indexOf(this.CATEGORY_STR) == theString.length - this.CATEGORY_STR.length){
          theString = theString.substr(0, numOfLetters - 3 - this.CATEGORY_STR.length);
          theString += '...';
          theString += this.CATEGORY_STR;
        }
        else if(theString.indexOf(this.FORM_STR) == theString.length - this.FORM_STR.length){
          theString = theString.substr(0, numOfLetters - 3 - this.FORM_STR.length);
          theString += '...';
          theString += this.FORM_STR;
        }

      }
      return theString;
    },

    validate : function(){
      if($('#newArticleName').val() && $('#listOfTemplatesAndCategories option:selected').val() &&
        !CREATENEWARTICLE.articleExists){
        $('#cna_submitBtn').removeAttr('disabled');
      }
      else{
        $('#cna_submitBtn').attr('disabled', 'disabled');
      }
    },

    showActivity : function(){
      $('#cna_submitBtn').attr('disabled', 'disabled');
      $.fancybox.showActivity();
    },

    hideActivity : function(){
      CREATENEWARTICLE.validate();
      $.fancybox.hideActivity();
    },

    openFancybox: function(){
      $.fancybox({
        'content'  : CREATENEWARTICLE.fancyBoxContent(),
        'modal'  : true,
        'width'		: '75%',
        'height'	: '75%',
        'autoScale'	: false,
        'overlayColor'  : '#222',
        'overlayOpacity' : '0.8',
        'scrolling' : 'no',
        'titleShow'  : false,
        'enableEscapeButton' : true,
        'enableNavButtons' : true,
        'onCleanup'  : function(){
          if($.query.get('todo')){
            document.location.search = $.query.remove('todo');
          }
        },
        'onComplete'  : function(){
          $('#fancybox-close').css('background-image','url("' + CREATENEWARTICLE.imgPath + 'fancy_close.png")').css('display', 'inline');

          var articleTitleTextBox = $('#newArticleName');
          //build list of forms and categories
          CREATENEWARTICLE.buildListOfFormsAndCategories();
          
          //unbind event before bind to prevent duplicate binding
          function setFocusOutEvent(){
            articleTitleTextBox.unbind('focusout').bind('focusout', function(){
              mw.log('onfocusout');
              CREATENEWARTICLE.articleTitleChange();
            });
          }
          
          articleTitleTextBox.unbind('mouseup').bind('mouseup', function(){
            mw.log('onmouseup');
            setFocusOutEvent();
          });

          articleTitleTextBox.unbind('keyup').bind('keyup', function() {
            mw.log('onkeyup');
            CREATENEWARTICLE.articleTitleChange();
            articleTitleTextBox.unbind('focusout');
          });

          articleTitleTextBox.unbind('input paste').bind('input paste', function(event) {
            mw.log('onpaste');
            window.setTimeout(function(){
              CREATENEWARTICLE.articleTitleChange();
            }, 0);
            articleTitleTextBox.unbind('focusout');
          });

          

          //set article title from url parameter
          articleTitleTextBox.val($.query.get('newarticletitle'));

          CREATENEWARTICLE.articleTitleChange();

          $('#cna_cancelBtn').unbind('click').bind('click', function() {
            $.fancybox.close();
          });

          $('#cna_submitBtn').unbind('click').bind('click', function() {
            CREATENEWARTICLE.initForm($('#createNewArticleForm'), $('#newArticleName').val(), $('#listOfTemplatesAndCategories option:selected').val());
            return true;
          });

          $('#listOfTemplatesAndCategories').change(function()
          {
            CREATENEWARTICLE.setRationaleDescription($('#listOfTemplatesAndCategories option:selected').val());
          });

          $('#listOfTemplatesAndCategories').keyup(function()
          {
            CREATENEWARTICLE.setRationaleDescription($('#listOfTemplatesAndCategories option:selected').val());
          });

          $('#fancyboxTitleTable img').click(function()
          {
            $.fancybox.close();
          });

          $('#newArticleName').keypress(function(event) {
            if (event.which == 13 && !$('#cna_submitBtn').attr('disabled')) {
              $('#cna_submitBtn').click();
            }
          });

          $('#listOfTemplatesAndCategories').keypress(function(event) {
            if (event.which == 13 && !$('#cna_submitBtn').attr('disabled')) {
              $('#cna_submitBtn').click();
            }
          });

          $.fancybox.resize();
          $.fancybox.center();

          articleTitleTextBox.focus();
        }
      });
    }
  }



  $(document).ready(function() {
    if($.query.get('todo').toLowerCase() === 'createnewarticle'){
      CREATENEWARTICLE.openFancybox();
    }



    $(CREATENEWARTICLE.BIND_CONTROL_ID).click(function(event) {
      CREATENEWARTICLE.openFancybox();
      event.preventDefault();
    });
  });
})(jQuery.noConflict());
