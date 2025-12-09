<?php
//-- HTML 生成クラス (div 拡張) --//
final class DivHTML {
  const TAG = 'div';

  //生成
  public static function Generate($str, array $list = [], bool $line = false) {
    return self::Header($list) . $str . self::Footer($line);
  }

  //ヘッダ生成
  public static function Header(array $list = [], bool $line = false) {
    return HTML::TagHeader(self::TAG, $list, $line);
  }

  //フッタ生成
  public static function Footer(bool $line = false) {
    return HTML::TagFooter(self::TAG, $line);
  }

  //出力
  public static function Output($str, array $list = []) {
    echo self::Generate($str, $list, true);
  }

  //ヘッダ出力
  public static function OutputHeader(array $list = []) {
    echo self::Header($list, true);
  }

  //フッタ出力
  public static function OutputFooter() {
    echo self::Footer(true);
  }
}
