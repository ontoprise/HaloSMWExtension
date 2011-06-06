steal.plugins(	
	'jquery/controller',			// a widget factory
	'jquery/controller/subscribe',	// subscribe to OpenAjax.hub
	'jquery/view/ejs',				// client side templates
	'jquery/controller/view',		// lookup views with the controller's name
	'jquery/model',					// Ajax wrappers
	'jquery/model/backup',			// Backup of model instances
//	'jquery/dom/fixture',			// simulated Ajax requests
	'jquery/dom/form_params',		// form data helper
	'jquery/throbber')
	
	.css('sngui')	// loads styles

	.resources()					// 3rd party script's (like jQueryUI), in resources folder

	// loads files in models folder
	.models('SNLanguage',
	        'SNUserData',
			'SNNotification')						 

	// loads files in controllers folder
	.controllers('SNMain', 
	             'SNQueryTextArea',
				 'SNPageState',
				 'SNNotificationList')					

	// adds views to be added to build
	.views('//sngui/views/snmain/SNMain.ejs',
	       '//sngui/views/snmain/SNNotification.ejs',
	       '//sngui/views/snmain/SNQueryTextArea.ejs',
		   '//sngui/views/snmain/SNWarning.ejs');						