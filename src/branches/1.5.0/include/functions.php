<?php
//-- セキュリティ関連 --//
//リファラチェック
function CheckReferer($page, $white_list = NULL){
  global $SERVER_CONF;

  if(is_array($white_list)){ //ホワイトリストチェック
    foreach($white_list as $host){
      if(strpos($_SERVER['REMOTE_ADDR'], $host) === 0) return false;
    }
  }
  $url = $SERVER_CONF->site_root . $page;
  return strncmp(@$_SERVER['HTTP_REFERER'], $url, strlen($url)) != 0;
}

//ブラックリストチェック
function CheckBlackList(){
  global $ROOM_CONF;

  $addr = $_SERVER['REMOTE_ADDR'];
  $host = gethostbyaddr($addr);
  foreach(array('white' => false, 'black' => true) as $type => $flag){
    foreach($ROOM_CONF->{$type . '_list_ip'} as $ip){
      if(strpos($addr, $ip) === 0) return $flag;
    }
    $list = $ROOM_CONF->{$type . '_list_host'};
    if(isset($list) && preg_match($list, $host)) return $flag;
  }
  return false;
}

/**
 * 実行環境にダメージを与える可能性がある値が含まれているかどうか検査します。
 * @param  : mixed   : $value 検査対象の変数
 * @param  : boolean : $found 疑わしい値が存在しているかどうかを示す値。
                       この値がtrueの場合、強制的に詳細なスキャンが実行されます。
 * @return : boolean : 危険な値が発見された場合true、それ以外の場合false
 */
function FindDangerValue($value, $found = false){
  if($found || (strpos(str_replace('.', '', serialize($value)), '22250738585072011') !== false)){
    //文字列の中に問題の数字が埋め込まれているケースを排除する
    if(is_array($value)){
      foreach($value as $item){
        if(FindDangerValue($item, true)) return true;
      }
    }
    else{
      $item = strval($value);
      $matches = '';
      if(preg_match('/^([0.]*2[0125738.]{15,16}1[0.]*)e(-[0-9]+)$/i', $item, $matches)){
        $exp = intval($matches[2]) + 1;
        if(2.2250738585072011e-307 === floatval("{$matches[1]}e{$exp}")) return true;
      }
    }
  }
  return false;
}

//-- DB 関連 --//
//DB 問い合わせ処理のラッパー関数
function SendQuery($query, $commit = false){
  if(($sql = mysql_query($query)) !== false) return $commit ? SendCommit() : $sql;
  $backtrace = debug_backtrace(); //バックトレースを取得

  //SendQuery() を call した関数と位置を取得して「SQLエラー」として返す
  $trace_stack = array_shift($backtrace);
  $stack = array($trace_stack['line'], $query);
  $trace_stack = array_shift($backtrace);
  array_unshift($stack, $trace_stack['function'] . '()');
  PrintData(implode(': ', $stack), 'SQLエラー');

  foreach($backtrace as $trace_stack){ //呼び出し元があるなら追加で出力
    $stack = array($trace_stack['function'] . '()', $trace_stack['line']);
    PrintData(implode(': ', $stack), 'Caller');
  }
  return false;
}

//コミット処理
function SendCommit(){ return mysql_query('COMMIT'); }

//DB から単体の値を取得する処理のラッパー関数
function FetchResult($query){
  if(($sql = SendQuery($query)) === false) return false;
  $data = mysql_num_rows($sql) > 0 ? mysql_result($sql, 0, 0) : false;
  mysql_free_result($sql);
  return $data;
}

//DB から該当するデータの行数を取得する処理のラッパー関数
function FetchCount($query){
  if(($sql = SendQuery($query)) === false) return false;
  $data = mysql_num_rows($sql);
  mysql_free_result($sql);
  return $data;
}

//DB から一次元の配列を取得する処理のラッパー関数
function FetchArray($query){
  $array = array();
  if(($sql = SendQuery($query)) === false) return $array;
  $count = mysql_num_rows($sql);
  for($i = 0; $i < $count; $i++) $array[] = mysql_result($sql, $i, 0);
  mysql_free_result($sql);
  return $array;
}

//DB から連想配列を取得する処理のラッパー関数
function FetchAssoc($query, $shift = false){
  $array = array();
  if(($sql = SendQuery($query)) === false) return $array;
  while(($stack = mysql_fetch_assoc($sql)) !== false) $array[] = $stack;
  mysql_free_result($sql);
  return $shift ? array_shift($array) : $array;
}

//DB からオブジェクト形式の配列を取得する処理のラッパー関数
function FetchObject($query, $class, $shift = false){
  $array = array();
  if(($sql = SendQuery($query)) === false) return $array;
  while(($stack = mysql_fetch_object($sql, $class)) !== false) $array[] = $stack;
  mysql_free_result($sql);
  return $shift ? array_shift($array) : $array;
}

//talk 専用 DB 取得関数 (負荷実験テスト用)
function FetchTalk($query, $class, $reverse){
  global $GAME_CONF, $ROOM;

  $stack = array();
  foreach(FetchObject($query, $class) as $object) $stack[$object->talk_id] = $object;
  if(! $reverse) krsort($stack);
  if(! $ROOM->IsPlaying() && $GAME_CONF->display_talk_limit > 0){
    $stack = array_slice($stack, 0, $GAME_CONF->display_talk_limit);
  }
  return $stack;
}

//データベース登録のラッパー関数
function InsertDatabase($table, $items, $values){
  return SendQuery("INSERT INTO {$table}({$items}) VALUES({$values})", true);
}

//ユーザ登録処理
function InsertUser($room_no, $uname, $handle_name, $password, $user_no = 1, $icon_no = 0,
		    $profile = NULL, $sex = 'male', $role = NULL, $session_id = NULL){
  global $MESSAGE;

  $crypt_password = CryptPassword($password);
  $items = 'room_no, user_no, uname, handle_name, icon_no, sex, password, live, profile, last_words';
  $values = "{$room_no}, {$user_no}, '{$uname}', '{$handle_name}', {$icon_no}, '{$sex}', " .
    "'{$crypt_password}', 'live', ";
  if($uname == 'dummy_boy'){
    $values .= "'{$MESSAGE->dummy_boy_comment}', '{$MESSAGE->dummy_boy_last_words}'";
  }
  else{
    $ip_address = $_SERVER['REMOTE_ADDR']; //ユーザのIPアドレスを取得
    $items .= ', role, session_id, ip_address, last_load_day_night';
    $values .= "'{$profile}', '', '{$role}', '{$session_id}', '{$ip_address}', 'beforegame'";
  }
  return InsertDatabase('user_entry', $items, $values);
}

//テーブルを排他的ロック
function LockTable($type = NULL){
  $stack = array('room', 'user_entry', 'talk', 'vote');
  switch($type){
  case 'game':
    array_push($stack, 'system_message', 'user_icon');
    break;

  case 'icon':
    $stack = array('user_icon');
    break;

  case 'icon_delete':
    $stack = array('user_icon', 'user_entry');
    break;
  }

  $query_stack = array();
  foreach($stack as $table) $query_stack[] = $table . ' WRITE';
  return ! SendQuery('LOCK TABLES ' . implode(', ', $query_stack));
}

//テーブルロック解除
function UnlockTable(){ return SendQuery('UNLOCK TABLES'); }

//部屋削除
function DeleteRoom($room_no){
  $header = 'DELETE FROM ';
  $footer = ' WHERE room_no = ' . $room_no;
  foreach(array('room', 'user_entry', 'talk', 'system_message', 'vote') as $name){
    SendQuery($header . $name . $footer);
  }
}

//DB 最適化
function OptimizeTable($name = NULL){
  $query = is_null($name) ? 'room, user_entry, talk, system_message, vote' : $name;
  SendQuery('OPTIMIZE TABLE ' . $query, true);
}

//-- 日時関連 --//
//TZ 補正をかけた時刻を返す (環境変数 TZ を変更できない環境想定？)
function TZTime(){
  global $SERVER_CONF;

  $time = time();
  if($SERVER_CONF->adjust_time_difference) $time += $SERVER_CONF->offset_seconds;
  return $time;
  /* // ミリ秒対応のコード(案) 2009-08-08 enogu
     return preg_replace('/([0-9]+)( [0-9]+)?/i', '$$2.$$1', microtime()) + $SERVER_CONF->offset_seconds; // ミリ秒
     対応のコード(案) 2009-08-08 enogu
  */
}

//TZ 補正をかけた日時を返す
function TZDate($format, $time){
  global $SERVER_CONF;
  return $SERVER_CONF->adjust_time_difference ? gmdate($format, $time) : date($format, $time);
}

//TIMESTAMP 形式の時刻を変換する
function ConvertTimeStamp($time_stamp, $convert_date = true){
  global $SERVER_CONF;

  $time = strtotime($time_stamp);
  if($SERVER_CONF->adjust_time_difference) $time += $SERVER_CONF->offset_seconds;
  return $convert_date ? TZDate('Y/m/d (D) H:i:s', $time) : $time;
}

//時間(秒)を変換する
function ConvertTime($seconds){
  $sentence = '';
  $hours    = 0;
  $minutes  = 0;

  if($seconds >= 60){
    $minutes = floor($seconds / 60);
    $seconds %= 60;
  }
  if($minutes >= 60){
    $hours = floor($minutes / 60);
    $minutes %= 60;
  }

  if($hours   > 0) $sentence .= $hours   . '時間';
  if($minutes > 0) $sentence .= $minutes . '分';
  if($seconds > 0) $sentence .= $seconds . '秒';
  return $sentence;
}

//-- 文字処理関連 --//
//POSTされたデータの文字コードを統一する
function EncodePostData(){
  global $SERVER_CONF;

  foreach($_POST as $key => $value){
    $encode = mb_detect_encoding($value, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
    if($encode != '' && $encode != $SERVER_CONF->encode){
      $_POST[$key] = mb_convert_encoding($value, $SERVER_CONF->encode, $encode);
    }
  }
}

//特殊文字のエスケープ処理
//htmlentities() を使うと文字化けを起こしてしまうようなので敢えてべたに処理
function EscapeStrings(&$str, $trim = true){
  if(is_array($str)){
    $result = array();
    foreach($str as $item) $result[] = EscapeStrings($item);
    return $result;
  }
  if(get_magic_quotes_gpc()) $str = stripslashes($str); // \ を自動でつける処理系対策
  // $str = htmlentities($str, ENT_QUOTES); //UTF に移行したら機能する？
  $replace_list = array('&' => '&amp;', '<' => '&lt;', '>' => '&gt;',
			'\\' => '&yen;', '"' => '&quot;', "'" => '&#039;');
  $str = strtr($str, $replace_list);
  $str = $trim ? trim($str) : str_replace(array("\r\n", "\r", "\n"), "\n", $str);
  return $str;
}

//トリップ変換
/*
  変換テスト結果＠2ch (2009/07/26)
  [入力文字列] => [変換結果] (ConvetTrip()の結果)
  test#test                     => test ◆.CzKQna1OU (test◆.CzKQna1OU)
  テスト#テスト                 => テスト ◆SQ2Wyjdi7M (テスト◆SQ2Wyjdi7M)
  てすと＃てすと                => てすと ◆ZUNa78GuQc (てすと◆ZUNa78GuQc)
  てすとテスト#てすと＃テスト   => てすとテスト ◆TBYWAU/j2qbJ (てすとテスト◆sXitOlnF0g)
  テストてすと＃テストてすと    => テストてすと ◆RZ9/PhChteSA (テストてすと◆XuUGgmt7XI)
  テストてすと＃テストてすと#   => テストてすと ◆rtfFl6edK5fK (テストてすと◆XuUGgmt7XI)
  テストてすと＃テストてすと＃  => テストてすと ◆rtfFl6edK5fK (テストてすと◆XuUGgmt7XI)
*/
function ConvertTrip($str){
  global $SERVER_CONF, $GAME_CONF;

  if($GAME_CONF->trip){
    if(get_magic_quotes_gpc()) $str = stripslashes($str); // \ を自動でつける処理系対策
    //トリップ関連のキーワードを置換
    $str = str_replace(array('◆', '＃'), array('◇', '#'), $str);
    if(($trip_start = mb_strpos($str, '#')) !== false){ //トリップキーの位置を検索
      $name = mb_substr($str, 0, $trip_start);
      $key  = mb_substr($str, $trip_start + 1);
      //PrintData("{$trip_start}, name: {$name}, key: {$key}", 'Trip Start'); //テスト用
      $key = mb_convert_encoding($key, 'SJIS', $SERVER_CONF->encode); //文字コードを変換

      if($GAME_CONF->trip_2ch && strlen($key) >= 12){
	$trip_mark = substr($key, 0, 1);
	if($trip_mark == '#' || $trip_mark == '$'){
	  if(preg_match('|^#([[:xdigit:]]{16})([./0-9A-Za-z]{0,2})$|', $key, $stack)){
	    $trip = substr(crypt(pack('H*', $stack[1]), "{$stack[2]}.."), -12);
	  }
	  else{
	    $trip = '???';
	  }
	}
	else{
	  $trip = str_replace('+', '.', substr(base64_encode(sha1($key, true)), 0, 12));
	}
      }
      else{
	$salt = substr($key . 'H.', 1, 2);

	//$salt =~ s/[^\.-z]/\./go; にあたる箇所
	$pattern = '/[\x00-\x20\x7B-\xFF]/';
	$salt = preg_replace($pattern, '.', $salt);

	//特殊文字の置換
	$from_list = array(':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`');
	$to_list   = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'a', 'b', 'c', 'd', 'e', 'f');
	$salt = str_replace($from_list, $to_list, $salt);

	$trip = substr(crypt($key, $salt), -10);
      }
      $str = $name . '◆' . $trip;
    }
    //PrintData($str, 'Result'); //テスト用
  }
  elseif(strpos($str, '#') !== false || strpos($str, '＃') !== false){
    $sentence = 'トリップは使用不可です。<br>' . "\n" . '"#" 又は "＃" の文字も使用不可です。';
    OutputActionResult('村人登録 [入力エラー]', $sentence);
  }

  return EscapeStrings($str); //特殊文字のエスケープ
}

//改行コードを <br> に変換する (PHP5.3 以下の nl2br() だと <br /> 固定なので HTML 4.01 だと不向き)
function LineToBR(&$str){
  $str = str_replace("\n", '<br>', $str);
  return $str;
}

//パスワード暗号化
function CryptPassword($raw_password){
  global $SERVER_CONF;
  return sha1($SERVER_CONF->salt . $raw_password);
}

//-- 出力関連 --//
//変数表示関数 (デバッグ用)
function PrintData($data, $name = NULL){
  $str = is_null($name) ? '' : $name . ': ';
  $str .= (is_array($data) || is_object($data)) ? print_r($data, true) : $data;
  echo $str . '<br>';
}

//村情報のRSSファイルを更新する
function OutputSiteSummary(){
  global $INIT_CONF;
  $INIT_CONF->LoadFile('feedengine');

  $filename = 'rss/rooms.rss';
  $rss = FeedEngine::Initialize('site_summary.php');
  $rss->Build();

  $fp = fopen(dirname(__FILE__)."/{$filename}", 'w');
  fwrite($fp, $rss->Export($filename));
  fflush($fp);
  fclose($fp);

  return $rss;
}

//ページ送り用のリンクタグを出力する
function OutputPageLink($CONFIG){
  $page_count = ceil($CONFIG->count / $CONFIG->view);
  $start_page = $CONFIG->current== 'all' ? 1 : $CONFIG->current;
  if($page_count - $CONFIG->current < $CONFIG->page){
    $start_page = $page_count - $CONFIG->page + 1;
    if($start_page < 1) $start_page = 1;
  }
  $end_page = $CONFIG->current + $CONFIG->page - 1;
  if($end_page > $page_count) $end_page = $page_count;

  $url_stack = array('[' . (is_null($CONFIG->title) ? 'Page' : $CONFIG->title) . ']');
  $url_header = '<a href="' . $CONFIG->url . '.php?';

  if($page_count > $CONFIG->page && $CONFIG->current> 1){
    $url_stack[] = GeneratePageLink($CONFIG, 1, '[1]...');
    $url_stack[] = GeneratePageLink($CONFIG, $start_page - 1, '&lt;&lt;');
  }

  for($page_number = $start_page; $page_number <= $end_page; $page_number++){
    $url_stack[] = GeneratePageLink($CONFIG, $page_number);
  }

  if($page_number <= $page_count){
    $url_stack[] = GeneratePageLink($CONFIG, $page_number, '&gt;&gt;');
    $url_stack[] = GeneratePageLink($CONFIG, $page_count, '...[' . $page_count . ']');
  }
  $url_stack[] = GeneratePageLink($CONFIG, 'all');

  if($CONFIG->url == 'old_log'){
    $list = $CONFIG->option;
    $list['page'] = 'page=' . $CONFIG->current;
    $list['reverse'] = 'reverse=' . ($CONFIG->is_reverse ? 'off' : 'on');
    $url_stack[] = '[表示順]';
    $url_stack[] = $CONFIG->is_reverse ? '新↓古' : '古↓新';

    $url = $url_header . implode('&', $list) . '">';
    $name = ($CONFIG->is_reverse xor $CONFIG->reverse) ? '元に戻す' : '入れ替える';
    $url_stack[] =  $url . $name . '</a>';
  }
  echo implode(' ', $url_stack);
}

//ページ送り用のリンクタグを作成する
function GeneratePageLink($CONFIG, $page, $title = NULL){
  if($page == $CONFIG->current) return '[' . $page . ']';
  $option = (is_null($CONFIG->page_type) ? 'page' : $CONFIG->page_type) . '=' . $page;
  $list = $CONFIG->option;
  array_unshift($list, $option);
  $url = $CONFIG->url . '.php?' . implode('&', $list);
  $attributes = array();
  if(isset($CONFIG->attributes)){
    foreach($CONFIG->attributes as $attr => $value){
      $attributes[] = $attr . '="'. eval($value) . '"';
    }
  }
  $attrs = implode(' ', $attributes);
  if(is_null($title)) $title = '[' . $page . ']';
  return '<a href="' . $url . '" ' . $attrs . '>' . $title . '</a>';
}

//ログへのリンクを生成
function GenerateLogLink($url, $watch = false, $header = '', $footer = ''){
  $str = <<<EOF
{$header} <a target="_top" href="{$url}"{$footer}>正</a>
<a target="_top" href="{$url}&reverse_log=on"{$footer}>逆</a>
<a target="_top" href="{$url}&heaven_talk=on"{$footer}>霊</a>
<a target="_top" href="{$url}&reverse_log=on&heaven_talk=on"{$footer}>逆&amp;霊</a>
<a target="_top" href="{$url}&heaven_only=on"{$footer} >逝</a>
<a target="_top" href="{$url}&reverse_log=on&heaven_only=on"{$footer}>逆&amp;逝</a>
EOF;

  if($watch){
    $str .= <<<EOF

<a target="_top" href="{$url}&watch=on"{$footer}>観</a>
<a target="_top" href="{$url}&watch=on&reverse_log=on"{$footer}>逆&amp;観</a>
EOF;
  }
  return $str;
}

//ゲームオプションの画像タグを作成する
function GenerateGameOptionImage($game_option, $option_role = ''){
  global $CAST_CONF, $ROOM_IMG, $GAME_OPT_MESS;

  $stack = new OptionParser($game_option . ' ' . $option_role);
  //PrintData($stack); //テスト用
  $str = '';
  $display_order_list = array(
    'wish_role', 'real_time', 'dummy_boy', 'gm_login', 'gerd', 'wait_morning', 'open_vote',
    'seal_message', 'open_day', 'not_open_cast', 'auto_open_cast', 'poison', 'assassin', 'wolf',
    'boss_wolf', 'poison_wolf', 'possessed_wolf', 'sirius_wolf', 'fox', 'child_fox', 'cupid',
    'medium', 'mania', 'decide', 'authority', 'detective', 'liar', 'gentleman', 'deep_sleep',
    'blinder', 'mind_open', 'sudden_death', 'perverseness', 'critical', 'joker', 'death_note',
    'weather', 'festival', 'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire',
    'full_chiroptera', 'full_mania', 'full_unknown_mania', 'change_common', 'change_hermit_common',
    'change_mad', 'change_fanatic_mad', 'change_whisper_mad', 'change_immolate_mad', 'change_cupid',
    'change_mind_cupid', 'change_triangle_cupid', 'change_angel', 'duel', 'gray_random', 'quiz',
    'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'topping', 'boost_rate', 'chaos_open_cast',
    'chaos_open_cast_camp', 'chaos_open_cast_role', 'secret_sub_role', 'no_sub_role',
    'sub_role_limit_easy', 'sub_role_limit_normal', 'sub_role_limit_hard');

  foreach($display_order_list as $option){
    if(! $stack->Exists($option) || $GAME_OPT_MESS->$option == '') continue;
    $footer = '';
    $sentence = $GAME_OPT_MESS->$option;
    if(property_exists($CAST_CONF, $option) && is_int($CAST_CONF->$option)){
      $sentence .= '(' . $CAST_CONF->$option . '人～)';
    }
    switch($option){
    case 'real_time':
      $day   = $stack->options[$option][0];
      $night = $stack->options[$option][1];
      $sentence .= "　昼： {$day} 分　夜： {$night} 分";
      $footer = '['. $day . '：' . $night . ']';
      break;

    case 'topping':
    case 'boost_rate':
      $type = $stack->options[$option][0];
      $sentence .= '(Type' . $GAME_OPT_MESS->{$option . '_' . $type} . ')';
      $footer = '['. strtoupper($type) . ']';
      break;
    }
    $str .= $ROOM_IMG->Generate($option, $sentence) . $footer;
  }
  return $str;
}

//ゲームオプションの画像タグを作成する (最大人数用)
function GenerateMaxUserImage($number){
  global $ROOM_CONF, $ROOM_IMG;
  return in_array($number, $ROOM_CONF->max_user_list) && $ROOM_IMG->Exists("max{$number}") ?
    $ROOM_IMG->Generate("max{$number}", "最大{$number}人") : "(最大{$number}人)";
}

//共通 HTML ヘッダ生成
function GenerateHTMLHeader($title, $css = 'action'){
  global $SERVER_CONF;

  $css_path = JINRO_CSS . '/' . $css . '.css';
  return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset={$SERVER_CONF->encode}">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>{$title}</title>
<link rel="stylesheet" href="{$css_path}">

EOF;
}

//共通 HTML ヘッダ出力
function OutputHTMLHeader($title, $css = 'action'){ echo GenerateHTMLHeader($title, $css); }

//結果ページ HTML ヘッダ出力
function OutputActionResultHeader($title, $url = ''){
  global $ROOM;

  $str = GenerateHTMLHeader($title);
  if($url != '') $str .= '<meta http-equiv="Refresh" content="1;URL='.$url.'">'."\n";
  if(is_object($ROOM)) $str .= $ROOM->GenerateCSS();
  echo $str . '</head><body>'."\n";
}

//結果ページ出力
function OutputActionResult($title, $body, $url = '', $unlock = false){
  global $DB_CONF;

  $DB_CONF->Disconnect($unlock); //DB 接続解除

  OutputActionResultHeader($title, $url);
  echo $body . "\n";
  OutputHTMLFooter(true);
}

//HTML フッタ出力
function OutputHTMLFooter($exit = false){
  global $DB_CONF;

  $DB_CONF->Disconnect(); //DB 接続解除
  echo '</body></html>'."\n";
  if($exit) exit;
}

//共有フレーム HTML ヘッダ出力
function OutputFrameHTMLHeader($title){
  global $SERVER_CONF;

  echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset={$SERVER_CONF->encode}">
<title>{$title}</title>
</head>

EOF;
}

//フレーム HTML フッタ出力
function OutputFrameHTMLFooter(){
  echo <<<EOF
<noframes><body>
フレーム非対応のブラウザの方は利用できません。
</body></noframes>
</frameset></html>

EOF;
}
