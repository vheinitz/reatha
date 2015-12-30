define(["knockout", "text!./admin.html"], function (ko, template) {
	
	var Domain = function( model, data) {
		//console.log( "Instrument", JSON.stringify(data) );
		var self = this;	
		this.model = model;	
		this.data = data;
		this.edit_domain = function() {
			self.model.edit_domain( self )	
		}.bind(this);
		
		this.delete_domain = function() {
			self.model.delete_domain( self )	
		}.bind(this);
		
		this.configure_domain = function() {
			//self.model.configure_domain( self )
			self.model.selected_domain(self.data.id)
		}.bind(this);
        
	}
	
	var DomainAdmin = function( model, data) {
		//console.log( "Instrument", JSON.stringify(data) );
		var self = this;	
		this.model = model;	
		this.data = data;
		this.edit_domain_admin = function() {
			self.model.edit_domain_admin( self )	
		}.bind(this);
		
		this.delete_domain_admin = function() {
			self.model.delete_domain_admin( self )	
		}.bind(this);
        
	}
	
	function AdminViewModel()
	{
	    var self = this;
		//console.log("AdminViewModel", app_share.instrument_id());
		this.domains = ko.observableArray();
		this.domain_admins = ko.observableArray();
		
		this.selected_domain = ko.observable('');
		
		this.addName = ko.observable();
		this.addInfo = ko.observable();
		this.addId = ko.observable();
		this.addMode = ko.observable(false);
		
		this.listDomains = function( )
		{
			console.log( "listInstruments " );
			$.post('/api/domain/list','{"session":"ABCDEFG"}', function(data) {
				console.log( "listInstruments ... ", data );
				js =  data
				//console.log( "listInstruments ... JSON ", js );
				
				self.domains([]);
				
				for(var di in js.domains)
				{
					//self.domains.push({id:js.devices[dev], type:"HELIOS", info:"Floor 001" });
					self.domains.push(new Domain(self, js.domains[di]));
					console.log( "DOMAIN:", di, js.domains[di] );
				}
				//setTimeout(self.listInstruments.bind(self), 3000);
			});
		};
		
		this.list_domain_admins = function( )
		{
			console.log( "listInstruments " );
			$.post('/api/domain/:'+'123','{"session":"ABCDEFG"}', function(data) {
				console.log( "listInstruments ... ", data );
				js =  data
				//console.log( "listInstruments ... JSON ", js );
				
				self.domains([]);
				
				for(var di in js.domains)
				{
					//self.domains.push({id:js.devices[dev], type:"HELIOS", info:"Floor 001" });
					self.domains.push(new Domain(self, js.domains[di]));
					console.log( "DOMAIN:", di, js.domains[di] );
				}
				//setTimeout(self.listInstruments.bind(self), 3000);
			});
		};

		this.edit_domain = function ( d) {
		    console.log("edit_domain ", d.data.id);
		    //app_share.domain_id(d.data.id)
		    //app_share.main_view('instrument');		  
		};
		
		this.delete_domain = function ( d) {
		    console.log("delete_domain ", d.data.id);
		    //app_share.domain_id(d.data.id)
		    //app_share.main_view('instrument');		  
		};
		
		this.configure_domain = function ( d) {
		    console.log("configure_domain ", d.data.id);
		    //app_share.domain_id(d.data.id)
		    //app_share.main_view('instrument');		  
		};
		
		this.add_domain = function ( ) {
		    console.log("add_domain ");
			self.addMode(true);
		    //app_share.domain_id(d.data.id)
		    //app_share.main_view('instrument');		  
		};
		
		this.add_ok = function ( ) {
		    console.log("add_ok ");
			self.addMode(false);
		    //app_share.domain_id(d.data.id)
		    //app_share.main_view('instrument');		  
		};
		
		this.add_cancel = function ( ) {
		    console.log("add_cancel ");
			self.addMode(false);
		    //app_share.domain_id(d.data.id)
		    //app_share.main_view('instrument');		  
		};

		
		this.init = function() {	   
		    console.log("init");
			self.listDomains()
		};

		this.init();
	}

  return { viewModel: AdminViewModel, template: template };

});
