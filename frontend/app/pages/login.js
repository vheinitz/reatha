console.log( "Login" )
define(["knockout", "text!./login.html"], function(ko, templ_arg) {

  function LoginViewModel(route) {
    var self = this;
    this.user = ko.observable('');
	this.password = ko.observable('');
	
	this.login = function ( ) {
		console.log("login ", self.user() );
		$.post('/api/auth/login/'+self.user()+'/'+self.password(),{}, function(data) {
			console.log("Data ", data );
			app_share.level(data.data.level);
			app_share.session(data.data.session);
			
			if ( app_share.level() )
			{
				app_share.main_view("home");
			}
			else
			{
				app_share.main_view("login");
			}
			console.log("Level ", app_share.level(), app_share.main_view(), app_share.session() );
			
		});		
	};
  }

  return { viewModel: LoginViewModel, template: templ_arg };

});
