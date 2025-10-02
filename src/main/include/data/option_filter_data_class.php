<?php
//-- ◆文字化け抑制◆ --//
//-- オプションフィルタデータベース --//
final class OptionFilterData {
  //-- Cast::Get() --//
  //配役 (順番依存あり)
  public static $cast_base = [
    'festival', 'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso',
    'duel', 'gray_random', 'step', 'quiz'
  ];

  //配役/闇鍋固定枠追加
  public static $cast_chaos_fix_role = ['topping', 'gerd', 'detective'];

  //配役/追加役職
  public static $cast_add_role = [
    'detective', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid',
    'medium', 'mania'
  ];

  //-- Cast::Execute() --//
  //身代わり君固定配役 (順番依存あり)
  public static $cast_dummy_boy_fix_role = ['quiz', 'gerd'];

  //身代わり君配役対象外
  public static $disable_cast_dummy_boy_role = ['detective'];

  //ユーザーサブ役職配役
  public static $cast_user_sub_role = [
    'decide', 'authority', 'joker', 'deep_sleep', 'blinder', 'mind_open', 'perverseness',
    'liar', 'gentleman', 'passion', 'critical', 'sudden_death', 'quiz'
  ];

  //ユーザーサブ役職配役 (闇鍋モード)
  public static $cast_user_chaos_sub_role = [
    'sub_role_limit_easy', 'sub_role_limit_normal', 'sub_role_limit_hard'
  ];

  //-- Cast::GenerateMessage() --//
  //配役メッセージ
  public static $cast_message = [
    'chaos_open_cast_camp', 'chaos_open_cast_role', 'chaos_open_cast_full'
  ];

  //-- UserManager --//
  //追加希望役職
  public static $add_wish_role = [
    'poison', 'assassin', 'boss_wolf', 'depraver', 'poison_wolf', 'possessed_wolf',
    'sirius_wolf', 'child_fox', 'cupid', 'medium'
  ];

  //-- Room --//
  //霊界公開判定
  public static $room_open_cast = ['not_open_cast', 'auto_open_cast'];

  //ゲーム開始時シーン取得
  public static $room_game_start_scene = ['open_day'];

  //-- User --//
  //発言回数初期化実施判定
  public static $initialize_talk_count = ['limit_talk', 'no_silence'];
}
