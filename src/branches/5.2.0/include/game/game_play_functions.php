<?php
//◆文字化け抑制◆//
//-- GamePlayView ローダー --//
final class GamePlayViewLoader extends LoadManager {
  const PATH = '%s/game/instance/game_play_view_%s.php';
  const CLASS_PREFIX = 'GamePlayView_';
  protected static $file  = [];
  protected static $class = [];
}

//-- GamePlay 処理クラス --//
final class GamePlay {
  //シーン別 View クラスロード
  public static function LoadView() {
    if (DB::$ROOM->IsOn(RoomMode::HEAVEN)) {
      $class = 'heaven';
    } elseif (DB::$ROOM->IsFinished()) {
      $class ='after';
    } elseif (DB::$ROOM->IsBeforeGame()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene);
      $class = 'before';
    } elseif (DB::$ROOM->IsDay()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene);
      $class = 'day';
    } elseif (DB::$ROOM->IsNight()) {
      $class = 'night';
    }
    GamePlayViewLoader::Load($class);

    $class_name = GamePlayViewLoader::CLASS_PREFIX . $class;
    return new $class_name;
  }

  //発言処理
  public static function Talk() {
    GamePlayTalk::InitStack(); //判定用変数初期化

    //発言送信フレーム (bottom) 判定 > 霊界GM判定
    if (RQ::Enable('individual_talk') ||
	DB::$ROOM->IsOff(RoomMode::DEAD) || DB::$ROOM->IsOn(RoomMode::HEAVEN)) {
      GamePlayTalk::Convert(); //発言変換処理

      /*
	空発言 (ゲーム停滞判定) > CSRF対策 > 遺言 (詳細判定は関数内で行う) >
	発言判定(死者 / 身代わり君 / 同一ゲームシーン) > 発言不可 (ゲーム停滞判定)
      */
      if (RQ::Fetch()->say == '') {
	self::FilterSilence();
      } elseif (Security::IsInvalidToken(DB::$ROOM->id)) {
	HTML::OutputUnusableError();
      } elseif (RQ::Fetch()->last_words && (DB::$ROOM->IsBeforeGame() || ! DB::$SELF->IsDummyBoy())) {
	GamePlayTalk::StoreLastWords(RQ::Fetch()->say);
      } elseif (DB::$SELF->IsDead() || DB::$SELF->IsDummyBoy() || ! DB::$SELF->IsInvalidScene()) {
	GamePlayTalk::Store(RQ::Fetch()->say);
      } else {
	self::FilterSilence();
      }

      if (DB::$SELF->IsInvalidScene()) { //ゲームシーンを更新
	DB::$SELF->Update('last_load_scene', DB::$ROOM->scene);
      }
    } elseif (DB::$ROOM->IsOn(RoomMode::DEAD) && DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      if (false === GameTime::IsInTime()) { //超過なら突然死タイマーを見れるようにする
	DB::$ROOM->SetSuddenDeath();
      }
    }
  }

  //ゲーム停滞のチェック
  public static function FilterSilence() {
    if (false === DB::$ROOM->IsPlaying()) { //スキップ判定
      return true;
    }

    //経過時間を取得
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      if ($left_time > 0) { //制限時間超過判定
	return true;
      }
    } else { //仮想時間制
      if (false === self::LockScene()) { //判定条件が全て DB なので即ロック
	return false;
      }
      $silence_pass_time = GameTime::GetTalkPass($left_time, true);

      if ($left_time > 0) { //制限時間超過判定
	if (RoomDB::GetTime() <= TimeConfig::SILENCE) { //沈黙判定
	  return DB::Rollback();
	}

	//沈黙メッセージを発行してリセット
	$talk = new RoomTalkStruct(sprintf(GamePlayMessage::SILENCE, $silence_pass_time));
	$talk->Set(TalkStruct::SPEND_TIME, TimeConfig::SILENCE_PASS);
	DB::$ROOM->Talk($talk);
	return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback();
      }
    }

    //オープニングなら即座に夜に移行する
    if (DateBorder::One() && DB::$ROOM->IsDay() && DB::$ROOM->IsOption('open_day')) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (false === self::LockScene()) { //シーン再判定
	  return false;
	}
      }
      DB::$ROOM->ChangeNight(); //夜に切り替え
      return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback(); //最終書き込み時刻を更新
    }

    if (! RoomDB::IsOvertimeAlert()) { //警告メッセージ出力判定
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (false === self::LockScene()) { //シーン再判定
	  return false;
	}
      }

      //警告メッセージを出力 (最終出力判定は呼び出し先で行う)
      $str = sprintf(GamePlayMessage::SUDDEN_DEATH_ALERT, Time::Convert(TimeConfig::SUDDEN_DEATH));
      if (DB::$ROOM->OvertimeAlert($str)) { //出力したら突然死タイマーをリセット
	DB::$ROOM->ResetSuddenDeath();
	if (DB::$ROOM->IsDay() && DB::$ROOM->IsOption('no_silence')) { //沈黙死 + 処刑投票処理
	  self::VoteNoSilence();
	}
	return DB::Commit(); //ロック解除
      }
    }

    DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得

    //制限時間前ならスキップ (この段階でロックしているのは仮想時間制のみ)
    if (DB::$ROOM->sudden_death > 0) {
      return DB::$ROOM->IsRealTime() || DB::Rollback();
    }

    //制限時間を過ぎていたら未投票の人を突然死させる
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
      if (false === self::LockScene()) { //シーン再判定
	return false;
      }

      DB::$ROOM->SetSuddenDeath(); //制限時間を再計算
      if (DB::$ROOM->sudden_death > 0) {
	return DB::Rollback();
      }
    }

    if (abs(DB::$ROOM->sudden_death) > TimeConfig::SERVER_DISCONNECT) { //サーバダウン検出
      //突然死タイマーと警告出力判定をリセット
      return RoomDB::UpdateOvertimeAlert() ? DB::Commit() : DB::Rollback();
    }

    $novote_list = []; //未投票者リスト
    DB::$ROOM->LoadVote(); //投票情報を取得
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->Get() as $user) { //生存中の未投票者を取得
	if ($user->IsLive() && ! $user->ExistsVote()) {
	  $novote_list[] = $user->id;
	}
      }
    } elseif (DB::$ROOM->IsNight()) {
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      foreach (DB::$USER->Get() as $user) { //未投票チェック
	if (RoleUser::ImcompletedVoteNight($user, $vote_data)) {
	  $novote_list[] = $user->id;
	}
      }
    }

    //未投票突然死処理
    GameAction::SuddenDeath($novote_list, DeadReason::NOVOTED);
    return DB::Commit(); //ロック解除
  }

  //シーン再判定付きロック処理
  private static function LockScene() {
    if (false === DB::Transaction()) {
      return false;
    }

    if (RoomDB::Get('scene', true) != DB::$ROOM->scene) { //シーン再判定 (ロック付き)
      DB::Rollback();
      return false;
    } else {
      return true;
    }
  }

  //沈黙死 + 処刑投票処理
  private static function VoteNoSilence() {
    if (RoomDB::Get('vote_count', true) != DB::$ROOM->vote_count) { //投票回数判定
      return;
    }

    if (TalkDB::CountNoVoteTalker() > 0) { //発言者の投票済み判定
      return;
    }

    RQ::Set(RequestDataVote::SITUATION, VoteAction::VOTE_KILL); //仮想的に処刑投票コマンドをセット
    /*
      Vote は初期化時点で ROOM/USER をロックをかけて生成している
      処刑投票処理時に霊界操作で変更される要素を参照しないので
      GamePlay 用のパラメータ再セットが煩雑になる事を配慮し、再生成は行わない
    */
    VoteDay::Aggregate();
  }
}
