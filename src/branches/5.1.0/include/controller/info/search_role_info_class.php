<?php
//-- 検索情報コントローラー (新役職情報) --//
final class SearchRoleInfoController extends JinrouController {
  protected static function LoadRequest() {
    RQ::LoadRequest();
    RQ::Fetch()->ParsePostOn('execute');
    RQ::Fetch()->ParsePostData('role');
  }

  protected static function Output() {
    InfoHTML::OutputRoleHeader(SearchRoleInfoMessage::TITLE);
    SearchRoleInfoHTML::OutputForm();
    if (self::IsExecute()) {
      self::RunSearch();
    }
    HTML::OutputFooter();
  }

  //検索実行判定
  private static function IsExecute() {
    return RQ::Fetch()->execute && isset(RQ::Fetch()->role);
  }

  //検索実行
  private static function RunSearch() {
    $stack = [];
    $search_list = RoleDataManager::Search(RQ::Fetch()->role);
    foreach ($search_list as $category => $list) {
      switch ($category) {
      case 'fix':
	foreach ($list as $type => $role) {
	  if (empty($role) || in_array($role, $stack)) {
	    continue;
	  }

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
	  if (empty($role_list)) {
	    continue;
	  }

	  foreach ($role_list as $role => $name) {
	    if (in_array($role, $stack)) {
	      continue;
	    }

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
