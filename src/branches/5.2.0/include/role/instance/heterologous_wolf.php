<?php
/*
  ◆姫狼 (heterologous_wolf)
  ○仕様
  ・処刑得票：同性なら死の宣告 (70%)
*/
RoleLoader::LoadFile('homogeneous_wolf');
class Role_heterologous_wolf extends Role_homogeneous_wolf {
  protected function IsVotekillReactionSex() {
    return true;
  }
}
