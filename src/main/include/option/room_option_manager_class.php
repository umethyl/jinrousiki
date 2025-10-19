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
      } elseif (RQ::Get()->duel) {
	self::LoadPostDuel();
      } elseif (RQ::Get()->gray_random || RQ::Get()->step) { //特殊配役
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
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$base_core);
    if (RQ::Get()->real_time) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_real_time);
    }
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$base);
  }

  //村作成オプション入力情報ロード (オプション変更用)
  private static function LoadPostInChange() {
    if (false === RQ::Get()->change_room) {
      return;
    }

    //変更できないオプションを自動セット
    foreach (RoomOptionFilterData::$fix_in_change as $option) {
      if (DB::$ROOM->IsOption($option)) {
	OptionLoader::Load($option)->LoadPost();
	if (RQ::Get()->$option) {
	  break;
	}
      }
    }

    $option = 'temporary_gm';
    if (DB::$ROOM->IsOption($option)) {
      RoomOptionLoader::Set(OptionGroup::GAME, $option);
    }
  }

  //村作成オプション入力情報ロード (クイズ村)
  private static function LoadPostQuiz() {
    if (false === RQ::Get()->change_room) {
      self::LoadPostPassword();
      self::Stack()->Set('gm_name', Message::GM);
      self::Stack()->Set('gm_password', RQ::Get()->gm_password);
    }
    RoomOptionLoader::Set(OptionGroup::GAME, 'dummy_boy');
    RoomOptionLoader::Set(OptionGroup::GAME, 'gm_login');
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
      RoomOptionLoader::Set(OptionGroup::GAME, 'dummy_boy');
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_gm_login);
    } else {
      if (false === RQ::Get()->change_room) {
	//「身代わり君はGM」が OFF なら仮GMモードを設定可能
	RoomOptionLoader::LoadPost(RoomOptionFilterData::$enable_temporary_gm);
      }
      if (RQ::Get()->dummy_boy) {
	if (false === RQ::Get()->change_room) {
	  self::Stack()->Set('gm_name', Message::DUMMY_BOY);
	  self::Stack()->Set('gm_password', ServerConfig::PASSWORD);
	}
	RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_dummy_boy);
      }
    }

    //ゲルト君モード無効はゲルト君モードと連動させる
    if (true === RQ::Get()->gerd) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_gerd);
    }
  }

  //村作成オプション入力情報ロード (闇鍋モード)
  private static function LoadPostChaos() {
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_chaos);
  }

  //村作成オプション入力情報ロード (決闘村)
  private static function LoadPostDuel() {
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_duel);
  }

  //村作成オプション入力情報ロード (特殊村)
  private static function LoadPostSpecial() {
    //現在は専用オプションなし
  }

  //村作成オプション入力情報ロード (通常村)
  private static function LoadPostNormal() {
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_normal);
    if (false === RQ::Get()->full_cupid) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$not_full_cupid);
    }
    if (false === RQ::Get()->full_mania) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$not_full_mania);
    }
    if (false === RQ::Get()->perverseness) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$not_perverseness);
    }
  }

  //村作成オプション入力情報ロード (クイズ村以外共通)
  private static function LoadPostCommonWithoutQuiz() {
    if (false === RQ::Get()->perverseness) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$not_perverseness_without_quiz);
    }
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_without_quiz);
    if (false === RQ::Get()->full_weather) {
      RoomOptionLoader::LoadPost(RoomOptionFilterData::$not_full_weather);
    }
  }

  //村作成オプション入力情報ロード (共通サブ役職関連)
  private static function LoadPostCommonSubRole() {
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$add_sub_role);
  }
}
