function dia_log(){
	this.init();
	this.check();
};
dia_log.prototype.init = function(){
	$("#parthead2").mouseover(function(){
		$(".mbquesBtn5").show();
	}).mouseleave(function(){
		$(".mbquesBtn5").hide();
	});	
	$(".questypebody").mouseover(function(){
		$(".quesopmenu").show();
	}).mouseleave(function(){
		$(".quesopmenu").hide();
	});
	$('#pui_title').mouseover(function(){
		$(".mbquesBtn1").show();
	});
	$('#pui_title').mouseleave(function(){
		$(".mbquesBtn1").hide();
	});
};
dia_log.prototype.check = function(){
	$("#mbanswerSheet").click(function(){
		$("#datika").dialog({
			title:"下载答题卡",
			width:"580",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#datike').dialog("destroy");
			}
		});
	});
	$("#mbdownQues").click(function(){
		$("#down_sj").dialog({
			title:"下载试卷",
			width:"430",
			height:"auto",
			modal:true,
			create: function(){
				
			},
			beforeClose: function() {
				
				$('#datike').dialog("destroy");
			}
		});
	});
	$("#mbsaveQues").click(function(){
		$("#save_sj").dialog({
			title:"保存试卷",
			width:"430",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#datike').dialog("destroy");
			}
		});
	});
	$(".icon3").click(function(){
		$("#sc_sj").dialog({
			title:"收藏试卷",
			width:"540",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#datike').dialog("destroy");
			}
		});
	});
};
$(function(){
	new dia_log();
});