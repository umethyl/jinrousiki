<?php
//-- 日時関連 (Game 拡張) --//
class GameTime {
  /* 取得系 */
  //経過時間取得
  public static function GetPass() {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      return self::GetRealPass($left_time);
    } else {
      return self::GetTalkPass($left_time);
    }
  }

  //経過時間取得 (リアルタイム制)
  public static function GetRealPass(&$left_time) {
    $start_time = DB::$ROOM->scene_start_time; //シーン開始時刻
    $base_time  = Time::ByMinute(DB::$ROOM->real_time->{DB::$ROOM->scene}); //設定された制限時間
    $pass_time  = DB::$ROOM->system_time - $start_time;
    if (DB::$ROOM->IsOption('wait_morning') && DB::$ROOM->IsDay()) { //早朝待機制
      $base_time += TimeConfig::WAIT_MORNING; //制限時間を追加する
      //待機判定
      DB::$ROOM->Stack()->Get('event')->Set('wait_morning', $pass_time <= TimeConfig::WAIT_MORNING);
    }
    $left_time = max(0, $base_time - $pass_time); //残り時間
    return $start_time + $base_time;
  }

  //経過時間取得 (仮想時間制)
  public static function GetTalkPass(&$left_time, $silence = false) {
    if (DB::$ROOM->IsDay()) { //昼は12時間
      $base_time = TimeConfig::DAY;
      $full_time = 12;
    } else { //夜は6時間
      $base_time = TimeConfig::NIGHT;
      $full_time = 6;
    }
    $spend_time     = TalkDB::GetSpendTime();
    $left_time      = max(0, $base_time - $spend_time); //残り時間
    $base_left_time = $silence ? TimeConfig::SILENCE_PASS : $left_time; //仮想時間の計算
    return Time::Convert(Time::ByHour($full_time * $base_left_time) / $base_time);
  }

  //残り時間取得
  public static function GetLeftTime() {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      self::GetRealPass($left_time);
    } else {
      self::GetTalkPass($left_time);
    }
    return $left_time;
  }

  //仮想時間制の発言量経過時間取得
  public static function GetSpendTime($str) {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制は無効にする
      return 0;
    } else {
      return min(4, max(1, floor(strlen($str) / 100))); //範囲は 1 - 4
    }
  }

  /* 判定系 */
  //超過前
  public static function IsInTime() {
    return self::GetLeftTime() > 0;
  }

  /* 変換系 */
  //JavaScript の Date() オブジェクト作成コード生成
  public static function ConvertJavaScriptDate($time) {
    $stack = Text::Parse(Time::GetDate('Y,m,j,G,i,s', $time), ',');
    $stack[1]--;  //JavaScript の Date() の Month は 0 からスタートする
    return sprintf('new Date(%s)', ArrayFilter::ToCSV($stack));
  }
}

//-- 位置関連クラス --//
class Position {
  const BASE = 5; //一列の基数

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
    $start = $id - ($id % self::BASE == 0 ? self::BASE : $id % self::BASE) + 1;
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
      if ($j < 1 || $max + 1 < $j) continue;
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
class Objection {
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
    return GameConfig::OBJECTION_IMAGE . 'objection_' . self::GetUser()->sex . '.gif';
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

      $talk = new RoomTalkStruct($user->sex);
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
	  SoundHTML::Output('objection_' . DB::$USER->ByID($i + 1)->sex);
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
    return RQ::Get()->set_objection && $user->objection < GameConfig::OBJECTION;
  }

  //セット有効判定 (シーン)
  private static function IsSetScene() {
    return DB::$ROOM->IsBeforeGame() ||
      (DB::$ROOM->IsDay() && DB::$SELF->IsLive() && GameTime::IsInTime());
  }
}

//-- 勝敗判定処理クラス --//
class Winner {
  const WIN	= 'win';
  const LOSE	= 'lose';
  const DRAW	= 'draw';

  //判定
  public static function Judge($draw = false) {
    if (DB::$ROOM->IsTest()) return false;

    //コピー能力者がいるのでキャッシュを更新するかクエリから引くこと
    $human  = UserLoaderDB::CountCamp(Camp::HUMAN);  //村人
    $wolf   = UserLoaderDB::CountCamp(Camp::WOLF);   //人狼
    $fox    = UserLoaderDB::CountCamp(Camp::FOX);    //妖狐
    $lovers = UserLoaderDB::CountCamp(Camp::LOVERS); //恋人
    $quiz   = UserLoaderDB::CountCamp(Camp::QUIZ);   //出題者

    //勝利陣営判定
    if ($human == $quiz && $wolf == 0 && $fox == 0) { //全滅
      $winner = $quiz > 0 ? WinCamp::QUIZ : WinCamp::VANISH;
    } elseif (RoleLoader::Load('vampire')->CheckWin()) { //吸血鬼支配
      $winner = $lovers > 1 ? WinCamp::LOVERS : WinCamp::VAMPIRE;
    } elseif ($wolf == 0) { //狼全滅
      $winner = $lovers > 1 ? WinCamp::LOVERS : ($fox > 0 ? WinCamp::FOX_HUMAN : WinCamp::HUMAN);
    } elseif ($wolf >= $human) { //村全滅
      $winner = $lovers > 1 ? WinCamp::LOVERS : ($fox > 0 ? WinCamp::FOX_WOLF  : WinCamp::WOLF);
    } elseif ($lovers >= $human + $wolf + $fox) { //恋人支配
      $winner = WinCamp::LOVERS;
    } elseif (DB::$ROOM->IsQuiz() && $quiz == 0) { //クイズ村 GM 死亡
      $winner = WinCamp::QUIZ_DEAD;
    } elseif ($draw && DB::$ROOM->revote_count >= GameConfig::DRAW) { //引き分け
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
    case WinCamp::FOX_HUMAN:	//妖狐勝利
    case WinCamp::FOX_WOLF:
      $winner = WinCamp::FOX;
      $class  = $winner;
      break;

    case WinCamp::DRAW:		//引き分け
    case WinCamp::VANISH:	//全滅
    case WinCamp::QUIZ_DEAD:	//クイズ村 GM 死亡
      $class = WinCamp::DRAW;
      break;

    case null: //廃村
      $class = WinCamp::NONE;
      $text  = DB::$ROOM->date > 0 ? WinCamp::UNFINISHED : WinCamp::NONE;
      break;
    }
    $str = GameHTML::GenerateWinner($class, $text);

    /* 個々の勝敗結果 */
    //スキップ判定 (勝敗未決定/観戦モード/ログ閲覧モード)
    if (is_null($winner) || DB::$ROOM->IsOn(RoomMode::VIEW) ||
	(DB::$ROOM->IsOn(RoomMode::LOG) &&
	 DB::$ROOM->IsOff(RoomMode::SINGLE) &&
	 DB::$ROOM->IsOff(RoomMode::PERSONAL))
	) {
      return $id > 0 ? WinnerMessage::$personal_none : $str;
    }

    $result = self::WIN;
    $class  = null;
    $user   = $id > 0 ? DB::$USER->ByID($id) : DB::$SELF;
    if ($user->id < 1) return $str;

    $camp = $user->GetWinCamp(); //所属勝利陣営を取得
    switch ($winner) {
    case WinCamp::DRAW:		//引き分け
    case WinCamp::VANISH:	//全滅
      $result = self::DRAW;
      $class  = $result;
      break;

    case WinCamp::QUIZ_DEAD:	//クイズ村 GM 死亡
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
