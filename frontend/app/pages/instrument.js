var Instr = {}

function poll_vars() {
    setTimeout(poll_vars, 3000);
    Instr.get_vars();
}

function view( v) {
    Instr.change_view(v);
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
		this.views = [];
		this.current_view_idx = 0;
		

		
		this.init = function() {	   
		    console.log("init");
		    self.instrumentData();
		};

		this.instrumentData = function () {
		    console.log("listInstruments :", '/api/instrument/' + this.id());
		    $.post('/api/instrument/' + this.id(), '{"session":"ABCDEFG"}', function (data) {
		        console.log("listInstruments ... ", data.data);
				console.log("listInstruments ... ", data.data);
		        self.info(data.data.info);
		        self.name(data.data.name);
		        self.location(data.data.location);
		        self.last_contact(data.data.last_contact);
		        self.type(data.data.type);
		        self.get_vars();
				self.views = data.data.views;
		        poll_vars();
		    });
		};

		this.change_view= function ( v ) {
			console.log("Change View :", v);
		    self.current_view_idx = 0;
			var found = false;
			for (var vi in self.views) {
				if (v == self.views[self.current_view_idx].n)
				{
					found = true;
					console.log("View OK!:", vi, self.views[self.current_view_idx].v );
					break;
				}
				self.current_view_idx +=1;
			}
			if (! found) 
				self.current_view_idx = 0;
			else
				console.log("Invalid View:", v );
			
			self.get_vars();
		}

		this.get_vars= function () {
		    console.log("get_vars :", '/api/instrument/get/:' + this.id());

		    $.post('/api/instrument/get/:' + this.id(), '{"session":"ABCDEFG"}', function (data) {
		        console.log("Instr vars ... ", data.data.vars);
		        self.vars([]);
		        //var template = data.data.template;
				var template = self.views[self.current_view_idx].v;//"<h4>{{$var3+1}} {{$var1}}</h4>Blog: {{$var2}} <div onClick=\"command('chpage', 'p1')\">Test click</div>";
				
		        for (var vi in data.data.vars) {
		            //self.instruments.push({id:js.devices[dev], type:"HELIOS", info:"Floor 001" });
		            self.vars.push(data.data.vars[vi]);
		            cid = "#" + data.data.vars[vi].n;
		            $("#" + data.data.vars[vi].n).text(data.data.vars[vi].v);
		            template = template.replace("$" + data.data.vars[vi].n, data.data.vars[vi].v);
		        }
				
				

		        var matches = template.match(/{{([^}]*?)}}/g);

		        console.log("VARS: ", matches);

		        /*for (var mi in matches) {
		            console.log("VARS: ", matches[mi]);
		        }*/

		        if (matches) {
		            var submatch = matches[1];
		        }
				
				var_results = []
				for (var mi in matches)
				{
					var val = eval(matches[mi]);
					var_results.push( val )
		        }
				console.log("VAR_RESULTS: ", var_results);
				for (var ri in var_results) {
		            template = template.replace( /\{\{[^}]+\}\}/, var_results[ri] );
					console.log("HTML: ",ri, template);
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