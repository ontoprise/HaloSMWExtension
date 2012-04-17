(function($){

  CKEDITOR.mw = {};
  
  function init(){
    removeMediawikiClutter();
    
    var wikieditor = $('#wpTextbox1');
    if(!wikieditor.length){
      wikieditor = $('#free_text');
    }
    var toolbar = $('#toolbar');
    
    //if not mode=wysiwyg then return
    if(mw.util.getParamValue('mode') !== 'wysiwyg'){
      return;
    }
    //if not ckeditor compatible browser then return
    if(CKEDITOR.env && !CKEDITOR.env.isCompatible){
      return;
    }
    //if this namespace is excluded then return
    var namespace = mw.config.get('wgCanonicalNamespace');
    if(mw.user.options.get('cke_ns_' + ('ns_' + namespace).toUpperCase())){
      return;
    }
    //if the content contains __NORICHEDITOR__ then return
    if(mw.util.$content.text().indexOf( '__NORICHEDITOR__' ) > -1){
      return;
    }
    //show the toggle if configured
    if(mw.user.options.get('riched_use_toggle')){
      var toggleDiv = $('<div/>').attr('id', 'ckTools').css('float', 'right');
      var toggleAnchor = $('<a/>').attr('class', 'fckToogle').attr('id', 'toggleAnchor').attr('href', '');
      
      toggleDiv.append(toggleAnchor);        
      
      if(toolbar.length){
        toolbar.before(toggleDiv);
      }
      else{
        wikieditor.before(toggleDiv);
      }

      toggleAnchor.html(mw.config.get('wgCKeditorVisible') ? mw.msg('wysiwyg-show-wikitexteditor') : mw.msg('wysiwyg-show-richtexteditor'));

      $('#toggleAnchor').live('click', function(event){
        event.preventDefault();
        toggleEditor($(this), wikieditor, toolbar);
      });

      toggleDiv.append('[');
      toggleDiv.append(toggleAnchor);
      toggleDiv.append(']');
    }
    //show ckeditor, hide the wikieditor and the wikitoolbar if configured
    if((mw.user.options.get('cke_show') === 'richeditor')
      || (mw.user.options.get('cke_show') === 'rememberlast'
        && mw.user.options.get('riched_use_toggle')
        && $.cookie('wgCKeditorToggleState') === 'visible'))
        {
      if(toolbar.length){
        toolbar.hide();
      }
      var editor = CKEDITOR.replace(wikieditor.attr('id'));
      mw.config.set('wgCKeditorInstance', editor);
      mw.config.set('wgCKeditorVisible', true);

      //open semantic toolbar if configured
      if ( mw.user.options.get('riched_load_semantic_toolbar')){
        editor.on('instanceReady', function(event){
          event.editor.execCommand('SMWtoolbarClose');
          event.editor.execCommand('SMWtoolbarOpen');
        });
      }
    }
  }

  function removeMediawikiClutter(){
    $('#editpage-copywarn').hide();
    $('#wpSummaryLabel').hide();
    $('#wpSummary').hide();
  }
  
  function toggleEditor(toggle, wikieditor, toolbar){
    if(mw.config.get('wgCKeditorVisible')){
      mw.config.set('wgCKeditorVisible', false);
      var editor = CKEDITOR.instances[wikieditor.attr('id')];
      editor.execCommand('SMWtoolbarClose');
      editor.execCommand('SMWtoolbarOpen');
      editor.destroy();
      mw.config.set('wgCKeditorInstance', null);
      if(mw.user.options.get('showtoolbar') && toolbar.length){
        toolbar.show();
      }
      if(toggle.length){
        toggle.text(mw.msg('wysiwyg-show-richtexteditor'));
      }
      
      if(mw.user.options.get('cke_show') === 'rememberlast' && mw.user.options.get('riched_use_toggle')){
        $.cookie('wgCKeditorToggleState', 'hidden', {
          expires: 1000
        });
      }
    }
    else{
      if(toggle.length){
        toggle.parent().hide();
      }
      if(toolbar.length){
        toolbar.hide();
      }
      wikieditor.ckeditor(function(){
        if(toggle.length){
          setTimeout(function(){
            toggle.parent().show();
            toggle.text(mw.msg('wysiwyg-show-wikitexteditor'));
          }, 1000);
        }
      });
      editor = wikieditor.ckeditorGet();
      mw.config.set('wgCKeditorVisible', true);
      mw.config.set('wgCKeditorInstance', editor);
      if(mw.user.options.get('cke_show') === 'rememberlast' && mw.user.options.get('riched_use_toggle')){
        $.cookie('wgCKeditorToggleState', 'visible', {
          expires: 1000
        });
      }

      editor.on('instanceReady', function(event){
        event.editor.execCommand('SMWtoolbarClose');
        event.editor.execCommand('SMWtoolbarOpen');
      });
    }
  }

  function createfloatingToolbar(ckeToolbar, editor){
    var textArea = $('#cke_contents_' + editor.name);
    var width = textArea.width() || ckeToolbar.css('width');
    width += 'px';
    
    var height = parseInt(ckeToolbar.height(), 10) + 'px';
    var placeholder = $('#cke-toolbar-placeholder');
    if(!placeholder.length){
      placeholder = $('<div/>').attr('id', 'cke-toolbar-placeholder');
      ckeToolbar.after(placeholder);
    }
    placeholder.css({
      'height' : height,
      'width' : width,
      'background-color' : 'white'
    });

    placeholder.click(function(){
      textArea.children('iframe').eq(0).focus();
    });

    ckeToolbar.addClass('cke_wrapper').css({
      'position' : 'absolute',
      'border-radius' : '1px',
      'padding' : '1px'
    });
    var ckeToolbarTd = ckeToolbar.children('td').eq(0);
    ckeToolbarTd.css({
      'width' : width
    });
    
    ckeToolbar.jScroll({
      top: 5,
      speed: 0
    });
  }

  function resizeFloatingToolbar(ckeToolbar, editor, size){
    size = size || {};
    var width = size.width || ckeToolbar.width();

    if(size.maximize){
      ckeToolbar.css('paddingTop', 1);
    }
    else{
      width -= 10;
    }
    width += 'px';
    var placeholder = $('#cke-toolbar-placeholder');
    placeholder.css({
      'width' : width
    });
    var ckeToolbarTd = ckeToolbar.children('td').eq(0);
    ckeToolbarTd.css({
      'width' : width
    });

    
  }

  CKEDITOR.mw.isEditAllowed = function(){
    var userGroups = mw.config.get('wgUserGroups');
    var wgGroupPermissions = mw.config.get('wgGroupPermissions');

    var result = false;
    //if edit=true in one of the user groups then edit is allowed
    $.each(wgGroupPermissions, function(group, permissions){
      if($.inArray(group, userGroups) > -1 && permissions.edit){
        result = true;
        return false;
      }
    });

    return result;
  };

  CKEDITOR.mw.isMoveAllowed = function(){
    var userGroups = mw.config.get('wgUserGroups');
    var wgGroupPermissions = mw.config.get('wgGroupPermissions');

    var result = false;
    //if edit=true in one of the user groups then edit is allowed
    $.each(wgGroupPermissions, function(group, permissions){
      if($.inArray(group, userGroups) > -1 && permissions.move){
        result = true;
        return false;
      }
    });

    return result;
  };



  $(document).ready( function(){
    if(!CKEDITOR.mw.isEditAllowed()){
      return;
    }
    
    init();

    //create a floating toolbar when ckeditor instance is ready
    CKEDITOR.on('instanceReady', function(event){
      var ckeditorInstance = event.editor;
      var instanceName = ckeditorInstance.name;
      var ckeToolbar = $('#cke_top_' + instanceName).parent();
      createfloatingToolbar(ckeToolbar, ckeditorInstance);
      //resize the floating toolbar when ckeditor is resized
      ckeditorInstance.on('resize', function(event){
        if(event.data && event.data.width){
          resizeFloatingToolbar(ckeToolbar, ckeditorInstance, event.data);
        }
      });
      //ckeditor is maximize/minimized by "maximize" command. Fire "resize" event after "maximize" command is executed
      ckeditorInstance.on('afterCommandExec', function(event){
        if(event.data.name === 'maximize'){
          var width = ckeditorInstance.getResizable(true).getSize('width', true) || $('#cke_contents_' + instanceName).width();
          var height = ckeditorInstance.getResizable(true).getSize('height', true) || $('#cke_contents_' + instanceName).height();
          ckeditorInstance.fire('resize', {
            width: width,
            height: height,
            maximize: event.data.command.state === CKEDITOR.TRISTATE_ON
          });
        }
      });
      //fire "resize" event when skin is resized
      var smwMenu = $( '#smwh_menu' );
      if(smwMenu.length && smwMenu.getOntoskin){
        smwMenu.getOntoskin().addResizeListener(function(){
          var width = ckeditorInstance.getResizable(true).getSize('width', true) || $('#cke_contents_' + instanceName).width();
          var height = ckeditorInstance.getResizable(true).getSize('height', true) || $('#cke_contents_' + instanceName).height();
          ckeditorInstance.fire('resize', {
            width: width,
            height: height
          });
        });
      }

      //if "save" button is clicked then reset dirty indicator so the save dilog won't popup
      $('#wpSave').click(function(){
        ckeditorInstance.resetDirty();
      });

      //clean up when leaving the page
      $(window).unload(function(){
        ckeditorInstance.destroy();
        mw.config.set('wgCKeditorInstance', null);
        mw.config.set('wgCKeditorVisible', false);
      });

      //show confirmation dialog when there are unsaved changes
      $(window).bind('beforeunload', function(event){
        if(ckeditorInstance.checkDirty()){
          return mw.msg('wysiwyg-save-before-exit');
        }
      });
    });
  });


})(jQuery);