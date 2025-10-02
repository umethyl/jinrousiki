<?php
//-- 役職データベース (サブ役職) --//
class RoleGroupSubData {
  /* 役職グループ */
  //サブ役職のグループリスト (CSS のクラス名 => 所属役職)
  //このリストの表示順に PlayerList の役職が表示される
  public static $list = array(
    'lovers'       => array('lovers', 'challenge_lovers', 'vega_lovers', 'fake_lovers',
			    'possessed_exchange', 'letter_exchange'),
    'duelist'      => array('joker', 'rival', 'enemy', 'supported'),
    'mind'         => array('mind_read', 'mind_open', 'mind_receiver', 'mind_friend',
			    'mind_sympathy', 'mind_evoke', 'mind_presage', 'mind_lonely',
			    'mind_sheep'),
    'vampire'      => array('infected', 'psycho_infected'),
    'sudden-death' => array('chicken', 'rabbit', 'perverseness', 'flattery', 'celibacy', 'nervy',
			    'androphobia', 'gynophobia', 'impatience', 'febris', 'chill_febris',
			    'frostbite', 'death_warrant', 'panelist', 'thorn_cross', 'infatuated'),
    'convert'      => array('liar', 'actor', 'passion', 'rainbow', 'weekly', 'grassy', 'invisible',
			    'side_reverse', 'line_reverse', 'gentleman', 'lady', 'cute_camouflage',
			    'confession'),
    'decide'       => array('decide', 'plague', 'counter_decide', 'dropout', 'good_luck',
			    'bad_luck'),
    'authority'    => array('authority', 'reduce_voter', 'upper_voter', 'downer_voter',
			    'critical_voter', 'rebel', 'random_voter', 'watcher', 'day_voter',
			    'wirepuller_luck'),
    'luck'         => array('upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck',
			    'random_luck', 'occupied_luck'),
    'voice'        => array('strong_voice', 'normal_voice', 'weak_voice', 'upper_voice',
			    'downer_voice', 'inside_voice', 'outside_voice', 'random_voice',
			    'tengu_voice'),
    'seal'         => array('no_last_words', 'blinder', 'earplug', 'speaker', 'whisper_ringing',
			    'howl_ringing', 'sweet_ringing', 'deep_sleep', 'silent', 'mower'),
    'wisp'         => array('wisp', 'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp',
			    'tengu_spell_wisp', 'sheep_wisp'),
    'assassin'     => array('death_note', 'death_selected'),
    'chiroptera'   => array('bad_status', 'sweet_status'),
    'guard'        => array('protected', 'penetration'),
    'poison'       => array('aspirator'),
    'step'         => array('levitation'),
    'human'        => array('lost_ability', 'muster_ability'),
    'wolf'         => array('possessed_target', 'possessed', 'changed_disguise', 'changed_therian'),
    'fox'          => array('changed_vindictive'),
    'mania'        => array('copied', 'copied_trick', 'copied_basic', 'copied_nymph', 'copied_soul',
			    'copied_teller')
  );
}
