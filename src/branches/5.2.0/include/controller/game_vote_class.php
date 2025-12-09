<?php
//-- ◆文字化け抑制◆ --//
//-- GameVote コントローラー --//
final class GameVoteController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'game_vote';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::Login();
  }

  protected static function EnableLoadRoom() {
    return true;
  }

  protected static function LoadRoom() {
    if (false === DB::Transaction()) { //トランザクション開始
      VoteHTML::OutputResult(VoteMessage::DB_ERROR);
    }

    DB::LoadRoom(true); //ロック付き
    if (DB::$ROOM->IsFinished()) {
      VoteHTML::OutputError(VoteMessage::FINISHED_TITLE, VoteMessage::FINISHED);
    }
    DB::$ROOM->SetTime();
  }

  protected static function LoadUser() {
    DB::LoadUser(true); //ロック付き
  }

  protected static function LoadSelf() {
    DB::LoadSelf();
  }

  protected static function EnableCommand() {
    return RQ::Fetch()->vote;
  }

  protected static function RunCommand() {
    if (DB::$ROOM->IsBeforeGame()) { //ゲーム開始 or Kick 投票処理
      switch (RQ::Fetch()->situation) {
      case VoteAction::GAME_START:
	return VoteGameStart::Execute();

      case VoteAction::KICK:
	return VoteKick::Execute();

      default: //ここに来たらロジックエラー
	return VoteHTML::OutputError(VoteMessage::INVALID_COMMAND);
      }
    } elseif (DB::$SELF->IsDead()) { //死者の霊界投票処理
      if (DB::$SELF->IsDummyBoy()) { //身代わり君機能
	switch (RQ::Fetch()->situation) {
	case VoteAction::FORCE_SUDDEN_DEATH:
	  return VoteForceSuddenDeath::Execute();

	case VoteAction::RESET_TIME:
	  return VoteResetTime::Execute();

	default:
	  return VoteHeaven::Execute();
	}
      } else {
	VoteHeaven::Execute();
      }
    } elseif (RQ::Fetch()->target_no == 0) { //空投票検出
      VoteHTML::OutputError(VoteMessage::NO_TARGET_TITLE, VoteMessage::NO_TARGET);
    } elseif (DB::$ROOM->IsDay()) { //昼の処刑投票処理
      VoteDay::Execute();
    } elseif (DB::$ROOM->IsNight()) { //夜の投票処理
      VoteNight::Execute();
    } else { //ここに来たらロジックエラー
      VoteHTML::OutputError(VoteMessage::INVALID_COMMAND, VoteMessage::NO_TARGET);
    }
  }

  protected static function Output() {
    if (DB::$SELF->IsDead()) { //死者は専用ページ
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

  protected static function Finish() {
    DB::Disconnect();
  }
}
