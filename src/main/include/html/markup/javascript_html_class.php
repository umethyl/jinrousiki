<?php
//-- HTML 生成クラス (JavaScript 拡張) --//
final class JavaScriptHTML {
  //読み込み
  public static function Load($file, $path = null) {
    if (null === $path) {
      $path = JINROU_ROOT . '/javascript';
    }
    return Text::Format(self::Get(), $path, $file);
  }

  //ヘッダ生成
  public static function GenerateHeader() {
    return Text::LineFeed(self::GetHeader());
  }

  //フッタ生成
  public static function GenerateFooter() {
    return Text::LineFeed(self::GetFooter());
  }

  //ページジャンプ生成
  public static function GenerateJump() {
    $str = Text::LineFeed('if (top != self) { top.location.href = self.location.href; }');
    return self::GenerateHeader() . $str . self::GenerateFooter();
  }

  //出力
  public static function Output($file, $path = null) {
    echo self::Load($file, $path);
  }

  //ヘッダ出力
  public static function OutputHeader() {
    echo self::GenerateHeader();
  }

  //フッタ出力
  public static function OutputFooter() {
    echo self::GenerateFooter();
  }

  //読み込みタグ
  private static function Get() {
    return '<script src="%s/%s.js"></script>';
  }

  //ヘッダタグ
  private static function GetHeader() {
    return '<script type="text/javascript"><!--';
  }

  //フッタタグ
  private static function GetFooter() {
    return '//--></script>';
  }
}
