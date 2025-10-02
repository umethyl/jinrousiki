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
    DB::$ROOM->SetFlag('log');
    DB::$ROOM->Flag()->Set('watch',    RQ::Get()->watch);
    DB::$ROOM->Flag()->Set('single',   RQ::Get()->user_no > 0);
    DB::$ROOM->Flag()->Set('personal', RQ::Get()->personal_result);
    DB::$ROOM->last_date = DB::$ROOM->date;
  }

  //ユーザ情報ロード
  private static function LoadUser() {
    DB::LoadUser();
    DB::$USER->SetEvent(true);
    DB::$USER->player = RoomDB::GetPlayer();
    if (DB::$ROOM->IsOn('watch') || DB::$ROOM->IsOn('single')) DB::$USER->SaveRoleList();
  }

  //本人情報ロード
  private static function LoadSelf() {
    DB::$ROOM->IsOn('single') ? DB::LoadSelf(RQ::Get()->user_no) : DB::LoadViewer();
    if (DB::$ROOM->IsOn('watch')) DB::$SELF->live = UserLive::LIVE;
  }
}
