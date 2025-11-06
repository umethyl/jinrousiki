<?php
//-- HTML 生成クラス (Game 拡張) --//
final class GameHTML {
  //投票データを整形する
  public static function ParseVote(array $raw_list, $date) {
    if (count($raw_list) < 1) { //投票総数
      return null;
    }

    $open_vote   = DB::$ROOM->IsOpenData() || DB::$ROOM->IsOption('open_vote'); //投票数開示判定
    $table_stack = [];
    foreach ($raw_list as $raw) { //個別投票データのパース
      extract($raw);
      $vote_target = $open_vote ? ' ' . $vote . ' ' . GameMessage::VOTE_UNIT : '';
      $vote_base   = sprintf(self::GetVoteBase(),
	$handle_name, $poll, GameMessage::VOTE_UNIT,
	sprintf(GameMessage::VOTE_TARGET, $vote_target), $target_name
      );
      $table_stack[$count][] = $vote_base;
    }
    if (true !== RQ::Fetch()->reverse_log) { //正順なら逆転させる
      krsort($table_stack);
    }

    $str = '';
    foreach ($table_stack as $count => $stack) {
      $str .= Text::Format(self::GetVote(),
	sprintf(GameMessage::VOTE_COUNT, $date, $count),
	ArrayFilter::Concat($stack, Text::LF)
      );
    }
    return $str;
  }

  //自動リロードリンク生成
  public static function GenerateAutoReloadLink($url) {
    $format = GameMessage::AUTO_RELOAD_HEADER . '%s' . GameMessage::AUTO_RELOAD_FOOTER;

    if (RQ::Get(RequestDataGame::RELOAD) > 0) {
      $name = GameMessage::AUTO_RELOAD_MANUAL;
    } else {
      $name = sprintf($format, GameMessage::AUTO_RELOAD_MANUAL);
    }
    $str = self::GenerateHeaderLink($url, $name);

    foreach (GameConfig::$auto_reload_list as $time) {
      $name  = $time . GameMessage::AUTO_RELOAD_TIME;
      $value = (RQ::Get(RequestDataGame::RELOAD) == $time) ? sprintf($format, $name) : $name;
      $str .= ' ' . self::GenerateHeaderLink($url . URL::GetReload($time), $value);
    }

    return Text::LineFeed(GameMessage::AUTO_RELOAD . Text::Quote($str));
  }

  //勝敗生成
  public static function GenerateWinner($class, $str) {
    return Text::Format(self::GetWinner(),
      $class, DB::$ROOM->IsOn(RoomMode::AUTO_PLAY) ? ' hide' : '', WinnerMessage::$$str
    );
  }

  //ログへのリンク生成
  public static function GenerateLogLink() {
    $url    = URL::GetRoom('old_log');
    $header = DB::$ROOM->IsOn(RoomMode::VIEW) ? GameMessage::LOG_LINK_VIEW : GameMessage::LOG_LINK;
    $str    = LinkHTML::GenerateLog($url, true, Text::BRLF . $header);

    $header = GameMessage::LOG_LINK_ROLE;
    $url   .= URL::AddSwitch(RequestDataLogRoom::ROLE);
    return $str . LinkHTML::GenerateLog($url, false, Text::BRLF . $header);
  }

  //プレイ中ログリンク一覧ヘッダー生成
  public static function GenerateGameLogLinkListHeader() {
    return Text::BRLF . GameMessage::LOG_LINK_VIEW . ' ';
  }

  //プレイ中ログリンク一覧生成
  public static function GenerateGameLogLinkList($url) {
    $str = self::GenerateGameLogLink($url, RoomScene::BEFORE, 0);

    //オープニングあり対応
    if (DB::$ROOM->IsOption('open_day')) {
      if (DateBorder::Second() || DB::$ROOM->IsNight()) {
	$str .= self::GenerateGameLogLink($url, RoomScene::DAY, 1);
      }
    }

    if (DateBorder::Second()) {
      $str .= self::GenerateGameLogLink($url, RoomScene::NIGHT, 1);
      for ($i = 2; DateBorder::Upper($i); $i++) {
	$str .= self::GenerateGameLogLink($url, RoomScene::DAY, $i);
	$str .= self::GenerateGameLogLink($url, RoomScene::NIGHT, $i);
      }
      if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsNight()) {
	//プレイ中の夜は当日の昼も表示する
	$str .= self::GenerateGameLogLink($url, RoomScene::DAY, DB::$ROOM->date);
      }
      if (DB::$SELF->IsDummyBoy() && DB::$SELF->IsLive()) {
	//身代わり君生存中(実質クイズ村GM)は霊界も表示する
	$str .= self::GenerateGameLogLink($url, RoomScene::HEAVEN);
      }
    }

    return $str;
  }

  //プレイ中ログリンク生成
  public static function GenerateGameLogLink($url, $scene, $date = null) {
    $caption = self::GetGameLogLinkCaption($scene);
    if (true === isset($date)) {
      $url .= URL::AddInt(RequestDataGameLog::DATE, $date);
      $str = $date . Text::Quote($caption);
    } else {
      $str = $caption;
    }
    $url .= URL::AddString(RequestDataGameLog::SCENE, $scene);
    return Text::Format(self::GetGameLogLink(), $url, $str);
  }

  //プレイヤー一覧生成
  public static function GeneratePlayer($heaven = false) {
    //DB::$ROOM->Stack()->p('event', '◆Event');
    $stack = self::LoadPlayerStack(); //キャッシュデータをセット
    //$stack->p(null, '◆Player');

    $count = 0; //改行カウントを初期化
    $str   = Text::LineFeed(self::GetPlayerHeader());
    foreach (DB::$USER->Get() as $id => $user) {
      $str .= Text::Fold($count++, TableHTML::GenerateTrLineFeed());

      $td_header = self::GeneratePlayerVoteHeader($user, $stack); //投票済み判定
      $str .= $td_header;

      //生死情報に応じたアイコンを設定
      $path = $stack->path . $user->icon_filename;
      if ($stack->before || DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$USER->IsVirtualLive($id)) {
	$live  = Text::Quote(GameMessage::LIVE);
	$mouse = '';
      } else {
	$live  = Text::Quote(GameMessage::DEAD);
	$mouse = sprintf(self::GetMouseOver(), $path); //元のアイコン

	$path = Icon::GetDead(); //アイコンを死亡アイコンに入れ替え
	$mouse .= sprintf(self::GetMouseOut(), $path);
      }

      if (DB::$ROOM->IsOn(RoomMode::PERSONAL)) { //個別情報
	$live .= Text::BR . Text::Quote(Winner::Generate($user->id));
      }

      //アイコンと名前を設定
      $str .= sprintf($stack->image,
        $path, self::ConvertImageLine($user->profile), $user->color, Icon::GetSize(), $mouse,
        $td_header, $user->color, Message::SYMBOL, $user->handle_name
      );

      if (ServerConfig::DEBUG_MODE) { //デバッグモード
	$str .= ' ' . Text::Quote(intval($id));
      }

      if ($stack->open) { //公開情報
	$str .= self::GenerateOpenPlayer($id, $user, $stack);
      }
      $str .= Text::BR . $live;

      if ($stack->before && $stack->temporary_gm && $id == $stack->temporary_gm_id) { //仮GM
	$str .= Text::BR . HTML::GenerateSpan(Text::QuoteBracket(GameMessage::TEMPORARY_GM));
      }

      $str .= Text::LineFeed(TableHTML::GenerateTdFooter());
    }
    return Text::LineFeed($str . self::GetPlayerFooter());
  }

  //死亡メッセージ生成
  public static function GenerateDead() {
    if (false === DB::$ROOM->IsPlaying()) { //スキップ判定
      return null;
    }

    $str = self::GenerateWeather() . self::LoadDead(); //天候メッセージも表示する

    //ログ閲覧モード以外なら前のシーンの死亡メッセージを追加
    if (DB::$ROOM->IsOn(RoomMode::LOG) || DB::$ROOM->IsTest() || DateBorder::PreTwo() ||
	(DateBorder::Two() && DB::$ROOM->Isday())) {
      return $str;
    }

    //死者が無いときに境界線を入れない仕様にする場合はデータ取得結果をチェックする
    return $str . '<hr>' . self::LoadDead(true);
  }

  //遺言生成
  public static function GenerateLastWords($shift = false) {
    //スキップ判定
    if (! (DB::$ROOM->IsPlaying() || DB::$ROOM->IsOn(RoomMode::LOG)) ||
	DB::$ROOM->IsOn(RoomMode::PERSONAL)) {
      return null;
    }

    $stack_list = SystemMessageDB::GetLastWords($shift);
    if (count($stack_list) < 1) {
      return null;
    }

    $str = '';
    foreach (Lottery::GetList($stack_list) as $stack) { //表示順はランダム
      extract($stack);
      $str .= Text::Format(self::GetLastWords(),
        $handle_name, GameMessage::LAST_WORDS_FOOTER, Text::ConvertLine($message)
      );
    }

    return Text::Format(self::GetLastWordsTable(), GameMessage::LAST_WORDS_TITLE, $str);
  }

  //投票結果生成
  public static function GenerateVote() {
    if (false === DB::$ROOM->IsPlaying()) { //ゲーム中以外は出力しない
      return null;
    }

    if (DB::$ROOM->IsEvent('blind_vote') && false === DB::$ROOM->IsOpenData()) { //傘化け判定
      return null;
    }

    //昼なら前日、夜なら今日の集計を表示
    if (DB::$ROOM->IsDay() && DB::$ROOM->IsOff(RoomMode::LOG)) {
      $date = DB::$ROOM->date - 1;
    } else {
      $date = DB::$ROOM->date;
    }
    return self::LoadVote($date);
  }

  //ヘッダ出力
  public static function OutputHeader($css = 'game') {
    //-- 引数と状態を照合して移動の有無を判定する --//
    /*
      ゲーム画面 → 天国モード (ゲーム中に死亡)
      天国モード → ゲーム終了画面
      霊界       → ゲーム画面 (蘇生など)
    */
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDead() &&
	! (DB::$ROOM->IsOn(RoomMode::DEAD)   ||
	   DB::$ROOM->IsOn(RoomMode::HEAVEN) ||
	   DB::$ROOM->IsOn(RoomMode::LOG))
	) {
      $jump = self::GenerateJump() . URL::AddSwitch(RequestDataRoom::DEAD);
      $str  = GameMessage::JUMP_HEAVEN;
    } elseif (DB::$ROOM->IsAfterGame() && DB::$ROOM->IsOn(RoomMode::DEAD)) {
      $jump = self::GenerateJump();
      $str  = GameMessage::JUMP_AFTERGAME;
    } elseif (DB::$SELF->IsLive() &&
	      (DB::$ROOM->IsOn(RoomMode::DEAD) || DB::$ROOM->IsOn(RoomMode::HEAVEN))) {
      $jump = self::GenerateJump();
      $str  = GameMessage::JUMP_PLAYING;
    } else {
      $jump = '';
    }

    if ($jump != '') { //移動先が設定されていたら画面切り替え
      $str .= Text::Format(Message::JUMP, $jump) . JavaScriptHTML::GenerateJump();
      HTML::OutputResult(ServerConfig::TITLE . GameMessage::TITLE, $str, $jump);
    }

    //-- 出力 --//
    HTML::OutputHeader(ServerConfig::TITLE . GameMessage::TITLE, $css);
    HTML::OutputCSS(sprintf('css/game_%s', DB::$ROOM->scene));

    if (DB::$ROOM->IsOff(RoomMode::LOG)) { //過去ログ閲覧時は不要
      JavaScriptHTML::Output('change_css');
      $on_load = sprintf("change_css('%s');", DB::$ROOM->scene);
    } else {
      $on_load = '';
    }

    if (DB::$ROOM->IsAfterGame()) {
      //ゲーム終了後は自動更新しない
    } elseif (RQ::Fetch()->async) {
      JavaScriptHTML::Output('game_async');
      //リクエストパラメータのハッシュ
      if (method_exists(RQ::Fetch(), 'GetRawUrlStack')) {
        $params = [];
        foreach (RQ::Fetch()->GetRawUrlStack() as $name => $value) {
          $params[] = "'{$name}':'{$value}'";
        }
        $params = '{'. ArrayFilter::ToCSV($params) . '}';
      } else {
        $params = '{}';
      }
      //ゲーム進行のハッシュ
      $room_status = [];
      $room_status[] = sprintf("'date':'%s'",  DB::$ROOM->date);
      $room_status[] = sprintf("'scene':'%s'", DB::$ROOM->scene);
      $room_status = '{'. ArrayFilter::ToCSV($room_status) . '}';
      //非同期処理の起動
      $on_load .= sprintf("game_async(%s, %s);", $params, $room_status);
    } else {
      self::OutputNoCacheHeader();
      if (RQ::Get(RequestDataGame::RELOAD) != 0) { //自動リロードをセット
	self::OutputAutoReloadHeader();
      }
    }

    //ゲーム中、リアルタイム制なら経過時間を Javascript でリアルタイム表示
    $game_top = self::GetGameTop();
    if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsRealTime() &&
	! (DB::$ROOM->IsOn(RoomMode::HEAVEN) || DB::$ROOM->IsOn(RoomMode::LOG))) {
      $end_time   = GameTime::GetRealPass($left_time);
      $sound_type = null;
      $alert_flag = false;
      $on_load .= 'output_realtime();';
      //超過判定 (身代わり君は霊界でも有効)
      if ($left_time < 1 && (DB::$SELF->IsLive() || DB::$SELF->IsDummyBoy())) {
	DB::$ROOM->LoadVote(); //投票情報を取得
	if (DB::$ROOM->IsDay()) { //未投票判定
	  $novote_flag = ! DB::$SELF->ExistsVote();
	} elseif (DB::$ROOM->IsNight()) {
	  $novote_flag = RoleUser::ImcompletedVoteNight(DB::$SELF, DB::$ROOM->ParseVote());
	}

	if ($novote_flag) {
	  if (TimeConfig::ALERT > TimeConfig::SUDDEN_DEATH - RoomDB::GetTime()) { //警告判定
	    $alert_flag = true;
	    $sound_type = 'alert';
	  } else {
	    $sound_type = 'novote';
	  }
	}
      }

      self::OutputTimer($end_time, $sound_type, $alert_flag);
      $game_top .= Text::LF . HTML::GenerateSpan('', null, 'vote_alert');
    }

    HTML::OutputBodyHeader(null, $on_load);
    Text::Output($game_top);
  }

  //ヘッダリンク出力
  public static function OutputHeaderLink($url, $str) {
    Text::Output(self::GenerateHeaderLink($url, $str));
  }

  //シーン用CSSヘッダ出力
  public static function OutputSceneCSS() {
    Text::Output(self::GetSceneCSS());
  }

  //ゲームトップアンカー出力
  public static function OutputGameTop() {
    Text::Output(self::GetGameTop());
  }

  //キャッシュ抑制ヘッダ出力
  public static function OutputNoCacheHeader() {
    Text::Output(self::GetNoCache());
  }

  //自動更新ヘッダ出力
  public static function OutputAutoReloadHeader() {
    Text::Output(sprintf(self::GetReload(), RQ::Get(RequestDataGame::RELOAD)));
  }

  //自動更新リンク出力
  public static function OutputAutoReloadLink($url) {
    echo self::GenerateAutoReloadLink($url);
  }

  //ログへのリンク出力
  public static function OutputLogLink() {
    echo self::GenerateLogLink();
  }

  //プレイ中ログリンク一覧ヘッダー出力
  public static function OutputGameLogLinkListHeader() {
    echo self::GenerateGameLogLinkListHeader();
  }

  //プレイ中ログリンク一覧出力
  public static function OutputGameLogLinkList($url) {
    echo self::GenerateGameLogLinkList($url);
  }

  //プレイ中ログリンク出力
  public static function OutputGameLogLink($url, $scene, $date = null) {
    echo self::GenerateGameLogLink($url, $scene, $date);
  }

  //タイマー JavaScript コード出力 (リアルタイム用)
  public static function OutputTimer($end_time, $type = null, $flag = false) {
    $end_date   = GameTime::ConvertJavaScriptDate($end_time);
    $play_sound = (true === isset($type)) && RQ::Fetch()->play_sound;

    JavaScriptHTML::Output('output_realtime');
    JavaScriptHTML::OutputHeader();
    Text::Printf(self::GetTimer(),
      DB::$ROOM->IsDay() ? GameMessage::TIME_LIMIT_DAY : GameMessage::TIME_LIMIT_NIGHT,
      GameTime::ConvertJavaScriptDate(DB::$ROOM->system_time),
      $end_date,
      $end_date, GameTime::ConvertJavaScriptDate(DB::$ROOM->scene_start_time),
      Switcher::GetBool($play_sound),
      (true === $play_sound) ? SoundHTML::Generate($type) : '',
      Switcher::GetBool($flag),
      TimeConfig::ALERT_DISTANCE
    );
    JavaScriptHTML::OutputFooter();
  }

  //日付と生存者の人数を出力
  public static function OutputTimeTable() {
    $str = TableHTML::GenerateHeader('time-table'); //ヘッダ
    if (DB::$ROOM->IsBeforeGame()) {
      if (DB::$ROOM->IsClosing()) { //募集停止判定
	$str .= Text::LF . TableHTML::GenerateTd(GameMessage::CLOSING, RoomStatus::CLOSING);
      }
    } else { //ゲーム開始以後は生存者を表示
      $td = sprintf(GameMessage::TIME_TABLE, DB::$ROOM->date, DB::$USER->CountLive());
      $str .= Text::LF . TableHTML::GenerateTd($td);
    }
    Text::Output($str);
  }

  //経過時間情報を出力
  public static function OutputTimePass(&$left_time) {
    if (DB::$ROOM->IsRealTime()) {
      Text::Output(self::GetRealTime());
      GameTime::GetRealPass($left_time);
    } else {
      switch (DB::$ROOM->scene) {
      case RoomScene::DAY:
	$str = GameMessage::TIME_LIMIT_DAY;
	break;

      case RoomScene::NIGHT:
	$str = GameMessage::TIME_LIMIT_NIGHT;
	break;
      }
      TableHTML::OutputTd($str . GameTime::GetTalkPass($left_time));
    }
  }

  //投票関連メッセージ出力
  public static function OutputVoteAnnounce($str = null) {
    if (null === $str) {
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
    DivHTML::Output($str, 'system-vote');
  }

  //プレイヤー一覧出力
  public static function OutputPlayer() {
    echo self::GeneratePlayer();
  }

  //再投票メッセージ出力
  public static function OutputRevote() {
    if (RQ::Fetch()->play_sound && DB::$ROOM->IsOff(RoomMode::VIEW) &&
	DB::$ROOM->vote_count > 1 &&
	DB::$ROOM->vote_count > JinrouCookie::$vote_count) {
      SoundHTML::Output('revote'); //音を鳴らす (未投票突然死対応)
    }

    //投票結果表示は再投票のみ
    if (false === DB::$ROOM->IsDay() || DB::$ROOM->revote_count < 1) {
      return false;
    }

    if (false === isset(DB::$SELF->target_no)) { //投票済みチェック
      $format = DivHTML::Generate(GameMessage::REVOTE, 'revote');
      printf($format . Text::BRLF, GameConfig::DRAW);
    }
    echo self::LoadVote(DB::$ROOM->date); //投票結果を出力
  }

  //遺言出力
  public static function OutputLastWords($shift = false) {
    Text::OutputExists(self::GenerateLastWords($shift));
  }

  //死亡メッセージ出力
  public static function OutputDead() {
    Text::OutputExists(self::GenerateDead());
  }

  //投票結果出力
  public static function OutputVote() {
    Text::OutputExists(self::GenerateVote());
  }

  //指定した日付の投票結果をロードして ParseVote() に渡す
  private static function LoadVote($date) {
    if (DB::$ROOM->IsOn(RoomMode::PERSONAL)) { //スキップ判定
      return null;
    }

    return self::ParseVote(SystemMessageDB::GetVote($date), $date);
  }

  //ヘッダリンク生成
  private static function GenerateHeaderLink($url, $str) {
    return sprintf(self::GetHeaderLink(), $url, $str);
  }

  //移動先 URL 取得
  private static function GenerateJump() {
    $url = URL::GetRoom('game_frame');
    if (RQ::Get(RequestDataGame::RELOAD) > 0) {
      $url .= RQ::Fetch()->ToURL(RequestDataGame::RELOAD, true);
    }

    $stack = [RequestDataGame::SOUND, RequestDataGame::LIST];
    if (GameConfig::ASYNC) {
      $stack[] = RequestDataGame::ASYNC;
    }

    foreach ($stack as $key) {
      $url .= RQ::Fetch()->ToURL($key);
    }
    return $url;
  }

  //プレイヤー一覧用キャッシュセット
  private static function LoadPlayerStack() {
    $stack = new Stack();
    $stack->Set('open',   DB::$ROOM->IsOpenData(true));
    $stack->Set('before', DB::$ROOM->IsBeforegame());
    $stack->Set('admin',  DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOption('gm_login'));
    $stack->Set('path',   Icon::GetPath());
    $stack->Set('image',  self::GetPlayerImage());

    if ($stack->open) {
      $stack->Set('trip_from', [Message::TRIP, Message::TRIP_CONVERT]);
      $stack->Set('trip_to',   [Message::TRIP . Text::BR, Message::TRIP_CONVERT . Text::BR]);
      $stack->Set('sex', DB::$ROOM->IsFinished() && RQ::Fetch()->sex);
      if ($stack->sex) {
	$stack->Set('sex_list', Sex::GetList());
      }
    }

    if ($stack->before) {
      //仮GM表示判定 (ログでは表示しない想定でステータスも参照しておく)
      $option = 'temporary_gm';
      $stack->Set($option, DB::$ROOM->IsOption($option) && false === DB::$ROOM->IsFinished());
      if ($stack->$option) {
	$filter = OptionLoader::Load($option);
	$stack->Set('temporary_gm_id', $filter->GetTemporaryGMID());
      }
    }

    if ($stack->admin && DB::$ROOM->IsNight()) {
      if (DB::$ROOM->Stack()->IsEmpty('vote')) {
	DB::$ROOM->LoadVote();
      }
      $stack->vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
    }

    return $stack;
  }

  //プレイヤー投票済み判定
  private static function GeneratePlayerVoteHeader(User $user, Stack $stack) {
    switch (DB::$ROOM->scene) {
    case RoomScene::BEFORE:
      $voted = ($user->vote_type == VoteAction::GAME_START) || $user->IsDummyBoy(true);
      break;

    case RoomScene::DAY:
      $voted = $stack->open && $user->target_no > 0;
      break;

    case RoomScene::NIGHT:
      $voted = $stack->admin && RoleUser::CompletedVoteNight($user, $stack->vote_data);
      break;

    default:
      $voted = false;
      break;
    }
    return TableHTML::GenerateTdHeader($voted ? 'already-vote' : null);
  }

  //ユーザ公開情報生成
  private static function GenerateOpenPlayer($id, User $user, Stack $stack) {
    $real = DB::$USER->ByReal($id); //実ユーザ
    if ($real->IsSame($user)) {
      $real = DB::$USER->TraceExchange($id); //交換憑依判定
    }

    $header = Text::BR . Message::SPACER;
    $uname  = str_replace($stack->trip_from, $stack->trip_to, $user->uname); //トリップ対応
    if (! $real->IsSame($user) && $real->IsLive()) { //憑依状態なら憑依者を追加
      $uname .= $header . Text::QuoteBracket($real->uname);
    }

    $str = $header . Text::Quote($uname) . Text::BR . $user->GenerateRoleName();
    if ($stack->sex) { //性別 (ゲーム終了後のみ)
      $str .= Text::BR . Text::Quote($stack->sex_list[Sex::Get($user)]);
    }

    return $str;
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
    if (isset($name)) {
      $name .= ' ';
    }
    $base   = true;
    $class  = null;
    $reason = null;
    $action = strtolower($type);
    $open_reason = DB::$ROOM->IsOpenData();
    $show_reason = (true === $open_reason) || self::FilterShowReason();

    switch ($type) {
    case DeadReason::VOTE_KILLED:
    case DeadReason::VOTE_CANCELLED:
    case DeadReason::BLIND_VOTE:
      $base  = false;
      $class = 'vote';
      break;

    case DeadReason::FOX_FOLLOWED:
      $base  = false;
      $class = 'fox';
      break;

    case DeadReason::LOVERS_FOLLOWED:
    case DeadReason::VEGA_LOVERS:
      $base  = false;
      $class = 'lovers';
      break;

    case DeadReason::REVIVE_SUCCESS:
      $base  = false;
      $class = 'revive';
      break;

    case DeadReason::GENDER_STATUS:
      $base  = false;
      $class = 'fairy';
      break;

    case DeadReason::REVIVE_FAILED:
      if (false === self::FilterShowReviveFailed()) {
	return;
      }
      $base  = false;
      $class = 'revive';
      break;

    case DeadReason::POSSESSED_TARGETED:
      if (false === $open_reason) {
	return;
      }
      $base = false;
      break;

    case DeadReason::NOVOTED:
    case DeadReason::SILENCE:
    case DeadReason::FORCE_SUDDEN_DEATH:
      $base  = false;
      $class = 'sudden-death';
      break;

    case DeadReason::SUDDEN_DEATH:
      $base  = false;
      $class = 'sudden-death';
      if (true === $show_reason) {
	$reason = strtolower($result);
      }
      break;

    case DeadReason::FLOWERED:
    case DeadReason::CONSTELLATION:
    case DeadReason::PIERROT:
      $base   = false;
      $class  = 'fairy';
      $action = strtolower($type . '_' . $result);
      break;

    case DeadReason::JOKER_MOVED:
    case DeadReason::DEATH_NOTE_MOVED:
      if (false === $open_reason) {
	return;
      }

      $base  = false;
      $class = 'fairy';
      break;

    case DeadReason::LETTER_EXCHANGE_MOVED:
      if (false === $open_reason) {
	return;
      }

      $base  = false;
      $class = 'lovers';
      break;

    case DeadReason::WOLF_FAILED:
      if (false === $open_reason && false === self::FilterShowWolfFailed()) {
	return;
      }

      $base   = false;
      $class  = 'wolf';
      $action = strtolower($type . '_' . $result);
      break;

    case DeadReason::STEP:
      $base  = false;
      $class = 'step';
      $stack = [];
      foreach (Text::Parse(trim($name)) as $id) {
	$stack[] = DB::$USER->ByID($id)->handle_name;
      }
      $name = ArrayFilter::Concat($stack) . ' ';
      break;

    case DeadReason::ACTIVE_CRITICAL_VOTER:
      $base  = false;
      $class = 'authority';
      break;

    case DeadReason::ACTIVE_CRITICAL_LUCK:
      $base  = false;
      $class = 'luck';
      break;

    case DeadReason::COPIED_TRICK:
      if (false === $open_reason) {
	return;
      }

      $base  = false;
      $class = 'mania';
      $name .= Text::Quote(RoleDataManager::GetName($result)) . ' ';
      break;

    default:
      if (true === $show_reason) {
	$reason = $action;
      }
      break;
    }

    $str  = Text::LineFeed(TableHTML::GenerateHeader('dead-type', false));
    $str .= TableHTML::GenerateTrHeader((null === $class) ? null : 'dead-type-' . $class);
    $str .= TableHTML::GenerateTd($name . DeadMessage::${true === $base ? 'deadman' : $action});
    if (isset($reason)) {
      $str .= TableHTML::GenerateTrLineFeed();
      $str .= TableHTML::GenerateTd(Text::Quote($name . DeadMessage::$$reason));
    }
    $str .= Text::LineFeed(TableHTML::GenerateTrFooter());
    $str .= Text::LineFeed(TableHTML::GenerateFooter(false));
    return $str;
  }

  //天候メッセージ生成
  private static function GenerateWeather() {
    //スキップ判定
    if (DB::$ROOM->Stack()->IsEmpty('weather') ||
	(DB::$ROOM->IsOn(RoomMode::LOG) && false === DB::$ROOM->IsTest() && DB::$ROOM->IsNight())) {
      return '';
    }

    $format  = DivHTML::Generate(GameMessage::WEATHER, 'weather');
    $weather = WeatherManager::Get(DB::$ROOM->Stack()->Get('weather'));
    return sprintf($format, $weather[WeatherData::NAME], $weather[WeatherData::CAPTION]);
  }

  //死因表示判定処理
  private static function FilterShowReason() {
    $flag = RoleManager::Stack()->Get('show_reason');
    if (null === $flag) {
      $flag = DB::$SELF->IsLive() && DB::$SELF->IsRole(RoleFilterData::$show_reason);
      RoleManager::Stack()->Set('show_reason', $flag);
    }
    return $flag;
  }

  //蘇生失敗表示判定処理
  private static function FilterShowReviveFailed() {
    $flag = RoleManager::Stack()->Get('show_revive_failed');
    if (null === $flag) {
      $flag = DB::$ROOM->IsFinished() || DB::$SELF->IsDead() ||
	DB::$SELF->IsRole(RoleFilterData::$show_revive_failed);
      RoleManager::Stack()->Set('show_revive_failed', $flag);
    }
    return $flag;
  }

  //人狼襲撃失敗表示判定
  private static function FilterShowWolfFailed() {
    $flag = RoleManager::Stack()->Get('show_wolf_failed');
    if (null === $flag) {
      $flag = DB::$SELF->IsLive() && DB::$SELF->IsRole(RoleFilterData::$show_wolf_failed);
      RoleManager::Stack()->Set('show_wolf_failed', $flag);
    }
    return $flag;
  }

  //アイコン用改行変換
  private static function ConvertImageLine($str) {
    return str_replace(Text::LF, '&#13;&#10;', $str);
  }

  //ヘッダリンクタグ
  private static function GetHeaderLink() {
    return '%s">%s</a>';
  }

  //キャッシュ抑制タグ
  private static function GetNoCache() {
    return <<<EOF
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="0">
EOF;
  }

  //自動更新タグ
  private static function GetReload() {
    return '<meta http-equiv="Refresh" content="%d">';
  }

  //シーンCSSタグ
  private static function GetSceneCSS() {
    return '<link rel="stylesheet" id="scene">';
  }

  //ゲームトップタグ
  private static function GetGameTop() {
    return '<a id="game_top"></a>';
  }

  //プレイ中ログリンクタグ
  private static function GetGameLogLink() {
    return '<a target="_blank" href="%s">%s</a>';
  }

  //プレイ中ログリンク表示名取得
  private static function GetGameLogLinkCaption($scene) {
    switch ($scene) {
    case RoomScene::BEFORE:
      return GameMessage::GAME_LOG_BEFOREGAME;

    case RoomScene::DAY:
      return GameMessage::GAME_LOG_DAY;

    case RoomScene::NIGHT:
      return GameMessage::GAME_LOG_NIGHT;

    case RoomScene::AFTER:
      return GameMessage::GAME_LOG_AFTERGAME;

    case RoomScene::HEAVEN:
      return GameMessage::GAME_LOG_HEAVEN;
    }
  }

  //タイマー JavaScript コードタグ
  private static function GetTimer() {
    return <<<EOF
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
EOF;
  }

  //勝敗タグ
  private static function GetWinner() {
    return <<<EOF
<table id="winner" class="winner winner-%s%s"><tr>
<td>%s</td>
</tr></table>
EOF;
  }

  //投票テーブルタグ
  private static function GetVote() {
    return <<<EOF
<table class="vote-list">
<tr><td class="vote-times" colspan="4">%s</td></tr>
%s
</table>
EOF;
  }

  //投票データタグ
  private static function GetVoteBase() {
    return <<<EOF
<tr><td class="vote-name">%s</td><td>%d %s</td><td>%s</td><td class="vote-name">%s</td></tr>
EOF;
  }

  //プレイヤー一覧ヘッダタグ
  private static function GetPlayerHeader() {
    return DivHTML::GenerateHeader('player') . TableHTML::GenerateHeader();
  }

  //プレイヤーアイコンタグ
  private static function GetPlayerImage() {
    return <<<EOF
<img src="%s" alt="icon" title="%s" style="border-color:%s;"%s%s></td>
%s<span class="symbol" style="color:%s;">%s</span>%s
EOF;
  }

  //マウスオーバータグ
  private static function GetMouseOver() {
    return <<<EOF
 onMouseover="this.src='%s'"
EOF;
  }

  //マウスアウトタグ
  private static function GetMouseOut() {
    return <<<EOF
 onMouseout="this.src='%s'"
EOF;
  }

  //プレイヤー一覧フッタタグ
  private static function GetPlayerFooter() {
    return TableHTML::GenerateFooter() . DivHTML::GenerateFooter();
  }

  //リアルタイム制残り時間表示タグ
  private static function GetRealTime() {
    return <<<EOF
<td class="real-time"><form name="realtime_form">
<input type="text" name="output_realtime" size="60" readonly>
</form></td>
EOF;
  }

  //遺言タグ
  private static function GetLastWords() {
    return <<<EOF
<tr>
<td class="lastwords-title">%s<span>%s</span></td>
<td class="lastwords-body">%s</td>
</tr>
EOF;
  }

  //遺言テーブルタグ
  private static function GetLastWordsTable() {
    return <<<EOF
<table class="system-lastwords"><tr>
<td>%s</td>
</tr></table>
<table class="lastwords">
%s</table>
EOF;
  }
}
