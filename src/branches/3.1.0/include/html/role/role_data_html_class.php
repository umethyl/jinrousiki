<?php
//-- HTML 生成クラス (RoleData 拡張) --//
//-- ◆文字化け抑制◆ --//
class RoleDataHTML {
  const TAG  = '<%s class="%s">%s</%s>';
  const SPAN = '<span class="%s">[%s]</span>';
  const LINK = '<a href="new_role/%s.php#%s">%s</a>';

  //タグ生成
  public static function Generate($role, $css = null, $sub_role = false) {
    $str = $sub_role ? Text::BR : '';
    if (is_null($css)) $css = RoleDataManager::GetCSS($role);
    return $str . sprintf(self::SPAN, $css, RoleDataManager::GetName($role, $sub_role));
  }

  //タグ生成 (メイン役職専用)
  public static function GenerateMain($role, $tag = 'span') {
    return sprintf(self::TAG,
      $tag, RoleDataManager::GetCSS($role), RoleDataManager::GetName($role), $tag
    );
  }

  //タグ生成 (サブ役職専用)
  public static function GenerateSub($role, $tag = 'span') {
    foreach (RoleGroupSubData::$list as $css => $list) {
      if (in_array($role, $list)) {
	return sprintf(self::TAG, $tag, $css, RoleDataManager::GetName($role, true), $tag);
      }
    }
  }

  //役職説明ページへのリンク生成
  public static function GenerateLink($role) {
    if (RoleDataManager::IsSub($role)) {
      $url  = 'sub_role';
      $name = RoleDataManager::GetName($role, true);
    } elseif (RoleDataManager::GetCamp($role, true) == Camp::MANIA) {
      $url  = 'mania';
      $name = RoleDataManager::GetName($role);
    } else {
      $url  = RoleDataManager::GetCamp($role);
      $name = RoleDataManager::GetName($role);
    }
    return sprintf(self::LINK, $url, $role, $name);
  }
}
