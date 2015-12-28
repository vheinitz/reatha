console.log( "Home" )
define(["knockout", "text!./home.html"], function(ko, templ_arg) {

  function HomeViewModel(route) {
    
  }

 

  return { viewModel: HomeViewModel, template: templ_arg };

});
