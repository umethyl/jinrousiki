<?php
//-- HTML 生成クラス (header 拡張) --//
final class HeaderHTML {
  //出力
  public static function Output(int $level, $str) {
    Text::Output(HTML::GenerateTag(self::GenerateTagName($level), $str));
  }

  //タイトル出力
  public static function OutputTitle($str) {
    self::Output(1, $str);
  }

  //サブタイトル出力
  public static function OutputSubTitle($str) {
    self::Output(2, $str);
  }

  //タグ名生成
  private static function GenerateTagName(int $level) {
    return 'h' . $level;
  }
}
