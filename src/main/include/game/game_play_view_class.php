<?php
//◆文字化け抑制◆//
//-- GamePlay 出力基礎クラス --//
abstract class GamePlayView extends stdClass {
  public function __construct() {}

  //リンク情報取得 (差分型)
  protected function GetURL(array $except, $header = null) {
    $url    = isset($header) ? $header : GamePlayHTML::GetURLHeader();
    $params = RQ::Fetch()->GenerateUrl($except, null);
    return $url . URL::HEAD . $params;
  }

  //リンク情報取得 (抽出型)
  protected function SelectURL(array $list, $header = null) {
    $url    = isset($header) ? $header : '';
    $params = RQ::Fetch()->GenerateUrl(null, array_merge($list, [RequestDataGame::ID]));
    return $url . URL::HEAD . $params;
  }

  //出力
  public function Output() {
    if ($this->IgnoreOutput()) {
      return;
    }

    GameHTML::OutputHeader('game_play');
    $this->OutputHeader();
    if ($this->EnableGamePlay()) {
      $this->OutputTimeTable();
    }
    $this->OutputLimitSay();
    if ($this->EnableGamePlay()) {
      if (RQ::Fetch()->Disable(RequestDataGame::LIST)) {
	GameHTML::OutputPlayer();
      }
      GamePlayHTML::OutputAbility();
      GamePlayHTML::OutputVote();
      if (DB::$ROOM->IsOff(RoomMode::DEAD) && RQ::Fetch()->Enable(RequestDataGame::WORDS)) {
	$this->OutputSelfLastWords();
      }
      if ($this->EnableForm() && RQ::Fetch()->Enable(RequestDataGame::INDIVIDUAL)) {
	$this->OutputForm();
      }
    }
    $this->OutputTalk();
    if ($this->EnableGamePlay()) {
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
      GameHTML::OutputVote();
      if (DB::$ROOM->IsOff(RoomMode::DEAD) && RQ::Fetch()->Disable(RequestDataGame::WORDS)) {
	$this->OutputSelfLastWords();
      }
      if (RQ::Fetch()->Enable(RequestDataGame::LIST)) {
	GameHTML::OutputPlayer();
      }
      if (RQ::Fetch()->Enable(RequestDataGame::SOUND)) {
	$this->OutputSound();
      }
      if ($this->EnableForm() && RQ::Fetch()->Disable(RequestDataGame::INDIVIDUAL)) {
	$this->OutputForm();
      }
    }
    HTML::OutputFooter();
  }

  //出力スキップ判定
  protected function IgnoreOutput() {
    return false;
  }

  //ヘッダ出力
  final protected function OutputHeader() {
    TableHTML::OutputHeader([HTML::CSS => 'game-header'], tr: true);
    $this->OutputHeaderTitle();
    $this->OutputHeaderLogLink();
    $this->OutputHeaderLinkHeader();
    $this->OutputHeaderLink();
    $this->OutputHeaderLinkFooter();
    TableHTML::OutputTdFooter();
    TableHTML::OutputFooter(tr: true);
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
      GamePlayHTML::OutputReloadButton($url . URL::AddSwitch(RequestDataRoom::DEAD));
    }

    GameHTML::OutputAutoReloadLink($this->GetURL([RequestDataGame::RELOAD]));
  }

  //リンク出力
  protected function OutputHeaderLink() {
    if (GameConfig::ASYNC) {
      $this->OutputHeaderSwitchLink([RequestDataGame::ASYNC]);
    }

    $stack = [RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::LIST];
    if (DB::$ROOM->IsOff(RoomMode::DEAD)) { //死者は自分の遺言は表示されない
      $stack[] = RequestDataGame::WORDS;
    }
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      $stack[] = RequestDataGame::INDIVIDUAL;
    }
    $this->OutputHeaderSwitchLink($stack);

    $url = $this->SelectURL([]) . URL::AddSwitch('describe_room');
    GamePlayHTML::OutputHeaderLink('room_manager', $url, 'describe_room');

    //別ページリンク
    $list = [RequestDataGame::LIST];
    if (DB::$ROOM->IsOff(RoomMode::DEAD)) {
      $list[] = RequestDataGame::WORDS;
    }
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      $list[] = RequestDataGame::INDIVIDUAL;
    }
    GamePlayHTML::OutputHeaderLink('game_play', $this->SelectURL($list));
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
  final protected function OutputHeaderSwitchLink(array $type_list) {
    foreach ($type_list as $type) {
      $url = $this->GetURL([$type]);
      switch ($type) {
      case RequestDataGame::LIST:
      case RequestDataGame::WORDS:
      case RequestDataGame::INDIVIDUAL:
	GamePlayHTML::OutputHeaderListLink($url, $type);
	break;

      default:
	GamePlayHTML::OutputHeaderSwitchLink($url, $type);
	break;
      }
    }
  }

  //ゲームプレイ状況出力有効判定
  protected function EnableGamePlay() {
    return true;
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
    TableHTML::OutputFooter(tr: true);

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
      RequestDataGame::RELOAD, RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::LIST,
      RequestDataGame::WORDS
    ];
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      $stack[] = RequestDataGame::INDIVIDUAL;
    }
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

    DivHTML::OutputHeader([HTML::CSS => 'timelimit']);
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
    DivHTML::OutputFooter();
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

  //フォーム出力有効判定
  protected function EnableForm() {
    return DB::$SELF->IsDummyBoy();
  }

  //フォーム出力
  final protected function OutputForm() {
    $stack = [
      RequestDataGame::RELOAD,
      RequestDataGame::SOUND,
      RequestDataGame::ICON,
      RequestDataGame::LIST,
      RequestDataGame::WORDS
    ];
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      $stack[] = RequestDataGame::INDIVIDUAL;
    }
    if (GameConfig::ASYNC) {
      $stack[] = RequestDataGame::ASYNC;
    }

    GamePlayHTML::OutputForm($this->SelectURL($stack, 'game_play.php'));
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
    if (RQ::Enable('play_sound')) {
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
