define(['knockout', 'text!./navbar.html'], function(ko, template) {

  function NavBarViewModel(params) {
      //app_share.main_view("home");
	  var self = this;
      this.route = params.route;
	  
      this.page_instruments = function ()
      {
          console.log("page_instruments ");
          app_share.main_view("instruments");
      }

      this.page_home = function () {

          console.log("page_home ");
          app_share.main_view("home");
      }
	  
	  this.page_admin = function () {

          console.log("page_home ");
          app_share.main_view("admin");
      }
	  
	  this.logout = function () {

          console.log("logout ");
          app_share.main_view("login");
      }
	  
	  //app_share.main_view("login");
	  app_share.main_view("admin");
  }



  return { viewModel: NavBarViewModel, template: template };
});
