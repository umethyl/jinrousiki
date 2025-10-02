<?php
/*
  ◆古狐 (elder_fox)
  ○仕様
  ・投票数：+1
*/
RoleLoader::LoadFile('fox');
class Role_elder_fox extends Role_fox {
  public $mix_in = ['authority'];
}
