<?php
//-- HTML 生成クラス (OldLog 拡張) --//
final class OldLogHTML {
  //指定の部屋番号のログを生成する
  public static function Generate() {
    $base_title = ServerConfig::TITLE . OldLogMessage::TITLE;

    //-- 閲覧可能判定 --//
    if (false === DB::$ROOM->IsFinished() || false === DB::$ROOM->IsAfterGame()) {
      $url  = RQ::Fetch()->generate_index ? 'index.html' : 'old_log.php';
      $back = LinkHTML::Generate($url, Message::BACK);
      $str  = Text::Join(OldLogMessage::NOT_FINISHED, $back);
      HTML::OutputResult($base_title, $str);
    }

    //-- キャッシュ存在判定 --//
    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG)) {
      $str = JinrouCacheManager::Get(JinrouCacheManager::LOG);
      if (true === isset($str)) {
	return $str;
      }
    }

    //-- 観戦モード判定 --//
    if (DB::$ROOM->IsOn(RoomMode::WATCH)) {
      DB::$ROOM->SetScene(RoomScene::DAY);
      DB::$ROOM->SetStatus(RoomStatus::PLAYING);
    }

    //-- 自動再生モード判定 --//
    if (RQ::Enable(RoomMode::AUTO_PLAY)) {
      if (RQ::Disable(RequestDataLogRoom::REVERSE_LOG) &&
	  RQ::Get(RequestDataLogRoom::TIME) &&
	  DB::$ROOM->IsOn(RoomMode::WATCH)) {
	DB::$ROOM->Flag()->Set(RoomMode::AUTO_PLAY, true);
	AutoPlayTalk::InitStack();
      } else {
	RQ::Set(RoomMode::AUTO_PLAY, false);
      }
    }

    //-- 村情報ロード --//
    $list = [
      'game_option' => DB::$ROOM->game_option->row,
      'option_role' => DB::$ROOM->option_role->row,
      'max_user'    => 0
    ];
    RoomOptionLoader::Load($list);

    //-- タイトル --//
    $title = sprintf('[%d%s] %s - %s',
      DB::$ROOM->id, GameMessage::ROOM_NUMBER_FOOTER, DB::$ROOM->name, $base_title
    );

    //-- モード別ヘッダ --//
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str = AutoPlayTalk::GenerateHeader($title);
    } elseif (RQ::Fetch()->reverse_log && RQ::Get(RequestDataLogRoom::SCROLL) > 0) {
      $str = self::GenerateScrollHeader($title);
    } else {
      $str = HTML::GenerateHeader($title, 'old_log', true);
    }
    $str .= Text::Join(
      LinkHTML::Generate(URL::GetHeaderDB('old_log'), Message::BACK),
      RoomHTML::GenerateLogTitle(), RoomOptionLoader::GenerateImage(),
      Text::LineFeed(LinkHTML::Generate('#beforegame', OldLogMessage::BEFORE))
    );

    //-- 日付ページ内リンク・スイッチリンク --/
    for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
      $str .= Text::LineFeed(LinkHTML::Generate('#date' . $i, $i));
    }
    $str .= LinkHTML::Generate('#aftergame', OldLogMessage::AFTER) . Message::SPACER;
    $str .= Text::LineFeed(RQ::Fetch()->GetURL());
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str .= sprintf('<a href="#game_top" onClick="start_auto_play();">%s</a>', '開始');
      $str .= ' ' . HTML::GenerateSpan('終了', null, 'auto_play_end');
    } elseif (RQ::Get(RequestDataLogRoom::AUTO_PLAY)) {
      $str .= Text::BRLF . OldLogMessage::AUTO_PLAY_ON . ' ';
      $str .= RQ::Fetch()->GetAutoPlayURL();
    } elseif (RQ::Get(RequestDataLogRoom::SCROLL_ON)) {
      $str .= Text::BRLF . OldLogMessage::SCROLL_ON . ' ';
      $str .= RQ::Fetch()->GetScrollURL();
    }

    //-- 参加者一覧 --//
    $str .= GameHTML::GeneratePlayer();
    if (RQ::Get(RequestDataLogRoom::ROLE_LIST)) {
      $str .= self::GenerateRoleLink();
    }

    //-- ログ本体 --//
    $str .= RQ::Fetch()->heaven_only ? self::GenerateHeavenLog() : self::GenerateLog();
    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG)) {
      JinrouCacheManager::Store($str);
    }
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str .= AutoPlayTalk::GenerateFooter();
    }
    return $str;
  }

  //ログへのリンク生成
  public static function GenerateSwitchLink($url, $str, $css) {
    return sprintf(self::getSwitchLink(), $url, $css, $str);
  }

  //指定の部屋番号のログを出力する
  public static function Output() {
    echo self::Generate();
  }

  //自動スクロール設定生成
  private static function GenerateScrollHeader($title) {
    $format = <<<EOF
var distance = %d;
var timeout  = %d;
var y = 0;
EOF;

    if (RQ::Get(RequestDataLogRoom::SCROLL_TIME) > 0) {
      $timeout = RQ::Get(RequestDataLogRoom::SCROLL_TIME);
    } else {
      $timeout = 1;
    }
    $str  = HTML::GenerateHeader($title, 'old_log');
    $str .= JavaScriptHTML::Load('auto_scroll');
    $str .= JavaScriptHTML::GenerateHeader();
    $str .= Text::Format($format, RQ::Get(RequestDataLogRoom::SCROLL), $timeout);
    $str .= JavaScriptHTML::GenerateFooter();
    $str .= HTML::GenerateBodyHeader(null, 'auto_scroll();');

    return $str;
  }

  //通常ログ出力
  private static function GenerateLog() {
    if (RQ::Enable('reverse_log')) {
      $str = self::GenerateTalk(0, RoomScene::BEFORE);
      if (DB::$ROOM->IsOption('open_day')) {
	$str .= self::GenerateTalk(0, RoomScene::DAY);
      }
      for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
	$str .= self::GenerateTalk($i, '');
      }
      //シーン切り替えの後に勝敗を出力する
      $after = self::GenerateTalk(DB::$ROOM->last_date, RoomScene::AFTER);
      $str  .= Winner::Generate() . $after;
    } else {
      $str = self::GenerateTalk(DB::$ROOM->last_date, RoomScene::AFTER) . Winner::Generate();
      for ($i = DB::$ROOM->last_date; $i > 0; $i--) {
	$str .= self::GenerateTalk($i, '');
      }
      $str .= self::GenerateTalk(0, RoomScene::BEFORE);
    }
    return $str;
  }

  //霊界ログ出力
  private static function GenerateHeavenLog() {
    $str = '';
    if (RQ::Enable('reverse_log')) {
      for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
	$str .= self::GenerateTalk($i, RoomScene::HEAVEN_ONLY);
      }
    } else {
      for ($i = DB::$ROOM->last_date; $i > 0; $i--) {
	$str .= self::GenerateTalk($i, RoomScene::HEAVEN_ONLY);
      }
    }
    return $str;
  }

  //指定の日付の会話ログを生成
  private static function GenerateTalk($date, $scene) {
    $border_game_flag = false;
    switch ($scene) { //シーンに合わせたデータをセット
    case RoomScene::BEFORE:
      $table_class = $scene;
      if (DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) {
	DB::$USER->ResetRoleList();
	DB::$ROOM->ResetEvent();
      }
      if (false === RQ::Fetch()->reverse_log) {
	DB::$USER->ResetPlayer(); //player 復元処理
      }
      break;

    case RoomScene::AFTER:
      $table_class = $scene;
      if (DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) {
	DB::$USER->ResetRoleList();
	DB::$ROOM->ResetEvent();
      }
      if (RQ::Enable('reverse_log')) {
	DB::$USER->ResetPlayer(); //player 復元処理
      }
      break;

    case RoomScene::HEAVEN_ONLY:
      if (RQ::Enable('reverse_log') && $date != 1) {
	$table_class = RoomScene::DAY; //2日目以降は昼から
      } else {
	$table_class = RoomScene::NIGHT;
      }
      break;

    default:
      $border_game_flag = true;
      if (RQ::Enable('reverse_log') && $date != 1) {
	$table_class = RoomScene::DAY; //2日目以降は昼から
      } else {
	$table_class = RoomScene::NIGHT;
      }

      if (DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) {
	DB::$USER->ResetRoleList();
	DB::$USER->SetEvent(true);
      }
      break;
    }

    //出力
    $str = '';
    if (true === $border_game_flag && false === RQ::Fetch()->reverse_log) {
      DB::$ROOM->SetDate($date + 1);
      DB::$ROOM->SetScene(RoomScene::DAY);
      $str .= self::GenerateLastWords() . self::GenerateDead(); //死亡者を出力
    }

    DB::$ROOM->SetDate($date);
    DB::$ROOM->SetScene($table_class);
    if ($scene != RoomScene::HEAVEN_ONLY) {
      DB::$ROOM->SetWeather();
    }
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      AutoPlayTalk::InitScene();
    }

    $id = DB::$ROOM->IsPlaying() ? 'date' . DB::$ROOM->date : DB::$ROOM->scene;
    $builder = new TalkBuilder('talk ' . $table_class, $id);
    if (ServerConfig::DEBUG_MODE) {
      Talk::SetBuilder($builder); //デバッグ発言出力用
    }
    if (RQ::Enable('reverse_log')) {
      $builder->GenerateTimeStamp();
    }

    foreach (TalkDB::GetLog($date, $scene) as $talk) {
      switch ($talk->scene) {
      case RoomScene::DAY:
      case RoomScene::NIGHT:
	if ($talk->scene == DB::$ROOM->scene || $talk->location == TalkLocation::DUMMY_BOY) {
	  break;
	}

	$str .= $builder->Refresh() . self::GenerateSceneChange($date);
	DB::$ROOM->SetScene($talk->scene);
	$id = 'date' . DB::$ROOM->date . '_' . DB::$ROOM->scene;
	$builder->Begin('talk ' . $talk->scene, $id);
	if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
	  AutoPlayTalk::SetScene(true);
	}
	break;
      }
      $builder->Generate($talk); //会話生成
    }

    if (false === RQ::Fetch()->reverse_log) {
      $builder->GenerateTimeStamp();
    }
    $str .= $builder->Refresh();

    if (true === $border_game_flag && RQ::Enable('reverse_log')) {
      //突然死で勝敗が決定したケース
      if ($date == DB::$ROOM->last_date && DB::$ROOM->IsDay()) {
	$str .= self::GenerateVote();
	DB::$ROOM->SetScene(RoomScene::NIGHT);
	$str .= self::GenerateDead();
      }

      DB::$ROOM->SetDate($date + 1);
      DB::$ROOM->SetScene(RoomScene::DAY);
      $str .= self::GenerateDead() . self::GenerateLastWords(); //遺言を出力
    }
    return $str;
  }

  //シーン切り替え処理
  private static function GenerateSceneChange($date) {
    $str = '';
    if (RQ::Fetch()->heaven_only) {
      return $str;
    }

    DB::$ROOM->SetDate($date);
    if (RQ::Enable('reverse_log')) {
      DB::$ROOM->SetScene(RoomScene::NIGHT);
      $str .= self::GenerateVote() . self::GenerateDead();
    } else {
      $str .= self::GenerateDead() . self::GenerateVote();
    }
    return $str;
  }

  //役職リンク生成
  private static function GenerateRoleLink() {
    $stack = [];
    foreach (DB::$USER->GetRole() as $role => $list) {
      $stack[] = $role;
    }

    $str_stack  = [];
    $role_stack = [];
    foreach (array_intersect(RoleDataManager::GetList(), $stack) as $role) {
      if (false === isset($camp)) {
	$camp = RoleDataManager::GetCamp($role);
      }
      if ($camp != RoleDataManager::GetCamp($role) || count($role_stack) > 9) {
	$str_stack[] = ArrayFilter::Concat($role_stack, ' / ');
	$role_stack = [];
	$camp = RoleDataManager::GetCamp($role);
      }
      $role_stack[] = RoleDataHTML::GenerateMain($role) . DB::$USER->CountRole($role);
    }
    $str_stack[] = ArrayFilter::Concat($role_stack, ' / ');

    $role_stack = [];
    foreach (array_intersect(RoleDataManager::GetList(true), $stack) as $role) {
      if (count($role_stack) > 9) {
	$str_stack[] = ArrayFilter::Concat($role_stack, ' / ');
	$role_stack = [];
      }
      $role_stack[] = RoleDataHTML::GenerateSub($role) . DB::$USER->CountRole($role);
    }
    $str_stack[] = ArrayFilter::Concat($role_stack, ' / ');
    return ArrayFilter::Concat($str_stack, Text::BRLF);
  }

  //投票結果生成
  private static function GenerateVote() {
    $str = GameHTML::GenerateVote();
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      return AutoPlayTalk::Hide($str, 'vote');
    } else {
      return $str;
    }
  }

  //死亡メッセージ生成
  private static function GenerateDead() {
    $str = GameHTML::GenerateDead();
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      return AutoPlayTalk::Hide($str, 'dead');
    } else {
      return $str;
    }
  }

  //遺言生成
  private static function GenerateLastWords() {
    $str = GameHTML::GenerateLastWords();
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      return AutoPlayTalk::Hide($str, 'lastwords');
    } else {
      return $str;
    }
  }

  //リンクタグ (スイッチ型)
  private static function GetSwitchLink() {
    return '[<a href="%s" class="option-%s">%s</a>]';
  }
}
