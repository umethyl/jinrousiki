<?php
/*
  ◆GM ログアウト
*/
class Option_gm_logout extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  protected function FilterEnable() {
    if (OptionManager::IsChange()) {
      $enable = GameOptionConfig::$gm_logout_enable;
      $this->enable = $enable && DB::$ROOM->IsOption('gm_login') && ! DB::$ROOM->IsQuiz();
    } else {
      $this->enable = false;
    }
  }

  public function GetCaption() {
    return 'GM ログアウト';
  }

  public function GetExplain() {
    return '仮想 GM をログアウトし、身代わり君に戻ります。 [村オプション変更専用]' . Text::BR .
      '　　　 <span class="warning">一度ログアウトすると GM に戻れません</span>';
  }
}
