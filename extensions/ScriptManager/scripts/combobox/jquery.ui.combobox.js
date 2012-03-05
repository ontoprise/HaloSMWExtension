
(function( $ ) {  
  var combobox = {
    options: {
      minLength: 0,
      delay: 0,
      isOpen: false
    },
    _init: function() {
      var self = this;
      this.autocompleteOptions = {
        open: function(event, ui) {
          self.options.isOpen = true;
        },
        close: function(event, ui) {
          self.options.isOpen = false;
        },
        change: function(event, ui){
          self.triggerChange(this, $(this).val(), self.options.oldValue);
        },
        select: function(event, ui){
          self.triggerChange(this, ui.item.value);
        }
      };
      $.extend(this.autocompleteOptions, this.options);
      $.ui.autocomplete.prototype._init.call(this);
      
      this.container = $('<div/>').attr('class', this.widgetBaseClass + '-container');
      this.input = this.element;
      $(this).data(this.widgetBaseClass + 'original', this.element);
      this.input.val(self.options.value);
      this.button = $('<button/>').addClass(this.widgetBaseClass + '-button');
      this.element.replaceWith(this.container);
      this.container.append(this.input).append(this.button);
      this.input.autocomplete(this.autocompleteOptions);
      this.button.click(function(){
        if(self.options.isOpen){
          self.input.autocomplete('close');
        }
        else{
          self.input.autocomplete('search', '');
        }
      });
      this.input.keydown(function(event){
        if(event.which === 13){
          self.triggerChange(this, $(this).val(), self.options.oldValue);
        }        
      });
      this.input.click(function(event){
        if(self.options.isOpen){
          self.input.autocomplete('close');
        }
        else{
          self.input.autocomplete('search', '');
        }
      });
      this.input.focus(function(event){
        self.options.oldValue = $(this).val();
      });
    },
    destroy: function() {
      this.container.replaceWith($(this).data(this.widgetBaseClass + 'original'));
      this.container.remove();
      this.button.remove();
      $.Widget.prototype.destroy.call( this );
    },
    triggerChange: function(element, newValue, oldValue){
      oldValue = oldValue || $(element).val();
      newValue = newValue || $(element).val();
      
      if(oldValue !== newValue){
        this.options.oldValue = newValue;
        if($.inArray(newValue, this.options.source) === -1){
          this.options.source.push(newValue);
          this.input.autocomplete(this.options);
        }        
        this.options.onChange.call(element, newValue);
      }
    }
  };

  $.widget( "smwhalo.combobox", $.ui.autocomplete, combobox);

})( jQuery );