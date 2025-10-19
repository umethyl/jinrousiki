<?php
/*
  ◆怨狼 (spell_wolf)
  ○仕様
  ・占い：呪殺
  ・罠：無効
  ・護衛：無効
  ・襲撃死因：呪殺
  ・襲撃追加：死亡(占い師系襲撃)
  ・襲撃毒発動：無効
*/
RoleLoader::LoadFile('wolf');
class Role_spell_wolf extends Role_wolf {
  public function EnableTrap(User $user) {
    return false;
  }

  public function EnableGuard(User $user) {
    return false;
  }

  protected function GetWolfKillReason() {
    return DeadReason::FOX_DEAD;
  }

  protected function WolfKillAction(User $user) {
    if ($user->IsDummyBoy()) { //身代わり君襲撃時は無効
      return;
    }

    if (CampGroup::MAGE === $user->DistinguishRoleGroup()) {
      $actor = $this->GetWolfVoter();
      if (false === RoleUser::AvoidLovers($actor)) {
	DB::$USER->Kill($actor->id, DeadReason::CURSED);
      }
    }
  }

  public function EnablePoisonEat(User $user) {
    return false;
  }
}
