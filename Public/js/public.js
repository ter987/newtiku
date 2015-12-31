$('.tk_left').find('l').click(
	function(){
		var id = $(this).attr('id');
		var href = window.location.href;
	    var reg = /tiku\/(\w*)p(\d+)(\w*)(\/)/g;
		
		if(href.search(/\?p=\d+/)!=-1){
			href = href.replace(/\?p=\d+/,'');
		}
		if(href.search(/r\d{1}/)!=-1){
			href = href.replace(/r\d{1}/,'');
		}
	    if(href.search(reg)!=-1){
	        var loc = href.replace(/p\d+/g,'p'+id);

	    }else{
	    	var str = '';
	    	if(href.search(/tiku\/\w+/g)==-1){
	    		str = '/';
	    	}
	        var loc = href.replace(/tiku\//g,'tiku/'+'p'+id+str);
	    }
	    window.location.href = loc;
	}
);
	


