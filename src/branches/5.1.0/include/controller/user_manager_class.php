<?php
//-- ユーザ登録コントローラー --//
final class UserManagerController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'user_manager';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::Start();
  }

  protected static function EnableCommand() {
    return RQ::Fetch()->entry;
  }

  protected static function RunCommand() {
    UserEntry::Execute();
  }

  protected static function Output() {
    if (RQ::Fetch()->user_no > 0) { //登録情報変更モード
      $stack = UserDB::Get();
      if ($stack['session_id'] != Session::GetID()) {
	UserEntry::OutputError(Message::SESSION_ERROR, UserManagerMessage::SESSION);
      }
      RQ::Fetch()->StorePost($stack);
    }

    DB::SetRoom(RoomLoaderDB::LoadEntryUserPage());
    if (null === DB::$ROOM->id) {
      $str = sprintf(UserManagerMessage::NOT_EXISTS, RQ::Get(RequestDataGame::ID));
      UserEntry::OutputError(UserManagerMessage::LOGIN, $str);
    }
    if (DB::$ROOM->IsFinished()) {
      UserEntry::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::FINISHED);
    }
    if (DB::$ROOM->IsPlaying()) {
      UserEntry::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::PLAYING);
    }
    DB::$ROOM->ParseOption(true);

    UserManagerHTML::Output();
  }

  protected static function Finish() {
    DB::Disconnect();
  }
}
