<?php
//-- 村配役テスト --//
class CastTest {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
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
  }

  //出力
  private static function Output() {
    HTML::OutputHeader(CastTestMessage::TITLE, 'game_play', true);
    GameHTML::OutputPlayer();
    self::RunTest();
    GameHTML::OutputPlayer();
    HTML::OutputFooter();
  }

  //テスト実行
  private static function RunTest() {
    VoteGameStart::Aggregate();
    DB::$ROOM->date++;
    DB::$ROOM->SetScene(RoomScene::NIGHT);
    foreach (DB::$USER->Get() as $user) $user->Reparse();
  }
}
