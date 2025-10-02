<?php
/*
  ◆彦星 (altair_cupid)
  ○仕様
  ・自分撃ち：固定
  ・追加役職：共鳴者 (両方) + 織姫 (相手)
  ・恋人抽選：織姫
*/
RoleLoader::LoadFile('cupid');
class Role_altair_cupid extends Role_cupid {
  protected function FixSelfShoot() {
    return true;
  }

  protected function AddCupidRole(User $user) {
    $user->AddRole($this->GetActor()->GetID('mind_friend'));
  }

  public function LotteryLovers() {
    $target_id = Lottery::Get(DB::$USER->GetRoleID($this->role)); //対象彦星を抽選
    foreach ($this->GetLoversList() as $id) { //恋人一覧から検索
      if ($id == $target_id) continue;
      $user = DB::$USER->ByID($id);
      foreach ($user->GetPartner('lovers') as $cupid_id) {
	if ($cupid_id == $target_id) {
	  $user->AddRole('vega_lovers');
	  DB::$ROOM->ResultDead($user->handle_name, DeadReason::VEGA_LOVERS);
	  return;
	}
      }
    }
  }
}
