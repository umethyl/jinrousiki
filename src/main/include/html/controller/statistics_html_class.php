<?php
//-- HTML 生成クラス (Statistics 拡張) --//
final class StatisticsHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    self::OutputForm();
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
    $camp_list  = JinrouStatistics::Aggregate($game_type);
    $draw_stack = JinrouStatistics::SubStack(StatisticsStack::DRAW_CAMP);
    foreach ($camp_list as $camp => $win_count) {
      if ($win_count < 1 && $camp_stack->$camp < 1) {
	continue;
      }

      switch ($camp) {
      case WinCamp::DRAW:
	$data_list = [
	  $win_count,
	  Number::Percent($win_count, $room_count, 2) . '%',
	  0,
	  0,
	  $win_count,
	  Number::Percent($win_count, $room_count, 2) . '%',
	  Number::Percent(0, $room_count, 2) . '%',
	];
	break;

      case WinCamp::NONE:
	$data_list = [
	  $win_count,
	  Number::Percent($win_count, $room_count, 2) . '%',
	  $win_count,
	  0,
	  0,
	  Number::Percent($win_count, $room_count, 2) . '%',
	  Number::Percent($win_count, $win_count,  2) . '%',
	];
	break;

      default:
	$data_list = [
	  $camp_stack->$camp,
	  Number::Percent($camp_stack->$camp, $room_count, 2) . '%',
	  $win_count,
	  $camp_stack->$camp - ($win_count + $draw_stack->$camp ?? 0),
	  $draw_stack->$camp ?? 0,
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
    $lose_stack = JinrouStatistics::SubStack(StatisticsStack::LOSE_ROLE);
    $draw_stack = JinrouStatistics::SubStack(StatisticsStack::DRAW_ROLE);
    $live_stack = JinrouStatistics::SubStack(StatisticsStack::LIVE_ROLE);
    foreach (JinrouStatistics::Aggregate($game_type, true) as $camp => $camp_count) {
      $appear_camp = StatisticsRole::ConvertAppearCamp($camp);
      if ($camp_count < 1 && $camp_stack->$appear_camp < 1) {
	continue;
      }

      $appear_count = 0;
      $win_count    = 0;
      $lose_count   = 0;
      $draw_count   = 0;
      $live_count   = 0;
      foreach ($role_stack as $role => $role_count) {
	$camp_role = StatisticsRole::ConvertOriginCampRole($role);
	if (RoleDataManager::GetCamp($camp_role, true) == $appear_camp) {
	  $appear_count += $role_count;
	  $win_count    += $win_stack->GetInt($role);
	  $lose_count   += $lose_stack->GetInt($role);
	  $draw_count   += $draw_stack->GetInt($role);
	  $live_count   += $live_stack->GetInt($role);
	}
      }

      $data_list = [
	$appear_count,
	$camp_stack->$appear_camp,
	Number::Percent($camp_stack->$appear_camp, $room_count, 2) . '%',
	$win_count,
	$lose_count,
	$draw_count,
	Number::Percent($win_count,  $appear_count, 2) . '%',
	$live_count,
	Number::Percent($live_count, $appear_count, 2) . '%'
      ];

      TableHTML::OutputTrHeader();
      TableHTML::OutputTd(StatisticsRole::GetWinCampName($camp), [HTML::CSS => $camp]);
      self::OutputData($data_list);
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
  }

  //出現役職統計出力
  public static function OutputAppearRole() {
    $title = StatisticsMessage::SUB_TITLE_APPEAR_ROLE;
    self::OutputSubHeader($title, StatisticsData::$category_header_appear_role);

    $name       = RQ::Get(StatisticsStack::GAME_TYPE);
    $room_count = JinrouStatistics::SubStack($name)->Get(StatisticsOperation::ROOM);
    $win_count  = JinrouStatistics::SubStack(StatisticsStack::WIN_ROLE);
    $lose_count = JinrouStatistics::SubStack(StatisticsStack::LOSE_ROLE);
    $draw_count = JinrouStatistics::SubStack(StatisticsStack::DRAW_ROLE);
    $live_count = JinrouStatistics::SubStack(StatisticsStack::LIVE_ROLE);
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
	$win_count->$role  ?? 0,
	$lose_count->$role ?? 0,
	$draw_count->$role ?? 0,
	Number::Percent($win_count->$role ?? 0, $stack->$role, 2) . '%',
	$live_count->$role ?? 0,
	Number::Percent($live_count->$role ?? 0, $stack->$role, 2) . '%',
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
      $role_count = $stack->$role ?? $change_stack->$role;
      $data_list = [
	$role_count,
 	$appear->$role,
	Number::Percent($appear->$role, $room_count, 2) . '%',
	$win_count->$role  ?? 0,
	$lose_count->$role ?? 0,
	$draw_count->$role ?? 0,
	Number::Percent($win_count->$role ?? 0,  $role_count, 2) . '%',
	$live_count->$role ?? 0,
	Number::Percent($live_count->$role ?? 0, $role_count, 2) . '%',
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
    HeaderHTML::OutputTitle(StatisticsMessage::TITLE);
    self::OutputHeaderLink();
  }

  //ヘッダリンク出力
  private static function OutputHeaderLink() {
    $list = [
      LinkHTML::Generate(StatisticsData::LINK_TOP,   StatisticsMessage::TOP),
      LinkHTML::Generate(StatisticsData::LINK_RESET, StatisticsMessage::RESET)
    ];
    HTML::OutputP(ArrayFilter::Concat($list));
  }

  //フォーム出力
  private static function OutputForm() {
    $url = URL::GetHeaderDB('statistics');
    $key = StatisticsStack::GAME_TYPE;
    if (null !== RQ::Get($key)) {
      if (URL::ExistsDB()) {
	$url .= URL::AddString($key, RQ::Get($key));
      } else {
	$url .= URL::HEAD . URL::ConvertString($key, RQ::Get($key));
      }
    }

    FormHTML::OutputHeader($url);
    FormHTML::OutputHiddenExecute();
    FormHTML::OutputText(RequestDataLogRoom::NAME);
    Text::Output(': 参加ユーザー名');
    FormHTML::OutputFooter();
    echo Text::BRLF;
  }

  //サブタイトルヘッダ出力
  private static function OutputSubHeader(string $title, array $list) {
    HeaderHTML::OutputSubTitle($title);
    TableHTML::OutputHeader(tr: true);
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
    TableHTML::OutputTd(StatisticsRole::GetWinCampName($camp), [HTML::CSS => $camp]);
    self::OutputData($list);
    TableHTML::OutputTrFooter();
  }

  //数値データ出力
  private static function OutputData(array $list) {
    foreach ($list as $data) {
      TableHTML::OutputTd($data, [HTML::CSS => 'member']);
    }
  }

  //リンク出力
  private static function OutputLink(string $url, string $game_type, string $name) {
    $list = [StatisticsStack::GAME_TYPE => $game_type];
    if (true !== empty(RQ::Get(RequestDataLogRoom::NAME))) {
      $list[RequestDataLogRoom::NAME] = RQ::Get(RequestDataLogRoom::NAME);
    }

    self::OutputTdLink(URL::GetSearch($url, $list), $name);
  }

  //役職リンク出力
  private static function OutputRoleLink(string $role, string $name) {
    self::OutputTdLink(URL::GetRole($role), $name);
  }

  //役職検索リンク出力
  private static function OutputSearchRoleLink(string $role) {
    $list = [
      StatisticsStack::ROLE      => $role,
      StatisticsStack::GAME_TYPE => RQ::Get(StatisticsStack::GAME_TYPE)
    ];
    if (true !== empty(RQ::Get(RequestDataLogRoom::NAME))) {
      $list[RequestDataLogRoom::NAME] = RQ::Get(RequestDataLogRoom::NAME);
    }

    $url  = URL::GetSearch(StatisticsData::LINK_LOG, $list);
    self::OutputTdLink($url, StatisticsMessage::SEARCH);
  }

  //リンク出力 (テーブル)
  private static function OutputTdLink(string $url, string $name) {
    TableHTML::OutputTd(LinkHTML::Generate($url, $name));
  }
}
