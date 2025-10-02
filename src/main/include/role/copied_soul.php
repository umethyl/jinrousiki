<?php
/*
  ◆元覚醒者 (copied_soul)
  ○仕様
  ・能力結果：コピー結果 (4 日目)
*/
RoleLoader::LoadFile('copied');
class Role_copied_soul extends Role_copied {
  protected function GetResultDate() {
    return 4;
  }
}
