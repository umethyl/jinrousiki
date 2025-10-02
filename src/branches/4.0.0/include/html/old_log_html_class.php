<?php
//-- HTML 生成クラス (OldLog 拡張) --//
class OldLogHTML {
  //指定の部屋番号のログを生成する
  public static function Generate() {
    $base_title = ServerConfig::TITLE . OldLogMessage::TITLE;
    if (false === DB::$ROOM->IsFinished() || false === DB::$ROOM->IsAfterGame()) { //閲覧判定
      $url  = RQ::Get()->generate_index ? 'index.html' : 'old_log.php';
      $back = HTML::GenerateLink($url, Message::BACK);
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
      DB::$ROOM->status = RoomStatus::PLAYING;
      DB::$ROOM->SetScene(RoomScene::DAY);
    }

    if (RQ::Get()->auto_play) { //自動再生モード判定
      if (false === RQ::Get()->reverse_log && RQ::Get()->time && DB::$ROOM->IsOn(RoomMode::WATCH)) {
	DB::$ROOM->Flag()->Set(RoomMode::AUTO_PLAY, true);
	Loader::LoadFile('auto_play_talk_class');
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
    RoomOption::Load($list);

    $title = sprintf('[%d%s] %s - %s',
      DB::$ROOM->id, GameMessage::ROOM_NUMBER_FOOTER, DB::$ROOM->name, $base_title
    );

    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str = AutoPlayTalk::GenerateHeader($title);
    } elseif (RQ::Get()->reverse_log && RQ::Get()->scroll > 0) {
      $str = self::GenerateScrollHeader($title);
    } else {
      $str = HTML::GenerateHeader($title, 'old_log', true);
    }
    $str .= Text::Join(
      HTML::GenerateLink(URL::GetHeaderDB('old_log'), Message::BACK),
      RoomHTML::GenerateLogTitle(), RoomOption::GenerateImage(),
      Text::LineFeed(HTML::GenerateLink('#beforegame', OldLogMessage::BEFORE))
    );
    for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
      $str .= Text::LineFeed(HTML::GenerateLink('#date' . $i, $i));
    }
    $str .= HTML::GenerateLink('#aftergame', OldLogMessage::AFTER) . Message::SPACER;
    $str .= Text::LineFeed(RQ::Get()->GetURL());
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $str .= Text::Format('<a href="#game_top" onClick="start_auto_play();">%s</a>', '開始');
    }
    $str .= GameHTML::GeneratePlayer();
    if (RQ::Get()->role_list) {
      $str .= self::GenerateRoleLink();
    }
    $str .= RQ::Get()->heaven_only ? self::GenerateHeavenLog() : self::GenerateLog();
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
      $back  = HTML::GenerateLink('./', Message::BACK);
      HTML::OutputResult($title, Text::Join(OldLogMessage::NO_LOG, $back));
    }

    $cache_flag = false; //キャッシュ取得判定
    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG_LIST)) {
      $cache_flag = self::IsCache();
      if (true === $cache_flag) {
	$str = JinrouCacheManager::Get(JinrouCacheManager::LOG_LIST);
	if (true === isset($str)) {
	  return $str;
	}
      }
    }

    //ページリンクデータの生成
    if (empty(RQ::Get()->reverse)) {
      $is_reverse = OldLogConfig::REVERSE;
    } else {
      $is_reverse = Switcher::IsOn(RQ::Get()->reverse);
    }

    if (RQ::Get()->generate_index) {
      $max = RQ::Get()->max_room_no;
      if (is_int($max) && $max > 0 && $room_count > $max) {
	$room_count = $max;
      }
      $builder = new PageLinkBuilder('index', RQ::Get()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->url = '<a href="index';
    } else {
      $builder = new PageLinkBuilder('old_log', RQ::Get()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->AddOption('reverse', Switcher::Get($is_reverse));
      $builder->AddOption('watch',   Switcher::Get(RQ::Get()->watch));
      foreach (['name', 'room_name', 'winner', 'role'] as $option) {
	if (RQ::Get()->$option) {
	  $builder->AddOption($option, RQ::Get()->$option);
	}
      }

      if (URL::ExistsDB()) {
	$builder->AddOption(RequestDataGame::DB, RQ::Get()->db_no);
      }
    }

    $str = self::GenerateListHeader($builder);

    //全部表示の場合、一ページで全部表示する。それ以外は設定した数毎に表示
    $format = self::GetList();
    $current_time = Time::Get();
    foreach (RoomLoaderDB::GetFinished($is_reverse) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));

      $vanish = DB::$ROOM->IsDate(0) ? ' vanish' : ''; //廃村判定
      if (RQ::Get()->generate_index) {
	$base_url = DB::$ROOM->id . '.html';
	$login    = '';
	$log_link = sprintf('(<a href="%dr.html">%s</a>)', DB::$ROOM->id, Message::LOG_REVERSE);
      } else {
	$base_url = URL::GetRoom('old_log');;
	if (URL::ExistsDB()) {
	  $view_url  = RQ::Get()->ToURL(RequestDataGame::DB, true);
	  $base_url .= $view_url;
	} else {
	  $view_url  = '';
	}
	if (RQ::Get()->watch) {
	  $base_url .= URL::GetSwitch(RequestDataLogRoom::WATCH);
	}

	if ($current_time - strtotime(DB::$ROOM->finish_datetime) > RoomConfig::KEEP_SESSION) {
	  $login = '';
	} else {
	  $login = Text::LineFeed(HTML::GenerateLink(URL::GetRoom('login'), OldLogMessage::LOGIN));
	}

	if (RQ::Get()->watch) {
	  $log_link  = self::GenerateWatchLogLink($base_url, '(', '', ' )');
	} else {
	  $log_link  = HTML::GenerateLogLink($base_url, true, '(', '', ' )');

	  $url       = $base_url . URL::GetSwitch(RequestDataLogRoom::ROLE);
	  $header    = Text::LF . OldLogMessage::ADD_ROLE . ' (';
	  $log_link .= HTML::GenerateLogLink($url, false, $header, $vanish, ' )');
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
      RoomOption::Load($list);
      RoomOption::SetStack();

      $str .= Text::Format($format,
	URL::GetRoom('game_view'), $view_url,
	DB::$ROOM->id, $vanish, $base_url, DB::$ROOM->GenerateName(),
	DB::$ROOM->user_count, ImageManager::Room()->GenerateMaxUser(DB::$ROOM->max_user),
	DB::$ROOM->date,
	RQ::Get()->watch ? '-' : ImageManager::Winner()->Generate(DB::$ROOM->winner),
	DB::$ROOM->GenerateComment(), $establish, $vanish,
	$login, $log_link, RoomOption::GenerateImage()
      );
    }

    $str .= Text::LineFeed(self::GetListFooter());
    if (true === $cache_flag) {
      JinrouCacheManager::Store($str);
    }
    return $str;
  }

  //過去ログ一覧のHTML化処理
  public static function GenerateIndex() {
    RQ::Set('reverse', Switcher::OFF);
    if (RQ::Get()->max_room_no < 1) return false;

    $header = sprintf('../log/%sindex', RQ::Get()->prefix);
    $footer = Text::LineFeed('</body></html>');
    $end_page = ceil((RQ::Get()->max_room_no - RQ::Get()->min_room_no + 1) / OldLogConfig::VIEW);
    for ($i = 1; $i <= $end_page; $i++) {
      RQ::Set('page', $i);
      $index = RQ::Get()->index_no - $i + 1;
      file_put_contents($header. $index . '.html', self::GenerateList($i) . $footer);
    }
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

    if (RQ::Get()->scroll_time > 0) {
      $timeout = RQ::Get()->scroll_time;
    } else {
      $timeout = 1;
    }
    $str  = HTML::GenerateHeader($title, 'old_log');
    $str .= HTML::LoadJavaScript('auto_scroll');
    $str .= HTML::GenerateJavaScriptHeader();
    $str .= Text::Format($format, RQ::Get()->scroll, $timeout);
    $str .= HTML::GenerateJavaScriptFooter();
    $str .= HTML::GenerateBodyHeader(null, 'auto_scroll();');

    return $str;
  }

  //通常ログ出力
  private static function GenerateLog() {
    if (true === RQ::Get()->reverse_log) {
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
    if (true === RQ::Get()->reverse_log) {
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
      if (false === RQ::Get()->reverse_log) {
	DB::$USER->ResetPlayer(); //player 復元処理
      }
      break;

    case RoomScene::AFTER:
      $table_class = $scene;
      if (DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) {
	DB::$USER->ResetRoleList();
	DB::$ROOM->ResetEvent();
      }
      if (true === RQ::Get()->reverse_log) {
	DB::$USER->ResetPlayer(); //player 復元処理
      }
      break;

    case RoomScene::HEAVEN_ONLY:
      if (true === RQ::Get()->reverse_log && $date != 1) {
	$table_class = RoomScene::DAY; //2日目以降は昼から
      } else {
	$table_class = RoomScene::NIGHT;
      }
      break;

    default:
      $border_game_flag = true;
      if (true === RQ::Get()->reverse_log && $date != 1) {
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
    if (true === $border_game_flag && false === RQ::Get()->reverse_log) {
      DB::$ROOM->date = $date + 1;
      DB::$ROOM->SetScene(RoomScene::DAY);
      $str .= self::GenerateLastWords() . self::GenerateDead(); //死亡者を出力
    }

    DB::$ROOM->date = $date;
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
    if (true === RQ::Get()->reverse_log) {
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

    if (false === RQ::Get()->reverse_log) {
      $builder->GenerateTimeStamp();
    }
    $str .= $builder->Refresh();

    if (true === $border_game_flag && true === RQ::Get()->reverse_log) {
      //突然死で勝敗が決定したケース
      if ($date == DB::$ROOM->last_date && DB::$ROOM->IsDay()) {
	$str .= self::GenerateVote();
	DB::$ROOM->SetScene(RoomScene::NIGHT);
	$str .= self::GenerateDead();
      }

      DB::$ROOM->date = $date + 1;
      DB::$ROOM->SetScene(RoomScene::DAY);
      $str .= self::GenerateDead() . self::GenerateLastWords(); //遺言を出力
    }
    return $str;
  }

  //シーン切り替え処理
  private static function GenerateSceneChange($date) {
    $str = '';
    if (RQ::Get()->heaven_only) {
      return $str;
    }

    DB::$ROOM->date = $date;
    if (true === RQ::Get()->reverse_log) {
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
  private static function IsCache() {
    foreach (RQ::Get() as $key => $value) { //何か値がセットされていたら無効
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
    if (RQ::Get()->generate_index) {
      $back = HTML::GenerateLink('../', Message::BACK);
      $url  = '../';
    } else {
      $back = HTML::GenerateLink('./', Message::BACK);
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
