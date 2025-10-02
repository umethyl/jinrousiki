<?php
/*
  ◆裏・闇鍋モード (chaos_verso)
*/
OptionManager::Load('chaos');
class Option_chaos_verso extends Option_chaos {
  public function GetCaption() {
    return '裏・闇鍋モード';
  }
}
