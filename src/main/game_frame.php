<?php
require_once('include/init.php');
OutputFrameHTMLHeader($SERVER_CONF->title . '[プレイ]');
$option = ' border="1" frameborder="1" framespacing="1" bordercolor="#C0C0C0"';
$header = '?room_no=' . @(int)$_GET['room_no'] . '&auto_reload=' . @(int)$_GET['auto_reload'];
if(@$_GET['play_sound'] == 'on') $header .= '&play_sound=on';
if(@$_GET['list_down']  == 'on') $header .= '&list_down=on';

if(@$_GET['dead_mode'] == 'on'){
  $url = $header . '&dead_mode=on';
  echo <<< EOF
<frameset rows="100, *, 20%"{$option}>
<frame name="up" src="game_up.php{$url}&heaven_mode=on">
<frame name="middle" src="game_play.php${url}">
<frame name="bottom" src="game_play.php${header}&heaven_mode=on">

EOF;
}
else{
  echo <<< EOF
<frameset rows="100, *"{$option}>
<frame name="up" src="game_up.php{$header}">
<frame name="bottom" src="game_play.php${header}">

EOF;
}
OutputFrameHTMLFooter();
