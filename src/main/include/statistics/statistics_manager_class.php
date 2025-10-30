<?php
//-- 人狼統計情報クラス --//
final class JinrouStatistics extends StackStaticManager {
  // stack
  const CATEGORY    = 'category';
  const WINNER      = 'winner';
  const CAMP_APPEAR = 'camp_appear';
  const ROLE_APPEAR = 'role_appear';
  const ROLE        = 'role';
  const WIN_ROLE    = 'win_role';

  //村種別
  public static $category = [
    'normal'		=> '普通',
    'chaos'		=> '闇鍋',
    'duel'		=> '決闘',
    'gray_random'	=> 'グレラン',
    'quiz'		=> 'クイズ',
  ];

  //出力
  public static function Output() {
    self::LoadRoom();
    self::OutputOperation();
    if (RQ::Fetch()->game_type) {
      self::OutputTotal();
    }
  }

  //村情報ロード
  private static function LoadRoom() {
    //村情報抽出オプションは Request に登録しておく
    foreach (RoomLoaderDB::GetFinished(false) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));
      DB::$ROOM->ParseOption();
      self::LoadUser();
      self::UpdateRoom();
    }
  }

  //ユーザー情報ロード
  private static function LoadUser() {
    RQ::Set(RequestDataGame::ID, DB::$ROOM->id);
    DB::LoadUser();

    $role_count  = self::SubStack(self::ROLE);
    $win_count   = self::SubStack(self::WIN_ROLE);
    $camp_appear = new Stack();
    $role_appear = new Stack();
    foreach (DB::$USER->Get() as $user) {
      //廃村判定
      if (count($user->GetRoleList()) < 1 || $user->main_role == 'none') {
	continue;
      }

      $role_count->AddNumber($user->main_role, 1);
      if (Winner::Generate($user->id) == WinnerMessage::$personal_win) { //個人勝利
	$win_count->AddNumber($user->main_role, 1);
      }
      $camp_appear->Set($user->GetWinCamp(), true);
      $role_appear->Set($user->main_role, true);
    }

    //陣営出現村数
    $stack = self::SubStack(self::CAMP_APPEAR);
    foreach ($camp_appear as $role => $flag) {
      $stack->AddNumber($role, 1);
    }

    //役職出現村数
    $stack = self::SubStack(self::ROLE_APPEAR);
    foreach ($role_appear as $role => $flag) {
      $stack->AddNumber($role, 1);
    }
  }

  //村の統計情報登録
  private static function UpdateRoom() {
    $stack = self::GetCategoryStack();
    $stack->AddNumber('room', 1);
    $stack->AddNumber('date', DB::$ROOM->date);
    $stack->AddNumber('user_count', DB::$ROOM->user_count);

    $category = self::WINNER;
    if ($stack->IsEmpty($category)) {
      $filter = new Stack();
      $stack->Set($category, $filter);
    } else {
      $filter = $stack->Get($category);
    }

    $winner = DB::$ROOM->winner;
    if (null === $winner) {
      $winner = WinCamp::NONE;
    }
    $filter->AddNumber($winner, 1);
  }

  //稼働数出力
  private static function OutputOperation() {
    StatisticsHTML::OutputOperationHeader();
    $stack = self::Stack();
    foreach (self::$category as $category => $name) {
      if ($stack->IsEmpty($category)) {
	continue;
      }
      $filter = $stack->Get($category);

      TableHTML::OutputTrHeader();
      StatisticsHTML::OutputLink('statistics', $category, $name);
      foreach (['room', 'date', 'user_count'] as $data) {
	if ($filter->IsEmpty($data)) {
	  $number = 0;
	} else {
	  $number = $filter->Get($data);
	}
	TableHTML::OutputTd($number, 'member');
      }
      StatisticsHTML::OutputLink('old_log', $category, '検索');
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
  }

  //種別全体統計出力
  private static function OutputTotal() {
    self::OutputWinCamp();
    self::OutputRole();
  }

  //陣営勝利統計出力
  private static function OutputWinCamp() {
    $room_count  = self::Stack()->Get(RQ::Fetch()->game_type)->room;
    $camp_appear = self::SubStack(self::CAMP_APPEAR);

    HeaderHTML::OutputSubTitle('陣営勝利');
    foreach (self::$category as $category => $name) {
      if (RQ::Fetch()->game_type != $category) {
	continue;
      }
      $stack = self::Stack()->Get($category);
      if (null === $stack) {
	continue;
      }

      StatisticsHTML::OutputWinCampHeader();
      $result_list = self::AggregateWinCamp($stack->Get(self::WINNER));
      foreach ($result_list as $camp => $count) {
	if ($count < 1 && $camp_appear->$camp < 1) {
	  continue;
	}

	switch ($camp) {
	case WinCamp::DRAW:
	case WinCamp::NONE:
	  $data_list = [
	    '',
	    '',
	    $count,
	    Number::Percent($count, $room_count, 2) . '%',
	    ''
          ];
	  break;

	default:
	  $data_list = [
	    $camp_appear->$camp,
	    Number::Percent($camp_appear->$camp, $room_count, 2) . '%',
	    $count,
	    Number::Percent($count, $room_count, 2) . '%',
	    Number::Percent($count, $camp_appear->$camp, 2) . '%'
	  ];
	  break;
	}

	TableHTML::OutputTrHeader();
	TableHTML::OutputTd(self::GetWinCampName($camp), $camp);
	StatisticsHTML::OutputData($data_list);
	TableHTML::OutputTrFooter();
      }
      TableHTML::OutputFooter(false);
    }
  }

  //陣営勝利統計出力 (旧版)
  private static function OutputWinCampOld() {
    HeaderHTML::OutputSubTitle('陣営勝利');
    foreach (self::$category as $category => $name) {
      if (RQ::Fetch()->game_type != $category) {
	continue;
      }

      $stack = self::Stack()->Get($category);
      if (null === $stack) {
	continue;
      }
      $result_list = self::AggregateWinCamp($stack->Get(self::WINNER));

      TableHTML::OutputHeader('');
      foreach ($result_list as $camp => $count) {
	if ($count > 0) {
	  TableHTML::OutputTh(self::GetWinCampName($camp));
	}
      }
      TableHTML::OutputTrFooter();

      TableHTML::OutputTrHeader();
      foreach ($result_list as $camp => $count) {
	if ($count > 0) {
	  TableHTML::OutputTd($count);
	}
      }
      TableHTML::OutputFooter();
    }
  }

  //出現役職統計出力
  private static function OutputRole() {
    $room_count = self::Stack()->Get(RQ::Fetch()->game_type)->room;
    $win_count  = self::SubStack(self::WIN_ROLE);
    $appear     = self::SubStack(self::ROLE_APPEAR);
    $stack      = self::SubStack(self::ROLE);
    $list       = get_object_vars($stack);

    StatisticsHTML::OutputRoleHeader();
    foreach (RoleDataManager::GetDiff($list) as $role => $name) {
      TableHTML::OutputTrHeader();
      StatisticsHTML::OutputRoleLink($role, $name);
      $data_list = [
	$stack->$role,
 	$appear->$role,
	Number::Percent($appear->$role, $room_count, 2) . '%',
	$win_count->$role ?? 0,
	Number::Percent($win_count->$role ?? 0, $stack->$role, 2) . '%'
      ];
      StatisticsHTML::OutputData($data_list);
      StatisticsHTML::OutputSearchRoleLink($role);
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
  }

  //村の種別を判定して専用スタックを取得する
  private static function GetCategoryStack() {
    $category = self::DecideRoomCategory();
    $stack    = self::SubStack($category);
    if ($stack->IsEmpty(self::CATEGORY)) {
      $stack->Set(self::CATEGORY, $category);
    }
    return $stack;
  }

  //村の種別判定
  private static function DecideRoomCategory() {
    //特殊村判定
    foreach (['duel', 'gray_random', 'quiz'] as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return $option;
      }
    }

    //闇鍋モード判定
    foreach (OptionFilterData::$group_chaos as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return 'chaos';
      }
    }

    return 'normal';
  }

  //勝利陣営集計
  private static function AggregateWinCamp(Stack $stack) {
    $result = [];
    $camp_list = [
      WinCamp::HUMAN,
      WinCamp::WOLF,
      WinCamp::FOX,
      WinCamp::LOVERS,
      WinCamp::QUIZ,
      WinCamp::VAMPIRE,
      WinCamp::DRAW,
      WinCamp::NONE
    ];
    foreach ($camp_list as $camp) {
      switch ($camp) {
      case WinCamp::LOVERS:
      case WinCamp::QUIZ:
      case WinCamp::VAMPIRE:
      case WinCamp::NONE:
	$result[$camp] = $stack->GetInt($camp);
	break;

      case WinCamp::HUMAN:
	$result[$camp] = 0;
	foreach ([WinCamp::HUMAN, WinCamp::HUMAN_QUIZ] as $win_camp) {
	  $result[$camp] += $stack->GetInt($win_camp);
	}
	break;

      case WinCamp::WOLF:
	$result[$camp] = 0;
	foreach ([WinCamp::WOLF, WinCamp::WOLF_QUIZ] as $win_camp) {
	  $result[$camp] += $stack->GetInt($win_camp);
	}
	break;

      case WinCamp::FOX:
	$result[$camp] = 0;
	foreach ([WinCamp::FOX_HUMAN, WinCamp::FOX_WOLF, WinCamp::FOX_QUIZ] as $win_camp) {
	  $result[$camp] += $stack->GetInt($win_camp);
	}
	break;

      case WinCamp::DRAW:
	$result[$camp] = 0;
	foreach ([$camp, WinCamp::VANISH, WinCamp::QUIZ_DEAD] as $win_camp) {
	  $result[$camp] += $stack->GetInt($win_camp);
	}
	break;
      }
    }

    return $result;
  }

  //陣営名取得
  private static function GetWinCampName($camp) {
    switch ($camp) {
    case WinCamp::HUMAN:
    case WinCamp::WOLF:
    case WinCamp::FOX:
    case WinCamp::QUIZ:
    case WinCamp::VAMPIRE:
      return RoleDataManager::GetName($camp);

    case WinCamp::LOVERS:
      return RoleDataManager::GetName($camp, true);

    case WinCamp::DRAW:
      return '引分';

    case WinCamp::NONE:
      return '無し';
    }
  }
}
