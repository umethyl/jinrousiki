<?php
require_once('init.php');
if (true !== ServerConfig::DEBUG_MODE) {
  HTML::OutputUnusableError();
}

#define('IMAGE_FONT_PATH', "C:\\WINDOWS\\Fonts\\");
define('IMAGE_FONT_PATH', "./");
//define('IMAGE_FONT_PATH', '/Library/Fonts');

require_once('include/message_image_generator.php');
require_once('include/message_image_builder.php');

$font = 'azuki.ttf';
//$font = 'aquafont.ttf';
//$font = 'Osaka.ttc';

//$role = 'poison'; //128
//$role = 'result_follow_chiroptera';
//$role = 'prediction_weather_no_escape';
$role = 'homogeneous_wolf';
//$role = 'heterologous_wolf';
//$role = 'follow_chiroptera';

//$calib_list = [0,0,0,0];
$calib_list = [0.5,0,0,0]; //wolf, vampire
//$calib_list = [0.8,0,0.7]; //collector_ogre
//$calib_list = [0.3,0,0,0]; //duelist

$test_mode   = false;
$save_mode   = false;
$all_mode    = false;
$sample_mode = false;

$wish_role = false;

if (true === $wish_role) {
  require_once('config/wish_role_list.php');
  $builder = new MessageImageBuilder('WishRoleList', $font);
} else {
  require_once('config/role_message_list.php');
  require_once('config/result_list.php');
  require_once('config/ability_list.php');
  require_once('config/weather_list.php');
  $builder = new MessageImageBuilder('RoleMessageList', $font);
  //$builder = new MessageImageBuilder('ResultList', $font);
  //$builder = new MessageImageBuilder('WeatherList', $font);
}

if (true === $test_mode) {
  $builder->Test($role);
} elseif (true === $save_mode) {
  $builder->Save($role);
} elseif (true === $all_mode) {
  $builder->OutputAll();
} else {
  $builder->Output($role, $calib_list);
}

//あなたは埋毒者です。人狼に襲われた場合は人狼の中から、処刑された場合は生きている村の人たちの中からランダムで一人道連れにします。
