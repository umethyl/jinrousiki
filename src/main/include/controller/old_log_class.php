<?php
//-- ◆文字化け抑制◆ --//
//-- 過去ログ表示クラス --//
final class OldLogController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('old_log');
    DB::Connect(RQ::Get()->db_no);
  }

  protected static function Output() {
    if (RQ::Get()->is_room) {
      Loader::LoadFile('icon_class', 'image_class', 'talk_class');
      self::LoadRoom();
      self::LoadUser();
      self::LoadSelf();
      OldLogHTML::Output();
    } else {
      Loader::LoadFile('room_config');
      OldLogHTML::OutputList(RQ::Get()->page);
    }
    HTML::OutputFooter();
  }

  //村情報ロード
  private static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->LoadOption();
    DB::$ROOM->SetFlag(RoomMode::LOG);
    DB::$ROOM->Flag()->Set(RoomMode::WATCH,    RQ::Get()->watch);
    DB::$ROOM->Flag()->Set(RoomMode::SINGLE,   RQ::Get()->user_no > 0);
    DB::$ROOM->Flag()->Set(RoomMode::PERSONAL, RQ::Get()->personal_result);
    DB::$ROOM->last_date = DB::$ROOM->date;
  }

  //ユーザ情報ロード
  private static function LoadUser() {
    DB::LoadUser();
    DB::$USER->SetEvent(true);
    DB::$USER->player = RoomDB::GetPlayer();
    if (DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) {
      DB::$USER->SaveRoleList();
    }
  }

  //本人情報ロード
  private static function LoadSelf() {
    DB::$ROOM->IsOn(RoomMode::SINGLE) ? DB::LoadSelf(RQ::Get()->user_no) : DB::LoadViewer();
    if (DB::$ROOM->IsOn(RoomMode::WATCH)) {
      DB::$SELF->live = UserLive::LIVE;
    }
  }
}
