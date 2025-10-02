<?php
//-- データベース処理の基底クラス --//
class DatabaseConfigBase{
  //データベース接続
  /*
    $header : HTML ヘッダ出力情報 [true: 出力済み / false: 未出力]
    $exit   : エラー処理 [true: exit を返す / false で終了]
  */
  function Connect($header = false, $exit = true){
    //データベースサーバにアクセス
    if(! ($db_handle = mysql_connect($this->host, $this->user, $this->password))){
      return $this->OutputError($header, $exit, 'MySQL サーバ接続失敗', $this->host);
    }

    mysql_set_charset($this->encode); //文字コード設定
    if(! mysql_select_db($this->name, $db_handle)){ //データベース接続
      return $this->OutputError($header, $exit, 'データベース接続失敗', $this->name);
    }
    if($this->encode == 'utf8') mysql_query('SET NAMES utf8');
    return $this->db_handle = $db_handle; //成功したらハンドルを返して処理終了
  }

  //データベースとの接続を閉じる
  function Disconnect($unlock = false){
    if(is_null($this->db_handle)) return;

    if($unlock) UnlockTable(); //ロック解除
    mysql_close($this->db_handle);
    unset($this->db_handle); //ハンドルをクリア
  }

  //エラー出力 ($header, $exit は Connect() 参照)
  function OutputError($header, $exit, $title, $type){
    $str = $title . ': ' . $type; //エラーメッセージ作成
    if($header){
      echo '<font color="#FF0000">' . $str . '</font><br>';
      if($exit) OutputHTMLFooter($exit);
      return false;
    }
    OutputActionResult($title, $str);
  }

  //データベース名変更
  function ChangeName($id){
    if(is_null($name = $this->name_list[$id - 1])) return;
    $this->name = $name;
  }
}

//-- セッション管理クラス --//
class Session{
  var $id;
  var $user_no;

  function Session(){ $this->__construct(); }
  function __construct(){
    session_start();
    $this->Set();
  }

  //ID セット
  function Set(){
    return $this->id = session_id();
  }

  //ID リセット
  function Reset(){
    //PHP のバージョンが古い場合は関数がないので自前で処理する
    if(function_exists('session_regenerate_id')){
      session_regenerate_id();
    }
    else{
      $id = serialize($_SESSION);
      session_destroy();
      session_id(md5(uniqid(rand(), 1)));
      session_start();
      $_SESSION = unserialize($id);
    }
    return $this->Set();
  }

  //ID 取得
  function Get($uniq = false){
    return $uniq ? $this->GetUniq() : $this->id;
  }

  //DB に登録されているセッション ID と被らないようにする
  function GetUniq(){
    $query = 'SELECT COUNT(room_no) FROM user_entry WHERE session_id = ';
    do{
      $this->Reset();
    }while(FetchResult($query ."'{$this->id}'") > 0);
    return $this->id;
  }

  //認証したユーザの ID 取得
  function GetUser(){
    return $this->user_no;
  }

  //認証
  function Certify($exit = true){
    global $RQ_ARGS;
    //$ip_address = $_SERVER['REMOTE_ADDR']; //IPアドレス認証は現在は行っていない

    //セッション ID による認証
    $query = "SELECT user_no FROM user_entry WHERE room_no = {$RQ_ARGS->room_no}" .
      " AND session_id = '{$this->id}' AND live <> 'kick'";
    $stack = FetchArray($query);
    if(count($stack) == 1){
      $this->user_no = $stack[0];
      return true;
    }

    if($exit) $this->OutputError(); //エラー処理
    return false;
  }

  //認証 (game_play 専用)
  function CertifyGamePlay(){
    global $RQ_ARGS;

    if($this->Certify(false)) return true;

    //村が存在するなら観戦ページにジャンプする
    if(FetchResult("SELECT COUNT(room_no) FROM room WHERE room_no = {$RQ_ARGS->room_no}") > 0){
      $url = 'game_view.php?room_no=' . $RQ_ARGS->room_no;
      $title = '観戦ページにジャンプ';
      $body .= "観戦ページに移動します。<br>\n" .
	'切り替わらないなら <a href="' . $url . '" target="_top">ここ</a> 。'."\n" .
	'<script type="text/javascript"><!--'."\n" .
	'if(top != self){ top.location.href = self.location.href; }'."\n" .
	'--></script>'."\n";
      OutputActionResult($title, $body, $url);
    }
    else{
      $this->OutputError();
    }
  }

  //エラー出力
  function OutputError(){
    $title = 'セッション認証エラー';
    $str = $title . '：<a href="./" target="_top">トップページ</a>からログインしなおしてください';
    OutputActionResult($title, $str);
  }
}

//-- クッキーデータのロード処理 --//
class CookieDataSet{
  var $day_night;  //夜明け
  var $objection;  //「異議あり」の情報
  var $vote_times; //投票回数
  var $user_count; //参加人数

  function CookieDataSet(){ $this->__construct(); }
  function __construct(){
    $this->day_night  = $_COOKIE['day_night'];
    $this->objection  = $_COOKIE['objection'];
    $this->vote_times = (int)$_COOKIE['vote_times'];
    $this->user_count = (int)$_COOKIE['user_count'];
  }
}

//-- 外部リンク生成の基底クラス --//
class ExternalLinkBuilder{
  var $time = 2; //タイムアウト時間 (秒)

  //サーバ通信状態チェック
  function CheckConnection($url){
    $url_stack = explode('/', $url);
    $this->host = $url_stack[2];
    if(! ($io = @fsockopen($this->host, 80, $status, $str, $this->time))) return false;

    stream_set_timeout($io, $this->time);
    fwrite($io, "GET / HTTP/1.1\r\nHost: {$host}\r\nConnection: Close\r\n\r\n");
    $data = fgets($io, 128);
    $stream_stack = stream_get_meta_data($io);
    fclose($io);
    //PrintData($data, 'Connection');
    return ! $stream_stack['timed_out'];
  }

  //HTML タグ生成
  function Generate($title, $data){
    return <<<EOF
<fieldset>
<legend>{$title}</legend>
<div class="game-list"><dl>{$data}</dl></div>
</fieldset>

EOF;
  }

  //BBS リンク生成
  function GenerateBBS($data){
    $title = '<a href="' . $this->view_url . $this->thread . 'l50' . '">告知スレッド情報</a>';
    return $this->Generate($title, $data);
  }

  //外部村リンク生成
  function GenerateSharedServerRoom($name, $url, $data){
    return $this->Generate('ゲーム一覧 (<a href="' . $url . '">' . $name . '</a>)', $data);
  }
}

//-- 掲示板情報取得の基底クラス --//
class BBSConfigBase extends ExternalLinkBuilder{
  function Output(){
    global $SERVER_CONF;

    if($this->disable) return;
    if(! $this->CheckConnection($this->raw_url)){
      echo $this->GenerateBBS($this->host . ": Connection timed out ({$this->time} seconds)");
      return;
    }

    //スレッド情報を取得
    $url = $this->raw_url . $this->thread . 'l' . $this->size . 'n';
    if(($data = @file_get_contents($url)) == '') return;
    //PrintData($data, 'Data'); //テスト用
    if($this->encode != $SERVER_CONF->encode){
      $data = mb_convert_encoding($data, $SERVER_CONF->encode, $this->encode);
    }
    $str = '';
    $str_stack = explode("\n", $data);
    array_pop($str_stack);
    foreach($str_stack as $res){
      $res_stack = explode('<>', $res);
      $str .= '<dt>' . $res_stack[0] . ' : <font color="#008800"><b>' . $res_stack[1] .
	'</b></font> : ' . $res_stack[3] . ' ID : ' . $res_stack[6] . '</dt>' . "\n" .
	'</dt><dd>' . $res_stack[4] . '</dd>';
    }
    echo $this->GenerateBBS($str);
  }
}

//ゲームプレイ時のアイコン表示設定の基底クラス --//
class IconConfigBase{
  //初期設定
  var $path   = 'user_icon'; //ユーザアイコンのパス
  var $dead   = 'grave.gif'; //死者
  var $wolf   = 'wolf.gif';  //狼
  var $width  = 45; //表示サイズ(幅)
  var $height = 45; //表示サイズ(高さ)
  var $title;
  var $page_type;

  function IconConfigBase(){ $this->__construct(); }
  function __construct(){
    $this->path = JINRO_ROOT . '/' . $this->path;
    $this->dead = JINRO_IMG  . '/' . $this->dead;
    $this->wolf = JINRO_IMG  . '/' . $this->wolf;
    $this->tag  = $this->GenerateTag();
  }

  function GenerateTag(){
    return ' width="' . $this->width . '" height="' . $this->height . '"';
  }
}

//-- ユーザアイコン管理の基底クラス --//
class UserIconBase{
  // アイコンの文字数
  function IconNameMaxLength(){
    return '半角で' . $this->name . '文字、全角で' . floor($this->name / 2) . '文字まで';
  }

  // アイコンのファイルサイズ
  function IconFileSizeMax(){
    return ($this->size > 1024 ? floor($this->size / 1024) . 'k' : $this->size) . 'Byte まで';
  }

  // アイコンの縦横のサイズ
  function IconSizeMax(){
    return '幅' . $this->width . 'ピクセル × 高さ' . $this->height . 'ピクセルまで';
  }
}

//-- 画像管理の基底クラス --//
class ImageManager{
  //画像のファイルパス取得
  function GetPath($name){
    return JINRO_IMG . '/' . $this->path . '/' . $name . '.' . $this->extension;
  }

  //画像の存在確認
  function Exists($name){
    return file_exists($this->GetPath($name));
  }

  //画像タグ生成
  function Generate($name, $alt = NULL, $table = false){
    $str = '<img';
    if($this->class != '') $str .= ' class="' . $this->class . '"';
    $str .= ' src="' . $this->GetPath($name) . '"';
    if(isset($alt)){
      EscapeStrings($alt);
      $str .= ' alt="' . $alt . '" title="' . $alt . '"';
    }
    $str .= '>';
    if($table) $str = '<td>' . $str . '</td>';
    return $str;
  }

  //画像出力
  function Output($name){
    echo $this->Generate($name) . "<br>\n";
  }
}

//-- 勝利陣営の画像処理の基底クラス --//
class VictoryImageBase extends ImageManager{
  function Generate($name){
    switch($name){
    case 'human':
      $alt = '村人勝利';
      break;

    case 'wolf':
      $alt = '人狼勝利';
      break;

    case 'fox1':
    case 'fox2':
      $name = 'fox';
      $alt = '妖狐勝利';
      break;

    case 'lovers':
      $alt = '恋人勝利';
      break;

    case 'quiz':
      $alt = '出題者勝利';
      break;

    case 'vampire':
      $alt = '吸血鬼勝利';
      break;

    case 'draw':
    case 'vanish':
    case 'quiz_dead':
      $name = 'draw';
      $alt = '引き分け';
      break;

    default:
      return '-';
    }
    return parent::Generate($name, $alt);
  }
}

//-- メニューリンク表示用の基底クラス --//
class MenuLinkConfigBase{
  //交流用サイト表示
  function Output(){
    //初期化処理
    $this->str = '';
    $this->header = '<li>';
    $this->footer = "</li>\n";

    $this->AddHeader('交流用サイト');
    $this->AddLink($this->list);
    $this->AddFooter();

    if(count($this->add_list) > 0){
      $this->AddHeader('外部リンク');
      foreach($this->add_list as $group => $list){
	$this->str .= $this->header . $group . $this->footer;
	$this->AddLink($list);
      }
      $this->AddFooter();
    }
    echo $this->str;
  }

  //ヘッダ追加
  function AddHeader($title){
    $this->str .= '<div class="menu">' . $title . "</div>\n<ul>\n";
  }

  //リンク生成
  function AddLink($list){
    $header = $this->header . '<a href="';
    $footer = '</a>' . $this->footer;
    foreach($list as $name => $url) $this->str .= $header . $url . '">' . $name . $footer;
  }

  //フッタ追加
  function AddFooter(){
    $this->str .= "</ul>\n";
  }
}

//-- Copyright 表示用の基底クラス --//
class CopyrightConfigBase{
  //投稿処理
  function Output(){
    $stack = $this->list;
    foreach($this->add_list as $class => $list){
      $stack[$class] = array_key_exists($class, $stack) ?
	array_merge($stack[$class], $list) : $list;
    }

    foreach($stack as $class => $list){
      $str = '<h2>' . $class . "</h2>\n<ul>\n";
      foreach($list as $name => $url){
	$str .= '<li><a href="' . $url . '">' . $name . "</a></li>\n";
      }
      echo $str . "</ul>\n";
    }
  }
}

//-- 音源処理の基底クラス --//
class SoundBase{
  //ファイルパス生成
  function GetPath($type, $file = null){
    $path = JINRO_ROOT . '/' . $this->path;
    return $path . '/' . (is_null($file) ? $this->$type : $file) . '.' . $this->extension;
  }

  //HTML 生成
  function Generate($type, $file = null){
    $path = $this->GetPath($type, $file);
    return <<<EOF
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0" width="0" height="0">
<param name="movie" value="{$path}">
<param name="quality" value="high">
<embed src="{$path}" type="application/x-shockwave-flash" quality="high" width="0" height="0" loop="false" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash">
</embed>
</object>

EOF;
  }

  //HTML 生成 (JavaScript 用)
  function GenerateJS($type){
    $path = $this->GetPath($type);
    return "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0' width='0' height='0'>" .
"<param name='movie' value='" . $path . "'><param name='quality' value='high'>" .
"<embed src='" . $path . "' type='application/x-shockwave-flash' quality='high' width='0' height='0' loop='false' pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'>" .
"</embed></object>";
  }

  //出力
  function Output($type){ echo $this->Generate($type); }
}

//-- Twitter 投稿用の基底クラス --//
class TwitterConfigBase{
  //メッセージのセット
  function GenerateMessage($id, $name, $comment){
    return true;
  }

  //投稿処理
  function Send($id, $name, $comment){
    global $SERVER_CONF;
    if($this->disable) return;
    require_once(JINRO_MOD . '/twitter/twitteroauth.php'); //ライブラリをロード

    $message = $this->GenerateMessage($id, $name, $comment);
    //TwitterはUTF-8
    if($SERVER_CONF->encode != 'UTF-8'){
      $message = mb_convert_encoding($message, 'UTF-8', $SERVER_CONF->encode);
    }
    if(mb_strlen($message) > 140) $message = mb_substr($message, 0, 139);

    if($this->add_url){
      $url = $SERVER_CONF->site_root;
      if($this->direct_url) $url .= 'login.php?room_no=' . $id;
      if($this->short_url){
	$short_url = @file_get_contents('http://tinyurl.com/api-create.php?url=' . $url);
	if($short_url != '') $url = $short_url;
      }
      if(mb_strlen($message . $url) + 1 < 140) $message .= ' ' . $url;
    }
    if(strlen($this->hash) > 0 && mb_strlen($message . $this->hash) + 2 < 140){
      $message .= " #{$this->hash}";
    }

    //投稿
    $to = new TwitterOAuth($this->key_ck, $this->key_cs, $this->key_at, $this->key_as);
    $response = $to->OAuthRequest('https://twitter.com/statuses/update.json', 'POST',
				  array('status' => $message));

    if(! ($response === false || (strrpos($response, 'error')))) return;
    //エラー処理
    $sentence = 'Twitter への投稿に失敗しました。<br>'."\n" .
      'ユーザ名：' . $this->user . '<br>'."\n" . 'メッセージ：' . $message;
    PrintData($sentence);
  }
}

//-- ページ送りリンク生成クラス --//
class PageLinkBuilder{
  function PageLinkBuilder($file, $page, $count, $config, $title = 'Page', $type = 'page'){
    $this->__construct($file, $page, $count, $config, $title, $type);
  }
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

  //表示するページのアドレスをセット
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
  function AddOption($type, $value = 'on'){
    $this->option[$type] = $type . '=' . $value;
  }

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
  function Output(){
    echo $this->Generate();
  }
}

//-- 役職データベース --//
class RoleData{
  //-- 役職名の翻訳 --//
  //メイン役職のリスト (コード名 => 表示名)
  //初日の役職通知リストはこの順番で表示される
  var $main_role_list = array(
    'human'                => '村人',
    'saint'                => '聖女',
    'executor'             => '執行者',
    'elder'                => '長老',
    'scripter'             => '執筆者',
    'suspect'              => '不審者',
    'unconscious'          => '無意識',
    'mage'                 => '占い師',
    'soul_mage'            => '魂の占い師',
    'psycho_mage'          => '精神鑑定士',
    'sex_mage'             => 'ひよこ鑑定士',
    'stargazer_mage'       => '占星術師',
    'voodoo_killer'        => '陰陽師',
    'dummy_mage'           => '夢見人',
    'necromancer'          => '霊能者',
    'soul_necromancer'     => '雲外鏡',
    'attempt_necromancer'  => '蟲姫',
    'yama_necromancer'     => '閻魔',
    'dummy_necromancer'    => '夢枕人',
    'medium'               => '巫女',
    'bacchus_medium'       => '神主',
    'seal_medium'          => '封印師',
    'revive_medium'        => '風祝',
    'priest'               => '司祭',
    'bishop_priest'        => '司教',
    'dowser_priest'        => '探知師',
    'high_priest'          => '大司祭',
    'crisis_priest'        => '預言者',
    'revive_priest'        => '天人',
    'border_priest'        => '境界師',
    'dummy_priest'         => '夢司祭',
    'guard'                => '狩人',
    'hunter_guard'         => '猟師',
    'blind_guard'          => '夜雀',
    'reflect_guard'        => '侍',
    'poison_guard'         => '騎士',
    'fend_guard'           => '忍者',
    'reporter'             => 'ブン屋',
    'anti_voodoo'          => '厄神',
    'dummy_guard'          => '夢守人',
    'common'               => '共有者',
    'detective_common'     => '探偵',
    'trap_common'          => '策士',
    'ghost_common'         => '亡霊嬢',
    'dummy_common'         => '夢共有者',
    'poison'               => '埋毒者',
    'strong_poison'        => '強毒者',
    'incubate_poison'      => '潜毒者',
    'guide_poison'         => '誘毒者',
    'chain_poison'         => '連毒者',
    'dummy_poison'         => '夢毒者',
    'poison_cat'           => '猫又',
    'revive_cat'           => '仙狸',
    'sacrifice_cat'        => '猫神',
    'eclipse_cat'          => '蝕仙狸',
    'pharmacist'           => '薬師',
    'cure_pharmacist'      => '河童',
    'revive_pharmacist'    => '仙人',
    'alchemy_pharmacist'   => '錬金術師',
    'assassin'             => '暗殺者',
    'doom_assassin'        => '死神',
    'reverse_assassin'     => '反魂師',
    'soul_assassin'        => '辻斬り',
    'eclipse_assassin'     => '蝕暗殺者',
    'mind_scanner'         => 'さとり',
    'evoke_scanner'        => 'イタコ',
    'clairvoyance_scanner' => '猩々',
    'presage_scanner'      => '件',
    'whisper_scanner'      => '囁騒霊',
    'howl_scanner'         => '吠騒霊',
    'telepath_scanner'     => '念騒霊',
    'jealousy'             => '橋姫',
    'divorce_jealousy'     => '縁切地蔵',
    'priest_jealousy'      => '恋司祭',
    'poison_jealousy'      => '毒橋姫',
    'brownie'              => '座敷童子',
    'revive_brownie'       => '蛇神',
    'cursed_brownie'       => '祟神',
    'history_brownie'      => '白澤',
    'doll'                 => '上海人形',
    'friend_doll'          => '仏蘭西人形',
    'phantom_doll'         => '倫敦人形',
    'poison_doll'          => '鈴蘭人形',
    'doom_doll'            => '蓬莱人形',
    'revive_doll'          => '西蔵人形',
    'scarlet_doll'         => '和蘭人形',
    'silver_doll'          => '露西亜人形',
    'doll_master'          => '人形遣い',
    'escaper'              => '逃亡者',
    'incubus_escaper'      => '一角獣',
    'wolf'                 => '人狼',
    'boss_wolf'            => '白狼',
    'gold_wolf'            => '金狼',
    'phantom_wolf'         => '幻狼',
    'cursed_wolf'          => '呪狼',
    'wise_wolf'            => '賢狼',
    'poison_wolf'          => '毒狼',
    'resist_wolf'          => '抗毒狼',
    'blue_wolf'            => '蒼狼',
    'emerald_wolf'         => '翠狼',
    'sex_wolf'             => '雛狼',
    'tongue_wolf'          => '舌禍狼',
    'possessed_wolf'       => '憑狼',
    'hungry_wolf'          => '餓狼',
    'doom_wolf'            => '冥狼',
    'sirius_wolf'          => '天狼',
    'elder_wolf'           => '古狼',
    'cute_wolf'            => '萌狼',
    'scarlet_wolf'         => '紅狼',
    'silver_wolf'          => '銀狼',
    'mad'                  => '狂人',
    'fanatic_mad'          => '狂信者',
    'whisper_mad'          => '囁き狂人',
    'jammer_mad'           => '月兎',
    'voodoo_mad'           => '呪術師',
    'enchant_mad'          => '狢',
    'dream_eater_mad'      => '獏',
    'possessed_mad'        => '犬神',
    'trap_mad'             => '罠師',
    'snow_trap_mad'        => '雪女',
    'corpse_courier_mad'   => '火車',
    'amaze_mad'            => '傘化け',
    'agitate_mad'          => '扇動者',
    'miasma_mad'           => '土蜘蛛',
    'therian_mad'          => '獣人',
    'fox'                  => '妖狐',
    'white_fox'            => '白狐',
    'black_fox'            => '黒狐',
    'gold_fox'             => '金狐',
    'phantom_fox'          => '幻狐',
    'poison_fox'           => '管狐',
    'blue_fox'             => '蒼狐',
    'emerald_fox'          => '翠狐',
    'voodoo_fox'           => '九尾',
    'revive_fox'           => '仙狐',
    'possessed_fox'        => '憑狐',
    'doom_fox'             => '冥狐',
    'cursed_fox'           => '天狐',
    'elder_fox'            => '古狐',
    'cute_fox'             => '萌狐',
    'scarlet_fox'          => '紅狐',
    'silver_fox'           => '銀狐',
    'child_fox'            => '子狐',
    'sex_fox'              => '雛狐',
    'stargazer_fox'        => '星狐',
    'jammer_fox'           => '月狐',
    'miasma_fox'           => '蟲狐',
    'howl_fox'             => '化狐',
    'cupid'                => 'キューピッド',
    'self_cupid'           => '求愛者',
    'moon_cupid'           => 'かぐや姫',
    'mind_cupid'           => '女神',
    'sweet_cupid'          => '弁財天',
    'triangle_cupid'       => '小悪魔',
    'angel'                => '天使',
    'rose_angel'           => '薔薇天使',
    'lily_angel'           => '百合天使',
    'exchange_angel'       => '魂移使',
    'ark_angel'            => '大天使',
    'sacrifice_angel'      => '守護天使',
    'quiz'                 => '出題者',
    'vampire'              => '吸血鬼',
    'incubus_vampire'      => '青髭公',
    'succubus_vampire'     => '飛縁魔',
    'doom_vampire'         => '冥血鬼',
    'sacrifice_vampire'    => '吸血公',
    'soul_vampire'         => '吸血姫',
    'chiroptera'           => '蝙蝠',
    'poison_chiroptera'    => '毒蝙蝠',
    'cursed_chiroptera'    => '呪蝙蝠',
    'boss_chiroptera'      => '大蝙蝠',
    'elder_chiroptera'     => '古蝙蝠',
    'scarlet_chiroptera'   => '紅蝙蝠',
    'dummy_chiroptera'     => '夢求愛者',
    'fairy'                => '妖精',
    'spring_fairy'         => '春妖精',
    'summer_fairy'         => '夏妖精',
    'autumn_fairy'         => '秋妖精',
    'winter_fairy'         => '冬妖精',
    'flower_fairy'         => '花妖精',
    'star_fairy'           => '星妖精',
    'sun_fairy'            => '日妖精',
    'moon_fairy'           => '月妖精',
    'grass_fairy'          => '草妖精',
    'light_fairy'          => '光妖精',
    'dark_fairy'           => '闇妖精',
    'shadow_fairy'         => '影妖精',
    'ice_fairy'            => '氷妖精',
    'mirror_fairy'         => '鏡妖精',
    'ogre'                 => '鬼',
    'orange_ogre'          => '前鬼',
    'indigo_ogre'          => '後鬼',
    'poison_ogre'          => '榊鬼',
    'west_ogre'            => '金鬼',
    'east_ogre'            => '風鬼',
    'north_ogre'           => '水鬼',
    'south_ogre'           => '隠行鬼',
    'incubus_ogre'         => '般若',
    'power_ogre'           => '星熊童子',
    'revive_ogre'          => '茨木童子',
    'sacrifice_ogre'       => '酒呑童子',
    'yaksa'                => '夜叉',
    'succubus_yaksa'       => '荼枳尼天',
    'dowser_yaksa'         => '毘沙門天',
    'mania'                => '神話マニア',
    'trick_mania'          => '奇術師',
    'soul_mania'           => '覚醒者',
    'dummy_mania'          => '夢語部',
    'unknown_mania'        => '鵺',
    'sacrifice_mania'      => '影武者');

  //サブ役職のリスト (コード名 => 表示名)
  //初日の役職通知リストはこの順番で表示される
  var $sub_role_list = array(
    'chicken'            => '小心者',
    'rabbit'             => 'ウサギ',
    'perverseness'       => '天邪鬼',
    'flattery'           => 'ゴマすり',
    'impatience'         => '短気',
    'celibacy'           => '独身貴族',
    'nervy'              => '自信家',
    'androphobia'        => '男性恐怖症',
    'gynophobia'         => '女性恐怖症',
    'febris'             => '熱病',
    'frostbite'          => '凍傷',
    'death_warrant'      => '死の宣告',
    'panelist'           => '解答者',
    'liar'               => '狼少年',
    'invisible'          => '光学迷彩',
    'rainbow'            => '虹色迷彩',
    'weekly'             => '七曜迷彩',
    'passion'            => '恋色迷彩',
    'grassy'             => '草原迷彩',
    'side_reverse'       => '鏡面迷彩',
    'line_reverse'       => '天地迷彩',
    'gentleman'          => '紳士',
    'lady'               => '淑女',
    'actor'              => '役者',
    'authority'          => '権力者',
    'critical_voter'     => '会心',
    'random_voter'       => '気分屋',
    'rebel'              => '反逆者',
    'watcher'            => '傍観者',
    'decide'             => '決定者',
    'plague'             => '疫病神',
    'good_luck'          => '幸運',
    'bad_luck'           => '不運',
    'upper_luck'         => '雑草魂',
    'downer_luck'        => '一発屋',
    'star'               => '人気者',
    'disfavor'           => '不人気',
    'critical_luck'      => '痛恨',
    'random_luck'        => '波乱万丈',
    'strong_voice'       => '大声',
    'normal_voice'       => '不器用',
    'weak_voice'         => '小声',
    'upper_voice'        => 'メガホン',
    'downer_voice'       => 'マスク',
    'inside_voice'       => '内弁慶',
    'outside_voice'      => '外弁慶',
    'random_voice'       => '臆病者',
    'no_last_words'      => '筆不精',
    'blinder'            => '目隠し',
    'earplug'            => '耳栓',
    'speaker'            => 'スピーカー',
    'whisper_ringing'    => '囁耳鳴',
    'howl_ringing'       => '吠耳鳴',
    'sweet_ringing'      => '恋耳鳴',
    'deep_sleep'         => '爆睡者',
    'silent'             => '無口',
    'mower'              => '草刈り',
    'mind_read'          => 'サトラレ',
    'mind_open'          => '公開者',
    'mind_receiver'      => '受信者',
    'mind_friend'        => '共鳴者',
    'mind_sympathy'      => '共感者',
    'mind_evoke'         => '口寄せ',
    'mind_presage'       => '受託者',
    'mind_lonely'        => 'はぐれ者',
    'lovers'             => '恋人',
    'possessed_exchange' => '交換憑依',
    'challenge_lovers'   => '難題',
    'infected'           => '感染者',
    'psycho_infected'    => '洗脳者',
    'protected'          => '庇護者',
    'changed_therian'    => '元獣人',
    'copied'             => '元神話マニア',
    'copied_trick'       => '元奇術師',
    'copied_soul'        => '元覚醒者',
    'copied_teller'      => '元夢語部',
    'possessed_target'   => '憑依者',
    'possessed'          => '憑依',
    'bad_status'         => '悪戯',
    'lost_ability'       => '能力喪失',
    'joker'              => 'ジョーカー');

  //役職の省略名 (過去ログ用)
  var $short_role_list = array(
    'human'                => '村',
    'saint'                => '聖',
    'executor'             => '執行',
    'elder'                => '老',
    'scripter'             => '執筆',
    'suspect'              => '不審',
    'unconscious'          => '無',
    'mage'                 => '占',
    'soul_mage'            => '魂',
    'psycho_mage'          => '心占',
    'sex_mage'             => '雛占',
    'stargazer_mage'       => '星占',
    'voodoo_killer'        => '陰陽',
    'dummy_mage'           => '夢見',
    'necromancer'          => '霊',
    'soul_necromancer'     => '雲',
    'attempt_necromancer'  => '蟲姫',
    'yama_necromancer'     => '閻',
    'dummy_necromancer'    => '夢枕',
    'medium'               => '巫',
    'bacchus_medium'       => '神主',
    'seal_medium'          => '封',
    'revive_medium'        => '風',
    'priest'               => '司',
    'bishop_priest'        => '司教',
    'dowser_priest'        => '探',
    'high_priest'          => '大司',
    'crisis_priest'        => '預',
    'revive_priest'        => '天人',
    'border_priest'        => '境',
    'dummy_priest'         => '夢司',
    'guard'                => '狩',
    'hunter_guard'         => '猟',
    'blind_guard'          => '雀',
    'reflect_guard'        => '侍',
    'poison_guard'         => '騎',
    'fend_guard'           => '忍',
    'reporter'             => '聞',
    'anti_voodoo'          => '厄',
    'dummy_guard'          => '夢守',
    'common'               => '共',
    'detective_common'     => '偵',
    'trap_common'          => '策',
    'ghost_common'         => '亡',
    'dummy_common'         => '夢共',
    'poison'               => '毒',
    'strong_poison'        => '強毒',
    'incubate_poison'      => '潜毒',
    'guide_poison'         => '誘毒',
    'chain_poison'         => '連毒',
    'dummy_poison'         => '夢毒',
    'poison_cat'           => '猫',
    'revive_cat'           => '仙狸',
    'sacrifice_cat'        => '猫神',
    'eclipse_cat'          => '蝕狸',
    'pharmacist'           => '薬',
    'cure_pharmacist'      => '河',
    'revive_pharmacist'    => '仙人',
    'alchemy_pharmacist'   => '錬',
    'assassin'             => '暗',
    'doom_assassin'        => '死神',
    'reverse_assassin'     => '反魂',
    'soul_assassin'        => '辻',
    'eclipse_assassin'     => '蝕暗',
    'mind_scanner'         => '覚',
    'evoke_scanner'        => 'イ',
    'presage_scanner'      => '件',
    'clairvoyance_scanner' => '猩',
    'whisper_scanner'      => '囁騒',
    'howl_scanner'         => '吠騒',
    'telepath_scanner'     => '念騒',
    'jealousy'             => '橋',
    'divorce_jealousy'     => '縁切',
    'priest_jealousy'      => '恋司',
    'poison_jealousy'      => '毒橋',
    'brownie'              => '童',
    'revive_brownie'       => '蛇',
    'cursed_brownie'       => '祟',
    'history_brownie'      => '白澤',
    'doll'                 => '上海',
    'friend_doll'          => '仏蘭',
    'phantom_doll'         => '倫敦',
    'poison_doll'          => '鈴蘭',
    'doom_doll'            => '蓬莱',
    'revive_doll'          => '西蔵',
    'scarlet_doll'         => '和蘭',
    'silver_doll'          => '露',
    'doll_master'          => '人遣',
    'escaper'              => '逃',
    'incubus_escaper'      => '一角',
    'wolf'                 => '狼',
    'boss_wolf'            => '白狼',
    'gold_wolf'            => '金狼',
    'phantom_wolf'         => '幻狼',
    'cursed_wolf'          => '呪狼',
    'wise_wolf'            => '賢狼',
    'poison_wolf'          => '毒狼',
    'resist_wolf'          => '抗狼',
    'blue_wolf'            => '蒼狼',
    'emerald_wolf'         => '翠狼',
    'sex_wolf'             => '雛狼',
    'tongue_wolf'          => '舌狼',
    'possessed_wolf'       => '憑狼',
    'hungry_wolf'          => '餓狼',
    'doom_wolf'            => '冥狼',
    'sirius_wolf'          => '天狼',
    'elder_wolf'           => '古狼',
    'cute_wolf'            => '萌狼',
    'scarlet_wolf'         => '紅狼',
    'silver_wolf'          => '銀狼',
    'mad'                  => '狂',
    'fanatic_mad'          => '狂信',
    'whisper_mad'          => '囁狂',
    'jammer_mad'           => '月兎',
    'voodoo_mad'           => '呪狂',
    'enchant_mad'          => '狢',
    'dream_eater_mad'      => '獏',
    'possessed_mad'        => '犬',
    'trap_mad'             => '罠',
    'snow_trap_mad'        => '雪',
    'corpse_courier_mad'   => '火',
    'amaze_mad'            => '傘',
    'agitate_mad'          => '扇',
    'miasma_mad'           => '蜘',
    'therian_mad'          => '獣',
    'fox'                  => '狐',
    'white_fox'            => '白狐',
    'black_fox'            => '黒狐',
    'gold_fox'             => '金狐',
    'phantom_fox'          => '幻狐',
    'poison_fox'           => '管狐',
    'blue_fox'             => '蒼狐',
    'emerald_fox'          => '翠狐',
    'voodoo_fox'           => '九尾',
    'revive_fox'           => '仙狐',
    'possessed_fox'        => '憑狐',
    'doom_fox'             => '冥狐',
    'cursed_fox'           => '天狐',
    'elder_fox'            => '古狐',
    'cute_fox'             => '萌狐',
    'scarlet_fox'          => '紅狐',
    'silver_fox'           => '銀狐',
    'child_fox'            => '子狐',
    'sex_fox'              => '雛狐',
    'stargazer_fox'        => '星狐',
    'jammer_fox'           => '月狐',
    'miasma_fox'           => '蟲狐',
    'howl_fox'             => '化狐',
    'cupid'                => 'QP',
    'self_cupid'           => '求愛',
    'moon_cupid'           => '姫',
    'mind_cupid'           => '女神',
    'sweet_cupid'          => '弁天',
    'triangle_cupid'       => '小悪',
    'angel'                => '天使',
    'rose_angel'           => '薔天',
    'lily_angel'           => '百天',
    'exchange_angel'       => '魂移',
    'ark_angel'            => '大天',
    'sacrifice_angel'      => '守天',
    'quiz'                 => 'GM',
    'vampire'              => '血',
    'incubus_vampire'      => '青髭',
    'succubus_vampire'     => '飛',
    'doom_vampire'         => '冥血',
    'sacrifice_vampire'    => '血公',
    'soul_vampire'         => '血姫',
    'chiroptera'           => '蝙',
    'poison_chiroptera'    => '毒蝙',
    'cursed_chiroptera'    => '呪蝙',
    'boss_chiroptera'      => '大蝙',
    'elder_chiroptera'     => '古蝙',
    'scarlet_chiroptera'   => '紅蝙',
    'dummy_chiroptera'     => '夢愛',
    'fairy'                => '妖精',
    'spring_fairy'         => '春精',
    'summer_fairy'         => '夏精',
    'autumn_fairy'         => '秋精',
    'winter_fairy'         => '冬精',
    'flower_fairy'         => '花精',
    'star_fairy'           => '星精',
    'sun_fairy'            => '日精',
    'moon_fairy'           => '月精',
    'grass_fairy'          => '草精',
    'light_fairy'          => '光精',
    'dark_fairy'           => '闇精',
    'shadow_fairy'         => '影精',
    'ice_fairy'            => '氷精',
    'mirror_fairy'         => '鏡精',
    'ogre'                 => '鬼',
    'orange_ogre'          => '前鬼',
    'indigo_ogre'          => '後鬼',
    'poison_ogre'          => '榊鬼',
    'west_ogre'            => '金鬼',
    'east_ogre'            => '風鬼',
    'north_ogre'           => '水鬼',
    'south_ogre'           => '隠鬼',
    'incubus_ogre'         => '般若',
    'power_ogre'           => '星熊',
    'revive_ogre'          => '茨木',
    'sacrifice_ogre'       => '酒呑',
    'yaksa'                => '夜叉',
    'succubus_yaksa'       => '荼',
    'dowser_yaksa'         => '毘',
    'mania'                => 'マ',
    'trick_mania'          => '奇',
    'soul_mania'           => '覚醒',
    'dummy_mania'          => '夢語',
    'unknown_mania'        => '鵺',
    'sacrifice_mania'      => '影',
    'chicken'              => '酉',
    'rabbit'               => '卯',
    'perverseness'         => '邪',
    'flattery'             => '胡麻',
    'impatience'           => '短',
    'celibacy'             => '独',
    'nervy'                => '信',
    'androphobia'          => '男恐',
    'gynophobia'           => '女恐',
    'febris'               => '熱',
    'frostbite'            => '凍',
    'death_warrant'        => '宣',
    'panelist'             => '解',
    'liar'                 => '嘘',
    'invisible'            => '光迷',
    'rainbow'              => '虹迷',
    'weekly'               => '曜迷',
    'passion'              => '恋迷',
    'grassy'               => '草迷',
    'side_reverse'         => '鏡迷',
    'line_reverse'         => '天迷',
    'gentleman'            => '紳',
    'lady'                 => '淑',
    'actor'                => '役',
    'authority'            => '権',
    'critical_voter'       => '会',
    'random_voter'         => '気',
    'rebel'                => '反',
    'watcher'              => '傍',
    'decide'               => '決',
    'plague'               => '疫',
    'good_luck'            => '幸',
    'bad_luck'             => '不運',
    'upper_luck'           => '雑',
    'downer_luck'          => '一発',
    'star'                 => '人気',
    'disfavor'             => '不人',
    'critical_luck'        => '痛',
    'random_luck'          => '乱',
    'strong_voice'         => '大',
    'normal_voice'         => '不',
    'weak_voice'           => '小',
    'upper_voice'          => '拡声',
    'downer_voice'         => '覆',
    'inside_voice'         => '内弁',
    'outside_voice'        => '外弁',
    'random_voice'         => '臆',
    'no_last_words'        => '筆',
    'blinder'              => '目',
    'earplug'              => '耳',
    'speaker'              => '集音',
    'whisper_ringing'      => '囁鳴',
    'howl_ringing'         => '吠鳴',
    'sweet_ringing'        => '恋鳴',
    'deep_sleep'           => '爆睡',
    'silent'               => '無口',
    'mower'                => '草刈',
    'mind_read'            => '漏',
    'mind_evoke'           => '口寄',
    'mind_presage'         => '受託',
    'mind_open'            => '公',
    'mind_receiver'        => '受',
    'mind_friend'          => '鳴',
    'mind_sympathy'        => '感',
    'mind_lonely'          => '逸',
    'lovers'               => '恋',
    'possessed_exchange'   => '換',
    'challenge_lovers'     => '難',
    'infected'             => '染',
    'psycho_infected'      => '洗',
    'protected'            => '庇',
    'copied'               => '元マ',
    'copied_trick'         => '元奇',
    'copied_soul'          => '元覚',
    'copied_teller'        => '元語',
    'changed_therian'      => '元獣',
    'possessed_target'     => '憑',
    'possessed'            => '被憑',
    'bad_status'           => '戯',
    'lost_ability'         => '失',
    'joker'                => '道化');

  //メイン役職のグループリスト (役職 => 所属グループ)
  // このリストの並び順に strpos() で判別する (毒系など、順番依存の役職があるので注意)
  var $main_role_group_list = array(
    'wolf' => 'wolf',
    'mad' => 'mad',
    'child_fox' => 'child_fox', 'sex_fox' => 'child_fox', 'stargazer_fox' => 'child_fox',
    'jammer_fox' => 'child_fox', 'miasma_fox' => 'child_fox', 'howl_fox' => 'child_fox',
    'fox' => 'fox',
    'cupid' => 'cupid',
    'angel' => 'angel',
    'quiz' => 'quiz',
    'vampire' => 'vampire',
    'chiroptera' => 'chiroptera',
    'fairy' => 'fairy',
    'ogre' => 'ogre',
    'yaksa' => 'yaksa',
    'mage' => 'mage', 'voodoo_killer' => 'mage',
    'necromancer' => 'necromancer',
    'medium' => 'medium',
    'jealousy' => 'jealousy',
    'priest' => 'priest',
    'guard' => 'guard', 'anti_voodoo' => 'guard', 'reporter' => 'guard',
    'common' => 'common',
    'cat' => 'poison_cat',
    'brownie' => 'brownie',
    'doll' => 'doll',
    'escaper' => 'escaper',
    'poison' => 'poison',
    'pharmacist' => 'pharmacist',
    'assassin' => 'assassin',
    'scanner' => 'mind_scanner',
    'unknown_mania' => 'unknown_mania', 'sacrifice_mania' => 'unknown_mania',
    'mania' => 'mania');

  //サブ役職のグループリスト (CSS のクラス名 => 所属役職)
  var $sub_role_group_list = array(
    'lovers'       => array('lovers', 'possessed_exchange', 'challenge_lovers'),
    'mind'         => array('mind_read', 'mind_open', 'mind_receiver', 'mind_friend', 'mind_sympathy',
			    'mind_evoke', 'mind_presage', 'mind_lonely'),
    'mania'        => array('copied', 'copied_trick', 'copied_soul', 'copied_teller'),
    'vampire'      => array('infected', 'psycho_infected'),
    'sudden-death' => array('chicken', 'rabbit', 'perverseness', 'flattery', 'impatience',
			    'celibacy', 'nervy', 'androphobia', 'gynophobia', 'febris', 'frostbite',
			    'death_warrant', 'panelist'),
    'convert'      => array('liar', 'invisible', 'rainbow', 'weekly', 'passion', 'grassy',
			    'side_reverse', 'line_reverse', 'gentleman', 'lady', 'actor'),
    'authority'    => array('authority', 'critical_voter', 'random_voter', 'rebel', 'watcher'),
    'decide'       => array('decide', 'plague', 'good_luck', 'bad_luck'),
    'luck'         => array('upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck',
			    'random_luck'),
    'voice'        => array('strong_voice', 'normal_voice', 'weak_voice', 'upper_voice',
			    'downer_voice', 'inside_voice', 'outside_voice', 'random_voice'),
    'seal'         => array('no_last_words', 'blinder', 'earplug', 'speaker', 'whisper_ringing',
			    'howl_ringing', 'sweet_ringing', 'deep_sleep', 'silent', 'mower'),
    'wolf'         => array('possessed_target', 'possessed', 'changed_therian'),
    'chiroptera'   => array('joker', 'bad_status'),
    'guard'        => array('protected'),
    'human'        => array('lost_ability'));

  //-- 関数 --//
  //役職グループ判定
  function DistinguishRoleGroup($role){
    foreach($this->main_role_group_list as $key => $value){
      if(strpos($role, $key) !== false) return $value;
    }
    return 'human';
  }

  //所属陣営判別
  function DistinguishCamp($role, $start = false){
    switch($camp = $this->DistinguishRoleGroup($role)){
    case 'wolf':
    case 'mad':
      return 'wolf';

    case 'fox':
    case 'child_fox':
      return 'fox';

    case 'cupid':
    case 'angel':
      return $start ? 'cupid' : 'lovers';

    case 'quiz':
    case 'vampire':
      return $camp;

    case 'chiroptera':
    case 'fairy':
      return 'chiroptera';

    case 'ogre':
    case 'yaksa':
      return 'ogre';

    case 'mania':
    case 'unknown_mania':
      return $start ? 'mania' : 'human';

    default:
      return 'human';
    }
  }

  //役職クラス (CSS) 判定
  function DistinguishRoleClass($role){
    switch($class = $this->DistinguishRoleGroup($role)){
    case 'poison_cat':
      $class = 'cat';
      break;

    case 'mind_scanner':
      $class = 'mind';
      break;

    case 'child_fox':
      $class = 'fox';
      break;

    case 'unknown_mania':
      $class = 'mania';
      break;
    }
    return $class;
  }

  //役職名のタグ生成
  function GenerateRoleTag($role, $css = NULL, $sub_role = false){
    $str = '';
    if(is_null($css)) $css = $this->DistinguishRoleClass($role);
    if($sub_role) $str .= '<br>';
    $str .= '<span class="' . $css . '">[' .
      ($sub_role ? $this->sub_role_list[$role] : $this->main_role_list[$role]) . ']</span>';
    return $str;
  }

  //役職名のタグ生成 (メイン役職専用)
  function GenerateMainRoleTag($role, $tag = 'span'){
    return '<' . $tag . ' class="' . $this->DistinguishRoleClass($role) . '">' .
      $this->main_role_list[$role] . '</' . $tag .'>';
  }
}

//-- 配役設定の基底クラス --//
class CastConfigBase{
  //配役テーブル出力
  function OutputCastTable($min = 0, $max = NULL){
    global $ROLE_DATA;

    //設定されている役職名を取得
    $stack = array();
    foreach($this->role_list as $key => $value){
      if($key < $min) continue;
      $stack = array_merge($stack, array_keys($value));
      if($key == $max) break;
    }
    //表示順を決定
    $role_list = array_intersect(array_keys($ROLE_DATA->main_role_list), array_unique($stack));

    $header = '<table class="member">';
    $str = '<tr><th>人口</th>';
    foreach($role_list as $role) $str .= $ROLE_DATA->GenerateMainRoleTag($role, 'th');
    $str .= '</tr>'."\n";
    echo $header . $str;

    //人数毎の配役を表示
    foreach($this->role_list as $key => $value){
      if($key < $min) continue;
      $tag = "<td><strong>{$key}</strong></td>";
      foreach($role_list as $role) $tag .= '<td>' . (int)$value[$role] . '</td>';
      echo '<tr>' . $tag . '</tr>'."\n";
      if($key == $max) break;
      if($key % 20 == 0) echo $str;
    }
    echo '</table>';
  }

  //「福引き」を一定回数行ってリストに追加する
  function AddRandom(&$list, $random_list, $count){
    $total = count($random_list) - 1;
    for(; $count > 0; $count--) $list[$random_list[mt_rand(0, $total)]]++;
  }

  //「比」の配列から「福引き」を作成する
  function GenerateRandomList($list){
    $stack = array();
    foreach($list as $role => $rate){
      for($i = $rate; $i > 0; $i--) $stack[] = $role;
    }
    return $stack;
  }

  //「比」から「確率」に変換する (テスト用)
  function RateToProbability($list){
    $stack = array();
    $total_rate = array_sum($list);
    foreach($list as $role => $rate){
      $stack[$role] = sprintf("%01.2f", $rate / $total_rate * 100);
    }
    PrintData($stack);
  }

  //決闘村の配役初期化処理
  function InitializeDuel($user_count){
    return true;
  }

  //決闘村の配役最終処理
  function FinalizeDuel($user_count, &$role_list){
    return true;
  }

  //決闘村の配役処理
  function SetDuel($user_count){
    $role_list = array(); //初期化処理
    $this->InitializeDuel($user_count);

    if(array_sum($this->duel_fix_list) <= $user_count){
      foreach($this->duel_fix_list as $role => $count) $role_list[$role] = $count;
    }
    $rest_user_count = $user_count - array_sum($role_list);
    asort($this->duel_rate_list);
    $total_rate = array_sum($this->duel_rate_list);
    $max_rate_role = array_pop(array_keys($this->duel_rate_list));
    foreach($this->duel_rate_list as $role => $rate){
      if($role == $max_rate_role) continue;
      $role_list[$role] = round($rest_user_count / $total_rate * $rate);
    }
    $role_list[$max_rate_role] = $user_count - array_sum($role_list);

    $this->FinalizeDuel($user_count, $role_list);
    return $role_list;
  }

  //配役フィルタリング処理
  function FilterRoles($user_count, $filter){
    $stack = array();
    foreach($this->role_list[$user_count] as $key => $value){
      $role = 'human';
      foreach($filter as $set_role){
	if(strpos($key, $set_role) !== false){
	  $role = $set_role;
	  break;
	}
      }
      $stack[$role] += (int)$value;
    }
    return $stack;
  }

  //クイズ村の配役処理
  function SetQuiz($user_count){
    $stack = $this->FilterRoles($user_count, array('common', 'wolf', 'mad', 'fox'));
    $stack['human']--;
    $stack['quiz'] = 1;
    return $stack;
  }

  //グレラン村の配役処理
  function SetGrayRandom($user_count){
    return $this->FilterRoles($user_count, array('wolf', 'mad', 'fox'));
  }
}

//-- バージョン情報設定の基底クラス --//
class ScriptInfoBase{
  //TOPページ向けのバージョン情報を出力する
  function Output($full = false){
    global $SERVER_CONF;

    $str = "Powered by {$this->package} {$this->version} from {$this->developer}";
    if($SERVER_CONF->admin) $str .= '<br>Founded by: ' . $SERVER_CONF->admin;
    echo $str;
  }

  //PHP + パッケージのバージョン情報を出力する
  function OutputSystem(){
    $php = PHP_VERSION;
    echo <<<EOF
<h2>パッケージ情報</h2>
<ul>
<li>PHP Ver. {$php}</li>
<li>{$this->package} {$this->version} (Rev. {$this->revision})</li>
<li>LastUpdate: {$this->last_update}</li>
</ul>

EOF;
  }
}
