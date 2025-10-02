<?php
/*
  ◆夢求愛者 (dummy_chiroptera)
  ○仕様
  ・仲間表示：仮想恋人作成対象者 (恋人系委託あり)
*/
class Role_dummy_chiroptera extends Role {
  public $mix_in = array('vote' => 'self_cupid', 'lovers');
  public $display_role = 'self_cupid';

  protected function GetPartner() {
    $stack = $this->GetActor()->GetPartner($this->role);
    if (! is_array($stack)) return array();

    //仮想恋人作成結果を表示
    $stack[] = $this->GetID();
    asort($stack);
    $pair = array();
    foreach ($stack as $id) {
      $pair[] = DB::$USER->ByID($id)->handle_name;
    }
    return array('cupid_pair' => $pair);
  }

  protected function OutputAddPartner() {
    foreach ($this->GetLoversPartner() as $type => $list) {
      RoleHTML::OutputPartner($list, $type, 'lovers_footer');
    }
  }

  protected function IgnoreGetLoversPartner() {
    return $this->GetActor()->IsRole('lovers', 'fake_lovers', 'sweet_status');
  }

  protected function IsLoversPartner(User $user) {
    return $this->GetActor()->IsPartner($this->role, $user->id);
  }

  public function VoteNightAction() {
    $list  = $this->GetStack('target_list');
    $stack = array();
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      if (! $this->IsActor($user)) $this->GetActor()->AddMainRole($user->id);
    }

    $this->SetStack(ArrayFilter::ConcatKey($list), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($stack), 'target_handle');
  }
}
