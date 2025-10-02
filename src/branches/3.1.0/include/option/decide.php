<?php
/*
  ◆決定者登場 (decide)
  ○仕様
  ・配役：誰か一人に決定者
*/
class Option_decide extends OptionCheckbox {
  public function GetCaption() {
    return '決定者登場';
  }

  public function GetExplain() {
    return '投票が同数の時、決定者の投票先が優先されます [兼任]';
  }

  public function Cast() {
    if (Cast::Stack()->Get(Cast::COUNT) >= CastConfig::${$this->name}) {
      return $this->CastOnce();
    }
  }
}
