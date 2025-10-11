<?php
//-- 発言処理クラス (GamePlay 拡張) --//
final class GamePlayTalk {
  //判定用変数初期化
  public static function InitStack() {
    Talk::Stack()->Set(Talk::LIMIT_SAY, null);
    Talk::Stack()->Set(Talk::UPDATE, false);
    if (DB::$ROOM->IsOption('limit_talk')) {
      Talk::Stack()->Set(Talk::LIMIT_TALK, false);
    }
  }

  //発言変換処理
  public static function Convert() {
    Talk::Stack()->Set(Talk::LIMIT_SAY, RoleTalk::Convert(RQ::Get()->say)); //発言置換処理
    Text::Escape(RQ::Get()->say, false); //エスケープ処理 (以降に置換処理が無いことが前提)
  }

  //発言登録
  public static function Store($say) {
    //-- 特殊発言判定 --//
    RQ::Set('individual_talk', false);
    RQ::Set('secret_talk',     false);
    if (self::IsIndividual()) {
      //-- 個別発言 --//
      RQ::Set('individual_talk', true);
      RQ::Set(RequestDataTalk::VOICE, TalkVoice::NORMAL); //声の大きさは普通で固定
    } elseif (RQ::Get()->font_type == TalkVoice::SECRET) {
      //-- 秘密発言判定 --//
      RQ::Set('secret_talk', true);
      RQ::Set(RequestDataTalk::VOICE, TalkVoice::NORMAL); //声の大きさは普通で固定
    }

    //-- タイマー更新判定 --//
    Talk::Stack()->Set(Talk::UPDATE, true);

    /*
      無条件登録判定
      身代わり君の個別発言 > ゲーム開始前後 > 身代わり君のシステムメッセージ (遺言) > 死者の霊話
    */
    $talk = new RoleTalkStruct($say);
    if (true === RQ::Get()->individual_talk) {
      $location = TalkLocation::INDIVIDUAL . ':' . RQ::Get()->{RequestDataTalk::TARGET};
      $talk->Set(TalkStruct::LOCATION, $location);
      return RoleTalk::Store($talk, true);
    } elseif (false === DB::$ROOM->IsPlaying()) {
      return RoleTalk::Store($talk, true);
    } elseif (RQ::Get()->last_words && DB::$SELF->IsDummyBoy()) {
      $talk->Set(TalkStruct::LOCATION, TalkLocation::DUMMY_BOY);
      return RoleTalk::Store($talk);
    } elseif (DB::$SELF->IsDead()) {
      $talk->Set(TalkStruct::SCENE, RoomScene::HEAVEN);
      return RoleTalk::Store($talk);
    }

    //-- 制限時間判定 --//
    if (GameTime::GetLeftTime() < 1) { //制限時間外ならスキップ (ここに来るのは生存者のみのはず)
      return false;
    }

    //-- シーン別処理 --//
    if (DB::$ROOM->IsDay()) { //昼はそのまま発言
      if (DB::$ROOM->IsEvent('wait_morning')) { //待機時間判定
	return false;
      }

      if (RQ::Get()->secret_talk) {
	$talk->Set(TalkStruct::LOCATION, TalkLocation::SECRET);
      } else {
	//発言数制限制
	if (DB::$ROOM->IsOption('limit_talk') && false === self::UpdateLimitTalkCount()) {
	  return false;
	}

	//沈黙禁止
	if (DB::$ROOM->IsOption('no_silence') && false === self::UpdateNoSilenceTalkCount()) {
	  return false;
	}

	RoleTalk::EchoSay(); //山彦の処理
      }

      $talk->Set(TalkStruct::SPEND_TIME, GameTime::GetSpendTime($say));
      return RoleTalk::Store($talk, true);
    } else { //夜の処理 (役職毎に分ける)
      //仮想ユーザで判定
      $talk->Set(TalkStruct::LOCATION, RoleTalk::GetLocation(DB::$SELF->GetVirtual(), DB::$SELF));

      //時間経過するのは人狼の発言のみ (本人判定)
      $update = DB::$SELF->IsMainGroup(CampGroup::WOLF);
      if (true === $update) {
	$talk->Set(TalkStruct::SPEND_TIME, GameTime::GetSpendTime($say));
      }
      return RoleTalk::Store($talk, $update);
    }
  }

  //遺言登録
  public static function StoreLastWords($say) {
    //-- スキップ判定 (シーン > オプション) --//
    if (DB::$ROOM->IsFinished()) {
      return false;
    } elseif (DB::$ROOM->IsOption('limit_last_words') && DB::$ROOM->IsPlaying()) {
      return false;
    }

    //-- 加工処理 --//
    if ($say == ' ') { //スペースだけなら「消去」
      $say = null;
    }

    //-- 登録処理 (ゲーム開始前(無条件) > 生存者(登録制限) > 死者(霊界遺言登録能力者)) --//
    if (DB::$ROOM->IsBeforeGame()) {
      DB::$SELF->Update('last_words', $say);
    } elseif (DB::$SELF->IsLive()) {
      if (! RoleUser::LimitedLastWords(DB::$SELF)) {
	DB::$SELF->Update('last_words', $say);
      }
    } elseif (DB::$SELF->IsDead()) {
      RoleTalk::StoreHeavenLastWords($say);
    }

    //-- タイマー更新判定 --//
    Talk::Stack()->Set(Talk::UPDATE, DB::$SELF->IsDummyBoy());
  }

  //特殊発言判定 (個別発言)
  private static function IsIndividual() {
    //身代わり君限定
    if (false === DB::$SELF->IsDummyBoy()) {
      return false;
    }

    //プレイ中限定
    if (false === DB::$ROOM->IsPlaying()) {
      return false;
    }

    //フラグ判定
    RQ::Get()->ParsePostOn(RequestDataTalk::INDIVIDUAL);
    if (RQ::Get()->Disable(RequestDataTalk::INDIVIDUAL)) {
      return false;
    }

    //対象者
    RQ::Get()->ParsePostInt(RequestDataTalk::TARGET);
    $target_id = RQ::Get()->{RequestDataTalk::TARGET};
    $user      = DB::$USER->ByID($target_id);
    if ($target_id != $user->id) {
      return false;
    }

    return true;
  }

  //発言数更新 (発言数制限制用)
  private static function UpdateLimitTalkCount() {
    if (DB::$SELF->GetTalkCount() >= DB::$ROOM->GetLimitTalk()) {
      Talk::Stack()->Set(Talk::LIMIT_TALK, true);
      return false;
    }

    //ロックをかけて発言数を更新
    DB::Transaction();
    if (DB::$SELF->GetTalkCount(true) >= DB::$ROOM->GetLimitTalk()) {
      DB::Rollback();
      Talk::Stack()->Set(Talk::LIMIT_TALK, true);
      return false;
    }

    if (TalkDB::UpdateUserTalkCount()) {
      DB::Commit();
      DB::$SELF->talk_count++;
      return true;
    } else {
      DB::Rollback();
      return false;
    }
  }

  //発言数更新 (沈黙禁止用)
  private static function UpdateNoSilenceTalkCount() {
    if (DB::$SELF->GetTalkCount() > 0) { //発言済みならスキップ
      return true;
    }
    return TalkDB::UpdateUserTalkCount(); //1 以上であればいいのでロックしない
  }
}
