<?php
/*
  ◆奉公童女 (serve_doll_master)
  ○仕様
  ・護衛失敗：70% (人形不在時)
  ・護衛処理：人形身代わり
  ・狩り：なし
*/
RoleManager::LoadFile('doll_master');
class Role_serve_doll_master extends Role_doll_master {
  public $mix_in = array('vote' => 'guard', 'protected');

  protected function OutputAddResult() {
    $this->OutputGuardResult();
  }

  public function IgnoreGuard() {
    return $this->GetDollCount() < 1 && Lottery::Percent(70);
  }

  public function GuardAction(User $user) {
    if (DB::$ROOM->IsEvent('no_sacrifice')) return; //蛍火は無効

    $stack = $this->GetStack();
    if (is_null($stack)) $stack = array();
    if (in_array($user->id, $stack)) return;

    $doll_stack = $this->GetLiveDoll();
    if (count($doll_stack) < 1) return;
    DB::$USER->Kill(Lottery::Get($doll_stack), 'SACRIFICE');

    $stack[] = $user->id;
    $this->SetStack($stack);
  }

  public function IgnoreHunt() {
    return true;
  }

  //生存人形取得
  private function GetLiveDoll() {
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive(true) && ! $user->IsAvoidLovers(true) && $this->IsDoll($user)) {
	$stack[] = $user->id;
      }
    }
    return $stack;
  }
}
