<?php
/*
  ◆従者支援 (serve_support)
  ○仕様
  ・役職表示：無し
  ・得票数：従者側に移譲
*/
class Role_serve_support extends Role {
  public $mix_in = ['upper_luck'];

  protected function IgnoreImage() {
    return true;
  }

  protected function GetVotePollCount() {
    $list = $this->GetActor()->GetPartner($this->role);
    if (null === $list) {
      return 0;
    }

    $count = 0;
    foreach ($list as $id) {
      $count += $this->CallServant($id, __FUNCTION__);
    }
    return $count;
  }

  //従者側関数呼び出し
  protected function CallServant($id, $method) {
    return RoleLoader::LoadMain(DB::$USER->ByID($id))->CallParent($method);
  }
}
