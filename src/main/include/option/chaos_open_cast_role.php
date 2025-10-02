<?php
/*
  ◆役職通知 (chaos_open_cast_role)
*/
OptionLoader::LoadFile('chaos_open_cast_full');
class Option_chaos_open_cast_role extends Option_chaos_open_cast_full {
  public function GetName() {
    return '役職通知';
  }

  public function GetCaption() {
    return '配役を通知する:役職通知';
  }

  public function GetExplain() {
    return '役職通知 (役職の種類別に合計を通知)';
  }

  public function GetCastMessageMainHeader() {
    return VoteMessage::GROUP_HEADER;
  }

  public function GetCastMessageMainFooter() {
    return VoteMessage::GROUP_FOOTER;
  }

  public function GetCastMessageMainRoleList(array $role_count_list) {
    $stack = [];
    foreach ($role_count_list as $role => $count) {
      if (RoleDataManager::IsMain($role)) {
	ArrayFilter::Add($stack, RoleDataManager::GetGroup($role), $count);
      }
    }
    return $stack;
  }

  public function GetCastMessageSubFooter() {
    return $this->GetCastMessageSubFooterGroup();
  }

  public function GetCastMessageSubRoleList(array $role_count_list) {
    return $this->GetCastMessageSubRoleGroupList($role_count_list);
  }
}
