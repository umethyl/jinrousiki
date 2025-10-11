<?php
/*
  ◆庇護者 (protected)
  ○仕様
  ・役職表示：無し
  ・人狼襲撃耐性：身代わり (庇護者付加者)
*/
class Role_protected extends Role {
  protected function IgnoreImage() {
    return true;
  }

  public function ResistWolfEat() {
    if ($this->IgnoreSacrifice()) {
      return false;
    }

    $stack = [];
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      $user = DB::$USER->ByID($id);
      if ($this->IgnoreSacrificeTarget($user)) {
	continue;
      }
      $stack[] = $id;
    }
    return $this->Sacrifice($stack);
  }

  //人狼襲撃得票カウンター (Mixin 用)
  public function WolfEatReaction() {
    if ($this->IgnoreSacrifice()) {
      return false;
    }

    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      if ($this->IgnoreSacrificeTarget($user)) {
	continue;
      }

      if ($this->CallParent('IsSacrifice', $user)) {
	$stack[] = $user->id;
      }
    }
    return $this->Sacrifice($stack);
  }

  //身代わり対象判定
  protected function IsSacrifice(User $user) {
    return false;
  }

  //身代わり無効判定
  private function IgnoreSacrifice() {
    return DB::$ROOM->IsEvent('no_sacrifice');
  }

  //身代わり無効対象判定
  private function IgnoreSacrificeTarget(User $user) {
    return $user->IsDead(true) || RoleUser::IsAvoidLovers($user, true);
  }

  //身代わり処理
  private function Sacrifice(array $stack) {
    //Text::p($stack, sprintf('◆Sacrifice [%s]', $this->role));
    if (count($stack) < 1) {
      return false;
    }

    $id = Lottery::Get($stack);
    DB::$USER->Kill($id, DeadReason::SACRIFICE);
    $this->AddStack($id, RoleVoteTarget::SACRIFICE);
    return true;
  }
}
