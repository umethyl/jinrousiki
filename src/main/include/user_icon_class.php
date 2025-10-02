<?php
//-- ユーザアイコンクラス --//
class UserIcon {
  const LENGTH_LIMIT = '半角で%d文字、全角で%d文字まで';
  const MAX_LENGTH   = 'maxlength="%d" size="%d"';
  const FILE_LIMIT   = '%sByte まで';
  const SIZE_LIMIT   = '幅%dピクセル × 高さ%dピクセルまで';

  //文字数制限
  static function GetLengthLimit() {
    $length = UserIconConfig::LENGTH;
    return sprintf(self::LENGTH_LIMIT, $length, floor($length / 2));
  }

  //文字数制限 (フォーム用)
  static function GetMaxLength($limit = false) {
    $length = UserIconConfig::LENGTH;
    $format = self::MAX_LENGTH;
    if ($limit) $format .= '>' . self::GetLengthLimit();
    return sprintf($format, $length, $length);
  }

  //ファイルサイズ制限
  static function GetFileLimit() {
    $size = UserIconConfig::FILE;
    return sprintf(self::FILE_LIMIT, $size > 1024 ? sprintf('%dk', floor($size / 1024)) : $size);
  }

  //アイコンのサイズ制限
  static function GetSizeLimit() {
    return sprintf(self::SIZE_LIMIT, UserIconConfig::WIDTH, UserIconConfig::HEIGHT);
  }

  //アイコンアップロード時の注意事項
  static function GetCaution() {
    $caution = UserIconConfig::CAUTION;
    return isset($caution) ? '<br>' . $caution : '';
  }

  //文字列長チェック
  static function CheckText($title, $url) {
    $stack = array();
    $list  = array('icon_name'  => 'アイコン名',
		   'appearance' => '出典',
		   'category'   => 'カテゴリ',
		   'author'     => 'アイコンの作者');
    foreach ($list as $key => $label) {
      $value = RQ::Get()->$key;
      if (strlen($value) > UserIconConfig::LENGTH) {
	HTML::OutputResult($title, $label . ': ' . self::GetLengthLimit() . $url);
      }
      $stack[$key] = strlen($value) > 0 ? $value : null;
    }
    return $stack;
  }

  //RGB カラーチェック
  static function CheckColor($str, $title, $url) {
    if (strlen($str) != 7 || substr($str, 0, 1) != '#' || ! ctype_xdigit(substr($str, 1, 7))) {
      $error = '色指定が正しくありません。<br>'."\n" .
	'指定は (例：#6699CC) のように RGB 16進数指定で行ってください。<br>'."\n" .
	'送信された色指定 → <span class="color">' . $str . '</span>';
      HTML::OutputResult($title, $error . $url);
    }
    return strtoupper($str);
  }
}
