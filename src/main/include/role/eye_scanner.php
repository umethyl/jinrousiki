<?php
/*
  ◆目目連 (eye_scanner)
  ○仕様
*/
RoleLoader::LoadFile('mind_scanner');
class Role_eye_scanner  extends Role_mind_scanner {
  public $action = null;

  protected function GetMindRole() {
    return null;
  }
}
