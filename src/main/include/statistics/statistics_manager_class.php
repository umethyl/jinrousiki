<?php
//-- 人狼統計情報クラス --//
final class JinrouStatistics extends StackStaticManager {
  //出力
  public static function Output() {
    self::LoadRoom();
    StatisticsHTML::OutputOperation();
    if (RQ::Get('game_type')) {
      self::OutputWinCamp();
      self::OutputAppearCamp();
      StatisticsHTML::OutputAppearRole();
    }
  }

  //村情報ロード
  private static function LoadRoom() {
    //村情報抽出オプションは Request に登録しておく
    foreach (RoomLoaderDB::GetFinished(false) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));
      DB::$ROOM->ParseOption();
      self::LoadUser();
      self::Save();
    }
  }

  //ユーザー情報ロード
  private static function LoadUser() {
    RQ::Set(RequestDataGame::ID, DB::$ROOM->id);
    DB::LoadUser();

    $role_count   = self::SubStack(StatisticsStack::ROLE);
    $win_role     = self::SubStack(StatisticsStack::WIN_ROLE);
    $lose_role    = self::SubStack(StatisticsStack::LOSE_ROLE);
    $draw_role    = self::SubStack(StatisticsStack::DRAW_ROLE);
    $live_count   = self::SubStack(StatisticsStack::LIVE_ROLE);
    $change_count = self::SubStack(StatisticsStack::CHANGE);
    self::InitCountUp();
    foreach (DB::$USER->Get() as $user) {
      //廃村判定
      if (count($user->GetRoleList()) < 1 || $user->main_role == 'none') {
	continue;
      }

      //個人勝利判定
      $personal_result = Winner::Generate($user->id);
      switch ($personal_result) {
      case WinnerMessage::$personal_win:
	$win_role->AddNumber($user->main_role, 1);
	break;

      case WinnerMessage::$personal_lose:
	$lose_role->AddNumber($user->main_role, 1);
	break;

      case WinnerMessage::$personal_draw:
	$draw_role->AddNumber($user->main_role, 1);
	break;
      }

      //生存情報
      if ($user->IsLive()) {
	$live_count->AddNumber($user->main_role, 1);
      }

      //統計登録
      $role_count->AddNumber($user->main_role, 1);
      $main_camp = $user->GetMainCamp(true);
      $list = [
	StatisticsCount::WIN  => $user->GetWinCamp(),
	StatisticsCount::CAMP => $main_camp,
	StatisticsCount::ROLE => $user->main_role
      ];
      self::StoreCountUp($list);

      //変化役職追跡
      if (StatisticsRole::IsChanged($user)) {
	foreach (StatisticsRole::GetOrigin($user) as $change_role => $origin_role) {
	  switch ($personal_result) {
	  case WinnerMessage::$personal_win:
	    $win_role->AddNumber($change_role, 1);
	    break;

	  case WinnerMessage::$personal_lose:
	    $lose_role->AddNumber($change_role, 1);
	    break;

	  case WinnerMessage::$personal_draw:
	    $draw_role->AddNumber($change_role, 1);
	    break;
	  }

	  if ($user->IsLive()) {
	    $live_count->AddNumber($change_role, 1);
	  }

	  //陣営変化追跡
	  $origin_camp = RoleDataManager::GetCamp($origin_role, true);
	  if ($origin_camp != $main_camp) {
	    $role_count->AddNumber($change_role, 1);
	    $list = [
	      StatisticsCount::WIN  => $origin_camp,
	      StatisticsCount::CAMP => $origin_camp,
	      StatisticsCount::ROLE => $change_role
	    ];
	  } else {
	    $change_count->AddNumber($change_role, 1);
	    $list = [StatisticsCount::ROLE => $change_role];
	  }
	  self::StoreCountUp($list);
	}
      }
    }
    self::SaveCountUp();
  }

  //統計カウントアップ初期化
  private static function InitCountUp() {
    $stack = self::SubStack(StatisticsStack::COUNT_UP);
    foreach (StatisticsData::$count as $type => $stack_key) {
      $stack->$type = new Stack();
    }
  }

  //統計カウントアップ登録
  private static function StoreCountUp(array $list) {
    $stack = self::SubStack(StatisticsStack::COUNT_UP);
    foreach ($list as $key => $value) {
      $stack->$key->Set($value, true);
    }
  }

  //統計カウントアップを統計情報に登録
  private static function SaveCountUp() {
    $stack = self::SubStack(StatisticsStack::COUNT_UP);
    foreach (StatisticsData::$count as $type => $stack_key) {
      $sub_stack = self::SubStack($stack_key);
      foreach ($stack->$type as $role => $flag) {
	$sub_stack->AddNumber($role, 1);
      }
    }

    //引き分け時の出現陣営カウントアップ
    $draw_list = StatisticsRole::GetWinCampGroup(WinCamp::DRAW);
    if (ArrayFilter::IsInclude($draw_list, DB::$ROOM->winner)) {
      $draw_stack = self::SubStack(StatisticsStack::DRAW_CAMP);
      $type = StatisticsCount::CAMP;
      foreach ($stack->$type as $camp => $flag) {
	if (ArrayFilter::IsInclude(StatisticsData::$win_camp_list, $camp)) {
	  $draw_stack->AddNumber($camp, 1);
	}
      }
    }
  }

  //村別統計情報登録
  private static function Save() {
    //基礎情報
    $stack = self::GetCategoryStack();
    $stack->AddNumber(StatisticsOperation::ROOM, 1);
    $stack->AddNumber(StatisticsOperation::DATE, DB::$ROOM->date);
    $stack->AddNumber(StatisticsOperation::USER, DB::$ROOM->user_count);

    //勝利陣営
    $category = StatisticsStack::WINNER;
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

  //勝利陣営統計出力
  private static function OutputWinCamp() {
    foreach (StatisticsData::$category as $game_type => $name) {
      if (RQ::Get('game_type') != $game_type) {
	continue;
      }

      $stack = self::Stack()->Get($game_type);
      if (null === $stack) {
	continue;
      }

      return StatisticsHTML::OutputWinCamp($game_type);
    }
  }

  //出現陣営統計出力
  private static function OutputAppearCamp() {
    foreach (StatisticsData::$category as $game_type => $name) {
      if (RQ::Get('game_type') != $game_type) {
	continue;
      }

      $stack = self::Stack()->Get($game_type);
      if (null === $stack) {
	continue;
      }

      return StatisticsHTML::OutputAppearCamp($game_type);
    }
  }

  //勝利陣営統計出力 (旧版)
  private static function OutputWinCampOld() {
    HeaderHTML::OutputSubTitle(StatisticsMessage::SUB_TITLE_WIN_CAMP);
    foreach (StatisticsData::$category as $game_type => $name) {
      if (RQ::Get('game_type') != $game_type) {
	continue;
      }

      $stack = self::Stack()->Get($game_type);
      if (null === $stack) {
	continue;
      }
      $result_list = self::Aggregate($stack->Get(StatisticsStack::WINNER));

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

  //村の種別を判定して専用スタックを取得する
  private static function GetCategoryStack() {
    $category = self::DecideGameType();
    $stack    = self::SubStack($category);
    if ($stack->IsEmpty(StatisticsStack::CATEGORY)) {
      $stack->Set(StatisticsStack::CATEGORY, $category);
    }
    return $stack;
  }

  //村の種別判定
  private static function DecideGameType() {
    //特殊村判定
    $option_list = array_diff(OptionFilterData::$cast_base, OptionFilterData::$group_chaos);
    foreach ($option_list as $option) {
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

  //陣営集計
  public static function Aggregate(string $game_type, $appear = false) {
    $result = [];
    $stack  = self::SubStack($game_type)->Get(StatisticsStack::WINNER);
    $draw   = self::SubStack($game_type)->Get(StatisticsStack::DRAW_CAMP);
    if (true === $appear) {
      $camp_list = StatisticsData::$appear_camp_list;
    } else {
      $camp_list = StatisticsData::$win_camp_list;
    }

    foreach ($camp_list as $camp) {
      $result[$camp] = 0;
      foreach (StatisticsRole::GetWinCampGroup($camp) as $win_camp) {
	$result[$camp] += $stack->GetInt($win_camp);
      }
    }

    return $result;
  }
}
