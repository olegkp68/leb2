function simple_tooltip(target_items, name){
	jQuery.noConflict();
	$(target_items).each(function(i){
		$("body").append("<div class='"+name+"' id='"+name+i+"'><p>"+$(this).attr('title')+"</p></div>");
			var my_tooltip = $("#"+name+i);
			$(this).removeAttr("title").mouseover(function(){
			my_tooltip.css({opacity:1, display:"none"}).fadeIn(400);
		}).mousemove(function(kmouse){
			my_tooltip.css({left:kmouse.pageX+15, top:kmouse.pageY+15});
		}).mouseout(function(){
			my_tooltip.fadeOut(400);
		});
	});
}
$(document).ready(function(){
	simple_tooltip(".mod_vm_universal a","tooltip");
});