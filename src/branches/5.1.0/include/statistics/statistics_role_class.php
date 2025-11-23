<?php
//-- ◆文字化け抑制◆ --//
//-- 人狼統計情報クラス (Role 拡張) --//
final class StatisticsRole {
  //陣営名取得
  public static function GetWinCampName($camp) {
    switch ($camp) {
    case WinCamp::LOVERS:
      return RoleDataManager::GetName($camp, true);

    case WinCamp::DRAW:
      return WinnerMessage::$personal_draw;

    case WinCamp::NONE:
      return WinnerMessage::$personal_none;

    default:
      return RoleDataManager::GetName($camp);
    }
  }

  //出現陣営変換 (恋人陣営対応)
  public static function ConvertAppearCamp(string $camp) {
    //キューピッド -> 恋人変換
    if (BaseCamp::LOVERS == $camp) {
      return Camp::CUPID;
    } else {
      return $camp;
    }
  }

  //出現陣営変換 (神話マニア陣営対応)
  public static function ConvertOriginCampRole(string $role) {
    if (ArrayFilter::IsKey(StatisticsData::$origin_role, $role)) {
      return StatisticsData::$origin_role[$role];
    } else {
      return $role;
    }
  }

  //勝利陣営グループ取得
  public static function GetWinCampGroup(string $camp) {
    switch ($camp) {
    case WinCamp::HUMAN:
      return [$camp, WinCamp::HUMAN_QUIZ];

    case WinCamp::WOLF:
      return [$camp, WinCamp::WOLF_QUIZ];

    case WinCamp::FOX:
      return [WinCamp::FOX_HUMAN, WinCamp::FOX_WOLF, WinCamp::FOX_QUIZ];

    case WinCamp::DRAW:
      return [$camp, WinCamp::VANISH, WinCamp::QUIZ_DEAD];

    default:
      return [$camp];
    }
  }

  //変化形判定
  public static function IsChanged(User $user) {
    return $user->IsRoleGroup('copied', 'changed');
  }

  //変化形前役職情報
  public static function GetOrigin(User $user) {
    foreach (StatisticsData::$origin_role as $change => $origin) {
      if ($user->IsRole($change)) {
	return [$change => $origin];
      }
    }
  }
}
