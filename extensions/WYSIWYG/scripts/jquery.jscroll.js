(function($) {

  // Public: jScroll Plugin
  $.fn.jScroll = function(options) {

    var opts = $.extend({}, $.fn.jScroll.defaults, options);
    var $element = $(this);
    var $window = $(window);
    var minOffset = $element.offset().top;
    var originalMargin = parseInt($element.css("margin-top"), 10) || 0;

    // Private
    function getMargin($element, $window, minOffset, originalMargin) {
//      var maxOffset = $element.parent().height() - $element.outerHeight();
      var margin = originalMargin;

      if ($window.scrollTop() >= minOffset){
        margin = margin + opts.top + $window.scrollTop() - minOffset;
      }
//      if (margin > maxOffset){
//        margin = maxOffset;
//      }
      return ({
        "paddingTop": margin + 'px'
      });
    }

    return this.each(function() {          
      $window.scroll(function() {
        $element.stop().animate(getMargin($element, $window, minOffset, originalMargin), opts.speed);
      });
    });
  };

  // Public: Default values
  $.fn.jScroll.defaults = {
    speed: "slow",
    top: 10
  };


})(jQuery);