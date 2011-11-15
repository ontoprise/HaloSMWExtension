var isInit = false;

//document ready somehow is not fired in Special:QueryInterface
isInit || init();

jQuery(document).ready(function(){
  //initialize QI script objects and vars
  isInit || init();
  //if displayed in dialog hide everything except <div id='qicontent'>
  var ckeParam = jQuery.query.get('rsargs');
  var ckeFound = false;
  jQuery.each(ckeParam, function(index, value){
    if(value.indexOf('CKE') === 0){
      ckeFound = true;
      return false; //break out of jQuery.each loop
    }
  });

  if(ckeFound){
    var qiContentDiv = jQuery('#qicontent');
    qiContentDiv.siblings().not('[id^="stb-qi"]').each(function(){
      jQuery(this).css('display', 'none');
    });
    qiContentDiv.parents().not('body, html').each(function(){
      jQuery(this).siblings().not('[id^="stb-qi"]').each(function(){
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
    content: {text: ''},
//    overwrite: false,
    prerender: true,
    show: {
//      ready: true,
      solo: true,
      when: {
        event: 'mouseover'
      },
      delay: 0
    },
    hide: {
      when: {
        event: 'mouseout'
      },
      delay: 0
    },
    style: {
      classes: 'ui-tooltip-blue ui-tooltip-shadow'
    },
    position: {
      my: 'top left',
      at: 'bottom center',
      target: 'mouse',
      viewport: $(window),
      adjust: {
        y: 0,
        x: 20
      }
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
    element.attr('title', toolTip);
    element.removeAttr('onmouseover');
  });

  jQuery('[title]').live('mouseover', function() {
    qtipConfig.content.text = jQuery(this).attr('title');
    jQuery(this).qtip(qtipConfig);
  });
 
}

function init(){
  isInit = true;

  initToolTips();
  initialize_qi();
  window.parent.qihelper = qihelper;
  $ = window.$P;
}
  

 

