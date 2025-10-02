<?php
//-- 役職データマネージャ --//
class RoleDataManager {
  //データ取得
  public static function Get($sub = false) {
    return $sub ? RoleSubData::$list : RoleData::$list;
  }

  //役職グループ取得
  public static function GetGroup($role) {
    foreach (RoleGroupData::$list as $main_role => $group) {
      if (Text::Search($role, $main_role)) return $group;
    }
    return CampGroup::HUMAN;
  }

  //所属陣営取得
  public static function GetCamp($role, $start = false) {
    switch (self::GetGroup($role)) {
    case CampGroup::WOLF:
    case CampGroup::MAD:
      return Camp::WOLF;

    case CampGroup::FOX:
    case CampGroup::CHILD_FOX:
    case CampGroup::DEPRAVER:
      return Camp::FOX;

    case CampGroup::CUPID:
    case CampGroup::ANGEL:
      return $start ? Camp::CUPID : Camp::LOVERS;

    case CampGroup::QUIZ:
      return Camp::QUIZ;

    case CampGroup::VAMPIRE:
      return Camp::VAMPIRE;

    case CampGroup::CHIROPTERA:
    case CampGroup::FAIRY:
      return Camp::CHIROPTERA;

    case CampGroup::OGRE:
    case CampGroup::YAKSA:
      return Camp::OGRE;

    case CampGroup::DUELIST:
    case CampGroup::AVENGER:
    case CampGroup::PATRON:
      return Camp::DUELIST;

    case CampGroup::TENGU:
      return Camp::TENGU;

    case CampGroup::MANIA:
    case CampGroup::UNKNOWN_MANIA:
      return $start ? Camp::MANIA : Camp::HUMAN;

    default:
      return Camp::HUMAN;
    }
  }

  //マニュアル記載ページ取得
  public static function GetManualPage($role) {
    $camp = self::GetCamp($role, true);
    return $camp == Camp::CUPID ? Camp::LOVERS : $camp;
  }

  //役職クラス (CSS) 取得
  public static function GetCSS($role) {
    switch ($css = self::GetGroup($role)) {
    case CampGroup::POISON_CAT:
      $css = 'cat';
      break;

    case CampGroup::MIND_SCANNER:
      $css = 'mind';
      break;

    case CampGroup::CHILD_FOX:
      $css = 'fox';
      break;

    case CampGroup::UNKNOWN_MANIA:
      $css = 'mania';
      break;
    }
    return $css;
  }

  //役職名取得
  public static function GetName($role, $sub = false) {
    return ArrayFilter::Get(self::Get($sub), $role);
  }

  //役職省略名取得
  public static function GetShortName($role) {
    return ArrayFilter::Get(RoleShortData::$list, $role);
  }

  //役職のコード名リスト取得
  public static function GetList($sub = false) {
    return array_keys(self::Get($sub));
  }

  //役職グループリスト取得
  public static function GetGroupList() {
    $stack = array_merge(array(CampGroup::HUMAN), RoleGroupData::$list); //村人は含まれていない
    return array_intersect(self::GetList(), $stack);
  }

  //役職の差分取得
  public static function GetDiff(array $list, $sub = false) {
    return array_intersect_key(self::Get($sub), $list);
  }

  //役職の差分取得 (省略名用)
  public static function GetShortDiff(array $list) {
    return array_intersect_key(RoleShortData::$list, array_flip($list));
  }

  //表示対象サブ役職取得
  public static function GetDisplayList(array $list) {
    $stack = array();
    if (count($list) < 1) return $stack;

    foreach (array('real', 'virtual') as $name) {
      ArrayFilter::Merge($stack, RoleFilterData::${'display_' . $name});
    }
    //Text::p($stack, '◆SubRole');

    $display_list = array_diff(array_keys(RoleSubData::$list), $stack);
    return array_intersect($display_list, $list);
  }

  //メイン役職判定
  public static function IsMain($role) {
    return isset(RoleData::$list[$role]);
  }

  //サブ役職判定
  public static function IsSub($role) {
    return isset(RoleSubData::$list[$role]);
  }

  //役職グループ判定
  public static function IsGroup($role, $group) {
    return self::GetGroup($role) == $group;
  }

  //役職名のソート
  public static function Sort(array $list) {
    return array_intersect(self::GetList(), $list);
  }

  //役職情報検索
  public static function Search($name) {
    /* 初期化 */
    $stack = array();

    /* 完全一致 */
    $stack['fix']['main'] = array_search($name, RoleData::$list);
    $stack['fix']['sub']  = array_search($name, RoleSubData::$list);

    /* 部分一致 */
    $stack['match']['main'] = preg_grep("/{$name}/", RoleData::$list);
    $stack['match']['sub']  = preg_grep("/{$name}/", RoleSubData::$list);

    return $stack;
  }
}
