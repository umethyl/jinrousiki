<?php
require_once(dirname(__FILE__) . '/config.php');
$USER_ICON = new UserIcon(); //アイコン登録設定をロード

// アイコンの文字数
function IconNameMaxLength(){
  global $USER_ICON;
  $length = $USER_ICON->name;
  return 'アイコン名は半角で' . $length . '文字、全角で' . floor($length/2) . '文字まで';
}
// アイコンのファイルサイズ
function IconFileSizeMax(){
  global $USER_ICON;
  $size = $USER_ICON->size;
  return ($size > 1024 ? floor($size/1024) . 'k' : $size) . 'Byte まで';
}
// アイコンの縦横のサイズ
function IconSizeMax(){
  global $USER_ICON;
  $width  = $USER_ICON->width;
  $height = $USER_ICON->height;
  return '幅' . $width . 'ピクセル × 高さ' . $height . 'ピクセルまで';
}
?>
