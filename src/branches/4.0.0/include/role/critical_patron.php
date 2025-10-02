<?php
/*
  ◆ひんな神 (critical_patron)
  ○仕様
  ・得票数：+100 (5%)
  ・追加役職：ひんな持ち
*/
RoleLoader::LoadFile('patron');
class Role_critical_patron extends Role_patron {
  public $mix_in = ['critical_luck'];

  protected function IgnoreFilterVotePoll() {
    return ! Lottery::Percent(5);
  }

  protected function AddDuelistRole(User $user) {
    $this->AddPatronRole($user, 'occupied_luck');
  }
}
