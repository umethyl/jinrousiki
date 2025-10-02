<?php
//-- 過去ログ表示クラス --//
class OldLog {
  /* ◆ 文字化け抑制用 */
  static function Output() {
    DB::Connect(RQ::Get()->db_no);
    if (RQ::Get()->is_room) {
      Loader::LoadFile('winner_message', 'icon_class', 'image_class', 'talk_class');
      self::LoadRoom();
      self::LoadUser();
      self::LoadSelf();
      OldLogHTML::Output();
    }
    else {
      Loader::LoadFile('room_config');
      OldLogHTML::OutputList(RQ::Get()->page);
    }
    HTML::OutputFooter();
  }

  //村情報ロード
  private static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->LoadOption();
    DB::$ROOM->SetFlag('log_mode');
    DB::$ROOM->watch_mode       = RQ::Get()->watch;
    DB::$ROOM->single_view_mode = RQ::Get()->user_no > 0;
    DB::$ROOM->personal_mode    = RQ::Get()->personal_result;
    DB::$ROOM->last_date        = DB::$ROOM->date;
  }

  //ユーザ情報ロード
  private static function LoadUser() {
    DB::LoadUser();
    DB::$USER->SetEvent(true);
    DB::$USER->player = RoomDB::GetPlayer();
    if (DB::$ROOM->watch_mode || DB::$ROOM->single_view_mode) DB::$USER->SaveRoleList();
  }

  //本人情報ロード
  private static function LoadSelf() {
    DB::$ROOM->single_view_mode ? DB::LoadSelf(RQ::Get()->user_no) : DB::LoadViewer();
    if (DB::$ROOM->watch_mode) DB::$SELF->live = 'live';
  }
}
