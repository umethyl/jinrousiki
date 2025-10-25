<?php
/*
  ◆陣営通知 (chaos_open_cast_camp)
*/
OptionLoader::LoadFile('chaos_open_cast_full');
class Option_chaos_open_cast_camp extends Option_chaos_open_cast_full {
  public function GetName() {
    return '陣営通知';
  }

  public function GetCaption() {
    return '配役を通知する:陣営通知';
  }

  public function GetExplain() {
    return '陣営通知 (陣営ごとの合計を通知)';
  }

  public function GetCastMessageMainHeader() {
    return VoteMessage::CAMP_HEADER;
  }

  public function GetCastMessageMainFooter() {
    return VoteMessage::CAMP_FOOTER;
  }

  public function GetCastMessageMainRoleList(array $role_count_list) {
    $stack = [];
    foreach ($role_count_list as $role => $count) {
      if (RoleDataManager::IsMain($role)) {
	ArrayFilter::Add($stack, RoleDataManager::GetCamp($role, true), $count);
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
