(function ($, window, document) {
	$(document).ready(function(){
		$(document).on("click",'.root-openicon, .root-closeicon',function(event){
			event.preventDefault();
			if($(this).hasClass('root-openicon'))
			{
				$(this).removeClass('root-openicon');
				$(this).addClass('root-closeicon');			
				var ul = $(this).parent('li').find('ul');
				ul.first().slideUp('fast');
			}
			else
			{
				$(this).removeClass('root-closeicon');
				$(this).addClass('root-openicon');	
				var ul = $(this).parent('li').find('ul');
				ul.first().slideDown('fast');			
			}
		});
	});
}(jQuery, window, document));