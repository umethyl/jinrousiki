<?php
/*
  ◆元神話マニア (copied)
  ○仕様
  ・役職表示：無し
  ・能力結果：コピー結果 (2 日目)
*/
class Role_copied extends Role {
  public $result = RoleAbility::MANIA;

  protected function IgnoreImage() {
    return true;
  }

  protected function IgnoreResult() {
    return false === DB::$ROOM->IsDate($this->GetCopiedResultDate());
  }

  //結果表示日取得
  protected function GetCopiedResultDate() {
    return 2;
  }
}
