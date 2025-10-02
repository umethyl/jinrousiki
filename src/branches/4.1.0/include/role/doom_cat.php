<?php
/*
  ◆神医 (doom_cat)
  ○仕様
  ・蘇生率：35% / 誤爆率：15%
  ・蘇生後：死の宣告延長
  ・蘇生キャンセル：死の宣告
*/
RoleLoader::LoadFile('poison_cat');
class Role_doom_cat extends Role_poison_cat {
  protected function GetReviveRate() {
    return 35;
  }

  protected function GetMissfireRate($revive) {
    return 15;
  }

  protected function ReviveAction() {
    $actor = $this->GetActor();
    $role  = 'death_warrant';
    if (! $actor->IsRole($role)) {
      return;
    }

    $date = $actor->GetDoomDate($role); //未達の宣告があれば延長する
    if (DB::$ROOM->date < $date) {
      $actor->AddDoom($date - DB::$ROOM->date + 2);
    }
  }

  //蘇生キャンセル後処理
  public function ReviveCancelAction() {
    $actor = $this->GetActor();
    if (! $actor->IsRole('death_warrant')) {
      $actor->AddDoom(3);
    }
  }
}
