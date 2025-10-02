<?php
//-- 発言処理クラス (GamePlay 拡張) --//
class GamePlayTalk {
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
  public static function Save($say) {
    //-- 秘密発言判定 --//
    if (RQ::Get()->font_type == TalkVoice::SECRET) {
      RQ::Set('secret_talk', true);
      RQ::Set('font_type', TalkVoice::NORMAL); //声の大きさは普通で固定
    } else {
      RQ::Set('secret_talk', false);
    }

    //-- タイマー更新判定 --//
    Talk::Stack()->Set(Talk::UPDATE, true);

    //-- 無条件登録 (ゲーム開始前後 > 身代わり君のシステムメッセージ (遺言) > 死者の霊話) --//
    if (! DB::$ROOM->IsPlaying()) {
      return RoleTalk::Save($say, DB::$ROOM->scene, null, 0, true);
    } elseif (RQ::Get()->last_words && DB::$SELF->IsDummyBoy()) {
      return RoleTalk::Save($say, DB::$ROOM->scene, TalkLocation::DUMMY_BOY);
    } elseif (DB::$SELF->IsDead()) {
      return RoleTalk::Save($say, RoomScene::HEAVEN);
    }

    //-- 制限時間判定 --//
    $left_time  = GameTime::GetLeftTime();
    $spend_time = GameTime::GetSpendTime($say);
    if ($left_time < 1) return false; //制限時間外ならスキップ (ここに来るのは生存者のみのはず)

    //-- シーン別処理 --//
    if (DB::$ROOM->IsDay()) { //昼はそのまま発言
      if (DB::$ROOM->IsEvent('wait_morning')) return false; //待機時間判定

      if (! RQ::Get()->secret_talk) {
	if (DB::$ROOM->IsOption('limit_talk')) { //発言数制限制
	  if (! self::UpdateLimitTalkCount()) return false;
	}

	if (DB::$ROOM->IsOption('no_silence')) { //沈黙禁止
	  if (! self::UpdateNoSilenceTalkCount()) return false;
	}

	RoleTalk::EchoSay(); //山彦の処理
      }

      $location = RQ::Get()->secret_talk ? TalkLocation::SECRET : null;
      return RoleTalk::Save($say, DB::$ROOM->scene, $location, $spend_time, true);
    } else { //夜の処理 (役職毎に分ける)
      //仮想ユーザで判定, 時間経過するのは人狼の発言のみ (本人判定)
      $location = RoleTalk::GetLocation(DB::$SELF->GetVirtual(), DB::$SELF);
      $update   = DB::$SELF->IsMainGroup(CampGroup::WOLF);
      return RoleTalk::Save($say, DB::$ROOM->scene, $location, $update ? $spend_time : 0, $update);
    }
  }

  //遺言登録
  public static function SaveLastWords($say) {
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
      RoleTalk::SaveHeavenLastWords($say);
    }

    //-- タイマー更新判定 --//
    Talk::Stack()->Set(Talk::UPDATE, DB::$SELF->IsDummyBoy());
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
    if (DB::$SELF->GetTalkCount() > 0) return true; //発言済みならスキップ
    return TalkDB::UpdateUserTalkCount(); //1 以上であればいいのでロックしない
  }
}
