<?php
//-- 外部リンク生成の基底クラス --//
class ExternalLinkBuilder {
  const TIME = 5; //タイムアウト時間 (秒)

  //サーバ通信状態チェック
  static function CheckConnection($url) {
    $url_stack  = explode('/', $url);
    $host = $url_stack[2];
    if (! ($io = @fsockopen($host, 80, $status, $str, self::TIME))) return false;

    stream_set_timeout($io, self::TIME);
    fwrite($io, sprintf("GET / HTTP/1.1\r\nHost: %s\r\nConnection: Close\r\n\r\n", $host));
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

//-- 「福引」クラス --//
class Lottery {
  static public $display = false;

  //確率表示設定 (デバッグ用)
  static function d($flag = true) {
    self::$display = $flag;
  }

  //確率判定
  static function Rate($base, $rate) {
    $rand = mt_rand(1, $base);
    if (self::$display) Text::p(sprintf('%d <= %d', $rand, $rate), 'rate');
    return $rand <= $rate;
  }

  //bool 判定
  static function Bool() { return self::Percent(50); }

  //パーセント判定
  static function Percent($rate) { return self::Rate(100, $rate); }

  //配列からランダムに一つ取り出す
  static function Get(array $list) {
    return count($list) > 0 ? $list[mt_rand(0, count($list) - 1)] : null;
  }

  //一定範囲からランダムに取り出す
  static function GetRange($from, $to) { return self::Get(range($from, $to)); }

  //闇鍋モードの配役リスト取得
  static function GetChaos(array $list, array $filter) {
    foreach ($filter as $role => $rate) { //出現率補正
      if (isset($list[$role])) $list[$role] = round($list[$role] * $rate);
    }
    return $list;
  }

  //「比」の配列から一つ引く
  static function Draw(array $list) { return self::Get(self::Generate($list)); }

  //「比」の配列から「福引き」を作成する
  static function Generate(array $list) {
    $stack = array();
    foreach ($list as $role => $rate) {
      for (; $rate > 0; $rate--) $stack[] = $role;
    }
    return $stack;
  }

  //「福引き」を一定回数行ってリストに追加する
  static function Add(array &$list, array $random_list, $count) {
    for (; $count > 0; $count--) {
      $role = self::Get($random_list);
      isset($list[$role]) ? $list[$role]++ : $list[$role] = 1;
    }
  }

  //「比」から「確率」に変換する (テスト用)
  static function ToProbability(array $list) {
    $stack = array();
    $total = array_sum($list);
    foreach ($list as $role => $rate) {
      $stack[$role] = sprintf('%01.2f', $rate / $total * 100);
    }
    Text::p($stack);
  }
}
