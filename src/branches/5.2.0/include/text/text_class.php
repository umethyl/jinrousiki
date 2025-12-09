<?php
//-- テキスト関連 --//
final class Text {
  const CR   = "\r";
  const LF   = "\n";
  const BR   = '<br>';
  const CRLF = "\r\n";
  const BRLF = "<br>\n";

  /* 判定 */
  //存在
  public static function Exists($str) {
    return self::Over($str ?? '', 0);
  }

  //検索
  public static function Search($str, $target) {
    return false !== strpos($str ?? '', $target);
  }

  //先頭
  public static function IsPrefix($str, $target) {
    return strpos($str ?? '', $target) === 0;
  }

  //文字数上限
  public static function Over($str, $limit) {
    return strlen($str ?? '') > $limit;
  }

  //RGB 文字列
  public static function IsRGB($str) {
    return strlen($str) == 7 && substr($str, 0, 1) == '#' && ctype_xdigit(substr($str, 1, 7));
  }

  /* 変換 */
  //整形
  public static function Format(...$stack) {
    $format = array_shift($stack);
    return self::LineFeed(vsprintf($format, $stack));
  }

  //改行追加
  public static function LineFeed($str) {
    return $str . self::LF;
  }

  //改行結合 (Text::BRLF 固定)
  public static function Join(...$stack) {
    return ArrayFilter::Concat($stack, self::BRLF);
  }

  //改行コードを <br> に変換する (PHP5.3 以下の nl2br() だと <br /> 固定なので HTML 4.01 だと不向き)
  public static function ConvertLine($str) {
    return str_replace(self::LF, self::BR, $str);
  }

  //折り返し
  public static function Fold($count, $str, $base = Position::BASE) {
    return ($count > 0 && $count % $base == 0) ? self::LineFeed($str) : null;
  }

  //ヘッダ追加
  public static function AddHeader($str, $header, $delimiter = ': ') {
    return self::Exists($header) ? $header . $delimiter . $str : $str;
  }

  //フッタ追加
  public static function AddFooter($str, $footer, $delimiter = '_') {
    return self::Exists($footer) ? $str . $delimiter . $footer : $str;
  }

  //カッコで括る
  public static function Quote($str, $header = '(', $footer = ')') {
    return $header . $str . $footer;
  }

  //カッコで括る (ブラケット版)
  public static function QuoteBracket($str) {
    return self::Quote($str, '[', ']');
  }

  //分離 (explode() ラッパー)
  public static function Parse($str, $delimiter = ' ', $limit = null) {
    if (null === $str) {
      return [];
    }
    return self::Exists($limit) ? explode($delimiter, $str, $limit) : explode($delimiter, $str);
  }

  //分割
  public static function Split($str) {
    $stack = [];
    $count = self::Count($str);
    for ($i = 0; $i < $count; $i++) {
      $stack[$i] = mb_substr($str, $i, 1);
    }
    return $stack;
  }

  //キーワードで分離して先頭を取り出す
  public static function CutPick($str, $delimiter = '_', $limit = null) {
    return ArrayFilter::Pick(self::Parse($str, $delimiter, $limit));
  }

  //キーワードで分離して末尾を取り出す
  public static function CutPop($str, $delimiter = '_', $limit = null) {
    return ArrayFilter::Pop(self::Parse($str, $delimiter, $limit));
  }

  //切り詰め
  public static function Shrink($str, $limit) {
    return mb_substr($str, 0, $limit);
  }

  //暗号化
  public static function Crypt($str) {
    return sha1(ServerConfig::SALT . $str);
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
  public static function Trip($str) {
    if (GameConfig::TRIP) {
      //トリップ関連のキーワードを置換
      $trip_list = [Message::TRIP, Message::TRIP_KEY];
      $str = str_replace($trip_list, [Message::TRIP_CONVERT, '#'], $str ?? '');

      $trip_start = mb_strpos($str, '#');
      if (false !== $trip_start) { //トリップキーの位置を検索
	$name = self::Shrink($str, $trip_start);
	$key  = mb_substr($str, $trip_start + 1);
	//self::p(sprintf('%s, name: %s, key: %s', $trip_start, $name, $key), '◆Trip Start');
	$key  = Encoder::Convert($key, ServerConfig::ENCODE, 'SJIS'); //文字コードを変換

	if (GameConfig::TRIP_2ch && self::Over($key, 12 - 1)) {
	  $trip = self::ConvertTrip2ch($key);
	} else {
	  $trip = self::ConvertTrip($key);
	}
	$str = $name . Message::TRIP . $trip;
      }
      //self::p($str, 'Result');
    } elseif (self::Search($str, '#') || self::Search($str, Message::TRIP_KEY)) {
      $sentence = sprintf(Message::TRIP_FORMAT, '#', Message::TRIP_KEY);
      HTML::OutputResult(Message::TRIP_ERROR, Message::DISABLE_TRIP . self::BRLF . $sentence);
    }

    return self::Escape($str); //特殊文字のエスケープ
  }

  //トリップ変換処理
  private static function ConvertTrip($key) {
    $salt = substr($key . 'H.', 1, 2);

    //$salt =~ s/[^\.-z]/\./go; にあたる箇所
    $pattern = '/[\x00-\x20\x7B-\xFF]/';
    $salt    = preg_replace($pattern, '.', $salt);

    //特殊文字の置換
    $from_list = [':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`'];
    $to_list   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'a', 'b',  'c', 'd', 'e', 'f'];
    $salt      = str_replace($from_list, $to_list, $salt);

    return substr(crypt($key, $salt), -10);
  }

  //トリップ変換処理 (2ch 仕様)
  private static function ConvertTrip2ch($key) {
    $trip_mark = substr($key, 0, 1);
    if ($trip_mark == '#' || $trip_mark == '$') {
      if (preg_match('|^#([[:xdigit:]]{16})([./0-9A-Za-z]{0,2})$|', $key, $stack)) {
	$trip = substr(crypt(pack('H*', $stack[1]), "{$stack[2]}.."), -12);
      } else {
	$trip = '???';
      }
    } else {
      $trip = str_replace('+', '.', substr(base64_encode(sha1($key, true)), 0, 12));
    }
    return $trip;
  }

  //文字数カウント
  public static function Count($str) {
    return mb_strlen($str);
  }

  /* 出力 */
  //出力
  public static function Output($str = '', $line = false) {
    echo self::LineFeed($str . (true === $line ? self::BR : ''));
  }

  //出力 (NULL 対応版)
  public static function OutputExists($str) {
    if (null === $str) {
      return null;
    }
    echo $str;
  }

  //出力 (折り返し用)
  public static function OutputFold($count, $str, $base = Position::BASE) {
    self::OutputExists(self::Fold($count, $str, $base));
  }

  //出力 (整形用)
  public static function Printf(...$stack) {
    $format = array_shift($stack);
    echo self::LineFeed(vsprintf($format, $stack));
  }

  /* 更新系 */
  //特殊文字のエスケープ処理
  //htmlentities() を使うと文字化けを起こしてしまうようなので敢えてべたに処理
  public static function Escape(&$str, $trim = true) {
    if (is_array($str)) {
      $stack = [];
      foreach ($str as $item) {
	$stack[] = self::Escape($item);
      }
      return $stack;
    }

    //$str = htmlentities($str, ENT_QUOTES); //UTF に移行したら機能する？
    $replace_list = [
      '&'  => '&amp;',
      '<'  => '&lt;',
      '>'  => '&gt;',
      '\\' => '&yen;',
      '"'  => '&quot;',
      "'"  => '&#039;'
    ];
    $str = strtr($str ?? '', $replace_list);
    if (true === $trim) {
      $str = trim($str);
    } else {
      $str = str_replace([self::CRLF, self::CR, self::LF], self::LF, $str);
    }
    return $str;
  }

  /* デバッグ用 */
  //改行タグ付きテキスト出力
  public static function d($str = '') {
    self::Output($str, true);
  }

  //データ表示
  public static function p($data, $name = null) {
    $str = (is_array($data) || is_object($data)) ? print_r($data, true) : $data;
    self::d(self::AddHeader($str, $name));
  }

  //データダンプ
  public static function v($data, $name = null) {
    self::OutputExists(self::AddHeader(null, $name));
    var_dump($data);
    self::d();
  }

  //Talk 出力
  public static function t($data, $name = null) {
    $builder = class_exists('Talk') ? Talk::GetBuilder() : null;
    return (null === $builder) ? self::p($data, $name) : $builder->TalkDebug($data, $name);
  }
}
