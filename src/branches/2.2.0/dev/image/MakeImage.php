<?php
define('IMAGE_FONT_PATH', "C:\\WINDOWS\\Fonts\\");
//define('IMAGE_FONT_PATH', '/Library/Fonts');

require_once('include/message_image_generator.php');
require_once('include/message_image_builder.php');

$font = 'azuki.ttf';
//$font = 'azukiP.ttf';
//$font = 'uzura.ttf';
//$font = 'aquafont.ttf';
//$font = 'Osaka.ttc';

//$role = 'poison'; //128
//$role = 'prediction_weather_lady';
$role = 'tough';

$calib_list = array();
//$calib_list = array(0.5,0,0,0,-1,-0.5,0,0,0.5,0.5); //位置調整例 (step_wolf)

$test_mode   = false;
$save_mode   = false;
$all_mode    = false;
$sample_mode = false;

$wish_role = false;
if ($wish_role) {
  require_once('config/wish_role_list.php');
  $builder = new MessageImageBuilder('WishRoleList', $font);
  $role = 'role_patron';
}
else {
  require_once('config/role_message_list.php');
  $builder = new MessageImageBuilder('RoleMessageList', $font);
}

if ($test_mode) {
  $builder->Test($role);
}
elseif ($save_mode) {
  $builder->Save($role);
}
elseif ($all_mode) {
  $builder->OutputAll();
}
else {
  $builder->Output($role, $calib_list);
}

//あなたは埋毒者です。人狼に襲われた場合は人狼の中から、処刑された場合は生きている村の人たちの中からランダムで一人道連れにします。
