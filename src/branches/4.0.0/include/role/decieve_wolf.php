<?php
/*
  ◆欺狼 (decieve_wolf)
  ○仕様
  ・襲撃：遺言偽装
*/
RoleLoader::LoadFile('wolf');
class Role_decieve_wolf extends Role_wolf {
  protected function WolfKillAction(User $user) {
    $actor = $this->GetWolfVoter();
    $actor->StoreLastWords($user->handle_name);
    $actor->Update('last_words', null);
  }
}
