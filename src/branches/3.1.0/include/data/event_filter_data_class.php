<?php
//-- イベントフィルタデータベース --//
class EventFilterData {
  //複合型イベント
  public static $multiple = array('aurora');

  //仮想役職セット
  public static $virtual_role = array(
    'liar', 'gentleman', 'lady', 'strong_voice', 'weak_voice', 'no_last_words', 'whisper_ringing',
    'howl_ringing', 'sweet_ringing', 'deep_sleep', 'mind_open'
  );

  //仮想役職セット (昼限定)
  public static $virtual_role_day = array(
    'actor', 'passion', 'rainbow', 'grassy', 'invisible', 'side_reverse', 'line_reverse',
    'confession', 'critical_voter', 'critical_luck', 'blinder', 'earplug', 'silent', 'mower',
    'hyper_critical'
  );

  //仮想役職セット (悪戯 / 順番依存あり)
  public static $bad_status = array('shadow_fairy', 'enchant_mad');

  //決選投票
  public static $vote_duel = array('vote_duel');

  //処刑投票数補正
  public static $vote_do = array('hyper_random_voter');

  //処刑投票妨害
  public static $vote_kill_action = array('frostbite', 'psycho_infected');

  //処刑者決定
  public static $decide_vote_kill = array('settle');

  //夜投票封印
  public static $seal_vote_night = array(
    'full_moon', 'new_moon', 'no_contact', 'no_trap', 'full_escape', 'no_escape', 'full_revive',
    'no_dream'
  );

  //足音
  public static $step = array('random_step');

  //罠能力無効
  public static $ignore_set_trap = array('no_contact', 'no_trap', 'full_escape');

  //神隠し
  public static $tengu_kill = array('tengu_kill');

  //悪戯 (妖精)
  public static $fairy_mage = array('star_fairy', 'flower_fairy');
}
