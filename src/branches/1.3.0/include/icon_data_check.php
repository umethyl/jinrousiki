<?php
require_once(dirname(__FILE__) . '/config.php');
$USER_ICON = new UserIcon(); //����������Ͽ��������

// ���������ʸ����
function IconNameMaxLength(){
  global $USER_ICON;
  $length = $USER_ICON->name;
  return '��������̾��Ⱦ�Ѥ�' . $length . 'ʸ�������Ѥ�' . floor($length/2) . 'ʸ���ޤ�';
}
// ��������Υե����륵����
function IconFileSizeMax(){
  global $USER_ICON;
  $size = $USER_ICON->size;
  return ($size > 1024 ? floor($size/1024) . 'k' : $size) . 'Byte �ޤ�';
}
// ��������νĲ��Υ�����
function IconSizeMax(){
  global $USER_ICON;
  $width  = $USER_ICON->width;
  $height = $USER_ICON->height;
  return '��' . $width . '�ԥ����� �� �⤵' . $height . '�ԥ�����ޤ�';
}
?>
