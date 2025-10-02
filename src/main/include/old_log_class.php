<?php
//-- 過去ログ表示クラス --//
class OldLog {
  /* ◆ 文字化け抑制用 */
  static function Output() {
    DB::Connect(RQ::Get()->db_no);
    if (RQ::Get()->is_room) {
      Loader::LoadFile('winner_message', 'icon_class', 'image_class', 'talk_class');

      DB::$ROOM = new Room(RQ::Get());
      DB::$ROOM->LoadOption();
      DB::$ROOM->log_mode         = true;
      DB::$ROOM->watch_mode       = RQ::Get()->watch;
      DB::$ROOM->single_view_mode = RQ::Get()->user_no > 0;
      DB::$ROOM->personal_mode    = RQ::Get()->personal_result;
      DB::$ROOM->last_date        = DB::$ROOM->date;

      DB::$USER = new UserData(RQ::Get());
      DB::$USER->SetEvent(true);
      DB::$USER->player = RoomDB::GetPlayer();
      if (DB::$ROOM->watch_mode || DB::$ROOM->single_view_mode) DB::$USER->SaveRoleList();

      DB::$SELF = DB::$ROOM->single_view_mode ? DB::$USER->ByID(RQ::Get()->user_no) : new User();
      if (DB::$ROOM->watch_mode) DB::$SELF->live = 'live';
      OldLogHTML::Output();
    }
    else {
      Loader::LoadFile('room_config');
      OldLogHTML::OutputList(RQ::Get()->page);
    }
    HTML::OutputFooter();
  }
}
