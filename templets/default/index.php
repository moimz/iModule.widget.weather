<?php
/**
 * 이 파일은 날씨위젯의 일부입니다. (https://www.imodules.io)
 * 
 * 날씨위젯 기본 템플릿
 *
 * @file /widgets/weather/templets/default/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 11. 9.
 */
if (defined('__IM__') == false) exit;
?>
<strong><?php echo $title; ?></strong>
<?php if (count($locations) > 1) { ?>
<div class="location" data-role="input">
	<select name="location">
		<?php foreach ($locations as $location) { ?>
		<option value="<?php echo $location->hash; ?>"><?php echo $location->name; ?></option>
		<?php } ?>
	</select>
</div>
<?php } ?>

<div class="weathers">
	<?php foreach ($weathers as $hash=>$weather) { ?>
	<div data-hash="<?php echo $hash; ?>" class="info">
		<div class="temperature">
			<i class="wi wi-<?php echo $weather->icon; ?>"></i>
			<span class="num"><?php echo sprintf('%0.1f',$weather->temperature); ?>℃</span>
		</div>
		<div class="txt">
			<strong><?php echo $weather->status; ?></strong>
			<p>
				<?php echo $weather->temperature_min; ?>℃ / <?php echo $weather->temperature_max; ?>℃, 습도 : <?php echo $weather->humidity; ?>%
				<?php if ($weather->aqi) { ?>
				<br><i class="aqi <?php echo $weather->aqiCode; ?>"><span>AQI : <?php echo $weather->aqi; ?> / <?php echo $weather->aqiStatus; ?></span></i> PM2.5 <?php echo $weather->pm25; ?> / PM10 : <?php echo $weather->pm10; ?>㎍/m³
				<?php } ?>
			</p>
		</div>
	</div>
	<?php } ?>
</div>