<?php
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
