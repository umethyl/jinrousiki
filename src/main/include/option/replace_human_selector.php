<?php
/*
  ◆村人置換村 (セレクタ)
*/
class Option_replace_human_selector extends OptionSelector {
  public $on_change = ' onChange="change_replace_human()"';

  protected function LoadFormList() {
    $this->form_list = GameOptionConfig::${$this->source};
  }

  protected function LoadValue() {
    if (RoomOptionManager::IsChange()) {
      $this->SetFormValue('int');
    }
  }

  public function GetCaption() {
    return '村人置換村';
  }

  public function GetExplain() {
    return '「村人」が全員特定の役職に入れ替わります';
  }
}
