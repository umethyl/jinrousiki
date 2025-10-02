<?php
/*
  ◆夢求愛者 (dummy_chiroptera)
  ○仕様
*/
class Role_dummy_chiroptera extends Role {
  public $mix_in = array('vote' => 'self_cupid');
  public $display_role = 'self_cupid';

  protected function OutputPartner() {
    $user   = $this->GetActor();
    $target = $user->GetPartner($this->role);
    $stack  = $target;
    if (is_array($stack)) { //仮想恋人作成結果を表示
      $stack[] = $user->id;
      asort($stack);
      $pair = array();
      foreach ($stack as $id) {
	$pair[] = DB::$USER->ByID($id)->handle_name;
      }
      RoleHTML::OutputPartner($pair, 'cupid_pair');
    }
    if (is_array($target)) $this->OutputLovers($target);
  }

  //仮想恋人表示
  private function OutputLovers(array $list) {
    if ($this->IgnoreOutputLovers()) return;
    $stack = array();
    foreach ($list as $id) {
      $stack[] = DB::$USER->ByVirtual($id)->handle_name; //憑依追跡
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'lovers_footer');
  }

  //処理委託判定
  private function IgnoreOutputLovers() {
    return $this->GetActor()->IsRole('lovers', 'fake_lovers', 'sweet_status');
  }

  public function VoteNightAction() {
    $list  = $this->GetStack('target_list');
    $stack = array();
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      if (! $this->IsActor($user)) $this->GetActor()->AddMainRole($user->id);
    }

    $this->SetStack(implode(' ', array_keys($list)), 'target_no');
    $this->SetStack(implode(' ', $stack), 'target_handle');
  }
}
