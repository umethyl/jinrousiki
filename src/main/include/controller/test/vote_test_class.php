<?php
//投票テスト
class VoteTest {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('game_view', true);
    include('data/vote_header.php');

    //特殊モード
    require_once('data/vote_flag.php');

    //仮想村
    $stack = array('name' => GameMessage::ROOM_TITLE_FOOTER, 'status' => RoomStatus::PLAYING);
    DevRoom::Initialize($stack);
    include('data/vote_option.php');

    //仮想ユーザ
    DevUser::Initialize(30);
    include('data/vote_user.php');
    DevUser::Complement();

    //仮想投票データ
    require_once('data/vote_room.php');
    RQ::GetTest()->vote = new stdClass();
    RQ::GetTest()->vote->day = array();
    include('data/vote_target_day.php');
    include('data/vote_target_night.php');

    //仮想イベント
    include('data/vote_event.php');

    //仮想発言
    RQ::Set('say', '');
    include('data/vote_say.php');

    //データロード
    DevRoom::Load();
    DB::$ROOM->date = VoteTestRoom::DATE;
    DB::$ROOM->SetScene(VoteTestRoom::SCENE);
    if (VoteTestRoom::TIME) DB::$ROOM->system_time = Time::Get(); //現在時刻を取得
    RQ::GetTest()->winner = VoteTestRoom::WINNER;

    DevUser::Load();
    DB::$USER->SetEvent(); //天候テスト用
    include('data/vote_self.php');
    foreach (DB::$USER->Get() as $user) {
      if (! isset($user->target_no)) {
	$user->target_no = 0;
      }

      if ($user->IsLive()) {
	RQ::GetTest()->talk_count[$user->id] = $user->id;
      }
    }
    if (DB::$ROOM->IsDay()) include('data/vote_talk_count.php'); //沈黙禁止
  }

  //出力
  private static function Output() {
    if (VoteTestFlag::VOTE) self::OutputVote();  //投票表示モード
    if (VoteTestFlag::CAST) self::OutputCast();  //配役情報表示モード
    if (VoteTestFlag::TALK) self::OutputTalk();  //発言表示モード
    if (VoteTestFlag::ROLE) self::OutputImage(); //画像表示モード

    HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play', true);
    GameHTML::OutputPlayer();
    GameHTML::OutputDead();
    RoleHTML::OutputAbility();
    self::ConvertTalk(); //発言変換テスト

    switch (DB::$ROOM->scene) {
    case RoomScene::DAY: //昼の投票テスト
      self::AggregateDay();
      break;

    case RoomScene::NIGHT: // 夜の投票テスト
      VoteNight::Aggregate();
      break;

    case RoomScene::AFTER: //勝敗判定表示
      self::OutputWinner();
      break;
    }

    include('data/vote_result.php');
    self::OutputResult();

    include('data/vote_debug.php'); //デバッグ情報
    HTML::OutputFooter(true);
  }

  //投票画面出力
  private static function OutputVote() {
    DevVote::Output('vote_test.php');
  }

  //配役情報出力
  private static function OutputCast() {
    Loader::LoadFile('chaos_config', 'vote_test_html_class');
    VoteTestHTML::OutputCast();
  }

  //発言出力
  private static function OutputTalk() {
    Loader::LoadFile('talk_class');

    RQ::Set(RequestDataLogRoom::ROLE, false);
    require_once('data/vote_talk.php');
    RQ::GetTest()->talk = array();
    foreach (VoteTestTalk::Get() as $stack) {
      $stack['scene'] = DB::$ROOM->scene;
      RQ::GetTest()->talk[] = new TalkParser($stack);
    }

    HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play');
    echo DB::$ROOM->GenerateCSS();
    HTML::OutputBodyHeader();
    GameHTML::OutputPlayer();
    if (DB::$SELF->id > 0) RoleHTML::OutputAbility();
    Talk::Output();
    HTML::OutputFooter(true);
  }

  //役職画像出力
  private static function OutputImage() {
    HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play', true);
    $list = VoteTestFlag::$role_list;
    if ($list['main']) {
      foreach (RoleDataManager::GetList() as $role) {
	ImageManager::Role()->OutputExists($role);
      }
    }
    if ($list['sub']) {
      foreach (RoleDataManager::GetList(true) as $role) {
	ImageManager::Role()->OutputExists($role);
      }
    }
    if ($list['result']) {
      foreach (RoleDataManager::GetList() as $role) {
	ImageManager::Role()->Output('result_' . $role);
      }
    }
    if ($list['weather']) {
      foreach (WeatherData::$list as $stack) {
	ImageManager::Role()->Output('prediction_weather_' . $stack['event']);
      }
    }
    HTML::OutputFooter(true);
  }

  //投票処理結果後表示
  private static function OutputResult() {
    Loader::LoadFile('vote_test_html_class');

    foreach (DB::$USER->Get() as $user) {
      unset($user->virtual_role);
      $user->live = $user->IsLive(true) ? UserLive::LIVE : UserLive::DEAD;
      $user->Reparse();
      $user->target_no = 0;
    }
    VoteTestHTML::OutputAbilityAction();

    DB::$ROOM->LoadEvent();
    DB::$USER->SetEvent();
    GameHTML::OutputDead();

    //DB::$ROOM->status = RoomStatus::FINISHED;
    GameHTML::OutputPlayer();
    RoleHTML::OutputAbility();
    foreach (DB::$USER->Get() as $user) {
      DB::$SELF = $user;
      RoleHTML::OutputAbility();
    }
  }

  //勝敗結果表示
  private static function OutputWinner() {
    Loader::LoadFile('winner_message');
    DB::$ROOM->Flag()->Off(RoomMode::LOG);
    DB::$ROOM->Flag()->Off(RoomMode::PERSONAL);
    Winner::Output();
    HTML::OutputFooter();
  }

  //発言変換テスト
  private static function ConvertTalk() {
    if (RQ::Get()->say == '') return;
    RoleTalk::Convert(RQ::Get()->say);
    Text::Escape(RQ::Get()->say, false);
    RoleTalk::Save(RQ::Get()->say, RoomScene::DAY, 0);
  }

  //投票集計処理 (昼)
  private static function AggregateDay() {
    $self_id = DB::$SELF->id;
    RQ::Get()->situation = VoteAction::VOTE_KILL;
    RQ::Get()->back_url  = '';
    RQ::Get()->hash      = Security::GetHash(DB::$ROOM->id);
    foreach (RQ::GetTest()->vote_target_day as $stack) {
      DB::LoadSelf($stack['id']);
      RQ::Set(RequestDataVote::TARGET, $stack[RequestDataVote::TARGET]);
      VoteDay::Execute();
    }

    $stack = array();
    foreach (ArrayFilter::Cast(VoteDay::Aggregate(), true) as $uname => $vote_data) {
      $vote_data['handle_name'] = DB::$USER->ByUname($uname)->handle_name;
      $vote_data['count']       = DB::$ROOM->revote_count + 1;
      $stack[] = $vote_data;
    }
    echo GameHTML::ParseVote($stack, DB::$ROOM->date);

    DB::$ROOM->date++;
    DB::$ROOM->Flag()->Off(RoomMode::LOG); //イベント確認用
    DB::$ROOM->SetScene(RoomScene::DAY); //イベント確認用
    //DB::$ROOM->SetScene(RoomScene::NIGHT);
    DB::LoadSelf($self_id);
  }

  //役職判定情報
  private static function OutputDistinguishMage() {
    $user   = new User();
    $filter = RoleLoader::Load('mage');
    foreach (RoleDataManager::Get() as $role => $name) {
      $user->Parse($role);
      Text::p($role, $filter->DistinguishMage($user));
    }
  }

  //闇鍋配役 (系列合計)
  private static function OutputChaosSumGroup() {
    $stack = array();
    foreach (ChaosConfig::$chaos_hyper_random_role_list as $role => $rate) {
      ArrayFilter::Add($stack, RoleDataManager::GetGroup($role), $rate);
    }
    Text::p($stack);
  }
}
