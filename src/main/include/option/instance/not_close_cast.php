<?php
/*
  ◆霊界常時公開
  ・霊界公開判定：常時有効
*/
class Option_not_close_cast extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  public function GetCaption() {
    return '常時公開 (蘇生能力は無効です)';
  }

  //霊界公開判定
  public function IsRoomOpenCast() {
    return true;
  }

  //ユーザー霊界公開判定
  final protected function IsUserOpenCast() {
    return DB::$USER->IsOpenCast();
  }
}
