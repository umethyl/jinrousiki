<?php
//-- 住民登録テストコントローラー --//
final class UserEntryTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function GetLoadRequest() {
    return 'game_view';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::Start();
  }

  protected static function LoadSetting() {
    //仮想村
    DevRoom::Initialize(['name' => GameMessage::ROOM_TITLE_FOOTER]);
    include('data/cast_option.php');

    //仮想ユーザ
    include('data/cast_user.php');
    DevUser::Complement();

    //設定調整
    include('data/cast_load.php');
  }

  protected static function EnableLoadRoom() {
    return true;
  }

  protected static function LoadRoom() {
    DevRoom::Load();
  }

  protected static function LoadUser() {
    DevUser::Load();
    DB::$ROOM->LoadOption();
  }

  protected static function Output() {
    UserManagerHTML::Output();
  }

  protected static function Finish() {
    DB::Disconnect();
  }
}
