function dia_log(){
	this.init();
	this.check();
};
dia_log.prototype.init = function(){
	var top = 680;
	$(window).scroll(function(){
		var scroH = $(this).scrollTop();
		if(top<scroH){
			$(".tt_left").css({
				"position": "fixed",
		    	"top":"0px"
			});
		}else if(scroH<=top){
			$(".tt_left").css({
				"position": "static",
				"top":"auto"
			});
		}
	});
	
	$("#parthead2").mouseover(function(){
		$(".mbquesBtn5").show();
	}).mouseleave(function(){
		$(".mbquesBtn5").hide();
	});	
	$("#parthead3").mouseover(function(){
		$(".mbquesBtn3").show();
	}).mouseleave(function(){
		$(".mbquesBtn3").hide();
	});	
	$("#pui_title").mouseover(function(){
		$(".mbquesBtn1").show();
	}).mouseleave(function(){
		$(".mbquesBtn1").hide();
	});	
	$("div #questypehead2_1").mouseover(function(){
		//$(".mbquesBtn4").show();
		$(this).children('.questypemenu').find('.mbquesBtn4').show();
	}).mouseleave(function(){
		$(this).children('.questypemenu').find('.mbquesBtn4').hide();
	});	
	$(".dragsort-ver").mouseover(function(){
		$(".quesopmenu",this).show();
	}).mouseleave(function(){
		$(".quesopmenu",this).hide();
	});
	/* 修改密码 */
	$("#check_password").click(function(){
		$("#check_password").hide();
		$(".passwordBox").show();
	});
	$("#cancel").click(function(){
		$("#check_password").show();
		$(".passwordBox").hide();
	});
};
dia_log.prototype.check = function(){
	$("#mbcreateQues").click(function(){
		$("#is_login").dialog({
			title:"登录",
			width:"400",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#is_login').dialog("destroy");
			}
		});
	});
	$("#mbanswerSheet").click(function(){
		$("#datika").dialog({
			title:"下载答题卡",
			width:"580",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#datika').dialog("destroy");
			}
		});
	});
	
	$("#down_next").click(function(){
		$('#down_sj').dialog("destroy");
		$("#down_sj2").dialog({
			title:"下载",
			width:"640",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#down_sj2').dialog("destroy");
			}
		});
	});
	
	
	$("#save_butt").click(function(){
		$('#save_sj').dialog("destroy");
		$("#is_save").dialog({
			title:"提示",
			width:"400",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#is_save').dialog("destroy");
			}
		});
	});
	$("#mbcreateQues").click(function(){
		$("#hint").dialog({
			title:"提示",
			width:"400",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#hint').dialog("destroy");
			}
		});
	});
	$("#bind_phone").click(function(){
		$("#phone_dialog").dialog({
			title:"提示",
			width:"450",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#phone_dialog').dialog("destroy");
			}
		});
	});
	$("#check_photo").click(function(){
		$("#head_photo").dialog({
			title:"选择头像",
			width:"630",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#head_photo').dialog("destroy");
			}
		});
	});
	
	$(".mbquesBtn3").click(function(){
		$("#modify3").dialog({
			title:"试卷设置",
			width:"400",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#modify3').dialog("destroy");
			}
		});
	});
	$(".mbquesBtn1").click(function(){
		$("#modify1").dialog({
			title:"试卷设置",
			width:"400",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#modify1').dialog("destroy");
			}
		});
	});
	$(".mbquesBtn5").click(function(){
		$("#modify5").dialog({
			title:"试卷设置",
			width:"400",
			height:"auto",
			modal:true,
			create: function(){
			},
			beforeClose: function() {
				$('#modify5').dialog("destroy");
			}
		});
	});

};
$(function(){
	new dia_log();
});