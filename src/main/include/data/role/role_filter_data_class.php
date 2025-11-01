<?php
//-- 役職フィルタデータベース --//
final class RoleFilterData {
  //常時表示サブ役職 (本体 / 順番依存あり)
  public static $display_real = [
    'copied', 'copied_trick', 'copied_basic', 'copied_nymph', 'copied_soul', 'copied_teller',
    'lost_ability', 'muster_ability', 'lovers', 'sweet_status', 'challenge_lovers', 'vega_lovers',
    'fake_lovers', 'possessed_exchange', 'letter_exchange', 'joker', 'rival', 'death_note'
  ];

  //常時表示サブ役職 (仮想 / 順番依存あり)
  public static $display_virtual = [
    'death_selected', 'febris', 'chill_febris', 'frostbite', 'death_warrant', 'thorn_cross',
    'infatuated', 'day_voter', 'wirepuller_luck', 'occupied_luck', 'tengu_voice', 'mind_open',
    'mind_read', 'mind_evoke', 'mind_lonely', 'mind_receiver', 'mind_friend', 'mind_sympathy',
    'mind_sheep', 'mind_presage', 'wisp', 'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp',
    'tengu_spell_wisp', 'sheep_wisp', 'male_status', 'female_status', 'gender_status', 'aspirator'
  ];

  //初期配役抑制役職
  public static $disable_cast = [
    'febris', 'chill_febris', 'frostbite', 'death_warrant', 'panelist', 'thorn_cross', 'infatuated',
    'cute_camouflage', 'confession', 'day_voter', 'wirepuller_luck', 'occupied_luck', 'tengu_voice',
    'mind_read', 'mind_receiver', 'mind_friend', 'mind_sympathy', 'mind_evoke', 'mind_presage',
    'mind_lonely', 'mind_sheep', 'sheep_wisp', 'lovers', 'challenge_lovers', 'vega_lovers',
    'fake_lovers', 'possessed_exchange', 'letter_exchange', 'joker', 'rival', 'enemy', 'supported',
    'death_note', 'death_selected', 'possessed_target', 'possessed', 'infected', 'psycho_infected',
    'bad_status', 'sweet_status', 'male_status', 'female_status', 'gender_status', 'protected',
    'penetration', 'aspirator', 'levitation', 'lost_ability', 'muster_ability', 'changed_disguise',
    'changed_therian', 'changed_vindictive', 'copied', 'copied_trick', 'copied_basic',
    'copied_nymph', 'copied_soul', 'copied_teller'
  ];

  //発言表示
  public static $talk = ['blinder', 'earplug', 'speaker'];

  //発言表示 (囁き)
  public static $talk_whisper = ['lovers'];

  //発言表示 (妖狐)
  public static $talk_fox = ['wise_wolf', 'wise_ogre'];

  //発言表示 (独り言)
  public static $talk_self = ['silver_wolf', 'howl_fox', 'mind_lonely', 'lovers'];

  //発言表示 (耳鳴)
  public static $talk_ringing = ['whisper_ringing', 'howl_ringing'];

  //発言表示 (恋耳鳴)/メイン役職
  public static $talk_sweet_ringing = ['sweet_wolf', 'sweet_fox'];

  //閲覧判定
  public static $mind_read = [
    'leader_common', 'whisper_scanner', 'howl_scanner', 'telepath_scanner', 'minstrel_cupid',
    'mind_read', 'mind_friend', 'mind_open'
  ];

  //閲覧判定 (能動型)
  public static $mind_read_active = ['mind_receiver'];

  //閲覧判定 (憑依型)
  public static $mind_read_possessed = ['possessed_wolf', 'possessed_mad', 'possessed_fox'];

  //発言置換 (仮想 / 順番依存あり)
  public static $say_convert_virtual = ['confession', 'cute_camouflage', 'gentleman', 'lady'];

  //発言置換 (本体)
  public static $say_convert = [
    'suspect', 'cute_mage', 'cute_wolf', 'cute_fox', 'cute_chiroptera', 'cute_avenger'
  ];

  //悪戯発言変換
  public static $say_bad_status = [
    'fairy', 'spring_fairy', 'summer_fairy', 'autumn_fairy', 'winter_fairy', 'greater_fairy'
  ];

  //発言変換 (順番依存あり)
  public static $say = [
    'passion', 'actor', 'liar', 'rainbow', 'weekly', 'grassy', 'invisible', 'side_reverse',
    'line_reverse', 'mower', 'silent'
  ];

  //声量
  public static $voice = [
    'strong_voice', 'normal_voice', 'weak_voice', 'inside_voice', 'outside_voice', 'upper_voice',
    'downer_voice', 'random_voice', 'tengu_voice'
  ];

  //霊界遺言登録
  public static $heaven_last_words = ['mind_evoke'];

  //死因閲覧
  public static $show_reason = ['yama_necromancer'];

  //蘇生失敗閲覧
  public static $show_revive_failed = ['attempt_necromancer', 'vajra_yaksa'];

  //人狼襲撃失敗閲覧
  public static $show_wolf_failed = ['eye_scanner', 'eye_wolf'];

  //処刑投票 (メイン)
  public static $vote_do_main = [
    'human', 'elder', 'scripter', 'eccentricer', 'elder_guard', 'critical_common', 'ascetic_wolf',
    'elder_wolf', 'possessed_mad', 'elder_fox', 'elder_chiroptera', 'critical_duelist',
    'cowboy_duelist'
  ];

  //処刑投票 (サブ)
  public static $vote_do_sub = [
    'authority', 'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter',
    'day_voter', 'wirepuller_luck', 'watcher', 'panelist', 'vega_lovers'
  ];

  //処刑得票 (メイン)
  public static $vote_poll_main = ['critical_common', 'critical_patron'];

  //処刑得票 (サブ)
  public static $vote_poll_sub = [
    'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck', 'random_luck',
    'occupied_luck', 'wirepuller_luck', 'vega_lovers'
  ];

  //処刑投票魔法
  public static $vote_kill_wizard = ['philosophy_wizard'];

  //処刑投票能力者 (メイン)
  public static $vote_kill_main = [
    'saint', 'executor', 'bacchus_medium', 'seal_medium', 'trap_common', 'spell_common',
    'pharmacist', 'cure_pharmacist', 'revive_pharmacist', 'alchemy_pharmacist',
    'centaurus_pharmacist', 'jealousy', 'divorce_jealousy', 'miasma_jealousy', 'critical_jealousy',
    'thunder_brownie', 'harvest_brownie', 'maple_brownie', 'cursed_brownie',  'disguise_wolf',
    'purple_wolf', 'snow_wolf', 'miasma_wolf', 'homogeneous_wolf', 'heterologous_wolf',
    'corpse_courier_mad', 'amaze_mad', 'agitate_mad', 'miasma_mad', 'critical_mad', 'fire_mad',
    'follow_mad', 'purple_fox', 'snow_fox', 'critical_fox', 'fire_depraver', 'sacrifice_cupid',
    'sweet_cupid', 'snow_cupid', 'quiz', 'step_vampire', 'cowboy_duelist', 'sea_duelist',
    'cursed_avenger', 'critical_avenger'
  ];

  //処刑投票能力者 (サブ)
  public static $vote_kill_sub = [
    'impatience', 'infatuated', 'decide', 'plague', 'counter_decide', 'dropout', 'good_luck',
    'bad_luck', 'authority', 'rebel', 'vega_lovers'
  ];

  //処刑投票補正能力者
  public static $vote_kill_correct = ['cowboy_duelist', 'rebel'];

  //処刑者決定 (順番依存あり)
  public static $decide_vote_kill = [
    'decide', 'bad_luck', 'counter_decide', 'dropout', 'impatience', 'vega_lovers', 'good_luck',
    'plague', 'quiz', 'executor', 'saint', 'agitate_mad'
  ];

  //毒能力鑑定
  public static $distinguish_poison = ['pharmacist', 'alchemy_pharmacist'];

  //解毒判定
  public static $detox = ['pharmacist', 'cure_pharmacist', 'alchemy_pharmacist'];

  //処刑抗毒
  public static $resist_vote_kill_poison = ['resist_wolf'];

  //連毒
  public static $chain_poison = ['chain_poison'];

  //処刑者カウンター
  public static $vote_kill_counter = [
    'brownie', 'sun_brownie', 'doom_doll', 'miasma_fox', 'follow_vampire', 'mirror_fairy'
  ];

  //処刑投票能力処理 (順番依存あり)
  public static $vote_kill_action = [
    'seal_medium', 'bacchus_medium', 'cowboy_duelist', 'sea_duelist', 'centaurus_pharmacist',
    'spell_common', 'miasma_jealousy', 'critical_jealousy', 'corpse_courier_mad', 'amaze_mad',
    'miasma_mad', 'fire_mad', 'fire_depraver', 'critical_mad', 'critical_fox', 'critical_avenger',
    'purple_wolf', 'purple_fox', 'cursed_avenger', 'sacrifice_cupid', 'sweet_cupid', 'snow_cupid',
    'step_vampire', 'disguise_wolf'
  ];

  //霊能
  public static $necromancer = [
    'necromancer', 'soul_necromancer', 'psycho_necromancer', 'embalm_necromancer',
    'emissary_necromancer', 'dummy_necromancer', 'monk_fox'
  ];

  //霊能魔法
  public static $necromancer_wizard = ['mimic_wizard', 'spiritism_wizard'];

  //処刑得票カウンター
  public static $vote_poll_reaction = ['trap_common', 'jealousy'];

  //落雷判定
  public static $thunderbolt = ['thunder_brownie'];

  //ショック死 (メイン)
  public static $sudden_death_main = ['eclipse_medium', 'cursed_angel', 'doom_chiroptera'];

  //ショック死 (サブ / 順番依存あり)
  public static $sudden_death_sub = [
    'challenge_lovers', 'febris', 'chill_febris', 'frostbite', 'death_warrant', 'panelist',
    'thorn_cross', 'chicken', 'critical_chicken', 'rabbit', 'perverseness', 'flattery',
    'celibacy', 'nervy', 'androphobia', 'gynophobia', 'impatience'
  ];

  //ショック死抑制
  public static $cure = ['cure_pharmacist', 'revive_pharmacist'];

  //処刑道連れ
  public static $vote_kill_followed = ['follow_mad'];

  //処刑後得票カウンター
  public static $vote_kill_reaction = [
    'divorce_jealousy', 'harvest_brownie', 'maple_brownie', 'cursed_brownie', 'snow_wolf',
    'miasma_wolf', 'homogeneous_wolf', 'heterologous_wolf', 'snow_fox'
  ];

  //処刑キャンセル
  public static $vote_kill_cancel = ['prince'];

  //足音 (コピー型)
  public static $step_copy = ['lute_mania', 'harp_mania'];

  //身代わり君人狼襲撃カウンター
  public static $wolf_eat_dummy_boy = ['involve_tengu'];

  //人狼襲撃耐性 (順番依存あり)
  public static $wolf_eat_resist = [
    'challenge_lovers', 'vega_lovers', 'protected', 'sacrifice_angel', 'doom_vampire',
    'sacrifice_patron', 'sacrifice_mania', 'tough', 'fend_guard', 'awake_wizard',
    'ascetic_assassin'
  ];

  //人狼襲撃得票カウンター (+ 身代わり能力者)
  public static $wolf_eat_reaction = [
    'therian_mad', 'immolate_mad', 'sacrifice_common', 'doll_master', 'toy_doll_master',
    'revive_doll_master', 'serve_doll_master', 'sacrifice_fox', 'sacrifice_cupid',
    'sacrifice_vampire', 'boss_chiroptera', 'sacrifice_ogre'
  ];

  //人狼襲撃カウンター
  public static $wolf_eat_counter = [
    'ghost_common', 'presage_scanner', 'cursed_brownie', 'sun_brownie', 'history_brownie',
    'miasma_fox', 'follow_vampire', 'involve_tengu', 'revive_mania', 'mind_sheep'
  ];

  //毒回避判定
  public static $avoid_poison = ['poison_vampire', 'horse_ogre', 'plumage_patron'];

  //襲撃毒死回避
  public static $avoid_poison_eat = ['guide_poison', 'poison_jealousy', 'poison_wolf'];

  //罠
  public static $trap = ['trap_mad', 'snow_trap_mad'];

  //護衛
  public static $guard = ['guard', 'barrier_wizard', 'barrier_brownie'];

  //対暗殺護衛
  public static $guard_assassin = ['gatekeeper_guard'];

  //対夢食い護衛
  public static $guard_dream = ['dummy_guard'];

  //厄払い
  public static $guard_curse = ['anti_voodoo'];

  //護衛後処理
  public static $guard_finish_action = ['serve_doll_master'];

  //呪殺身代わり
  public static $sacrifice_mage = ['sacrifice_depraver'];

  //占い判定妨害 (順番依存あり)
  public static $jammer_mage_result = [
    'sheep_wisp', 'wisp', 'tengu_spell_wisp', 'foughten_wisp', 'black_wisp'
  ];

  //透視/範囲投票
  public static $scan_plural = ['plural_wizard', 'barrier_wizard', 'plural_mad'];

  //透視/足音 (本人起点型)
  public static $scan_step_chain = ['step_mage', 'step_guard', 'step_wolf', 'step_vampire'];

  //透視/足音 (直線型)
  public static $scan_step_line = ['step_assassin', 'step_scanner', 'step_mad', 'step_fox'];

  //復活
  public static $resurrect = [
    'revive_pharmacist', 'revive_brownie', 'revive_doll', 'revive_mad', 'revive_cupid',
    'scarlet_vampire', 'revive_ogre', 'revive_avenger', 'revive_mania', 'resurrect_mania'
  ];

  //天人帰還
  public static $priest_return = ['revive_priest'];

  //恋人抽選
  public static $lottery_lovers = ['altair_cupid', 'letter_cupid', 'exchange_angel'];

  //時間差コピー
  public static $delay_copy = ['soul_mania', 'dummy_mania'];

  //霊能 (夜発動型)
  public static $necromancer_night = ['attempt_necromancer'];

  //人狼襲撃失敗カウンター
  public static $wolf_eat_failed_counter = ['wanderer_guard'];

  //死者妨害
  public static $grave = ['grave_mad'];

  //蘇生キャンセル
  public static $revive_cancel = ['doom_cat'];

  //蘇生制限判定
  public static $limited_revive = ['detective_common', 'scarlet_vampire', 'resurrect_mania'];

  //憑依能力者判定
  public static $possessed_group = ['possessed_wolf', 'possessed_mad', 'possessed_fox'];

  //憑依制限判定
  public static $limited_possessed = [
    'detective_common', 'revive_priest', 'revive_pharmacist', 'revive_brownie', 'revive_doll',
    'revive_wolf', 'revive_mad', 'revive_cupid', 'scarlet_vampire', 'revive_ogre',
    'revive_avenger', 'resurrect_mania', 'revive_mania'
  ];

  //暗殺反射判定 (常時反射)
  public static $reflect_assassin = [
    'reflect_guard', 'detective_common', 'cursed_fox', 'soul_vampire'
  ];

  //遺言制限判定
  public static $limited_last_words = [
    'reporter', 'soul_assassin', 'evoke_scanner', 'no_last_words'
  ];

  //遺言登録制限判定
  public static $limited_store_last_words = ['possessed_exchange', 'letter_exchange'];

  //恋人系判定
  public static $lovers = ['lovers', 'fake_lovers'];

  //特殊勝敗判定 (ジョーカー系)
  public static $joker = ['joker', 'rival'];

  //特殊暗殺 (デスノート系)
  public static $death_note = ['death_note'];

  //性別判定 (順番依存あり)
  public static $gender_status = ['male_status', 'female_status', 'gender_status'];

  //覚醒コピー変換リスト
  public static $soul_delay_copy = [
    CampGroup::HUMAN		=> 'executor',
    CampGroup::MAGE		=> 'soul_mage',
    CampGroup::NECROMANCER	=> 'soul_necromancer',
    CampGroup::MEDIUM		=> 'revive_medium',
    CampGroup::PRIEST		=> 'high_priest',
    CampGroup::GUARD		=> 'poison_guard',
    CampGroup::COMMON		=> 'ghost_common',
    CampGroup::POISON		=> 'strong_poison',
    CampGroup::POISON_CAT	=> 'revive_cat',
    CampGroup::PHARMACIST	=> 'alchemy_pharmacist',
    CampGroup::ASSASSIN		=> 'soul_assassin',
    CampGroup::MIND_SCANNER	=> 'clairvoyance_scanner',
    CampGroup::JEALOUSY		=> 'flower_jealousy',
    CampGroup::BROWNIE		=> 'barrier_brownie',
    CampGroup::WIZARD		=> 'soul_wizard',
    CampGroup::DOLL		=> 'serve_doll_master',
    CampGroup::ESCAPER		=> 'divine_escaper',
    CampGroup::WOLF		=> 'sirius_wolf',
    CampGroup::MAD		=> 'whisper_mad',
    CampGroup::FOX		=> 'cursed_fox',
    CampGroup::CHILD_FOX	=> 'jammer_fox',
    CampGroup::DEPRAVER		=> 'sacrifice_depraver',
    CampGroup::CUPID		=> 'minstrel_cupid',
    CampGroup::ANGEL		=> 'sacrifice_angel',
    CampGroup::QUIZ		=> 'quiz',
    CampGroup::VAMPIRE		=> 'soul_vampire',
    CampGroup::CHIROPTERA	=> 'boss_chiroptera',
    CampGroup::FAIRY		=> 'ice_fairy',
    CampGroup::OGRE		=> 'sacrifice_ogre',
    CampGroup::YAKSA		=> 'dowser_yaksa',
    CampGroup::DUELIST		=> 'critical_duelist',
    CampGroup::AVENGER		=> 'revive_avenger',
    CampGroup::PATRON		=> 'sacrifice_patron',
    CampGroup::TENGU		=> 'soul_tengu'
  ];
}
