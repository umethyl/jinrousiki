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

//-- 文字コード関連 --//
final class Encoder {
  //変換
  public static function Convert($str, $encode, $convert = ServerConfig::ENCODE) {
    if ($encode == '' || $encode == 'ASCII' || $encode == $convert) {
      return $str;
    } else {
      return mb_convert_encoding($str, $convert, $encode);
    }
  }

  //BOM 消去
  public static function BOM(string $str) {
    if (ord($str[0]) == '0xef' && ord($str[1]) == '0xbb' && ord($str[2]) == '0xbf') {
      $str = substr($str, 3);
    }
    return $str;
  }

  //POST されたデータの文字コードを統一する
  public static function Post() {
    self::Filter($_POST);
  }

  //配列データフィルタリング (POST 変換用)
  private static function Filter(array &$list) {
    foreach ($list as $key => $value) {
      //多段配列対応(例: アイコンのカテゴリ)
      if (is_array($value)) {
	self::Filter($value);
      } else {
	$list[$key] = self::Convert($value, self::Detect($value));
      }
    }
  }

  //文字コード判定
  public static function Detect(string $str) {
    if (self::UTF($str)) {
      return 'UTF-8';
    } else {
      return @mb_detect_encoding($str, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
    }
  }

  //UTF-8判定
  private static function UTF(string $str) {
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
      $c = ord($str[$i]);
      if ($c > 128) {
	if ($c > 247) {
	  return false;
	} elseif ($c > 239) {
	  $bytes = 4;
	} elseif ($c > 223) {
	  $bytes = 3;
	} elseif ($c > 191) {
	  $bytes = 2;
	} else {
	  return false;
	}
	if (($i + $bytes) > $len) {
	  return false;
	}
	while ($bytes > 1) {
	  $i++;
	  if (Number::OutRange(ord($str[$i]), 128, 191)) {
	    return false;
	  }
	  $bytes--;
	}
      }
    }
    return true;
  }
}

//-- 数字判定関連 --//
final class Number {
  //範囲内 (a < target <= b)
  public static function Within($number, $from, $to) {
    return $from < $number && $number <= $to;
  }

  //範囲内 (a < target < b)
  public static function InRange($number, $from, $to) {
    return $from < $number && $number < $to;
  }

  //範囲外 (target < a || b < target)
  public static function OutRange($number, $from, $to) {
    return $number < $from || $to < $number;
  }

  //倍数
  public static function Multiple($number, $base, $target, $limit = null) {
    if (null === $limit) {
      return $number % $base == $target;
    } else {
      return $number < $limit || $number % $base == $target;
    }
  }

  //偶数
  public static function Even($number, $limit = null) {
    return self::Multiple($number, 2, 0, $limit);
  }

  //奇数
  public static function Odd($number, $limit = null) {
    return self::Multiple($number, 2, 1, $limit);
  }

  //3の倍数
  public static function MultipleThree($number, $limit = null) {
    return self::Multiple($number, 3, 0, $limit);
  }

  //割合
  public static function Percent(int $number, int $base, int $digit) {
    return sprintf('%.' . $digit . 'f', $number / $base * 100);
  }
}

//-- Switch (bool) 関連 --//
final class Switcher {
  const ON  = 'on';
  const OFF = 'off';
  const OK  = 'true';
  const NG  = 'false';

  /* 判定 */
  //ON・OFF 判定
  public static function IsOn($str) {
    return $str == self::ON;
  }

  /* 変換 */
  //ON・OFF 変換
  public static function Get($flag) {
    return (true === $flag) ? self::ON : self::OFF;
  }

  //OK・NG 変換
  public static function GetBool($flag) {
    return (true === $flag) ? self::OK : self::NG;
  }
}

//-- URL 関連 --//
final class URL {
  const EXT       = '.php';
  const HEAD      = '?';
  const ADD       = '&';
  const DELIMITER = '/';
  const PAGE      = '#';

  /* 判定 */
  //存在判定 (db_no)
  public static function ExistsDB() {
    return is_int(RQ::Get(RequestDataGame::DB)) && RQ::Get(RequestDataGame::DB) > 0;
  }

  /* パラメータ取得 */
  //拡張子 + ヘッダー取得
  public static function GetExt() {
    return self::EXT . self::HEAD;
  }

  /* 変換 (URL) */
  //分割
  public static function Parse($url) {
    return Text::Parse($url, self::DELIMITER);
  }

  //結合 (URL)
  public static function Combine(...$list) {
    return ArrayFilter::Concat($list, self::DELIMITER);
  }

  /* リンク生成 */
  //取得 (部屋共通)
  public static function GetRoom($url, $id = null) {
    $value = (null === $id) ? DB::$ROOM->id : $id;
    return self::GenerateInt($url, RequestDataGame::ID, $value);
  }

  //取得 (ヘッダー/db_no)
  public static function GetHeaderDB($url) {
    return $url . self::EXT . self::GetDB(self::HEAD);
  }

  //取得 (移動用)
  public static function GetJump($url) {
    return sprintf(Message::JUMP, $url);
  }

  //取得 (自動更新)
  public static function GetReload($time) {
    return self::AddInt(RequestDataGame::RELOAD, $time);
  }

  //取得 (アイコン一覧)
  public static function GetIcon($url, $icon_no) {
    return $url . self::HEAD . self::ConvertInt('icon_no', $icon_no);
  }

  //取得 (検索リンク)
  public static function GetSearch($url, array $list) {
    $head = false;
    foreach ($list as $key => $value) {
      if (false === $head) {
	if (self::ExistsDB()) {
	  $str = self::GetHeaderDB($url) . self::AddString($key, $value);
	} else {
	  $str = $url . self::GetExt() . self::ConvertString($key, $value);
	}
	$head = true;
      } else {
	$str .= self::AddString($key, $value);
      }
    }
    return $str;
  }

  //取得 (新役職情報)
  public static function GetRole($role) {
    if (RoleDataManager::IsSub($role)) {
      $camp = 'sub_role';
    } else {
      $camp = RoleDataManager::GetCamp($role);
    }
    $page = ArrayFilter::Concat(['info', 'new_role', $camp], self::DELIMITER);
    return $page . self::EXT . self::PAGE . $role;
  }

  /* ヘッダーリンク生成 */
  //ヘッダーリンク生成 (数値型)
  public static function GenerateInt($url, $key, $value) {
    return $url . self::GetExt() . self::ConvertInt($key, $value);
  }

  //ヘッダーリンク生成 (bool 型)
  public static function GenerateSwitch($url, $key) {
    return $url . self::GetExt() . self::ConvertSwitch($key);
  }

  /* パラメータ加工 */
  //結合 (パラメータ)
  public static function Concat(array $list) {
    return ArrayFilter::Concat($list, self::ADD);
  }

  /* パラメータ生成 */
  //パラメータ生成 (数値型)
  public static function ConvertInt($key, $value) {
    return sprintf('%s=%d', $key, $value);
  }

  //パラメータ生成 (文字型)
  public static function ConvertString($key, $value) {
    return sprintf('%s=%s', $key, $value);
  }

  //パラメータ生成 (bool 型)
  public static function ConvertSwitch($key) {
    return self::ConvertString($key, Switcher::ON);
  }

  //パラメータ生成 (配列)
  public static function ConvertList($key, $value) {
    return sprintf('%s[]=%s', $key, $value);
  }

  //パラメータ追加 (数値型)
  public static function AddInt($key, $value) {
    return self::ADD . self::ConvertInt($key, $value);
  }

  //パラメータ追加 (文字型)
  public static function AddString($key, $value) {
    return self::ADD . self::ConvertString($key, $value);
  }

  //パラメータ追加 (bool 型)
  public static function AddSwitch($key) {
    return self::AddString($key, Switcher::ON);
  }

  //パラメータ追加 (db_no)
  public static function AddDB() {
    return self::GetDB(self::ADD);
  }

  //取得 (db_no)
  private static function GetDB($str) {
    if (self::ExistsDB()) {
      $key = RequestDataGame::DB;
      return $str . self::ConvertInt($key, RQ::Get($key));
    } else {
      return '';
    }
  }
}

//-- 配列関連 --//
final class ArrayFilter {
  /* 取得 */
  //取得
  public static function Get(array $list, $key) {
    return self::IsKey($list, $key) ? $list[$key] : null;
  }

  //取得 (int 型)
  public static function GetInt(array $list, $key) {
    return (int) self::Get($list, $key);
  }

  //取得 (配列型)
  public static function GetList(array $list, $key) {
    return self::IsAssoc($list, $key) ? $list[$key] : [];
  }

  //取得 (array_keys() ラッパー)
  public static function GetKeyList(array $list, $key = null) {
    return (null === $key) ? array_keys($list) : array_keys($list, $key);
  }

  //取得 (引数)
  public static function GetArg(array $list) {
    return is_array($list[0]) ? $list[0] : $list;
  }

  //先頭取得
  public static function Pick(array $list) {
    return array_shift($list);
  }

  //末尾取得
  public static function Pop(array $list) {
    return array_pop($list);
  }

  //先頭取得 (key ベース)
  public static function PickKey(array $list) {
    return self::Pick(array_keys($list));
  }

  //末尾取得 (key ベース)
  public static function PopKey(array $list) {
    return self::Pop(array_keys($list));
  }

  //最大値取得 (key ベース)
  public static function GetMax(array $list) {
    return max(array_keys($list));
  }

  //最小値取得 (key ベース)
  public static function GetMin(array $list) {
    return min(array_keys($list));
  }

  //最大値 key 取得
  public static function GetMaxKey(array $list) {
    return $list[self::GetMax($list)];
  }

  /* 判定 */
  //配列添字
  public static function IsKey(array $list, $key) {
    return isset($list[$key]);
  }

  //連想配列
  public static function IsAssoc(array $list, $key) {
    return self::IsKey($list, $key) && is_array($list[$key]);
  }

  //配列添字 (連想配列)
  /*
    連想配列の中身の評価の有無が異なる。
    $list = ['a' => ['b' => null]];
    self::IsAssocKey(  $list, 'a', 'b') => false
    self::IsIncludeKey($list, 'a', 'b') => true
  */
  public static function IsAssocKey(array $list, $assoc_key, $key) {
    return isset($list[$assoc_key][$key]);
  }

  //存在 (key ベース)
  public static function Exists($data, $key) {
    return is_array($data) && self::IsKey($data, $key);
  }

  //存在 (is_array() && in_array() ラッパー)
  public static function IsInclude($data, $value) {
    return is_array($data) && in_array($value, $data);
  }

  //存在 (連想配列内 key)
  public static function IsIncludeKey(array $list, $key, $value) {
    return self::IsKey($list, $key) && array_key_exists($value, $list[$key]);
  }

  /* 変換 */
  //配列化
  public static function Pack($data) {
    return is_array($data) ? $data : [$data];
  }

  //型変換
  public static function Cast($data, $fill = false) {
    return is_array($data) ? $data : self::Fill($fill);
  }

  //空データ
  public static function Fill($flag) {
    return (true === $flag) ? [] : null;
  }

  //結合 (implode() ラッパー)
  public static function Concat(array $list, $delimiter = ' ') {
    return implode($delimiter, $list);
  }

  //結合 (key ベース)
  public static function ConcatKey(array $list, $delimiter = ' ') {
    return self::Concat(array_keys($list), $delimiter);
  }

  //結合 (array_reverse() ラッパー)
  public static function ConcatReverse(array $list, $delimiter = ' ') {
    return self::Concat(array_reverse($list), $delimiter);
  }

  //CSV変換
  public static function ToCSV(array $list) {
    return self::Concat($list, ',');
  }

  //カウント
  public static function CountKey(array $list, $key = null) {
    return count(self::GetKeyList($list, $key));
  }

  /* 更新系 */
  //初期化
  public static function Initialize(array &$list, $target, $value = []) {
    if (is_array($target)) {
      foreach ($target as $key) {
	self::Initialize($list, $key);
      }
    } else {
      $key = $target;
    }

    if (false === self::IsAssoc($list, $key)) {
      $list[$key] = $value;
    }
  }

  //空配列化
  public static function Reset(array &$list, $target) {
    if (is_array($target)) {
      foreach ($target as $key) {
	self::Reset($list, $key);
      }
    } else {
      $key = $target;
    }
    $list[$key] = [];
  }

  //追加
  public static function Add(array &$list, $key, $value = 1) {
    if (self::IsKey($list, $key)) {
      $list[$key] += $value;
    } else {
      $list[$key]  = $value;
    }
  }

  //登録
  public static function Register(array &$list, $value) {
    if (false === in_array($value, $list)) {
      $list[] = $value;
    }
  }

  //bool 反転
  public static function ReverseBool(array &$list, $key) {
    if (self::IsKey($list, $key)) {
      $list[$key] = (false === $list[$key]);
    } else {
      $list[$key] = true;
    }
  }

  //置換
  public static function Replace(array &$list, $from, $to, $value = 1) {
    $list[$from] -= $value;
    self::Add($list, $to, $value);
  }

  //配列追加
  public static function AddMerge(array &$list, $data) {
    if (is_array($data)) {
      $list = array_merge($list, $data);
    }
  }

  //削除
  public static function Delete(array &$list, $value) {
    $key = array_search($value, $list);
    if (false === $key) {
      return false;
    } else {
      unset($list[$key]);
      return true;
    }
  }

  //切り詰め (削除 + 再生成)
  public static function Shrink(array &$list, $value) {
    if (self::Delete($list, $value)) {
      $list = array_values($list);
    }
  }

  //中身が無い場合は削除
  public static function Sweep(array &$list, $key) {
    if ($list[$key] < 1) {
      unset($list[$key]);
    }
  }
}

//-- 性別関連クラス --//
final class Sex {
  const MALE   = 'male';
  const FEMALE = 'female';

  //定数・表示変換リスト取得
  public static function GetList() {
    return [self::MALE => Message::MALE, self::FEMALE => Message::FEMALE];
  }

  //性別リスト存在判定
  public static function Exists($sex) {
    return array_key_exists($sex, self::GetList());
  }

  //取得
  public static function Get(User $user) {
    return RoleUser::GetSex($user);
  }

  //反転取得
  public static function GetInversion($sex) {
    return ($sex === self::MALE) ? self::FEMALE : self::MALE;
  }

  //鑑定
  public static function Distinguish(User $user) {
    return 'sex_' . self::Get($user);
  }

  //男性判定
  public static function IsMale(User $user) {
    return self::Get($user) === self::MALE;
  }

  //女性判定
  public static function IsFemale(User $user) {
    return self::Get($user) === self::FEMALE;
  }

  //同姓判定
  public static function IsSame(User $a, User $b) {
    return self::Get($a) === self::Get($b);
  }

  //性転換
  public static function Exchange(User $user) {
    $role = self::GetInversion(self::Get($user)) . '_status';
    $user->AddDoom(1, $role);
  }
}

//-- 「福引」クラス --//
final class Lottery {
  public static $display = false;

  //乱数取得
  public static function Rand($max) {
    return mt_rand(1, $max);
  }

  //確率判定
  public static function Rate($base, $rate) {
    $rand = self::Rand($base);
    if (self::$display) {
      Text::p(sprintf('%d <= %d', $rand, $rate), '◆Rate');
    }
    return $rand <= $rate;
  }

  //パーセント判定
  public static function Percent($rate) {
    return self::Rate(100, $rate);
  }

  //bool 判定
  public static function Bool() {
    return self::Percent(50);
  }

  //配列からランダムに一つ取り出す
  public static function Get(array $list) {
    if (count($list) == 1) {
      return array_shift($list);
    } elseif (count($list) > 0) {
      return $list[self::Rand(count($list)) - 1];
    } else {
      return null;
    }
  }

  //一定範囲からランダムに取り出す
  public static function GetRange($from, $to) {
    return self::Get(range($from, $to));
  }

  //パーセント値取得
  public static function GetPercent() {
    return self::Rand(100);
  }

  //シャッフルした配列を返す
  public static function GetList(array $list) {
    shuffle($list);
    return $list;
  }

  //闇鍋モードの配役リスト取得
  public static function GetChaos(array $list, array $filter) {
    foreach ($filter as $role => $rate) { //出現率補正
      if (isset($list[$role])) {
	$list[$role] = round($list[$role] * $rate);
      }
    }
    return $list;
  }

  //「比」の配列から一つ引く
  public static function Draw(array $list) {
    return self::Get(self::Generate($list));
  }

  //「比」の配列から「福引き」を作成する
  public static function Generate(array $list) {
    $stack = [];
    foreach ($list as $role => $rate) {
      for (; $rate > 0; $rate--) {
	$stack[] = $role;
      }
    }
    return $stack;
  }

  //「福引き」を一定回数行ってリストに追加する
  public static function Add(array &$list, array $random_list, $count) {
    $stack = []; //抽選結果
    for (; $count > 0; $count--) {
      $data = self::Get($random_list);
      ArrayFilter::Add($list, $data);
      $stack[] = $data;
    }
    return $stack;
  }

  //「福引き」を一定回数行ってリストに追加する(減算ピック型)
  public static function Pick(array &$list, array $random_list, $count) {
    $stack = []; //抽選結果
    $pick_list = self::GetList($random_list);
    for (; $count > 0; $count--) {
      if (count($pick_list) < 1) {
	break;
      }
      $data = array_pop($pick_list);
      ArrayFilter::Add($list, $data);
      $stack[] = $data;
    }
    return $stack;
  }

  //「比」から「確率」に変換する (テスト用)
  public static function ToProbability(array $list) {
    $stack = [];
    $total = array_sum($list);
    foreach ($list as $role => $rate) {
      $stack[$role] = sprintf('%01.2f', $rate / $total * 100);
    }
    Text::p($stack);
  }

  //確率表示設定 (デバッグ用)
  public static function d($flag = true) {
    self::$display = $flag;
  }
}

//-- セキュリティ関連クラス --//
final class Security {
  /* 取得系 */
  //IPアドレス取得
  public static function GetIP() {
    return @$_SERVER['REMOTE_ADDR'];
  }

  //CSRF対策用トークン取得
  public static function GetToken($id) {
    return md5(ServerConfig::GAME_HASH . $id);
  }

  /* 検証系 */
  /**
   * 実行環境にダメージを与える可能性がある値が含まれているかどうか検査します。
   * @param  : mixed   : $data 検査対象の変数
   * @param  : boolean : $found 疑わしい値が存在しているかどうかを示す値。
                         この値がtrueの場合、強制的に詳細なスキャンが実行されます。
   * @return : boolean : 危険な値が発見された場合 true、それ以外の場合 false
   */
  public static function IsInvalidValue($data, $found = false) {
    $num = '22250738585072011';
    if (true === $found || Text::Search(str_replace('.', '', serialize($data)), $num)) {
      //文字列の中に問題の数字が埋め込まれているケースを排除する
      if (is_array($data)) {
	foreach ($data as $item) {
	  if (self::IsInvalidValue($item, true)) {
	    return true;
	  }
	}
      } else {
	$preg = '/^([0.]*2[0125738.]{15,16}1[0.]*)e(-[0-9]+)$/i';
	$item = strval($data);
	$matches = '';
	if (preg_match($preg, $item, $matches)) {
	  $exp = intval($matches[2]) + 1;
	  if (2.2250738585072011e-307 === floatval("{$matches[1]}e{$exp}")) {
	    return true;
	  }
	}
      }
    }
    return false;
  }

  //リファラ検証
  public static function IsInvalidReferer($page, $white_list = null) {
    if (is_array($white_list)) { //ホワイトリストチェック
      $addr = self::GetIP();
      foreach ($white_list as $host) {
	if (Text::IsPrefix($addr, $host)) {
	  return false;
	}
      }
    }
    $url = ServerConfig::SITE_ROOT . $page;
    return strncmp(@$_SERVER['HTTP_REFERER'], $url, strlen($url)) != 0;
  }

  //CSRF対策用トークン検証
  public static function IsInvalidToken($id) {
    return RQ::Fetch()->token != self::GetToken($id);
  }

  /* 判定系 */
  //ブラックリスト判定 (ログイン用)
  public static function IsLoginBlackList($trip = '') {
    if (GameConfig::TRIP && $trip != '' && in_array($trip, RoomConfig::$white_list_trip)) {
      return false;
    }
    return self::IsBlackList();
  }

  //ブラックリスト判定 (村立て用)
  public static function IsEstablishBlackList() {
    return self::IsLoginBlackList() || self::IsBlackList('establish_');
  }

  //ブラックリスト判定
  private static function IsBlackList($prefix = '') {
    $addr = self::GetIP();
    $host = gethostbyaddr($addr);
    foreach (['white' => false, 'black' => true] as $type => $flag) {
      foreach (RoomConfig::${$prefix . $type . '_list_ip'} as $ip) {
	if (Text::IsPrefix($addr, $ip)) {
	  return $flag;
	}
      }
      $list = RoomConfig::${$prefix . $type . '_list_host'};
      if (isset($list) && preg_match($list, $host)) {
	return $flag;
      }
    }
    return false;
  }
}

//-- 外部リンク生成の基底クラス --//
final class ExternalLinkBuilder {
  const TIME = 5; //タイムアウト時間 (秒)

  //サーバ通信状態チェック
  public static function IsConnect($url) {
    $stack = URL::Parse($url);
    $host  = $stack[2];
    $io    = @fsockopen($host, 80, $status, $str, self::TIME);
    if (! $io) {
      return false;
    }

    stream_set_timeout($io, self::TIME);
    $format = 'GET / HTTP/1.1%sHost: %s%sConnection: Close' . Text::CRLF . Text::CRLF;
    fwrite($io, sprintf($format, Text::CRLF, $host, Text::CRLF));
    $data   = fgets($io, 128);
    $stream = stream_get_meta_data($io);
    fclose($io);
    return ! $stream['timed_out'];
  }

  //出力
  public static function Output($title, $data) {
    HTML::OutputFieldsetHeader($title);
    DivHTML::Output(HTML::GenerateTag('dl', $data), 'game-list');
    HTML::OutputFieldsetFooter();
  }

  //タイムアウトメッセージ出力
  public static function OutputTimeOut($title, $url) {
    $stack  = URL::Parse($url);
    $format = '%s: Connection timed out (%d seconds)';
    self::Output($title, sprintf($format, $stack[2], self::TIME));
  }
}
