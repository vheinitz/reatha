define(["knockout", "text!./wellinfo.html"], function(ko, template) {

function WellInfoViewModel(params){
	var self = this;
	this.wellInfo = params.wellInfo;
	//console.log( "WellInfoViewModel", this.wellInfo )
   
};


 return { viewModel: WellInfoViewModel, template: template };

});




