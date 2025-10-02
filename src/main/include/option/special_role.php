<?php
/*
  ◆特殊配役モード (セレクタ)
  ○仕様
  ・モードリスト：GameOptionCofing::$special_role_list
*/
class Option_special_role extends OptionSelector {
  public $group = OptionGroup::GAME;
  public $on_change  = ' onChange="change_special_role()"';
  public $javascript = "change_option_display('chaos', 'none');";

  protected function LoadFormList() {
    $this->form_list = GameOptionConfig::${$this->source};
  }

  protected function LoadValue() {
    if (OptionManager::IsChange()) $this->SetFormValue('int');
  }

  public function GetCaption() {
    return '特殊配役モード';
  }

  public function GetExplain() {
    return '詳細は<a href="info/game_option.php">ゲームオプション</a>を参照してください';
  }
}
