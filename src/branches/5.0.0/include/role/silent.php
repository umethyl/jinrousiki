<?php
/*
  ◆無口 (silent)
  ○仕様
  ・発言変換：文字数制限 (サーバ設定)
*/
class Role_silent extends Role {
  public function ConvertSay() {
    $str = $this->GetStack('say');
    $len = GameConfig::SILENT_LENGTH;
    if (Text::Count($str) > $len) {
      $this->SetStack(Text::Shrink($str, $len) . '……', 'say');
    }
  }
}
