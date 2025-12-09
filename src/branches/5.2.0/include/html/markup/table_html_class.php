<?php
//-- HTML 生成クラス (Table 拡張) --//
final class TableHTML {
  //-- タグ --//
  const TAG    = 'table';
  const TAG_TH = 'th';
  const TAG_TR = 'tr';
  const TAG_TD = 'td';

  //-- Attribute --//
  const ATTR_COL = 'colspan';
  const ATTR_ROW = 'rowspan';

  //ヘッダ生成
  public static function Header(array $list = [], bool $line = false, bool $tr = false) {
    $str = HTML::TagHeader(self::TAG, $list, $line);
    return (true === $tr) ? $str . self::TrHeader(line: true) : $str;
  }

  //フッタ生成
  public static function Footer(bool $line = true, bool $tr = false) {
    $str = HTML::TagFooter(self::TAG, $line);
    return (true === $tr) ? self::TrFooter(false) . $str : $str;
  }

  //caption 生成
  public static function Caption(string $str) {
    return HTML::Tag('caption', $str, line: true);
  }

  //thead ヘッダ生成
  public static function TheadHeader() {
    return HTML::TagHeader('thead', line: true);
  }

  //thead フッタ生成
  public static function TheadFooter() {
    return HTML::TagFooter('thead', true);
  }

  //tbody ヘッダ生成
  public static function TbodyHeader() {
    return HTML::TagHeader('tbody', line: true);
  }

  //tbody フッタ生成
  public static function TbodyFooter() {
    return HTML::TagFooter('tbody', true);
  }

  //th 生成
  public static function Th(string $str, array $list = [], bool $line = false) {
    return HTML::Tag(self::TAG_TH, $str, $list, $line);
  }

  //tr 生成
  public static function Tr(string $str, array $list = [], bool $line = false) {
    return self::TrHeader($list) . $str . self::TrFooter($line);
  }

  //tr ヘッダ生成
  public static function TrHeader(array $list = [], bool $line = false) {
    return HTML::TagHeader(self::TAG_TR, $list, $line);
  }

  //tr フッタ生成
  public static function TrFooter(bool $line = true) {
    return HTML::TagFooter(self::TAG_TR, $line);
  }

  //tr 改行生成
  public static function TrLineFeed() {
    return self::TrFooter(true) . self::TrHeader();
  }

  //td 生成
  public static function Td(string $str, array $list = [], bool $line = false) {
    return self::TdHeader($list) . $str . self::TdFooter($line);
  }

  //td ヘッダ生成
  public static function TdHeader(array $list = [], bool $line = false) {
    return HTML::TagHeader(self::TAG_TD, $list, $line);
  }

  //td フッタ生成
  public static function TdFooter(bool $line = false) {
    return HTML::TagFooter(self::TAG_TD, $line);
  }

  //ヘッダ出力
  public static function OutputHeader(array $list = [], bool $line = false, bool $tr = false) {
    echo self::Header($list, $line, $tr);
  }

  //フッタ出力
  public static function OutputFooter(bool $line = true, bool $tr = false) {
    echo self::Footer($line, $tr);
  }

  //tr 出力
  public static function OutputTr(string $str, array $list = [], bool $line = false) {
    echo self::Tr($str, $list, $line);
  }

  //tr ヘッダ生成
  public static function OutputTrHeader(array $list = [], bool $line = false) {
    echo self::TrHeader($list, $line);
  }

  //tr フッタ出力
  public static function OutputTrFooter() {
    echo self::TrFooter(true);
  }

  //th 出力
  public static function OutputTh(string $str, array $list = []) {
    echo self::Th($str, $list, true);
  }

  //td 出力
  public static function OutputTd(string $str, array $list = [], bool $line = true) {
    echo self::Td($str, $list, $line);
  }

  //td ヘッダ出力
  public static function OutputTdHeader(array $list = [], bool $line = true) {
    echo self::TdHeader($list, $line);
  }

  //td フッタ出力
  public static function OutputTdFooter() {
    echo self::TdFooter(true);
  }

  //出力 (折り返し)
  public static function OutputFold($count, $base = Position::BASE) {
    Text::OutputFold($count, self::TrLineFeed(), $base);
  }
}
