$(function(){
    $(".ce > li > a").click(function(){
	     $(this).addClass("xz").parents().siblings().find("a").removeClass("xz");
		 $(this).parents().siblings().find(".er").hide(300);
		 $(this).siblings(".er").toggle(300);
		 $(this).parents().siblings().find(".er > li > .thr").hide().parents().siblings().find(".thr_nr").hide();
	})
	
    $(".er > li > a").click(function(){
        $(this).addClass("sen_x").parents().siblings().find("a").removeClass("sen_x");
        $(this).parents().siblings().find(".thr").hide(300);	
	    $(this).siblings(".thr").toggle(300);	
	})

    $(".thr > li > a").click(function(){
	     $(this).addClass("xuan").parents().siblings().find("a").removeClass("xuan");
		 $(this).parents().siblings().find(".thr_nr").hide();	
	     $(this).siblings(".thr_nr").toggle();
	})
});
$(function(){
    var browserWidth = $(window).width();
    if(browserWidth<800){
      $(".rightNav").css("bottom","150px");
    }
    $(".printNav").addClass("bPrintNav");
    //$(".rightNav").addClass("bRightNav");
    $("#lock").click(function(){
      $("#lock").toggleClass("lock");
      if($("#lock").hasClass('lock')){
        $(this).text("点击展开");
      }else{
        $(this).text("点击收起");
      }
      $(".rightNav").toggleClass("rightNav1");
    });
  })
  $(".openLock").mouseover(function(){
    $(".rightNav").removeClass("rightNav1");
  });
  $(".openLock").mouseout(function(){
    $(".rightNav").addClass("rightNav1");
  }); 

