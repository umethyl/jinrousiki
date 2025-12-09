<?php
/*
  ◆村の説明 (room_comment)
*/
class Option_room_comment extends OptionText {
  public function GetCaption() {
    return '村についての説明';
  }

  public function GetExplain() {
    return null;
  }

  public function GetPlaceholder() {
    return RoomEntryMessage::PLACEHOLDER_ROOM_COMMENT;
  }
}
