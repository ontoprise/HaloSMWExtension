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
    //show ckeditor, hide the wikieditor and the wikitoolbar if configured
    if((mw.user.options.get('cke_show') === 'richeditor')
      || (mw.user.options.get('cke_show') === 'rememberlast'
        && mw.user.options.get('riched_use_toggle')
        && $.cookie('wysiwygToggleState') === 'visible'))
    {
      if(toolbar.length){
        toolbar.hide();
      }
      mw.config.set('wgCKeditorInstance', CKEDITOR.replace(wikieditor.attr('id')));
      mw.config.set('wgCKeditorVisible', true);
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
    
  }

  function removeMediawikiClutter(){
    $('#editpage-copywarn').hide();
    $('#wpSummaryLabel').hide();
    $('#wpSummary').hide();
  }
  
  function toggleEditor(toggle, wikieditor, toolbar){
    if(mw.config.get('wgCKeditorVisible')){      
      CKEDITOR.instances[wikieditor.attr('id')].destroy();
      mw.config.set('wgCKeditorInstance', null);
      mw.config.set('wgCKeditorVisible', false);
      if(mw.user.options.get('showtoolbar') && toolbar.length){
        toolbar.show();
      }
      if(toggle.length){
        toggle.text(mw.msg('wysiwyg-show-richtexteditor'));
      }
      
      if(mw.user.options.get('cke_show') === 'rememberlast' && mw.user.options.get('riched_use_toggle')){
        $.cookie('wysiwygToggleState', 'hidden', {expires: 1000});
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
        $.cookie('wysiwygToggleState', 'visible', {expires: 1000});
      }
    }
  }

  $(document).ready( function(){
    init();
  });
})(jQuery);