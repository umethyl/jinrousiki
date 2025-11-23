<?php
//-- ◆文字化け抑制◆ --//
//-- 位置関連クラス --//
final class Position {
  const BASE = 5; //一列の基数

  //X座標
  public static function GetX($id) {
    return (true === self::IsBase($id) ? self::BASE : $id % self::BASE);
  }

  //Y座標
  public static function GetY($id) {
    return floor($id / self::BASE) + (true === self::IsBase($id) ? 0 : 1);
  }

  //経路距離取得
  public static function GetRouteDistance($id, $viewer) {
    $x = abs(self::GetX($id) - self::GetX($viewer));
    $y = abs(self::GetY($id) - self::GetY($viewer));
    return $x + $y;
  }

  //東
  public static function GetEast($id) {
    $max   = DB::$USER->Count();
    $stack = [];
    for ($i = $id + 1; $i <= $max && $i % self::BASE != 1; $i++) {
      $stack[] = $i;
    }
    return $stack;
  }

  //西
  public static function GetWest($id) {
    $stack = [];
    for ($i = $id - 1; $i > 0 && $i % self::BASE != 0; $i--) {
      $stack[] = $i;
    }
    return $stack;
  }

  //南
  public static function GetSouth($id) {
    $max   = DB::$USER->Count();
    $stack = [];
    for ($i = $id + self::BASE; $i <= $max; $i += self::BASE) {
      $stack[] = $i;
    }
    return $stack;
  }

  //北
  public static function GetNorth($id) {
    $stack = [];
    for ($i = $id - self::BASE; $i > 0; $i -= self::BASE) {
      $stack[] = $i;
    }
    return $stack;
  }

  //縦軸
  public static function GetVertical($id) {
    $max   = DB::$USER->Count();
    $stack = [];
    for ($i = $id % self::BASE; $i <= $max; $i += self::BASE) {
      if ($i > 0) {
	$stack[] = $i;
      }
    }
    return $stack;
  }

  //横軸
  public static function GetHorizontal($id) {
    $max   = DB::$USER->Count();
    $start = $id - self::GetX($id) + 1;
    $stack = [];
    for ($i = $start; $i < $start + self::BASE && $i <= $max; $i++) {
      $stack[] = $i;
    }
    return $stack;
  }

  //周囲
  public static function GetAround(User $user) {
    $max   = DB::$USER->Count();
    $num   = $user->id;
    $stack = [];
    for ($i = -1; $i < 2; $i++) {
      $j = $num + $i * self::BASE;
      if (Number::OutRange($j, 1, $max + 1)) {
	continue;
      }

      if ($j <= $max) {
	$stack[] = $j;
      }
      if (self::ExistsWest($j)) {
	$stack[] = $j - 1;
      }
      if (self::ExistsEast($j, $max)) {
	$stack[] = $j + 1;
      }
    }
    return $stack;
  }

  //隣接
  public static function GetChain($id, $max) {
    $stack = [];
    if ($id - self::BASE >= 1) {
      $stack['N'] = $id - self::BASE;
    }
    if ($id + self::BASE <= $max) {
      $stack['S'] = $id + self::BASE;
    }
    if (self::ExistsWest($id)) {
      $stack['W'] = $id - 1;
    }
    if (self::ExistsEast($id, $max)) {
      $stack['E'] = $id + 1;
    }
    return $stack;
  }

  //隣接 (斜め対応)
  public static function GetFullChain($id, $max) {
    $stack = self::GetChain($id, $max);

    $point = $id + self::BASE - 1;
    if (self::ExistsWest($id) && $point <= $max) {
      $stack['SW'] = $point;
    }

    $point = $id + self::BASE + 1;
    if (self::ExistsEast($id, $max) && $point <= $max) {
      $stack['SE'] = $point;
    }

    return $stack;
  }

  //十字
  public static function IsCross($id, $viewer) {
    return abs($id - $viewer) == self::BASE ||
      $id == $viewer - 1 && ($id     % self::BASE) != 0 ||
      $id == $viewer + 1 && ($viewer % self::BASE) != 0;
  }

  //基数倍判定
  private static function IsBase($id) {
    return ($id % self::BASE) == 0;
  }

  //東存在
  private static function ExistsEast($id, $max) {
    return ($id % self::BASE) != 0 && $id < $max;
  }

  //西存在
  private static function ExistsWest($id) {
    return ($id % self::BASE) != 1 && $id > 1;
  }
}
