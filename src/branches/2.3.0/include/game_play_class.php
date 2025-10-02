<?php
//-- GamePlay 出力クラス --//
class GamePlay {
  private static $url_stack = array(); //リンク生成用スタック

  //実行
  static function Execute() {
    self::Load();
    self::Talk();
    self::SetURL();
    self::Output();
  }

  //データロード
  private static function Load() {
    DB::Connect();
    Session::CertifyGamePlay();

    DB::LoadRoom(); //村情報
    DB::$ROOM->dead_mode    = RQ::Get()->dead_mode;
    DB::$ROOM->heaven_mode  = RQ::Get()->heaven_mode;
    DB::$ROOM->system_time  = Time::Get();
    DB::$ROOM->sudden_death = 0; //突然死実行までの残り時間

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsFinished()) { //勝敗結果表示
      Loader::LoadFile('winner_message');
    }
    elseif (DB::$ROOM->IsBeforeGame()) { //ゲームオプション表示
      Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
      RQ::Set('retrive_type', DB::$ROOM->scene);
    }
    elseif (! DB::$ROOM->heaven_mode && DB::$ROOM->IsDay()) {
      RQ::Set('retrive_type', DB::$ROOM->scene);
    }

    DB::LoadUser(); //ユーザ情報
    DB::LoadSelf();

    //「異議」ありセット判定
    if (RQ::Get()->set_objection && DB::$SELF->objection < GameConfig::OBJECTION &&
	(DB::$ROOM->IsBeforeGame() || (DB::$ROOM->IsDay() && DB::$SELF->IsLive()))) {
      DB::$SELF->objection++;
      DB::$SELF->Update('objection', DB::$SELF->objection);
      DB::$ROOM->Talk(DB::$SELF->sex, 'OBJECTION', DB::$SELF->uname);
    }

    if (RQ::Get()->play_sound) { //音でお知らせ
      Loader::LoadFile('cookie_class');
      JinrouCookie::Set(); //クッキー情報セット
    }
  }

  //発言処理
  private static function Talk() {
    //判定用変数をセット
    RQ::Set('say_limit', null);
    RQ::Set('update_talk', false);

    if (! DB::$ROOM->dead_mode || DB::$ROOM->heaven_mode) { //発言が送信されるのは bottom フレーム
      RQ::Set('say_limit', RoleTalk::Convert(RQ::Get()->say)); //発言置換処理

      if (RQ::Get()->say == '') {
	self::CheckSilence(); //発言が空ならゲーム停滞のチェック (沈黙、突然死)
      }
      elseif (RQ::Get()->last_words && (! DB::$SELF->IsDummyBoy() || DB::$ROOM->IsBeforeGame())) {
	self::SaveLastWords(RQ::Get()->say); //遺言登録 (細かい判定条件は関数内で行う)
	RQ::Set('update_talk', DB::$SELF->IsDummyBoy());
      }
      //死者 or 身代わり君 or 同一ゲームシーンなら書き込む
      elseif (DB::$SELF->IsDead() || DB::$SELF->IsDummyBoy() || DB::$SELF->CheckScene()) {
	self::SaveTalk(RQ::Get()->say);
	RQ::Set('update_talk', true);
      }
      else {
	self::CheckSilence(); //発言ができない状態ならゲーム停滞チェック
      }

      //ゲームシーンを更新
      if (! DB::$SELF->CheckScene()) DB::$SELF->Update('last_load_scene', DB::$ROOM->scene);
    }
    //霊界の GM でも突然死タイマーを見れるようにする
    elseif (DB::$ROOM->dead_mode && DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      //経過時間を取得
      if (DB::$ROOM->IsRealTime()) {
	GameTime::GetRealPass($left_time);
      } else {
	GameTime::GetTalkPass($left_time, true);
      }

      if ($left_time == 0) DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得
    }
  }

  //ゲーム停滞のチェック
  private static function CheckSilence() {
    if (! DB::$ROOM->IsPlaying()) return true; //スキップ判定

    //経過時間を取得
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      if ($left_time > 0) return true; //制限時間超過判定
    }
    else { //仮想時間制
      if (! DB::Transaction()) return false; //判定条件が全て DB なので即ロック

      //シーン再判定 (ロック付き)
      if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();
      $silence_pass_time = GameTime::GetTalkPass($left_time, true);

      if ($left_time > 0) { //制限時間超過判定
	if (RoomDB::GetTime() <= TimeConfig::SILENCE) return DB::Rollback(); //沈黙判定

	//沈黙メッセージを発行してリセット
	$str = sprintf(GamePlayMessage::SILENCE, $silence_pass_time);
	DB::$ROOM->Talk($str, null, '', '', null, null, null, TimeConfig::SILENCE_PASS);
	return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback();
      }
    }

    //オープニングなら即座に夜に移行する
    if (DB::$ROOM->IsDate(1) && DB::$ROOM->IsDay() && DB::$ROOM->IsOption('open_day')) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! DB::Transaction()) return false;

	//シーン再判定 (ロック付き)
	if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();
      }
      DB::$ROOM->ChangeNight(); //夜に切り替え
      return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback(); //最終書き込み時刻を更新
    }

    if (! RoomDB::IsOvertimeAlert()) { //警告メッセージ出力判定
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! DB::Transaction()) return false;

	//シーン再判定 (ロック付き)
	if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();
      }

      //警告メッセージを出力 (最終出力判定は呼び出し先で行う)
      $str = sprintf(GamePlayMessage::SUDDEN_DEATH_ALERT, Time::Convert(TimeConfig::SUDDEN_DEATH));
      if (DB::$ROOM->OvertimeAlert($str)) { //出力したら突然死タイマーをリセット
	DB::$ROOM->sudden_death = TimeConfig::SUDDEN_DEATH;
	return DB::Commit(); //ロック解除
      }
    }

    DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得

    //制限時間前ならスキップ (この段階でロックしているのは仮想時間制のみ)
    if (DB::$ROOM->sudden_death > 0) return DB::$ROOM->IsRealTime() || DB::Rollback();

    //制限時間を過ぎていたら未投票の人を突然死させる
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
      if (! DB::Transaction()) return false;

      //シーン再判定 (ロック付き)
      if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();

      DB::$ROOM->SetSuddenDeath(); //制限時間を再計算
      if (DB::$ROOM->sudden_death > 0) return DB::Rollback();
    }

    if (abs(DB::$ROOM->sudden_death) > TimeConfig::SERVER_DISCONNECT) { //サーバダウン検出
      //突然死タイマーと警告出力判定をリセット
      return RoomDB::UpdateOvertimeAlert() ? DB::Commit() : DB::Rollback();
    }

    $novote_list = array(); //未投票者リスト
    DB::$ROOM->LoadVote(); //投票情報を取得
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->rows as $user) { //生存中の未投票者を取得
	if ($user->IsLive() && ! isset(DB::$ROOM->vote[$user->id])) {
	  $novote_list[] = $user->id;
	}
      }
    }
    elseif (DB::$ROOM->IsNight()) {
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      //Text::p($vote_data, 'Vote Data');
      foreach (DB::$USER->rows as $user) { //未投票チェック
	if ($user->CheckVote($vote_data) === false) $novote_list[] = $user->id;
      }
    }

    //未投票突然死処理
    foreach ($novote_list as $id) DB::$USER->SuddenDeath($id, 'NOVOTED');
    RoleManager::GetClass('lovers')->Followed(true);
    RoleManager::GetClass('medium')->InsertResult();

    DB::$ROOM->Talk(GameMessage::VOTE_RESET); //投票リセットメッセージ
    RoomDB::ResetVote(); //投票リセット
    if (Winner::Check()) DB::$USER->ResetJoker(); //勝敗チェック
    return DB::Commit(); //ロック解除
  }

  //遺言登録
  private static function SaveLastWords($say) {
    //スキップ判定
    if (DB::$ROOM->IsFinished() || (GameConfig::LIMIT_LAST_WORDS && DB::$ROOM->IsPlaying())) {
      return false;
    }

    if ($say == ' ') $say = null; //スペースだけなら「消去」

    if (DB::$ROOM->IsBeforeGame()) { //ゲーム開始前は無条件で登録
      DB::$SELF->Update('last_words', $say);
    }
    elseif (DB::$SELF->IsLive()) { //登録しない役職をチェック
      if (! DB::$SELF->IsLastWordsLimited()) DB::$SELF->Update('last_words', $say);
    }
    elseif (DB::$SELF->IsDead()) { //霊界遺言登録能力者の処理
      RoleManager::SetActor(DB::$SELF);
      foreach (RoleManager::Load('heaven_last_words') as $filter) {
	$filter->SaveHeavenLastWords($say);
      }
    }
  }

  //発言登録
  private static function SaveTalk($say) {
    if (! DB::$ROOM->IsPlaying()) { //ゲーム開始前後
      return RoleTalk::Save($say, DB::$ROOM->scene, null, 0, true);
    }
    if (RQ::Get()->last_words && DB::$SELF->IsDummyBoy()) { //身代わり君のシステムメッセージ (遺言)
      return RoleTalk::Save($say, DB::$ROOM->scene, 'dummy_boy');
    }
    if (DB::$SELF->IsDead()) return RoleTalk::Save($say, 'heaven'); //死者の霊話

    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      $spend_time = 0; //仮想時間制の経過時間は無効にする
    } else { //仮想時間制
      GameTime::GetTalkPass($left_time); //経過時間の和
      $spend_time = min(4, max(1, floor(strlen($say) / 100))); //経過時間 (範囲は 1 - 4)
    }
    if ($left_time < 1) return; //制限時間外ならスキップ (ここに来るのは生存者のみのはず)

    if (DB::$ROOM->IsDay()) { //昼はそのまま発言
      if (DB::$ROOM->IsEvent('wait_morning')) return; //待機時間判定

      //山彦の処理
      if (DB::$SELF->IsRole('echo_brownie')) RoleManager::LoadMain(DB::$SELF)->EchoSay();
      return RoleTalk::Save($say, DB::$ROOM->scene, null, $spend_time, true);
    }

    //ここからは夜の処理 (役職毎に分ける)
    $user = DB::$SELF->GetVirtual(); //仮想ユーザを取得
    if (DB::$ROOM->IsEvent('blind_talk_night')) { //天候：風雨
      $location = 'self_talk';
    }
    elseif ($user->IsWolf(true)) { //人狼
      $location = DB::$SELF->IsRole('possessed_mad') ? 'self_talk' : 'wolf'; //犬神判定
    }
    elseif ($user->IsRole('whisper_mad')) { //囁き狂人
      $location = DB::$SELF->IsRole('possessed_mad') ? 'self_talk' : 'mad'; //犬神判定
    }
    elseif ($user->IsCommon(true)) { //共有者
      $location = 'common';
    }
    elseif ($user->IsFox(true)) { //妖狐
      $location = 'fox';
    }
    else { //独り言
      $location = 'self_talk';
    }

    $update = DB::$SELF->IsWolf(); //時間経過するのは人狼の発言のみ (本人判定)
    return RoleTalk::Save($say, DB::$ROOM->scene, $location, $update ? $spend_time : 0, $update);
  }

  //リンク情報収集
  private static function SetURL() {
    self::$url_stack['room'] = '?room_no=' . DB::$ROOM->id;

    $url = RQ::Get()->auto_reload > 0 ? '&auto_reload=' . RQ::Get()->auto_reload : '';
    self::$url_stack['auto_reload'] = $url;

    foreach (array('play_sound', 'icon', 'name', 'list_down') as $name) {
      self::$url_stack[$name] = RQ::Get()->$name ? sprintf('&%s=on', $name) : '';
    }

    foreach (array('dead', 'heaven') as $name) {
      $mode = $name . '_mode';
      self::$url_stack[$mode] = DB::$ROOM->$mode ? sprintf('&%s=on', $mode) : '';
    }
  }

  //リンク情報取得 (差分型)
  private static function GetURL(array $list, $header = null) {
    $url = is_null($header) ? '<a target="_top" href="game_frame.php' : $header;
    foreach (array_diff(array_keys(self::$url_stack), $list) as $key) {
      $url .= self::$url_stack[$key];
    }
    return $url;
  }

  //リンク情報取得 (抽出型)
  private static function SelectURL(array $list, $header = null) {
    $url = (isset($header) ? $header : '') . self::$url_stack['room'];
    foreach ($list as $key) {
      $url .= self::$url_stack[$key];
    }
    return $url;
  }

  //出力
  private static function Output() {
    GameHTML::OutputHeader('game_play');
    self::OutputLink();

    if (DB::$ROOM->heaven_mode) {
      self::OutputSayLimit();
      self::OutputTalk();
    }
    else {
      self::OutputTimeTable();
      self::OutputSayLimit();
      if (! RQ::Get()->list_down) GameHTML::OutputPlayer();
      RoleHTML::OutputAbility();
      RoleHTML::OutputVoteKill();
      if (DB::$ROOM->IsPlaying()) GameHTML::OutputRevote();
      if (DB::$ROOM->IsQuiz() && DB::$ROOM->IsDay() && DB::$SELF->IsDummyBoy()) {
	self::OutputQuizVote();
      }
      self::OutputTalk();
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
      GameHTML::OutputVote();
      if (! DB::$ROOM->dead_mode) self::OutputLastWords();
      if (RQ::Get()->list_down) GameHTML::OutputPlayer();
      if (RQ::Get()->play_sound && (DB::$ROOM->IsBeforeGame() || DB::$ROOM->IsDay())) {
	self::OutputSound();
      }
    }

    HTML::OutputFooter();
  }

  //リンク出力
  private static function OutputLink() {
    Text::Output('<table class="game-header"><tr>');

    if (DB::$ROOM->IsFinished()) { //ゲーム終了後
      echo DB::$ROOM->GenerateTitleTag();
      self::OutputLogLink();
    }
    elseif (DB::$ROOM->heaven_mode && DB::$SELF->IsDead()) { //霊界
      printf('<td>%s</td>' . Text::LF, GamePlayMessage::HEAVEN_TITLE);
      self::OutputLogLink();
      return;
    }
    else {
      echo DB::$ROOM->GenerateTitleTag();
      Text::Output('<td class="view-option">');

      //中央フレーム内の下界発言更新ボタン (死亡者用)
      if (DB::$ROOM->dead_mode && DB::$SELF->IsDead()) self::OutputReloadButton();
    }

    if (DB::$ROOM->IsFinished()) {
      echo Text::BR;
    }
    else { //ゲーム終了後は自動更新しない
      GameHTML::OutputAutoReloadLink(self::GetURL(array('auto_reload')));
      self::OutputHeaderSwitchLink('play_sound'); //ユーザ名表示
    }

    self::OutputHeaderSwitchLink('icon'); //アイコン表示
    if (DB::$ROOM->IsFinished()) self::OutputHeaderSwitchLink('name'); //ユーザ名表示

    //プレイヤーリストの表示位置
    $format = '%s">%s</a>' . Text::LF;
    $url    = self::GetURL(array('list_down'));
    if (RQ::Get()->list_down) {
      printf($format, $url, GamePlayMessage::$header_list_down_off);
    } else {
      printf($format, $url . '&list_down=on', GamePlayMessage::$header_list_down_on);
    }

    if (! DB::$ROOM->IsFinished()) { //オプションリンク
      $url = self::SelectURL(array()) . '&describe_room=on';
      self::OutputHeaderLink('room_manager', $url, 'describe_room');
    }

    //別ページリンク
    self::OutputHeaderLink('game_play', self::SelectURL(array('list_down')));
    if (ServerConfig::DEBUG_MODE) { //観戦モードリンク
      self::OutputHeaderLink('game_view', self::SelectURL(array()));
    }

    if (DB::$ROOM->IsFinished()) {
      GameHTML::OutputLogLink();
    }
    elseif (DB::$ROOM->IsBeforegame()) {
      $url = sprintf('%s&user_no=%s', self::SelectURL(array()), DB::$SELF->id);
      self::OutputHeaderLink('user_manager', $url); //登録情報変更
      if (DB::$SELF->IsDummyBoy()) { //村オプション変更
	self::OutputHeaderLink('room_manager', self::SelectURL(array()));
      }
    }

    Text::Output('</td>' . Text::LF . '</tr></table>');
  }

  //過去ログリンク出力
  private static function OutputLogLink() {
    $header = sprintf('<a target="_blank" href="game_log.php%s', self::SelectURL(array()));
    $format = $header . '&date=%d&scene=%s">%d(%s)</a>' . Text::LF;

    printf('<td class="view-option">%s ', GamePlayMessage::LOG_NAME);
    printf($format, 0, 'beforegame', 0,   GamePlayMessage::LOG_BEFOREGAME);
    if (DB::$ROOM->date > 1) {
      if (DB::$ROOM->IsOption('open_day')) {
	printf($format, 1, 'day', 1, GamePlayMessage::LOG_DAY);
      }
      printf($format, 1, 'night', 1, GamePlayMessage::LOG_NIGHT);
      for ($i = 2; $i < DB::$ROOM->date; $i++) {
	printf($format, $i, 'day',   $i, GamePlayMessage::LOG_DAY);
	printf($format, $i, 'night', $i, GamePlayMessage::LOG_NIGHT);
      }

      if (DB::$ROOM->heaven_mode) {
	if (DB::$ROOM->IsNight()) {
	  printf($format, $i, 'day', $i, GamePlayMessage::LOG_DAY);
	}
	Text::Output('</td>' . Text::LF . '</tr></table>');
	return;
      }
    }

    if (DB::$ROOM->IsFinished()) {
      if (DB::$ROOM->date > 0) {
	printf($format, DB::$ROOM->date, 'day',   DB::$ROOM->date, GamePlayMessage::LOG_DAY);
      }

      if (TalkDB::ExistsLastNight()) {
	printf($format, DB::$ROOM->date, 'night', DB::$ROOM->date, GamePlayMessage::LOG_NIGHT);
      }

      $format = $header . '&scene=%s">(%s)</a>' . Text::LF;
      printf($format, 'aftergame', GamePlayMessage::LOG_AFTERGAME);
      printf($format, 'heaven',    GamePlayMessage::LOG_HEAVEN);
    }
  }

  //更新ボタン出力 (霊界用)
  private static function OutputReloadButton() {
    $format = <<<EOF
<form method="post" action="%s" name="reload_middle_frame" target="middle">
<input type="submit" value="%s">
</form>
EOF;
    $url = self::GetURL(array('dead_mode', 'heaven_mode'), 'game_play.php') . '&dead_mode=on';
    printf($format . Text::LF, $url, GamePlayMessage::RELOAD);
  }

  //ヘッダーリンク出力
  private static function OutputHeaderLink($url, $add_url, $type = null) {
    $format = '<a target="_blank" href="%s.php%s">%s</a>' . Text::LF;
    if (is_null($type)) $type = $url;

    printf($format, $url, $add_url, GamePlayMessage::${'header_' . $type});
  }

  //ヘッダーリンク出力 (スイッチ型)
  private static function OutputHeaderSwitchLink($type) {
    $format  = '[%s" class="option-%s">%s</a>]' . Text::LF;
    $url     = self::GetURL(array($type));
    $message = GamePlayMessage::${'header_' . $type};

    if (RQ::Get()->$type) {
      printf($format, $url, 'on', $message);
    } else {
      printf($format, $url . sprintf('&%s=on', $type), 'off', $message);
    }
  }

  //タイムテーブル出力
  private static function OutputTimeTable() {
    switch (DB::$ROOM->scene) {
    case 'beforegame': //開始前の注意を出力
      $format = '<div class="caution">%s%s<span>%s</span>%s</div>' . Text::LF;
      printf($format, Text::LF,
	     GamePlayMessage::BEFOREGAME_CATION, GamePlayMessage::BEFOREGAME_VOTE, Text::LF);
      RoomOption::Output(); //ゲームオプション表示
      break;

    case 'aftergame': //勝敗結果を出力して処理終了
      Winner::Output();
      return;
    }

    GameHTML::OutputTimeTable();
    $left_time = 0;
    if (DB::$ROOM->IsBeforeGame()) {
      echo '<td class="real-time">';
      if (DB::$ROOM->IsRealTime()) { //実時間の制限時間を取得
	printf(GamePlayMessage::REAL_TIME, DB::$ROOM->real_time->day, DB::$ROOM->real_time->night);
      }
      printf(GamePlayMessage::SUDDEN_DEATH, Time::Convert(TimeConfig::SUDDEN_DEATH));
      Text::Output('</td>');
    }
    elseif (DB::$ROOM->IsPlaying()) {
      GameHTML::OutputTimePass($left_time);
    }
    self::OutputObjection($left_time);
    Text::Output('</tr></table>');

    if (! DB::$ROOM->IsPlaying()) return;

    if (DB::$ROOM->IsEvent('wait_morning')) {
      GameHTML::OutputVoteAnnounce(GameMessage::WAIT_MORNING);
    }
    elseif ($left_time == 0) {
      GameHTML::OutputVoteAnnounce();
      if (DB::$ROOM->sudden_death > 0) {
	$str = GamePlayMessage::SUDDEN_DEATH_TIME . Time::Convert(DB::$ROOM->sudden_death);
	if (DB::$ROOM->IsDay() || DB::$SELF->IsDummyBoy()) {
	  $str .= ' / ' . self::GetNovotedCount();
	}
	self::OutputSuddenDeathAnnounce($str);
      }
    }
    elseif (DB::$SELF->IsDummyBoy()) {
      self::OutputSuddenDeathAnnounce(self::GetNovotedCount());
    }

    if (DB::$SELF->IsDead() && ! DB::$ROOM->IsOpenCast()) {
      GameHTML::OutputVoteAnnounce(GameMessage::CLOSE_CAST);
    }
  }

  //異議ありボタン表示
  private static function OutputObjection($left_time) {
    //スキップ判定
    if (DB::$ROOM->IsDay()) {
      //昼は制限時間内の生存者のみ
      if ($left_time == 0 || DB::$ROOM->dead_mode || DB::$ROOM->heaven_mode) return;
    }
    elseif (! DB::$ROOM->IsBeforeGame()) {
      return;
    }

    $format = <<<EOF
<td class="objection"><form method="post" action="%s">
<input type="hidden" name="set_objection" value="on">
<input type="image" name="objimage" src="%s">
(%d)</form></td>
EOF;

    $list  = array('auto_reload', 'play_sound', 'icon', 'list_down');
    $url   = self::SelectURL($list, 'game_play.php');
    $image = GameConfig::OBJECTION_IMAGE;
    $count = GameConfig::OBJECTION - DB::$SELF->objection;
    printf($format . Text::LF, $url, $image, $count);
  }

  //未投票突然死メッセージ表示
  private static function OutputSuddenDeathAnnounce($str) {
    printf('<div class="system-sudden-death">%s</div>' . Text::LF, $str);
  }

  //未投票人数情報取得
  private static function GetNovotedCount() {
    $count = 0;
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->rows as $user) {
	if ($user->IsLive() && count($user->target_no) < 1) $count++;
      }
    }
    elseif (DB::$ROOM->IsNight() && DB::$SELF->IsDummyBoy()) { //身代わり君以外は不可
      if (! isset(DB::$ROOM->vote)) DB::$ROOM->LoadVote();
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      //Text::p($vote_data, 'Vote Data');
      foreach (DB::$USER->rows as $user) { //未投票チェック
	if ($user->CheckVote($vote_data) === false) $count++;
      }
    }
    return sprintf(GamePlayMessage::NOVOTED_COUNT, $count);
  }

  //発言制限メッセージ表示
  static function OutputSayLimit() {
    if (RQ::Get()->say_limit === false) {
      printf('<font color="#FF0000">%s</font>' . Text::BRLF, GamePlayMessage::SAY_LIMIT);
    }
  }

  //投票結果表示 (クイズ村 GM 専用)
  static function OutputQuizVote() {
    $stack = array();
    foreach (SystemMessageDB::GetQuizVote() as $key => $list) {
      $stack[$list['target_no']][] = $key;
    }
    ksort($stack);

    $format = '<tr class="vote-name"><td>%s</td><td>%s</td></tr>';
    $header = sprintf($format, GamePlayMessage::QUIZ_VOTED_NAME, GamePlayMessage::QUIZ_VOTED_COUNT);
    $table_stack = array('<table class="vote-list">', $header);

    $format = '<tr><td class="vote-name">%s</td><td class="vote-times">%d %s</td></tr>';
    foreach ($stack as $id => $list) {
      $user = DB::$USER->ByID($id);
      $table_stack[] = sprintf($format, $user->handle_name, count($list), GameMessage::VOTE_UNIT);
    }
    $table_stack[] = '</table>';
    Text::Output(implode(Text::LF, $table_stack));
  }

  //会話出力
  private static function OutputTalk() {
    $update_talk = RQ::Get()->update_talk;
    if (DB::$ROOM->heaven_mode && DB::$SELF->IsDead()) {
      $cache_type = 'talk_heaven';
      if (DocumentCache::Enable($cache_type)) {
	$cache_name = 'game_play/heaven';
	if (RQ::Get()->icon) $cache_name .= '_icon';

	DocumentCache::Load($cache_name, CacheConfig::TALK_HEAVEN_EXPIRE);
	$filter = DocumentCache::GetTalk($update_talk, true);
	DocumentCache::Save($filter, true, $update_talk);
	DocumentCache::Output($cache_type);
      } else {
	$filter = Talk::GetHeaven();
      }
    } else {
      $cache_type = 'talk_play';
      if (! DB::$ROOM->IsPlaying() && DocumentCache::Enable($cache_type)) {
	$cache_name = 'game_play/talk';
	if (RQ::Get()->icon) $cache_name .= '_icon';
	if (RQ::Get()->name) $cache_name .= '_name';

	DocumentCache::Load($cache_name, CacheConfig::TALK_PLAY_EXPIRE);
	$filter = DocumentCache::GetTalk($update_talk);
	DocumentCache::Save($filter, true, $update_talk);
	DocumentCache::Output($cache_type);
      } else {
	$filter = Talk::Get();
      }
    }
    $filter->Output();
  }

  //自分の遺言出力
  private static function OutputLastWords() {
    if (DB::$ROOM->IsAfterGame()) return false; //スキップ判定

    $str = UserDB::GetLastWords(DB::$SELF->id);
    if ($str == '') return false;

    Text::Line($str); //改行コードを変換
    if ($str == '') return false;

    $format = <<<EOF
<table class="lastwords"><tr>
<td class="lastwords-title">%s</td>
<td class="lastwords-body">%s</td>
</tr></table>
EOF;

    printf($format . Text::LF, GamePlayMessage::LAST_WORDS, $str);
  }

  //音声出力
  private static function OutputSound() {
    if (DB::$ROOM->IsBeforeGame()) { //入村・満員
      if (JinrouCookie::$user_count > 0) {
	$user_count = DB::$USER->GetUserCount();
	$max_user   = RoomDB::Fetch('max_user');
	if ($user_count == $max_user && JinrouCookie::$user_count != $max_user) {
	  Sound::Output('full');
	} elseif (JinrouCookie::$user_count != $user_count) {
	  Sound::Output('entry');
	}
      }
    }
    elseif (JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //夜明け
      Sound::Output('morning');
    }

    //「異議」あり
    $cookie = explode(',', JinrouCookie::$objection); //クッキーの値を配列に格納する
    $stack  = JinrouCookie::$objection_list;
    $count  = count($stack);
    if (count($cookie) == $count) {
      for ($i = 0; $i < $count; $i++) { //差分を計算 (index は 0 から)
	//差分があれば性別を確認して音を鳴らす
	if (isset($cookie[$i]) && $stack[$i] > $cookie[$i]) {
	  Sound::Output('objection_' . DB::$USER->ByID($i + 1)->sex);
	}
      }
    }
  }
}
