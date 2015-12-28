//console.log( "START: Startup" )
var app_share = {
    main_view: 'login',
	level: '',
	session:'',
    instrument_id: ''
};
define(['jquery', 'knockout', './router', 'bootstrap', 'knockout-projections'], function($, ko, router) {

    app_share.main_view = ko.observable();
	app_share.level = ko.observable();
	app_share.session = ko.observable();

	//router.app_components = app_components;
	//router.app_pages = app_pages;
	for(var ci in app_components) 
	{					
		c = app_components[ci];
		cp = 'app/components/' + c;
		ko.components.register(c, { require: cp }, router);
		//console.log( c, cp );
	}

	for(var pi in app_pages) 
	{					
		p = app_pages[pi];
		
		pp = 'app/pages/'+p;
		ko.components.register(p, { require: pp });
		//console.log( p, pp );
	}
 
  ko.components.register('empty', { template: { require: 'text!app/components/empty/empty.html' }
  });
  // ... or for template-only components, you can just point to a .html file directly:
  ko.components.register('about-page', {
    template: { require: 'text!app/components/about-page/about.html' }
  });


  // [Scaffolded component registrations will be inserted here. To retain this feature, don't remove this comment.]

  // Start the application
  ko.applyBindings({ route: router.currentRoute });
});
