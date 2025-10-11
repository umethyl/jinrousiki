<?php
/*
  ◆お祭り村 (festival)
  ○仕様
  ・配役有効判定：設定が存在する (CastConfig::$festival_role_list)
  ・配役：設定引用
  ・村人置換：無効
*/
class Option_festival extends OptionCastCheckbox {
  public function GetCaption() {
    return 'お祭り村';
  }

  public function GetExplain() {
    return '管理人がカスタムする特殊設定です';
  }

  public function EnableCast($user_count) {
    return ArrayFilter::Exists(CastConfig::$festival_role_list, $user_count);
  }

  public function GetCastRole($user_count) {
    return ArrayFilter::Get(CastConfig::$festival_role_list, $user_count);
  }

  public function EnableReplaceRole() {
    return false;
  }
}
