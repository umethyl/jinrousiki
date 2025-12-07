<?php
//-- 検索情報コントローラー (新役職情報) --//
final class SearchRoleInfoController extends JinrouController {
  protected static function LoadRequestExtra() {
    DevHTML::LoadRequest();
    RQ::Fetch()->ParsePostData('role');
  }

  protected static function OutputRunHeader() {
    InfoHTML::OutputRoleHeader(SearchRoleInfoMessage::TITLE);
    SearchRoleInfoHTML::OutputForm();
  }

  protected static function EnableCommand() {
    return RQ::Fetch()->execute && isset(RQ::Fetch()->role);
  }

  protected static function RunCommand() {
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

  protected static function OutputRunFooter() {
    HTML::OutputFooter();
  }
}
