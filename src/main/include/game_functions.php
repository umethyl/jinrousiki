<?php
//-- ゲーム内処理クラス --//
final class GameAction {
  //突然死
  public static function SuddenDeath(array $target_list, string $reason) {
    foreach ($target_list as $id) {
      DB::$USER->SuddenDeath($id, $reason);
    }
    RoleLoader::Load('lovers')->Followed(true);
    RoleLoader::Load('medium')->InsertMediumResult();

    RoomTalk::StoreSystem(GameMessage::VOTE_RESET); //投票リセットメッセージ
    RoomDB::ResetVote(); //投票リセット
    if (Winner::Judge()) { //勝敗判定
      if (DB::$ROOM->IsOption('joker')) { //ジョーカー再配布
	RoleLoader::Load('joker')->ResetJoker();
      }
    }
  }

  //身代わり君の個別発言投稿判定
  public static function IsIndividual() {
    //身代わり君限定
    if (false === DB::$SELF->IsDummyBoy()) {
      return false;
    }

    //プレイ中限定
    if (false === DB::$ROOM->IsPlaying()) {
      return false;
    }

    //フラグ判定
    RQ::Fetch()->ParsePostOn(RequestDataTalk::INDIVIDUAL);
    if (RQ::Fetch()->Disable(RequestDataTalk::INDIVIDUAL)) {
      return false;
    }

    //対象者
    RQ::Fetch()->ParsePostInt(RequestDataTalk::TARGET);
    $target_id = RQ::Fetch()->{RequestDataTalk::TARGET};
    $user      = DB::$USER->ByID($target_id);
    if ($target_id != $user->id) {
      return false;
    }

    return true;
  }
}

//-- 位置関連クラス --//
final class Position {
  const BASE = 5; //一列の基数

  //X座標
  public static function GetX($id) {
    return (true === self::IsBase($id) ? self::BASE : $id % self::BASE);
  }

  //Y座標
  public static function GetY($id) {
    return floor($id / self::BASE) + (true === self::IsBase($id) ? 0 : 1);
  }

  //経路距離取得
  public static function GetRouteDistance($id, $viewer) {
    $x = abs(self::GetX($id) - self::GetX($viewer));
    $y = abs(self::GetY($id) - self::GetY($viewer));
    return $x + $y;
  }

  //東
  public static function GetEast($id) {
    $max   = DB::$USER->Count();
    $stack = [];
    for ($i = $id + 1; $i <= $max && $i % self::BASE != 1; $i++) {
      $stack[] = $i;
    }
    return $stack;
  }

  //西
  public static function GetWest($id) {
    $stack = [];
    for ($i = $id - 1; $i > 0 && $i % self::BASE != 0; $i--) {
      $stack[] = $i;
    }
    return $stack;
  }

  //南
  public static function GetSouth($id) {
    $max   = DB::$USER->Count();
    $stack = [];
    for ($i = $id + self::BASE; $i <= $max; $i += self::BASE) {
      $stack[] = $i;
    }
    return $stack;
  }

  //北
  public static function GetNorth($id) {
    $stack = [];
    for ($i = $id - self::BASE; $i > 0; $i -= self::BASE) {
      $stack[] = $i;
    }
    return $stack;
  }

  //縦軸
  public static function GetVertical($id) {
    $max   = DB::$USER->Count();
    $stack = [];
    for ($i = $id % self::BASE; $i <= $max; $i += self::BASE) {
      if ($i > 0) {
	$stack[] = $i;
      }
    }
    return $stack;
  }

  //横軸
  public static function GetHorizontal($id) {
    $max   = DB::$USER->Count();
    $start = $id - self::GetX($id) + 1;
    $stack = [];
    for ($i = $start; $i < $start + self::BASE && $i <= $max; $i++) {
      $stack[] = $i;
    }
    return $stack;
  }

  //周囲
  public static function GetAround(User $user) {
    $max   = DB::$USER->Count();
    $num   = $user->id;
    $stack = [];
    for ($i = -1; $i < 2; $i++) {
      $j = $num + $i * self::BASE;
      if (Number::OutRange($j, 1, $max + 1)) {
	continue;
      }

      if ($j <= $max) {
	$stack[] = $j;
      }
      if (self::ExistsWest($j)) {
	$stack[] = $j - 1;
      }
      if (self::ExistsEast($j, $max)) {
	$stack[] = $j + 1;
      }
    }
    return $stack;
  }

  //隣接
  public static function GetChain($id, $max) {
    $stack = [];
    if ($id - self::BASE >= 1) {
      $stack['N'] = $id - self::BASE;
    }
    if ($id + self::BASE <= $max) {
      $stack['S'] = $id + self::BASE;
    }
    if (self::ExistsWest($id)) {
      $stack['W'] = $id - 1;
    }
    if (self::ExistsEast($id, $max)) {
      $stack['E'] = $id + 1;
    }
    return $stack;
  }

  //十字
  public static function IsCross($id, $viewer) {
    return abs($id - $viewer) == self::BASE ||
      $id == $viewer - 1 && ($id     % self::BASE) != 0 ||
      $id == $viewer + 1 && ($viewer % self::BASE) != 0;
  }

  //基数倍判定
  private static function IsBase($id) {
    return ($id % self::BASE) == 0;
  }

  //東存在
  private static function ExistsEast($id, $max) {
    return ($id % self::BASE) != 0 && $id < $max;
  }

  //西存在
  private static function ExistsWest($id) {
    return ($id % self::BASE) != 1 && $id > 1;
  }
}

//-- 「異議」あり関連クラス --//
final class Objection {
  //クッキー用情報取得
  public static function GetCookie() {
    //KICK も含めたユーザ総数から配列をセット (index は 0 から)
    $stack = array_fill(0, DB::$USER->CountAll(), 0);

    //ユーザ全体の「異議」ありを集計
    $count = 0;
    foreach (DB::$USER->GetName() as $uname => $id) {
      $stack[$count++] = DB::$USER->ByID($id)->objection;
    }
    return $stack;
  }

  //会話メッセージ取得
  public static function GetTalk($sex) {
    $str = Text::AddFooter(TalkAction::OBJECTION, strtoupper($sex));
    return VoteTalkMessage::$$str;
  }

  //画像パス取得
  public static function GetImage() {
    return GameConfig::OBJECTION_IMAGE . 'objection_' . Sex::Get(self::GetUser()) . '.gif';
  }

  //残り回数取得
  public static function Count() {
    return GameConfig::OBJECTION - self::GetUser()->objection;
  }

  //セット判定
  public static function Set() {
    $user = self::GetUser();
    if (self::IsSetUser($user) && self::IsSetScene()) {
      $user->objection++;
      $user->Update('objection', $user->objection);

      $talk = new RoomTalkStruct(Sex::Get($user));
      $talk->Set(TalkStruct::UNAME,  $user->uname);
      $talk->Set(TalkStruct::ACTION, TalkAction::OBJECTION);
      DB::$ROOM->Talk($talk);
    }
  }

  //音声出力
  public static function OutputSound() {
    $cookie = Text::Parse(JinrouCookie::$objection, ','); //クッキーの値を配列に格納する
    $stack  = JinrouCookie::$objection_list;
    $count  = count($stack);
    if (count($cookie) == $count) {
      for ($i = 0; $i < $count; $i++) { //差分を計算 (index は 0 から)
	//差分があれば性別を確認して音を鳴らす
	if (isset($cookie[$i]) && $stack[$i] > $cookie[$i]) {
	  SoundHTML::Output('objection_' . Sex::Get(DB::$USER->ByID($i + 1)));
	}
      }
    }
  }

  //ユーザ取得
  private static function GetUser() {
    return DB::$SELF->GetVirtual(); //情報は憑依先を参照する
  }

  //セット有効判定 (ユーザ)
  private static function IsSetUser(User $user) {
    return RQ::Fetch()->set_objection && $user->objection < GameConfig::OBJECTION;
  }

  //セット有効判定 (シーン)
  private static function IsSetScene() {
    return DB::$ROOM->IsBeforeGame() ||
      (DB::$ROOM->IsDay() && DB::$SELF->IsLive() && GameTime::IsInTime());
  }
}

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
	$win_flag = $winner == $camp && (DB::$SELF->IsRoleGroup('mania') || $user->IsLive());
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
