<?php
/*
  ◆募集停止 (close_room)
*/
class Option_close_room extends OptionCheckbox {
  protected function Ingore() {
    return ! OptionManager::IsChange();
  }

  protected function LoadValue() {
    if (DB::ExistsRoom()) {
      $this->value = DB::$ROOM->IsClosing();
    }
  }

  public function GetCaption() {
    return '募集停止';
  }

  public function GetExplain() {
    return '住民登録を停止します (村オプション変更専用)';
  }
}
