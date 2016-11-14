$(function(){
	$('.ajax-stop').click(function(ev){
		var href = this.href;
		ev.preventDefault();
		$.get(href);
	})
})

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	