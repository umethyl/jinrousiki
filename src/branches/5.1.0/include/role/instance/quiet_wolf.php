<?php
/*
  ◆静狼 (quiet_wolf)
  ○仕様
  ・遠吠え：非表示
*/
RoleLoader::LoadFile('wolf');
class Role_quiet_wolf extends Role_wolf {
  public function Howl(TalkBuilder $builder, TalkParser $talk) {
    return false;
  }
}
