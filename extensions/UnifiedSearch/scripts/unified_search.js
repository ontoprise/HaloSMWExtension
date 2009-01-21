// deactivate combined search if necessary
var csLoadObserver;
if (csLoadObserver != null) Event.stopObserving(window, 'load', csLoadObserver);