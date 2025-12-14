<?php
/*
  ◆従者支援 (serve_support)
  ○仕様
  ・役職表示：無し
  ・投票数：従者側に移譲
  ・得票数：従者側に移譲
*/
class Role_serve_support extends Role {
  public $mix_in = ['authority', 'upper_luck'];

  protected function IgnoreImage() {
    return true;
  }

  protected function GetVoteDoCount() {
    return $this->FilterVoteCount(__FUNCTION__);
  }

  protected function GetVotePollCount() {
    return $this->FilterVoteCount(__FUNCTION__);
  }

  //投票数/得票数支援
  final protected function FilterVoteCount(string $method) {
    $list = $this->GetActor()->GetPartner($this->role);
    if (null === $list) {
      return 0;
    }

    $count = 0;
    foreach ($list as $id) {
      $count += $this->CallServant($id, $method);
    }
    return $count;
  }

  //従者側関数呼び出し
  final protected function CallServant(int $id, string $method) {
    return RoleLoader::LoadMain(DB::$USER->ByID($id))->CallParent($method);
  }
}
