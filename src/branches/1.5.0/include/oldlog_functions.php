<?php
//-- クラス定義 --//
//-- ページ送りリンク生成クラス --//
class PageLinkBuilder{
  function __construct($file, $page, $count, $config, $title = 'Page', $type = 'page'){
    $this->view_total = $count;
    $this->view_page  = $config->page;
    $this->view_count = $config->view;
    $this->reverse    = $config->reverse;

    $this->file   = $file;
    $this->url    = '<a href="' . $file . '.php?';
    $this->title  = $title;
    $this->type   = $type;
    $this->option = array();
    $this->SetPage($page);
  }

  //表示するページのアドレスをセット (private)
  function SetPage($page){
    $total = ceil($this->view_total / $this->view_count);
    $start = $page == 'all' ? 1 : $page;
    if($total - $start < $this->view_page){ //残りページが少ない場合は表示開始位置をずらす
      $start = $total - $this->view_page + 1;
      if($start < 1) $start = 1;
    }
    $end = $start + $this->view_page - 1;
    if($end > $total) $end = $total;

    $this->page->set   = $page;
    $this->page->total = $total;
    $this->page->start = $start;
    $this->page->end   = $end;

    $this->limit = $page == 'all' ? '' : $this->view_count * ($page - 1);
    $this->query = $page == 'all' ? '' : sprintf(' LIMIT %d, %d', $this->limit, $this->view_count);
  }

  //オプションを追加する
  function AddOption($type, $value = 'on'){ $this->option[$type] = $type . '=' . $value; }

  //ページ送り用のリンクタグを作成する
  function GenerateTag($page, $title = NULL, $force = false){
    if($page == $this->page->set && ! $force) return '[' . $page . ']';
    if(is_null($title)) $title = '[' . $page . ']';
    if($this->file == 'index'){
      $footer = $page . '.html';
    }
    else{
      $list = $this->option;
      array_unshift($list, $this->type . '=' . $page);
      $footer = implode('&', $list);
    }
    return $this->url . $footer . '">' . $title . '</a>';
  }

  //ページリンクを生成する
  function Generate(){
    $url_stack = array('[' . $this->title . ']');
    if($this->file == 'index') $url_stack[] = '[<a href="index.html">new</a>]';
    if($this->page->start > 1 && $this->page->total > $this->view_page){
      $url_stack[] = $this->GenerateTag(1, '[1]...');
      $url_stack[] = $this->GenerateTag($this->page->start - 1, '&lt;&lt;');
    }

    for($i = $this->page->start; $i <= $this->page->end; $i++){
      $url_stack[] = $this->GenerateTag($i);
    }

    if($this->page->end < $this->page->total){
      $url_stack[] = $this->GenerateTag($this->page->end + 1, '&gt;&gt;');
      $url_stack[] = $this->GenerateTag($this->page->total, '...[' . $this->page->total . ']');
    }
    if($this->file != 'index') $url_stack[] = $this->GenerateTag('all');

    if($this->file == 'old_log'){
      $this->AddOption('reverse', $this->set_reverse ? 'off' : 'on');
      $url_stack[] = '[表示順]';
      $url_stack[] = $this->set_reverse ? '新↓古' : '古↓新';
      $name = ($this->set_reverse xor $this->reverse) ? '元に戻す' : '入れ替える';
      $url_stack[] =  $this->GenerateTag($this->page->set, $name, true);
    }
    return $this->header . implode(' ', $url_stack) . $this->footer;
  }

  //ページリンクを出力する
  function Output(){ echo $this->Generate(); }
}

//-- 関数 --//
//過去ログ一覧生成
function GenerateFinishedRooms($page){
  global $SERVER_CONF, $ROOM_CONF, $MESSAGE, $ROOM_IMG, $RQ_ARGS;

  //村数の確認
  $title = $SERVER_CONF->title . ' [過去ログ]';
  $room_count = FetchResult("SELECT COUNT(status) FROM room WHERE status = 'finished'");
  if($room_count < 1){
    OutputActionResult($title, 'ログはありません。<br>'."\n" . '<a href="./">←戻る</a>'."\n");
  }

  //ページリンクデータの生成
  $LOG_CONF = new OldLogConfig(); //設定をロード
  $is_reverse = empty($RQ_ARGS->reverse) ? $LOG_CONF->reverse : ($RQ_ARGS->reverse == 'on');
  if($RQ_ARGS->generate_index){
    $max = $RQ_ARGS->max_room_no;
    if(is_int($max) && $max > 0 && $room_count > $max) $room_count = $max;
    $builder = new PageLinkBuilder('index', $RQ_ARGS->page, $room_count, $LOG_CONF);
    $builder->set_reverse = $is_reverse;
    $builder->url = '<a href="index';
  }
  else{
    $builder = new PageLinkBuilder('old_log', $RQ_ARGS->page, $room_count, $LOG_CONF);
    $builder->set_reverse = $is_reverse;
    $builder->AddOption('reverse', $is_reverse ? 'on' : 'off');
    $builder->AddOption('watch', $RQ_ARGS->watch ? 'on' : 'off');
    $db_no = $RQ_ARGS->db_no;
    if(is_int($db_no) && $db_no > 0) $builder->AddOption('db_no', $db_no);
  }

  $back_url = $RQ_ARGS->generate_index ? '../' : './';
  $img_url  = $RQ_ARGS->generate_index ? '../' : '';
  $str = GenerateHTMLHeader($title, 'old_log_list') . <<<EOF
</head>
<body id="room_list">
<p><a href="{$back_url}">←戻る</a></p>
<img src="{$img_url}img/old_log_title.jpg"><br>
<div align="center">
<table><tr><td class="list">
{$builder->Generate()}
</td></tr>
<tr><td>
<table class="main">
<tr><th>村No</th><th>村名</th><th>人数</th><th>日数</th><th>勝</th></tr>

EOF;

  //全部表示の場合、一ページで全部表示する。それ以外は設定した数毎に表示
  $ROOM_DATA = new RoomDataSet();
  $VICT_IMG  = new VictoryImage();
  $current_time = TZTime(); // 現在時刻の取得

  $query = "SELECT room_no FROM room WHERE status = 'finished' ORDER BY room_no";
  if($is_reverse) $query .= ' DESC';
  if($RQ_ARGS->page != 'all'){
    $query .= sprintf(' LIMIT %d, %d', $LOG_CONF->view * ($RQ_ARGS->page - 1), $LOG_CONF->view);
  }
  foreach(FetchArray($query) as $room_no){
    $ROOM = $ROOM_DATA->LoadFinishedRoom($room_no);

    $dead_room = $ROOM->date == 0 ? ' style="color:silver"' : ''; //廃村の場合、色を灰色にする
    $establish_time = $ROOM->establish_time == '' ? '' : ConvertTimeStamp($ROOM->establish_time);
    if($RQ_ARGS->generate_index){
      $base_url = $ROOM->id . '.html';
      $login    = '';
      $log_link = '(<a href="' .  $ROOM->id . 'r.html">逆</a>)';
    }
    else{
      $base_url = 'old_log.php?room_no=' . $ROOM->id;
      if(is_int($RQ_ARGS->db_no) && $RQ_ARGS->db_no > 0) $base_url .= '&db_no=' . $RQ_ARGS->db_no;
      $login = $current_time - strtotime($ROOM->finish_time) > $ROOM_CONF->clear_session_id ? '' :
	'<a href="login.php?room_no=' . $ROOM->id . '"' . $dead_room . ">[再入村]</a>\n";
      $log_link = GenerateLogLink($base_url, true, '(', $dead_room) . ' )' .
	GenerateLogLink($base_url . '&add_role=on', false, "\n[役職表示] (", $dead_room) . ' )';
    }
    $max_user    = GenerateMaxUserImage($ROOM->max_user);
    $game_option = GenerateGameOptionImage($ROOM->game_option, $ROOM->option_role);
    $victory     = $RQ_ARGS->watch ? '-' : $VICT_IMG->Generate($ROOM->victory_role);
    $str .= <<<EOF
<tr class="list">
<td class="number" rowspan="3">{$ROOM->id}</td>
<td class="title"><a href="{$base_url}"{$dead_room}>{$ROOM->name} 村</a>
<td class="upper">{$ROOM->user_count} {$max_user}</td>
<td class="upper">{$ROOM->date}</td>
<td class="side">{$victory}</td>
</tr>
<tr class="list middle">
<td class="comment side">～{$ROOM->comment}～</td>
<td class="time comment" colspan="3">{$establish_time}</td>
</tr>
<tr class="lower list">
<td class="comment">
{$login}{$log_link}
</td>
<td colspan="3">{$game_option}</td>
</tr>

EOF;
  }

  return $str . <<<EOF
</table>
</td></tr>
</table>
</div>

EOF;
}

//過去ログ一覧表示
function OutputFinishedRooms($page){ echo GenerateFinishedRooms($page); }

//過去ログ一覧のHTML化処理
function GenerateLogIndex(){
  global $RQ_ARGS;

  $RQ_ARGS->reverse = 'off';
  $LOG_CONF = new OldLogConfig(); //設定をロード
  if($RQ_ARGS->max_room_no < 1) return false;
  $header = "../log/{$RQ_ARGS->prefix}index";
  $footer = '</body></html>'."\n";
  $end_page = ceil(($RQ_ARGS->max_room_no - $RQ_ARGS->min_room_no + 1) / $LOG_CONF->view);
  for($i = 1; $i <= $end_page; $i++){
    $RQ_ARGS->page = $i;
    $index = $RQ_ARGS->index_no - $i + 1;
    file_put_contents("{$header}{$index}.html",  GenerateFinishedRooms($i) . $footer);
  }
}

//指定の部屋番号のログを生成する
function GenerateOldLog(){
  global $SERVER_CONF, $RQ_ARGS, $ROOM;

  $base_title = $SERVER_CONF->title . ' [過去ログ]'; //
  if(! $ROOM->IsFinished() || ! $ROOM->IsAfterGame()){ //閲覧判定
    $url = $RQ_ARGS->generate_index ? 'index.html' : 'old_log.php';
    $str = 'まだこの部屋のログは閲覧できません。<br>'."\n".'<a href="'.$url.'">←戻る</a>'."\n";
    OutputActionResult($base_title, $str);
  }
  if($ROOM->watch_mode){
    $ROOM->status    = 'playing';
    $ROOM->day_night = 'day';
  }
  $title  = '[' . $ROOM->id . '番地] ' . $ROOM->name . ' - ' . $base_title;
  $option = GenerateGameOptionImage($ROOM->game_option->row, $ROOM->option_role->row);
  $log    = GeneratePlayerList() . ($RQ_ARGS->heaven_only ? LayoutHeaven() : LayoutTalkLog());
  $link = '<a href="#beforegame">前</a>'."\n";
  for($i = 1; $i <= $ROOM->last_date ; $i++) $link .= '<a href="#date'.$i.'">'.$i.'</a>'."\n";
  $link .= '<a href="#aftergame">後</a>'."\n";
  return GenerateHTMLHeader($title, 'old_log') . <<<EOF
</head>
<body>
<a href="old_log.php">←戻る</a><br>
{$ROOM->GenerateTitleTag()}<br>
{$option}<br>
{$link}<br>
{$log}
EOF;
}

//指定の部屋番号のログを出力する
function OutputOldLog(){ echo GenerateOldLog(); }

//通常のログ表示順を表現します。
function LayoutTalkLog(){
  global $RQ_ARGS, $ROOM;

  if($RQ_ARGS->reverse_log){
    $str = GenerateDateTalkLog(0, 'beforegame');
    for($i = 1; $i <= $ROOM->last_date; $i++) $str .= GenerateDateTalkLog($i, '');
    $str .= GenerateVictory() . GenerateDateTalkLog($ROOM->last_date, 'aftergame');
  }
  else{
    $str = GenerateDateTalkLog($ROOM->last_date, 'aftergame') . GenerateVictory();
    for($i = $ROOM->last_date; $i > 0; $i--) $str .= GenerateDateTalkLog($i, '');
    $str .= GenerateDateTalkLog(0, 'beforegame');
  }
  return $str;
}

//霊界のみのログ表示順を表現します。
function LayoutHeaven(){
  global $RQ_ARGS, $ROOM;

  $str = '';
  if($RQ_ARGS->reverse_log){
    for($i = 1; $i <= $ROOM->last_date; $i++) $str .= GenerateDateTalkLog($i, 'heaven_only');
  }
  else{
    for($i = $ROOM->last_date; $i > 0; $i--)  $str .= GenerateDateTalkLog($i, 'heaven_only');
  }
  return $str;
}

//指定の日付の会話ログを生成
function GenerateDateTalkLog($set_date, $set_location){
  global $SERVER_CONF, $RQ_ARGS, $ROOM, $ROLES, $USERS;

  //シーンに合わせた会話ログを取得するためのクエリを生成
  $flag_border_game = false;
  if($SERVER_CONF->sort_talk_by_php){
    $query = "SELECT talk_id, uname, sentence, font_type, location " .
      "FROM talk WHERE room_no = {$ROOM->id} AND ";
  }
  else{
    $query = "SELECT uname, sentence, font_type, location FROM talk WHERE room_no = {$ROOM->id} AND ";
  }
  switch($set_location){
  case 'beforegame':
  case 'aftergame':
    $table_class = $set_location;
    $query .= "location LIKE '{$set_location}%'";
    if($ROOM->watch_mode || $ROOM->single_view_mode){
      $USERS->ResetRoleList();
      unset($ROOM->event);
    }
    break;

  case 'heaven_only':
    $table_class = $RQ_ARGS->reverse_log && $set_date != 1 ? 'day' : 'night'; //2日目以降は昼から
    $query .= "date = {$set_date} AND (location = 'heaven' OR uname = 'system')";
    break;

  default:
    $flag_border_game = true;
    $table_class = $RQ_ARGS->reverse_log && $set_date != 1 ? 'day' : 'night'; //2日目以降は昼から
    $query .= "date = {$set_date} AND location <> 'beforegame' AND location <> 'aftergame'";
    if(! $RQ_ARGS->heaven_talk) $query .= " AND location <> 'heaven'";
    if($ROOM->watch_mode || $ROOM->single_view_mode){
      $USERS->ResetRoleList();
      $USERS->SetEvent(true);
    }
    break;
  }
  if($ROOM->personal_mode) $query .= " AND uname = 'system'"; //個人結果表示モード
  if($SERVER_CONF->sort_talk_by_php){
    $talk_list = FetchTalk($query, 'Talk', $RQ_ARGS->reverse_log);
  }
  else{
    $query .= ' ORDER BY talk_id' . ($RQ_ARGS->reverse_log ? '' : ' DESC'); //ログの表示順
    //PrintData($query, $set_location);
    $talk_list = FetchObject($query, 'Talk');
  }
  //-- 仮想稼動モードテスト用 --//
  //global $USERS, $SELF;
  //$SELF = $USERS->rows[3];
  //$SELF->ParseRoles('human earplug');
  //$SELF->live = 'live';
  //$ROOM->status = 'playing';
  //$ROOM->option_list[] = 'not_open_cast';

  //出力
  $str = '';
  if($flag_border_game && ! $RQ_ARGS->reverse_log){
    $ROOM->date = $set_date + 1;
    $ROOM->day_night = 'day';
    $str .= GenerateLastWords() . GenerateDeadMan();//死亡者を出力
  }
  $ROOM->date = $set_date;
  $ROOM->day_night = $table_class;
  if($set_location != 'heaven_only') $ROOM->SetWeather();

  $builder = new DocumentBuilder();
  $id = $ROOM->IsPlaying() ? 'date' . $ROOM->date : $ROOM->day_night;
  $builder->BeginTalk('talk ' . $table_class, $id);
  if($RQ_ARGS->reverse_log) OutputTimeStamp($builder);
  //if($ROOM->watch_mode) $builder->AddSystemTalk($ROOM->date . print_r($ROOM->event, true));

  foreach($talk_list as $talk){
    switch($talk->scene){
    case 'day':
      if($ROOM->IsDay() || $talk->type == 'dummy_boy') break;
      $str .= $builder->RefreshTalk() . GenerateSceneChange($set_date);
      $ROOM->day_night = $talk->scene;
      $builder->BeginTalk('talk ' . $talk->scene);
      break;

    case 'night':
      if($ROOM->IsNight() || $talk->type == 'dummy_boy') break;
      $str .= $builder->RefreshTalk() . GenerateSceneChange($set_date);
      $ROOM->day_night = $talk->scene;
      $builder->BeginTalk('talk ' . $talk->scene);
      break;
    }
    OutputTalk($talk, $builder); //会話出力
  }

  if(! $RQ_ARGS->reverse_log) OutputTimeStamp($builder);
  $str .= $builder->RefreshTalk();

  if($flag_border_game && $RQ_ARGS->reverse_log){
    //突然死で勝敗が決定したケース
    if($set_date == $ROOM->last_date && $ROOM->IsDay()) $str .= GenerateVoteResult();

    $ROOM->date = $set_date + 1;
    $ROOM->day_night = 'day';
    $str .= GenerateDeadMan() . GenerateLastWords(); //遺言を出力
  }
  return $str;
}

//指定の日付の会話ログを出力
function OutputDateTalkLog($set_date, $set_location){
  echo GenerateDateTalkLog($set_date, $set_location);
}

//シーン切り替え時のログ出力
function GenerateSceneChange($set_date){
  global $RQ_ARGS, $ROOM;

  $str = '';
  if($RQ_ARGS->heaven_only) return $str;
  $ROOM->date = $set_date;
  if($RQ_ARGS->reverse_log){
    $ROOM->day_night = 'night';
    $str .= GenerateVoteResult() . GenerateDeadMan();
  }
  else{
    $str .= GenerateDeadMan() . GenerateVoteResult();
  }
  return $str;
}
