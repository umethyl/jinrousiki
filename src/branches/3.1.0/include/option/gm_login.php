<?php
/*
  ◆身代わり君は GM (gm_login)
*/
class Option_gm_login extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  protected function FilterEnable() {
    $enable = GameOptionConfig::$gm_login_enable;
    if (OptionManager::IsChange()) {
      $this->enable = $enable && DB::$ROOM->IsOption($this->name);
    } else {
      $this->enable = $enable;
    }
  }

  public function LoadPost() {
    if (OptionManager::IsChange()) { //GM ログアウト判定 (クイズ村は無効)
      if (RQ::Get()->dummy_boy_selector == 'gm_logout' && ! DB::$ROOM->IsQuiz()) {
	RQ::Get()->gm_logout = true;
	return;
      }
    }
    RoomOption::Set($this->group, $this->name);
  }

  public function GetCaption() {
    return '身代わり君は GM';
  }

  public function GetExplain() {
    return '仮想 GM が身代わり君としてログインします';
  }
}
