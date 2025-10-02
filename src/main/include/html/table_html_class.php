<?php
//-- HTML 生成クラス (Table 拡張) --//
class TableHTML {
  //ヘッダ生成
  public static function GenerateHeader($class = null, $tr = true, $id = null) {
    $str = HTML::GenerateTagHeader('table', $class, $id);
    return $tr ? $str . self::GenerateTrHeader() : $str;
  }

  //フッタ生成
  public static function GenerateFooter($tr = true) {
    $str = HTML::GenerateTagFooter('table');
    return $tr ? self::GenerateTrFooter() . $str : $str;
  }

  //tr 生成
  public static function GenerateTr($str, $class = null) {
    return self::GenerateTrHeader($class) . $str . self::GenerateTrFooter();
  }

  //tr ヘッダ生成
  public static function GenerateTrHeader($class = null, $align = null) {
    return HTML::GenerateTagHeader('tr', $class, null, $align);
  }

  //tr フッタ生成
  public static function GenerateTrFooter() {
    return HTML::GenerateTagFooter('tr');
  }

  //th 生成
  public static function GenerateTh($str, $class = null) {
    return HTML::GenerateTagHeader('th', $class) . $str . HTML::GenerateTagFooter('th');
  }

  //tr 改行生成
  public static function GenerateTrLine() {
    return Text::Add(self::GenerateTrFooter()) . self::GenerateTrHeader();
  }

  //td 生成
  public static function GenerateTd($str, $class = null) {
    return self::GenerateTdHeader($class) . $str . self::GenerateTdFooter();
  }

  //td ヘッダ生成
  public static function GenerateTdHeader($class = null) {
    return HTML::GenerateTagHeader('td', $class);
  }

  //td フッタ生成
  public static function GenerateTdFooter() {
    return HTML::GenerateTagFooter('td');
  }

  //ヘッダ出力
  public static function OutputHeader($class, $tr = true) {
    Text::Output(self::GenerateHeader($class, $tr));
  }

  //フッタ出力
  public static function OutputFooter($tr = true) {
    Text::Output(self::GenerateFooter($tr));
  }

  //tr 出力
  public static function OutputTr($str, $class = null) {
    Text::Output(self::GenerateTr($str, $class));
  }

  //tr ヘッダ生成
  public static function OutputTrHeader($class = null, $align = null) {
    Text::Output(self::GenerateTrHeader($class, $align));
  }

  //tr フッタ出力
  public static function OutputTrFooter() {
    Text::Output(self::GenerateTrFooter());
  }

  //th 出力
  public static function OutputTh($str, $class = null) {
    Text::Output(self::GenerateTh($str, $class));
  }

  //td 出力
  public static function OutputTd($str, $class = null) {
    Text::Output(self::GenerateTd($str, $class));
  }

  //td ヘッダ出力
  public static function OutputTdHeader($class = null) {
    Text::Output(self::GenerateTdHeader($class));
  }

  //td フッタ出力
  public static function OutputTdFooter() {
    Text::Output(self::GenerateTdFooter());
  }

  //出力 (折り返し)
  public static function OutputFold($count, $base = Position::BASE) {
    Text::OutputFold($count, self::GenerateTrLine(), $base);
  }
}
