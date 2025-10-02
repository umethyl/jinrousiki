<?php
//-- 検索出力クラス (新役職情報) --//
class SearchRoleInfo {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load(){
    Loader::LoadRequest();
    RQ::Get()->ParsePostOn('execute');
    RQ::Get()->ParsePostData('role');
  }

  //出力
  private static function Output() {
    InfoHTML::OutputRoleHeader(SearchRoleInfoMessage::TITLE);
    SearchRoleInfoHTML::OutputForm();
    if (self::IsExecute()) self::RunSearch();
    HTML::OutputFooter();
  }

  //検索実行判定
  private static function IsExecute() {
    return RQ::Get()->execute && isset(RQ::Get()->role);
  }

  //検索実行
  private static function RunSearch() {
    Loader::LoadFile('role_data_manager_class');
    $stack = array();
    $search_list = RoleDataManager::Search(RQ::Get()->role);
    foreach ($search_list as $category => $list) {
      switch ($category) {
      case 'fix':
	foreach ($list as $type => $role) {
	  if (empty($role) || in_array($role, $stack)) continue;
	  if ($type == 'main') {
	    $page = RoleDataManager::GetManualPage($role);
	    $name = RoleDataManager::GetName($role);
	  } else {
	    $page = 'sub_role';
	    $name = RoleDataManager::GetName($role, true);
	  }
	  SearchRoleInfoHTML::OutputLink($page, $role, $name);
	  $stack[] = $role;
	}
	break;

      case 'match':
	foreach ($list as $type => $role_list) {
	  if (empty($role_list)) continue;
	  foreach ($role_list as $role => $name) {
	    if (in_array($role, $stack)) continue;
	    if ($type == 'main') {
	      $page = RoleDataManager::GetManualPage($role);
	    } else {
	      $page = 'sub_role';
	    }
	    SearchRoleInfoHTML::OutputLink($page, $role, $name);
	    $stack[] = $role;
	  }
	}
	break;
      }
    }
  }
}
