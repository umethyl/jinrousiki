<?php
//JinrouLogger::Get()->Output();

/* ROOM */
//foreach (DB::$ROOM as $name => $class) Text::p($class, "◆Room [{$name}]");
//foreach (DB::$ROOM->Stack() as $key => $data) Text::p($data, "◆Room/Stack [{$key}]");
//Text::p(DB::$ROOM->Flag(), '◆Flag');
//DB::$ROOM->Stack()->p('event', '◆Event');
//Text::p(DB::$ROOM->game_option, '◆GameOption');

/* USER */
//Text::p(DB::$USER->GetRole(), '◆ROOM/Role');
//Text::v(DB::$USER->IsOpenCast(), '◆OpenCast');
//foreach (DB::$USER->Get() as $user) {
//  Text::p($user->Stack(), "◆{$user->uname} [{$user->main_role}]");
//}

/* ROLE */
//foreach (RoleManager::Stack() as $name => $class) Text::p($class, "◆Role [${name}]");

/* ライブラリロード情報 */
//Loader::OutputFile();
//OptionLoader::OutputFile();
//EventLoader::OutputFile();
//RoleLoader::OutputFile();
//Text::p(array_keys(RoleLoader::$class), '◆Role [class]');

/* 役職判定情報 */
//self::OutputDistinguishMage();
//self::OutputDistinguishNecromancer();
//foreach (RoleData::$list as $role => $v) RoleLoader::LoadMain(new User($role))->OutputAbility();

/* 配役情報 */
//Text::p(Lottery::ToProbability(ChaosConfig::$chaos_hyper_random_role_list));	//確率
//Text::p(array_sum(ChaosConfig::$chaos_hyper_random_role_list), '◆Sum');	//確率合計
//self::OutputChaosSumGroup();							//系列合計
//Text::p(ChaosConfig::$role_group_rate_list);					//系列係数

/* 天候情報 */
//Text::p(Lottery::ToProbability(GameConfig::$weather_list));

/* DB テスト */
//DB::Connect(); DB::d();
//$q = Query::Init()->Table('user_entry')->Select(['user_no'])->Where(['room_no']);
//$q->p();

class JinrouStatistics extends StackStaticManager {
  public static function GetRoom() {
    DB::Connect();
    RQ::Get()->page = 'all';
    foreach (RoomLoaderDB::GetFinished(false) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));
      DB::$ROOM->ParseOption();
      $stack = self::GetCategoryStack();
      self::UpdateRoom($stack);
      #Text::p($stack);
    }
    #Text::p(DB::$ROOM);

    self::OutputCategory();
    self::OutputCategoryWin();
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

  //カテゴリ別統計出力
  private static function OutputCategory() {
    TableHTML::OutputHeader('');
    TableHTML::OutputTh('種別');
    TableHTML::OutputTh('総数');
    TableHTML::OutputTh('日数');
    TableHTML::OutputTh('人数');
    $stack = self::Stack();
    foreach (self::GetCategoryList() as $category => $name) {
      TableHTML::OutputTrHeader();
      TableHTML::OutputTd($name);
      if ($stack->IsEmpty($category)) {
	$filter = new Stack();
      } else {
	$filter = $stack->Get($category);
      }
      foreach (['room', 'date', 'user_count'] as $data) {
	if ($filter->IsEmpty($data)) {
	  $number = 0;
	} else {
	  $number = $filter->Get($data);
	}
	TableHTML::OutputTd($number);
      }
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter();
  }

  //カテゴリ別勝利陣営統計出力
  private static function OutputCategoryWin() {
    foreach (self::GetCategoryList() as $category => $name) {
      Text::p($name);
      $stack = self::Stack()->Get($category);
      if (null === $stack) {
	continue;
      }

      TableHTML::OutputHeader('');
      $filter = $stack->Get('winner');
      Text::p($filter);
      $result = self::AggregateWinCamp($filter);
      foreach ($result as $camp => $count) {
	if ($count > 0) {
	  TableHTML::OutputTh(self::GetWinCampName($camp));
	}
      }
      TableHTML::OutputTrFooter();

      TableHTML::OutputTrHeader();
      foreach ($result as $camp => $count) {
	if ($count > 0) {
	  TableHTML::OutputTd($count);
	}
      }
      TableHTML::OutputTrFooter();
      TableHTML::OutputFooter();
    }
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

  //カテゴリリスト取得
  private static function GetCategoryList() {
    return [
      'normal'		=> '普通',
      'chaos'		=> '闇鍋',
      'duel'		=> '決闘',
      'gray_random'	=> 'グレラン',
      'quiz'		=> 'クイズ',
    ];
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

//JinrouStatistics::GetRoom();
