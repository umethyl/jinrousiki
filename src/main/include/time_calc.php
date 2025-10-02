<?php
require_once(dirname(__FILE__) . '/functions.php');

//非リアルタイム制の発言で消費される時間　昼
$day_seconds = floor(12 * 60 * 60 / $TIME_CONF->day);
$spend_day = ConvertTime($day_seconds);

//非リアルタイム制の発言で消費される時間　夜
$night_seconds = floor(6 * 60 * 60 / $TIME_CONF->night);
$spend_night = ConvertTime($night_seconds);

//非リアルタイム制の沈黙で経過する時間　昼
$silence_day = ConvertTime($day_seconds * $TIME_CONF->silence_pass);

//非リアルタイム制の沈黙で経過する時間　夜
$silence_night = ConvertTime($night_seconds * $TIME_CONF->silence_pass);

//非リアルタイム制の沈黙になるまでの時間
$silence = ConvertTime($TIME_CONF->silence);

//制限時間を消費後に突然死するまでの時間
$sudden_death = ConvertTime($TIME_CONF->sudden_death);
?>
