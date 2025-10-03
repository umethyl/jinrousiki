<?php
//◆文字化け抑制◆//
//-- GamePlay コントローラー --//
final class GamePlayController extends JinrouController {
  private static $view;

  protected static function GetLoadRequest() {
    return 'game_play';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::LoginGamePlay();
  }

  protected static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->Flag()->Set(RoomMode::DEAD,   RQ::Get()->dead_mode);
    DB::$ROOM->Flag()->Set(RoomMode::HEAVEN, RQ::Get()->heaven_mode);
    DB::$ROOM->SetTime();
    DB::$ROOM->InitializeSuddenDeath();

    //-- シーンに応じた追加クラスをロード --//
    if (DB::$ROOM->IsOn(RoomMode::HEAVEN)) {
      self::$view = new GamePlayView_Heaven();
    } elseif (DB::$ROOM->IsFinished()) {
      self::$view = new GamePlayView_After();
    } elseif (DB::$ROOM->IsBeforeGame()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene);
      self::$view = new GamePlayView_Before();
    } elseif (DB::$ROOM->IsDay()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene);
      self::$view = new GamePlayView_Day();
    } elseif (DB::$ROOM->IsNight()) {
      self::$view = new GamePlayView_Night();
    }
  }

  protected static function LoadUser() {
    DB::LoadUser();
  }

  protected static function LoadSelf() {
    DB::LoadSelf();
  }

  protected static function LoadExtra() {
    //-- 音声情報 --//
    Objection::Set(); //「異議」ありセット判定
    if (RQ::Get()->play_sound) { //音でお知らせ
      JinrouCookie::Set(); //クッキー情報セット
    }

    //-- リンク情報収集 --//
    RQ::Get()->StackIntParam(RequestDataGame::ID, false);
    RQ::Get()->StackIntParam(RequestDataGame::RELOAD);

    $stack = [
      RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::NAME, RequestDataGame::DOWN
    ];
    if (GameConfig::ASYNC) {
      $stack[] = RequestDataGame::ASYNC;
    }
    foreach ($stack as $name) {
      RQ::Get()->StackOnParam($name);
    }

    foreach ([RoomMode::DEAD, RoomMode::HEAVEN] as $name) {
      RQ::Get()->StackOnValue($name . '_mode', DB::$ROOM->IsOn($name));
    }
  }

  protected static function Output() {
    self::Talk();
    self::$view->Output();
  }

  //実行 (非同期用)
  public static function ExecuteAsync() {
    self::Load();
    self::FilterSilence();
    self::$view->OutputAsync();
  }

  //発言処理
  private static function Talk() {
    GamePlayTalk::InitStack(); //判定用変数初期化

    //発言送信フレーム (bottom) 判定 > 霊界GM判定
    if (DB::$ROOM->IsOff(RoomMode::DEAD) || DB::$ROOM->IsOn(RoomMode::HEAVEN)) {
      GamePlayTalk::Convert(); //発言変換処理

      /*
	空発言 (ゲーム停滞判定) > CSRF対策 > 遺言 (詳細判定は関数内で行う) >
	発言判定(死者 / 身代わり君 / 同一ゲームシーン) > 発言不可 (ゲーム停滞判定)
      */
      if (RQ::Get()->say == '') {
	self::FilterSilence();
      } elseif (Security::IsInvalidToken(DB::$ROOM->id)) {
	HTML::OutputUnusableError();
      } elseif (RQ::Get()->last_words && (DB::$ROOM->IsBeforeGame() || ! DB::$SELF->IsDummyBoy())) {
	GamePlayTalk::StoreLastWords(RQ::Get()->say);
      } elseif (DB::$SELF->IsDead() || DB::$SELF->IsDummyBoy() || ! DB::$SELF->IsInvalidScene()) {
	GamePlayTalk::Store(RQ::Get()->say);
      } else {
	self::FilterSilence();
      }

      if (DB::$SELF->IsInvalidScene()) { //ゲームシーンを更新
	DB::$SELF->Update('last_load_scene', DB::$ROOM->scene);
      }
    } elseif (DB::$ROOM->IsOn(RoomMode::DEAD) && DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      if (false === GameTime::IsInTime()) { //超過なら突然死タイマーを見れるようにする
	DB::$ROOM->SetSuddenDeath();
      }
    }
  }

  //ゲーム停滞のチェック
  private static function FilterSilence() {
    if (false === DB::$ROOM->IsPlaying()) { //スキップ判定
      return true;
    }

    //経過時間を取得
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      if ($left_time > 0) { //制限時間超過判定
	return true;
      }
    } else { //仮想時間制
      if (false === self::LockScene()) { //判定条件が全て DB なので即ロック
	return false;
      }
      $silence_pass_time = GameTime::GetTalkPass($left_time, true);

      if ($left_time > 0) { //制限時間超過判定
	if (RoomDB::GetTime() <= TimeConfig::SILENCE) { //沈黙判定
	  return DB::Rollback();
	}

	//沈黙メッセージを発行してリセット
	$talk = new RoomTalkStruct(sprintf(GamePlayMessage::SILENCE, $silence_pass_time));
	$talk->Set(TalkStruct::SPEND_TIME, TimeConfig::SILENCE_PASS);
	DB::$ROOM->Talk($talk);
	return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback();
      }
    }

    //オープニングなら即座に夜に移行する
    if (DB::$ROOM->IsDate(1) && DB::$ROOM->IsDay() && DB::$ROOM->IsOption('open_day')) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (false === self::LockScene()) { //シーン再判定
	  return false;
	}
      }
      DB::$ROOM->ChangeNight(); //夜に切り替え
      return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback(); //最終書き込み時刻を更新
    }

    if (! RoomDB::IsOvertimeAlert()) { //警告メッセージ出力判定
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (false === self::LockScene()) { //シーン再判定
	  return false;
	}
      }

      //警告メッセージを出力 (最終出力判定は呼び出し先で行う)
      $str = sprintf(GamePlayMessage::SUDDEN_DEATH_ALERT, Time::Convert(TimeConfig::SUDDEN_DEATH));
      if (DB::$ROOM->OvertimeAlert($str)) { //出力したら突然死タイマーをリセット
	DB::$ROOM->ResetSuddenDeath();
	if (DB::$ROOM->IsDay() && DB::$ROOM->IsOption('no_silence')) { //沈黙死 + 処刑投票処理
	  self::VoteNoSilence();
	}
	return DB::Commit(); //ロック解除
      }
    }

    DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得

    //制限時間前ならスキップ (この段階でロックしているのは仮想時間制のみ)
    if (DB::$ROOM->sudden_death > 0) {
      return DB::$ROOM->IsRealTime() || DB::Rollback();
    }

    //制限時間を過ぎていたら未投票の人を突然死させる
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
      if (false === self::LockScene()) { //シーン再判定
	return false;
      }

      DB::$ROOM->SetSuddenDeath(); //制限時間を再計算
      if (DB::$ROOM->sudden_death > 0) {
	return DB::Rollback();
      }
    }

    if (abs(DB::$ROOM->sudden_death) > TimeConfig::SERVER_DISCONNECT) { //サーバダウン検出
      //突然死タイマーと警告出力判定をリセット
      return RoomDB::UpdateOvertimeAlert() ? DB::Commit() : DB::Rollback();
    }

    $novote_list = []; //未投票者リスト
    DB::$ROOM->LoadVote(); //投票情報を取得
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->Get() as $user) { //生存中の未投票者を取得
	if ($user->IsLive() && ! $user->ExistsVote()) {
	  $novote_list[] = $user->id;
	}
      }
    } elseif (DB::$ROOM->IsNight()) {
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      foreach (DB::$USER->Get() as $user) { //未投票チェック
	if (RoleUser::ImcompletedVoteNight($user, $vote_data)) {
	  $novote_list[] = $user->id;
	}
      }
    }

    //未投票突然死処理
    foreach ($novote_list as $id) {
      DB::$USER->SuddenDeath($id, DeadReason::NOVOTED);
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
    return DB::Commit(); //ロック解除
  }

  //シーン再判定付きロック処理
  private static function LockScene() {
    if (false === DB::Transaction()) {
      return false;
    }

    if (RoomDB::Get('scene', true) != DB::$ROOM->scene) { //シーン再判定 (ロック付き)
      DB::Rollback();
      return false;
    } else {
      return true;
    }
  }

  //沈黙死 + 処刑投票処理
  private static function VoteNoSilence() {
    if (RoomDB::Get('vote_count', true) != DB::$ROOM->vote_count) { //投票回数判定
      return;
    }

    if (TalkDB::CountNoVoteTalker() > 0) { //発言者の投票済み判定
      return;
    }

    RQ::Set(RequestDataVote::SITUATION, VoteAction::VOTE_KILL); //仮想的に処刑投票コマンドをセット
    /*
      Vote は初期化時点で ROOM/USER をロックをかけて生成している
      処刑投票処理時に霊界操作で変更される要素を参照しないので
      GamePlay 用のパラメータ再セットが煩雑になる事を配慮し、再生成は行わない
    */
    VoteDay::Aggregate();
  }
}

//-- GamePlay 出力基礎クラス --//
abstract class GamePlayView extends stdClass {
  public function __construct() {}

  //リンク情報取得 (差分型)
  protected function GetURL(array $except, $header = null) {
    $url    = isset($header) ? $header : GamePlayHTML::GetURLHeader();
    $params = RQ::Get()->GenerateUrl($except, null);
    return $url . URL::HEAD . $params;
  }

  //リンク情報取得 (抽出型)
  protected function SelectURL(array $list, $header = null) {
    $url    = isset($header) ? $header : '';
    $params = RQ::Get()->GenerateUrl(null, array_merge($list, [RequestDataGame::ID]));
    return $url . URL::HEAD . $params;
  }

  //出力
  public function Output() {
    GameHTML::OutputHeader('game_play');
    $this->OutputHeader();
    $this->OutputTimeTable();
    $this->OutputLimitSay();
    if (false === RQ::Get()->list_down) {
      GameHTML::OutputPlayer();
    }
    GamePlayHTML::OutputAbility();
    GamePlayHTML::OutputVote();
    $this->OutputTalk();
    GameHTML::OutputLastWords();
    GameHTML::OutputDead();
    GameHTML::OutputVote();
    if (DB::$ROOM->IsOff(RoomMode::DEAD)) {
      $this->OutputSelfLastWords();
    }
    if (true === RQ::Get()->list_down) {
      GameHTML::OutputPlayer();
    }
    if (true === RQ::Get()->play_sound) {
      $this->OutputSound();
    }
    HTML::OutputFooter();
  }

  //ヘッダ出力
  final protected function OutputHeader() {
    TableHTML::OutputHeader('game-header');
    $this->OutputHeaderTitle();
    $this->OutputHeaderLogLink();
    $this->OutputHeaderLinkHeader();
    $this->OutputHeaderLink();
    $this->OutputHeaderLinkFooter();
    TableHTML::OutputTdFooter();
    TableHTML::OutputFooter();
  }

  //ヘッダタイトル
  protected function OutputHeaderTitle() {
    RoomHTML::OutputTitle();
  }

  //ヘッダログリンク
  protected function OutputHeaderLogLink() {}

  //ヘッダログリンクヘッダ
  protected function OutputHeaderLinkHeader() {
    Text::Output(GamePlayHTML::GetLogLinkTableTd());

    //中央フレーム内の下界発言更新ボタン (死亡者用)
    if (DB::$ROOM->IsOn(RoomMode::DEAD) && DB::$SELF->IsDead()) {
      $url = $this->GetURL([RequestDataRoom::DEAD, RequestDataRoom::HEAVEN], 'game_play.php');
      GamePlayHTML::OutputReloadButton($url . URL::GetSwitch(RequestDataRoom::DEAD));
    }

    GameHTML::OutputAutoReloadLink($this->GetURL([RequestDataGame::RELOAD]));
  }

  //リンク出力
  protected function OutputHeaderLink() {
    if (GameConfig::ASYNC) {
      $this->OutputHeaderSwitchLink(RequestDataGame::ASYNC);
    }
    $this->OutputHeaderSwitchLink(
      RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::DOWN
    );

    $url = $this->SelectURL([]) . URL::GetSwitch('describe_room');
    GamePlayHTML::OutputHeaderLink('room_manager', $url, 'describe_room');

    //別ページリンク
    GamePlayHTML::OutputHeaderLink('game_play', $this->SelectURL([RequestDataGame::DOWN]));
    if (ServerConfig::DEBUG_MODE) { //観戦モードリンク
      GamePlayHTML::OutputHeaderLink('game_view', $this->SelectURL([]));
    }
  }

  //ヘッダログリンクフッタ
  protected function OutputHeaderLinkFooter() {}

  //プレイ中ログリンク一覧出力
  final protected function OutputGameLogLinkList() {
    $this->OutputGameLogLinkListHeader();
    GameHTML::OutputGameLogLinkList($this->GetLogLinkHeader());
    $this->OutputGameLogLinkListFooter();
  }

  //プレイ中ログリンク一覧ヘッダ出力
  protected function OutputGameLogLinkListHeader() {
    echo GamePlayHTML::GetLogLinkTableTd() . GameMessage::LOG_LINK_VIEW . ' ';
  }

  //プレイ中ログリンク一覧フッタ出力
  protected function OutputGameLogLinkListFooter() {}

  //プレイ中ログリンク出力
  final protected function OutputGameLogLink($scene, $date = null) {
    GameHTML::OutputGameLogLink($this->GetLogLinkHeader(), $scene, $date);
  }

  //ヘッダーリンク出力 (スイッチ型)
  final protected function OutputHeaderSwitchLink(...$type_list) {
    foreach ($type_list as $type) {
      $url = $this->GetURL([$type]);
      if (RequestDataGame::DOWN == $type) {
	GamePlayHTML::OutputHeaderListLink($url, $type);
      } else {
	GamePlayHTML::OutputHeaderSwitchLink($url, $type);
      }
    }
  }

  //タイムテーブル出力
  protected function OutputTimeTable() {
    $this->OutputTimeTableHeader();
    GameHTML::OutputTimeTable();
    $left_time = 0;
    if (DB::$ROOM->IsBeforeGame()) {
      GamePlayHTML::OutputTimeSetting();
    } elseif (DB::$ROOM->IsPlaying()) {
      GameHTML::OutputTimePass($left_time);
    }
    if (DB::$ROOM->IsOption('limit_talk')) {
      GamePlayHTML::OutputTalkCount();
    }
    $this->OutputObjection($left_time);
    TableHTML::OutputFooter();

    $this->OutputTimelimit($left_time);
  }

  //タイムテーブルヘッダ出力
  protected function OutputTimeTableHeader() {}

  //異議ありボタン表示
  final protected function OutputObjection($left_time) {
    if ($this->IgnoreObjection($left_time)) { //スキップ判定
      return;
    }

    $stack = [
      RequestDataGame::RELOAD, RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::DOWN
    ];
    if (GameConfig::ASYNC) {
      $stack[] = RequestDataGame::ASYNC;
    }
    GamePlayHTML::OutputObjection($this->SelectURL($stack, 'game_play.php'));
  }

  //異議ありボタン表示スキップ判定
  protected function IgnoreObjection($left_time) {
    //昼 + 制限時間内  + 生存者のみ
    return false === (DB::$ROOM->IsDay() && $left_time > 0 && DB::$ROOM->IsOff(RoomMode::DEAD));
  }

  //時間制限通知出力
  final protected function OutputTimelimit($left_time) {
    if ($this->IgnoreTimelimit()) { //スキップ判定
      return;
    }

    HTML::OutputDivHeader('timelimit');
    if (DB::$ROOM->IsEvent('wait_morning')) {
      GameHTML::OutputVoteAnnounce(GameMessage::WAIT_MORNING);
    } elseif ($left_time == 0) {
      GameHTML::OutputVoteAnnounce();
      if (DB::$ROOM->sudden_death > 0) {
	$str = GamePlayMessage::SUDDEN_DEATH_TIME . Time::Convert(DB::$ROOM->sudden_death);
	if (DB::$ROOM->IsDay() || DB::$SELF->IsDummyBoy()) {
	  $str .= ' / ' . $this->CountNoVoted();
	}
	GamePlayHTML::OutputSuddenDeathAnnounce($str);
      }
    } elseif (DB::$SELF->IsDummyBoy()) {
      GamePlayHTML::OutputSuddenDeathAnnounce($this->CountNoVoted());
    }

    if (DB::$SELF->IsDead() && false === DB::$ROOM->IsOpenCast()) {
      GameHTML::OutputVoteAnnounce(GameMessage::CLOSE_CAST);
    }
    HTML::OutputDivFooter();
  }

  //時間制限通知スキップ判定
  protected function IgnoreTimelimit() {
    return false;
  }

  //未投票人数情報取得
  final protected function CountNoVoted() {
    $count = 0;
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->Get() as $user) {
	if ($user->IsLive() && false === isset($user->target_no)) {
	  $count++;
	}
      }
    } elseif (DB::$ROOM->IsNight() && DB::$SELF->IsDummyBoy()) { //身代わり君以外は不可
      if (DB::$ROOM->Stack()->IsEmpty('vote')) {
	DB::$ROOM->LoadVote();
      }
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      foreach (DB::$USER->Get() as $user) { //未投票チェック
	if (RoleUser::ImcompletedVoteNight($user, $vote_data)) {
	  $count++;
	}
      }
    }
    return sprintf(GamePlayMessage::NOVOTED_COUNT, $count);
  }

  //発言制限メッセージ出力
  final protected function OutputLimitSay() {
    if (false === Talk::Stack()->Get(Talk::LIMIT_SAY)) {
      HTML::OutputWarning(GamePlayMessage::LIMIT_SAY);
    }

    if (DB::$ROOM->IsOption('limit_talk') && Talk::Stack()->Get(Talk::LIMIT_TALK)) {
      HTML::OutputWarning(GamePlayMessage::LIMIT_TALK);
    }
  }

  //会話出力
  protected function OutputTalk() {
    if (false === DB::$ROOM->IsPlaying() &&
	JinrouCacheManager::Enable(JinrouCacheManager::TALK_PLAY)) {
      $filter = JinrouCacheManager::Get(JinrouCacheManager::TALK_PLAY);
    } else {
      $filter = Talk::Fetch();
    }
    $filter->Output();
  }

  //自分の遺言出力
  final protected function OutputSelfLastWords() {
    if ($this->IgnoreSelfLastWords()) { //スキップ判定
      return;
    }

    $str = UserDB::GetLastWords(DB::$SELF->id);
    if ($str == '') {
      return false;
    }

    $str = Text::ConvertLine($str);
    if ($str == '') {
      return false;
    }
    GamePlayHTML::OutputSelfLastWords($str);
  }

  //自分の遺言スキップ判定
  protected function IgnoreSelfLastWords() {
    return false;
  }

  //音声出力
  final protected function OutputSound() {
    if ($this->IgnoreSound()) {
      return;
    }

    $this->OutputSoundHeader();
    $this->OutputSoundScene();
    $this->OutputSoundObjection();
  }

  //音声スキップ判定
  protected function IgnoreSound() {
    return false;
  }

  //音声出力 (ヘッダ)
  protected function OutputSoundHeader() {}

  //音声出力 (シーン別)
  protected function OutputSoundScene() {}

  //音声出力 (「異議」あり)
  final protected function OutputSoundObjection() {
    if ($this->IgnoreSoundObjection()) {
      return;
    }

    Objection::OutputSound();
  }

  //音声スキップ判定 (「異議」あり)
  protected function IgnoreSoundObjection() {
    return false;
  }

  //発言出力 (非同期用)
  public function OutputAsync() {
    GamePlayHTML::OutputSceneAsync();
    $this->OutputTimelimit(GameTime::GetLeftTime());
    GamePlayHTML::OutputVote();
    if (DB::$ROOM->IsNight()) {
      GamePlayHTML::OutputAbility();
    }
    $this->OutputTalk();
    if (true === RQ::Get()->play_sound) {
      $this->OutputSound();
    }
  }

  //過去ログリンクヘッダタグ取得
  private function GetLogLinkHeader() {
    if (false === isset($this->link_header)) {
      $this->link_header = $this->SelectURL([], 'game_log.php');
    }
    return $this->link_header;
  }
}

//-- GamePlay 出力クラス (ゲーム開始前) --//
class GamePlayView_Before extends GamePlayView {
  protected function OutputHeaderLinkFooter() {
    $url = sprintf('%s&user_no=%s', $this->SelectURL([]), DB::$SELF->id);
    GamePlayHTML::OutputHeaderLink('user_manager', $url); //登録情報変更
    if (RoomOptionManager::EnableChange()) { //村オプション変更
      GamePlayHTML::OutputHeaderLink('room_manager', $this->SelectURL([]));
    }
  }

  protected function OutputTimeTableHeader() {
    GamePlayHTML::OutputHeaderCaution();
    RoomOption::Output();
  }

  protected function IgnoreObjection($left_time) {
    return false;
  }

  protected function IgnoreTimelimit() {
    return true;
  }

  protected function OutputSoundHeader() {
    if (JinrouCookie::$user_count > 0) { //人数変動
      $user_count = DB::$USER->Count();
      $max_user   = RoomDB::Get('max_user');
      if ($user_count == $max_user && JinrouCookie::$user_count != $max_user) { //満員
	SoundHTML::Output('full');
      } elseif (JinrouCookie::$user_count != $user_count) { //入村
	SoundHTML::Output('entry');
      }
    }
  }

  protected function OutputSoundScene() {
    if (JinrouCookie::$vote_result == DB::$ROOM->scene) { //投票完了
      SoundHTML::Output('vote_success');
    }
  }

  public function OutputAsync() {
    GamePlayHTML::OutputSceneAsync();
    GameHTML::OutputPlayer();
    $this->OutputTalk();
    if (true === RQ::Get()->play_sound) {
      $this->OutputSound();
    }
  }
}

//-- GamePlay 出力クラス (昼) --//
class GamePlayView_Day extends GamePlayView {
  protected function OutputGameLogLinkListHeader() {
    GameHTML::OutputGameLogLinkListHeader();
  }

  protected function OutputHeaderLinkFooter() {
    $this->OutputGameLogLinkList();
  }

  protected function OutputSoundScene() {
    if (JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //夜明け
      SoundHTML::Output('morning');
    }

    if (JinrouCookie::$vote_result == DB::$ROOM->scene) { //投票完了
      SoundHTML::Output('vote_success');
    }
  }
}

//-- GamePlay 出力クラス (夜) --//
class GamePlayView_Night extends GamePlayView {
  protected function OutputGameLogLinkListHeader() {
    GameHTML::OutputGameLogLinkListHeader();
  }

  protected function OutputHeaderLinkFooter() {
    $this->OutputGameLogLinkList();
  }

  protected function OutputSoundScene() {
    if (JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //日没
      SoundHTML::Output('night');
    }
  }

  protected function IgnoreSoundObjection() {
    return true;
  }
}

//-- GamePlay 出力クラス (ゲーム終了後) --//
class GamePlayView_After extends GamePlayView {
  protected function OutputHeaderLogLink() {
    $this->OutputGameLogLinkList();
  }

  protected function OutputHeaderLinkHeader() {
    echo Text::BR; //ゲーム終了後は自動更新しない
  }

  protected function OutputHeaderLink() {
    $this->OutputHeaderSwitchLink(
      RequestDataGame::ICON, RequestDataGame::NAME, RequestDataGame::DOWN
    );

    //別ページリンク
    GamePlayHTML::OutputHeaderLink('game_play', $this->SelectURL([RequestDataGame::DOWN]));
    if (ServerConfig::DEBUG_MODE) { //観戦モードリンク
      GamePlayHTML::OutputHeaderLink('game_view', $this->SelectURL([]));
    }

    GameHTML::OutputLogLink();
  }

  protected function OutputGameLogLinkListFooter() {
    if (DB::$ROOM->date > 0) {
      $this->OutputGameLogLink(RoomScene::DAY, DB::$ROOM->date);
    }

    if (TalkDB::ExistsLastNight()) {
      $this->OutputGameLogLink(RoomScene::NIGHT, DB::$ROOM->date);
    }

    $this->OutputGameLogLink(RoomScene::AFTER);
    $this->OutputGameLogLink(RoomScene::HEAVEN);
  }

  protected function OutputTimeTable() {
    Winner::Output();
  }

  protected function IgnoreSelfLastWords() {
    return true;
  }

  protected function IgnoreSound() {
    return true;
  }

  public function OutputAsync() {
    GamePlayHTML::OutputSceneAsync();
    $this->OutputTalk();
  }
}

//-- GamePlay 出力クラス (霊界) --//
class GamePlayView_Heaven extends GamePlayView {
  public function Output() {
    if (false === DB::$SELF->IsDead()) {
      return;
    }

    GameHTML::OutputHeader('game_play');
    $this->OutputHeader();
    $this->OutputLimitSay();
    $this->OutputTalk();
    HTML::OutputFooter();
  }

  protected function OutputHeaderTitle() {
    TableHTML::OutputTd(GamePlayMessage::HEAVEN_TITLE);
  }

  protected function OutputHeaderLinkHeader() {
    return;
  }

  protected function OutputHeaderLink() {
    return;
  }

  protected function IgnoreObjection($left_time) {
    return true;
  }

  protected function OutputTalk() {
    if (JinrouCacheManager::Enable(JinrouCacheManager::TALK_HEAVEN)) {
      $filter = JinrouCacheManager::Get(JinrouCacheManager::TALK_HEAVEN);
    } else {
      $filter = Talk::FetchHeaven();
    }
    $filter->Output();
  }

  protected function IgnoreSelfLastWords() {
    return true;
  }

  protected function IgnoreSound() {
    return true;
  }

  public function OutputAsync() {
    if (false === DB::$SELF->IsDead()) {
      return;
    }

    GamePlayHTML::OutputSceneAsync();
    $this->OutputTalk();
  }
}
