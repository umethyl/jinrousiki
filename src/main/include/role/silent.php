<?php
/*
  ◆無口 (silent)
  ○仕様
  ・発言変換：文字数制限 (サーバ設定)
*/
class Role_silent extends Role {
  function ConvertSay() {
    $str = $this->GetStack('say');
    $len = GameConfig::SILENT_LENGTH;
    if (mb_strlen($str) > $len) $this->SetStack(mb_substr($str, 0, $len) . '……', 'say');
  }
}
