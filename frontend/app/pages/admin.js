define(["knockout", "text!./admin.html"], function (ko, template) {
	
	function AdminViewModel()
	{
	    var self = this;
		console.log("AdminViewModel", app_share.instrument_id());
		
		

		
		this.init = function() {	   
		    console.log("init");
		};

		this.init();
	}

  return { viewModel: AdminViewModel, template: template };

});
