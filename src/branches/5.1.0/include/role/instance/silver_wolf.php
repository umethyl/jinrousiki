<?php
/*
  ◆銀狼 (silver_wolf)
  ○仕様
  ・仲間表示：なし
*/
RoleLoader::LoadFile('wolf');
class Role_silver_wolf extends Role_wolf {
  public function Whisper(TalkBuilder $builder, TalkParser $talk) {
    return $this->WolfWhisper($builder, $talk);
  }

  //囁き (遠吠え変換)
  final public function WolfWhisper(TalkBuilder $builder, TalkParser $talk) {
    return DateBorder::Second() && $this->Howl($builder, $talk);
  }

  protected function IsWolfPartner($id) {
    return false;
  }
}
