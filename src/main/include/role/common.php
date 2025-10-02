<?php
/*
  ◆共有者 (common)
  ○仕様
  ・仲間表示：共有者系
*/
class Role_common extends Role {
  protected function OutputPartner() {
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($this->IsCommonPartner($user)) $stack[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($stack, 'common_partner');
  }

  //仲間判定
  protected function IsCommonPartner(User $user) { return $user->IsCommon(true); }

  //囁き
  function Whisper(TalkBuilder $builder, $voice) {
    if (! $builder->flag->common_whisper) return false; //スキップ判定
    $str = Message::$common_talk;
    $builder->AddRaw('', '共有者の小声', $str, $voice, '', 'talk-common', 'say-common');
    return true;
  }
}
