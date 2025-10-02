<?php
/*
  ◆元夢語部 (copied_teller)
  ○仕様
  ・能力結果：コピー結果 (4 日目)
*/
RoleLoader::LoadFile('copied');
class Role_copied_teller extends Role_copied {
  protected function GetResultDate() {
    return 4;
  }
}
