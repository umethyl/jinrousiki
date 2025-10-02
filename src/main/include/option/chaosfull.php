<?php
/*
  ◆真・闇鍋モード (chaosfull)
*/
OptionManager::Load('chaos');
class Option_chaosfull extends Option_chaos {
  public function GetCaption() {
    return '真・闇鍋モード';
  }
}
