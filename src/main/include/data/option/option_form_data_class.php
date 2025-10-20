<?php
//-- ◆文字化け抑制◆ --//
//-- オプション入力画面データベース --//
final class OptionFormData {
  //表示順
  public static $order = [
    'room_name', 'room_comment', 'max_user',
    'base' => null,
    'wish_role', 'real_time', 'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name',
    'necessary_trip', 'close_room',
    'dummy_boy' => null,
    'dummy_boy_selector', 'gm_password', 'gerd', 'disable_gerd', 'temporary_gm',
    'dummy_boy_cast_limit',
    'talk' => null,
    'wait_morning', 'limit_last_words', 'limit_talk', 'secret_talk', 'no_silence',
    'open_cast' => null,
    'not_open_cast_selector',
    'add_role' => null,
    'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
    'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid', 'medium', 'mania',
    'decide', 'authority',
    'special' => null,
    'liar', 'gentleman', 'passion', 'sudden_death', 'perverseness', 'deep_sleep', 'mind_open',
    'blinder', 'critical', 'notice_critical', 'critical_chicken', 'joker', 'death_note',
    'detective', 'weather', 'full_weather', 'festival', 'replace_human_selector',
    'change_common_selector', 'change_mad_selector', 'change_cupid_selector',
    'special_cast' => null,
    'special_role',
    'chaos' => null,
    'topping', 'boost_rate', 'chaos_open_cast', 'sub_role_limit', 'secret_sub_role',
    'duel' => null,
    'duel_selector'
  ];
}
