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
			//check if view is json, and if so - redirect to view url
			$is_json = true;
			try{
				$response = $.parseJSON($view);
			} catch($err){
				$is_json = false;
			}
			if(!$is_json){
				$('#user-device-variables').html($view);
			} else {
				window.location.replace($response.new_view_url);				
			}
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

	function device_list_preview($base_url, $device_id){
		$view = $('textarea#device-list-view-body').val();
		$.post($base_url+'da/device_list_view_preview',{view: $view, device_id: $device_id}, function($result){
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

	function get_notifications_data($base_url, $device_id){
		$.get($base_url+"u/get_notifications_data/"+$device_id, function($json) {
			$result = $.parseJSON($json);
			$.each($result, function(i, val){
				//indicator class
				$('table#'+val.id+' .user-notification-indicator').attr('class','').addClass('user-notification-indicator '+val.indicator_class);

				//button class/href
				$('table#'+val.id+' a.btn').attr('class','').addClass('btn '+val.button_class).attr('href',val.button_href);
			})
			// $('#user-notifications').html($data);
		});			
	}

	function update_notifications_view($base_url, $device_id){
		clearInterval(window.update_notifications_interval);
		get_notifications_data($base_url, $device_id);			
		window.update_notifications_interval = setInterval(function(){
			get_notifications_data($base_url, $device_id);
		}, 5000);	
	}

