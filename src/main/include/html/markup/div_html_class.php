<?php
//-- HTML 生成クラス (div 拡張) --//
final class DivHTML {
  //生成
  public static function Generate($str, $class = null, $id = null) {
    return self::GenerateHeader($class, $id) . $str . self::GenerateFooter();
  }

  //ヘッダ生成
  public static function GenerateHeader($class = null, $id = null) {
    return HTML::GenerateTagHeader('div', $class, $id);
  }

  //フッタ生成
  public static function GenerateFooter($return = false) {
    return HTML::GenerateTagFooter('div') . (true === $return ? Text::LF : '');
  }

  //出力
  public static function Output($str, $class = null, $id = null) {
    Text::Output(self::Generate($str, $class, $id));
  }

  //ヘッダ出力
  public static function OutputHeader($class = null, $id = null) {
    Text::Output(self::GenerateHeader($class, $id));
  }

  //フッタ出力
  public static function OutputFooter($return = false) {
    Text::Output(self::GenerateFooter($return));
  }
}
