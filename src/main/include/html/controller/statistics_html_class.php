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
    HeaderHTML::OutputTitle(StatisticsMessage::TITLE);
  }

  //ヘッダリンク出力
  private static function OutputHeaderLink() {
    $list = [
      LinkHTML::Generate(StatisticsData::LINK_TOP,   StatisticsMessage::TOP),
      LinkHTML::Generate(StatisticsData::LINK_RESET, StatisticsMessage::RESET)
    ];
    DivHTML::Output(ArrayFilter::Concat($list), 'link');
  }

  //稼働数出力
  public static function OutputOperation() {
    self::OutputOperationHeader();
    $stack = JinrouStatistics::Stack();
    foreach (StatisticsData::$category as $game_type => $name) {
      if ($stack->IsEmpty($game_type)) {
	continue;
      }

      TableHTML::OutputTrHeader();
      self::OutputLink(StatisticsData::LINK_SELF, $game_type, $name);
      self::OutputOperationData($stack->Get($game_type));
      self::OutputLink(StatisticsData::LINK_LOG,  $game_type, StatisticsMessage::SEARCH);
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
  }

  //稼働数ヘッダ出力
  private static function OutputOperationHeader() {
    HeaderHTML::OutputSubTitle(StatisticsMessage::SUB_TITLE_OPERATION);
    TableHTML::OutputHeader('');
    foreach (StatisticsData::$category_header_operation as $str) {
      TableHTML::OutputTh($str);
    }
  }

  //稼働数データ出力
  private static function OutputOperationData(stack $stack) {
    $list = [];
    foreach (StatisticsData::$operation as $data) {
      if ($stack->IsEmpty($data)) {
	$count = 0;
      } else {
	$count = $stack->Get($data);
      }
      $list[] = $count;
    }
    self::OutputData($list);
  }

  //陣営勝利ヘッダ出力
  public static function OutputWinCampHeader() {
    TableHTML::OutputHeader('');
    foreach (['陣営', '出現数', '出現率', '勝利', '勝率', '出現時勝率'] as $str) {
      TableHTML::OutputTh($str);
    }
    TableHTML::OutputTrFooter();
  }

  //出現陣営ヘッダ出力
  public static function OutputCampHeader() {
    HeaderHTML::OutputSubTitle('出現陣営');
    TableHTML::OutputHeader('');
    foreach (['陣営', '出現数', '出現村数', '出現率', '勝利', '勝率'] as $str) {
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
