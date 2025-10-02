<?php
class TimeCalculation{
  var $spend_day;      //非リアルタイム制の発言で消費される時間 (昼)
  var $spend_night;    //非リアルタイム制の発言で消費される時間 (夜)
  var $silence_day;    //非リアルタイム制の沈黙で経過する時間 (昼)
  var $silence_night;  //非リアルタイム制の沈黙で経過する時間 (夜)
  var $silence;        //非リアルタイム制の沈黙になるまでの時間
  var $alert;          //警告音開始
  var $alert_distance; //警告音の間隔
  var $sudden_death;   //制限時間を消費後に突然死するまでの時間
  var $die_room;       //自動廃村になるまでの時間
  var $establish_wait; //次の村を立てられるまでの待ち時間

  function TimeCalculation(){ $this->__construct(); }
  function __construct(){
    global $ROOM_CONF, $TIME_CONF;

    $day_seconds   = floor(12 * 60 * 60 / $TIME_CONF->day);
    $night_seconds = floor( 6 * 60 * 60 / $TIME_CONF->night);

    $this->spend_day      = ConvertTime($day_seconds);
    $this->spend_night    = ConvertTime($night_seconds);
    $this->silence_day    = ConvertTime($TIME_CONF->silence_pass * $day_seconds);
    $this->silence_night  = ConvertTime($TIME_CONF->silence_pass * $night_seconds);
    $this->silence        = ConvertTime($TIME_CONF->silence);
    $this->sudden_death   = ConvertTime($TIME_CONF->sudden_death);
    $this->alert          = ConvertTime($TIME_CONF->alert);
    $this->alert_distance = ConvertTime($TIME_CONF->alert_distance);
    $this->die_room       = ConvertTime($ROOM_CONF->die_room);
    $this->establish_wait = ConvertTime($ROOM_CONF->establish_wait);
  }
}
