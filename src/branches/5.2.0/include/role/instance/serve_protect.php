<?php
/*
  ◆従者護衛 (serve_protect)
  ○仕様
  ・人狼襲撃耐性：従者側に移譲
*/
RoleLoader::LoadFile('serve_support');
class Role_serve_protect extends RoleAbility_serve_support {
  public function ResistWolfEat() {
    $list = $this->GetActor()->GetPartner($this->role);
    if (null === $list) {
      return false;
    }

    /*
      成功者に対する通知/リアクションは存在しないので
      誰か一人でも成功した時点で判定終了
    */
    foreach ($list as $id) {
      if ($this->CallServant($id, __FUNCTION__)) {
	return true;
      }
    }
    return false;
  }
}
