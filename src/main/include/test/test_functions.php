<?php
//-- テスト村生成クラス --//
class DevRoom {
  //テスト村データ初期化
  public static function Initialize($list = []) {
    //初期村データを生成
    RQ::Set(RequestDataGame::ID, 1);
    RQ::Set('vote_times', 1);
    RQ::Set(RequestDataLogRoom::REVERSE, null);
    $base_list = [
      'id'		=> RQ::Get()->room_no,
      'comment'		=> '',
      'date'		=> 0,
      'scene'		=> RoomScene::BEFORE,
      'status'		=> RoomStatus::WAITING,
      'game_option'	=> 'dummy_boy real_time:6:4 wish_role',
      'option_role'	=> '',
      'vote_count'	=> 1
    ];

    RQ::InitTestRoom();
    RQ::GetTest()->test_room = array_merge($base_list, $list);
    RQ::GetTest()->event           = [];
    RQ::GetTest()->result_ability  = [];
    RQ::GetTest()->result_dead     = [];
    RQ::GetTest()->system_message  = [];
  }

  //村データロード
  public static function Load() {
    DB::LoadRoom();
    DB::$ROOM->SetFlag(RoomMode::TEST, RoomMode::LOG);
    DB::$ROOM->SetScene(RoomScene::BEFORE);
    DB::$ROOM->ResetRevoteCount();
    if (DB::$ROOM->Stack()->IsEmpty('vote')) {
      DB::$ROOM->Stack()->Init('vote');
    }
  }

  //イベント情報取得
  public static function GetEvent() {
    $stack = [];
    foreach (RQ::GetTest()->system_message as $date => $date_list) {
      //Text::p($date_list, "◆Event [{$date}]");
      if (false === DB::$ROOM->IsDate($date)) {
	continue;
      }

      foreach ($date_list as $type => $type_list) {
	switch ($type) {
	case EventType::WEATHER:
	case EventType::EVENT:
	case EventType::VOTE_DUEL:
	case EventType::SAME_FACE:
	case DeadReason::BLIND_VOTE:
	  foreach ($type_list as $event) {
	    $stack[] = ['type' => $type, 'message' => $event];
	  }
	  break;
	}
      }
    }
    //Text::p($stack, '◆Event');
    return $stack;
  }

  //能力発動結果取得
  public static function GetAbility($date, $action, $limit) {
    $stack = RQ::GetTest()->result_ability;
    $stack = ArrayFilter::GetList(ArrayFilter::GetList($stack, $date), $action);
    if (true === $limit) {
      $limit_stack = [];
      foreach ($stack as $list) {
	if ($list['user_no'] == DB::$SELF->id) {
	  $limit_stack[] = $list;
	}
      }
      $stack = $limit_stack;
    }
    return $stack;
  }

  //配役テスト
  public static function Cast(stdClass $stack) {
    RQ::SetTestRoom('game_option', ArrayFilter::Concat($stack->game_option));
    RQ::SetTestRoom('option_role', ArrayFilter::Concat($stack->option_role));

    DB::LoadRoom();
    DB::$ROOM->LoadOption();
    //Text::p(DB::$ROOM, '◆Room');

    $user_count = RQ::Get()->user_count;
    $try_count  = RQ::Get()->try_count;
    $format     = '%0' . strlen($try_count) . 'd回目: ';
    for ($i = 1; $i <= $try_count; $i++) {
      printf($format, $i);
      $role_list = Cast::Get($user_count);
      if ($role_list == '') {
	break;
      }
      Text::p(Cast::GenerateMessage(array_count_values($role_list), true));
    }
  }
}

//-- テストユーザ生成クラス --//
class DevUser {
  // ユーザのアイコンカラーリスト
  static $icon_color_list = [
    '#DDDDDD', '#999999', '#FFD700', '#FF9900', '#FF0000',
    '#99CCFF', '#0066FF', '#00EE00', '#CC00CC', '#FF9999'
  ];

  // ユーザの初期データ
  static $user_list = [
     1 => ['uname'         => GM::DUMMY_BOY,
	   'handle_name'   => Message::DUMMY_BOY,
	   'icon_filename' => '../img/dummy_boy_user_icon.jpg',
	   'color'         => '#000000'],
     2 => ['uname'         => 'light_gray',
	   'handle_name'   => '明灰'],
     3 => ['uname'         => 'dark_gray',
	   'handle_name'   => '暗灰'],
     4 => ['uname'         => 'yellow',
	   'handle_name'   => '黄色'],
     5 => ['uname'         => 'orange',
	   'handle_name'   => 'オレンジ'],
     6 => ['uname'         => 'red',
	   'handle_name'   => '赤'],
     7 => ['uname'         => 'light_blue',
	   'handle_name'   => '水色'],
     8 => ['uname'         => 'blue',
	   'handle_name'   => '青'],
     9 => ['uname'         => 'green',
	   'handle_name'   => '緑'],
    10 => ['uname'         => 'purple',
	   'handle_name'   => '紫'],
    11 => ['uname'         => 'cherry',
	   'handle_name'   => 'さくら'],
    12 => ['uname'         => 'white',
	   'handle_name'   => '白'],
    13 => ['uname'         => 'black',
	   'handle_name'   => '黒'],
    14 => ['uname'         => 'gold',
	   'handle_name'   => '金'],
    15 => ['uname'         => 'frame',
	   'handle_name'   => '炎'],
    16 => ['uname'         => 'crimson',
	   'handle_name'   => '紅'],
    17 => ['uname'         => 'ice',
	   'handle_name'   => '氷'],
    18 => ['uname'         => 'deep_blue',
	   'handle_name'   => '蒼'],
    19 => ['uname'         => 'emerald',
	   'handle_name'   => '翠'],
    20 => ['uname'         => 'rose',
	   'handle_name'   => '薔薇'],
    21 => ['uname'         => 'peach',
	   'handle_name'   => '桃'],
    22 => ['uname'         => 'gust',
	   'handle_name'   => '霧'],
    23 => ['uname'         => 'cloud',
	   'handle_name'   => '雲'],
    24 => ['uname'         => 'moon',
	   'handle_name'   => '月'],
    25 => ['uname'         => 'sun',
	   'handle_name'   => '太陽'],
    26 => ['uname'         => 'scarlet',
	   'handle_name'   => '緋色'],
    27 => ['uname'         => 'sky',
	   'handle_name'   => '空'],
    28 => ['uname'         => 'sea',
	   'handle_name'   => '海'],
    29 => ['uname'         => 'forest',
	   'handle_name'   => '森'],
    30 => ['uname'         => 'violet',
	   'handle_name'   => '菫'],
  ];

  //ユーザデータ初期化
  public static function Initialize($count, $role_list = []) {
    RQ::GetTest()->test_users = [];
    for ($id = 1; $id <= $count; $id++) {
      RQ::GetTest()->test_users[$id] = new User(isset($role_list[$id]) ? $role_list[$id] : null);
    }

    foreach (self::$user_list as $id => $list) {
      if ($id > $count) {
	break;
      }

      foreach ($list as $key => $value) {
	RQ::GetTest()->test_users[$id]->id   = $id;
	RQ::GetTest()->test_users[$id]->$key = $value;
      }
    }
  }

  //ユーザデータ補完
  public static function Complement($scene = RoomScene::BEFORE) {
    foreach (RQ::GetTest()->test_users as $id => $user) {
      $user->room_no = RQ::Get()->room_no;
      $user->role_id = $id;
      if (false === isset($user->live)) {
	$user->live = UserLive::LIVE;
      }
      if (false === isset($user->sex)) {
	$user->sex = (Number::Even($id) ? Sex::FEMALE : Sex::MALE);
      }
      if (false === isset($user->profile)) {
	$user->profile = $id;
      }
      $user->last_load_scene = $scene;
      if ($id > 1) {
	$user->color         = self::$icon_color_list[($id - 2) % 10];
	$user->icon_filename = sprintf('%03d.gif', ($id - 2) % 10 + 1);
      }
    }
  }

  //ユーザ情報をロード
  public static function Load() {
    DB::LoadUser();
    DB::LoadDummyBoy();
    if (DB::$ROOM->IsBeforeGame()) {
      foreach (DB::$USER->Get() as $user) {
	if (false === isset($user->vote_type)) {
	  $user->vote_type = 'GAME_START';
	}
      }
    }

    if (DB::$ROOM->IsDate(1)) { //初日は死亡者ゼロ
      foreach (DB::$USER->Get() as $user) {
	if ($user->IsDead()) {
	  $user->live = UserLive::LIVE;
	}
      }
    }
  }
}

//-- テスト投票処理クラス --//
class DevVote {
  //出力
  public static function Output($url) {
    self::Load($url);
    RQ::Get()->vote ? self::Execute() : self::OutputForm($url); //投票処理
    DB::LoadDummyBoy();
    GameHTML::OutputPlayer();
    HTML::OutputFooter(true);
  }

  //ロード
  private static function Load($url) {
    RQ::LoadFile('game_vote');

    $stack = new Request_game_vote();
    RQ::Set('token', Security::GetToken(DB::$ROOM->id));
    RQ::Set(RequestDataVote::ON,         $stack->vote);
    RQ::Set(RequestDataVote::TARGET,     $stack->target_no);
    RQ::Set(RequestDataVote::SITUATION,  $stack->situation);
    RQ::Set(RequestDataVote::ADD_ACTION, $stack->add_action);
    RQ::Set(RequestDataVote::BACK_URL,   HTML::GenerateLink($url, Message::BACK));
  }

  //実行
  private static function Execute() {
    if (DB::$SELF->IsDead()) { //死者の霊界投票処理
      HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play', true);
      if (DB::$SELF->IsDummyBoy()) {
	switch (RQ::Get()->situation) {
	case VoteAction::FORCE_SUDDEN_DEATH:
	  return VoteForceSuddenDeath::Execute();

	case VoteAction::RESET_TIME:
	  return VoteResetTime::Execute();

	default:
	  return VoteHeaven::Execute();
	}
      } else {
	VoteHeaven::Execute();
      }
    } elseif (RQ::Get()->target_no == 0) { //空投票検出
      VoteHTML::OutputError(VoteMessage::NO_TARGET_TITLE, VoteMessage::NO_TARGET);
    } elseif (DB::$ROOM->IsDay()) { //昼の処刑投票処理
      HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play', true);
      VoteDay::Execute();
    } elseif (DB::$ROOM->IsNight()) { //夜の投票処理
      HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play', true);
      VoteNight::Execute();
    } else { //ここに来たらロジックエラー
      VoteHTML::OutputError(VoteMessage::INVALID_COMMAND, VoteMessage::NO_TARGET);
    }
  }

  //フォーム出力
  private static function OutputForm($url) {
    RQ::Set('post_url', $url);
    DB::$SELF->last_load_scene = DB::$ROOM->scene;

    if (DB::$SELF->IsDead()) {
      DB::$SELF->IsDummyBoy() ? VoteHTML::OutputDummyBoy() : VoteHTML::OutputHeaven();
    } else {
      switch (DB::$ROOM->scene) {
      case RoomScene::BEFORE:
	VoteHTML::OutputBeforeGame();
	break;

      case RoomScene::DAY:
	VoteHTML::OutputDay();
	break;

      case RoomScene::NIGHT:
	VoteHTML::OutputNight();
	break;

      default: //ここに来たらロジックエラー
	VoteHTML::OutputError(VoteMessage::INVALID_SCENE);
	break;
      }
    }
  }
}
