<?php
//mbstring非対応の場合、エミュレータを使用する
if(! extension_loaded('mbstring')){
  require_once(dirname(__FILE__) . '/../module/mb-emulator.php');
}
require_once(dirname(__FILE__) .  '/setting.php');

//データベース接続
//$header : すでに HTMLヘッダが出力されて [いる / いない]
//$exit   : エラー時に [HTML を閉じて exit を返す / false で終了]
function ConnectDatabase($header = false, $exit = true){
  global $db_host, $db_uname, $db_pass, $db_name;

  if(! ($db_handle = mysql_connect($db_host, $db_uname, $db_pass))){
    if($header){
      echo "<font color=\"#FF0000\">MySQL接続失敗: $db_host</font><br>";
      if($exit)
	OutputHTMLFooter($exit);
      else
	return false;
    }
    else{
      OutputActionResult('MySQL接続失敗', "MySQL接続失敗: $db_host");
    }
  }

  mysql_set_charset('ujis');
  if(! mysql_select_db($db_name, $db_handle)){
    if($header){
      echo "<font color=\"#FF0000\">データベース接続失敗: $db_name</font><br>";
      if($exit)
	OutputHTMLFooter($exit);
      else
	return false;
    }
    else{
      OutputActionResult('データベース接続失敗', "データベース接続失敗: $db_name");
    }
  }

  return $db_handle;
}

//データベースとの接続を閉じる
function DisconnectDatabase($dbHandle){
  mysql_close($dbHandle);
}

//発言を DB に登録する (talk Table)
function InsertTalk($room_no, $date, $location, $uname, $time, $sentence, $font_type, $spend_time){
  mysql_query("INSERT INTO talk(room_no, date, location, uname, time,
				sentence, font_type, spend_time)
		VALUES($room_no, $date, '$location', '$uname', '$time',
				'$sentence', '$font_type', $spend_time)");
}

//セッションIDを新しくする(PHPのバージョンが古いとこの関数が無いので定義する)
if(! function_exists('session_regenerate_id')){
  function session_regenerate_id(){
    $QQ = serialize($_SESSION);
    session_destroy();
    session_id(md5(uniqid(rand(), 1)));
    session_start();
    $_SESSION = unserialize($QQ);
  }
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

//TZ 補正をかけた時刻を返す (環境変数 TZ を変更できない環境想定？)
function TZTime(){
  global $OFFSET_SECONDS;
  return time() + $OFFSET_SECONDS;
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

//POSTされたデータの文字コードを統一する
function EncodePostData(){
  global $ENCODE;

  foreach($_POST as $key => $value){
    $encode_type = mb_detect_encoding($value, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
    if($encode_type != '' && $encode_type != $ENCODE)
      $_POST[$key] = mb_convert_encoding($value, $ENCODE, $encode_type);
  }
}

//入力禁止文字のチェック
function CheckForbiddenStrings($str){
  return (strstr($str, "'") || strstr($str, "\\"));
}

//特殊文字のエスケープ処理
//htmlentities() を使うと文字化けを起こしてしまうようなので敢えてべたに処理
function EscapeStrings(&$str, $trim = true){
  if(get_magic_quotes_gpc()) $str = stripslashes($str); // \ を自動でつける処理系対策
  // $str = htmlentities($str, ENT_QUOTES); //UTF に移行したら機能する？
  $str = str_replace('&' , '&amp;' , $str);
  $str = str_replace('<' , '&lt;'  , $str);
  $str = str_replace('>' , '&gt;'  , $str);
  $str = str_replace('\\', '&yen;' , $str);
  $str = str_replace('"' , '&quot;', $str);
  $str = str_replace("'" , '&#039;', $str);
  if($trim)
    $str = trim($str); //前後の空白と改行コードを削除
  else
    $str = str_replace(array("\r\n", "\r", "\n"), "\n", $str); //改行コードを統一
}

//改行コードを <br> に変換する (nl2br() だと <br /> なので HTML 4.01 だと不向き)
function LineToBR(&$str){
  $str = str_replace("\n", '<br>', $str);
}

//共通 HTML ヘッダ出力
//$path は $CSS_PATH みたいなグローバル変数設定できると楽かな？
function OutputHTMLHeader($title, $css = 'action', $path = 'css'){
  global $ENCODE;

  echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset={$ENCODE}">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>{$title}</title>
<link rel="stylesheet" href="{$path}/{$css}.css">

EOF;
}

//結果ページ HTML ヘッダ出力
function OutputActionResultHeader($title, $url = ''){
  global $day_night;

  OutputHTMLHeader($title);
  if($url != '') echo '<meta http-equiv="Refresh" content="1;URL=' . $url . '">'."\n";
  if($day_night != '')  echo '<link rel="stylesheet" href="css/game_' . $day_night . '.css">'."\n";
  echo '</head><body>'."\n";
}

//結果ページ出力
function OutputActionResult($title, $body, $url = '', $unlock = false){
  global $dbHandle;

  if($unlock) mysql_query('UNLOCK TABLES'); //ロック解除
  if($dbHandle != '') DisconnectDatabase($dbHandle); //DB 接続解除

  OutputActionResultHeader($title, $url);
  echo $body . "\n";
  OutputHTMLFooter(true);
}

//HTML フッタ出力
function OutputHTMLFooter($exit = false){
  echo '</body></html>'."\n";
  if($exit) exit;
}
?>
