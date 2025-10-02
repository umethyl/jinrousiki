<?php
//-- ページ送りリンク生成クラス --//
class PageLinkBuilder {
  public function __construct($file, $page, $count, $title = 'Page', $type = 'page') {
    $this->view_total = $count;
    $this->view_page  = OldLogConfig::PAGE;
    $this->view_count = OldLogConfig::VIEW;
    $this->reverse    = OldLogConfig::REVERSE;

    $this->file   = $file;
    $this->url    = '<a href="' . $file . '.php?';
    $this->title  = $title;
    $this->type   = $type;
    $this->option = array();
    $this->SetPage($page);
  }

  //オプションを追加する
  public function AddOption($type, $value = 'on') {
    $this->option[$type] = $type . '=' . $value;
  }

  //ページリンクを生成する
  public function Generate() {
    $url_stack = array(sprintf('[%s]', $this->title));
    if ($this->file == 'index') $url_stack[] = '[<a href="index.html">new</a>]';
    if ($this->page->start > 1 && $this->page->total > $this->view_page) {
      $url_stack[] = $this->GenerateTag(1, '[1]...');
      $url_stack[] = $this->GenerateTag($this->page->start - 1, '&lt;&lt;');
    }

    for ($i = $this->page->start; $i <= $this->page->end; $i++) {
      $url_stack[] = $this->GenerateTag($i);
    }

    if ($this->page->end < $this->page->total) {
      $url_stack[] = $this->GenerateTag($this->page->end + 1, '&gt;&gt;');
      $url_stack[] = $this->GenerateTag($this->page->total, sprintf('...[%s]', $this->page->total));
    }
    if ($this->file != 'index') $url_stack[] = $this->GenerateTag('all');

    if ($this->file == 'old_log') {
      $this->AddOption('reverse', $this->set_reverse ? 'off' : 'on');
      $url_stack[] = OldLogMessage::LINK_ORDER;
      if ($this->set_reverse) {
	$url_stack[] = OldLogMessage::ORDER_REVERSE;
      } else {
	$url_stack[] = OldLogMessage::ORDER_NORMAL;
      }

      if ($this->set_reverse xor $this->reverse) {
	$name = OldLogMessage::ORDER_RESET;
      } else {
	$name = OldLogMessage::ORDER_CHANGE;
      }
      $url_stack[] =  $this->GenerateTag($this->page->set, $name, true);

      if (RQ::Get()->watch) {
	$this->AddOption('reverse', $this->set_reverse ? 'on' : 'off');
	$this->AddOption('watch', 'off');
	$url_stack[] = $this->GenerateTag($this->page->set, OldLogMessage::LINK_WIN, true);
      }
    }
    return implode(' ', $url_stack);
  }

  //ページリンクを出力する
  public function Output() {
    echo $this->Generate();
  }

  //ページ送り用のリンクタグを作成する
  protected function GenerateTag($page, $title = null, $force = false) {
    if ($page == $this->page->set && ! $force) return sprintf('[%s]', $page);
    if (is_null($title)) $title = sprintf('[%s]', $page);
    if ($this->file == 'index') {
      $footer = $page . '.html';
    }
    else {
      $list = $this->option;
      array_unshift($list, $this->type . '=' . $page);
      $footer = implode('&', $list);
    }
    return $this->url . $footer . '">' . $title . '</a>';
  }

  //表示するページのアドレスをセット
  private function SetPage($page) {
    $total = ceil($this->view_total / $this->view_count);
    if ($page == 'all') {
      $start = 1;
    }
    else {
      $start = $page - floor($this->view_page / 2);
      if ($total - $start < $this->view_page) { //残りページが少ない場合は表示開始位置をずらす
	$start = $total - $this->view_page + 1;
      }
      $start = max(1, $start);
    }
    $end = $start + $this->view_page - 1;
    if ($end > $total) $end = $total;

    $this->page->set   = $page;
    $this->page->total = $total;
    $this->page->start = $start;
    $this->page->end   = $end;
    //Text::p($this->page, '◆page');
    $this->limit = $page == 'all' ? '' : $this->view_count * ($page - 1);
    $this->query = $page == 'all' ? '' : sprintf(' LIMIT %d, %d', $this->limit, $this->view_count);
  }
}

//-- HTML 生成クラス (OldLog 拡張) --//
class OldLogHTML {
  //指定の部屋番号のログを生成する
  static function Generate() {
    $base_title = ServerConfig::TITLE . OldLogMessage::TITLE;
    if (! DB::$ROOM->IsFinished() || ! DB::$ROOM->IsAfterGame()) { //閲覧判定
      $url  = RQ::Get()->generate_index ? 'index.html' : 'old_log.php';
      $back = HTML::GenerateLink($url, Message::BACK);
      $str  = OldLogMessage::NOT_FINISHED . Text::BRLF . $back;
      HTML::OutputResult($base_title, $str);
    }

    if (DocumentCache::Enable('old_log')) { //キャッシュ取得判定
      DocumentCache::Load('old_log/' . print_r(RQ::Get(), true), CacheConfig::OLD_LOG_EXPIRE);
      $str = DocumentCache::GetData();
      if (isset($str)) return $str;
    }

    if (DB::$ROOM->watch_mode) { //観戦モード判定
      DB::$ROOM->status = 'playing';
      DB::$ROOM->SetScene('day');
    }

    $list = array(
      'game_option' => DB::$ROOM->game_option->row,
      'option_role' => DB::$ROOM->option_role->row,
      'max_user'    => 0);
    RoomOption::Load($list);

    $title = sprintf('[%d%s] %s - %s',
		     DB::$ROOM->id, GameMessage::ROOM_NUMBER_FOOTER, DB::$ROOM->name, $base_title);

    if (RQ::Get()->reverse_log && RQ::Get()->scroll > 0) {
      $str = self::GenerateScrollHeader($title);
    } else {
      $str = HTML::GenerateHeader($title, 'old_log', true);
    }
    $url = RQ::Get()->db_no > 0 ? sprintf('?db_no=%d', RQ::Get()->db_no) : '';
    $str .= HTML::GenerateLink('old_log.php' . $url, Message::BACK) . Text::BRLF;
    $str .= DB::$ROOM->GenerateTitleTag(true) . Text::BRLF;
    $str .= RoomOption::GenerateImage() . Text::BRLF;
    $str .= sprintf('<a href="#beforegame">%s</a>' . Text::LF, OldLogMessage::BEFORE);
    for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
      $str .= sprintf('<a href="#date%d">%d</a>', $i, $i) . Text::LF;
    }
    $str .= sprintf('<a href="#aftergame">%s</a>' . Text::LF . Text::BRLF, OldLogMessage::AFTER);
    $str .= GameHTML::GeneratePlayer();
    if (RQ::Get()->role_list) $str .= self::GenerateRoleLink();
    $str .= RQ::Get()->heaven_only ? self::GenerateHeavenLog() : self::GenerateLog();

    if (DocumentCache::Enable('old_log')) DocumentCache::Save($str);
    return $str;
  }

  //過去ログ一覧生成
  static function GenerateList($page) {
    //村数の確認
    $room_count = RoomDataDB::GetFinishedCount();
    if ($room_count < 1) {
      $title = ServerConfig::TITLE . OldLogMessage::TITLE;
      $back  = HTML::GenerateLink('./', Message::BACK);
      HTML::OutputResult($title, OldLogMessage::NO_LOG . Text::BRLF . $back);
    }

    $cache_flag = false; //キャッシュ取得判定
    if (DocumentCache::Enable('old_log_list')) {
      $cache_flag = self::IsCache();
      if ($cache_flag) {
	DocumentCache::Load('old_log/list', CacheConfig::OLD_LOG_LIST_EXPIRE);
	$str = DocumentCache::GetData();
	if (isset($str)) return $str;
      }
    }

    //ページリンクデータの生成
    $is_reverse = empty(RQ::Get()->reverse) ? OldLogConfig::REVERSE : RQ::Get()->reverse == 'on';
    if (RQ::Get()->generate_index) {
      $max = RQ::Get()->max_room_no;
      if (is_int($max) && $max > 0 && $room_count > $max) $room_count = $max;
      $builder = new PageLinkBuilder('index', RQ::Get()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->url = '<a href="index';
    }
    else {
      $builder = new PageLinkBuilder('old_log', RQ::Get()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->AddOption('reverse', $is_reverse      ? 'on' : 'off');
      $builder->AddOption('watch',   RQ::Get()->watch ? 'on' : 'off');
      if (RQ::Get()->name) $builder->AddOption('name', RQ::Get()->name);
      $db_no = RQ::Get()->db_no;
      if (is_int($db_no) && $db_no > 0) $builder->AddOption('db_no', $db_no);
    }

    $str = self::GenerateListHeader($builder);

    //全部表示の場合、一ページで全部表示する。それ以外は設定した数毎に表示
    $format = self::GetGenerateListFormat();
    $current_time = Time::Get();
    foreach (RoomDataDB::GetFinished($is_reverse) as $room_no) {
      DB::SetRoom(RoomDataDB::LoadFinished($room_no));

      $vanish = DB::$ROOM->IsDate(0) ? ' vanish' : ''; //廃村判定
      if (RQ::Get()->generate_index) {
	$base_url = DB::$ROOM->id . '.html';
	$login    = '';
	$log_link = sprintf('(<a href="%dr.html">%s</a>)', DB::$ROOM->id, Message::LOG_REVERSE);
      }
      else {
	$base_url = 'old_log.php?room_no=' . DB::$ROOM->id;
	if (is_int(RQ::Get()->db_no) && RQ::Get()->db_no > 0) {
	  $base_url .= '&db_no=' . RQ::Get()->db_no;
	}
	if (RQ::Get()->watch) $base_url .= '&watch=on';

	if ($current_time - strtotime(DB::$ROOM->finish_datetime) > RoomConfig::KEEP_SESSION) {
	  $login = '';
	} else {
	  $login = self::GenerateLogin($vanish);
	}

	if (RQ::Get()->watch) {
	  $log_link = HTML::GenerateWatchLogLink($base_url, '(', '', ' )');
	}
	else {
	  $log_link = HTML::GenerateLogLink($base_url, true, '(', '', ' )');

	  $url    = $base_url . '&add_role=on';
	  $header = Text::LF . OldLogMessage::ADD_ROLE . ' (';
	  $log_link .= HTML::GenerateLogLink($url, false, $header, $vanish, ' )');
	}
      }

      if (DB::$ROOM->establish_datetime == '') {
	$establish = '';
      } else {
	$establish = Time::ConvertTimeStamp(DB::$ROOM->establish_datetime);
      }

      $list = array(
        'game_option' => DB::$ROOM->game_option,
	'option_role' => DB::$ROOM->option_role,
	'max_user'    => DB::$ROOM->max_user);
      RoomOption::Load($list);
      RoomOption::SetStack();

      $str .= sprintf($format . Text::LF,
	DB::$ROOM->id, DB::$ROOM->id, $vanish, $base_url, DB::$ROOM->GenerateName(),
	DB::$ROOM->user_count, Image::GenerateMaxUser(DB::$ROOM->max_user), DB::$ROOM->date,
	RQ::Get()->watch ? '-' : Image::Winner()->Generate(DB::$ROOM->winner),
	DB::$ROOM->GenerateComment(), $establish, $vanish, $login, $log_link,
	RoomOption::GenerateImage());
    }

    $str .= self::GenerateListFooter();
    if ($cache_flag) DocumentCache::Save($str);
    return $str;
  }

  //過去ログ一覧のHTML化処理
  static function GenerateIndex() {
    RQ::Set('reverse', 'off');
    if (RQ::Get()->max_room_no < 1) return false;
    $header = sprintf('../log/%sindex', RQ::Get()->prefix);
    $footer = '</body></html>' . Text::LF;
    $end_page = ceil((RQ::Get()->max_room_no - RQ::Get()->min_room_no + 1) / OldLogConfig::VIEW);
    for ($i = 1; $i <= $end_page; $i++) {
      RQ::Set('page', $i);
      $index = RQ::Get()->index_no - $i + 1;
      file_put_contents($header. $index . '.html', self::GenerateList($i) . $footer);
    }
  }

  //指定の部屋番号のログを出力する
  static function Output() {
    echo self::Generate();
  }

  //過去ログ一覧表示
  static function OutputList($page) {
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
    $str .= sprintf($format . Text::LF, RQ::Get()->scroll, $timeout);
    $str .= HTML::GenerateJavaScriptFooter();
    $str .= HTML::GenerateBodyHeader(null, 'auto_scroll();');

    return $str;
  }

  //通常ログ出力
  private static function GenerateLog() {
    if (RQ::Get()->reverse_log) {
      $str = self::GenerateTalk(0, 'beforegame');
      if (DB::$ROOM->IsOption('open_day')) $str .= self::GenerateTalk(0, 'day');
      for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
	$str .= self::GenerateTalk($i, '');
      }
      $str .= Winner::Generate() . self::GenerateTalk(DB::$ROOM->last_date, 'aftergame');
    }
    else {
      $str = self::GenerateTalk(DB::$ROOM->last_date, 'aftergame') . Winner::Generate();
      for ($i = DB::$ROOM->last_date; $i > 0; $i--) {
	$str .= self::GenerateTalk($i, '');
      }
      $str .= self::GenerateTalk(0, 'beforegame');
    }
    return $str;
  }

  //霊界ログ出力
  private static function GenerateHeavenLog() {
    $str = '';
    if (RQ::Get()->reverse_log) {
      for ($i = 1; $i <= DB::$ROOM->last_date; $i++) {
	$str .= self::GenerateTalk($i, 'heaven_only');
      }
    }
    else {
      for ($i = DB::$ROOM->last_date; $i > 0; $i--) {
	$str .= self::GenerateTalk($i, 'heaven_only');
      }
    }
    return $str;
  }

  //指定の日付の会話ログを生成
  private static function GenerateTalk($date, $scene) {
    $flag_border_game = false;
    switch ($scene) { //シーンに合わせたデータをセット
    case 'beforegame':
      $table_class = $scene;
      if (DB::$ROOM->watch_mode || DB::$ROOM->single_view_mode) {
	DB::$USER->ResetRoleList();
	unset(DB::$ROOM->event);
      }
      if (! RQ::Get()->reverse_log) DB::$USER->ResetPlayer(); //player 復元処理
      break;

    case 'aftergame':
      $table_class = $scene;
      if (DB::$ROOM->watch_mode || DB::$ROOM->single_view_mode) {
	DB::$USER->ResetRoleList();
	unset(DB::$ROOM->event);
      }
      if (RQ::Get()->reverse_log) DB::$USER->ResetPlayer(); //player 復元処理
      break;

    case 'heaven_only':
      $table_class = RQ::Get()->reverse_log && $date != 1 ? 'day' : 'night'; //2日目以降は昼から
      break;

    default:
      $flag_border_game = true;
      $table_class = RQ::Get()->reverse_log && $date != 1 ? 'day' : 'night'; //2日目以降は昼から
      if (DB::$ROOM->watch_mode || DB::$ROOM->single_view_mode) {
	DB::$USER->ResetRoleList();
	DB::$USER->SetEvent(true);
      }
      break;
    }

    //出力
    $str = '';
    if ($flag_border_game && ! RQ::Get()->reverse_log) {
      DB::$ROOM->date = $date + 1;
      DB::$ROOM->SetScene('day');
      $str .= GameHTML::GenerateLastWords() . GameHTML::GenerateDead(); //死亡者を出力
    }
    DB::$ROOM->date = $date;
    DB::$ROOM->SetScene($table_class);
    if ($scene != 'heaven_only') DB::$ROOM->SetWeather();

    $id = DB::$ROOM->IsPlaying() ? 'date' . DB::$ROOM->date : DB::$ROOM->scene;
    $builder = new TalkBuilder('talk ' . $table_class, $id);
    if (RQ::Get()->reverse_log) $builder->GenerateTimeStamp();

    foreach (TalkDB::GetLog($date, $scene) as $talk) {
      switch ($talk->scene) {
      case 'day':
      case 'night':
	if ($talk->scene == DB::$ROOM->scene || $talk->location == 'dummy_boy') break;
	$str .= $builder->Refresh() . self::GenerateSceneChange($date);
	DB::$ROOM->SetScene($talk->scene);
	$builder->Begin('talk ' . $talk->scene);
	break;
      }
      $builder->Generate($talk); //会話生成
    }

    if (! RQ::Get()->reverse_log) $builder->GenerateTimeStamp();
    $str .= $builder->Refresh();

    if ($flag_border_game && RQ::Get()->reverse_log) {
      //突然死で勝敗が決定したケース
      if ($date == DB::$ROOM->last_date && DB::$ROOM->IsDay()) {
	$str .= GameHTML::GenerateVote();
	DB::$ROOM->SetScene('night');
	$str .= GameHTML::GenerateDead();
      }

      DB::$ROOM->date = $date + 1;
      DB::$ROOM->SetScene('day');
      $str .= GameHTML::GenerateDead() . GameHTML::GenerateLastWords(); //遺言を出力
    }
    return $str;
  }

  //シーン切り替え処理
  private static function GenerateSceneChange($date) {
    $str = '';
    if (RQ::Get()->heaven_only) return $str;
    DB::$ROOM->date = $date;
    if (RQ::Get()->reverse_log) {
      DB::$ROOM->SetScene('night');
      $str .= GameHTML::GenerateVote() . GameHTML::GenerateDead();
    } else {
      $str .= GameHTML::GenerateDead() . GameHTML::GenerateVote();
    }
    return $str;
  }

  //役職リンク生成
  private static function GenerateRoleLink() {
    $stack = array();
    foreach (DB::$USER->role as $role => $list) {
      $stack[] = $role;
    }

    $str_stack  = array();
    $role_stack = array();
    foreach (array_intersect(RoleData::GetList(), $stack) as $role) {
      if (! isset($camp)) $camp = RoleData::GetCamp($role);
      if ($camp != RoleData::GetCamp($role) || count($role_stack) > 9) {
	$str_stack[] = implode(' / ', $role_stack);
	$role_stack = array();
	$camp = RoleData::GetCamp($role);
      }
      $role_stack[] = RoleDataHTML::GenerateMain($role) . DB::$USER->GetRoleCount($role);
    }
    $str_stack[] = implode(' / ', $role_stack);

    $role_stack = array();
    foreach (array_intersect(RoleData::GetList(true), $stack) as $role) {
      if (count($role_stack) > 9) {
	$str_stack[] = implode(' / ', $role_stack);
	$role_stack = array();
      }
      $role_stack[] = RoleDataHTML::GenerateSub($role) . DB::$USER->GetRoleCount($role);
    }
    $str_stack[] = implode(' / ', $role_stack);
    return implode(Text::BRLF, $str_stack);
  }

  //キャッシュ有効判定
  private static function IsCache() {
    foreach (RQ::Get() as $key => $value) { //何か値がセットされていたら無効
      switch ($key) {
      case 'page':
	if ($value != 1) return false;
	break;

      default:
	if (! empty($value)) return false;
	break;
      }
    }
    return true;
  }

  //一覧ヘッダー生成
  private static function GenerateListHeader(PageLinkBuilder $builder) {
    $format = <<<EOF
<p>%s</p>
<img src="%simg/title/old_log.jpg"><br>
<div>
<table>
<caption>%s</caption>
<thead>
<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>
</thead>
<tbody>
EOF;

    if (RQ::Get()->generate_index) {
      $back = HTML::GenerateLink('../', Message::BACK);
      $url  = '../';
    } else {
      $back = HTML::GenerateLink('./', Message::BACK);
      $url  = '';
    }
    $str = sprintf($format . Text::LF, $back, $url, $builder->Generate(),
		   OldLogMessage::NUMBER, OldLogMessage::NAME, OldLogMessage::COUNT,
		   OldLogMessage::DATE, OldLogMessage::WIN);

    $title = ServerConfig::TITLE . OldLogMessage::TITLE;
    return HTML::GenerateHeader($title, 'old_log_list', true) . $str;
  }

  //一覧個別村情報出力フォーマット取得
  private static function GetGenerateListFormat() {
    return <<<EOF
<tr>
<td class="number" rowspan="3"><a href="game_view.php?room_no=%d">%d</a></td>
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

  //再入村リンク生成
  private static function GenerateLogin($vanish) {
    $format = '<a href="login.php?room_no=%d%s">%s</a>';
    return sprintf($format, DB::$ROOM->id, $vanish, OldLogMessage::LOGIN) . Text::LF;
  }

  //一覧フッター生成
  private static function GenerateListFooter() {
    return <<<EOF
</tbody>
</table>
</div>

EOF;
  }
}
