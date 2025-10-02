<?php
/*
  ◆紳士 (gentleman)
  ○仕様
  ・発言変換：完全置換 (生存者ユーザ名 (ランダム) + サーバ設定)
*/
class Role_gentleman extends Role {
  function ConvertSay() {
    if (! Lottery::Percent(GameConfig::GENTLEMAN_RATE)) return false; //スキップ判定

    $stack = array_keys(DB::$USER->GetLivingUsers()); //生存者のユーザ ID を取得
    unset($stack[array_search($this->GetID(), $stack)]); //自分を削除
    $target = DB::$USER->ByVirtual(Lottery::Get($stack))->handle_name;
    $this->SetStack(sprintf(Message::${$this->role}, $target), 'say');
    return true;
  }
}
