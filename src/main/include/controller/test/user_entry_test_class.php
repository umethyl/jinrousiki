<?php
//-- 住民登録テスト --//
class UserEntryTest {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    DB::Connect();
    Session::Start();
    Loader::LoadRequest('game_view', true);

    //仮想村
    DevRoom::Initialize(array('name' => GameMessage::ROOM_TITLE_FOOTER));
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

  //出力
  private static function Output() {
    UserManagerHTML::Output();
    DB::Disconnect();
  }
}
