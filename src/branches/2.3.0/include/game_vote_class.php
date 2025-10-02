<?php
//-- GameVote 出力クラス --//
class GameVote {
  //実行
  static function Execute() {
    self::Load();
    RQ::Get()->vote ? self::Vote() : self::Output();
    DB::Disconnect();
  }

  //データロード
  private static function Load() {
    DB::Connect();
    Session::Certify();
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
      case 'GAMESTART':
	Loader::LoadFile('chaos_config', 'cast_class'); //配役情報をロード
	VoteGameStart::Execute();
	break;

      case 'KICK_DO':
	VoteKick::Execute();
	break;

      default: //ここに来たらロジックエラー
	VoteHTML::OutputError(VoteMessage::INVALID_COMMAND);
	break;
      }
    }
    elseif (DB::$SELF->IsDead()) { //死者の霊界投票処理
      if (DB::$SELF->IsDummyBoy() && RQ::Get()->situation == 'RESET_TIME') {
	VoteDummyBoy::ResetTime();
      } else {
	VoteHeaven::Execute();
      }
    }
    elseif (RQ::Get()->target_no == 0) { //空投票検出
      VoteHTML::OutputError(VoteMessage::NO_TARGET_TITLE, VoteMessage::NO_TARGET);
    }
    elseif (DB::$ROOM->IsDay()) { //昼の処刑投票処理
      VoteDay::Execute();
    }
    elseif (DB::$ROOM->IsNight()) { //夜の投票処理
      VoteNight::Execute();
    }
    else { //ここに来たらロジックエラー
      VoteHTML::OutputError(VoteMessage::INVALID_COMMAND, VoteMessage::NO_TARGET);
    }
  }

  //シーンに合わせた投票ページを出力
  static function Output() {
    Loader::LoadFile('vote_message');
    if (DB::$SELF->IsDead()) {
      DB::$SELF->IsDummyBoy() ? VoteHTML::OutputDummyBoy() : VoteHTML::OutputHeaven();
    }
    else {
      switch (DB::$ROOM->scene) {
      case 'beforegame':
	VoteHTML::OutputBeforeGame();
	break;

      case 'day':
	VoteHTML::OutputDay();
	break;

      case 'night':
	VoteHTML::OutputNight();
	break;

      default: //ここに来たらロジックエラー
	VoteHTML::OutputError(VoteMessage::INVALID_SCENE);
	break;
      }
    }
  }
}
