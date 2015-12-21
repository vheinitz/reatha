define(['knockout', 'text!./navbar.html'], function(ko, template) {

  function NavBarViewModel(params) {
      app_share.main_view("home");
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
  }



  return { viewModel: NavBarViewModel, template: template };
});
