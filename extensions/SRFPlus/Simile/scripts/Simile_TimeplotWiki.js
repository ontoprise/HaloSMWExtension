window.simileTimeplotRecords = [];

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
function SMWSimile_FillData(data, eventSource) {
	var evt = null, evts = new Array();
	for(var i=0;i<data.length;++i) {
		evt = new Timeplot.DefaultEventSource.NumericEvent(
			SimileAjax.DateTime.parseIso8601DateTime(data[i].start),data[i].values);
		evts[i] = evt;
	}
	eventSource.addMany(evts);
}
function SMWSimile_FillEvent(data, eventSource) {
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
}


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
	$(document).ready(function(){
		var plotInfo = [];
		var eventSource = null;
		var eventSourceEvt = null;

		for(var i=0;i<simileTimeplotRecords.length;++i) {
			eventSource = new Timeplot.DefaultEventSource();
			eventSourceEvt = new Timeplot.DefaultEventSource();
			plotInfo.push( Timeplot.createPlotInfo({
					id: "plotEvents" + i,
					eventSource: eventSourceEvt,
					timeGeometry: timeGeometry,
					lineColor: red
				})
			);
			for(var j=0;j<simileTimeplotRecords[i].count;++j) {
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
			simileTimeplots.push( Timeplot.create(document.getElementById(simileTimeplotRecords[i].div), plotInfo) );
			SMWSimile_FillData(simileTimeplotRecords[i].data, eventSource);
			SMWSimile_FillEvent(simileTimeplotRecords[i].data, eventSourceEvt);
		}
	});
})(jQuery);