<?php
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
