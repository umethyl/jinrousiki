<?php
//-- 住民登録テストコントローラー --//
final class UserEntryTestController extends JinrouController {
  protected static function Load() {
    DB::Connect();
    Session::Start();
    RQ::LoadRequest('game_view');

    //仮想村
    DevRoom::Initialize(['name' => GameMessage::ROOM_TITLE_FOOTER]);
    include('data/cast_option.php');

    //仮想ユーザ
    include('data/cast_user.php');
    DevUser::Complement();

    //設定調整
    include('data/cast_load.php');

    //データロード
    DevRoom::Load();
    DevUser::Load();
    DB::$ROOM->LoadOption();
  }

  protected static function Output() {
    UserManagerHTML::Output();
    DB::Disconnect();
  }
}
