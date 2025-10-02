<?php
//足音投票テスト
class StepVoteTest {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('game_view', true);

    //仮想村
    $stack = array('name' => GameMessage::ROOM_TITLE_FOOTER, 'status' => RoomStatus::PLAYING);
    DevRoom::Initialize($stack);
    include('data/step_vote_option.php');

    //仮想ユーザ
    DevUser::Initialize(25);
    include('data/step_vote_user.php');
    DevUser::Complement();

    //仮想投票データ
    $set_date = 6;
    RQ::GetTest()->vote = new stdClass();
    RQ::GetTest()->vote->night = array();

    //仮想イベント
    include('data/vote_event.php');

    //データロード
    $set_date = 6;
    //DB::Connect(); //DB接続 (必要なときだけ設定する)
    DevRoom::Load();
    DB::$ROOM->date = $set_date;
    DB::$ROOM->SetScene(RoomScene::NIGHT);
    RQ::GetTest()->winner = WinCamp::WOLF;

    DevUser::Load();
    #DB::$USER->ByID(9)->live = UserLive::LIVE;
    DB::LoadSelf(18);
    foreach (DB::$USER->Get() as $user) {
      if (! isset($user->target_no)) $user->target_no = 0;
    }
  }

  //出力
  private static function Output() {
    DevVote::Output('step_vote_test.php');
  }
}
