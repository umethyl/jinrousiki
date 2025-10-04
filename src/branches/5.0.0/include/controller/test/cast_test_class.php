<?php
//-- 村配役テストコントローラー --//
final class CastTestController extends JinrouTestController {
  protected static function GetLoadRequest() {
    return 'game_view';
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

  protected static function LoadRoom() {
    DevRoom::Load();
  }

  protected static function LoadUser() {
    DevUser::Load();
  }

  protected static function Output() {
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
    foreach (DB::$USER->Get() as $user) {
      $user->Reparse();
    }
  }
}
