<?php
/**
 * 이 파일은 날씨위젯의 일부입니다. (https://www.imodules.io)
 * 
 * @file /widgets/weather/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 11. 9.
 */
if (defined('__IM__') == false) exit;

$IM->addHeadResource('style',$Widget->getDir().'/styles/weather.css');

$header = '';
$footer = '';

$title = $Widget->getValue('title') ? $Widget->getValue('title') : '날씨';
$weatherKey = $Widget->getValue('weather_key');
$aqicnKey = $Widget->getValue('aqicn_key');
$locations = $Widget->getValue('locations');
$cache = $Widget->getValue('cache') ? $Widget->getValue('cache') : 1800;

for ($i=0, $loop=count($locations);$i<$loop;$i++) {
	$location = explode(',',$locations[$i]);
	$locations[$i] = (object)array('latitude'=>$location[0],'longitude'=>$location[1],'name'=>$location[2],'hash'=>sha1(implode(',',$location)));
}

if ($Widget->checkCache() < time() - $cache) {
	$aqis = array('good'=>'좋음','moderate'=>'보통','unhealthy_sensitive'=>'민감군영향','unhealthy'=>'나쁨','very_unhealthy'=>'매우나쁨','hazardous'=>'위험');

	function ConcPM25($aqi) {
		if ($aqi >= 0 && $aqi <= 50) {
			$concentration = InvLinear(50,0,12,0,$aqi);
		} else if ($aqi > 50 && $aqi <= 100) {
			$concentration = InvLinear(100,51,35.4,12.1,$aqi);
		} else if ($aqi > 100 && $aqi <= 150) {
			$concentration = InvLinear(150,101,55.4,35.5,$aqi);
		} else if ($aqi > 150 && $aqi <= 200) {
			$concentration = InvLinear(200,151,150.4,55.5,$aqi);
		} else if ($aqi > 200 && $aqi <= 300) {
			$concentration = InvLinear(300,201,250.4,150.5,$aqi);
		} else if ($aqi > 300 && $aqi <= 400) {
			$concentration = InvLinear(400,301,350.4,250.5,$aqi);
		} else if ($aqi > 400 && $aqi <= 500) {
			$concentration = InvLinear(500,401,500.4,350.5,$aqi);
		}
		
		return $concentration;
	}
	function ConcPM10($aqi) {
		if ($aqi >= 0 && $aqi <= 50) {
			$concentration = InvLinear(50,0,54,0,$aqi);
		} else if ($aqi > 50 && $aqi <= 100) {
			$concentration = InvLinear(100,51,154,55,$aqi);
		} else if ($aqi > 100 && $aqi <= 150) {
			$concentration = InvLinear(150,101,254,155,$aqi);
		} else if ($aqi > 150 && $aqi <= 200) {
			$concentration = InvLinear(200,151,354,255,$aqi);
		} else if ($aqi > 200 && $aqi <= 300) {
			$concentration = InvLinear(300,201,424,355,$aqi);
		} else if ($aqi > 300 && $aqi <= 400) {
			$concentration = InvLinear(400,301,504,425,$aqi);
		} else if ($aqi > 400 && $aqi <= 500) {
			$concentration = InvLinear(500,401,604,505,$aqi);
		}
		
		return $concentration;
	}
	
	function InvLinear($AQIhigh,$AQIlow,$Conchigh,$Conclow,$aqi) {
		return round((($aqi - $AQIlow) / ($AQIhigh - $AQIlow)) * ($Conchigh - $Conclow) + $Conclow);
	}

	$weathers = array();
	
	foreach ($locations as $location) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,'https://api.openweathermap.org/data/2.5/weather?lat='.$location->latitude.'&lon='.$location->longitude.'&units=metric&lang=kr&APPID='.$weatherKey);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'GET');
		$response = curl_exec($ch);
		curl_close($ch);
		
		$data = json_decode($response);
		
		$weather = new stdClass();
		$weather->icon = $data->weather[0]->icon;
		$weather->status = $data->weather[0]->description;
		
		$weather->temperature = $data->main->temp;
		$weather->temperature_max = $data->main->temp_max;
		$weather->temperature_min = $data->main->temp_min;
		
		$weather->humidity = $data->main->humidity;
		
		if ($aqicnKey) {
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,'https://api.waqi.info/feed/geo:'.$location->latitude.';'.$location->longitude.'/?token='.$aqicnKey);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER,false);
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'GET');
			$response = curl_exec($ch);
			curl_close($ch);
			
			$data = json_decode($response);
			
			$weather->aqi = $data->data->aqi;
			
			if ($data->data->aqi < 50) $weather->aqiCode = 'good';
			elseif ($data->data->aqi < 100) $weather->aqiCode = 'moderate';
			elseif ($data->data->aqi < 150) $weather->aqiCode = 'unhealthy_sensitive';
			elseif ($data->data->aqi < 200) $weather->aqiCode = 'unhealthy';
			elseif ($data->data->aqi < 300) $weather->aqiCode = 'very_unhealthy';
			else $weather->aqiCode = 'hazardous';
			
			$weather->aqiStatus = $aqis[$weather->aqiCode];
			
			$weather->city = $data->data->city->name;
			$weather->pm10 = ConcPM10($data->data->iaqi->pm10->v);
			$weather->pm25 = ConcPM25($data->data->iaqi->pm25->v);
		}
		
		$weathers[$location->hash] = $weather;
	}
	
	$Widget->storeCache(json_encode($weathers,JSON_UNESCAPED_UNICODE));
} else {
	$weathers = json_decode($Widget->getCache());
}

return $Templet->getContext('index',get_defined_vars(),$header,$footer);
?>