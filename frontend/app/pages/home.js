console.log( "Home" )
define(["knockout", "text!./home.html"], function(ko, templ_arg) {

  function HomeViewModel(route) {
    this.message = ko.observable('Welcome to web monitoring!');
  }

 

  return { viewModel: HomeViewModel, template: templ_arg };

});
