<?php
//-- システムユーザクラス --//
class GM {
  const ID        = 1; //ユーザ ID
  const SYSTEM    = 'system'; //システムユーザ
  const DUMMY_BOY = 'dummy_boy'; //身代わり君
}

//-- 汎用スタッククラス --//
class Stack {
  //初期化
  public function Init($name) {
    //Text::p($name, '◆Stack/Init');
    $this->Set($name, array());
  }

  //取得
  public function Get($name) {
    //Text::p($name, '◆Stack/Get[Get]');
    return isset($this->$name) ? $this->$name : null;
  }

  //取得 (配列)
  public function GetKey($name, $key) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return null;
    return isset($stack[$key]) ? $stack[$key] : null;
  }

  //セット
  public function Set($name, $data) {
    //Text::p($data, "◆Stack/Set[{$name}]");
    $this->$name = $data;
  }

  //追加
  public function Add($name, $data) {
    //Text::p($data, "◆Stack/Add[{$name}]");
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    $stack[] = $data;
    $this->Set($name, $stack);
  }

  //存在判定
  public function Exists($name) {
    //Text::p($name, '◆Stack/Exists');
    return $this->Count($name) > 0;
  }

  //存在判定 (配列)
  public function ExistsKey($name, $key) {
    //Text::p($name, "◆Stack/Exists[{$key}]");
    $stack = $this->GetArray($name);
    return is_array($stack) && array_key_exists($key, $stack);
  }

  //存在判定 (in_array() ラッパー)
  public function ExistsArray($name, $value) {
    //Text::p($name, "◆Stack/ExistsArray[{$value}]");
    $stack = $this->GetArray($name);
    return is_array($stack) && in_array($value, $stack);
  }

  //未設定判定
  public function IsEmpty($name) {
    return is_null($this->Get($name));
  }

  //カウント
  public function Count($name) {
    return count($this->Get($name));
  }

  //シャッフル
  public function Shuffle($name) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    shuffle($stack);
    $this->Set($name, $stack);
  }

  //削除
  public function Delete($name, $data) {
    //Text::p($data, "◆Stack/Delete[{$name}]");
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    $key = array_search($data, $stack);
    if ($key !== false) $this->DeleteKey($name, $key);
  }

  //削除 (キー指定)
  public function DeleteKey($name, $key) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    unset($stack[$key]);
    $this->Set($name, $stack);
  }

  //削除 (差分指定)
  public function DeleteDiff($name, array $list) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    $this->Set($name, array_values(array_diff($stack, $list)));
  }

  //消去
  public function Clear($name) {
    //Text::p($name, '◆Stack/Clear');
    unset($this->$name);
  }

  //表示 (デバッグ用)
  public function p($data = null, $name = null) {
    Text::p(isset($data) ? $this->Get($data) : $this, $name);
  }

  //取得 (配列固定)
  private function GetArray($name) {
    $stack = $this->Get($name);
    return is_array($stack) ? $stack : null;
  }
}

//-- フラグ専用スタッククラス --//
class FlagStack extends Stack {
  public function __get($name) {
    //Text::p($name, '◆FlagStack/__get');
    return $this->Off($name);
  }

  public function Set($name, $data) {
    //Text::v($data, $name);
    $this->$name = true === $data;
  }

  //ON
  public function On($name) {
    $this->Set($name, true);
  }

  //OFF
  public function Off($name) {
    $this->Set($name, false);
  }
}

//-- 「福引」クラス --//
class Lottery {
  static public $display = false;

  //乱数取得
  static function Rand($max) {
    return mt_rand(1, $max);
  }

  //確率判定
  static function Rate($base, $rate) {
    $rand = self::Rand($base);
    if (self::$display) Text::p(sprintf('%d <= %d', $rand, $rate), '◆Rate');
    return $rand <= $rate;
  }

  //パーセント判定
  static function Percent($rate) {
    return self::Rate(100, $rate);
  }

  //bool 判定
  static function Bool() {
    return self::Percent(50);
  }

  //配列からランダムに一つ取り出す
  static function Get(array $list) {
    return count($list) > 0 ? $list[self::Rand(count($list)) - 1] : null;
  }

  //一定範囲からランダムに取り出す
  static function GetRange($from, $to) {
    return self::Get(range($from, $to));
  }

  //パーセント値取得
  static function GetPercent() {
    return self::Rand(100);
  }

  //シャッフルした配列を返す
  static function GetList(array $list) {
    shuffle($list);
    return $list;
  }

  //闇鍋モードの配役リスト取得
  static function GetChaos(array $list, array $filter) {
    foreach ($filter as $role => $rate) { //出現率補正
      if (isset($list[$role])) $list[$role] = round($list[$role] * $rate);
    }
    return $list;
  }

  //「比」の配列から一つ引く
  static function Draw(array $list) {
    return self::Get(self::Generate($list));
  }

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

  //確率表示設定 (デバッグ用)
  static function d($flag = true) {
    self::$display = $flag;
  }
}
