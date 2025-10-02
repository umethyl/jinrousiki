<?php
/*
  ◆静狼 (quiet_wolf)
  ○仕様
  ・遠吠え：非表示
*/
RoleManager::LoadFile('wolf');
class Role_quiet_wolf extends Role_wolf {
  function Howl(TalkBuilder $builder, $voice) { return false; }
}
