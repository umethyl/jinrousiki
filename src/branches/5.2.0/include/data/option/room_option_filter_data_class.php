<?php
//-- ◆文字化け抑制◆ --//
//-- 村作成オプションフィルタデータベース --//
final class RoomOptionFilterData {
  //-- 入力データチェック --//
  //村の名前・コメント
  public static $validate_create_name = ['room_name', 'room_comment'];

  //最大人数
  public static $validate_create_user = ['max_user'];

  //変更登録時
  public static $store_in_change = ['close_room'];

  //-- RoomOptionManager::LoadPostBase() --//
  //標準(併用判定用)
  public static $base_core = ['wish_role', 'real_time'];

  //real_time 併用
  public static $add_real_time = ['wait_morning'];

  //標準
  public static $base = [
    'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name', 'necessary_trip',
    'limit_last_words', 'limit_talk', 'secret_talk', 'dummy_boy_selector',
    'not_open_cast_selector', 'perverseness', 'replace_human_selector', 'special_role'
  ];

  //-- RoomOptionManager::LoadPostInChange() --//
  //変更時固定
  public static $fix_in_change = ['gm_login', 'dummy_boy'];

  //-- RoomOptionManager::LoadPostQuiz() --//
  //-- RoomOptionManager::LoadPostSpecial() --//
  //特殊配役村併用
  public static $add_special = ['wolf', 'mad', 'fox', 'no_fox', 'depraver'];

  //-- RoomOptionManager::LoadPostDummyBoy() --//
  //身代わり君はGM有効時
  public static $add_gm_login = ['gerd', 'dummy_boy_cast_limit'];

  //仮GMモード有効
  public static $enable_temporary_gm = ['temporary_gm'];

  //身代わり君有効時
  public static $add_dummy_boy = ['gerd', 'dummy_boy_cast_limit'];

  //ゲルト君モード有効時
  public static $add_gerd = ['disable_gerd'];

  //-- RoomOptionManager::LoadPostChaos() --//
  //闇鍋モード併用
  public static $add_chaos = [
    'secret_sub_role', 'topping', 'boost_rate', 'museum_topping', 'museum_boost',
    'chaos_open_cast', 'sub_role_limit'
  ];

  //-- RoomOptionManager::LoadPostDuel() --//
  //決闘村併用
  public static $add_duel = ['duel_selector'];

  //-- RoomOptionManager::LoadPostNormal() --//
  //普通村併用
  public static $add_normal = [
    'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
    'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'medium'
  ];

  //キューピッド村併用不可
  public static $not_full_cupid = ['cupid'];

  //神話マニア村併用不可
  public static $not_full_mania = ['mania'];

  //天邪鬼村併用不可
  public static $not_perverseness = ['decide', 'authority'];

  //-- RoomOptionManager::LoadPostCommonWithoutQuiz() --//
  //天邪鬼村併用不可 (クイズ村以外)
  public static $not_perverseness_without_quiz = ['sudden_death'];

  //クイズ村以外併用
  public static $add_without_quiz = [
    'detective', 'full_weather', 'festival', 'change_common_selector',
    'change_mad_selector', 'change_cupid_selector'
  ];

  //天変地異併用不可
  public static $not_full_weather = ['weather'];

  //-- RoomOptionManager::LoadPostCommonSubRole() --//
  //標準サブ役職
  public static $add_sub_role = [
    'no_silence', 'liar', 'gentleman', 'passion', 'deep_sleep', 'mind_open', 'blinder',
    'critical', 'notice_critical', 'critical_chicken', 'joker', 'death_note'
  ];
}
