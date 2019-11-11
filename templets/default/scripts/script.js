/**
 * 이 파일은 날씨위젯의 일부입니다. (https://www.imodules.io)
 * 
 * 날씨위젯 기본 템플릿 스크립트
 *
 * @file /widgets/weather/templets/default/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 11. 9.
 */
$(document).ready(function() {
	$("select[name=location]",$("div[data-widget=weather][data-templet=default]")).on("change",function() {
		var $widget = $(this).parents("div[data-widget=weather][data-templet=default]");
		var hash = $(this).val();
		
		var $weathers = $("div.weathers",$widget);
		var $info = $("div.info",$weathers);
		$info.hide();
		console.log(hash);
		$("div.info[data-hash="+hash+"]",$weathers).show();
	})
});