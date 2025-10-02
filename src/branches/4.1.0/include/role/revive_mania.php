<?php
/*
  ◆五徳猫 (revive_mania)
  ○仕様
  ・人狼襲撃：コピー先蘇生
*/
RoleLoader::LoadFile('unknown_mania');
class Role_revive_mania extends Role_unknown_mania {
  public $mix_in = ['revive_pharmacist'];

  public function WolfEatCounter(User $user) {
    //全体無効判定 (公開 > 天候)
    if (DB::$ROOM->IsOpenCast() || DB::$ROOM->IsEvent('no_revive')) {
      return false;
    }

    //コピー先無効判定 (不在 > 生存 > 蘇生制限)
    $id = $this->GetActor()->GetMainRoleTarget();
    if (null === $id) {
      return false;
    }

    $target = DB::$USER->ByID($id);
    if ($target->IsLive(true) || RoleUser::LimitedRevive($target)) {
      return false;
    }

    $this->SetStack($id);
    //RoleManager::Stack()->p($this->role, '◆ResurrectTarget');
  }

  protected function IgnoreResurrect() {
    return null === $this->GetStack();
  }

  protected function IsResurrect() {
    return true;
  }

  protected function ResurrectRevive() {
    $user = DB::$USER->ByID($this->GetStack());
    $real = $user->GetReal();
    if ($user->IsSame($real)) {
      $user->Revive();
    } else { //憑依対応
      $user->ReturnPossessed('possessed');
      $user->Revive(true);
      DB::$ROOM->StoreDead($real->handle_name, DeadReason::REVIVE_SUCCESS);
      $real->ReturnPossessed('possessed_target');
    }
    RoleManager::Stack()->Clear($this->role);
  }

  protected function IsResurrectLost() {
    return false;
  }
}
