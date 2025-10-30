<?php
//-- HTML 生成クラス (OldLog 拡張) --//
final class OldLogHTML {
  //指定の部屋番号のログを生成する
  public static function Generate() {
    $base_title = ServerConfig::TITLE . OldLogMessage::TITLE;
    if (false === DB::$ROOM->IsFinished() || false === DB::$ROOM->IsAfterGame()) { //閲覧判定
      $url  = RQ::Fetch()->generate_index ? 'index.html' : 'old_log.php';
      $back = LinkHTML::Generate($url, Message::BACK);
      $str  = Text::Join(OldLogMessage::NOT_FINISHED, $back);
      HTML::OutputResult($base_title, $str);
    }

    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG)) { //キャッシュ取得判定
      $str = JinrouCacheManager::Get(JinrouCacheManager::LOG);
      if (true === isset($str)) {
	return $str;
      }
    }

    if (DB::$ROOM->IsOn(RoomMode::WATCH)) { //観戦モード判定
      DB::$ROOM->SetScene(RoomScene::DAY);
      DB::$ROOM->SetStatus(RoomStatus::PLAYING);
    }

    if (RQ::Fetch()->auto_play) { //自動再生モード判定
      if (false === RQ::Get('reverse_log') && RQ::Get('time') && DB::$ROOM->IsOn(RoomMode::WATCH)) {
	DB::$ROOM->Flag()->Set(RoomMode::AUTO_PLAY, true);
	AutoPlayTalk::InitStack();
      } else {
	RQ::Set(RoomMode::AUTO_PLAY, false);
      }
    }

    $list = [
      'game_option' => DB::$ROOM->game_option->row,
      'option_role' => DB::$ROOM->option_role->row,
      'max_user'    => 0
    ];
    RoomOptionLoader::Load($list);

    $title = sprintf('[%d%s] %s - %s',
      DB::$ROOM->id, GameMessage::ROOM_NUMBER_FOOTER, DB::$ROOM->name, $base_title
    );

    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str = AutoPlayTalk::GenerateHeader($title);
    } elseif (RQ::Fetch()->reverse_log && RQ::Fetch()->scroll > 0) {
      $str = self::GenerateScrollHeader($title);
    } else {
      $str = HTML::GenerateHeader($title, 'old_log', true);
    }
    $str .= Text::Join(
      LinkHTML::Generate(URL::GetHeaderDB('old_log'), Message::BACK),
      RoomHTML::GenerateLogTitle(), RoomOptionLoader::GenerateImage(),
      Text::LineFeed(LinkHTML::Generate('#beforegame', OldLogMessage::BEFORE))
    );
    for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
      $str .= Text::LineFeed(LinkHTML::Generate('#date' . $i, $i));
    }
    $str .= LinkHTML::Generate('#aftergame', OldLogMessage::AFTER) . Message::SPACER;
    $str .= Text::LineFeed(RQ::Fetch()->GetURL());
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str .= Text::Format('<a href="#game_top" onClick="start_auto_play();">%s</a>', '開始');
    }
    $str .= GameHTML::GeneratePlayer();
    if (RQ::Fetch()->role_list) {
      $str .= self::GenerateRoleLink();
    }
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

  //過去ログ一覧生成
  public static function GenerateList($page) {
    //村数の確認
    $room_count = RoomLoaderDB::CountFinished();
    if ($room_count < 1) {
      $title = ServerConfig::TITLE . OldLogMessage::TITLE;
      $back  = LinkHTML::Generate('./', Message::BACK);
      HTML::OutputResult($title, Text::Join(OldLogMessage::NO_LOG, $back));
    }

    $cache_flag = false; //キャッシュ有効判定
    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG_LIST)) {
      $cache_flag = self::EnableCache();
      if (true === $cache_flag) {
	$str = JinrouCacheManager::Get(JinrouCacheManager::LOG_LIST);
	if (true === isset($str)) {
	  return $str;
	}
      }
    }

    //ページリンクデータの生成
    if (empty(RQ::Fetch()->reverse)) {
      $is_reverse = OldLogConfig::REVERSE;
    } else {
      $is_reverse = Switcher::IsOn(RQ::Fetch()->reverse);
    }

    if (RQ::Fetch()->generate_index) {
      $max = RQ::Fetch()->max_room_no;
      if (is_int($max) && Number::InRange($max, 0, $room_count)) {
	$room_count = $max;
      }
      $builder = new PageLinkBuilder('index', RQ::Fetch()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->url = '<a href="index';
    } else {
      $builder = new PageLinkBuilder('old_log', RQ::Fetch()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->AddOption('reverse', Switcher::Get($is_reverse));
      $builder->AddOption('watch',   Switcher::Get(RQ::Fetch()->watch));
      foreach (['name', 'room_name', 'winner', 'role', 'game_type'] as $option) {
	if (RQ::Get($option)) {
	  $builder->AddOption($option, RQ::Get($option));
	}
      }

      if (URL::ExistsDB()) {
	$builder->AddOption(RequestDataGame::DB, RQ::Fetch()->db_no);
      }
    }

    $str = self::GenerateListHeader($builder);

    //全部表示の場合、一ページで全部表示する。それ以外は設定した数毎に表示
    $format = self::GetList();
    $current_time = Time::Get();
    foreach (RoomLoaderDB::GetFinished($is_reverse) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));

      $vanish = DateBorder::On(0) ? ' vanish' : ''; //廃村判定
      if (RQ::Fetch()->generate_index) {
	$base_url = RQ::Fetch()->prefix . DB::$ROOM->id . '.html';
	$view_url = '';
	$login    = '';
	$log_link = sprintf('(<a href="%s%dr.html">%s</a>)',
	  RQ::Fetch()->prefix, DB::$ROOM->id, Message::LOG_REVERSE
	);
      } else {
	$base_url = URL::GetRoom('old_log');;
	if (URL::ExistsDB()) {
	  $view_url  = RQ::Fetch()->ToURL(RequestDataGame::DB, true);
	  $base_url .= $view_url;
	} else {
	  $view_url  = '';
	}
	if (RQ::Fetch()->watch) {
	  $base_url .= URL::AddSwitch(RequestDataLogRoom::WATCH);
	}

	if ($current_time - strtotime(DB::$ROOM->finish_datetime ?? 0) > RoomConfig::KEEP_SESSION) {
	  $login = '';
	} else {
	  $login = Text::LineFeed(LinkHTML::Generate(URL::GetRoom('login'), OldLogMessage::LOGIN));
	}

	if (RQ::Fetch()->watch) {
	  $log_link  = self::GenerateWatchLogLink($base_url, '(', '', ' )');
	} else {
	  $log_link  = LinkHTML::GenerateLog($base_url, true, '(', '', ' )');

	  $url       = $base_url . URL::AddSwitch(RequestDataLogRoom::ROLE);
	  $header    = Text::LF . OldLogMessage::ADD_ROLE . ' (';
	  $log_link .= LinkHTML::GenerateLog($url, false, $header, $vanish, ' )');
	}
      }

      if (DB::$ROOM->establish_datetime == '') {
	$establish = '';
      } else {
	$establish = Time::ConvertTimeStamp(DB::$ROOM->establish_datetime);
      }

      $list = [
	'game_option' => DB::$ROOM->game_option,
	'option_role' => DB::$ROOM->option_role,
	'max_user'    => DB::$ROOM->max_user
      ];
      RoomOptionLoader::Load($list);
      RoomOptionLoader::SetStack();

      $str .= Text::Format($format,
	URL::GetRoom('game_view'), $view_url,
	DB::$ROOM->id, $vanish, $base_url, DB::$ROOM->GenerateName(),
	DB::$ROOM->user_count, ImageManager::Room()->GenerateMaxUser(DB::$ROOM->max_user),
	DB::$ROOM->date,
	RQ::Fetch()->watch ? '-' : ImageManager::Winner()->Generate(DB::$ROOM->winner),
	DB::$ROOM->GenerateComment(), $establish, $vanish,
	$login, $log_link, RoomOptionLoader::GenerateImage()
      );
    }

    $str .= Text::LineFeed(self::GetListFooter());
    if (true === $cache_flag) {
      JinrouCacheManager::Store($str);
    }
    return $str;
  }

  //指定の部屋番号のログを出力する
  public static function Output() {
    echo self::Generate();
  }

  //過去ログ一覧表示
  public static function OutputList($page) {
    echo self::GenerateList($page);
  }

  //自動スクロール設定生成
  private static function GenerateScrollHeader($title) {
    $format = <<<EOF
var distance = %d;
var timeout  = %d;
var y = 0;
EOF;

    if (RQ::Fetch()->scroll_time > 0) {
      $timeout = RQ::Fetch()->scroll_time;
    } else {
      $timeout = 1;
    }
    $str  = HTML::GenerateHeader($title, 'old_log');
    $str .= HTML::LoadJavaScript('auto_scroll');
    $str .= HTML::GenerateJavaScriptHeader();
    $str .= Text::Format($format, RQ::Fetch()->scroll, $timeout);
    $str .= HTML::GenerateJavaScriptFooter();
    $str .= HTML::GenerateBodyHeader(null, 'auto_scroll();');

    return $str;
  }

  //通常ログ出力
  private static function GenerateLog() {
    if (true === RQ::Fetch()->reverse_log) {
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

  //ログへのリンク生成 (観戦モード用)
  private static function GenerateWatchLogLink($url, $header = '', $css = '', $footer = '') {
    $str = sprintf(self::GetWolfSiteLogLink(), $header,
      $url, $css, Message::LOG_NORMAL,
      $url, $css, Message::LOG_REVERSE,
      $url, $css, Message::LOG_WOLF,
      $url, $css, Message::LOG_WOLF_REVERSE
    );
    return $str . $footer;
  }

  //霊界ログ出力
  private static function GenerateHeavenLog() {
    $str = '';
    if (true === RQ::Fetch()->reverse_log) {
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
      if (true === RQ::Fetch()->reverse_log) {
	DB::$USER->ResetPlayer(); //player 復元処理
      }
      break;

    case RoomScene::HEAVEN_ONLY:
      if (true === RQ::Fetch()->reverse_log && $date != 1) {
	$table_class = RoomScene::DAY; //2日目以降は昼から
      } else {
	$table_class = RoomScene::NIGHT;
      }
      break;

    default:
      $border_game_flag = true;
      if (true === RQ::Fetch()->reverse_log && $date != 1) {
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
    if (true === RQ::Fetch()->reverse_log) {
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

    if (true === $border_game_flag && true === RQ::Fetch()->reverse_log) {
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
    if (true === RQ::Fetch()->reverse_log) {
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

  //キャッシュ有効判定
  private static function EnableCache() {
    foreach (RQ::Fetch() as $key => $value) { //何か値がセットされていたら無効
      switch ($key) {
      case 'page':
	if ($value != 1) {
	  return false;
	}
	break;

      default:
	if (false === empty($value)) {
	  return false;
	}
	break;
      }
    }
    return true;
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

  //一覧ヘッダー生成
  private static function GenerateListHeader(PageLinkBuilder $builder) {
    if (RQ::Fetch()->generate_index) {
      $back = LinkHTML::Generate('../', Message::BACK);
      $url  = '../';
    } else {
      $back = LinkHTML::Generate('./', Message::BACK);
      $url  = '';
    }
    $str = Text::Format(self::GetListHeader(),
      $back, $url, OldLogMessage::TITLE, OldLogMessage::TITLE, $builder->Generate(),
      OldLogMessage::NUMBER, OldLogMessage::NAME, OldLogMessage::COUNT,
      OldLogMessage::DATE, OldLogMessage::WIN
    );

    $title = ServerConfig::TITLE . OldLogMessage::TITLE;
    return HTML::GenerateHeader($title, 'old_log_list', true) . $str;
  }

  //一覧ヘッダータグ
  private static function GetListHeader() {
    return <<<EOF
<p>%s</p>
<img src="%simg/title/old_log.jpg" alt="%s" title="%s"><br>
<div>
<table>
<caption>%s</caption>
<thead>
<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>
</thead>
<tbody>
EOF;
  }

  //一覧個別村情報タグ
  private static function GetList() {
    return <<<EOF
<tr>
<td class="number" rowspan="3"><a href="%s%s">%d</a></td>
<td class="title%s"><a href="%s">%s</a></td>
<td class="upper">%d %s</td>
<td class="upper">%d</td>
<td class="side">%s</td>
</tr>
<tr class="list middle">
<td class="comment side">%s</td>
<td class="time comment" colspan="3">%s</td>
</tr>
<tr class="lower list">
<td class="comment%s">
%s%s
</td>
<td colspan="3">%s</td>
</tr>
EOF;
  }

  //ログへのリンクタグ (人狼視点モード用)
  private static function GetWolfSiteLogLink() {
    return <<<EOF
%s <a target="_top" href="%s"%s>%s</a>
<a target="_top" href="%s&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&wolf_sight=on"%s >%s</a>
<a target="_top" href="%s&wolf_sight=on&reverse_log=on"%s>%s</a>
EOF;
  }

  //リンクタグ (スイッチ型)
  private static function GetSwitchLink() {
    return '[<a href="%s" class="option-%s">%s</a>]';
  }

  //一覧フッタータグ
  private static function GetListFooter() {
    return <<<EOF
</tbody>
</table>
</div>
EOF;
  }
}
