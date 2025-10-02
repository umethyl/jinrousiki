<?php
/*
  ◆裏・闇鍋モード (chaos_verso)
  ・闇鍋モード配役補正：無効
*/
OptionLoader::LoadFile('chaos');
class Option_chaos_verso extends Option_chaos {
  public function GetCaption() {
    return '裏・闇鍋モード';
  }

  protected function EnableCastChaosCalibration() {
    return false;
  }
}
