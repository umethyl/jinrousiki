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

  //闇鍋固定枠追加
  public function FilterChaosFixRole(array &$list) {
    if ($this->EnableGerd()) {
      ArrayFilter::Initialize($list, 'human', 1);
    }
  }

  //身代わり君固定配役取得
  public function GetDummyBoyFixRole(array $list) {
    $role = 'human';
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
      return 'human' === $role;
    }
  }
}
