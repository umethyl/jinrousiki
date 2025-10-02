<?php
/*
  ◆霊界自動公開 (auto_open_cast)
  ・霊界公開判定：ユーザー霊界公開判定
*/
OptionLoader::LoadFile('not_close_cast');
class Option_auto_open_cast extends Option_not_close_cast {
  public function GetCaption() {
    return '自動で霊界の配役を公開する';
  }

  public function GetExplain() {
    return '自動公開 (蘇生能力者などが能力を持っている間だけ霊界が非公開になります)';
  }

  public function IsRoomOpenCast() {
    return $this->IsUserOpenCast();
  }
}
