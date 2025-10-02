<?php
/*
  ◆覚醒者 (soul_mania)
  ○仕様
  ・コピー：特殊
  ・変化：上位種
*/
RoleManager::LoadFile('mania');
class Role_soul_mania extends Role_mania {
  public $copied = 'copied_soul';
  public $delay_copy = true;
  public $copy_list = array(
    'human'		=> 'executor',
    'mage'		=> 'soul_mage',
    'necromancer'	=> 'soul_necromancer',
    'medium'		=> 'revive_medium',
    'priest'		=> 'high_priest',
    'guard'		=> 'poison_guard',
    'common'		=> 'ghost_common',
    'poison'		=> 'strong_poison',
    'poison_cat'	=> 'revive_cat',
    'pharmacist'	=> 'alchemy_pharmacist',
    'assassin'		=> 'soul_assassin',
    'mind_scanner'	=> 'clairvoyance_scanner',
    'jealousy'		=> 'miasma_jealousy',
    'brownie'		=> 'history_brownie',
    'wizard'		=> 'soul_wizard',
    'doll'		=> 'doll_master',
    'escaper'		=> 'divine_escaper',
    'wolf'		=> 'sirius_wolf',
    'mad'		=> 'whisper_mad',
    'fox'		=> 'cursed_fox',
    'child_fox'		=> 'jammer_fox',
    'cupid'		=> 'minstrel_cupid',
    'angel'		=> 'sacrifice_angel',
    'quiz'		=> 'quiz',
    'vampire'		=> 'soul_vampire',
    'chiroptera'	=> 'boss_chiroptera',
    'fairy'		=> 'ice_fairy',
    'ogre'		=> 'sacrifice_ogre',
    'yaksa'		=> 'dowser_yaksa',
    'duelist'		=> 'critical_duelist',
    'avenger'		=> 'revive_avenger',
    'patron'		=> 'sacrifice_patron');

  protected function OutputResult() {
    if (DB::$ROOM->date == 2) $this->OutputAbilityResult($this->result);
  }

  protected function GetManiaRole(User $user) { return $user->DistinguishRoleGroup(); }

  //覚醒コピー
  function DelayCopy(User $user) {
    $actor = $this->GetActor();
    if ($user->IsRoleGroup('mania', 'copied')) {
      $role = 'human';
    }
    elseif ($user->IsRole('changed_disguise')) {
      $role = $this->copy_list['wolf'];
    }
    elseif ($user->IsRole('changed_therian')) {
      $role = $this->copy_list['mad'];
    }
    else {
      $role = $this->copy_list[$user->DistinguishRoleGroup()];
    }
    $actor->ReplaceRole($this->role, $role);
    $actor->AddRole($this->copied);
    DB::$ROOM->ResultAbility($this->result, $role, $actor->handle_name, $actor->user_no);
  }
}
