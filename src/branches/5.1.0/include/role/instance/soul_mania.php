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
    return false === DateBorder::Two();
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
    DB::$ROOM->StoreAbility($this->result, $role, $user->handle_name, $actor->id);
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
    DB::$ROOM->StoreAbility($this->result, $role, $actor->handle_name, $actor->id);
  }

  //覚醒コピー変換リスト取得
  protected function GetDelayCopyList() {
    return RoleFilterData::$soul_delay_copy;
  }
}
