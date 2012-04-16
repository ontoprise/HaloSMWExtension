(function($) {

  // Public: jScroll Plugin
  $.fn.jScroll = function(options) {

    var opts = $.extend({}, $.fn.jScroll.defaults, options);
    var $element = $(this);
    var $window = $(window);
    var minOffset = $element.offset().top;
    var margin = parseInt($element.css("margin-top"), 10) || 0;


    // Private
    function getMargin($element, $window) {
      var maxOffset = $element.parent().height() - 70;
      var defaultMargin = parseInt($element.css("margin-top"), 10) || 0;

      //when starting update the initial margin and offset in case elements were inserted dynamically
      if ($window.scrollTop() < minOffset){
        minOffset = $element.offset().top;
        margin = defaultMargin;
      }
      else{
        margin = defaultMargin + opts.top + $window.scrollTop() - $element.offset().top;
      }      
      if (margin > maxOffset){
        margin = maxOffset;
      }
      return ({
        "paddingTop": margin + 'px'
      });
    }

    return this.each(function() {          
      $window.scroll(function() {
        $element.stop().animate(getMargin($element, $window), opts.speed);
      });
    });
  };

  // Public: Default values
  $.fn.jScroll.defaults = {
    speed: "slow",
    top: 10
  };


})(jQuery);