// Due to the ResourceLoader Prototype must be run in a closure. The function $
// polutes the global variable space. It is replaced by $P.
// All scripts that include this file are executed inside a closure. It is safe
// to let $ point to Prototype's $

var $ = $P;  