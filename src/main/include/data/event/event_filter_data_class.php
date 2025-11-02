<?php
//-- イベントフィルタデータベース --//
final class EventFilterData {
  //複合型イベント
  public static $multiple = ['aurora'];

  //仮想役職セット
  public static $virtual_role = [
    'liar', 'gentleman', 'lady', 'strong_voice', 'weak_voice', 'no_last_words', 'whisper_ringing',
    'howl_ringing', 'sweet_ringing', 'deep_sleep', 'mind_open'
  ];

  //仮想役職セット (昼限定)
  public static $virtual_role_day = [
    'actor', 'passion', 'rainbow', 'grassy', 'invisible', 'side_reverse', 'line_reverse',
    'confession', 'critical_voter', 'critical_luck', 'blinder', 'earplug', 'silent', 'mower',
    'hyper_critical'
  ];

  //仮想役職セット (悪戯 / 順番依存あり)
  public static $bad_status = ['shadow_fairy', 'face_status', 'enchant_mad'];

  //決選投票
  public static $vote_duel = ['vote_duel'];

  //処刑投票数補正
  public static $vote_do = ['hyper_random_voter'];

  //処刑投票妨害
  public static $vote_kill_action = ['frostbite', 'psycho_infected'];

  //処刑者決定
  public static $decide_vote_kill = ['settle'];

  //夜投票封印
  public static $seal_vote_night = [
    'full_moon', 'new_moon', 'no_contact', 'no_trap', 'full_escape', 'no_escape', 'full_revive',
    'no_dream'
  ];

  //足音
  public static $step = ['random_step'];

  //罠能力無効
  public static $disable_trap = ['no_contact', 'no_trap', 'full_escape'];

  //神隠し
  public static $tengu_kill = ['tengu_kill'];

  //悪戯 (妖精)
  public static $fairy_mage = ['star_fairy', 'flower_fairy'];
}
