define(["knockout", "text!./instruments.html"], function(ko, template ) {


	var Instrument = function( model, data) {
		//console.log( "Instrument", JSON.stringify(data) );
		var self = this;	
		this.model = model;	
		this.data = data;
		this.select = function() {
			console.log("select:" + self.data._id);
			self.model.selectInstrument( self )	
		}.bind(this);
        
	}
	
	function InstrumentsViewModel()
	{
	    console.log("InstrumentsViewModel");
	    var self = this;
		this.instruments = ko.observableArray();
		this.nextUpdateInMs = 1000;
		

		console.log("Pages", app_pages);

		this.init = function() {	   
			self.listInstruments();
			console.log( "init" );
			console.log( "setTimeout");			
		};

		  
		this.listInstruments = function( )
		{
			console.log( "listInstruments " );
			$.post('/api/instrument/list','{"session":"ABCDEFG"}', function(data) {
				console.log( "listInstruments ... ", data );
				js =  data
				//console.log( "listInstruments ... JSON ", js );
				
				self.instruments([]);
				
				for(var di in js.instruments)
				{
					//self.instruments.push({id:js.devices[dev], type:"HELIOS", info:"Floor 001" });
					self.instruments.push(new Instrument(self, js.instruments[di]));
					console.log( "INSTR:", di, js.instruments[di] );
				}
				//setTimeout(self.listInstruments.bind(self), 3000);
			});
		};

		this.selectInstrument = function ( instrument) {
		    console.log("listInstrument ", instrument.data.id);
		    app_share.instrument_id(instrument.data._id)
		    app_share.main_view('instrument');		  
		};

		this.init();
	}

  return { viewModel: InstrumentsViewModel, template: template };

});

/*
function pollDevices( ){
			$.post('/api_get_devices','{"key":"KEY1"}', function(data) {
				js = jQuery.parseJSON( data )
				nextUpdateInMs = 300
				$('#tdevices').empty()
				for(var dev in js.devices)
				{
					$('#tdevices').append('<tr><td><a href="/device">'+js.devices[dev]+'</a></td></tr>');
				
				}	
				setTimeout(pollDevices,nextUpdateInMs);
			});
		}

		pollDevices();

*/