<?php
/*
  ◆発言数制限制 (limit_talk)
*/
class Option_limit_talk extends OptionLimitedCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '発言数制限制';
  }

  public function GetExplain() {
    return '昼の発言数に制限がかかります';
  }

  public function GetDefaultLimitedCount() {
    return GameConfig::LIMIT_TALK_COUNT;
  }

  public function GetLimitedFormCaption() {
    return '回';
  }

  protected function GetRoomCaptionFooterFormat() {
    return '%d回';
  }
}
