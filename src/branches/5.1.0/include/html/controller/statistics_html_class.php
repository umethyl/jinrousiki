<?php
//-- HTML 生成クラス (Statistics 拡張) --//
final class StatisticsHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    JinrouStatistics::Output();
    HTML::OutputFooter();
  }

  //稼働統計出力
  public static function OutputOperation() {
    $title = StatisticsMessage::SUB_TITLE_OPERATION;
    self::OutputSubHeader($title, StatisticsData::$category_header_operation);

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

  //勝利陣営統計出力
  public static function OutputWinCamp(string $game_type) {
    $title = StatisticsMessage::SUB_TITLE_WIN_CAMP;
    self::OutputSubHeader($title, StatisticsData::$category_header_win_camp);

    $room_count = JinrouStatistics::SubStack($game_type)->Get(StatisticsOperation::ROOM);
    $camp_stack = JinrouStatistics::SubStack(StatisticsStack::WIN_CAMP);
    foreach (JinrouStatistics::Aggregate($game_type) as $camp => $win_count) {
      if ($win_count < 1 && $camp_stack->$camp < 1) {
	continue;
      }

      switch ($camp) {
      case WinCamp::DRAW:
      case WinCamp::NONE:
	$data_list = [
	  '',
	  '',
	  $win_count,
	  Number::Percent($win_count, $room_count, 2) . '%',
	  ''
	];
	break;

      default:
	$data_list = [
	  $camp_stack->$camp,
	  Number::Percent($camp_stack->$camp, $room_count, 2) . '%',
	  $win_count,
	  Number::Percent($win_count, $room_count, 2) . '%',
	  Number::Percent($win_count, $camp_stack->$camp, 2) . '%'
	];
	break;
      }
      self::OutputWinCampData($camp, $data_list);
    }
    TableHTML::OutputFooter(false);
  }

  //出現陣営統計出力
  public static function OutputAppearCamp(string $game_type) {
    $title = StatisticsMessage::SUB_TITLE_APPEAR_CAMP;
    self::OutputSubHeader($title, StatisticsData::$category_header_appear_camp);

    $room_count = JinrouStatistics::SubStack($game_type)->Get(StatisticsOperation::ROOM);
    $camp_stack = JinrouStatistics::SubStack(StatisticsStack::APPEAR_CAMP);
    $role_stack = JinrouStatistics::SubStack(StatisticsStack::ROLE);
    $win_stack  = JinrouStatistics::SubStack(StatisticsStack::WIN_ROLE);
    foreach (JinrouStatistics::Aggregate($game_type, true) as $camp => $camp_count) {
      $appear_camp = StatisticsRole::ConvertAppearCamp($camp);
      if ($camp_count < 1 && $camp_stack->$appear_camp < 1) {
	continue;
      }

      $appear_count = 0;
      $win_count    = 0;
      foreach ($role_stack as $role => $role_count) {
	$camp_role = StatisticsRole::ConvertOriginCampRole($role);
	if (RoleDataManager::GetCamp($camp_role, true) == $appear_camp) {
	  $appear_count += $role_count;
	  $win_count    += $win_stack->GetInt($role);
	}
      }

      $data_list = [
	$appear_count,
	$camp_stack->$appear_camp,
	Number::Percent($camp_stack->$appear_camp, $room_count, 2) . '%',
	$win_count,
	Number::Percent($win_count, $appear_count, 2) . '%'
      ];

      TableHTML::OutputTrHeader();
      TableHTML::OutputTd(StatisticsRole::GetWinCampName($camp), $camp);
      self::OutputData($data_list);
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
  }

  //出現役職統計出力
  public static function OutputAppearRole() {
    $title = StatisticsMessage::SUB_TITLE_APPEAR_ROLE;
    self::OutputSubHeader($title, StatisticsData::$category_header_appear_role);

    $room_count = JinrouStatistics::SubStack(RQ::Get('game_type'))->Get(StatisticsOperation::ROOM);
    $win_count  = JinrouStatistics::SubStack(StatisticsStack::WIN_ROLE);
    $appear     = JinrouStatistics::SubStack(StatisticsStack::APPEAR_ROLE);
    $stack      = JinrouStatistics::SubStack(StatisticsStack::ROLE);
    $list       = get_object_vars($stack);

    //メイン役職
    foreach (RoleDataManager::GetDiff($list) as $role => $name) {
      TableHTML::OutputTrHeader();
      self::OutputRoleLink($role, $name);
      $data_list = [
	$stack->$role,
 	$appear->$role,
	Number::Percent($appear->$role, $room_count, 2) . '%',
	$win_count->$role ?? 0,
	Number::Percent($win_count->$role ?? 0, $stack->$role, 2) . '%'
      ];
      self::OutputData($data_list);
      self::OutputSearchRoleLink($role);
      TableHTML::OutputTrFooter();
    }

    //変化役職
    $change_stack = JinrouStatistics::SubStack(StatisticsStack::CHANGE);
    $change_list  = array_merge($list, get_object_vars($change_stack));
    foreach (RoleDataManager::GetDiff($change_list, true) as $role => $name) {
      TableHTML::OutputTrHeader();
      self::OutputRoleLink($role, $name);
      $data_list = [
	$stack->$role ?? $change_stack->$role,
 	$appear->$role,
	Number::Percent($appear->$role, $room_count, 2) . '%',
	$win_count->$role ?? 0,
	Number::Percent($win_count->$role ?? 0, $stack->$role ?? $change_stack->$role, 2) . '%'
      ];
      self::OutputData($data_list);
      self::OutputSearchRoleLink($role);
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
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

  //サブタイトルヘッダ出力
  private static function OutputSubHeader(string $title, array $list) {
    HeaderHTML::OutputSubTitle($title);
    TableHTML::OutputHeader('');
    foreach ($list as $str) {
      TableHTML::OutputTh($str);
    }
    TableHTML::OutputTrFooter();
  }

  //稼働統計データ出力
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

  //勝利陣営統計データ出力
  private static function OutputWinCampData(string $camp, array $list) {
    TableHTML::OutputTrHeader();
    TableHTML::OutputTd(StatisticsRole::GetWinCampName($camp), $camp);
    self::OutputData($list);
    TableHTML::OutputTrFooter();
  }

  //数値データ出力
  private static function OutputData(array $list) {
    foreach ($list as $data) {
      TableHTML::OutputTd($data, 'member');
    }
  }

  //リンク出力
  private static function OutputLink(string $url, string $game_type, string $name) {
    self::OutputTdLink(URL::GetSearch($url, ['game_type' => $game_type]), $name);
  }

  //役職リンク出力
  private static function OutputRoleLink(string $role, string $name) {
    self::OutputTdLink(URL::GetRole($role), $name);
  }

  //役職検索リンク出力
  private static function OutputSearchRoleLink(string $role) {
    $list = ['role' => $role, 'game_type' => RQ::Get('game_type')];
    $url  = URL::GetSearch(StatisticsData::LINK_LOG, $list);
    self::OutputTdLink($url, StatisticsMessage::SEARCH);
  }

  //リンク出力 (テーブル)
  private static function OutputTdLink(string $url, string $name) {
    TableHTML::OutputTd(LinkHTML::Generate($url, $name));
  }
}
