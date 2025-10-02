<?php
//-- ◆文字化け抑制◆ --//
//-- 過去ログ表示クラス --//
final class OldLogController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'old_log';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function GetLoadDatabaseID() {
    return RQ::Get()->db_no;
  }

  protected static function EnableLoadRoom() {
    return true === RQ::Get()->is_room;
  }

  protected static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->LoadOption();
    DB::$ROOM->SetFlag(RoomMode::LOG);
    DB::$ROOM->Flag()->Set(RoomMode::WATCH,    RQ::Get()->watch);
    DB::$ROOM->Flag()->Set(RoomMode::SINGLE,   RQ::Get()->user_no > 0);
    DB::$ROOM->Flag()->Set(RoomMode::PERSONAL, RQ::Get()->personal_result);
    DB::$ROOM->last_date = DB::$ROOM->date;
  }

  protected static function LoadUser() {
    DB::LoadUser();
    DB::$USER->SetEvent(true);
    DB::$USER->player = RoomDB::GetPlayer();
    if (DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) {
      DB::$USER->SaveRoleList();
    }
  }

  protected static function LoadSelf() {
    if (DB::$ROOM->IsOn(RoomMode::SINGLE)) {
      DB::LoadSelf(RQ::Get()->user_no);
    } else {
      DB::LoadViewer();
    }

    if (DB::$ROOM->IsOn(RoomMode::WATCH)) {
      DB::$SELF->live = UserLive::LIVE;
    }
  }

  protected static function Output() {
    if (RQ::Get()->is_room) {
      OldLogHTML::Output();
    } else {
      OldLogHTML::OutputList(RQ::Get()->page);
    }
    HTML::OutputFooter();
  }
}
