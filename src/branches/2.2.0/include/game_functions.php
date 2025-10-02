<?php
//-- 日時関連 (Game 拡張) --//
class GameTime {
  //リアルタイムの経過時間
  static function GetRealPass(&$left_time) {
    $start_time = DB::$ROOM->scene_start_time; //シーンの最初の時刻を取得
    $base_time  = DB::$ROOM->real_time->{DB::$ROOM->scene} * 60; //設定された制限時間 (秒)
    $pass_time  = DB::$ROOM->system_time - $start_time;
    if (DB::$ROOM->IsOption('wait_morning') && DB::$ROOM->IsDay()) { //早朝待機制
      $base_time += TimeConfig::WAIT_MORNING; //制限時間を追加する
      DB::$ROOM->event->wait_morning = $pass_time <= TimeConfig::WAIT_MORNING; //待機判定
    }
    $left_time = max(0, $base_time - $pass_time); //残り時間
    return $start_time + $base_time;
  }

  //会話で時間経過制の経過時間
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

  //リアルタイム表示に使う JavaScript の変数を生成
  static function GenerateTimer($end_time, $type = null, $flag = false) {
    $end_date = self::GetJavaScriptDate($end_time);
    $format = <<<EOF
<script language="JavaScript"><!--
var sentence       = "　%sまで ";
var end_date       = %s * 1 + (new Date() - %s);
var diff_seconds   = Math.floor((%s - %s) / 1000);
var sound_flag     = %s;
var sound_file     = "%s";
var countdown_flag = %s;
var alert_distance = %d;
%s
EOF;
    $str  = HTML::LoadJavaScript('output_realtime');
    $str .= sprintf($format,
		    DB::$ROOM->IsDay() ? '日没' : '夜明け',
		    $end_date, self::GetJavaScriptDate(DB::$ROOM->system_time),
		    $end_date, self::GetJavaScriptDate(DB::$ROOM->scene_start_time),
		    isset($type) ? 'true' : 'false',
		    isset($type) && class_exists('Sound') ? Sound::Generate($type) : '',
		    $flag ? 'true' : 'false',
		    TimeConfig::ALERT_DISTANCE,
		    '//--></script>' . Text::LF);
    return $str;
  }

  //リアルタイム表示に使う JavaScript の変数を出力
  static function OutputTimer($end_time, $type = null, $flag = false) {
    echo self::GenerateTimer($end_time, $type, $flag);
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
    if (DB::$ROOM->test_mode) return false;

    //コピー能力者がいるのでキャッシュを更新するかクエリから引くこと
    $human  = UserDataDB::GetCampCount('human');  //村人
    $wolf   = UserDataDB::GetCampCount('wolf');   //人狼
    $fox    = UserDataDB::GetCampCount('fox');    //妖狐
    $lovers = UserDataDB::GetCampCount('lovers'); //恋人
    $quiz   = UserDataDB::GetCampCount('quiz');   //出題者

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

    case null:  //廃村
      $class = 'none';
      $text  = DB::$ROOM->date > 0 ? 'unfinished' : 'none';
      break;
    }
    $format = <<<EOF
<table class="winner winner-%s"><tr>
<td>%s</td>
</tr></table>

EOF;
    $str = sprintf($format, $class, WinnerMessage::$$text);

    /* 個々の勝敗結果 */
    //スキップ判定 (勝敗未決定/観戦モード/ログ閲覧モード)
    if (is_null($winner) || DB::$ROOM->view_mode ||
	(DB::$ROOM->log_mode && ! DB::$ROOM->single_view_mode && ! DB::$ROOM->personal_mode)) {
      return $id > 0 ? '不明' : $str;
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
      RoleManager::SetStack('class', null);
      switch ($camp) {
      case 'human':
      case 'wolf':
      case 'fox':
	$win_flag = $winner == $camp && RoleManager::LoadMain($user)->Win($winner);
	break;

      case 'vampire':
	$win_flag = $winner == $camp && (DB::$SELF->IsRoleGroup('mania') || $user->IsLive());
	break;

      case 'chiroptera':
	$win_flag = $user->IsLive();
	break;

      case 'ogre':
      case 'duelist':
	$win_flag = $user->IsRoleGroup('mania') ? $user->IsLive()
	  : RoleManager::LoadMain($user)->Win($winner);
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
	$class = is_null(RoleManager::GetStack('class')) ? $camp : RoleManager::GetStack('class');
      }
      else {
	$result = 'lose';
	$class  = $result;
      }
      break;
    }
    if ($id > 0) {
      switch ($result) {
      case 'win':
	return '勝利';

      case 'lose':
	return '敗北';

      case 'draw':
	return '引分';

      default:
	return '不明';
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
      $stack = array('<tr>' .  $header . $handle_name, '<td>' . $poll . ' 票',
		     '<td>投票先' . ($open_vote ? ' ' . $vote . ' 票' : '') . ' →',
		     $header . $target_name, '</tr>');
      $table_stack[$count][] = implode('</td>', $stack);
    }
    if (! RQ::Get()->reverse_log) krsort($table_stack); //正順なら逆転させる

    $header = '<tr><td class="vote-times" colspan="4">' . $date . ' 日目 ( ';
    $footer = ' 回目)</td>';
    $str    = '';
    foreach ($table_stack as $count => $stack) {
      array_unshift($stack, '<table class="vote-list">', $header . $count . $footer);
      $stack[] = '</table>' . Text::LF;
      $str .= implode(Text::LF, $stack);
    }
    return $str;
  }

  //自動リロードリンク生成
  static function GenerateAutoReloadLink($url) {
    $str = sprintf('[自動更新](%s">%s</a>', $url, RQ::Get()->auto_reload > 0 ? '手動' : '【手動】');
    foreach (GameConfig::$auto_reload_list as $time) {
      $name  = $time . '秒';
      $value = RQ::Get()->auto_reload == $time ? sprintf('【%s】', $name) : $name;
      $str .= sprintf(' %s&auto_reload=%d">%s</a>', $url, $time, $value);
    }
    return $str . ')' . Text::LF;
  }

  //ログへのリンク生成
  static function GenerateLogLink() {
    $url    = 'old_log.php?room_no=' . DB::$ROOM->id;
    $header = DB::$ROOM->view_mode ? '[ログ]' : '[全体ログ]';
    $str    = HTML::GenerateLogLink($url, true, Text::BRLF . $header);

    $header = '[役職表示ログ]';
    return  $str . HTML::GenerateLogLink($url . '&add_role=on', false, Text::BRLF . $header);
  }

  //日付と生存者の人数を生成
  static function GenerateTimeTable() {
    $str = '<table class="time-table"><tr>' . Text::LF; //ヘッダ

    if (DB::$ROOM->IsBeforeGame()) return $str; //ゲームが始まっていなければスキップ
    $format = '<td> %d 日目<span>(生存者 %d 人)</span></td>' . Text::LF;
    return $str . sprintf($format, DB::$ROOM->date, count(DB::$USER->GetLivingUsers()));
  }

  //プレイヤー一覧生成
  static function GeneratePlayer($heaven = false) {
    //Text::p(DB::$ROOM->event);
    //キャッシュデータをセット
    $admin      = DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOption('gm_login');
    $open_data  = DB::$ROOM->IsOpenData(true);
    $beforegame = DB::$ROOM->IsBeforegame();
    $base_path  = Icon::GetPath();
    $img_format = '<img src="%s" style="border-color: %s;" alt="icon" title="%s" ' .
      Icon::GetTag() . '%s>';
    if ($admin && DB::$ROOM->IsNight()) {
      if (! isset(DB::$ROOM->vote)) DB::$ROOM->LoadVote();
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
    }
    if ($open_data) {
      $trip_from = array('◆', '◇');
      $trip_to   = array('◆<br>', '◇<br>');
    }
    $count = 0; //改行カウントを初期化
    $str   = '<div class="player"><table><tr>' . Text::LF;
    foreach (DB::$USER->rows as $id => $user) {
      if ($count > 0 && ($count % 5) == 0) $str .= Text::TR . Text::LF; //5個ごとに改行
      $count++;

      //投票済み判定
      switch (DB::$ROOM->scene) {
      case 'beforegame':
	$td_header = $user->vote_type == 'GAMESTART' || $user->IsDummyBoy(true) ?
	  '<td class="already-vote">' : '<td>';
	break;

      case 'day':
	$td_header = $open_data && $user->target_no > 0 ? '<td class="already-vote">' : '<td>';
	break;

      case 'night':
	$td_header = $admin && $user->CheckVote($vote_data) ? '<td class="already-vote">' : '<td>';
	break;

      default:
	$td_header = '<td>';
	break;
      }
      $str .= $td_header;

      //生死情報に応じたアイコンを設定
      $path = $base_path . $user->icon_filename;
      if ($beforegame || DB::$ROOM->watch_mode || DB::$USER->IsVirtualLive($id)) {
	$live  = '(生存中)';
	$mouse = '';
      }
      else {
	$live  = '(死亡)';
	$mouse = ' onMouseover="this.src=' . "'{$path}'" . '"'; //元のアイコン

	$path = Icon::GetDead(); //アイコンを死亡アイコンに入れ替え
	$mouse .= ' onMouseout="this.src=' . "'{$path}'" . '"';
      }

      if (DB::$ROOM->personal_mode) {
	$live .= Text::BR . sprintf('(%s)', Winner::Generate($user->id));
      }

      //ユーザプロフィールと枠線の色を追加
      //Title 内の改行はブラウザ依存あり (Firefox 系は無効)
      $profile = str_replace(Text::LF, '&#13;&#10', $user->profile);
      $str .= sprintf($img_format, $path, $user->color, $profile, $mouse) . '</td>'. Text::LF;

      //HN を追加
      $name_format = '%s<font color="%s">◆</font>%s';
      $str .= sprintf($name_format, $td_header, $user->color, $user->handle_name);
      if (ServerConfig::DEBUG_MODE) $str .= sprintf(' (%d)', $id);

      if ($open_data) { //ゲーム終了後・死亡後＆霊界役職公開モードなら、役職・ユーザネームも表示
	$str .= Text::BR . '　(' . str_replace($trip_from, $trip_to, $user->uname); //トリップ対応

	//憑依状態なら憑依しているユーザを追加
	$real_user = DB::$USER->ByReal($id);
	//交換憑依判定
	if ($real_user->IsSame($user)) $real_user = DB::$USER->TraceExchange($id);
	if (! $real_user->IsSame($user) && $real_user->IsLive()) {
	  $str .= Text::BR . sprintf('[%s]', $real_user->uname);
	}
	$str .= ')' . Text::BR . $user->GenerateRoleName(); //役職情報を追加
      }
      $str .= Text::BR . $live . '</td>' . Text::LF;
    }
    return $str . '</tr></table></div>' . Text::LF;
  }

  //死亡メッセージ生成
  static function GenerateDead() {
    //ゲーム中以外は出力しない
    if (! DB::$ROOM->IsPlaying()) return null;

    $str = self::GenerateWeather();
    $stack_list = SystemMessageDB::GetDead();
    if (count($stack_list) > 0) {
      shuffle($stack_list);
      foreach ($stack_list as $stack) {
	$str .= self::ParseDead($stack['handle_name'], $stack['type'], $stack['result']);
      }
    }

    //ログ閲覧モード以外なら二つ前も死亡者メッセージ表示
    if (DB::$ROOM->date < 2 || DB::$ROOM->log_mode || DB::$ROOM->test_mode ||
	(DB::$ROOM->IsDate(2) && DB::$ROOM->Isday())) {
      return $str;
    }
    $str .= '<hr>'; //死者が無いときに境界線を入れない仕様にする場合はクエリの結果をチェックする
    $stack_list = SystemMessageDB::GetDead(true);
    if (count($stack_list) > 0) {
      shuffle($stack_list);
      foreach ($stack_list as $stack) {
	$str .= self::ParseDead($stack['handle_name'], $stack['type'], $stack['result']);
      }
    }
    return $str;
  }

  //遺言生成
  static function GenerateLastWords($shift = false) {
    //スキップ判定
    if (! (DB::$ROOM->IsPlaying() || DB::$ROOM->log_mode) || DB::$ROOM->personal_mode) return null;
    $stack = SystemMessageDB::GetLastWords($shift);
    if (count($stack) < 1) return null;
    shuffle($stack); //表示順はランダム

    $str = '';
    foreach ($stack as $list) {
      extract($list);
      Text::Line($message);
      $str .= <<<EOF
<tr>
<td class="lastwords-title">{$handle_name}<span>さんの遺言</span></td>
<td class="lastwords-body">{$message}</td>
</tr>

EOF;
    }

    $format = <<<EOF
<table class="system-lastwords"><tr>
<td>%s</td>
</tr></table>
<table class="lastwords">
%s</table>

EOF;
    return sprintf($format, Message::$lastwords, $str);
  }

  //投票結果生成
  static function GenerateVote() {
    if (! DB::$ROOM->IsPlaying()) return null; //ゲーム中以外は出力しない
    if (DB::$ROOM->IsEvent('blind_vote') && ! DB::$ROOM->IsOpenData()) return null; //傘化け判定

    //昼なら前日、夜ならの今日の集計を表示
    $date = (DB::$ROOM->IsDay() && ! DB::$ROOM->log_mode) ? DB::$ROOM->date - 1 : DB::$ROOM->date;
    return self::LoadVote($date);
  }

  //ヘッダ出力
  static function OutputHeader($css = 'game') {
    //引数を格納
    $url_header = sprintf('game_frame.php?room_no=%d', DB::$ROOM->id);
    if (RQ::Get()->auto_reload > 0) {
      $url_header .= sprintf('&auto_reload=%d', RQ::Get()->auto_reload);
    }
    if (RQ::Get()->play_sound) $url_header .= '&play_sound=on';
    if (RQ::Get()->list_down)  $url_header .= '&list_down=on';

    $title = ServerConfig::TITLE . ' [プレイ]';
    $anchor_header = Text::BRLF;
    /*
      Mac に JavaScript でエラーを吐くブラウザがあった当時のコード
      現在の Safari・Firefox では不要なので false でスキップしておく
      //if (preg_match('/Mac( OS|intosh|_PowerPC)/i', $_SERVER['HTTP_USER_AGENT'])) {
      */
    if (false) {
      $sentence = '';
      $anchor_header .= '<a href="';
      $anchor_footer = '" target="_top">ここをクリックしてください</a>';
    }
    else {
      $sentence = HTML::GenerateSetLocation();
      $anchor_header .= '切り替わらないなら <a href="';
      $anchor_footer = '" target="_top">ここ</a>';
    }

    //ゲーム画面→天国モード (ゲーム中に死亡)
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDead() &&
	! (DB::$ROOM->log_mode || DB::$ROOM->dead_mode || DB::$ROOM->heaven_mode)) {
      $jump_url = $url_header . '&dead_mode=on';
      $sentence .= '天国モードに切り替えます。';
    }
    elseif (DB::$ROOM->IsAfterGame() && DB::$ROOM->dead_mode) { //天国モード→ゲーム終了画面
      $jump_url = $url_header;
      $sentence .= 'ゲーム終了後のお部屋に飛びます。';
    }
    elseif (DB::$SELF->IsLive() && (DB::$ROOM->dead_mode || DB::$ROOM->heaven_mode)) {
      $jump_url = $url_header;
      $sentence .= 'ゲーム画面に飛びます。';
    }
    else {
      $jump_url = '';
    }

    if ($jump_url != '') { //移動先が設定されていたら画面切り替え
      $sentence .= $anchor_header . $jump_url . $anchor_footer;
      HTML::OutputResult($title, $sentence, $jump_url);
    }

    HTML::OutputHeader($title, $css);
    HTML::OutputCSS(sprintf('css/game_%s', DB::$ROOM->scene));
    if (! DB::$ROOM->log_mode) { //過去ログ閲覧時は不要
      HTML::OutputJavaScript('change_css');
      $on_load = sprintf("change_css('%s');", DB::$ROOM->scene);
    }

    if (RQ::Get()->auto_reload != 0 && ! DB::$ROOM->IsAfterGame()) { //自動リロードをセット
      printf('<meta http-equiv="Refresh" content="%s">' . Text::LF, RQ::Get()->auto_reload);
    }

    //ゲーム中、リアルタイム制なら経過時間を Javascript でリアルタイム表示
    $game_top = '<a id="game_top"></a>';
    if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsRealTime() &&
	! (DB::$ROOM->log_mode || DB::$ROOM->heaven_mode)) {
      $end_time   = GameTime::GetRealPass($left_time);
      $sound_type = null;
      $alert_flag = false;
      $on_load .= 'output_realtime();';
      if ($left_time < 1 && DB::$SELF->IsLive()) { //超過判定
	DB::$ROOM->LoadVote(); //投票情報を取得
	if (DB::$ROOM->IsDay()) { //未投票判定
	  $novote_flag = ! array_key_exists(DB::$SELF->id, DB::$ROOM->vote);
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
      $game_top .= "\n".'<span id="vote_alert"></span>';
    }
    $body = isset($on_load) ? sprintf('<body onLoad="%s">', $on_load) : '<body>';
    printf("</head>\n%s\n%s\n", $body, $game_top);
  }

  //自動更新リンク出力
  static function OutputAutoReloadLink($url) { echo self::GenerateAutoReloadLink($url); }

  //ログへのリンク出力
  static function OutputLogLink() { echo self::GenerateLogLink(); }

  //日付と生存者の人数を出力
  static function OutputTimeTable() { echo self::GenerateTimeTable(); }

  //プレイヤー一覧出力
  static function OutputPlayer() { echo self::GeneratePlayer(); }

  //死亡メッセージ出力
  static function OutputDead() {
    Text::OutputExists(self::GenerateDead());
  }

  //遺言出力
  static function OutputLastWords($shift = false) {
    Text::OutputExists(self::GenerateLastWords($shift));
  }

  //投票結果出力
  static function OutputVote() {
    Text::OutputExists(self::GenerateVote());
  }

  //再投票メッセージ出力
  static function OutputRevote() {
    if (RQ::Get()->play_sound && ! DB::$ROOM->view_mode && DB::$ROOM->vote_count > 1 &&
	DB::$ROOM->vote_count > JinrouCookie::$vote_count) {
      Sound::Output('revote'); //音を鳴らす (未投票突然死対応)
    }

    //投票結果表示は再投票のみ
    if (! DB::$ROOM->IsDay() || DB::$ROOM->revote_count < 1) return false;

    if (is_null(DB::$SELF->target_no)) { //投票済みチェック
      $format = '<div class="revote">%s (%d回%s)</div>' . Text::BRLF;
      printf($format, Message::$revote, GameConfig::DRAW, Message::$draw_announce);
    }
    echo self::LoadVote(DB::$ROOM->date); //投票結果を出力
  }

  //投票結果表示 (クイズ村 GM 専用)
  static function OutputQuizVote() {
    $stack = array();
    foreach (SystemMessageDB::GetQuizVote() as $key => $list) {
      $stack[$list['target_no']][] = $key;
    }
    ksort($stack);
    $format = '<tr><td class="vote-name">%s</td><td class="vote-times">%d票</td></tr>';
    $table_stack = array('<table class="vote-list">',
			 '<tr class="vote-name"><td>名前</td><td>得票数</td></tr>');
    foreach ($stack as $id => $list) {
      $table_stack[] = sprintf($format, DB::$USER->ByID($id)->handle_name, count($list));
    }
    $table_stack[] = '</table>';
    Text::Output(implode(Text::LF, $table_stack));
  }

  //指定した日付の投票結果をロードして ParseVote() に渡す
  private static function LoadVote($date) {
    if (DB::$ROOM->personal_mode) return null; //スキップ判定
    return self::ParseVote(SystemMessageDB::GetVote($date), $date);
  }

  //死亡メッセージパース
  private static function ParseDead($name, $type, $result) {
    if (isset($name)) $name .= ' ';
    $base   = true;
    $class  = null;
    $reason = null;
    $action = strtolower($type);
    $open_reason = DB::$ROOM->IsOpenData();
    $show_reason = $open_reason || DB::$SELF->IsLiveRole('yama_necromancer');
    $str = '<table class="dead-type">' . Text::LF;
    switch ($type) {
    case 'VOTE_KILLED':
    case 'BLIND_VOTE':
      $base  = false;
      $class = 'vote';
      break;

    case 'LOVERS_FOLLOWED':
      $base  = false;
      $class = 'lovers';
      break;

    case 'REVIVE_SUCCESS':
      $base  = false;
      $class = 'revive';
      break;

    case 'REVIVE_FAILED':
      if (! DB::$ROOM->IsFinished() &&
	  ! (DB::$SELF->IsDead() || DB::$SELF->IsRole('attempt_necromancer', 'vajra_yaksa'))) {
	return;
      }
      $base  = false;
      $class = 'revive';
      break;

    case 'POSSESSED_TARGETED':
      if (! $open_reason) return;
      $base = false;
      break;

    case 'NOVOTED':
      $base  = false;
      $class = 'sudden-death';
      break;

    case 'SUDDEN_DEATH':
      $base   = false;
      $class  = 'sudden-death';
      $action = 'vote_sudden_death';
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
    $str .= sprintf('<td>%s%s</td>', $name, Message::${$base ? 'deadman' : $action});
    if (isset($reason)) $str .= sprintf(Text::TR . '<td>(%s%s)</td>', $name, Message::$$reason);
    return $str . "</tr>\n</table>" . Text::LF;
  }

  //天候メッセージ生成
  private static function GenerateWeather() {
    if (! isset(DB::$ROOM->event->weather) ||
	(! DB::$ROOM->test_mode && DB::$ROOM->log_mode && DB::$ROOM->IsNight())) {
      return '';
    }
    $format  = '<div class="weather">今日の天候は<span>%s</span>です (%s)</div>';
    $weather = WeatherData::Get(DB::$ROOM->event->weather);
    return sprintf($format, $weather['name'], $weather['caption']);
  }
}
