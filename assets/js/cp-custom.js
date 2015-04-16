// prevent CommentPress from interpreting link clicks as paragraph clicks
jQuery( document ).ready(function() { 
	jQuery( '#content a' ).click(function(e){ 
		e.stopPropagation(); 
	});  
});  
