<?php
//-- HTML 生成クラス (Statistics 拡張) --//
final class StatisticsHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    self::OutputMenu();
    self::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(StatisticsMessage::TITLE, 'statistics');
    HTML::OutputBodyHeader();
    Text::Printf(self::GetHeader(), StatisticsMessage::TOP);
  }

  //メニュー出力
  private static function OutputMenu() {
    self::OutputTotal();
  }

  //全体統計出力
  private static function OutputTotal() {
    JinrouStatistics::OutputTotal();
  }

  //フッタ出力
  private static function OutputFooter() {
    HTML::OutputFooter();
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<div class="link"><a href="./">%s</a></div>
<h1>統計情報</h1>
EOF;
  }
}
