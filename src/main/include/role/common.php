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
  protected function IsCommonPartner(User $user) {
    return $user->IsCommon(true);
  }

  //囁き
  public function Whisper(TalkBuilder $builder, $voice) {
    return $this->CommonWhisper($builder, $voice);
  }

  //囁き (共有囁き変換)
  final public function CommonWhisper(TalkBuilder $builder, $voice) {
    if (! $builder->flag->common_whisper) return false; //スキップ判定

    $stack = array(
      'str'        => RoleTalkMessage::COMMON_TALK,
      'symbol'     => '',
      'user_info'  => RoleTalkMessage::COMMON,
      'voice'      => $voice,
      'user_class' => 'talk-common',
      'say_class'  => 'say-common'
    );
    return $builder->AddRaw($stack);
  }
}
