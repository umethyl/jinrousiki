<?php
/*
  ◆共有者 (common)
  ○仕様
  ・仲間表示：共有者系
*/
class Role_common extends Role {
  protected function GetPartner() {
    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      if ($this->IsActor($user)) continue;
      if ($this->IsCommonPartner($user)) {
	$stack[] = $user->handle_name;
      }
    }
    return ['common_partner' => $stack];
  }

  //仲間判定
  protected function IsCommonPartner(User $user) {
    return RoleUser::IsCommon($user);
  }

  //囁き
  public function Whisper(TalkBuilder $builder, TalkParser $talk) {
    return $this->CommonWhisper($builder, $talk);
  }

  //囁き (共有囁き変換)
  final public function CommonWhisper(TalkBuilder $builder, TalkParser $talk) {
    if (! $builder->flag->common_whisper) return false; //スキップ判定

    $stack = [
      TalkElement::ID       => $builder->GetTalkID($talk),
      TalkElement::SYMBOL   => '',
      TalkElement::NAME     => RoleTalkMessage::COMMON,
      TalkElement::VOICE    => $talk->font_type,
      TalkElement::SENTENCE => RoleTalkMessage::COMMON_TALK,
      TalkElement::CSS_USER => TalkCSS::COMMON,
      TalkElement::CSS_SAY  => TalkCSS::COMMON_SAY
    ];
    return $builder->Register($stack);
  }
}
