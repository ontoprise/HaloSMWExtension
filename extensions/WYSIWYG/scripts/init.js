(function($){  
  
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
      var toggleDiv = $('<div/>').attr('id', 'ckTools');
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
      CKEDITOR.instances[wikieditor.attr('id')].destroy();
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
      mw.config.set('wgCKeditorVisible', true);
      mw.config.set('wgCKeditorInstance', wikieditor.ckeditorGet());
      if(mw.user.options.get('cke_show') === 'rememberlast' && mw.user.options.get('riched_use_toggle')){
        $.cookie('wgCKeditorToggleState', 'visible', {
          expires: 1000
        });
      }
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
      textArea.children('iframe').focus();
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

    if(!size.maximize){
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



  $(document).ready( function(){
    init();

    //create a floating toolbar when ckeditor instance is ready
    CKEDITOR.on('instanceReady', function(){
      var ckeditorInstance = mw.config.get('wgCKeditorInstance');
      if(ckeditorInstance){
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
        jQuery( '#smwh_menu' ).getOntoskin().addResizeListener(function(){
          var width = ckeditorInstance.getResizable(true).getSize('width', true) || $('#cke_contents_' + instanceName).width();
          var height = ckeditorInstance.getResizable(true).getSize('height', true) || $('#cke_contents_' + instanceName).height();
          ckeditorInstance.fire('resize', {
            width: width,
            height: height
          });

        });
      }
    })

    //unset global vars when leaving the page
    $(window).unload(function(){
      var editor = mw.config.get('wgCKeditorInstance');
      if(editor){
        if(editor.checkDirty() && confirm(mw.msg('wysiwyg-save-before-exit'))){
          editor.execCommand('saveAndContinue');
        }
        mw.config.set('wgCKeditorInstance', null);
        mw.config.set('wgCKeditorVisible', false);
      }
    });
  });


})(jQuery);