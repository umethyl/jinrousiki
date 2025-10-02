<?php
define('IMAGE_FONT_PATH', "C:\\WINDOWS\\Fonts\\");
//define('IMAGE_FONT_PATH', '/Library/Fonts');

require_once('include/message_image_generator.php');
require_once('include/message_image_builder.php');

$font = 'azuki.ttf';
//$font = 'aquafont.ttf';
//$font = 'Osaka.ttc';

//$role = 'poison'; //128
//$role = 'role_tengu';
//$role = 'prediction_weather_flower_fairy';
$role = 'eye_scanner';

$calib_list = array();

$test_mode   = false;
$save_mode   = false;
$all_mode    = false;
$sample_mode = false;

$wish_role = false;
if ($wish_role) {
  require_once('config/wish_role_list.php');
  $builder = new MessageImageBuilder('WishRoleList', $font);
} else {
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
