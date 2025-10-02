<?php
//-- テキスト処理クラス --//
class Text {
  const BR   = '<br>';
  const LF   = "\n";
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
  static function Crypt($str) { return sha1(ServerConfig::SALT . $str); }

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
  static function Trip($str) {
    if (GameConfig::TRIP) {
      if (get_magic_quotes_gpc()) $str = stripslashes($str); // \ を自動でつける処理系対策
      //トリップ関連のキーワードを置換
      $str = str_replace(array('◆', '＃'), array('◇', '#'), $str);
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
	$str = $name . '◆' . $trip;
      }
      //self::p($str, 'Result');
    }
    elseif (strpos($str, '#') !== false || strpos($str, '＃') !== false) {
      $sentence = "トリップは使用不可です。<br>\n" . '"#" 又は "＃" の文字も使用不可です。';
      HTML::OutputResult('村人登録 [入力エラー]', $sentence);
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
    $replace_list = array('&' => '&amp;', '<' => '&lt;', '>' => '&gt;',
			  '\\' => '&yen;', '"' => '&quot;', "'" => '&#039;');
    $str = strtr($str, $replace_list);
    $str = $trim ? trim($str) : str_replace(array("\r\n", "\r", "\n"), "\n", $str);
    return $str;
  }

  //改行コードを <br> に変換する (PHP5.3 以下の nl2br() だと <br /> 固定なので HTML 4.01 だと不向き)
  static function Line(&$str) {
    return $str = str_replace(self::LF, self::BR, $str);
  }

  /* デバッグ用 */
  //改行タグ付きテキスト出力
  static function d($str = '') { self::Output($str, true); }

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
}

//-- セキュリティ関連クラス --//
class Security {
  //IPアドレス取得
  static function GetIP() { return @$_SERVER['REMOTE_ADDR']; }

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
    if ($hours   > 0) $str .= $hours   . '時間';
    if ($minutes > 0) $str .= $minutes . '分';
    if ($seconds > 0) $str .= $seconds . '秒';
    return $str;
  }

  //TIMESTAMP 形式の時刻を変換する
  static function ConvertTimeStamp($time_stamp, $date = true) {
    $time = strtotime($time_stamp);
    if (ServerConfig::ADJUST_TIME) $time += ServerConfig::OFFSET_SECONDS;
    return $date ? self::GetDate('Y/m/d (D) H:i:s', $time) : $time;
  }
}

//-- HTML 生成クラス --//
class HTML {
  const HEADER = "</head>\n<body>\n";
  const FOOTER = "</body>\n</html>";
  const JUMP   = "<meta http-equiv=\"Refresh\" content=\"1;URL=%s\">\n";
  const CSS    = "<link rel=\"stylesheet\" href=\"%s.css\">\n";
  const JS     = "<script type=\"text/javascript\" src=\"%s/%s.js\"></script>\n";

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
    $str = sprintf($format, ServerConfig::ENCODE, $title);
    if (is_null($css)) $css = 'action';
    $str .= self::LoadCSS(sprintf('%s/%s', JINRO_CSS, $css));
    if ($close) $str .= self::GenerateBodyHeader();
    return $str;
  }

  //ページジャンプ用 JavaScript 生成
  static function GenerateSetLocation() {
    $str = <<<EOF
<script type="text/javascript"><!--
if (top != self) { top.location.href = self.location.href; }
%s
EOF;
    return sprintf($str, "//--></script>\n");
  }

  //HTML ヘッダクローズ
  static function GenerateBodyHeader($css = null) {
    $str = isset($css) ? self::LoadCSS($css) : '';
    return $str . self::HEADER;
  }

  //ログへのリンク生成
  static function GenerateLogLink($url, $watch = false, $header = '', $css = '', $footer = '') {
    $str = <<<EOF
{$header} <a target="_top" href="{$url}"{$css}>正</a>
<a target="_top" href="{$url}&reverse_log=on"{$css}>逆</a>
<a target="_top" href="{$url}&heaven_talk=on"{$css}>霊</a>
<a target="_top" href="{$url}&reverse_log=on&heaven_talk=on"{$css}>逆&amp;霊</a>
<a target="_top" href="{$url}&heaven_only=on"{$css} >逝</a>
<a target="_top" href="{$url}&reverse_log=on&heaven_only=on"{$css}>逆&amp;逝</a>
EOF;

    if ($watch) {
      $str .= <<<EOF

<a target="_top" href="{$url}&watch=on"{$css}>観</a>
<a target="_top" href="{$url}&watch=on&reverse_log=on"{$css}>逆&amp;観</a>
EOF;
    }
    return $str . $footer;
  }

  //ログへのリンク生成 (観戦モード用)
  static function GenerateWatchLogLink($url, $header = '', $css = '', $footer = '') {
    $str = <<<EOF
{$header} <a target="_top" href="{$url}"{$css}>正</a>
<a target="_top" href="{$url}&reverse_log=on"{$css}>逆</a>
<a target="_top" href="{$url}&wolf_sight=on"{$css}>正&amp;狼</a>
<a target="_top" href="{$url}&wolf_sight=on&reverse_log=on"{$css}>逆&amp;狼</a>{$footer}
EOF;
    return $str;
  }

  //CSS 読み込み
  static function LoadCSS($path) { return sprintf(self::CSS, $path); }

  //JavaScript 読み込み
  static function LoadJavaScript($file, $path = null) {
    if (is_null($path)) $path = JINRO_ROOT . '/javascript';
    return sprintf(self::JS, $path, $file);
  }

  //共通 HTML ヘッダ出力
  static function OutputHeader($title, $css = null, $close = false) {
    echo self::GenerateHeader($title, $css, $close);
  }

  //CSS 出力
  static function OutputCSS($path) { echo self::LoadCSS($path); }

  //JavaScript 出力
  static function OutputJavaScript($file, $path = null) {
    echo self::LoadJavaScript($file, $path);
  }

  //HTML ヘッダクローズ出力
  static function OutputBodyHeader($css = null) { echo self::GenerateBodyHeader($css); }

  //結果ページ HTML ヘッダ出力
  static function OutputResultHeader($title, $url = '') {
    self::OutputHeader($title);
    if ($url != '') printf(self::JUMP, $url);
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

  //HTML フッタ出力
  static function OutputFooter($exit = false) {
    DB::Disconnect();
    echo self::FOOTER;
    if ($exit) exit;
  }

  //共有フレーム HTML ヘッダ出力
  static function OutputFrameHeader($title) {
    $str = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s">
<title>%s</title>
</head>

EOF;
    printf($str, ServerConfig::ENCODE, $title);
  }

  //フレーム HTML フッタ出力
  static function OutputFrameFooter() {
    echo <<<EOF
<noframes>
<body>
フレーム非対応のブラウザの方は利用できません。
</body>
</noframes>
</frameset>
</html>
EOF;
  }
}
