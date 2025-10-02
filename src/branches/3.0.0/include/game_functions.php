<?php
//-- 日時関連 (Game 拡張) --//
class GameTime {
  //リアルタイム制の経過時間
  static function GetRealPass(&$left_time) {
    $start_time = DB::$ROOM->scene_start_time; //シーン開始時刻
    $base_time  = DB::$ROOM->real_time->{DB::$ROOM->scene} * 60; //設定された制限時間 (秒)
    $pass_time  = DB::$ROOM->system_time - $start_time;
    if (DB::$ROOM->IsOption('wait_morning') && DB::$ROOM->IsDay()) { //早朝待機制
      $base_time += TimeConfig::WAIT_MORNING; //制限時間を追加する
      //待機判定
      DB::$ROOM->Stack()->Get('event')->Set('wait_morning', $pass_time <= TimeConfig::WAIT_MORNING);
    }
    $left_time = max(0, $base_time - $pass_time); //残り時間
    return $start_time + $base_time;
  }

  //仮想時間制の経過時間
  static function GetTalkPass(&$left_time, $silence = false) {
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
    return Time::Convert($full_time * $base_left_time * 60 * 60 / $base_time);
  }

  //残り時間取得
  static function GetLeftTime() {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      self::GetRealPass($left_time);
    } else {
      self::GetTalkPass($left_time);
    }
    return $left_time;
  }

  //経過時間取得
  static function GetPass() {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      return self::GetRealPass($left_time);
    } else {
      return self::GetTalkPass($left_time);
    }
  }

  //リアルタイム表示に使う JavaScript の変数を出力
  static function OutputTimer($end_time, $type = null, $flag = false) {
    $format = <<<EOF
<script language="JavaScript"><!--
var sentence       = "%s";
var std_time       = (new Date() - %s);
var end_date       = %s * 1 + std_time;
var diff_seconds   = Math.floor((%s - %s) / 1000);
var sound_flag     = %s;
var sound_file     = "%s";
var countdown_flag = %s;
var alert_distance = %d;

function updateEndDate(value) {
  end_date = value + std_time;
}
%s
EOF;
    $end_date = self::GetJavaScriptDate($end_time);

    echo HTML::LoadJavaScript('output_realtime');
    printf($format . Text::LF,
	   DB::$ROOM->IsDay() ? GameMessage::TIME_LIMIT_DAY : GameMessage::TIME_LIMIT_NIGHT,
	   self::GetJavaScriptDate(DB::$ROOM->system_time),
	   $end_date,
	   $end_date, self::GetJavaScriptDate(DB::$ROOM->scene_start_time),
	   isset($type) ? 'true' : 'false',
	   isset($type) && class_exists('Sound') ? Sound::Generate($type) : '',
	   $flag ? 'true' : 'false',
	   TimeConfig::ALERT_DISTANCE,
	   '//--></script>');
  }

  //JavaScript の Date() オブジェクト作成コードを生成する
  private static function GetJavaScriptDate($time) {
    $stack = explode(',', Time::GetDate('Y,m,j,G,i,s', $time));
    $stack[1]--;  //JavaScript の Date() の Month は 0 からスタートする
    return sprintf('new Date(%s)', implode(',', $stack));
  }
}

//-- 勝敗判定処理クラス --//
class Winner {
  //勝敗チェック
  static function Check($check_draw = false) {
    if (DB::$ROOM->IsTest()) return false;

    //コピー能力者がいるのでキャッシュを更新するかクエリから引くこと
    $human  = UserDataDB::CountCamp('human');  //村人
    $wolf   = UserDataDB::CountCamp('wolf');   //人狼
    $fox    = UserDataDB::CountCamp('fox');    //妖狐
    $lovers = UserDataDB::CountCamp('lovers'); //恋人
    $quiz   = UserDataDB::CountCamp('quiz');   //出題者

    //-- 吸血鬼の勝利判定 --//
    $vampire = false;
    $living_id_list = array(); //生存者の ID リスト
    $infected_list  = array(); //吸血鬼 => 感染者リスト
    foreach (DB::$USER->GetLivingUsers(true) as $id => $uname) {
      $user = DB::$USER->ByID($id);
      $user->Reparse();
      if (! $user->IsRole('psycho_infected')) $living_id_list[] = $user->id;
      if ($user->IsRole('infected')) {
	foreach ($user->GetPartner('infected') as $id) {
	  $infected_list[$id][] = $user->id;
	}
      }
    }
    if (count($living_id_list) == 1) {
      $vampire = DB::$USER->ByID(array_shift($living_id_list))->IsMainGroup('vampire');
    }
    else {
      foreach ($infected_list as $id => $stack) {
	$diff_list = array_diff($living_id_list, $stack);
	if (count($diff_list) == 1 && in_array($id, $diff_list)) {
	  $vampire = true;
	  break;
	}
      }
    }

    //勝利陣営判定
    if ($human == $quiz && $wolf == 0 && $fox == 0) { //全滅
      $winner = $quiz > 0 ? 'quiz' : 'vanish';
    } elseif ($vampire) { //吸血鬼支配
      $winner = $lovers > 1 ? 'lovers' : 'vampire';
    } elseif ($wolf == 0) { //狼全滅
      $winner = $lovers > 1 ? 'lovers' : ($fox > 0 ? 'fox1' : 'human');
    } elseif ($wolf >= $human) { //村全滅
      $winner = $lovers > 1 ? 'lovers' : ($fox > 0 ? 'fox2' : 'wolf');
    } elseif ($lovers >= $human + $wolf + $fox) { //恋人支配
      $winner = 'lovers';
    } elseif (DB::$ROOM->IsQuiz() && $quiz == 0) { //クイズ村 GM 死亡
      $winner = 'quiz_dead';
    } elseif ($check_draw && DB::$ROOM->revote_count >= GameConfig::DRAW) { //引き分け
      $winner = 'draw';
    } else {
      return false;
    }

    //ゲーム終了
    //JinrouRSS::Update(); //RSS機能はテスト中
    return RoomDB::Finish($winner);
  }

  //勝敗結果生成
  static function Generate($id = 0) {
    /* 村の勝敗結果 */
    $winner = DB::$ROOM->LoadWinner();
    $class  = $winner;
    $text   = $winner;

    switch ($winner) { //特殊ケース対応
    case 'fox1': //妖狐勝利
    case 'fox2':
      $winner = 'fox';
      $class  = $winner;
      break;

    case 'draw': //引き分け
    case 'vanish': //全滅
    case 'quiz_dead': //クイズ村 GM 死亡
      $class = 'draw';
      break;

    case null: //廃村
      $class = 'none';
      $text  = DB::$ROOM->date > 0 ? 'unfinished' : 'none';
      break;
    }
    $format = <<<EOF
<table class="winner winner-%s"><tr>
<td>%s</td>
</tr></table>
EOF;
    $str = sprintf($format . Text::LF, $class, WinnerMessage::$$text);

    /* 個々の勝敗結果 */
    //スキップ判定 (勝敗未決定/観戦モード/ログ閲覧モード)
    if (is_null($winner) || DB::$ROOM->IsOn('view') ||
	(DB::$ROOM->IsOn('log') && DB::$ROOM->IsOff('single') && DB::$ROOM->IsOff('personal'))) {
      return $id > 0 ? WinnerMessage::$personal_none : $str;
    }

    $result = 'win';
    $class  = null;
    $user   = $id > 0 ? DB::$USER->ByID($id) : DB::$SELF;
    if ($user->id < 1) return $str;

    $camp = $user->GetCamp(true); //所属陣営を取得
    switch ($winner) {
    case 'draw':   //引き分け
    case 'vanish': //全滅
      $result = 'draw';
      $class  = $result;
      break;

    case 'quiz_dead': //出題者死亡
      $result = $camp == 'quiz' ? 'lose' : 'draw';
      $class  = $result;
      break;

    default:
      RoleManager::Stack()->Set('class', null);
      switch ($camp) {
      case 'human':
      case 'wolf':
	$win_flag = $winner == $camp && RoleManager::LoadMain($user)->Win($winner);
	break;

      case 'fox':
	if ($user->IsFoxCount()) {
	  $win_flag = $winner == $camp && RoleManager::LoadMain($user)->Win($winner);
	} elseif (DB::$USER->GetFoxCount() > 0) {
	  $win_flag = $winner == $camp;
	} else {
	  $win_flag = $user->IsLive();
	}
	break;

      case 'vampire':
	$win_flag = $winner == $camp && (DB::$SELF->IsRoleGroup('mania') || $user->IsLive());
	break;

      case 'chiroptera':
	$win_flag = $user->IsLive();
	break;

      case 'ogre':
      case 'duelist':
	if ($user->IsRoleGroup('mania')) {
	  $win_flag = $user->IsLive();
	} else {
	  $win_flag = RoleManager::LoadMain($user)->Win($winner);
	}
	break;

      case 'tengu':
	$win_flag = RoleManager::GetClass($camp)->Win($winner);
	break;

      default:
	$win_flag = $winner == $camp;
	break;
      }

      if ($win_flag) { //ジョーカー系判定
	RoleManager::SetActor($user);
	foreach (RoleManager::Load('joker') as $filter) $filter->FilterWin($win_flag);
      }

      if ($win_flag) {
	if (is_null(RoleManager::Stack()->Get('class'))) {
	  $class = $camp;
	} else {
	  $class = RoleManager::Stack()->Get('class');
	}
      } else {
	$result = 'lose';
	$class  = $result;
      }
      break;
    }

    if ($id > 0) {
      switch ($result) {
      case 'win':
      case 'lose':
      case 'draw':
	return WinnerMessage::${'personal_' . $result};

      default:
	return WinnerMessage::$personal_none;
      }
    }

    return $str . sprintf($format, $class, WinnerMessage::${'self_' . $result});
  }

  //勝敗結果出力
  static function Output() { echo self::Generate(); }
}

//-- HTML 生成クラス (Game 拡張) --//
class GameHTML {
  //投票データを整形する
  static function ParseVote(array $raw_data, $date) {
    if (count($raw_data) < 1) return null; //投票総数

    $open_vote   = DB::$ROOM->IsOpenData() || DB::$ROOM->IsOption('open_vote'); //投票数開示判定
    $header      = '<td class="vote-name">';
    $table_stack = array();
    foreach ($raw_data as $raw) { //個別投票データのパース
      extract($raw);
      $vote_target = $open_vote ? ' ' . $vote . ' ' . GameMessage::VOTE_UNIT : '';
      $stack = array('<tr>' . $header . $handle_name,
		     '<td>' . $poll . ' ' . GameMessage::VOTE_UNIT,
		     '<td>' . sprintf(GameMessage::VOTE_TARGET, $vote_target),
		     $header . $target_name, '</tr>');
      $table_stack[$count][] = implode('</td>', $stack);
    }
    if (! RQ::Get()->reverse_log) krsort($table_stack); //正順なら逆転させる

    $format = '<tr><td class="vote-times" colspan="4">' . GameMessage::VOTE_COUNT . '</td>';
    $str    = '';
    foreach ($table_stack as $count => $stack) {
      array_unshift($stack, '<table class="vote-list">', sprintf($format, $date, $count));
      $stack[] = '</table>' . Text::LF;
      $str .= implode(Text::LF, $stack);
    }
    return $str;
  }

  //自動リロードリンク生成
  static function GenerateAutoReloadLink($url) {
    $format = GameMessage::AUTO_RELOAD_HEADER . '%s' . GameMessage::AUTO_RELOAD_FOOTER;

    if (RQ::Get()->auto_reload > 0) {
      $name = GameMessage::AUTO_RELOAD_MANUAL;
    } else {
      $name = sprintf($format, GameMessage::AUTO_RELOAD_MANUAL);
    }
    $str = sprintf('%s">%s</a>', $url, $name);

    foreach (GameConfig::$auto_reload_list as $time) {
      $name  = $time . GameMessage::AUTO_RELOAD_TIME;
      $value = RQ::Get()->auto_reload == $time ? sprintf($format, $name) : $name;
      $str .= sprintf(' %s&auto_reload=%d">%s</a>', $url, $time, $value);
    }

    return GameMessage::AUTO_RELOAD . '(' . $str . ')' . Text::LF;
  }

  //ログへのリンク生成
  static function GenerateLogLink() {
    $url    = 'old_log.php?room_no=' . DB::$ROOM->id;
    $header = DB::$ROOM->IsOn('view') ? GameMessage::LOG_LINK_VIEW : GameMessage::LOG_LINK;
    $str    = HTML::GenerateLogLink($url, true, Text::BRLF . $header);

    $header = GameMessage::LOG_LINK_ROLE;
    return $str . HTML::GenerateLogLink($url . '&add_role=on', false, Text::BRLF . $header);
  }

  //プレイヤー一覧生成
  static function GeneratePlayer($heaven = false) {
    //DB::$ROOM->Stack()->p('event', '◆Event');
    //キャッシュデータをセット
    $is_open     = DB::$ROOM->IsOpenData(true);
    $beforegame  = DB::$ROOM->IsBeforegame();
    $admin       = DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOption('gm_login');
    $base_path   = Icon::GetPath();
    $img_format  = '<img src="%s" style="border-color: %s;" alt="icon" title="%s" ' .
      Icon::GetTag() . '%s>';
    $name_format = '%s<font color="%s">' . Message::SYMBOL . '</font>%s';

    if ($is_open) {
      $trip_from = array(Message::TRIP, Message::TRIP_CONVERT);
      $trip_to   = array(Message::TRIP . Text::BR, Message::TRIP_CONVERT . Text::BR);
      $is_sex    = DB::$ROOM->IsFinished() && RQ::Get()->sex;
      if ($is_sex) $sex_list = Sex::GetList();
    }
    if ($admin && DB::$ROOM->IsNight()) {
      if (DB::$ROOM->Stack()->IsEmpty('vote')) DB::$ROOM->LoadVote();
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
    }

    $count = 0; //改行カウントを初期化
    $str   = '<div class="player"><table><tr>' . Text::LF;
    foreach (DB::$USER->rows as $id => $user) {
      if ($count > 0 && ($count % 5) == 0) $str .= Text::TR . Text::LF; //5個ごとに改行
      $count++;

      //投票済み判定
      switch (DB::$ROOM->scene) {
      case RoomScene::BEFORE:
	$voted = $user->vote_type == 'GAMESTART' || $user->IsDummyBoy(true);
	break;

      case RoomScene::DAY:
	$voted = $is_open && $user->target_no > 0;
	break;

      case RoomScene::NIGHT:
	$voted = $admin && $user->CheckVote($vote_data);
	break;

      default:
	$voted = false;
	break;
      }
      $td_header = $voted ? '<td class="already-vote">' : '<td>';
      $str .= $td_header;

      //生死情報に応じたアイコンを設定
      $path = $base_path . $user->icon_filename;
      if ($beforegame || DB::$ROOM->IsOn('watch') || DB::$USER->IsVirtualLive($id)) {
	$live  = sprintf('(%s)', GameMessage::LIVE);
	$mouse = '';
      }
      else {
	$live  = sprintf('(%s)', GameMessage::DEAD);
	$mouse = ' onMouseover="this.src=' . "'{$path}'" . '"'; //元のアイコン

	$path = Icon::GetDead(); //アイコンを死亡アイコンに入れ替え
	$mouse .= ' onMouseout="this.src=' . "'{$path}'" . '"';
      }

      if (DB::$ROOM->IsOn('personal')) {
	$live .= sprintf(Text::BR . '(%s)', Winner::Generate($user->id));
      }

      //ユーザプロフィールと枠線の色を追加
      //Title 内の改行はブラウザ依存あり (Firefox 系は無効)
      $profile = str_replace(Text::LF, '&#13;&#10', $user->profile);
      $str .= sprintf($img_format, $path, $user->color, $profile, $mouse) . '</td>' . Text::LF;

      //HN を追加
      $str .= sprintf($name_format, $td_header, $user->color, $user->handle_name);
      if (ServerConfig::DEBUG_MODE) $str .= sprintf(' (%d)', $id);

      if ($is_open) { //ゲーム終了後・死亡後＆霊界役職公開モードなら、役職・ユーザネームも表示
	$uname = str_replace($trip_from, $trip_to, $user->uname); //トリップ対応

	//憑依状態なら憑依しているユーザを追加
	$real_user = DB::$USER->ByReal($id);
	//交換憑依判定
	if ($real_user->IsSame($user)) $real_user = DB::$USER->TraceExchange($id);
	if (! $real_user->IsSame($user) && $real_user->IsLive()) {
	  $uname .= sprintf(Text::BR . Message::SPACER . '[%s]', $real_user->uname);
	}
	$uname = sprintf(Text::BR . Message::SPACER . '(%s)', $uname);

	$str .= $uname . Text::BR . $user->GenerateRoleName(); //役職情報を追加

	//ゲーム終了後のみ性別を表示
	if ($is_sex) $str .= sprintf(Text::BR . '(%s)', $sex_list[$user->sex]);
      }
      $str .= Text::BR . $live . '</td>' . Text::LF;
    }
    return $str . '</tr></table></div>' . Text::LF;
  }

  //死亡メッセージ生成
  static function GenerateDead() {
    if (! DB::$ROOM->IsPlaying()) return null; //スキップ判定

    $str = self::GenerateWeather() . self::LoadDead(); //天候メッセージも表示する

    //ログ閲覧モード以外なら前のシーンの死亡メッセージを追加
    if (DB::$ROOM->IsOn('log') || DB::$ROOM->IsTest() || DB::$ROOM->date < 2 ||
	(DB::$ROOM->IsDate(2) && DB::$ROOM->Isday())) {
      return $str;
    }

    //死者が無いときに境界線を入れない仕様にする場合はデータ取得結果をチェックする
    return $str . '<hr>' . self::LoadDead(true);
  }

  //遺言生成
  static function GenerateLastWords($shift = false) {
    //スキップ判定
    if (! (DB::$ROOM->IsPlaying() || DB::$ROOM->IsOn('log')) || DB::$ROOM->IsOn('personal')) {
      return null;
    }

    $stack_list = SystemMessageDB::GetLastWords($shift);
    if (count($stack_list) < 1) return null;

    $format = <<<EOF
<tr>
<td class="lastwords-title">%s<span>%s</span></td>
<td class="lastwords-body">%s</td>
</tr>
EOF;

    $str = '';
    foreach (Lottery::GetList($stack_list) as $stack) { //表示順はランダム
      extract($stack);
      $str .= sprintf($format . Text::LF,
		      $handle_name, GameMessage::LAST_WORDS_FOOTER, Text::Line($message));
    }

    $format = <<<EOF
<table class="system-lastwords"><tr>
<td>%s</td>
</tr></table>
<table class="lastwords">
%s</table>
EOF;
    return sprintf($format . Text::LF, GameMessage::LAST_WORDS_TITLE, $str);
  }

  //投票結果生成
  static function GenerateVote() {
    if (! DB::$ROOM->IsPlaying()) return null; //ゲーム中以外は出力しない
    if (DB::$ROOM->IsEvent('blind_vote') && ! DB::$ROOM->IsOpenData()) return null; //傘化け判定

    //昼なら前日、夜ならの今日の集計を表示
    $date = (DB::$ROOM->IsDay() && DB::$ROOM->IsOff('log')) ? DB::$ROOM->date - 1 : DB::$ROOM->date;
    return self::LoadVote($date);
  }

  //ヘッダ出力
  static function OutputHeader($css = 'game') {
    //-- 引数を格納 --//
    $title = ServerConfig::TITLE . GameMessage::TITLE;
    $url   = sprintf('game_frame.php?room_no=%d', DB::$ROOM->id);
    if (RQ::Get()->auto_reload > 0) $url .= sprintf('&auto_reload=%d', RQ::Get()->auto_reload);
    if (RQ::Get()->play_sound)      $url .= '&play_sound=on';
    if (RQ::Get()->list_down)       $url .= '&list_down=on';
    if (RQ::Get()->async)           $url .= '&async=on';

    //-- 引数と状態を照合して移動の有無を判定する --//
    //ゲーム画面→天国モード (ゲーム中に死亡)
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDead() &&
	! (DB::$ROOM->IsOn('dead') || DB::$ROOM->IsOn('heaven') || DB::$ROOM->IsOn('log'))) {
      $jump = $url . '&dead_mode=on';
      $str  = GameMessage::JUMP_HEAVEN;
    }
    elseif (DB::$ROOM->IsAfterGame() && DB::$ROOM->IsOn('dead')) { //天国モード→ゲーム終了画面
      $jump = $url;
      $str  = GameMessage::JUMP_AFTERGAME;
    }
    //霊界→ゲーム画面 (蘇生など)
    elseif (DB::$SELF->IsLive() && (DB::$ROOM->IsOn('dead') || DB::$ROOM->IsOn('heaven'))) {
      $jump = $url;
      $str  = GameMessage::JUMP_PLAYING;
    }
    else {
      $jump = '';
    }

    if ($jump != '') { //移動先が設定されていたら画面切り替え
      $str .= sprintf(Message::JUMP, $jump) . Text::LF . HTML::GenerateSetLocation();
      HTML::OutputResult($title, $str, $jump);
    }

    //-- 出力 --//
    HTML::OutputHeader($title, $css);
    HTML::OutputCSS(sprintf('css/game_%s', DB::$ROOM->scene));

    if (DB::$ROOM->IsOff('log')) { //過去ログ閲覧時は不要
      HTML::OutputJavaScript('change_css');
      $on_load = sprintf("change_css('%s');", DB::$ROOM->scene);
    }
    else {
      $on_load = '';
    }

    if (DB::$ROOM->IsAfterGame()) {
      //ゲーム終了後は自動更新しない
    }
    elseif (RQ::Get()->async) {
      HTML::OutputJavaScript('game_async');
      //リクエストパラメータのハッシュ
      if (method_exists(RQ::Get(), 'GetRawUrlStack')) {
        $params = array();
        foreach (RQ::Get()->GetRawUrlStack() as $name => $value) {
          $params[] = "'{$name}':'{$value}'";
        }
        $params = '{'.implode(',', $params).'}';
      }
      else {
        $params = '{}';
      }
      //ゲーム進行のハッシュ
      $room_status = array();
      $room_status[] = sprintf("'date':'%s'", DB::$ROOM->date);
      $room_status[] = sprintf("'scene':'%s'", DB::$ROOM->scene);
      $room_status = '{'.implode(',', $room_status).'}';
      //非同期処理の起動
      $on_load .= sprintf("game_async(%s, %s);", $params, $room_status); 
    }
    elseif (RQ::Get()->auto_reload != 0) { //自動リロードをセット
      self::OutputAutoReloadHeader();
    }

    //ゲーム中、リアルタイム制なら経過時間を Javascript でリアルタイム表示
    $game_top = '<a id="game_top"></a>';
    if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsRealTime() &&
	! (DB::$ROOM->IsOn('heaven') || DB::$ROOM->IsOn('log'))) {
      $end_time   = GameTime::GetRealPass($left_time);
      $sound_type = null;
      $alert_flag = false;
      $on_load .= 'output_realtime();';
      if ($left_time < 1 && DB::$SELF->IsLive()) { //超過判定
	DB::$ROOM->LoadVote(); //投票情報を取得
	if (DB::$ROOM->IsDay()) { //未投票判定
	  $novote_flag = ! DB::$SELF->ExistsVote();
	}
	elseif (DB::$ROOM->IsNight()) {
	  $novote_flag = DB::$SELF->CheckVote(DB::$ROOM->ParseVote()) === false;
	}

	if ($novote_flag) {
	  if (TimeConfig::ALERT > TimeConfig::SUDDEN_DEATH - RoomDB::GetTime()) { //警告判定
	    $alert_flag = true;
	    $sound_type = 'alert';
	  }
	  else {
	    $sound_type = 'novote';
	  }
	}
      }

      GameTime::OutputTimer($end_time, $sound_type, $alert_flag);
      $game_top .= Text::LF . '<span id="vote_alert"></span>';
    }

    HTML::OutputBodyHeader(null, $on_load);
    Text::Output($game_top);
  }

  //自動更新ヘッダ出力
  static function OutputAutoReloadHeader() {
    printf('<meta http-equiv="Refresh" content="%d">' . Text::LF, RQ::Get()->auto_reload);
  }

  //自動更新リンク出力
  static function OutputAutoReloadLink($url) {
    echo self::GenerateAutoReloadLink($url);
  }

  //ログへのリンク出力
  static function OutputLogLink() {
    echo self::GenerateLogLink();
  }

  //日付と生存者の人数を出力
  static function OutputTimeTable() {
    $str = '<table class="time-table"><tr>'; //ヘッダ
    if (! DB::$ROOM->IsBeforeGame()) { //ゲーム開始以後は生存者を表示
      $format = sprintf(Text::LF . '<td>%s</td>', GameMessage::TIME_TABLE);
      $str .= sprintf($format, DB::$ROOM->date, count(DB::$USER->GetLivingUsers()));
    }
    Text::Output($str);
  }

  //経過時間情報を出力
  static function OutputTimePass(&$left_time) {
    if (DB::$ROOM->IsRealTime()) {
      echo <<<EOF
<td class="real-time"><form name="realtime_form">
<input type="text" name="output_realtime" size="60" readonly>
</form></td>

EOF;
      GameTime::GetRealPass($left_time);
    }
    else {
      switch (DB::$ROOM->scene) {
      case RoomScene::DAY:
	$str = GameMessage::TIME_LIMIT_DAY;
	break;

      case RoomScene::NIGHT:
	$str = GameMessage::TIME_LIMIT_NIGHT;
	break;
      }
      printf('<td>%s%s</td>' . Text::LF, $str, GameTime::GetTalkPass($left_time));
    }
  }

  //投票関連メッセージ出力
  static function OutputVoteAnnounce($str = null) {
    $format = '<div class="system-vote">%s</div>' . Text::LF;
    if (is_null($str)) {
      switch (DB::$ROOM->scene) {
      case RoomScene::DAY:
	$str = GameMessage::TIME_LIMIT_DAY;
	break;

      case RoomScene::NIGHT:
	$str = GameMessage::TIME_LIMIT_NIGHT;
	break;
      }
      $str .= GameMessage::VOTE_ANNOUNCE;
    }
    printf($format, $str);
  }

  //プレイヤー一覧出力
  static function OutputPlayer() {
    echo self::GeneratePlayer();
  }

  //再投票メッセージ出力
  static function OutputRevote() {
    if (RQ::Get()->play_sound && DB::$ROOM->IsOff('view') && DB::$ROOM->vote_count > 1 &&
	DB::$ROOM->vote_count > JinrouCookie::$vote_count) {
      Sound::Output('revote'); //音を鳴らす (未投票突然死対応)
    }

    //投票結果表示は再投票のみ
    if (! DB::$ROOM->IsDay() || DB::$ROOM->revote_count < 1) return false;

    if (! isset(DB::$SELF->target_no)) { //投票済みチェック
      $format = sprintf('<div class="revote">%s</div>', GameMessage::REVOTE);
      printf($format . Text::BRLF, GameConfig::DRAW);
    }
    echo self::LoadVote(DB::$ROOM->date); //投票結果を出力
  }

  //遺言出力
  static function OutputLastWords($shift = false) {
    Text::OutputExists(self::GenerateLastWords($shift));
  }

  //死亡メッセージ出力
  static function OutputDead() {
    Text::OutputExists(self::GenerateDead());
  }

  //投票結果出力
  static function OutputVote() {
    Text::OutputExists(self::GenerateVote());
  }

  //指定した日付の投票結果をロードして ParseVote() に渡す
  private static function LoadVote($date) {
    if (DB::$ROOM->IsOn('personal')) return null; //スキップ判定
    return self::ParseVote(SystemMessageDB::GetVote($date), $date);
  }

  //死亡者情報を取得して ParseDead() に渡す
  private static function LoadDead($shift = false) {
    $str = '';
    $stack_list = SystemMessageDB::GetDead($shift);
    if (count($stack_list) > 0) {
      foreach (Lottery::GetList($stack_list) as $stack) { //表示順はランダム
	$str .= self::ParseDead($stack['handle_name'], $stack['type'], $stack['result']);
      }
    }
    return $str;
  }

  //死亡メッセージ整形
  private static function ParseDead($name, $type, $result) {
    if (isset($name)) $name .= ' ';
    $base   = true;
    $class  = null;
    $reason = null;
    $action = strtolower($type);
    $open_reason = DB::$ROOM->IsOpenData();
    $show_reason = $open_reason || self::FilterShowReason();

    $str = '<table class="dead-type">' . Text::LF;
    switch ($type) {
    case 'VOTE_KILLED':
    case 'VOTE_CANCELLED':
    case 'BLIND_VOTE':
      $base  = false;
      $class = 'vote';
      break;

    case 'FOX_FOLLOWED':
      $base  = false;
      $class = 'fox';
      break;

    case 'LOVERS_FOLLOWED':
    case 'VEGA_LOVERS':
      $base  = false;
      $class = 'lovers';
      break;

    case 'REVIVE_SUCCESS':
      $base  = false;
      $class = 'revive';
      break;

    case 'REVIVE_FAILED':
      if (! self::FilterShowReviveFailed()) return;
      $base  = false;
      $class = 'revive';
      break;

    case 'POSSESSED_TARGETED':
      if (! $open_reason) return;
      $base = false;
      break;

    case 'NOVOTED':
    case 'SILENCE':
      $base  = false;
      $class = 'sudden-death';
      break;

    case 'SUDDEN_DEATH':
      $base  = false;
      $class = 'sudden-death';
      if ($show_reason) $reason = strtolower($result);
      break;

    case 'FLOWERED':
    case 'CONSTELLATION':
    case 'PIERROT':
      $base   = false;
      $class  = 'fairy';
      $action = strtolower($type . '_' . $result);
      break;

    case 'JOKER_MOVED':
    case 'DEATH_NOTE_MOVED':
      if (! $open_reason) return;
      $base  = false;
      $class = 'fairy';
      break;

    case 'LETTER_EXCHANGE_MOVED':
      if (! $open_reason) return;
      $base  = false;
      $class = 'lovers';
      break;

    case 'WOLF_FAILED':
      if (! $open_reason && ! self::FilterShowWolfFailed()) return;
      $base   = false;
      $class  = 'wolf';
      $action = strtolower($type . '_' . $result);
      break;

    case 'STEP':
      $base  = false;
      $class = 'step';
      $stack = array();
      foreach (explode(' ', trim($name)) as $id) {
	$stack[] = DB::$USER->ByID($id)->handle_name;
      }
      $name = implode(' ', $stack) . ' ';
      break;

    default:
      if ($show_reason) $reason = $action;
      break;
    }

    $str .= is_null($class) ? '<tr>' : sprintf('<tr class="dead-type-%s">', $class);
    $str .= sprintf('<td>%s%s</td>', $name, DeadMessage::${$base ? 'deadman' : $action});
    if (isset($reason)) {
      $str .= sprintf(Text::TR . '<td>(%s%s)</td>', $name, DeadMessage::$$reason);
    }
    return $str . '</tr>' . Text::LF . '</table>' . Text::LF;
  }

  //天候メッセージ生成
  private static function GenerateWeather() {
    //スキップ判定
    if (DB::$ROOM->Stack()->IsEmpty('weather') ||
	(DB::$ROOM->IsOn('log') && ! DB::$ROOM->IsTest() && DB::$ROOM->IsNight())) {
      return '';
    }

    $format  = '<div class="weather">' . GameMessage::WEATHER . '</div>';
    $weather = WeatherData::Get(DB::$ROOM->Stack()->Get('weather'));
    return sprintf($format, $weather['name'], $weather['caption']);
  }

  //死因表示判定処理
  private static function FilterShowReason() {
    $flag = RoleManager::Stack()->Get('show_reason');
    if (is_null($flag)) {
      $flag = DB::$SELF->IsLive() && DB::$SELF->IsRole(RoleFilterData::$show_reason);
      RoleManager::Stack()->Set('show_reason', $flag);
    }
    return $flag;
  }

  //蘇生失敗表示判定処理
  private static function FilterShowReviveFailed() {
    $flag = RoleManager::Stack()->Get('show_revive_failed');
    if (is_null($flag)) {
      $flag = DB::$ROOM->IsFinished() || DB::$SELF->IsDead() ||
	DB::$SELF->IsRole(RoleFilterData::$show_revive_failed);
      RoleManager::Stack()->Set('show_revive_failed', $flag);
    }
    return $flag;
  }

  //人狼襲撃失敗表示判定
  private static function FilterShowWolfFailed() {
    $flag = RoleManager::Stack()->Get('show_wolf_failed');
    if (is_null($flag)) {
      $flag = DB::$SELF->IsLive() && DB::$SELF->IsRole(RoleFilterData::$show_wolf_failed);
      RoleManager::Stack()->Set('show_wolf_failed', $flag);
    }
    return $flag;
  }
}
