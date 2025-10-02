<?php
/*
  ◆ゲルト君モード (gerd)
*/
class Option_gerd extends OptionCheckbox {
  public function GetCaption() {
    return 'ゲルト君モード';
  }

  public function GetExplain() {
    return '役職が村人固定になります (村人が出現している場合のみ有効)';
  }

  public function FilterCastChaosFixRole(array &$list) {
    if ($this->EnableGerd()) {
      ArrayFilter::Add($list, $this->GetTargetRole());
    }
  }

  public function GetCastDummyBoyFixRole(array $list) {
    $role = $this->GetTargetRole();
    if ($this->EnableGerd() && in_array($role, $list)) {
      return $role;
    } else {
      return null;
    }
  }

  //ゲルト君モード有効判定
  public function EnableGerd($role = 'human') {
    $option = 'disable_gerd';
    if (DB::$ROOM->IsOption($option)) {
      return false === OptionLoader::Load($option)->DisableGerd();
    } else {
      return $this->GetTargetRole() === $role;
    }
  }

  //対象役職取得
  private function GetTargetRole() {
    return 'human';
  }
}
