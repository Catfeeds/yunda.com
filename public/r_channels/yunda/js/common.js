(function(doc, win) {
	var docEl = doc.documentElement,
		resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
		recalc = function() {
			var clientWidth = docEl.clientWidth;
			if (!clientWidth) return;
			if (clientWidth > 750) {
				clientWidth = 750;
			}
			docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
		};
	if (!doc.addEventListener) return;
	win.addEventListener(resizeEvt, recalc, false);
	doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);
$(function() {
	mui('.mui-scroll-wrapper').scroll({
		deceleration: 0.0005
	});
	$('a:not(.mui-control-item)').on('tap',function(){
		document.location.href=this.href;
	});
	
	// 单选框状态
	$(':input[type=radio]').click(function() {
		$(this).parents('.radiobox').find('.icon-danxuan2').removeClass('icon-danxuan2').addClass('icon-danxuan1');
		$(this).parent().find('.icon-danxuan1').removeClass('icon-danxuan1').addClass('icon-danxuan2');
	});
	
	// 登录
	$('.btn-message,.btn-password').on('tap',function(){
		$(this).parents('.login-wrapper').hide().siblings().show()
	});
	
	$('.icon-close').on('tap',function(){
		mui('.mui-popover').popover('hide');
	});
	$('.icon-guanbi').on('tap',function(){
		mui('.mui-popover').popover('hide');
	});
	

});


function timer(wait,ele) {
	wait--;
	ele.html(wait + 's');
	ele.attr('disabled', 'disabled').addClass('disabled');
	var clear = setInterval(function() {
		if (wait > 0) {
			wait--;
			ele.html(wait + 's');
		}
		if (wait <= 0) {
			ele.html('发送验证码').removeAttr('disabled').removeClass('disabled')
			clearInterval(clear);
		}
	},
	1000);
}
// 弹出层
var Mask = function() {
	this.btn = ["取消", "确定"],
	this.init = function(){
	},
	this.open = function(html){
		$("body").append(html);
		$("html,body").css("overflow", "hidden");
		this.init();
	},
	this.close = function() {
		$(".mask").hide();
		$("html,body").css("overflow", "auto");
	}
};
Mask.prototype.alert = function(msg, time, callback) {
	var _this = this;
	var timer = null;
	var html = '<div class="mask"><div class="mask-bg"></div><div class="mask-container">' + msg + '</div></div>'
	_this.open(html);
	$(".mask").click(function(ev) {
		clearTimeout(timer);
		_this.close();
	});
	$(".mask-container").click(function(ev) {
		ev.stopPropagation()
	});
	if(time && time > 0) {
		timer = setTimeout(function() {
			_this.close();
			callback && callback();
			clearTimeout(timer);
		}, time * 1000);
	}
};
//Mask.prototype.loding = function(msg){
//	var _this = this;
//	var html = '<div class="test loading-wrapper"><i class="iconfont icon-fanhui"></i><i class="iconfont icon-close"></i><div class="loading">正在加载...</div></div>';
//	_this.open(html);
//}
Mask.prototype.loding = function(msg){
    var _this = this;
    var html = '<div class="toast_loading"><img src="./imges/loadinig.png" class="aniLoad"> <p style="line-height:1;">正在加载中…</p ></div>';
    setTimeout(function (){
    		$('.toast_loading').hide();
    	},1000);
    _this.open(html);
}
Mask.prototype.img = function(url){
	var _this = this;
	var html = '<div class="mask mask-loding"><div class="mask-bg"></div><div class="mask-container">\
				<div class="img-wrapper">\
					<img src="'+ url +'"/>\
				</div>\
			</div>\
		</div>';
	_this.open(html);
}

var Mask = new Mask();


// 弹出框
var Popups = function(){
	this.open = function(ele,callback) {
		var _this = this;
		$(ele).show();
		$("html,body").css("overflow", "hidden");
		$(ele).find('.popups-title .iconfont').click(function(){
			_this.close(ele);
			if(typeof callback === 'function'){
				callback();
			}
		});
	},
	this.close = function(ele) {
		$(ele).hide();
		$("html,body").css("overflow", "auto");
	}
}
$('.popups-title .iconfont').click(function(){
	$(this).parents('.popups-wrapper').hide();
});
var Popups = new Popups();

// 地区选择器
function areaPicker(ele,callback){
	var cityPicker = new mui.PopPicker({layer: 3});
	$(ele).on('tap', function() {
		$('input').blur();
		var _this = $(this);
		cityPicker.setData(changeCityData(areaData));
		cityPicker.show(function(items) {
			if(typeof callback === 'function'){
				callback(items);
			}
		});
	});
}
function changeCityData(areaData){
	var cityData = [];
	for(var i in areaData.province){
		var level1 = {};
		level1.value = i;
		level1.text = areaData.province[i];
		level1.children = [];
		for(var l in areaData.city){
			if(l === level1.value){
				var arr = areaData.city[l];
				for(var m=0;m<arr.length;m++){
					var level2 = {};
					level2.value = arr[m][1];
					level2.text = arr[m][0];
					level2.children = [];
					for(var a in areaData.county){
						if(a === level2.value){
							var level2arr = areaData.county[a];
							for(var j=0;j<level2arr.length;j++){
								var level3 = {};
								level3.value = level2arr[j][1];
								level3.text = level2arr[j][0];
								level2.children.push(level3);
							}
						}
					}
					level1.children.push(level2);
				}
			}
		}
		cityData.push(level1)
	}
	return cityData;
}