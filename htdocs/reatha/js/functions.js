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

	function get_device_vars($device_id, $base_url){
		$.get($base_url+"u/get_device_vars/"+$device_id, function($vars) {
			$('#user-device-variables').html($vars);
		});			
	}	

	function show_device_view($view_id, $base_url){	
		clearInterval(window.device_view_interval);
		get_device_view($view_id, $base_url);			
		window.device_view_interval = setInterval(function(){
			get_device_view($view_id, $base_url);
		}, 2000);		
	}

	function show_device_vars($device_id, $base_url){	
		clearInterval(window.device_vars_interval);
		get_device_vars($device_id, $base_url);			
		window.device_vars_interval = setInterval(function(){
			get_device_vars($device_id, $base_url);
		}, 2000);		
	}

	function view_preview($base_url,$device_id){
		$view = $('textarea#view-body').val();
		$.post($base_url+'da/view_preview',{view: $view, device_id: $device_id}, function($result){
			$('#view-preview').html($result);
		} )
	}

	function toggle_notification_status($base_url, $id){
		$.get($base_url+'u/toggle_notification_status/'+$id, function($result){
			$result = $.parseJSON($result);
			if($result.type != 'success'){
				alert($result.message);
			}
		});
	}

	function reset_notification($base_url, $id){
		alert($id);
	}	

