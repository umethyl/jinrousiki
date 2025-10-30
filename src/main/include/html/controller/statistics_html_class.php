<?php
//-- HTML 生成クラス (Statistics 拡張) --//
final class StatisticsHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    JinrouStatistics::Output();
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(StatisticsMessage::TITLE, 'statistics');
    HTML::OutputBodyHeader();
    self::OutputHeaderLink();
    HeaderHTML::OutputTitle('統計情報');
  }

  //ヘッダリンク出力
  private static function OutputHeaderLink() {
    $top   = LinkHTML::Generate('./', StatisticsMessage::TOP);
    $reset = LinkHTML::Generate('statistics.php', 'リセット');
    DivHTML::Output(ArrayFilter::Concat([$top, $reset]), 'link');
  }

  //稼働数ヘッダ出力
  public static function OutputOperationHeader() {
    HeaderHTML::OutputSubTitle('稼働数');
    TableHTML::OutputHeader('');
    foreach (['種別', '総数', '日数', '人数', 'ログ検索'] as $str) {
      TableHTML::OutputTh($str);
    }
  }

  //陣営勝利ヘッダ出力
  public static function OutputWinCampHeader() {
    TableHTML::OutputHeader('');
    foreach (['陣営', '出現数', '出現率', '勝利', '勝率', '出現時勝利'] as $str) {
      TableHTML::OutputTh($str);
    }
    TableHTML::OutputTrFooter();
  }

  //出現役職ヘッダ出力
  public static function OutputRoleHeader() {
    HeaderHTML::OutputSubTitle('出現役職');
    TableHTML::OutputHeader('');
    foreach (['役職', '出現数', '出現村数', '出現率', '勝利', '勝率', 'ログ検索'] as $str) {
      TableHTML::OutputTh($str);
    }
    TableHTML::OutputTrFooter();
  }

  //数値データ出力
  public static function OutputData(array $list) {
    foreach ($list as $data) {
      TableHTML::OutputTd($data, 'member');
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

  //役職検索リンク出力
  public static function OutputSearchRoleLink(string $role) {
    $url = URL::GetSearch('old_log', ['role' => $role, 'game_type' => RQ::Fetch()->game_type]);
    self::OutputTdLink($url, '検索');
  }

  //リンク出力 (テーブル)
  private static function OutputTdLink(string $url, string $name) {
    TableHTML::OutputTd(LinkHTML::Generate($url, $name));
  }
}
