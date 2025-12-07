<?php
//足音投票テストコントローラー
final class StepVoteTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function GetLoadRequest() {
    return 'game_view';
  }

  protected static function EnableLoadDatabase() {
    return false; //必要なときだけ設定する
  }

  protected static function LoadSetting() {
    //仮想村
    $stack = ['name' => GameMessage::ROOM_TITLE_FOOTER, 'status' => RoomStatus::PLAYING];
    DevRoom::Initialize($stack);
    include('data/step_vote_option.php');

    //仮想ユーザ
    DevUser::Initialize(25);
    include('data/step_vote_user.php');
    DevUser::Complement();

    //仮想投票データ
    RQ::GetTest()->vote = new stdClass();
    RQ::GetTest()->vote->night = [];

    //仮想イベント
    include('data/vote_event.php');
  }

  protected static function EnableLoadRoom() {
    return true;
  }

  protected static function LoadRoom() {
    $date = 6;
    DevRoom::Load();
    DB::$ROOM->SetDate($date);
    DB::$ROOM->SetScene(RoomScene::NIGHT);
    RQ::GetTest()->winner = WinCamp::WOLF;
  }

  protected static function LoadUser() {
    DevUser::Load();
    #DB::$USER->ByID(9)->live = UserLive::LIVE;
    DB::LoadSelf(18);
    foreach (DB::$USER->Get() as $user) {
      if (false === isset($user->target_no)) {
	$user->target_no = 0;
      }
    }
  }

  protected static function Output() {
    DevVote::Output('step_vote_test.php');
  }
}
