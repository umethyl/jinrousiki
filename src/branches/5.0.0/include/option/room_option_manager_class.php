<?php
//-- 村作成オプションマネージャ --//
//-- ◆文字化け抑制◆ --//
final class RoomOptionManager extends StackStaticManager {
  //村オプション変更実施判定
  public static function IsChange() {
    if (self::Stack()->IsEmpty('change')) {
      self::Stack()->Set('change', false);
    }
    return self::Stack()->Get('change');
  }

  //村オプション変更権限判定 (身代わり君 or 仮GM)
  public static function EnableChange() {
    return DB::$SELF->IsDummyBoy() || self::IsTemporaryGM();
  }

  //村作成オプション入力情報ロード
  public static function LoadPost() {
    self::LoadPostBase();
    self::LoadPostInChange();

    if (RQ::Get()->quiz) { //クイズ村
      self::LoadPostQuiz();
    } else {
      self::LoadPostDummyBoy();
      if (RQ::Get()->chaos || RQ::Get()->chaosfull || RQ::Get()->chaos_hyper ||
	  RQ::Get()->chaos_verso) { //闇鍋モード
	self::LoadPostChaos();
      } elseif (RQ::Get()->duel || RQ::Get()->gray_random || RQ::Get()->step) { //特殊配役
	self::LoadPostSpecial();
      } else { //通常村
	self::LoadPostNormal();
      }
      self::LoadPostCommonWithoutQuiz();
    }

    self::LoadPostCommonSubRole();
  }

  //仮GM判定
  private static function IsTemporaryGM() {
    $option = 'temporary_gm';
    if (DB::$ROOM->IsOption($option)) {
      $filter = OptionLoader::Load($option);
      return $filter->IsTemporaryGM();
    } else {
      return false;
    }
  }

  //村作成オプション入力情報ロード (基本オプション)
  private static function LoadPostBase() {
    RoomOption::LoadPost('wish_role', 'real_time');
    if (RQ::Get()->real_time) {
      RoomOption::LoadPost('wait_morning');
    }

    RoomOption::LoadPost(
      'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name', 'necessary_trip',
      'limit_last_words', 'limit_talk', 'secret_talk', 'dummy_boy_selector',
      'not_open_cast_selector', 'perverseness', 'replace_human_selector', 'special_role'
    );
  }

  //村作成オプション入力情報ロード (オプション変更用)
  private static function LoadPostInChange() {
    if (false === RQ::Get()->change_room) {
      return;
    }

    //変更できないオプションを自動セット
    foreach (['gm_login', 'dummy_boy'] as $option) {
      if (DB::$ROOM->IsOption($option)) {
	OptionLoader::Load($option)->LoadPost();
	if (RQ::Get()->$option) {
	  break;
	}
      }
    }

    $option = 'temporary_gm';
    if (DB::$ROOM->IsOption($option)) {
      RoomOption::Set(OptionGroup::GAME, $option);
    }
  }

  //村作成オプション入力情報ロード (クイズ村)
  private static function LoadPostQuiz() {
    if (false === RQ::Get()->change_room) {
      self::LoadPostPassword();
      self::Stack()->Set('gm_name', Message::GM);
      self::Stack()->Set('gm_password', RQ::Get()->gm_password);
    }
    RoomOption::Set(OptionGroup::GAME, 'dummy_boy');
    RoomOption::Set(OptionGroup::GAME, 'gm_login');
  }

  //村作成オプション入力情報ロード (GMログインパスワード)
  private static function LoadPostPassword() {
    RQ::Get()->ParsePostStr('gm_password');
    if (RQ::Get()->gm_password == '') {
      RoomManagerHTML::OutputResult('no_password');
    }
  }

  //村作成オプション入力情報ロード (身代わり君関連)
  private static function LoadPostDummyBoy() {
    if (RQ::Get()->gm_login) {
      if (false === RQ::Get()->change_room) {
	self::LoadPostPassword();
	self::Stack()->Set('gm_name', Message::GM);
	self::Stack()->Set('gm_password', RQ::Get()->gm_password);
      }
      RoomOption::Set(OptionGroup::GAME, 'dummy_boy');
      RoomOption::LoadPost('gerd', 'dummy_boy_cast_limit');
    } else {
      if (false === RQ::Get()->change_room) {
	RoomOption::LoadPost('temporary_gm'); //「身代わり君はGM」が OFF なら仮GMモードを設定可能
      }
      if (RQ::Get()->dummy_boy) {
	if (false === RQ::Get()->change_room) {
	  self::Stack()->Set('gm_name', Message::DUMMY_BOY);
	  self::Stack()->Set('gm_password', ServerConfig::PASSWORD);
	}
	RoomOption::LoadPost('gerd', 'dummy_boy_cast_limit');
      }
    }

    //ゲルト君モード無効はゲルト君モードと連動させる
    if (true === RQ::Get()->gerd) {
      RoomOption::LoadPost('disable_gerd');
    }
  }

  //村作成オプション入力情報ロード (闇鍋モード)
  private static function LoadPostChaos() {
    RoomOption::LoadPost(
      'secret_sub_role', 'topping', 'boost_rate', 'chaos_open_cast', 'sub_role_limit'
    );
  }

  //村作成オプション入力情報ロード (特殊村)
  private static function LoadPostSpecial() {
    //現在は専用オプションなし
  }

  //村作成オプション入力情報ロード (通常村)
  private static function LoadPostNormal() {
    RoomOption::LoadPost(
      'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
      'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'medium'
    );
    if (false === RQ::Get()->full_cupid) {
      RoomOption::LoadPost('cupid');
    }
    if (false === RQ::Get()->full_mania) {
      RoomOption::LoadPost('mania');
    }
    if (false === RQ::Get()->perverseness) {
      RoomOption::LoadPost('decide', 'authority');
    }
  }

  //村作成オプション入力情報ロード (クイズ村以外共通)
  private static function LoadPostCommonWithoutQuiz() {
    if (false === RQ::Get()->perverseness) {
      RoomOption::LoadPost('sudden_death');
    }
    RoomOption::LoadPost(
      'joker', 'death_note', 'detective', 'full_weather', 'festival', 'change_common_selector',
      'change_mad_selector', 'change_cupid_selector'
    );
    if (false === RQ::Get()->full_weather) {
      RoomOption::LoadPost('weather');
    }
  }

  //村作成オプション入力情報ロード (共通サブ役職関連)
  private static function LoadPostCommonSubRole() {
    RoomOption::LoadPost(
      'no_silence', 'liar', 'gentleman', 'passion', 'deep_sleep', 'mind_open', 'blinder',
      'critical', 'notice_critical'
    );
  }
}
