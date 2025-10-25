<?php
/*
  ◆仮GMモード (temporary_gm)
  ○仕様
*/
class Option_temporary_gm extends OptionCastCheckbox {
  protected function FilterEnable() {
    if (RoomOptionManager::IsChange()) {
      $this->enable = false;
    }
  }

  public function GetCaption() {
    return '仮GMモード';
  }

  public function GetExplain() {
    return '最初の入村者(仮GM)が一部のゲームオプションを編集できるようになります';
  }

  //仮GM権限者判定
  public function IsTemporaryGM() {
    $id = $this->GetTemporaryGMID();
    return (null !== $id && DB::$SELF->id == $id);
  }

  //仮GM権限者取得
  /*
    最初の入村者IDを特定する (キックでずれるので固定ではない)
    DB取得時にソート順が指定されているので身代わり君の次で確定
  */
  public function GetTemporaryGMID() {
    foreach (DB::$USER->Get() as $user) {
      if (false === $user->IsDummyBoy()) {
	return $user->id;
      }
    }
    return null;
  }
}
