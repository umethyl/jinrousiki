<?php
/*
  ◆転妖精 (gender_fairy)
  ○仕様
  ・悪戯：サブ役職付加 (性転換)
*/
RoleLoader::LoadFile('fairy');
class Role_gender_fairy extends Role_fairy {
  protected function FairyAction(User $user) {
    //「異議」ありで憑依を察知させないために、憑依先に付与する
    //システムメッセージは憑依追跡が標準搭載されているのでそのまま
    $target = $user->GetVirtual();
    //Text::p($target->uname, "◆Target [{$this->role}/{$user->uname}]");

    $target->AddDoom(1, 'gender_status');
    DB::$ROOM->StoreDead($user->GetName(), DeadReason::GENDER_STATUS);
  }
}
