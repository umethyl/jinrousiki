<?php
//-- ユーザアイコンクラス --//
class UserIcon {
  //文字数制限
  public static function GetLengthLimit() {
    $length = UserIconConfig::LENGTH;
    return sprintf(IconMessage::LENGTH_LIMIT, $length, floor($length / 2));
  }

  //文字数制限 (フォーム用)
  public static function GetMaxLength($limit = false) {
    $length = UserIconConfig::LENGTH;
    $format = 'maxlength="%d" size="%d"';
    if ($limit) {
      $format .= '>' . self::GetLengthLimit();
    }
    return sprintf($format, $length, $length);
  }

  //ファイルサイズ制限
  public static function GetFileLimit() {
    $size = UserIconConfig::FILE;
    $byte = $size > 1024 ? sprintf('%dk', floor($size / 1024)) : $size;
    return sprintf(IconMessage::FILE_LIMIT, $byte);
  }

  //アイコンのサイズ制限
  public static function GetSizeLimit() {
    return sprintf(IconMessage::SIZE_LIMIT, UserIconConfig::WIDTH, UserIconConfig::HEIGHT);
  }

  //アイコンアップロード時の注意事項
  public static function GetCaution() {
    return Text::AddFooter('', UserIconConfig::CAUTION, Text::BR);
  }

  //文字列長チェック
  public static function ValidateText($title, $url) {
    $stack = [];
    $list  = [
      RequestDataIcon::NAME       => IconMessage::NAME,
      RequestDataIcon::CATEGORY   => IconMessage::CATEGORY,
      RequestDataIcon::APPEARANCE => IconMessage::APPEARANCE,
      RequestDataIcon::AUTHOR     => IconMessage::AUTHOR
    ];
    foreach ($list as $key => $label) {
      $str = RQ::Get()->$key;
      if (Text::Over($str, UserIconConfig::LENGTH)) {
	$error = Text::Join(Text::AddHeader(self::GetLengthLimit(), $label), $url);
	HTML::OutputResult($title, $error);
      }
      $stack[$key] = Text::Exists($str) ? $str : null;
    }
    return $stack;
  }

  //RGB カラーチェック
  public static function ValidateColor($str, $title, $url) {
    if (false === Text::IsRGB($str)) {
      $error = Text::Join(
	IconMessage::INVALID_COLOR, IconMessage::COLOR_EXPLAIN,
	sprintf(IconMessage::INPUT_COLOR, $str), $url
      );
      HTML::OutputResult($title, $error);
    }
    return strtoupper($str);
  }
}
