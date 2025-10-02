<?php
//-- ユーザアイコンクラス --//
class UserIcon {
  //文字数制限
  static function GetLengthLimit() {
    $length = UserIconConfig::LENGTH;
    return sprintf(IconMessage::LENGTH_LIMIT, $length, floor($length / 2));
  }

  //文字数制限 (フォーム用)
  static function GetMaxLength($limit = false) {
    $length = UserIconConfig::LENGTH;
    $format = 'maxlength="%d" size="%d"';
    if ($limit) $format .= '>' . self::GetLengthLimit();
    return sprintf($format, $length, $length);
  }

  //ファイルサイズ制限
  static function GetFileLimit() {
    $size = UserIconConfig::FILE;
    $byte = $size > 1024 ? sprintf('%dk', floor($size / 1024)) : $size;
    return sprintf(IconMessage::FILE_LIMIT, $byte);
  }

  //アイコンのサイズ制限
  static function GetSizeLimit() {
    return sprintf(IconMessage::SIZE_LIMIT, UserIconConfig::WIDTH, UserIconConfig::HEIGHT);
  }

  //アイコンアップロード時の注意事項
  static function GetCaution() {
    $caution = UserIconConfig::CAUTION;
    return isset($caution) ? Text::BR . $caution : '';
  }

  //文字列長チェック
  static function CheckText($title, $url) {
    $stack = array();
    $list  = array('icon_name'  => IconMessage::NAME,
		   'appearance' => IconMessage::APPEARANCE,
		   'category'   => IconMessage::CATEGORY,
		   'author'     => IconMessage::AUTHOR);
    foreach ($list as $key => $label) {
      $value = RQ::Get()->$key;
      if (strlen($value) > UserIconConfig::LENGTH) {
	HTML::OutputResult($title, $label . ': ' . self::GetLengthLimit() . Text::BRLF . $url);
      }
      $stack[$key] = strlen($value) > 0 ? $value : null;
    }
    return $stack;
  }

  //RGB カラーチェック
  static function CheckColor($str, $title, $url) {
    if (strlen($str) != 7 || substr($str, 0, 1) != '#' || ! ctype_xdigit(substr($str, 1, 7))) {
      $error = IconMessage::INVALID_COLOR . Text::BRLF .
	IconMessage::COLOR_EXPLAIN . Text::BRLF .
	sprintf(IconMessage::INPUT_COLOR, $str);
      HTML::OutputResult($title, $error . Text::BRLF . $url);
    }
    return strtoupper($str);
  }
}
