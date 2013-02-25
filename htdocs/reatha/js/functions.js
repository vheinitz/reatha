	function show_device_key($device_id, $base_url){
		$.get($base_url+'da/get_device_key/'+$device_id, function($data){
			$data = $.parseJSON($data);
			if($data.type == 'success'){
				alert($data.key);
			} else {
				alert($data.message);
			}
		});
	}

	function get_devices_power_status($base_url){
		$.getJSON($base_url+"u/get_devices_power_status", function($result) {
			$.each($result, function($i, $power_status){
				$('#power_'+$power_status.device_id).html($power_status.power);
			});
		});			
	}

	function get_device_view($view_id, $base_url){
		$.get($base_url+"u/get_device_view/"+$view_id, function($view) {
			$('#user-device-variables').html($view);
		});			
	}

	function show_device_view($view_id, $base_url){	
		clearInterval(window.device_vars_interval);
		get_device_view($view_id, $base_url);			
		window.device_vars_interval = setInterval(function(){
			get_device_view($view_id, $base_url);
		}, 2000);		
	}

