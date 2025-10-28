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
    JinrouStatistics::Output();
  }

  //稼働数ヘッダ出力
  public static function OutputOperationHeader() {
    Text::Output('<h2>稼働数</h2>');
    TableHTML::OutputHeader('');
    foreach (['種別', '総数', '日数', '人数', 'ログ検索'] as $str) {
      TableHTML::OutputTh($str);
    }
  }

  //リンク出力
  public static function OutputLink(string $url, string $game_type, string $name) {
    self::OutputTdLink(URL::GetSearch($url, ['game_type' => $game_type]), $name);
  }

  //役職リンク出力
  public static function OutputRoleLink(string $role, string $name) {
    self::OutputTdLink(URL::GetRole($role), $name);
  }

  //役職検索リンク出力 (闇鍋用)
  public static function OutputSearchRoleLink(string $role) {
    $url = URL::GetSearch('old_log', ['role' => $role, 'game_type' => 'chaos']);
    self::OutputTdLink($url, '検索');
  }

  //リンク出力 (テーブル)
  private static function OutputTdLink(string $url, string $name) {
    TableHTML::OutputTd(HTML::GenerateLink($url, $name));
  }

  //フッタ出力
  private static function OutputFooter() {
    HTML::OutputFooter();
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<div class="link"><a href="./">%s</a> <a href="statistics.php">リセット</a></div>
<h1>統計情報</h1>
EOF;
  }
}
