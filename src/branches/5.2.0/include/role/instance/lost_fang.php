<?php
/*
  ◆抜牙 (lost_fang)
  ○仕様
  ・人狼襲撃無効判定 (サブ役職)：有効
  ・人狼襲撃失敗メッセージ：襲撃能力喪失
*/
class Role_lost_fang extends Role {
  //人狼襲撃無効判定 (サブ役職)
  public function DisableWolfEatSub() {
    return true;
  }

  //人狼襲撃失敗メッセージ
  public function GetWolfEatFailedType() {
    return 'LOST';
  }
}
