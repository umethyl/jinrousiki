<?php
/*
  ◆サブ役職制限：NORMALモード
*/
OptionLoader::LoadFile('sub_role_limit_none');
class Option_sub_role_limit_normal extends Option_sub_role_limit_none {
  public function GetCaption() {
    return 'サブ役職制限：NORMALモード';
  }
}
