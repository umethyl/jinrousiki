<?php
/*
  ◆ゲルト君モード無効 (disable_gerd)
*/
class Option_disable_gerd extends OptionLimitedCheckbox {
  public function GetCaption() {
    return 'ゲルト君モード無効';
  }

  public function GetExplain() {
    return 'ゲルト君モードが無効化されます';
  }

  public function GetDefaultLimitedCount() {
    return GameConfig::DISABLE_GERD_COUNT;
  }

  public function GetLimitedFormCaption() {
    return '人以上';
  }

  protected function GetRoomCaptionFooterFormat() {
    return '%d人以上';
  }

  //ゲルト君モード無効判定
  public function DisableGerd() {
    $count = ArrayFilter::Pick(DB::$ROOM->option_role->list[$this->name]);
    return DB::$USER->Count() >= $count;
  }
}
