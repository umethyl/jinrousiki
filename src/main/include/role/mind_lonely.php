<?php
/*
  ◆はぐれ者 (mind_lonely)
  ○仕様
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_lonely extends Role_mind_read {
  public $mix_in = array('silver_wolf');

  public function Whisper(TalkBuilder $builder, TalkParser $talk) {
    return $this->GetActor()->IsMainGroup(CampGroup::WOLF) && $this->WolfWhisper($builder, $talk);
  }
}
