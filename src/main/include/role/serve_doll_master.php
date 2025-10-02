<?php
/*
  ◆奉公童女 (serve_doll_master)
  ○仕様
  ・能力結果：護衛 (天啓封印あり)
  ・護衛失敗：70% (人形不在時)
  ・護衛処理：人形身代わり
  ・狩り：なし
*/
RoleLoader::LoadFile('doll_master');
class Role_serve_doll_master extends Role_doll_master {
  public $mix_in = array('vote' => 'guard', 'protected');

  protected function OutputAddResult() {
    $this->OutputGuardResult();
  }

  public function IgnoreGuard(User $user) {
    return $this->CountDoll() < 1 && Lottery::Percent(70);
  }

  public function GuardAction(User $user) {
    if (DB::$ROOM->IsEvent('no_sacrifice')) return; //蛍火は無効
    if (ArrayFilter::IsInclude($this->GetStack(), $user->id)) return;
    $this->AddStack($user->id);
    $this->AddStack($user->id, $this->role . '_kill');
  }

  //護衛判定後処理
  public function GuardFinishAction() {
    $target_stack = $this->GetStack($this->role . '_kill');
    //Text::p($target_stack, "◆Target [{$this->role}]");
    if (! is_array($target_stack)) return;
    RoleManager::Stack()->Clear($this->role . '_kill');

    $stack = Lottery::GetList($this->GetLiveDoll());
    foreach ($target_stack as $id) {
      if (count($stack) < 1) return;
      DB::$USER->Kill(array_pop($stack), DeadReason::SACRIFICE);
    }
  }

  public function IgnoreHunt() {
    return true;
  }

  //生存人形取得
  private function GetLiveDoll() {
    $stack = array();
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive(true) && $this->IsDoll($user) && ! RoleUser::IsAvoidLovers($user, true)) {
	$stack[] = $user->id;
      }
    }
    return $stack;
  }
}
