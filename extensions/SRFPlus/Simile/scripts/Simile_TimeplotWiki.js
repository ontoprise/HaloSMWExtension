if(typeof(simileTimeplot)=="undefined") {
	window.simileTimeplot = { records : [], js : {} };
//	jQuery("head").append('<script src="http://api.simile-widgets.org/timeplot/1.1/timeplot-api.js"></script>');
}

(function($) {
	var red = new Timeplot.Color('#B9121B');
	var lightRed = new Timeplot.Color('#cc8080');
	var blue = new Timeplot.Color('#193441');
	var green = new Timeplot.Color('#468966');
	var lightGreen = new Timeplot.Color('#5C832F');
	var gridColor  = new Timeplot.Color('#000000');
	
	var timeGeometry = new Timeplot.DefaultTimeGeometry({
		gridColor: gridColor,
		axisLabelsPlacement: "bottom"
	});
	var valueGeometry = new Timeplot.DefaultValueGeometry({
		gridColor: gridColor,
		axisLabelsPlacement: "left"
	});
	var simileTimeplotResizeTimerID = null;
	var simileTimeplots = [];

	$(window).resize(function() {
		if (simileTimeplotResizeTimerID == null) {
			simileTimeplotResizeTimerID = window.setTimeout(function() {
				simileTimeplotResizeTimerID = null;
				for(var i=0;i<simileTimeplots.length;++i) {
					simileTimeplots[i].repaint();
				}
			}, 300);
		}
	});
	
	simileTimeplot.js = {
		SMWSimile_FillData : function(data, eventSource) {
			var evt = null, evts = new Array();
			for(var i=0;i<data.length;++i) {
				evt = new Timeplot.DefaultEventSource.NumericEvent(
					SimileAjax.DateTime.parseIso8601DateTime(data[i].start),data[i].values);
				evts[i] = evt;
			}
			eventSource.addMany(evts);
		},
		SMWSimile_FillEvent : function(data, eventSource) {
			var evt = null, evts = new Array();
			for(var i=0;i<data.length;++i) {
				evt = new Timeline.DefaultEventSource.Event({
					   id: undefined,
					start: SimileAjax.DateTime.parseIso8601DateTime(data[i].start),
					  end: data[i].end ? SimileAjax.DateTime.parseIso8601DateTime(data[i].end): null,
					 text: data[i].title,
					 link: data[i].link,
					description: data[i].description
				});
				evt._obj = data[i];
				evt.getProperty = function(name) {
					return this._obj[name];
				};
				evts[i] = evt;
			}
			eventSource.addMany(evts);
		},
		renderTimeplot : function() {
			var plotInfo = [];
			var eventSource = null;
			var eventSourceEvt = null;
	
			for(var i=0;i<simileTimeplot.records.length;++i) {
				eventSource = new Timeplot.DefaultEventSource();
				eventSourceEvt = new Timeplot.DefaultEventSource();
				plotInfo.push( Timeplot.createPlotInfo({
						id: "plotEvents" + i,
						eventSource: eventSourceEvt,
						timeGeometry: timeGeometry,
						lineColor: red
					})
				);
				for(var j=0;j<simileTimeplot.records[i].count;++j) {
					plotInfo.push( Timeplot.createPlotInfo({
							id: "plot" + i + j,
							dataSource: new Timeplot.ColumnSource(eventSource, j + 1),
							valueGeometry: valueGeometry,
							lineColor: green,
							dotColor: lightGreen,
							timeGeometry: timeGeometry,
							showValues: true
						})
					);
				}
				simileTimeplots.push( Timeplot.create(document.getElementById(simileTimeplot.records[i].div), plotInfo) );
				simileTimeplot.js.SMWSimile_FillData(simileTimeplot.records[i].data, eventSource);
				simileTimeplot.js.SMWSimile_FillEvent(simileTimeplot.records[i].data, eventSourceEvt);
			}
		}
	};

	$(document).ready(function(){
		simileTimeplot.js.renderTimeplot();
	});
})(jQuery);