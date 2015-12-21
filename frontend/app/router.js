//console.log( "START: Router" )
define(["knockout", "crossroads", "hasher"], function(ko, crossroads, hasher) {

    // This module configures crossroads.js, a routing library. If you prefer, you
    // can use any other routing library (or none at all) as Knockout is designed to
    // compose cleanly with external libraries.

	
    return new Router();

    function Router() {

        //console.log( "Router: ", self)
       

        var currentRoute = this.currentRoute = ko.observable({});

        //Default route to whatever main/home page
		crossroads.addRoute("", function(requestParams) {
                currentRoute(ko.utils.extend(requestParams, { page: 'home' }));
            });
			
        //Routes to pages defined in global array app_pages
		ko.utils.arrayForEach(app_pages, function(p) {
            crossroads.addRoute(p, function(requestParams) {
                currentRoute(ko.utils.extend(requestParams, { page: p }));
            });
        });

        activateCrossroads();
    }

    function activateCrossroads() {
        function parseHash(newHash, oldHash) { crossroads.parse(newHash); }
        crossroads.normalizeFn = crossroads.NORM_AS_OBJECT;
        hasher.initialized.add(parseHash);
        hasher.changed.add(parseHash);
        hasher.init();
    }
});