<?php
/*
  ◆恋色迷彩 (passion)
  ○仕様
  ・発言変換：部分置換
  ・変換リスト：サーバ設定 (GameConfig::$passion_replace_list)
*/
class Role_passion extends Role {
  function ConvertSay() {
    if (! is_array($stack = $this->GetConvertSayList())) return;
    $this->SetStack(strtr($this->GetStack('say'), $stack), 'say');
  }

  protected function GetConvertSayList() {
    $list = $this->role . '_replace_list';
    return isset(GameConfig::$$list) ? GameConfig::$$list : $this->convert_say_list;
  }
}
