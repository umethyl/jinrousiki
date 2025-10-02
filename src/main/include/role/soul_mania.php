<?php
/*
  ◆覚醒者 (soul_mania)
  ○仕様
  ・能力結果：所属陣営 (天狗陣営コピー時)
  ・コピー：時間差覚醒
  ・変化：上位種
*/
RoleLoader::LoadFile('mania');
class Role_soul_mania extends Role_mania {
  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }

  protected function OutputAddResult() {
    if ($this->GetActor()->IsWinCamp(Camp::TENGU)) { //天狗陣営コピー時は所属陣営を通知する
      RoleHTML::OutputResult(RoleAbility::TENGU_CAMP);
    }
  }

  protected function GetCopyRole(User $user) {
    return $user->DistinguishRoleGroup();
  }

  protected function CopyAction(User $user, $role) {
    $actor = $this->GetActor();
    $actor->AddMainRole($user->id);
    DB::$ROOM->ResultAbility($this->result, $role, $user->handle_name, $actor->id);
  }

  protected function GetCopiedRole() {
    return 'copied_soul';
  }

  //覚醒コピー
  final public function DelayCopy(User $user) {
    if ($user->IsRoleGroup('mania', 'copied')) {
      $role = 'human';
    } else {
      $stack = $this->GetDelayCopyList();
      if ($user->IsRole('changed_disguise')) {
	$role = $stack[CampGroup::WOLF];
      } elseif ($user->IsRole('changed_therian')) {
	$role = $stack[CampGroup::MAD];
      } else {
	$role = $stack[$user->DistinguishRoleGroup()];
      }
    }
    $actor = $this->GetActor();
    $actor->ReplaceRole($user->GetID($this->role), $role);
    $actor->AddRole($this->GetCopiedRole());
    DB::$ROOM->ResultAbility($this->result, $role, $actor->handle_name, $actor->id);
  }

  //覚醒コピー変換リスト取得
  protected function GetDelayCopyList() {
    return array(
      CampGroup::HUMAN		=> 'executor',
      CampGroup::MAGE		=> 'soul_mage',
      CampGroup::NECROMANCER	=> 'soul_necromancer',
      CampGroup::MEDIUM		=> 'revive_medium',
      CampGroup::PRIEST		=> 'high_priest',
      CampGroup::GUARD		=> 'poison_guard',
      CampGroup::COMMON		=> 'ghost_common',
      CampGroup::POISON		=> 'strong_poison',
      CampGroup::POISON_CAT	=> 'revive_cat',
      CampGroup::PHARMACIST	=> 'alchemy_pharmacist',
      CampGroup::ASSASSIN	=> 'soul_assassin',
      CampGroup::MIND_SCANNER	=> 'clairvoyance_scanner',
      CampGroup::JEALOUSY	=> 'miasma_jealousy',
      CampGroup::BROWNIE	=> 'barrier_brownie',
      CampGroup::WIZARD		=> 'soul_wizard',
      CampGroup::DOLL		=> 'serve_doll_master',
      CampGroup::ESCAPER	=> 'divine_escaper',
      CampGroup::WOLF		=> 'sirius_wolf',
      CampGroup::MAD		=> 'whisper_mad',
      CampGroup::FOX		=> 'cursed_fox',
      CampGroup::CHILD_FOX	=> 'jammer_fox',
      CampGroup::DEPRAVER	=> 'sacrifice_depraver',
      CampGroup::CUPID		=> 'minstrel_cupid',
      CampGroup::ANGEL		=> 'sacrifice_angel',
      CampGroup::QUIZ		=> 'quiz',
      CampGroup::VAMPIRE	=> 'soul_vampire',
      CampGroup::CHIROPTERA	=> 'boss_chiroptera',
      CampGroup::FAIRY		=> 'ice_fairy',
      CampGroup::OGRE		=> 'sacrifice_ogre',
      CampGroup::YAKSA		=> 'dowser_yaksa',
      CampGroup::DUELIST	=> 'critical_duelist',
      CampGroup::AVENGER	=> 'revive_avenger',
      CampGroup::PATRON		=> 'sacrifice_patron',
      CampGroup::TENGU		=> 'soul_tengu'
    );
  }
}
