<?php
/*
  ◆完全通知 (chaos_open_cast_full)
*/
class Option_chaos_open_cast_full extends  OptionCheckbox {
  public $type = OptionFormType::RADIO;

  public function GetName() {
    return '完全通知';
  }

  public function GetCaption() {
    return '配役を通知する:完全通知';
  }

  public function GetExplain() {
    return '完全通知 (通常村相当)';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }

  //-- 役職通知 --//
  //役職通知無効判定
  public function IgnoreCastMessage() {
    return false;
  }

  //配役通知無効表示
  public function GetCastMessage() {
    return TalkMessage::CHAOS;
  }

  //メイン役職ヘッダー
  public function GetCastMessageMainHeader() {
    return VoteMessage::ROLE_HEADER;
  }

  //メイン役職フッター
  public function GetCastMessageMainFooter() {
    return '';
  }

  //メイン役職一覧
  public function GetCastMessageMainRoleList(array $role_count_list) {
    return $role_count_list;
  }

  //サブ役職フッター
  public function GetCastMessageSubFooter() {
    return '';
  }

  //サブ役職フッター (グループ用)
  final protected function GetCastMessageSubFooterGroup() {
    return VoteMessage::GROUP_FOOTER;
  }

  //サブ役職一覧
  public function GetCastMessageSubRoleList(array $role_count_list) {
    return $role_count_list;
  }

  //サブ役職一覧 (グループ用)
  final protected function GetCastMessageSubRoleGroupList(array $role_count_list) {
    $stack = [];
    foreach ($role_count_list as $role => $count) {
      if (false === RoleDataManager::IsSub($role)) {
	continue;
      }

      foreach (RoleGroupSubData::$list as $list) {
	if (in_array($role, $list)) {
	  ArrayFilter::Add($stack, $list[0], $count);
	}
      }
    }
    return $stack;
  }
}
