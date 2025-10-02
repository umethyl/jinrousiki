<?php
//-- テキスト処理クラス --//
class Text {
  const BR   = '<br>';
  const CR   = "\r";
  const LF   = "\n";
  const CRLF = "\r\n";
  const BRLF = "<br>\n";
  const TR   = "</tr>\n<tr>";

  //結合
  static function Concat($stack) {
    $stack = func_get_args();
    return implode(self::BRLF, $stack);
  }

  //出力
  static function Output($str = '', $line = false) {
    echo $str . ($line ? self::BR : '') . self::LF;
  }

  //出力 (NULL 対応版)
  static function OutputExists($str) {
    if (is_null($str)) return null;
    echo $str;
  }

  //暗号化
  static function Crypt($str) {
    return sha1(ServerConfig::SALT . $str);
  }

  //改行コードを <br> に変換する (PHP5.3 以下の nl2br() だと <br /> 固定なので HTML 4.01 だと不向き)
  static function Line($str) {
    return str_replace(self::LF, self::BR, $str);
  }

  //トリップ変換
  /*
    変換テスト結果＠2ch (2009/07/26)
    [入力文字列] => [変換結果] (Text::Trip() の結果)
    test#test                     => test ◆.CzKQna1OU (test◆.CzKQna1OU)
    テスト#テスト                 => テスト ◆SQ2Wyjdi7M (テスト◆SQ2Wyjdi7M)
    てすと＃てすと                => てすと ◆ZUNa78GuQc (てすと◆ZUNa78GuQc)
    てすとテスト#てすと＃テスト   => てすとテスト ◆TBYWAU/j2qbJ (てすとテスト◆sXitOlnF0g)
    テストてすと＃テストてすと    => テストてすと ◆RZ9/PhChteSA (テストてすと◆XuUGgmt7XI)
    テストてすと＃テストてすと#   => テストてすと ◆rtfFl6edK5fK (テストてすと◆XuUGgmt7XI)
    テストてすと＃テストてすと＃  => テストてすと ◆rtfFl6edK5fK (テストてすと◆XuUGgmt7XI)
  */
  static function Trip($str) {
    if (GameConfig::TRIP) {
      if (get_magic_quotes_gpc()) $str = stripslashes($str); // \ を自動でつける処理系対策
      //トリップ関連のキーワードを置換
      $trip_list = array(Message::TRIP, Message::TRIP_KEY);
      $str = str_replace($trip_list, array(Message::TRIP_CONVERT, '#'), $str);
      if (($trip_start = mb_strpos($str, '#')) !== false) { //トリップキーの位置を検索
	$name = mb_substr($str, 0, $trip_start);
	$key  = mb_substr($str, $trip_start + 1);
	//self::p(sprintf('%s, name: %s, key: %s', $trip_start, $name, $key), 'Trip Start');
	$key = mb_convert_encoding($key, 'SJIS', ServerConfig::ENCODE); //文字コードを変換

	if (GameConfig::TRIP_2ch && strlen($key) >= 12) {
	  $trip_mark = substr($key, 0, 1);
	  if ($trip_mark == '#' || $trip_mark == '$') {
	    if (preg_match('|^#([[:xdigit:]]{16})([./0-9A-Za-z]{0,2})$|', $key, $stack)) {
	      $trip = substr(crypt(pack('H*', $stack[1]), "{$stack[2]}.."), -12);
	    } else {
	      $trip = '???';
	    }
	  }
	  else {
	    $trip = str_replace('+', '.', substr(base64_encode(sha1($key, true)), 0, 12));
	  }
	}
	else {
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
	$str = $name . Message::TRIP . $trip;
      }
      //self::p($str, 'Result');
    }
    elseif (strpos($str, '#') !== false || strpos($str, Message::TRIP_KEY) !== false) {
      $sentence = sprintf(Message::TRIP_FORMAT, '#', Message::TRIP_KEY);
      HTML::OutputResult(Message::TRIP_ERROR, Message::DISABLE_TRIP . self::BRLF . $sentence);
    }

    return self::Escape($str); //特殊文字のエスケープ
  }

  /* 更新系 */
  //POST されたデータの文字コードを統一する
  static function Encode() {
    foreach ($_POST as $key => $value) {
      $encode = @mb_detect_encoding($value, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
      if ($encode != '' && $encode != ServerConfig::ENCODE) {
	$_POST[$key] = mb_convert_encoding($value, ServerConfig::ENCODE, $encode);
      }
    }
  }

  //特殊文字のエスケープ処理
  //htmlentities() を使うと文字化けを起こしてしまうようなので敢えてべたに処理
  static function Escape(&$str, $trim = true) {
    if (is_array($str)) {
      $result = array();
      foreach ($str as $item) $result[] = self::Escape($item);
      return $result;
    }
    if (get_magic_quotes_gpc()) $str = stripslashes($str); //'\' を自動でつける処理系対策
    //$str = htmlentities($str, ENT_QUOTES); //UTF に移行したら機能する？
    $replace_list = array(
      '&'  => '&amp;',
      '<'  => '&lt;',
      '>'  => '&gt;',
      '\\' => '&yen;',
      '"'  => '&quot;',
      "'"  => '&#039;');
    $str = strtr($str, $replace_list);
    if ($trim) {
      $str = trim($str);
    } else {
      $str = str_replace(array(self::CRLF, self::CR, self::LF), self::LF, $str);
    }
    return $str;
  }

  /* デバッグ用 */
  //改行タグ付きテキスト出力
  static function d($str = '') {
    self::Output($str, true);
  }

  //データ表示
  static function p($data, $name = null) {
    $str = is_null($name) ? '' : $name . ': ';
    $str .= (is_array($data) || is_object($data)) ? print_r($data, true) : $data;
    self::d($str);
  }

  //データダンプ
  static function v($data, $name = null) {
    if (! is_null($name)) echo $name . ': ';
    var_dump($data);
    self::d();
  }

  //Talk 出力
  static function t($data, $name = null) {
    if (! class_exists('Talk')) return self::p($data, $name);
    if (is_null($builder = Talk::GetBuilder())) return self::p($data, $name);
    $builder->AddDebug($data, $name);
  }
}

//-- セキュリティ関連クラス --//
class Security {
  //IPアドレス取得
  static function GetIP() {
    return @$_SERVER['REMOTE_ADDR'];
  }

  //リファラチェック
  static function CheckReferer($page, $white_list = null) {
    if (is_array($white_list)) { //ホワイトリストチェック
      $addr = self::GetIP();
      foreach ($white_list as $host) {
	if (strpos($addr, $host) === 0) return false;
      }
    }
    $url = ServerConfig::SITE_ROOT . $page;
    return strncmp(@$_SERVER['HTTP_REFERER'], $url, strlen($url)) != 0;
  }

  //ブラックリストチェック (ログイン用)
  static function IsLoginBlackList($trip = '') {
    if (GameConfig::TRIP && $trip != '' && in_array($trip, RoomConfig::$white_list_trip)) {
      return false;
    }
    return self::IsBlackList();
  }

  //ブラックリストチェック (村立て用)
  static function IsEstablishBlackList() {
    return self::IsLoginBlackList() || self::IsBlackList('establish_');
  }

  /**
   * 実行環境にダメージを与える可能性がある値が含まれているかどうか検査します。
   * @param  : mixed   : $value 検査対象の変数
   * @param  : boolean : $found 疑わしい値が存在しているかどうかを示す値。
                         この値がtrueの場合、強制的に詳細なスキャンが実行されます。
   * @return : boolean : 危険な値が発見された場合 true、それ以外の場合 false
   */
  static function CheckValue($value, $found = false) {
    $num = '22250738585072011';
    if ($found || (strpos(str_replace('.', '', serialize($value)), $num) !== false)) {
      //文字列の中に問題の数字が埋め込まれているケースを排除する
      if (is_array($value)) {
	foreach ($value as $item) {
	  if (self::CheckValue($item, true)) return true;
	}
      }
      else {
	$preg = '/^([0.]*2[0125738.]{15,16}1[0.]*)e(-[0-9]+)$/i';
	$item = strval($value);
	$matches = '';
	if (preg_match($preg, $item, $matches)) {
	  $exp = intval($matches[2]) + 1;
	  if (2.2250738585072011e-307 === floatval("{$matches[1]}e{$exp}")) return true;
	}
      }
    }
    return false;
  }

  //ブラックリスト判定
  private static function IsBlackList($prefix = '') {
    $addr = self::GetIP();
    $host = gethostbyaddr($addr);
    foreach (array('white' => false, 'black' => true) as $type => $flag) {
      foreach (RoomConfig::${$prefix . $type . '_list_ip'} as $ip) {
	if (strpos($addr, $ip) === 0) return $flag;
      }
      $list = RoomConfig::${$prefix . $type . '_list_host'};
      if (isset($list) && preg_match($list, $host)) return $flag;
    }
    return false;
  }
}

//-- 日時関連 --//
class Time {
  //TZ 補正をかけた時刻を返す (環境変数 TZ を変更できない環境想定？)
  static function Get() {
    $time = time();
    if (ServerConfig::ADJUST_TIME) $time += ServerConfig::OFFSET_SECONDS;
    return $time;
    /*
    // ミリ秒対応のコード(案) 2009-08-08 enogu
    $preg = '/([0-9]+)( [0-9]+)?/i';
    return preg_replace($preg, '$$2.$$1', microtime()) + ServerConfig::OFFSET_SECONDS; // ミリ秒
    */
  }

  //TZ 補正をかけた日時を返す
  static function GetDate($format, $time) {
    return ServerConfig::ADJUST_TIME ? gmdate($format, $time) : date($format, $time);
  }

  //DATETIME 形式の日時を返す
  static function GetDateTime($time) {
    return self::GetDate('Y-m-d H:i:s', $time);
  }

  //TIMESPAMP 形式の日時を返す
  static function GetTimeStamp($time) {
    return self::GetDate('Y/m/d (D) H:i:s', $time);
  }

  //時間 (秒) を変換する
  static function Convert($seconds) {
    $hours   = 0;
    $minutes = 0;
    if ($seconds >= 60) {
      $minutes = floor($seconds / 60);
      $seconds %= 60;
    }
    if ($minutes >= 60) {
      $hours = floor($minutes / 60);
      $minutes %= 60;
    }

    $str = '';
    if ($hours   > 0) $str .= $hours   . Message::HOUR;
    if ($minutes > 0) $str .= $minutes . Message::MINUTE;
    if ($seconds > 0) $str .= $seconds . Message::SECOND;
    return $str;
  }

  //TIMESTAMP 形式の時刻を変換する
  static function ConvertTimeStamp($time_stamp, $date = true) {
    $time = strtotime($time_stamp);
    if (ServerConfig::ADJUST_TIME) $time += ServerConfig::OFFSET_SECONDS;
    return $date ? self::GetTimeStamp($time) : $time;
  }
}

//-- 性別関連クラス --//
class Sex {
  const MALE   = 'male';
  const FEMALE = 'female';

  //定数・表示変換リスト取得
  static function GetList() {
    return array(self::MALE => Message::MALE, self::FEMALE => Message::FEMALE);
  }

  //性別リスト存在判定
  static function Exists($sex) {
    return array_key_exists($sex, self::GetList());
  }
}

//-- 外部リンク生成の基底クラス --//
class ExternalLinkBuilder {
  const TIME = 5; //タイムアウト時間 (秒)

  //サーバ通信状態チェック
  static function CheckConnection($url) {
    $url_stack = explode('/', $url);
    $host = $url_stack[2];
    if (! ($io = @fsockopen($host, 80, $status, $str, self::TIME))) return false;

    stream_set_timeout($io, self::TIME);
    $format = 'GET / HTTP/1.1%sHost: %s%sConnection: Close' . Text::CRLF . Text::CRLF;
    fwrite($io, sprintf($format, Text::CRLF, $host, Text::CRLF));
    $data = fgets($io, 128);
    $stream_stack = stream_get_meta_data($io);
    fclose($io);
    return ! $stream_stack['timed_out'];
  }

  //出力
  static function Output($title, $data) {
    echo <<<EOF
<fieldset>
<legend>{$title}</legend>
<div class="game-list"><dl>{$data}</dl></div>
</fieldset>

EOF;
  }

  //タイムアウトメッセージ出力
  static function OutputTimeOut($title, $url) {
    $format = '%s: Connection timed out (%d seconds)' . Text::LF;
    $stack  = explode('/', $url);
    self::Output($title, sprintf($format, $stack[2], self::TIME));
  }
}

//-- HTML 生成クラス --//
class HTML {
  //共通 HTML ヘッダ生成
  static function GenerateHeader($title, $css = null, $close = false) {
    $format = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>%s</title>
EOF;
    $str = sprintf($format . Text::LF, ServerConfig::ENCODE, $title);
    if (is_null($css)) $css = 'action';
    $str .= self::LoadCSS(sprintf('%s/%s', JINROU_CSS, $css));
    if ($close) $str .= self::GenerateBodyHeader();
    return $str;
  }

  //JavaScript ヘッダ生成
  static function GenerateJavaScriptHeader() {
    return '<script type="text/javascript"><!--' . Text::LF;
  }

  //JavaScript フッタ生成
  static function GenerateJavaScriptFooter() {
    return '//--></script>' . Text::LF;
  }

  //ページジャンプ用 JavaScript 生成
  static function GenerateSetLocation() {
    $str = 'if (top != self) { top.location.href = self.location.href; }' . Text::LF;
    return self::GenerateJavaScriptHeader() . $str . self::GenerateJavaScriptFooter();
  }

  //HTML BODY ヘッダ生成
  static function GenerateBodyHeader($css = null, $on_load = null) {
    $str  = isset($css) ? self::LoadCSS($css) : '';
    $body = isset($on_load) ? sprintf('<body onLoad="%s">', $on_load) : '<body>';
    return $str . '</head>' . Text::LF . $body . Text::LF;
  }

  //リンク生成
  static function GenerateLink($url, $str) {
    return sprintf('<a href="%s">%s</a>', $url, $str);
  }

  //ログへのリンク生成
  static function GenerateLogLink($url, $watch = false, $header = '', $css = '', $footer = '') {
    $format = <<<EOF
%s <a target="_top" href="%s"%s>%s</a>
<a target="_top" href="%s&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&heaven_talk=on"%s>%s</a>
<a target="_top" href="%s&heaven_talk=on&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&heaven_only=on"%s >%s</a>
<a target="_top" href="%s&heaven_only=on&reverse_log=on"%s>%s</a>
EOF;
    $str = sprintf($format, $header,
		   $url, $css, Message::LOG_NORMAL,
		   $url, $css, Message::LOG_REVERSE,
		   $url, $css, Message::LOG_DEAD,
		   $url, $css, Message::LOG_DEAD_REVERSE,
		   $url, $css, Message::LOG_HEAVEN,
		   $url, $css, Message::LOG_HEAVEN_REVERSE);
    if ($watch) {
      $format = <<<EOF
<a target="_top" href="%s&watch=on"%s>%s</a>
<a target="_top" href="%s&watch=on&reverse_log=on"%s>%s</a>
EOF;
      $str .= sprintf(Text::LF . $format,
		      $url, $css, Message::LOG_WATCH,
		      $url, $css, Message::LOG_WATCH_REVERSE);
    }
    return $str . $footer;
  }

  //ログへのリンク生成 (観戦モード用)
  static function GenerateWatchLogLink($url, $header = '', $css = '', $footer = '') {
    $format = <<<EOF
%s <a target="_top" href="%s"%s>%s</a>
<a target="_top" href="%s&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&wolf_sight=on"%s >%s</a>
<a target="_top" href="%s&wolf_sight=on&reverse_log=on"%s>%s</a>
EOF;
    $str = sprintf($format, $header,
		   $url, $css, Message::LOG_NORMAL,
		   $url, $css, Message::LOG_REVERSE,
		   $url, $css, Message::LOG_WOLF,
		   $url, $css, Message::LOG_WOLF_REVERSE);
    return $str . $footer;
  }

  //窓を閉じるボタン生成
  static function GenerateCloseWindow($str) {
    $format = <<<EOF
%s%s
<form method="post" action="#">
<input type="button" value="%s" onClick="window.close()">
</form>
EOF;
    return sprintf($format . Text::LF, $str, Text::BR, Message::CLOSE_WINDOW);
  }

  //警告メッセージ生成
  static function GenerateWarning($str) {
    return sprintf('<font color="#FF0000">%s</font>', $str);
  }

  //CSS 読み込み
  static function LoadCSS($path) {
    return sprintf('<link rel="stylesheet" href="%s.css">' . Text::LF, $path);
  }

  //JavaScript 読み込み
  static function LoadJavaScript($file, $path = null) {
    if (is_null($path)) $path = JINROU_ROOT . '/javascript';
    $format = '<script type="text/javascript" src="%s/%s.js"></script>';
    return sprintf($format . Text::LF, $path, $file);
  }

  //共通 HTML ヘッダ出力
  static function OutputHeader($title, $css = null, $close = false) {
    echo self::GenerateHeader($title, $css, $close);
  }

  //CSS 出力
  static function OutputCSS($path) {
    echo self::LoadCSS($path);
  }

  //JavaScript 出力
  static function OutputJavaScript($file, $path = null) {
    echo self::LoadJavaScript($file, $path);
  }

  //HTML BODY ヘッダ出力
  static function OutputBodyHeader($css = null, $on_load = null) {
    echo self::GenerateBodyHeader($css, $on_load);
  }

  //結果ページ HTML ヘッダ出力
  static function OutputResultHeader($title, $url = '') {
    self::OutputHeader($title);
    if ($url != '') {
      printf('<meta http-equiv="Refresh" content="1;URL=%s">' . Text::LF, $url);
    }
    if (is_object(DB::$ROOM)) echo DB::$ROOM->GenerateCSS();
    self::OutputBodyHeader();
  }

  //結果ページ出力
  static function OutputResult($title, $body, $url = '') {
    DB::Disconnect();
    self::OutputResultHeader($title, $url);
    Text::Output($body, true);
    self::OutputFooter(true);
  }

  //使用不可エラー出力
  static function OutputUnusableError() {
    self::OutputResult(Message::DISABLE_ERROR, Message::UNUSABLE_ERROR);
  }

  //HTML フッタ出力
  static function OutputFooter($exit = false) {
    DB::Disconnect();
    echo '</body>' . Text::LF . '</html>';
    if ($exit) exit;
  }

  //共有フレーム HTML ヘッダ出力
  static function OutputFrameHeader($title) {
    $format = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s">
<title>%s</title>
</head>
EOF;
    printf($format . Text::LF, ServerConfig::ENCODE, $title);
  }

  //フレーム HTML フッタ出力
  static function OutputFrameFooter() {
    $format = <<<EOF
<noframes>
<body>
%s
</body>
</noframes>
</frameset>
</html>
EOF;
    printf($format, Message::NO_FRAME);
  }
}
