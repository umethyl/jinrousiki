<?php
/*
  ◆配役通知 - 通知なし
*/
class Option_chaos_open_cast_none extends OptionCheckbox {
  public $type = OptionFormType::RADIO;

  public function GetCaption() {
    return '通知なし';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }
}
