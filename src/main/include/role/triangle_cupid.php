<?php
/*
  ◆小悪魔 (triangle_cupid)
  ○仕様
  ・投票人数：3人
*/
RoleLoader::LoadFile('cupid');
class Role_triangle_cupid extends Role_cupid {
  protected function GetVoteNightNeedCount() {
    return 3;
  }
}
