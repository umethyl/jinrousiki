<?php
//-- ◆文字化け抑制◆ --//
//-- オプションフィルタデータベース --//
final class OptionFilterData {
  //追加役職
  public static $add_role = [
    'detective', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid',
    'medium', 'mania'
  ];

  //追加サブ配役
  public static $add_sub_role = [
    'decide', 'authority', 'joker', 'deep_sleep', 'blinder', 'mind_open', 'perverseness',
    'liar', 'gentleman', 'passion', 'critical', 'sudden_death', 'quiz'
  ];

  //追加希望役職
  public static $add_wish_role = [
    'poison', 'assassin', 'boss_wolf', 'depraver', 'poison_wolf', 'possessed_wolf',
    'sirius_wolf', 'child_fox', 'cupid', 'medium'
  ];

  //闇鍋固定枠追加
  public static $chaos_fix_role = ['topping', 'gerd', 'detective'];

  //サブ役職配布対象リスト
  public static $cast_sub_role = [
    'sub_role_limit_easy', 'sub_role_limit_normal', 'sub_role_limit_hard'
  ];

  //配役メッセージ
  public static $cast_message = ['chaos_open_cast_camp', 'chaos_open_cast_role'];
}
