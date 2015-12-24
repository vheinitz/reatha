


var Instr = {}

function pollVars() {
    setTimeout(pollVars, 3000);
    Instr.getVars();
}

define(["knockout", "text!./instrument.html"], function (ko, template) {
	
	function InstrumentViewModel()
	{
	    var self = this;
	    Instr = this;
		console.log("InstrumentViewModel", app_share.instrument_id());
		this.nextUpdateInMs = 1000;
		//this.data = ko.observable();
		this.id = ko.observable(app_share.instrument_id());
		this.location = ko.observable("");
		this.info = ko.observable("");
		this.last_contact = ko.observable("");
		this.name = ko.observable("");
		this.type = ko.observable("");
		this.vars = ko.observableArray([]);
		

		
		this.init = function() {	   
		    console.log("init");
		    self.instrumentData();
		};

		this.instrumentData = function () {
		    console.log("listInstruments :", '/api/instrument/:' + this.id());
		    $.post('/api/instrument/:' + this.id(), '{"session":"ABCDEFG"}', function (data) {
		        console.log("listInstruments ... ", data.data);
		        self.info(data.data.info);
		        self.name(data.data.name);
		        self.location(data.data.location);
		        self.last_contact(data.data.last_contact);
		        self.type(data.data.type);
		        self.getVars();
		        pollVars();
		    });
		};


		this.getVars= function () {
		    console.log("getVars :", '/api/instrument/get/:' + this.id());

		   
		    //var html = mustache.to_html(template, person);
		    //$('#sampleArea').html(html);
           		    
		    console.log("listInstruments ... ", template);

		    $.post('/api/instrument/get/:' + this.id(), '{"session":"ABCDEFG"}', function (data) {
		        console.log("listInstruments ... ", data.data);
		        self.vars([]);
		        var template = data.data.template;

		        var matches = template.match(/{{([^}]*?)}}/);

		        console.log("VARS: ", matches);

		        /*for (var mi in matches) {
		            console.log("VARS: ", matches[mi]);
		        }*/

		        if (matches) {
		            var submatch = matches[1];
		        }

		        for (var vi in data.data.vars) {
		            //self.instruments.push({id:js.devices[dev], type:"HELIOS", info:"Floor 001" });
		            self.vars.push(data.data.vars[vi]);
		            cid = "#" + data.data.vars[vi].n;
		            //if ($(cid).length) {

		            $("#" + data.data.vars[vi].n).text(data.data.vars[vi].v);
		            //}
		            template = template.replace("{{" + data.data.vars[vi].n + "}}", data.data.vars[vi].v);
		        }
		        document.getElementById('sampleArea').innerHTML = template;
		    });
		};

		this.init();
	}

  return { viewModel: InstrumentViewModel, template: template };

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