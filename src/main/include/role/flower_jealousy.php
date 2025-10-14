<?php
/*
  ◆花占い師 (flower_jealousy)
  ○仕様
  ・占い：恋占い
  ・占い結果：恋占い
*/
RoleLoader::LoadFile('jealousy');
class Role_flower_jealousy extends Role_jealousy {
  public $mix_in = ['mage'];
  public $action = VoteAction::MAGE;
  public $result = RoleAbility::MAGE;
  public $display_role = 'mage';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  public function OutputAction() {
    if ($this->ExistsVoteMix()) {
      return $this->CallVoteMix(__FUNCTION__);
    }
    RoleHTML::OutputVoteNight(VoteCSS::MAGE, RoleAbilityMessage::MAGE, $this->action);
  }

  //占い(恋占い) (妨害 > 呪返し > 占い判定)
  public function Mage(User $user) {
    if ($this->IsJammer($user)) {
      return $this->SaveMageResult($user, $this->GetMageFailed(), $this->result);
    } elseif ($this->IsCursed($user)) {
      return false;
    } else {
      $result = $this->GetMageResult($user);
      return $this->SaveMageResult($user, $result, $this->result);
    }
  }

  /*
    恋人系特殊サブ > 恋人系サブ > 恋人陣営 > 位置占い
    - 恋人系特殊サブ (交換憑依 > 交換日記 > 難題 > 織姫)
    - 恋人系サブ (恋人 > 愛人 > 悲恋)
  */
  protected function GetMageResult(User $user) {
    if ($user->IsRole('possessed_exchange')) {
      return 'flower_mage_possessed';
    } elseif ($user->IsRole('letter_exchange')) {
      return 'flower_mage_letter';
    } elseif ($user->IsRole('challenge_lovers')) {
      return 'flower_mage_challenge';
    } elseif ($user->IsRole('vega_lovers')) {
      return 'flower_mage_vega';
    } elseif ($user->IsRole('lovers')) {
      return 'flower_mage_lovers';
    } elseif ($user->IsRole('fake_lovers')) {
      return 'flower_mage_fake';
    } elseif ($user->IsRole('sweet_status')) {
      return 'flower_mage_sweet';
    } elseif ($user->IsMainCamp(Camp::LOVERS)) {
      return 'flower_mage_cupid';
    } else {
      return $this->DistinguishFlowerMage($user);
    }
  }

  //位置占い (経路距離が奇数：好き / 偶数：嫌い)
  private function DistinguishFlowerMage(User $user) {
    if (Number::Odd(Position::GetRouteDistance($user->id, $this->GetID()))) {
      return 'flower_mage_like';
    } else {
      return 'flower_mage_dislike';
    }
  }
}
