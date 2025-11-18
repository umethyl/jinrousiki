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
    return RQ::Get(RequestDataGame::DB);
  }

  protected static function EnableLoadRoom() {
    return RQ::Enable('is_room');
  }

  protected static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->LoadOption();
    DB::$ROOM->SetFlag(RoomMode::LOG);
    DB::$ROOM->Flag()->Set(RoomMode::WATCH,    RQ::Fetch()->watch);
    DB::$ROOM->Flag()->Set(RoomMode::SINGLE,   RQ::Fetch()->user_no > 0);
    DB::$ROOM->Flag()->Set(RoomMode::PERSONAL, RQ::Fetch()->personal_result);
    DB::$ROOM->SetLastDate();
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
      DB::LoadSelf(RQ::Fetch()->user_no);
    } else {
      DB::LoadViewer();
    }

    if (DB::$ROOM->IsOn(RoomMode::WATCH)) {
      DB::$SELF->live = UserLive::LIVE;
    }
  }

  protected static function Output() {
    if (RQ::Enable('is_room')) {
      OldLogHTML::Output();
    } else {
      LogListHTML::Output(RQ::Fetch()->page);
    }
    HTML::OutputFooter();
  }
}
