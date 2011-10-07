var isInit = false;

//document ready some how is not fired in Special:QueryInterface
isInit || init();

jQuery(document).ready(function(){
  //initialize QI script objects and vars
  isInit || init();
  //if displayed in dialog hide everything except <div id='qicontent'>
  if(jQuery.query.get('showQIContentOnly')){
    var qiContentDiv = jQuery('#qicontent');
    qiContentDiv.siblings().each(function(){
      jQuery(this).css('display', 'none');
    });
    qiContentDiv.parents().not('body, html').each(function(){
      jQuery(this).siblings().each(function(){
        jQuery(this).css('display', 'none');
      });
    });

    //put qihelper in parent window so is can be used in the dialog script
    window.parent.qihelper = qihelper;
  }
});

//get all elements with onmouseover="Tip('...')" attribute or title attribute and attach qTip tooltip to them
function initToolTips(){
  var qtipConfig = {
      content: '',
      show: {when: {event: 'mouseover'}, delay: 100},
      hide: {when: {event: 'mouseout'}, delay: 0},
      style: {
        classes: 'ui-tooltip-blue ui-tooltip-shadow'
      },
      position: {
        my: 'top left',
        at: 'bottom center',
        target: 'mouse',
        viewport: $(window),
        adjust: {y: 0, x: 20}
      }
    };

  jQuery('[onmouseover^="Tip("]').not('#qiLoadConditionTerm').each(function(){
    var element = jQuery(this);
    var toolTip = element.attr('onmouseover').toString();
    toolTip = /[.\n\r\s]+Tip\(\"([^\"]*?)\"|\'([^\']*?)\'\)[.\n\r\s]*/i.exec(toolTip);
    if ( toolTip && toolTip.length ){
      if( jQuery.client.profile().name == 'msie' ) {
        toolTip = toolTip[toolTip.length - 1];
      }
      else{
        toolTip = toolTip[1];
      }
    }
    qtipConfig.content = toolTip;
    element.qtip(qtipConfig);
    element.removeAttr('onmouseover');
  });

  jQuery('[title]').each(function(){
    qtipConfig.content = jQuery(this).attr('title') || '';
      jQuery(this).qtip(qtipConfig);
  });

  //when elements are added and removed we need to run the qtip setup again
  //do it when user clicks anywere in the page
  jQuery('*').live('click', function(){    
    jQuery('[title]').each(function(){
      qtipConfig.content = jQuery(this).attr('title') || '';
      jQuery(this).qtip(qtipConfig);
    });
  });  
}

function init(){
  initialize_qi();
  window.qihelper = qihelper;
  initToolTips();
  isInit = true;

  $ = window.$P;
}
  

 

