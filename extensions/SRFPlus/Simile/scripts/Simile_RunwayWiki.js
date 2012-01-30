if(typeof(simileRunway)=="undefined") {
	window.simileRunway = { records : [], js : {} };
//	jQuery("head").append('<script src="http://api.simile-widgets.org/runway/1.0/runway-api.js"></script>');
}

(function($) {
	simileRunway.js = {
		renderRunway : function() {
			var simileRunways = [];
			var _widget = null, _record = null;
			for(var i=0;i<simileRunway.records.length;++i) {
				_record = simileRunway.records[i];
				if(typeof(_record)=='undefined') continue;
				_widget = Runway.createOrShowInstaller(
					document.getElementById(_record.div),
					{
						slideSize: 300,
						index: i,
						backgroundColorTop: "#fff",
						// event handlers
						onReady: function() {
							simileRunways[this.index].setRecords(this.record.data);
							simileRunways[this.index].select(this.record.items >> 1);
						},
						onSelect: _record.onSelect,
						record: _record
					}
				);
				simileRunways[i] = _widget;
			}
		}
	};

	$(document).ready(function(){
		simileRunway.js.renderRunway();
	});
})(jQuery);