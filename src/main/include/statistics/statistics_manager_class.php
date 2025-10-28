<?php
//-- 人狼統計情報クラス --//
class JinrouStatistics extends StackStaticManager {
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
    if (RQ::Get()->game_type) {
      self::OutputTotal();
    }
  }

  //村情報ロード
  private static function LoadRoom() {
    DB::Connect();
    RQ::Get()->page = 'all';
    foreach (RoomLoaderDB::GetFinished(false) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));
      DB::$ROOM->ParseOption();
      $stack = self::GetCategoryStack();
      if ($stack->category == 'chaos') {
	self::LoadUser();
      }
      self::UpdateRoom($stack);
      #Text::p($stack);
    }
  }

  //ユーザー情報ロード
  private static function LoadUser() {
    RQ::Set(RequestDataGame::ID, DB::$ROOM->id);
    DB::LoadUser();

    $stack = self::GetRoleStack();
    foreach (DB::$USER->GetRole() as $role => $list) {
      $stack->AddNumber($role, count($list));
    }
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
	TableHTML::OutputTd($number);
      }
      StatisticsHTML::OutputLink('old_log', $category, '検索');
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter();
  }

  //種別全体統計出力
  private static function OutputTotal() {
    self::OutputWinCamp();
    if (RQ::Get()->game_type == 'chaos') {
      self::OutputRole();
    }
  }

  //陣営勝利統計出力
  private static function OutputWinCamp() {
    Text::Output('<h2>陣営勝利</h2>');
    foreach (self::$category as $category => $name) {
      if (RQ::Get()->game_type != $category) {
	continue;
      }
      $stack = self::Stack()->Get($category);
      if (null === $stack) {
	continue;
      }

      $filter = $stack->Get('winner');
      //Text::p($filter);
      $result_list = self::AggregateWinCamp($filter);

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

  //出現役職統計出力 (闇鍋用)
  private static function OutputRole() {
    $stack = self::GetRoleStack();
    $list = get_object_vars($stack);

    Text::Output('<h2>出現役職</h2>');
    TableHTML::OutputHeader('');
    foreach (['役職', '出現数', 'ログ検索'] as $name) {
      TableHTML::OutputTh($name);
    }
    TableHTML::OutputTrFooter();
    foreach (RoleDataManager::GetDiff($list) as $role => $name) {
      TableHTML::OutputTrHeader();
      $log_link  = URL::GetSearch('old_log', ['role' => $role, 'game_type' => 'chaos']);

      StatisticsHTML::OutputRoleLink($role, $name);
      TableHTML::OutputTd($stack->$role);
      TableHTML::OutputTd(HTML::GenerateLink($log_link, '検索'));

      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter();
  }

  //役職データ専用スタックを取得する
  private static function GetRoleStack() {
    $category = 'role';
    $stack    = self::Stack();
    if ($stack->IsEmpty($category)) {
      $stack->Set($category, new Stack());
    }
    return $stack->Get($category);
  }

  //村の種別を判定して専用スタックを取得する
  private static function GetCategoryStack() {
    $category = self::DecideRoomCategory();
    $stack    = self::Stack();
    if ($stack->IsEmpty($category)) {
      $stack->Set($category, new Stack());
      $stack->Get($category)->Set('category', $category);
    }
    return $stack->Get($category);
  }

  //村の種別判定
  private static function DecideRoomCategory() {
    //特殊村判定
    foreach (self::GetSpecialCategoryList() as $option) {
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

  //特殊村判定オプション一覧取得
  private static function GetSpecialCategoryList() {
    //OptionFilterData に登録してもいいかもしれない
    return ['duel', 'gray_random', 'quiz'];
  }

  //村の統計情報登録
  private static function UpdateRoom(Stack $stack) {
    $stack->AddNumber('room', 1);
    $stack->AddNumber('date', DB::$ROOM->date);
    $stack->AddNumber('user_count', DB::$ROOM->user_count);

    $category = 'winner';
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
      case WinCamp::HUMAN:
      case WinCamp::WOLF:
      case WinCamp::LOVERS:
      case WinCamp::QUIZ:
      case WinCamp::VAMPIRE:
      case WinCamp::NONE:
	$result[$camp] = $stack->GetInt($camp);
	break;

      case WinCamp::FOX:
	$result[$camp] = 0;
	foreach ([WinCamp::FOX_HUMAN, WinCamp::FOX_WOLF] as $win_camp) {
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
      return '引き分け';

    case WinCamp::NONE:
      return '無し';
    }
  }
}
