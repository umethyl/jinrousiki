<?php
//-- 役職フィルタデータベース --//
class RoleFilterData {
  //常時表示サブ役職 (本体 / 順番依存あり)
  public static $display_real = array(
    'copied', 'copied_trick', 'copied_basic', 'copied_nymph', 'copied_soul', 'copied_teller',
    'lost_ability', 'muster_ability', 'lovers', 'sweet_status', 'challenge_lovers', 'vega_lovers',
    'fake_lovers', 'possessed_exchange', 'letter_exchange', 'joker', 'rival', 'death_note'
  );

  //常時表示サブ役職 (仮想 / 順番依存あり)
  public static $display_virtual = array(
    'death_selected', 'febris', 'chill_febris', 'frostbite', 'death_warrant', 'thorn_cross',
    'infatuated', 'day_voter', 'wirepuller_luck', 'occupied_luck', 'tengu_voice', 'mind_open',
    'mind_read', 'mind_evoke', 'mind_lonely', 'mind_receiver', 'mind_friend', 'mind_sympathy',
    'mind_sheep', 'mind_presage', 'wisp', 'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp',
    'tengu_spell_wisp', 'sheep_wisp', 'aspirator'
  );

  //初期配役抑制役職
  public static $disable_cast = array(
    'febris', 'chill_febris', 'frostbite', 'death_warrant', 'panelist', 'thorn_cross', 'infatuated',
    'cute_camouflage', 'confession', 'day_voter', 'wirepuller_luck', 'occupied_luck', 'tengu_voice',
    'mind_read', 'mind_receiver', 'mind_friend', 'mind_sympathy', 'mind_evoke', 'mind_presage',
    'mind_lonely', 'mind_sheep', 'sheep_wisp', 'lovers', 'challenge_lovers', 'vega_lovers',
    'fake_lovers', 'possessed_exchange', 'letter_exchange', 'joker', 'rival', 'enemy', 'supported',
    'death_note', 'death_selected', 'possessed_target', 'possessed', 'infected', 'psycho_infected',
    'bad_status', 'sweet_status', 'protected', 'penetration', 'aspirator', 'levitation',
    'lost_ability', 'muster_ability', 'changed_disguise', 'changed_therian', 'changed_vindictive',
    'copied', 'copied_trick', 'copied_basic', 'copied_nymph', 'copied_soul', 'copied_teller'
  );

  //発言表示
  public static $talk = array('blinder', 'earplug', 'speaker');

  //発言表示 (囁き)
  public static $talk_whisper = array('lovers');

  //発言表示 (妖狐)
  public static $talk_fox = array('wise_wolf', 'wise_ogre');

  //発言表示 (独り言)
  public static $talk_self = array('silver_wolf', 'howl_fox', 'mind_lonely', 'lovers');

  //発言表示 (耳鳴)
  public static $talk_ringing = array('whisper_ringing', 'howl_ringing');

  //閲覧判定
  public static $mind_read = array(
    'leader_common', 'whisper_scanner', 'howl_scanner', 'telepath_scanner', 'minstrel_cupid',
    'mind_read', 'mind_friend', 'mind_open'
  );

  //閲覧判定 (能動型)
  public static $mind_read_active = array('mind_receiver');

  //閲覧判定 (憑依型)
  public static $mind_read_possessed = array('possessed_wolf', 'possessed_mad', 'possessed_fox');

  //発言置換 (仮想 / 順番依存あり)
  public static $say_convert_virtual = array('confession', 'cute_camouflage', 'gentleman', 'lady');

  //発言置換 (本体)
  public static $say_convert = array(
    'suspect', 'cute_mage', 'cute_wolf', 'cute_fox', 'cute_chiroptera', 'cute_avenger'
  );

  //悪戯発言変換
  public static $say_bad_status = array(
    'fairy', 'spring_fairy', 'summer_fairy', 'autumn_fairy', 'winter_fairy', 'greater_fairy'
  );

  //発言変換 (順番依存あり)
  public static $say = array(
    'passion', 'actor', 'liar', 'rainbow', 'weekly', 'grassy', 'invisible', 'side_reverse',
    'line_reverse', 'mower', 'silent'
  );

  //声量
  public static $voice = array(
    'strong_voice', 'normal_voice', 'weak_voice', 'inside_voice', 'outside_voice', 'upper_voice',
    'downer_voice', 'random_voice', 'tengu_voice'
  );

  //霊界遺言登録
  public static $heaven_last_words = array('mind_evoke');

  //死因閲覧
  public static $show_reason = array('yama_necromancer');

  //蘇生失敗閲覧
  public static $show_revive_failed = array('attempt_necromancer', 'vajra_yaksa');

  //人狼襲撃失敗閲覧
  public static $show_wolf_failed = array('eye_scanner', 'eye_wolf');

  //処刑投票 (メイン)
  public static $vote_do_main = array(
    'human', 'elder', 'scripter', 'eccentricer', 'elder_guard', 'critical_common', 'ascetic_wolf',
    'elder_wolf', 'possessed_mad', 'elder_fox', 'elder_chiroptera', 'critical_duelist',
    'cowboy_duelist'
  );

  //処刑投票 (サブ)
  public static $vote_do_sub = array(
    'authority', 'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter',
    'day_voter', 'wirepuller_luck', 'watcher', 'panelist', 'vega_lovers'
  );

  //処刑得票 (メイン)
  public static $vote_poll_main = array('critical_common', 'critical_patron');

  //処刑得票 (サブ)
  public static $vote_poll_sub = array(
    'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck', 'random_luck',
    'occupied_luck', 'wirepuller_luck', 'vega_lovers'
  );

  //処刑投票魔法
  public static $vote_kill_wizard = array('philosophy_wizard');

  //処刑投票能力者 (メイン)
  public static $vote_kill_main = array(
    'saint', 'executor', 'bacchus_medium', 'seal_medium', 'trap_common', 'spell_common',
    'pharmacist', 'cure_pharmacist', 'revive_pharmacist', 'alchemy_pharmacist',
    'centaurus_pharmacist', 'jealousy', 'divorce_jealousy', 'miasma_jealousy', 'critical_jealousy',
    'thunder_brownie', 'harvest_brownie', 'maple_brownie', 'cursed_brownie',  'disguise_wolf',
    'purple_wolf', 'snow_wolf', 'corpse_courier_mad', 'amaze_mad', 'agitate_mad', 'miasma_mad',
    'critical_mad', 'fire_mad', 'follow_mad', 'purple_fox', 'snow_fox', 'critical_fox',
    'fire_depraver', 'sweet_cupid', 'snow_cupid', 'quiz', 'step_vampire', 'cowboy_duelist',
    'sea_duelist', 'cursed_avenger', 'critical_avenger'
  );

  //処刑投票能力者 (サブ)
  public static $vote_kill_sub = array(
    'impatience', 'infatuated', 'decide', 'plague', 'counter_decide', 'dropout', 'good_luck',
    'bad_luck', 'authority', 'rebel', 'vega_lovers'
  );

  //処刑投票補正能力者
  public static $vote_kill_correct = array('cowboy_duelist', 'rebel');

  //処刑者決定 (順番依存あり)
  public static $decide_vote_kill = array(
    'decide', 'bad_luck', 'counter_decide', 'dropout', 'impatience', 'vega_lovers', 'good_luck',
    'plague', 'quiz', 'executor', 'saint', 'agitate_mad'
  );

  //毒能力鑑定
  public static $distinguish_poison = array('pharmacist', 'alchemy_pharmacist');

  //解毒判定
  public static $detox = array('pharmacist', 'cure_pharmacist', 'alchemy_pharmacist');

  //処刑抗毒
  public static $resist_vote_kill_poison = array('resist_wolf');

  //連毒
  public static $chain_poison = array('chain_poison');

  //処刑者カウンター
  public static $vote_kill_counter = array(
    'brownie', 'sun_brownie', 'doom_doll', 'miasma_fox', 'follow_vampire', 'mirror_fairy'
  );

  //処刑投票能力処理 (順番依存あり)
  public static $vote_kill_action = array(
    'seal_medium', 'bacchus_medium', 'cowboy_duelist', 'sea_duelist', 'centaurus_pharmacist',
    'spell_common', 'miasma_jealousy', 'critical_jealousy', 'corpse_courier_mad', 'amaze_mad',
    'miasma_mad', 'fire_mad', 'fire_depraver', 'critical_mad', 'critical_fox', 'critical_avenger',
    'purple_wolf', 'purple_fox', 'cursed_avenger', 'sweet_cupid', 'snow_cupid', 'step_vampire',
    'disguise_wolf'
  );

  //霊能
  public static $necromancer = array(
    'necromancer', 'soul_necromancer', 'psycho_necromancer', 'embalm_necromancer',
    'emissary_necromancer', 'dummy_necromancer', 'monk_fox'
  );

  //霊能魔法
  public static $necromancer_wizard = array('mimic_wizard', 'spiritism_wizard');

  //処刑得票カウンター
  public static $vote_poll_reaction = array('trap_common', 'jealousy');

  //落雷判定
  public static $thunderbolt = array('thunder_brownie');

  //ショック死 (メイン)
  public static $sudden_death_main = array('eclipse_medium', 'cursed_angel', 'doom_chiroptera');

  //ショック死 (サブ / 順番依存あり)
  public static $sudden_death_sub = array(
    'challenge_lovers', 'febris', 'chill_febris', 'frostbite', 'death_warrant', 'panelist',
    'thorn_cross', 'chicken', 'rabbit', 'perverseness', 'flattery', 'celibacy', 'nervy',
    'androphobia', 'gynophobia', 'impatience'
  );

  //ショック死抑制
  public static $cure = array('cure_pharmacist', 'revive_pharmacist');

  //処刑道連れ
  public static $vote_kill_followed = array('follow_mad');

  //処刑後得票カウンター
  public static $vote_kill_reaction = array(
    'divorce_jealousy', 'harvest_brownie', 'maple_brownie', 'cursed_brownie', 'snow_wolf',
    'snow_fox'
  );

  //処刑キャンセル
  public static $vote_kill_cancel = array('prince');

  //足音 (コピー型)
  public static $step_copy = array('lute_mania', 'harp_mania');

  //身代わり君人狼襲撃カウンター
  public static $wolf_eat_dummy_boy = array('involve_tengu');

  //人狼襲撃耐性 (順番依存あり)
  public static $wolf_eat_resist = array(
    'challenge_lovers', 'vega_lovers', 'protected', 'sacrifice_angel', 'doom_vampire',
    'sacrifice_patron', 'sacrifice_mania', 'tough', 'fend_guard', 'awake_wizard',
    'ascetic_assassin'
  );

  //人狼襲撃得票カウンター (+ 身代わり能力者)
  public static $wolf_eat_reaction = array(
    'therian_mad', 'immolate_mad', 'sacrifice_common', 'doll_master', 'toy_doll_master',
    'revive_doll_master', 'serve_doll_master', 'sacrifice_fox', 'sacrifice_vampire',
    'boss_chiroptera', 'sacrifice_ogre'
  );

  //人狼襲撃カウンター
  public static $wolf_eat_counter = array(
    'ghost_common', 'presage_scanner', 'cursed_brownie', 'sun_brownie', 'history_brownie',
    'miasma_fox', 'follow_vampire', 'involve_tengu', 'revive_mania', 'mind_sheep'
  );

  //毒回避判定
  public static $avoid_poison = array('poison_vampire', 'horse_ogre', 'plumage_patron');

  //襲撃毒死回避
  public static $avoid_poison_eat = array('guide_poison', 'poison_jealousy', 'poison_wolf');

  //罠
  public static $trap = array('trap_mad', 'snow_trap_mad');

  //護衛
  public static $guard = array('guard', 'barrier_wizard', 'barrier_brownie');

  //対暗殺護衛
  public static $guard_assassin = array('gatekeeper_guard');

  //対夢食い護衛
  public static $guard_dream = array('dummy_guard');

  //厄払い
  public static $guard_curse = array('anti_voodoo');

  //護衛後処理
  public static $guard_finish_action = array('serve_doll_master');

  //呪殺身代わり
  public static $sacrifice_mage = array('sacrifice_depraver');

  //占い判定妨害 (順番依存あり)
  public static $jammer_mage_result = array(
    'sheep_wisp', 'wisp', 'tengu_spell_wisp', 'foughten_wisp', 'black_wisp'
  );

  //復活
  public static $resurrect = array(
    'revive_pharmacist', 'revive_brownie', 'revive_doll', 'revive_mad', 'revive_cupid',
    'scarlet_vampire', 'revive_ogre', 'revive_avenger', 'revive_mania', 'resurrect_mania'
  );

  //天人帰還
  public static $priest_return = array('revive_priest');

  //恋人抽選
  public static $lottery_lovers = array('altair_cupid', 'letter_cupid', 'exchange_angel');

  //時間差コピー
  public static $delay_copy = array('soul_mania', 'dummy_mania');

  //霊能 (夜発動型)
  public static $necromancer_night = array('attempt_necromancer');

  //人狼襲撃失敗カウンター
  public static $wolf_eat_failed_counter = array('wanderer_guard');

  //死者妨害
  public static $grave = array('grave_mad');

  //蘇生キャンセル
  public static $revive_cancel = array('doom_cat');

  //蘇生制限判定
  public static $limited_revive = array('detective_common', 'scarlet_vampire', 'resurrect_mania');

  //憑依能力者判定
  public static $possessed_group = array('possessed_wolf', 'possessed_mad', 'possessed_fox');

  //憑依制限判定
  public static $limited_possessed = array(
    'detective_common', 'revive_priest', 'revive_pharmacist', 'revive_brownie', 'revive_doll',
    'revive_wolf', 'revive_mad', 'revive_cupid', 'scarlet_vampire', 'revive_ogre',
    'revive_avenger', 'resurrect_mania', 'revive_mania'
  );

  //暗殺反射判定 (常時反射)
  public static $reflect_assassin = array(
    'reflect_guard', 'detective_common', 'cursed_fox', 'soul_vampire'
  );

  //遺言制限判定
  public static $limited_last_words = array(
    'reporter', 'soul_assassin', 'evoke_scanner', 'no_last_words'
  );

  //遺言保存制限判定
  public static $limited_save_last_words = array('possessed_exchange', 'letter_exchange');

  //恋人系判定
  public static $lovers = array('lovers', 'fake_lovers');

  //特殊勝敗判定 (ジョーカー系)
  public static $joker = array('joker', 'rival');

  //特殊暗殺 (デスノート系)
  public static $death_note = array('death_note');
}
