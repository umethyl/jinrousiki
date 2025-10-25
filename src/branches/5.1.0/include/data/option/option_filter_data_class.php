<?php
//-- ◆文字化け抑制◆ --//
//-- オプションフィルタデータベース --//
final class OptionFilterData {
  //-- 役職グループ --//
  //村人置換村グループ
  public static $group_replace_human = [
    'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire',
    'full_chiroptera', 'full_patron', 'full_mania', 'full_unknown_mania'
  ];

  //闇鍋モードグループ
  public static $group_chaos = ['chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso'];

  //闇鍋式希望制グループ (村人置換 + 闇鍋を追加)
  public static $group_wish_role_chaos = [
    'duel', 'festival',
    'change_common', 'change_hermit_common',
    'change_mad', 'change_fanatic_mad', 'change_whisper_mad', 'change_immolate_mad',
    'change_cupid', 'change_mind_cupid', 'change_triangle_cupid',
    'change_angel', 'change_exchange_angel'
  ];

  //-- Cast::Get() --//
  //基礎配役 (順番依存あり)
  public static $cast_base = [
    'festival', 'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso',
    'duel', 'gray_random', 'step', 'quiz'
  ];

  //追加配役/普通村
  public static $cast_add_role = [
    'detective', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid',
    'medium', 'mania'
  ];

  //追加配役/特殊配役村
  public static $cast_add_role_special = ['wolf', 'mad', 'fox', 'no_fox', 'depraver'];

  //追加配役/闇鍋固定枠
  public static $cast_chaos_fix_role = ['topping', 'museum_topping', 'gerd', 'detective'];

  //-- Cast::Execute() --//
  //身代わり君固定配役 (順番依存あり)
  public static $cast_dummy_boy_fix_role = ['quiz', 'gerd'];

  //身代わり君配役対象外
  public static $disable_cast_dummy_boy_role = ['detective'];

  //身代わり君配役制限
  public static $dummy_boy_cast_limit = ['dummy_boy_cast_limit'];

  //ユーザーサブ役職配役
  public static $cast_user_sub_role = [
    'decide', 'authority', 'joker', 'deep_sleep', 'blinder', 'mind_open', 'perverseness',
    'liar', 'gentleman', 'passion', 'critical', 'critical_chicken', 'sudden_death', 'quiz'
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
  //ユーザー名入力制限 (注意事項表示処理は順番依存あり)
  public static $user_entry_uname = ['necessary_name', 'necessary_trip'];

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
