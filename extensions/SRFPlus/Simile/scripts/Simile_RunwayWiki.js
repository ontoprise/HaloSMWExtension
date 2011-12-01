window.simileRunwayRecords = [];

(function($) {
	var simileRunways = [];

	$(document).ready(function(){
		var _widget = null, _record = null;
		for(var i=0;i<simileRunwayRecords.length;++i) {
			_record = simileRunwayRecords[i];
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
	});
})(jQuery);