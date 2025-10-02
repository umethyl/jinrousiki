<?php
//-- 個別の村情報の基底クラス --//
class Room{
  var $id;
  var $name;
  var $comment;
  var $game_option = '';
  var $option_role = '';
  var $date;
  var $day_night;
  var $status;
  var $system_time;
  var $sudden_death;
  var $view_mode = false;
  var $dead_mode = false;
  var $heaven_mode = false;
  var $log_mode = false;
  var $watch_mode = false;
  var $single_view_mode = false;
  var $test_mode = false;

  function Room($request = NULL){ $this->__construct($request); }
  function __construct($request = NULL){
    if(is_null($request)) return;
    if($request->IsVirtualRoom()){
      $stack = $request->TestItems->test_room;
      $this->event->rows = $request->TestItems->event;
    }
    else{
      $stack = $this->LoadRoom($request->room_no);
    }
    foreach($stack as $name => $value) $this->$name = $value;
    $this->ParseOption();
  }

  //指定した部屋番号の DB 情報を取得する
  function LoadRoom($room_no){
    $query = 'SELECT room_no AS id, room_name AS name, room_comment AS comment, ' .
      'game_option, date, day_night, status FROM room WHERE room_no = ' . $room_no;
    $stack = FetchAssoc($query, true);
    if(count($stack) < 1) OutputActionResult('村番号エラー', '無効な村番号です: ' . $room_no);
    return $stack;
  }

  //option_role を追加ロードする
  function LoadOption(){
    global $RQ_ARGS;

    $option_role = $RQ_ARGS->IsVirtualRoom() ? $RQ_ARGS->TestItems->test_room['option_role'] :
      FetchResult($this->GetQueryHeader('room', 'option_role'));
    $this->option_role = new OptionManager($option_role);
    $this->option_list = array_merge($this->option_list, array_keys($this->option_role->options));
  }

  //発言を取得する
  function LoadTalk($heaven = false){
    global $GAME_CONF;

    $query = 'SELECT uname, sentence, font_type, location FROM talk' . $this->GetQuery(! $heaven) .
      ' AND location LIKE ' . ($heaven ? "'heaven'" : "'{$this->day_night}%'") .
      ' ORDER BY talk_id DESC';
    if(! $this->IsPlaying()) $query .= ' LIMIT 0, ' . $GAME_CONF->display_talk_limit;
    return FetchObject($query, 'Talk');
  }

  //シーンに合わせた投票情報を取得する
  function LoadVote($kick = false){
    global $RQ_ARGS;

    if($RQ_ARGS->IsVirtualRoom()){
      if(is_null($vote_list = $RQ_ARGS->TestItems->vote->{$this->day_night})) return NULL;
    }
    else{
      switch($this->day_night){
      case 'beforegame':
	if($kick){
	  $data = 'uname, target_uname';
	  $action = "situation = 'KICK_DO'";
	}
	else{
	  $data = 'uname, target_uname, situation';
	  $action = "situation = 'GAMESTART'";
	}
	break;

      case 'day':
	$data = 'uname, target_uname, vote_number';
	$action = "situation = 'VOTE_KILL' AND vote_times = " . $this->GetVoteTimes();
	break;

      case 'night':
	$data = 'uname, target_uname, situation';
	$action = "situation <> 'VOTE_KILL'";
	break;

      default:
	return NULL;
      }
      $vote_list = FetchAssoc("SELECT {$data} FROM vote {$this->GetQuery()} AND {$action}");
    }

    $stack = array();
    if($kick){
      foreach($vote_list as $list) $stack[$list['uname']][] = $list['target_uname'];
    }
    else{
      foreach($vote_list as $list){
	$uname = $list['uname'];
	unset($list['uname']);
	$stack[$uname] = $list;
      }
    }
    $this->vote = $stack;

    return count($this->vote);
  }

  //投票回数を DB から取得する
  function LoadVoteTimes($revote = false){
    $query = 'SELECT message FROM system_message' . $this->GetQuery() . ' AND type = ' .
      ($revote ?  "'RE_VOTE' ORDER BY message DESC" : "'VOTE_TIMES'");
    return (int)FetchResult($query);
  }

  //特殊イベント判定用の情報を DB から取得する
  function LoadEvent(){
    global $RQ_ARGS;

    if(! $this->IsPlaying()) return NULL;
    $date = $this->date;
    if($this->log_mode && ! $RQ_ARGS->reverse_log) $date--;
    $day   = $date;
    $night = $date - 1;
    if($this->IsDay() && ! ($this->watch_mode || $this->single_view_mode)) $day--;

    $query = $this->GetQueryHeader('system_message', 'message', 'type') .
      " AND((date = '{$day}'   AND type = 'VOTE_KILLED') OR " .
      "     (date = '{$night}' AND type = 'WOLF_KILLED'))";
    $this->event->rows = FetchAssoc($query);
  }

  //投票情報をコマンド毎に分割する
  function ParseVote(){
    $stack = array();
    foreach($this->vote as $uname => $list){
      $stack[$list['situation']][$uname] = $list['target_uname'];
    }
    return $stack;
  }

  //ゲームオプションの展開処理
  function ParseOption($join = false){
    $this->game_option = new OptionManager($this->game_option);
    $this->option_role = new OptionManager($this->option_role);
    $this->option_list = $join ?
      array_merge(array_keys($this->game_option->options),
		  array_keys($this->option_role->options)) :
      array_keys($this->game_option->options);

    if($this->IsRealTime()){
      $this->real_time->day   = $this->game_option->options['real_time'][0];
      $this->real_time->night = $this->game_option->options['real_time'][1];
    }
  }

  //今までの投票を全部削除
  function DeleteVote(){
    if(is_null($this->id)) return true;

    $query = 'DELETE FROM vote' . $this->GetQuery();
    if($this->IsDay())
      $query .= " AND situation = 'VOTE_KILL' AND vote_times = " . $this->GetVoteTimes();
    elseif($this->IsNight())
      $query .= ' AND situation <> ' . ($this->date == 1 ? "'CUPID_DO'" : "'VOTE_KILL'");

    SendQuery($query);
    OptimizeTable('vote');
    return true;
  }

  //共通クエリを取得
  function GetQuery($date = true, $count = NULL){
    $query = (is_null($count) ? '' : 'SELECT COUNT(uname) FROM ' . $count) .
      ' WHERE room_no = ' . $this->id;
    return $date ? $query . ' AND date = ' . $this->date : $query;
  }

  //共通クエリヘッダを取得
  function GetQueryHeader($data){
    $stack = func_get_args();
    $from = array_shift($stack);
    return 'SELECT ' . implode(', ', $stack) . ' FROM ' . $from . $this->GetQuery(false);
  }

  //投票回数を取得する
  function GetVoteTimes($revote = false){
    $value = $revote ? 'revote_times' : 'vote_times';
    if(is_null($this->$value)) $this->$value = $this->LoadVoteTimes($revote);
    return $this->$value;
  }

  //特殊イベント判定用の情報を取得する
  function GetEvent($force = false){
    if(! $this->IsPlaying()) return array();
    if($force) unset($this->event);
    if(is_null($this->event)) $this->LoadEvent();
    return $this->event->rows;
  }

  //オプション判定
  function IsOption($option){
    return in_array($option, $this->option_list);
  }

  //オプショングループ判定
  function IsOptionGroup($option){
    foreach($this->option_list as $this_option){
      if(strpos($this_option, $option) !== false) return true;
    }
    return false;
  }

  //リアルタイム制判定
  function IsRealTime(){
    return $this->IsOption('real_time');
  }

  //身代わり君使用判定
  function IsDummyBoy(){
    return $this->IsOption('dummy_boy');
  }

  //クイズ村判定
  function IsQuiz(){
    return $this->IsOption('quiz');
  }

  //村人置換村グループオプション判定
  function IsReplaceHumanGroup(){
    return $this->IsOption('replace_human') || $this->IsOption('full_mania') ||
      $this->IsOption('full_chiroptera') || $this->IsOption('full_cupid');
  }

  //闇鍋式希望制オプション判定
  function IsChaosWish(){
    return $this->IsOptionGroup('chaos') || $this->IsOption('duel') ||
      $this->IsOption('festival') || $this->IsReplaceHumanGroup();
  }

  //霊界公開判定
  function IsOpenCast(){
    global $USERS;

    if(is_null($this->open_cast)){ //未設定ならキャッシュする
      if($this->IsOption('not_open_cast')) //常時非公開
	$this->open_cast = false;
      elseif($this->IsOption('auto_open_cast')) //自動公開
	$this->open_cast = $USERS->IsOpenCast();
      else
	$this->open_cast = true; //常時公開
    }
    return $this->open_cast;
  }

  //情報公開判定
  function IsOpenData($virtual = false){
    global $SELF;
    return $SELF->IsDummyBoy() ||
      ($SELF->IsDead() && ! $this->single_view_mode && $this->IsOpenCast()) ||
      ($virtual ? $this->IsAfterGame() : ($this->IsFinished() && ! $this->single_view_mode));
  }

  //ゲーム開始前判定
  function IsBeforeGame(){
    return $this->day_night == 'beforegame';
  }

  //ゲーム中 (昼) 判定
  function IsDay(){
    return $this->day_night == 'day';
  }

  //ゲーム中 (夜) 判定
  function IsNight(){
    return $this->day_night == 'night';
  }

  //ゲーム終了後判定
  function IsAfterGame(){
    return $this->day_night == 'aftergame';
  }

  //ゲーム中判定 (仮想処理をする為、status では判定しない)
  function IsPlaying(){
    return $this->IsDay() || $this->IsNight();
  }

  //ゲーム終了判定
  function IsFinished(){
    return $this->status == 'finished';
  }

  //特殊イベント判定
  function IsEvent($type){
    return $this->event->$type;
  }

  //発言登録
  function Talk($sentence, $uname = '', $location = '', $font_type = NULL, $spend_time = 0){
    if($uname == '') $uname = 'system';
    if($location == '') $location = $this->day_night . ' system';
    if($this->test_mode){
      PrintData($sentence, 'Talk: ' . $uname . ': '. $location);
      return true;
    }

    $items  = 'room_no, date, location, uname, sentence, spend_time, time';
    $values = "{$this->id}, {$this->date}, '{$location}', '{$uname}', '{$sentence}', " .
      "{$spend_time}, UNIX_TIMESTAMP()";
    if(isset($font_type)){
      $items .= ', font_type';
      $values .= ", '{$font_type}'";
    }
    return InsertDatabase('talk', $items, $values);
  }

  //超過警告メッセージ登録
  function OvertimeAlert($str){
    $query = $this->GetQuery(true, 'talk') . " AND location = '{$this->day_night} system' " .
      "AND uname = 'system' AND sentence = '{$str}'";
    return FetchResult($query) == 0 ? $this->Talk($str) : false;
  }

  //システムメッセージ登録
  function SystemMessage($str, $type){
    global $RQ_ARGS;

    if($this->test_mode){
      PrintData($str, 'SystemMessage: ' . $type);
      if(is_array($RQ_ARGS->TestItems->system_message)){
	switch($type){
	case 'VOTE_KILL':
	case 'LAST_WORDS':
	  break;

	default:
	  $RQ_ARGS->TestItems->system_message[$this->date][$type][] = $str;
	  break;
	}
      }
      return true;
    }
    $items = 'room_no, date, message, type';
    $values = "{$this->id}, {$this->date}, '{$str}', '{$type}'";
    return InsertDatabase('system_message', $items, $values);
  }

  //最終更新時刻を更新
  function UpdateTime($commit = false){
    if($this->test_mode) return true;
    SendQuery('UPDATE room SET last_updated = UNIX_TIMESTAMP()' . $this->GetQuery(false));
    return $commit ? SendCommit() : true;
  }

  //夜にする
  function ChangeNight(){
    $this->day_night = 'night';
    SendQuery("UPDATE room SET day_night = '{$this->day_night}'" . $this->GetQuery(false));
    $this->Talk('NIGHT'); //夜がきた通知
  }

  //次の日にする
  function ChangeDate(){
    $this->date++;
    $this->day_night = 'day';
    SendQuery("UPDATE room SET date = {$this->date}, day_night = 'day'" . $this->GetQuery(false));

    //夜が明けた通知
    $this->Talk("MORNING\t" . $this->date);
    $this->SystemMessage(1, 'VOTE_TIMES'); //処刑投票のカウントを 1 に初期化(再投票で増える)
    $this->UpdateTime(); //最終書き込みを更新
    //$this->DeleteVote(); //今までの投票を全部削除

    $status = CheckVictory(); //勝敗のチェック
    SendCommit(); //一応コミット
    return $status;
  }

  //夜を飛ばす
  function SkipNight(){
    global $MESSAGE;

    if($this->IsEvent('skip_night')){
      AggregateVoteNight(true);
      $this->talk($MESSAGE->skip_night);
    }
  }

  //村のタイトルタグを生成
  function GenerateTitleTag(){
    return '<td class="room"><span>' . $this->name . '村</span>　[' . $this->id .
      '番地]<br>～' . $this->comment . '～</td>'."\n";
  }
}

class RoomDataSet{
  var $rows = array();

  function LoadFinishedRoom($room_no){
    $query = <<<EOF
SELECT room_no AS id, room_name AS name, room_comment AS comment, date, game_option,
  option_role, max_user, victory_role, establish_time, start_time, finish_time,
  (SELECT COUNT(user_no) FROM user_entry WHERE user_entry.room_no = room.room_no
   AND user_entry.user_no > 0) AS user_count
FROM room WHERE status = 'finished' AND room_no = {$room_no}
EOF;
    return FetchObject($query, 'Room', true);
  }

  function LoadEntryUser($room_no){
    $query = <<<EOF
SELECT room_no AS id, date, day_night, status, max_user FROM room WHERE room_no = {$room_no}
EOF;
    return FetchObject($query, 'Room', true);
  }

  function LoadEntryUserPage($room_no){
    $query = <<<EOF
SELECT room_no AS id, room_name AS name, room_comment AS comment, status,
  game_option, option_role FROM room WHERE room_no = {$room_no}
EOF;
    return FetchObject($query, 'Room', true);
  }

  function LoadClosedRooms($room_order, $limit_statement) {
    $sql = <<<SQL
SELECT room.room_no AS id, room.room_name AS name, room.room_comment AS comment,
    room.date AS room_date AS date, room.game_option AS room_game_option,
    room.option_role AS room_option_role, room.max_user AS room_max_user, users.room_num_user,
    room.victory_role AS room_victory_role, room.establish_time, room.start_time, room.finish_time
FROM room
    LEFT JOIN (SELECT room_no, COUNT(user_no) AS room_num_user FROM user_entry GROUP BY room_no) users
	USING (room_no)
WHERE status = 'finished'
ORDER BY room_no {$room_order}
{$limit_statement}
SQL;
    return self::__load($sql);
  }

  function LoadOpeningRooms($class = 'RoomDataSet') {
    $sql = <<<SQL
SELECT room_no AS id, room_name AS name, room_comment AS comment, game_option, option_role, max_user, status
FROM room
WHERE status <> 'finished'
ORDER BY room_no DESC
SQL;
    return self::__load($sql);
  }

  function __load($sql, $class = 'Room') {
    $result = new RoomDataSet();
    if(($q_rooms = mysql_query($sql)) !== false){
      while(($object = mysql_fetch_object($q_rooms, $class)) !== false){
        $object->ParseOption();
        $result->rows[] = $object;
      }
    }
    else{
      die('村一覧の取得に失敗しました');
    }
    return $result;
  }
}
