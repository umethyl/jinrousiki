<?php
require_once(dirname(__FILE__) . '/functions.php');

//��ꥢ�륿��������ȯ���Ǿ��񤵤����֡���
$day_seconds = floor(12 * 60 * 60 / $TIME_CONF->day);
$spend_day = ConvertTime($day_seconds);

//��ꥢ�륿��������ȯ���Ǿ��񤵤����֡���
$night_seconds = floor(6 * 60 * 60 / $TIME_CONF->night);
$spend_night = ConvertTime($night_seconds);

//��ꥢ�륿�����������ۤǷв᤹����֡���
$silence_day = ConvertTime($day_seconds * $TIME_CONF->silence_pass);

//��ꥢ�륿�����������ۤǷв᤹����֡���
$silence_night = ConvertTime($night_seconds * $TIME_CONF->silence_pass);

//��ꥢ�륿�����������ۤˤʤ�ޤǤλ���
$silence = ConvertTime($TIME_CONF->silence);

//���»��֤�����������ह��ޤǤλ���
$sudden_death = ConvertTime($TIME_CONF->sudden_death);
?>
