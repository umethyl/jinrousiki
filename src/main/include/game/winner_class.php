<?php
//-- ◆文字化け抑制◆ --//
//-- 勝敗判定処理クラス --//
final class Winner {
  const WIN	= 'win';
  const LOSE	= 'lose';
  const DRAW	= 'draw';

  //判定
  public static function Judge($draw = false) {
    if (DB::$ROOM->IsTest()) {
      return false;
    }

    //コピー能力者がいるのでキャッシュを更新するかクエリから引くこと
    $human  = UserLoaderDB::CountCamp(Camp::HUMAN);  //村人
    $wolf   = UserLoaderDB::CountCamp(Camp::WOLF);   //人狼
    $fox    = UserLoaderDB::CountCamp(Camp::FOX);    //妖狐
    $lovers = UserLoaderDB::CountCamp(Camp::LOVERS); //恋人
    $quiz   = UserLoaderDB::CountCamp(Camp::QUIZ);   //出題者

    //勝利陣営判定
    if (DB::$ROOM->IsQuiz() && $quiz == 0) { //クイズ村GM死亡
      if (true === UserDB::IsQuizFold()) { //降参判定
	if ($fox > 0) {
	  $winner = WinCamp::FOX_QUIZ;
	} elseif ($wolf > 0) {
	  $winner = WinCamp::WOLF_QUIZ;
	} elseif ($human > 0) {
	  $winner = WinCamp::HUMAN_QUIZ;
	} else {
	  $winner = WinCamp::VANISH;
	}
      } else {
	$winner = WinCamp::QUIZ_DEAD;
      }
    } elseif ($human == $quiz && $wolf == 0 && $fox == 0) { //全滅
      $winner = $quiz > 0 ? WinCamp::QUIZ : WinCamp::VANISH;
    } elseif (RoleLoader::Load('vampire')->CheckWin()) { //吸血鬼支配
      $winner = $lovers > 1 ? WinCamp::LOVERS : WinCamp::VAMPIRE;
    } elseif ($wolf == 0) { //人狼全滅
      $winner = $lovers > 1 ? WinCamp::LOVERS : ($fox > 0 ? WinCamp::FOX_HUMAN : WinCamp::HUMAN);
    } elseif ($wolf >= $human) { //人狼支配
      $winner = $lovers > 1 ? WinCamp::LOVERS : ($fox > 0 ? WinCamp::FOX_WOLF  : WinCamp::WOLF);
    } elseif ($lovers >= $human + $wolf + $fox) { //恋人支配
      $winner = WinCamp::LOVERS;
    } elseif (true === $draw && DB::$ROOM->revote_count >= GameConfig::DRAW) { //引き分け
      $winner = WinCamp::DRAW;
    } else {
      return false;
    }

    //ゲーム終了
    return RoomDB::Finish($winner);
  }

  //結果生成
  public static function Generate($id = 0) {
    /* 村の勝敗結果 */
    $winner = DB::$ROOM->LoadWinner();
    $class  = $winner;
    $text   = $winner;

    switch ($winner) { //特殊ケース対応
    case WinCamp::HUMAN_QUIZ:	//村人勝利
      $winner = WinCamp::HUMAN;
      $class  = $winner;
      break;

    case WinCamp::WOLF_QUIZ:	//人狼勝利
      $winner = WinCamp::WOLF;
      $class  = $winner;
      break;

    case WinCamp::FOX_HUMAN:	//妖狐勝利
    case WinCamp::FOX_WOLF:
    case WinCamp::FOX_QUIZ:
      $winner = WinCamp::FOX;
      $class  = $winner;
      break;

    case WinCamp::DRAW:		//引き分け
    case WinCamp::VANISH:	//全滅
    case WinCamp::QUIZ_DEAD:	//クイズ村GM死亡
      $class = WinCamp::DRAW;
      break;

    case null: //廃村
      $class = WinCamp::NONE;
      $text  = DateBorder::First() ? WinCamp::UNFINISHED : WinCamp::NONE;
      break;
    }
    $str = GameHTML::GenerateWinner($class, $text);

    /* 個々の勝敗結果 */
    //スキップ判定 (勝敗未決定/観戦モード/ログ閲覧モード)
    if ((null === $winner) || DB::$ROOM->IsOn(RoomMode::VIEW) ||
	(DB::$ROOM->IsOn(RoomMode::LOG) &&
	 DB::$ROOM->IsOff(RoomMode::SINGLE) &&
	 DB::$ROOM->IsOff(RoomMode::PERSONAL))
	) {
      return $id > 0 ? WinnerMessage::$personal_none : $str;
    }

    $result = self::WIN;
    $class  = null;
    $user   = $id > 0 ? DB::$USER->ByID($id) : DB::$SELF;
    if ($user->id < 1) {
      return $str;
    }

    $camp = $user->GetWinCamp(); //所属勝利陣営を取得
    switch ($winner) {
    case WinCamp::DRAW:		//引き分け
    case WinCamp::VANISH:	//全滅
      $result = self::DRAW;
      $class  = $result;
      break;

    case WinCamp::QUIZ_DEAD:	//クイズ村GM死亡
      $result = $camp == Camp::QUIZ ? self::LOSE : self::DRAW;
      $class  = $result;
      break;

    default:
      RoleManager::Stack()->Set('class', null);
      switch ($camp) {
      case Camp::HUMAN:
      case Camp::WOLF:
	$win_flag = $winner == $camp && RoleLoader::LoadMain($user)->Win($winner);
	break;

      case Camp::FOX:
	if (RoleUser::IsFoxCount($user)) {
	  $win_flag = $winner == $camp && RoleLoader::LoadMain($user)->Win($winner);
	} elseif (DB::$USER->GetFoxCount() > 0) {
	  $win_flag = $winner == $camp;
	} else {
	  $win_flag = $user->IsLive();
	}
	break;

      case Camp::VAMPIRE:
	$win_flag = $winner == $camp && ($user->IsRoleGroup('mania') || $user->IsLive());
	break;

      case Camp::CHIROPTERA:
	$win_flag = $user->IsLive();
	break;

      case Camp::OGRE:
      case Camp::DUELIST:
	if ($user->IsRoleGroup('mania')) {
	  $win_flag = $user->IsLive();
	} else {
	  $win_flag = RoleLoader::LoadMain($user)->Win($winner);
	}
	break;

      case Camp::TENGU:
	$win_flag = RoleLoader::Load($camp)->Win($winner);
	break;

      default:
	$win_flag = $winner == $camp;
	break;
      }

      if ($win_flag) { //ジョーカー系判定
	foreach (RoleLoader::LoadUser($user, 'joker') as $filter) {
	  $filter->FilterWin($win_flag);
	}
      }

      if ($win_flag) {
	if (RoleManager::Stack()->Exists('class')) {
	  $class = RoleManager::Stack()->Get('class');
	} else {
	  $class = $camp;
	}
      } else {
	$result = self::LOSE;
	$class  = $result;
      }
      break;
    }

    if ($id > 0) {
      switch ($result) {
      case self::WIN:
      case self::LOSE:
      case self::DRAW:
	return WinnerMessage::${'personal_' . $result};

      default:
	return WinnerMessage::$personal_none;
      }
    }
    return $str . GameHTML::GenerateWinner($class, 'self_' . $result);
  }

  //結果出力
  public static function Output() {
    echo self::Generate();
  }
}
