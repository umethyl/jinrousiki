<?php
//-- GameVote 出力クラス --//
class GameVote {
  //実行
  public static function Execute() {
    self::Load();
    RQ::Get()->vote ? self::Vote() : self::Output();
    DB::Disconnect();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('game_vote', true);
    DB::Connect();
    Session::Login();
    if (! DB::Transaction()) VoteHTML::OutputResult(VoteMessage::DB_ERROR); //トランザクション開始

    DB::LoadRoom(true); //村情報 (ロック付き)
    if (DB::$ROOM->IsFinished()) {
      VoteHTML::OutputError(VoteMessage::FINISHED_TITLE, VoteMessage::FINISHED);
    }
    DB::$ROOM->system_time = Time::Get();

    DB::LoadUser(true); //ユーザ情報 (ロック付き)
    DB::LoadSelf();
  }

  //投票処理
  private static function Vote() {
    if (DB::$ROOM->IsBeforeGame()) { //ゲーム開始 or Kick 投票処理
      switch (RQ::Get()->situation) {
      case VoteAction::GAME_START:
	Loader::LoadFile('cast_class'); //配役情報をロード
	return VoteGameStart::Execute();

      case VoteAction::KICK:
	return VoteKick::Execute();

      default: //ここに来たらロジックエラー
	return VoteHTML::OutputError(VoteMessage::INVALID_COMMAND);
      }
    } elseif (DB::$SELF->IsDead()) { //死者の霊界投票処理
      if (RQ::Get()->situation == VoteAction::RESET_TIME && DB::$SELF->IsDummyBoy()) {
	VoteDummyBoy::ResetTime();
      } else {
	VoteHeaven::Execute();
      }
    } elseif (RQ::Get()->target_no == 0) { //空投票検出
      VoteHTML::OutputError(VoteMessage::NO_TARGET_TITLE, VoteMessage::NO_TARGET);
    } elseif (DB::$ROOM->IsDay()) { //昼の処刑投票処理
      VoteDay::Execute();
    } elseif (DB::$ROOM->IsNight()) { //夜の投票処理
      VoteNight::Execute();
    } else { //ここに来たらロジックエラー
      VoteHTML::OutputError(VoteMessage::INVALID_COMMAND, VoteMessage::NO_TARGET);
    }
  }

  //出力 (死者は専用ページ / シーン別の投票ページ)
  private static function Output() {
    Loader::LoadFile('vote_message');
    if (DB::$SELF->IsDead()) {
      return DB::$SELF->IsDummyBoy() ? VoteHTML::OutputDummyBoy() : VoteHTML::OutputHeaven();
    }

    switch (DB::$ROOM->scene) {
    case RoomScene::BEFORE:
      return VoteHTML::OutputBeforeGame();

    case RoomScene::DAY:
      return VoteHTML::OutputDay();

    case RoomScene::NIGHT:
      return VoteHTML::OutputNight();

    default: //ここに来たらロジックエラー
      return VoteHTML::OutputError(VoteMessage::INVALID_SCENE);
    }
  }
}
