<?php
//-- ゲーム内処理クラス --//
final class GameAction {
  //突然死
  public static function SuddenDeath(array $target_list, string $reason) {
    foreach ($target_list as $id) {
      DB::$USER->SuddenDeath($id, $reason);
    }
    RoleLoader::Load('lovers')->Followed(true);
    RoleLoader::Load('medium')->InsertMediumResult();

    RoomTalk::StoreSystem(GameMessage::VOTE_RESET); //投票リセットメッセージ
    RoomDB::ResetVote(); //投票リセット
    if (Winner::Judge()) { //勝敗判定
      if (DB::$ROOM->IsOption('joker')) { //ジョーカー再配布
	RoleLoader::Load('joker')->ResetJoker();
      }
    }
  }

  //身代わり君の個別発言投稿判定
  public static function IsIndividual() {
    //身代わり君限定
    if (false === DB::$SELF->IsDummyBoy()) {
      return false;
    }

    //プレイ中限定
    if (false === DB::$ROOM->IsPlaying()) {
      return false;
    }

    //フラグ判定
    RQ::Fetch()->ParsePostOn(RequestDataTalk::INDIVIDUAL);
    if (RQ::Fetch()->Disable(RequestDataTalk::INDIVIDUAL)) {
      return false;
    }

    //対象者
    RQ::Fetch()->ParsePostInt(RequestDataTalk::TARGET);
    $target_id = RQ::Get(RequestDataTalk::TARGET);
    $user      = DB::$USER->ByID($target_id);
    if ($target_id != $user->id) {
      return false;
    }

    return true;
  }
}
