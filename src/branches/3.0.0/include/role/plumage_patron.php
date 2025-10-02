<?php
/*
  ◆鬼車鳥 (plumage_patron)
  ○仕様
  ・追加役職：吸毒者
*/
RoleManager::LoadFile('patron');
class Role_plumage_patron extends Role_patron {
  public $patron_role = 'aspirator';

  protected function GetPatronRole() { return $this->patron_role; }
}
