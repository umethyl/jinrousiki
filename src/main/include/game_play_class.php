<?php
//-- GamePlay 出力クラス --//
class GamePlay {
  //実行
  static function Execute() {
    $controller = new GamePlay();
    $controller->Load();
    $controller->view->SetSound();
    $controller->Talk();
    $controller->view->SetURL();
    $controller->view->Output();
  }

  var $view;

  function __construct() {
    DB::Connect();
  }

  //データロード
  function Load() {
    Session::LoginGamePlay();

    DB::LoadRoom(); //村情報
    DB::$ROOM->Flag()->Set('dead',   RQ::Get()->dead_mode);
    DB::$ROOM->Flag()->Set('heaven', RQ::Get()->heaven_mode);
    DB::$ROOM->system_time  = Time::Get();
    DB::$ROOM->sudden_death = 0; //突然死実行までの残り時間

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsOn('heaven')) {
      $this->view = new GamePlayView_Heaven();
    }
    elseif (DB::$ROOM->IsFinished()) { //勝敗結果表示
      $this->view = new GamePlayView_After();
    }
    elseif (DB::$ROOM->IsBeforeGame()) { //ゲームオプション表示
      RQ::Set('retrieve_type', DB::$ROOM->scene);
      $this->view = new GamePlayView_Before();
    }
    elseif (DB::$ROOM->IsDay()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene);
      $this->view = new GamePlayView();
    }
    else {
      $this->view = new GamePlayView();
    }

    DB::LoadUser(); //ユーザ情報
    DB::LoadSelf();
  }

  //発言処理
  function Talk() {
    //判定用変数をセット
    $this->view->say_limit = null;
    $this->view->update_talk = false;
    if (DB::$ROOM->IsOption('limit_talk')) $this->view->limit_talk = false;

    //発言が送信されるのは bottom フレーム
    if (DB::$ROOM->IsOff('dead') || DB::$ROOM->IsOn('heaven')) {
      $this->view->say_limit = RoleTalk::Convert(RQ::Get()->say); //発言置換処理

      if (RQ::Get()->say == '') {
        self::CheckSilence(); //発言が空ならゲーム停滞のチェック (沈黙、突然死)
      }
      elseif (RQ::Get()->last_words && (! DB::$SELF->IsDummyBoy() || DB::$ROOM->IsBeforeGame())) {
        self::SaveLastWords(RQ::Get()->say); //遺言登録 (細かい判定条件は関数内で行う)
        $this->view->update_talk = DB::$SELF->IsDummyBoy();
      }
      //死者 or 身代わり君 or 同一ゲームシーンなら書き込む
      elseif (DB::$SELF->IsDead() || DB::$SELF->IsDummyBoy() || DB::$SELF->CheckScene()) {
        self::SaveTalk(RQ::Get()->say);
        $this->view->update_talk = true;
      }
      else {
        self::CheckSilence(); //発言ができない状態ならゲーム停滞チェック
      }

      //ゲームシーンを更新
      if (! DB::$SELF->CheckScene()) DB::$SELF->Update('last_load_scene', DB::$ROOM->scene);
    }
    //霊界の GM でも突然死タイマーを見れるようにする
    elseif (DB::$ROOM->IsOn('dead') && DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      //超過なら最終発言時刻からの差分を取得
      if (GameTime::GetLeftTime() == 0) DB::$ROOM->SetSuddenDeath();
    }
  }

  //ゲーム停滞のチェック
  function CheckSilence() {
    if (! DB::$ROOM->IsPlaying()) return true; //スキップ判定

    //経過時間を取得
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      if ($left_time > 0) return true; //制限時間超過判定
    }
    else { //仮想時間制
      if (! self::LockScene()) return false; //判定条件が全て DB なので即ロック
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
	if (! self::LockScene()) return false; //シーン再判定
      }
      DB::$ROOM->ChangeNight(); //夜に切り替え
      return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback(); //最終書き込み時刻を更新
    }

    if (! RoomDB::IsOvertimeAlert()) { //警告メッセージ出力判定
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! self::LockScene()) return false; //シーン再判定
      }

      //警告メッセージを出力 (最終出力判定は呼び出し先で行う)
      $str = sprintf(GamePlayMessage::SUDDEN_DEATH_ALERT, Time::Convert(TimeConfig::SUDDEN_DEATH));
      if (DB::$ROOM->OvertimeAlert($str)) { //出力したら突然死タイマーをリセット
	DB::$ROOM->sudden_death = TimeConfig::SUDDEN_DEATH;
	if (DB::$ROOM->IsDay() && DB::$ROOM->IsOption('no_silence')) { //沈黙死 + 処刑投票処理
	  self::VoteNoSilence();
	}
	return DB::Commit(); //ロック解除
      }
    }

    DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得

    //制限時間前ならスキップ (この段階でロックしているのは仮想時間制のみ)
    if (DB::$ROOM->sudden_death > 0) return DB::$ROOM->IsRealTime() || DB::Rollback();

    //制限時間を過ぎていたら未投票の人を突然死させる
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
      if (! self::LockScene()) return false; //シーン再判定

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
	if ($user->IsLive() && ! $user->ExistsVote()) {
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
    if (Winner::Check()) { //勝敗チェック
      //ジョーカー再配布
      if (DB::$ROOM->IsOption('joker')) RoleManager::GetClass('joker')->ResetJoker();
    }
    return DB::Commit(); //ロック解除
  }

  //遺言登録
  private function SaveLastWords($say) {
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
  private function SaveTalk($say) {
    if (RQ::Get()->font_type == TalkVoice::SECRET) { //秘密の発言判定
      RQ::Set('secret_talk', true);
      RQ::Set('font_type', TalkVoice::NORMAL); //声の大きさは普通で固定
    } else {
      RQ::Set('secret_talk', false);
    }

    if (! DB::$ROOM->IsPlaying()) { //ゲーム開始前後
      return RoleTalk::Save($say, DB::$ROOM->scene, null, 0, true);
    }
    if (RQ::Get()->last_words && DB::$SELF->IsDummyBoy()) { //身代わり君のシステムメッセージ (遺言)
      return RoleTalk::Save($say, DB::$ROOM->scene, TalkLocation::DUMMY_BOY);
    }
    if (DB::$SELF->IsDead()) return RoleTalk::Save($say, RoomScene::HEAVEN); //死者の霊話

    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      $spend_time = 0; //仮想時間制の経過時間は無効にする
    } else { //仮想時間制
      GameTime::GetTalkPass($left_time); //経過時間の和
      $spend_time = min(4, max(1, floor(strlen($say) / 100))); //経過時間 (範囲は 1 - 4)
    }
    if ($left_time < 1) return false; //制限時間外ならスキップ (ここに来るのは生存者のみのはず)

    if (DB::$ROOM->IsDay()) { //昼はそのまま発言
      if (DB::$ROOM->IsEvent('wait_morning')) return false; //待機時間判定

      if (! RQ::Get()->secret_talk) {
        if (DB::$ROOM->IsOption('limit_talk')) { //発言数制限制
          if (! self::UpdateLimitTalkCount()) return false;
        }
        elseif (DB::$ROOM->IsOption('no_silence')) { //沈黙禁止
          if (! self::UpdateNoSilenceTalkCount()) return false;
        }

        //山彦の処理
        if (DB::$SELF->IsRole('echo_brownie')) RoleManager::LoadMain(DB::$SELF)->EchoSay();
      }

      $location = RQ::Get()->secret_talk ? TalkLocation::SECRET : null;
      return RoleTalk::Save($say, DB::$ROOM->scene, $location, $spend_time, true);
    }

    //ここからは夜の処理 (役職毎に分ける)
    $location = RoleTalk::GetLocation(DB::$SELF->GetVirtual(), DB::$SELF); //仮想ユーザで判定
    $update   = DB::$SELF->IsWolf(); //時間経過するのは人狼の発言のみ (本人判定)
    return RoleTalk::Save($say, DB::$ROOM->scene, $location, $update ? $spend_time : 0, $update);
  }

  //シーン再判定付きロック処理
  private function LockScene() {
    if (! DB::Transaction()) return false;

    if (RoomDB::Get('scene', true) != DB::$ROOM->scene) { //シーン再判定 (ロック付き)
      DB::Rollback();
      return false;
    }
    return true;
  }

  //沈黙死 + 処刑投票処理
  private function VoteNoSilence() {
    if (RoomDB::Get('vote_count', true) != DB::$ROOM->vote_count) return; //投票回数判定
    if (TalkDB::GetNotVoteTalkUserCount() > 0) return; //発言者の投票済み判定

    Loader::LoadFile('game_vote_functions');
    RQ::Set('situation', 'VOTE_KILL'); //仮想的に処刑投票コマンドをセット
    /*
      Vote は初期化時点で ROOM/USER をロックをかけて生成している
      処刑投票処理時に霊界操作で変更される要素を参照しないので
      GamePlay 用のパラメータ再セットが煩雑になる事を配慮し、再生成は行わない
    */
    VoteDay::Aggregate();
  }

  //発言数更新 (発言数制限制用)
  private function UpdateLimitTalkCount() {
    if (DB::$SELF->GetTalkCount() >= DB::$ROOM->GetLimitTalk()) {
      $this->view->limit_talk = true;
      return false;
    }

    //ロックをかけて発言数を更新
    DB::Transaction();
    if (DB::$SELF->GetTalkCount(true) >= DB::$ROOM->GetLimitTalk()) {
      DB::Rollback();
      $this->view->limit_talk = true;
      return false;
    }

    if (TalkDB::UpdateUserTalkCount()) {
      DB::Commit();
      DB::$SELF->talk_count++;
    } else {
      DB::Rollback();
      return false;
    }
    return true;
  }

  //発言数更新 (沈黙禁止用)
  private function UpdateNoSilenceTalkCount() {
    if (DB::$SELF->GetTalkCount() > 0) return true; //発言済みならスキップ
    return TalkDB::UpdateUserTalkCount(); //1 以上であればいいのでロックしない
  }
}



/**
 * ゲーム画面の出力を制御します。
 */
class GamePlayView {
  var $say_limit = null;
  var $limit_talk = false;
  var $update_talk = false;

  function __construct() {
  }

  function SetSound() {
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

  //リンク情報収集
  function SetURL() {
    RQ::Get()->StackIntParam('room_no', false);
    RQ::Get()->StackIntParam('auto_reload');

    $stack = array('play_sound', 'icon', 'name', 'list_down');
    if (GameConfig::ASYNC) $stack[] = 'async';
    foreach ($stack as $name) {
      RQ::Get()->StackOnParam($name);
    }

    foreach (array('dead', 'heaven') as $name) {
      RQ::Get()->StackOnValue($name.'_mode', DB::$ROOM->IsOn($name));
    }
  }

  //リンク情報取得 (差分型)
  protected function GetURL(array $except, $header = null) {
    $params = RQ::Get()->GenerateUrl($except, null);
    return is_null($header)
      ? '<a target="_top" href="game_frame.php?'.$params
      : $header.'?'.$params;
  }

  //リンク情報取得 (抽出型)
  protected function SelectURL(array $list, $header = null) {
    $params = RQ::Get()->GenerateUrl(null, array_merge($list, array('room_no')));
    return isset($header)
      ? $header.'?'.$params
      : '?'.$params;
  }

  function Output() {
    GameHTML::OutputHeader('game_play');
    $this->OutputLink();

    $this->OutputTimeTable();
    $this->OutputSayLimit();
    if (! RQ::Get()->list_down) GameHTML::OutputPlayer();
    $this->OutputAbility();
    $this->OutputVoteSection();
    $this->OutputTalk();
    GameHTML::OutputLastWords();
    GameHTML::OutputDead();
    GameHTML::OutputVote();
    if (DB::$ROOM->IsOff('dead')) $this->OutputMyLastWords();
    if (RQ::Get()->list_down) GameHTML::OutputPlayer();
    if (RQ::Get()->play_sound && (DB::$ROOM->IsBeforeGame() || DB::$ROOM->IsDay())) {
      $this->OutputSound();
    }

    HTML::OutputFooter();
  }

  /** 非同期APIが要求する発言ログのみを出力します */
  function OutputTalkAsync() {
    $this->OutputSceneAsync();
    $this->OutputTimelimit(GameTime::GetLeftTime());
    $this->OutputVoteSection();
    if (DB::$ROOM->IsNight()) {
      $this->OutputAbility();
    }
    $this->OutputTalk();
    if (RQ::Get()->play_sound) {
      $this->OutputSound();
    }
  }

  protected function OutputSceneAsync() {
    $room = DB::$ROOM;
    $end_date = GameTime::GetPass();
    echo <<<HTML
<ul>
  <li class="status" id="date">{$room->date}</li>
  <li class="status" id="scene">{$room->scene}</li>
  <li class="status" id="end_date">{$end_date}</li>
</ul>
HTML;
  }

  //リンク出力
  protected function OutputLink() {
    Text::Output('<table class="game-header"><tr>');
    $this->OutputLinkContent();
    Text::Output('</td>' . Text::LF . '</tr></table>');
  }

  protected function OutputLinkContent() {
    echo DB::$ROOM->GenerateTitleTag();
    Text::Output('<td class="view-option">');

    //中央フレーム内の下界発言更新ボタン (死亡者用)
    if (DB::$ROOM->IsOn('dead') && DB::$SELF->IsDead()) $this->OutputReloadButton();

    GameHTML::OutputAutoReloadLink($this->GetURL(array('auto_reload')));
    if (GameConfig::ASYNC) $this->OutputHeaderSwitchLink('async'); //非同期更新
    $this->OutputHeaderSwitchLink('play_sound'); //ユーザ名表示

    $this->OutputHeaderSwitchLink('icon'); //アイコン表示
    $this->OutputHeaderSwitchLink('list_down'); //プレイヤーリストの表示位置

    $url = $this->SelectURL(array()) . '&describe_room=on';
    $this->OutputHeaderLink('room_manager', $url, 'describe_room');

    //別ページリンク
    $this->OutputHeaderLink('game_play', $this->SelectURL(array('list_down')));
    if (ServerConfig::DEBUG_MODE) { //観戦モードリンク
      $this->OutputHeaderLink('game_view', $this->SelectURL(array()));
    }
  }

  //過去ログリンク出力
  protected function OutputLogLinkList() {
    printf('<td class="view-option">%s ', GamePlayMessage::LOG_NAME);
    $this->OutputLogLink(RoomScene::BEFORE, GamePlayMessage::LOG_BEFOREGAME, 0);
    if (DB::$ROOM->date > 1) {
      if (DB::$ROOM->IsOption('open_day')) {
        $this->OutputLogLink(RoomScene::DAY, GamePlayMessage::LOG_DAY, 1);
      }
      $this->OutputLogLink(RoomScene::NIGHT, GamePlayMessage::LOG_NIGHT, 1);
      for ($i = 2; $i < DB::$ROOM->date; $i++) {
        $this->OutputLogLink(RoomScene::DAY, GamePlayMessage::LOG_DAY, $i);
        $this->OutputLogLink(RoomScene::NIGHT, GamePlayMessage::LOG_NIGHT, $i);
      }
    }
  }

  protected function OutputLogLink($scene, $caption, $date = null) {
    if (empty($this->link_header)) {
      $this->loglink_header = sprintf('<a target="_blank" href="game_log.php%s', $this->SelectURL(array()));
    }
    return isset($date)
      ? printf($this->loglink_header.'&date=%d&scene=%s">%d(%s)</a>'.TEXT::LF, $date, $scene, $date, $caption)
      : printf($this->loglink_header.'&scene=%s">%s</a>'.TEXT::LF, $scene, $caption);
  }

  //更新ボタン出力 (霊界用)
  protected function OutputReloadButton() {
    $format = <<<EOF
<form method="post" action="%s" name="reload_middle_frame" target="middle">
<input type="submit" value="%s">
</form>
EOF;
    $url = $this->GetURL(array('dead_mode', 'heaven_mode'), 'game_play.php') . '&dead_mode=on';
    printf($format . Text::LF, $url, GamePlayMessage::RELOAD);
  }

  //ヘッダーリンク出力
  protected function OutputHeaderLink($url, $add_url, $type = null) {
    $format = '<a target="_blank" href="%s.php%s">%s</a>' . Text::LF;
    if (is_null($type)) $type = $url;

    printf($format, $url, $add_url, GamePlayMessage::${'header_' . $type});
  }

  //ヘッダーリンク出力 (スイッチ型)
  protected function OutputHeaderSwitchLink($type) {
    foreach (func_get_args() as $type) {
      $url = $this->GetURL(array($type));
      if ($type != 'list_down') {
        $format  = '[%s" class="option-%s">%s</a>]' . Text::LF;
        $message = GamePlayMessage::${'header_' . $type};
    
        if (RQ::Get()->$type) {
          printf($format, $url, 'on', $message);
        } else {
          printf($format, $url . sprintf('&%s=on', $type), 'off', $message);
        }
      }
      else { 
        //プレイヤーリストの表示位置
        $format = '%s">%s</a>' . Text::LF;
        if (RQ::Get()->$type) {
          printf($format, $url, GamePlayMessage::${'header_'.$type.'_off'});
        } else {
          printf($format, $url . sprintf('&%s=on', $type), GamePlayMessage::${'header_'.$type.'_on'});
        }
      }
    }
  }

  //タイムテーブル出力
  protected function OutputTimeTable() {
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
    if (DB::$ROOM->IsOption('limit_talk')) $this->OutputTalkCount();
    $this->OutputObjection($left_time);
    Text::Output('</tr></table>');

    $this->OutputTimelimit($left_time);
  }

  /** 時間制限の通知 */
  final protected function OutputTimelimit($left_time) {
    echo '<div class="timelimit">',Text::LF;
    $this->OutputTimelimitContent($left_time);
    echo '</div>',Text::LF;
  }

  protected function OutputTimelimitContent($left_time) {
    if (DB::$ROOM->IsEvent('wait_morning')) {
      GameHTML::OutputVoteAnnounce(GameMessage::WAIT_MORNING);
    }
    elseif ($left_time == 0) {
      GameHTML::OutputVoteAnnounce();
      if (DB::$ROOM->sudden_death > 0) {
        $str = GamePlayMessage::SUDDEN_DEATH_TIME . Time::Convert(DB::$ROOM->sudden_death);
        if (DB::$ROOM->IsDay() || DB::$SELF->IsDummyBoy()) {
          $str .= ' / ' . $this->GetNovotedCount();
        }
        $this->OutputSuddenDeathAnnounce($str);
      }
    }
    elseif (DB::$SELF->IsDummyBoy()) {
      $this->OutputSuddenDeathAnnounce($this->GetNovotedCount());
    }

    if (DB::$SELF->IsDead() && ! DB::$ROOM->IsOpenCast()) {
      GameHTML::OutputVoteAnnounce(GameMessage::CLOSE_CAST);
    }
  }

  //発言数表示
  protected function OutputTalkCount() {
    $format = '<td>%s%s(%d/%d)</td>';
    printf($format . Text::LF,
         GamePlayMessage::TALK_COUNT, Message::COLON,
         DB::$SELF->GetTalkCount(), DB::$ROOM->GetLimitTalk());
  }

  //異議ありボタン表示
  protected function OutputObjection($left_time) {
    //スキップ判定
    if (!$this->CanObjection($left_time)) {
      return;
    }

    $format = <<<EOF
<td class="objection"><form method="post" action="%s">
<input type="hidden" name="set_objection" value="on">
<input type="image" name="objimage" src="%s" alt="異議あり">
(%d)</form></td>
EOF;

    $list  = array('auto_reload', 'play_sound', 'icon', 'list_down');
    if (GameConfig::ASYNC) $list[] = 'async';
    $url   = $this->SelectURL($list, 'game_play.php');
    $image = GameConfig::OBJECTION_IMAGE;
    $count = GameConfig::OBJECTION - DB::$SELF->objection;
    printf($format . Text::LF, $url, $image, $count);
  }

  /** 異議ありを投稿できるかどうか示す値を取得します。 */
  protected function CanObjection($left_time) {
    if (DB::$ROOM->IsDay()) {
      //昼は制限時間内の生存者のみ
      return ! ($left_time == 0 || DB::$ROOM->IsOn('dead'));
    }
    //夜は禁止
    return false;
  }

  //未投票突然死メッセージ表示
  protected function OutputSuddenDeathAnnounce($str) {
    printf('<div class="system-sudden-death">%s</div>' . Text::LF, $str);
  }

  protected function OutputVoteSection() {
    echo '<div class="vote-elements">',Text::LF;
    RoleHTML::OutputVoteKill();
    if (DB::$ROOM->IsPlaying()) GameHTML::OutputRevote();
    if (DB::$ROOM->IsQuiz() && DB::$ROOM->IsDay() && DB::$SELF->IsDummyBoy()) {
      $this->OutputQuizVote();
    }
    echo '</div>';
  }

  //未投票人数情報取得
  protected function GetNovotedCount() {
    $count = 0;
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->rows as $user) {
        if ($user->IsLive() && count($user->target_no) < 1) $count++;
      }
    }
    elseif (DB::$ROOM->IsNight() && DB::$SELF->IsDummyBoy()) { //身代わり君以外は不可
      if (DB::$ROOM->Stack()->IsEmpty('vote')) DB::$ROOM->LoadVote();
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      //Text::p($vote_data, 'Vote Data');
      foreach (DB::$USER->rows as $user) { //未投票チェック
        if ($user->CheckVote($vote_data) === false) $count++;
      }
    }
    return sprintf(GamePlayMessage::NOVOTED_COUNT, $count);
  }

  //発言制限メッセージ表示
  function OutputSayLimit() {
    if ($this->say_limit === false) {
      Text::Output(HTML::GenerateWarning(GamePlayMessage::SAY_LIMIT), true);
    }

    if (DB::$ROOM->IsOption('limit_talk') && $this->limit_talk) {
      Text::Output(HTML::GenerateWarning(GamePlayMessage::LIMIT_TALK), true);
    }
  }
  
  protected function OutputAbility() {
    echo '<div class="ability-elements">',Text::LF;
    RoleHTML::OutputAbility();
    echo '</div>',Text::LF;
  }

  //投票結果表示 (クイズ村 GM 専用)
  function OutputQuizVote() {
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
  protected function OutputTalk() {
    $cache_type = 'talk_play';
    if (! DB::$ROOM->IsPlaying() && DocumentCache::Enable($cache_type)) {
      $cache_name = 'game_play/talk';
      if (RQ::Get()->icon) $cache_name .= '_icon';
      if (RQ::Get()->name) $cache_name .= '_name';

      DocumentCache::Load($cache_name, CacheConfig::TALK_PLAY_EXPIRE);
      $filter = DocumentCache::GetTalk($this->update_talk);
      DocumentCache::Save($filter, true, $this->update_talk);
      DocumentCache::Output($cache_type);
    } else {
      $filter = Talk::Get();
    }
    $filter->Output();
  }

  //自分の遺言出力
  protected function OutputMyLastWords() {
    $str = UserDB::GetLastWords(DB::$SELF->id);
    if ($str == '') return false;

    $str = Text::Line($str); //改行コードを変換
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
  protected function OutputSound() {
    if (DB::$ROOM->IsDay() && JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //夜明け
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



/**
 * ゲーム開始前の画面の出力を制御します。
 */
class GamePlayView_Before extends GamePlayView {
  function __construct() {
    parent::__construct();
    Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
  }

  /** 非同期APIが要求する発言ログのみを出力します */
  function OutputTalkAsync() {
    $this->OutputSceneAsync();
    GameHTML::OutputPlayer();
    $this->OutputTalk();
    if (RQ::Get()->play_sound) {
      $this->OutputSound();
    }
  }

  protected function CanObjection($left_time) {
    return true;
  }

  protected function OutputLinkContent() {
    parent::OutputLinkContent();

    $url = sprintf('%s&user_no=%s', $this->SelectURL(array()), DB::$SELF->id);
    $this->OutputHeaderLink('user_manager', $url); //登録情報変更
    if (DB::$SELF->IsDummyBoy()) { //村オプション変更
      $this->OutputHeaderLink('room_manager', $this->SelectURL(array()));
    }
  }

  protected function OutputTimeTable() {
    $format = '<div class="caution">%s%s<span>%s</span>%s</div>' . Text::LF;
    printf($format, Text::LF,
      GamePlayMessage::BEFOREGAME_CATION, GamePlayMessage::BEFOREGAME_VOTE, Text::LF);
    RoomOption::Output(); //ゲームオプション表示

    parent::OutputTimeTable();
  }

  protected function OutputTimelimitContent($left_time) {
    //ゲーム開始前は投票時間などの制限がない
  }

  protected function OutputSound() {
    //入村・満員
    if (JinrouCookie::$user_count > 0) {
      $user_count = DB::$USER->GetUserCount();
      $max_user   = RoomDB::Get('max_user');
      if ($user_count == $max_user && JinrouCookie::$user_count != $max_user) {
        Sound::Output('full');
      } elseif (JinrouCookie::$user_count != $user_count) {
        Sound::Output('entry');
      }
    }

    parent::OutputSound();
  }
}



/**
 * ゲーム終了後の画面の出力を制御します。
 */
class GamePlayView_After extends GamePlayView {
  function __construct() {
    parent::__construct();
    Loader::LoadFile('winner_message');
  }

  /** 非同期APIが要求する発言ログのみを出力します */
  function OutputTalkAsync() {
    $this->OutputSceneAsync();
    $this->OutputTalk();
  }

  protected function CanObjection($left_time) {
    return false;
  }

  protected function OutputLinkContent() {
    echo DB::$ROOM->GenerateTitleTag();
    $this->OutputLogLinkList();

    //ゲーム終了後は自動更新しない
    echo Text::BR;
    
    $this->OutputHeaderSwitchLink('icon'); //アイコン表示
    $this->OutputHeaderSwitchLink('name'); //ユーザ名表示
    $this->OutputHeaderSwitchLink('list_down'); //プレイヤーリストの表示位置

    //別ページリンク
    $this->OutputHeaderLink('game_play', $this->SelectURL(array('list_down')));
    if (ServerConfig::DEBUG_MODE) { //観戦モードリンク
      $this->OutputHeaderLink('game_view', $this->SelectURL(array()));
    }

    GameHTML::OutputLogLink();
  }

  protected function OutputLogLinkList() {
    parent::OutputLogLinkList();

    if (DB::$ROOM->date > 0) {
      $this->OutputLogLink(RoomScene::DAY, GamePlayMessage::LOG_DAY, DB::$ROOM->date);
    }

    if (TalkDB::ExistsLastNight()) {
      $this->OutputLogLink(RoomScene::NIGHT, GamePlayMessage::LOG_NIGHT, DB::$ROOM->date);
    }

    $this->OutputLogLink(RoomScene::AFTER, GamePlayMessage::LOG_AFTERGAME);
    $this->OutputLogLink(RoomScene::HEAVEN, GamePlayMessage::LOG_HEAVEN);
  }

  protected function OutputTimeTable() {
    Winner::Output();
  }

  protected function OutputTimelimitContent($left_time) {
    //ゲーム終了後は投票時間などの制限がない
  }

  protected function OutputMyLastWords() {
    return false; //ゲーム終了後は自分の遺言を表示しない
  }
}



/**
 * 死者会話画面の出力を制御します。
 */
class GamePlayView_Heaven extends GamePlayView {
  function Output() {
    if (!DB::$SELF->IsDead()) return;

    GameHTML::OutputHeader('game_play');
    $this->OutputLink();
    $this->OutputSayLimit();
    $this->OutputTalk();
    HTML::OutputFooter();
  }

  function OutputTalkAsync() {
    if (!DB::$SELF->IsDead()) return;

    $this->OutputSceneAsync();
    $this->OutputTalk();
  }

  protected function CanObjection($left_time) {
    return false;
  }

  protected function OutputLinkContent() {
    printf('<td>%s</td>' . Text::LF, GamePlayMessage::HEAVEN_TITLE);
    $this->OutputLogLinkList();
  }

  protected function OutputLogLinkList() {
    parent::OutputLogLinkList();

    if (DB::$ROOM->date > 1 && DB::$ROOM->IsNight()) {
      $this->OutputLogLink(RoomScene::DAY, GamePlayMessage::LOG_DAY, DB::$ROOM->date);
    }
  }

  protected function OutputTalk() {
    $cache_type = 'talk_heaven';
    if (DocumentCache::Enable($cache_type)) {
      $cache_name = 'game_play/heaven';
      if (RQ::Get()->icon) $cache_name .= '_icon';

      DocumentCache::Load($cache_name, CacheConfig::TALK_HEAVEN_EXPIRE);
      $filter = DocumentCache::GetTalk($this->update_talk, true);
      DocumentCache::Save($filter, true, $this->update_talk);
      DocumentCache::Output($cache_type);
    } else {
      $filter = Talk::GetHeaven();
    }
    $filter->Output();
  }

  protected function OutputMyLastWords() {
    return false; //死者会話には自分の遺言を表示しない
  }
}

