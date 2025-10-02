<?php
//-- 役職コントローラークラス --//
class RoleManager {
  const PATH = '%s/role/%s.php';
  static $file  = array(); //ロード済みファイル
  static $class = array(); //ロード済みクラス
  static $actor; //対象ユーザ
  static $get;   //スタックデータ

  //常時表示サブ役職 (本体 / 順番依存あり)
  static $display_real_list = array(
    'copied', 'copied_trick', 'copied_basic', 'copied_soul', 'copied_teller', 'lost_ability',
    'muster_ability', 'lovers', 'sweet_status', 'challenge_lovers', 'possessed_exchange', 'joker',
    'rival', 'death_note');

  //常時表示サブ役職 (仮想 / 順番依存あり)
  static $display_virtual_list = array(
    'death_selected', 'febris', 'frostbite', 'death_warrant', 'day_voter', 'wirepuller_luck',
    'occupied_luck', 'mind_open', 'mind_read', 'mind_evoke', 'mind_lonely', 'mind_receiver',
    'mind_friend', 'mind_sympathy', 'mind_sheep', 'mind_presage', 'wisp', 'black_wisp',
    'spell_wisp', 'foughten_wisp', 'gold_wisp', 'sheep_wisp');

  //非表示サブ役職 (呼び出し抑制用)
  static $display_none_list = array(
    'decide', 'plague', 'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'critical_voter',
    'critical_luck', 'confession', 'enemy', 'supported', 'infected', 'psycho_infected',
    'possessed_target', 'possessed', 'bad_status', 'protected', 'changed_disguise',
    'changed_therian', 'changed_vindictive');

  //初期配役抑制役職
  static $disable_cast_list = array(
    'febris', 'frostbite', 'death_warrant', 'panelist', 'cute_camouflage', 'confession',
    'day_voter', 'wirepuller_luck', 'occupied_luck', 'mind_read', 'mind_receiver', 'mind_friend',
    'mind_sympathy', 'mind_evoke', 'mind_presage', 'mind_lonely', 'mind_sheep', 'sheep_wisp',
    'lovers', 'challenge_lovers', 'possessed_exchange', 'joker', 'rival', 'enemy', 'supported',
    'death_note', 'death_selected', 'possessed_target', 'possessed', 'infected', 'psycho_infected',
    'bad_status', 'sweet_status', 'protected', 'lost_ability', 'muster_ability', 'changed_disguise',
    'changed_therian', 'changed_vindictive', 'copied', 'copied_trick', 'copied_basic',
    'copied_soul', 'copied_teller');

  //発言表示
  static $talk_list = array('blinder', 'earplug', 'speaker');

  //発言表示 (囁き)
  static $talk_whisper_list = array('lovers');

  //発言表示 (妖狐)
  static $talk_fox_list = array('wise_wolf', 'wise_ogre');

  //発言表示 (独り言)
  static $talk_self_list = array('silver_wolf', 'howl_fox', 'mind_lonely', 'lovers');

  //発言表示 (耳鳴)
  static $talk_ringing_list = array('whisper_ringing', 'howl_ringing');

  //閲覧判定
  static $mind_read_list = array(
    'leader_common', 'whisper_scanner', 'howl_scanner', 'telepath_scanner', 'minstrel_cupid',
    'mind_read', 'mind_friend', 'mind_open');

  //閲覧判定 (能動型)
  static $mind_read_active_list = array('mind_receiver');

  //閲覧判定 (憑依型)
  static $mind_read_possessed_list = array('possessed_wolf', 'possessed_mad', 'possessed_fox');

  //発言置換 (仮想 / 順番依存あり)
  static $say_convert_virtual_list = array('confession', 'cute_camouflage', 'gentleman', 'lady');

  //発言置換 (本体)
  static $say_convert_list = array('suspect', 'cute_mage', 'cute_wolf', 'cute_fox',
				   'cute_chiroptera', 'cute_avenger');

  //悪戯発言変換
  static $say_bad_status_list = array('fairy', 'spring_fairy', 'summer_fairy', 'autumn_fairy',
				      'winter_fairy', 'greater_fairy');

  //発言変換 (順番依存あり)
  static $say_list = array('passion', 'actor', 'liar', 'rainbow', 'weekly', 'grassy', 'invisible',
			   'side_reverse', 'line_reverse', 'mower', 'silent');

  //声量
  static $voice_list = array('strong_voice', 'normal_voice', 'weak_voice', 'inside_voice',
			     'outside_voice', 'upper_voice', 'downer_voice', 'random_voice');

  //処刑投票 (メイン)
  static $vote_do_main_list = array(
    'human', 'elder', 'scripter', 'eccentricer', 'elder_guard', 'critical_common', 'ascetic_wolf',
    'elder_wolf', 'possessed_mad', 'elder_fox', 'elder_chiroptera', 'critical_duelist',
    'cowboy_duelist');

  //処刑投票 (サブ)
  static $vote_do_sub_list = array(
    'authority', 'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter',
    'day_voter', 'wirepuller_luck', 'watcher', 'panelist');

  //処刑得票 (メイン)
  static $vote_poll_main_list = array('critical_common', 'critical_patron');

  //処刑得票 (サブ)
  static $vote_poll_sub_list = array(
    'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck', 'random_luck',
    'occupied_luck', 'wirepuller_luck');

  //処刑投票能力者
  static $vote_day_list = array(
    'saint', 'executor', 'bacchus_medium', 'seal_medium', 'trap_common', 'spell_common',
    'pharmacist', 'cure_pharmacist', 'revive_pharmacist', 'alchemy_pharmacist',
    'centaurus_pharmacist', 'jealousy', 'divorce_jealousy', 'miasma_jealousy', 'critical_jealousy',
    'thunder_brownie', 'harvest_brownie', 'maple_brownie', 'cursed_brownie',  'disguise_wolf',
    'purple_wolf', 'snow_wolf', 'corpse_courier_mad', 'amaze_mad', 'agitate_mad', 'miasma_mad',
    'critical_mad', 'follow_mad', 'purple_fox', 'snow_fox', 'critical_fox', 'sweet_cupid',
    'snow_cupid', 'quiz', 'cursed_avenger', 'critical_avenger', 'impatience', 'decide', 'plague',
    'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'authority', 'rebel');

  //反逆者判定
  static $rebel_list = array('rebel');

  //処刑者決定 (順番依存あり)
  static $vote_kill_list = array('decide', 'bad_luck', 'counter_decide', 'dropout', 'impatience',
				 'good_luck', 'plague', 'quiz', 'executor', 'saint', 'agitate_mad');

  //毒能力鑑定
  static $distinguish_poison_list = array('pharmacist', 'alchemy_pharmacist');

  //解毒判定
  static $detox_list = array('pharmacist', 'cure_pharmacist', 'alchemy_pharmacist');

  //処刑者カウンター
  static $vote_kill_counter_list = array('brownie', 'sun_brownie', 'doom_doll', 'miasma_fox',
					 'mirror_fairy');

  //処刑投票能力処理 (順番依存あり)
  static $vote_action_list = array(
    'seal_medium', 'bacchus_medium', 'centaurus_pharmacist', 'spell_common', 'miasma_jealousy',
    'critical_jealousy', 'corpse_courier_mad', 'amaze_mad', 'miasma_mad', 'critical_mad',
    'critical_fox', 'critical_avenger', 'purple_wolf', 'purple_fox', 'cursed_avenger',
    'sweet_cupid', 'snow_cupid', 'disguise_wolf');

  //霊能
  static $necromancer_list = array(
    'necromancer', 'soul_necromancer', 'psycho_necromancer', 'embalm_necromancer',
    'emissary_necromancer', 'dummy_necromancer', 'monk_fox');

  //得票カウンター
  static $voted_reaction_list = array('trap_common', 'jealousy');

  //落雷判定
  static $thunderbolt_list = array('thunder_brownie');

  //ショック死 (メイン)
  static $sudden_death_main_list = array('eclipse_medium', 'cursed_angel');

  //ショック死 (サブ / 順番依存あり)
  static $sudden_death_sub_list = array(
    'challenge_lovers', 'febris', 'frostbite', 'death_warrant', 'panelist', 'chicken', 'rabbit',
    'perverseness', 'flattery', 'celibacy', 'nervy', 'androphobia', 'gynophobia', 'impatience');

  //ショック死抑制
  static $cure_list = array('cure_pharmacist', 'revive_pharmacist');

  //処刑得票カウンター
  static $vote_kill_reaction_list = array('divorce_jealousy', 'harvest_brownie', 'maple_brownie',
					  'cursed_brownie', 'snow_wolf', 'snow_fox');

  //道連れ
  static $followed_list = array('follow_mad');

  //人狼襲撃耐性 (順番依存あり)
  static $wolf_eat_resist_list = array(
    'challenge_lovers', 'protected', 'sacrifice_angel', 'doom_vampire', 'sacrifice_patron',
    'sacrifice_mania', 'fend_guard', 'ascetic_assassin', 'awake_wizard');

  //人狼襲撃得票カウンター (+ 身代わり能力者)
  static $wolf_eat_reaction_list = array(
    'therian_mad', 'immolate_mad', 'sacrifice_common', 'doll_master', 'sacrifice_fox',
    'sacrifice_vampire', 'boss_chiroptera', 'sacrifice_ogre');

  //人狼襲撃カウンター
  static $wolf_eat_counter_list = array(
    'ghost_common', 'presage_scanner', 'cursed_brownie', 'sun_brownie', 'history_brownie',
    'miasma_fox', 'revive_mania', 'mind_sheep');

  //襲撃毒死回避
  static $avoid_poison_eat_list = array('guide_poison', 'poison_jealousy', 'poison_wolf');

  //罠
  static $trap_list = array('trap_mad', 'snow_trap_mad');

  //護衛
  static $guard_list = array('guard', 'barrier_wizard');

  //対暗殺護衛
  static $guard_assassin_list = array('gatekeeper_guard');

  //対夢護衛
  static $guard_dream_list = array('dummy_guard');

  //厄払い
  static $guard_curse_list = array('anti_voodoo');

  //復活
  static $resurrect_list = array(
    'revive_pharmacist', 'revive_brownie', 'revive_doll', 'revive_mad', 'revive_cupid',
    'scarlet_vampire', 'revive_ogre', 'revive_avenger', 'resurrect_mania');

  //イベントセット
  static $event_virtual_list = array('no_last_words', 'whisper_ringing', 'howl_ringing',
				     'sweet_ringing', 'deep_sleep', 'mind_open');

  //イベントセット (昼限定)
  static $event_virtual_day_list = array(
    'actor', 'passion', 'rainbow', 'grassy', 'invisible', 'side_reverse', 'line_reverse',
    'critical_voter', 'critical_luck', 'blinder', 'earplug', 'silent', 'mower');

  //特殊勝敗判定 (ジョーカー系)
  static $joker_list = array('joker', 'rival');

  //フィルタロード
  static function Load($type, $shift = false, $virtual = false) {
    $stack = array();
    $virtual |= $type == 'main_role';
    foreach (self::GetList($type) as $role) {
      if (! ($virtual ? self::$actor->IsRole(true, $role) : self::$actor->IsRole($role))) continue;
      $stack[] = $role;
      if (self::LoadFile($role)) self::LoadClass($role);
    }
    $filter = self::GetFilter($stack);
    return $shift ? array_shift($filter) : $filter;
  }

  //ファイルロード
  static function LoadFile($name) {
    if (is_null($name) || ! file_exists($file = self::GetPath($name))) return false;
    if (in_array($name, self::$file)) return true;
    require_once($file);
    self::$file[] = $name;
    return true;
  }

  //フィルタ用クラスロード
  static function LoadFilter($type) { return self::GetFilter(self::GetList($type)); }

  //メイン役職クラスロード
  static function LoadMain(User $user) {
    self::$actor = $user;
    return self::Load('main_role', true);
  }

  //個別クラスロード (Mixin 用)
  static function LoadMix($name) {
    if (! self::LoadFile($name)) return null;
    $class = 'Role_' . $name;
    return new $class();
  }

  //個別クラス取得 ($actor を参照していない事を確認すること)
  static function GetClass($role) {
    return (self::LoadFile($role) && self::LoadClass($role)) ? self::$class[$role] : null;
  }

  //データ取得
  static function GetStack($name) { return isset(self::$get->$name) ? self::$get->$name : null; }

  //データセット
  static function SetStack($name, $data) {
    self::$get->$name = $data;
  }

  //クラスセット
  static function SetClass($role) { return self::LoadFile($role) && self::LoadClass($role); }

  //クラスのロード済み判定
  private function IsClass($role) {
    return array_key_exists($role, self::$class) && is_object(self::$class[$role]);
  }

  //ファイルパス取得
  private function GetPath($name) { return sprintf(self::PATH, JINRO_INC, $name); }

  //役職リスト取得
  private function GetList($type) {
    $stack = $type == 'main_role' ? array(self::$actor->GetMainRole(true)) :
      self::${$type . '_list'};
    return is_array($stack) ? $stack : array();
  }

  //役職リストに応じたクラスリスト取得
  private function GetFilter(array $list) {
    $stack = array();
    foreach ($list as $role) { //順番依存があるので配列関数を使わないで処理する
      if (self::IsClass($role)) $stack[] = self::$class[$role];
    }
    return $stack;
  }

  //クラスロード
  private function LoadClass($role) {
    if (is_null($role)) return false;
    if (! self::IsClass($role)) {
      $class_name = 'Role_' . $role;
      self::$class[$role] = new $class_name();
    }
    return true;
  }
}

//-- 役職の基底クラス --//
abstract class Role {
  public $role;
  public $action;
  public $not_action;
  public $submit;
  public $not_submit;
  public $ignore_message;

  function __construct() {
    $this->role = array_pop(explode('Role_', get_class($this)));
    if (isset($this->mix_in)) {
      $this->filter = RoleManager::LoadMix($this->mix_in);
      $this->filter->role = $this->role;
      //Text::p(get_class_vars(get_class($this)));
      if (isset($this->display_role)) $this->filter->display_role = $this->display_role;
    }
  }

  //Mixin 呼び出し用
  function __call($name, $args) {
    if (! is_object($this->filter)) {
      Text::p('Error: Mixin not found: ' . get_class($this) . ": {$name}()");
      return false;
    }
    if (! method_exists($this->filter, $name)) {
      Text::p('Error: Method not found: ' . get_class($this) . ": {$name}()");
      return false;
    }
    return call_user_func_array(array($this->filter, $name), $args);
  }

  //メソッド保持クラス取得 (Mixin 用)
  protected function GetClass($method) {
    $class = 'Role_' . $this->role;
    return method_exists($class, $method) ? new $class() : $this;
  }

  //プロパティ取得 (Mixin 用)
  protected function GetProperty($property) {
    $class  = 'Role_' . $this->role;
    $mix_in = new $class();
    return isset($mix_in->$property) ? $mix_in->$property :
      (isset($this->$property) ? $this->$property : null);
  }

  //function __get($name) { return null; } //メモ

  //-- 汎用関数 --//
  //ユーザ取得
  protected function GetActor() { return RoleManager::$actor; }

  //ユーザ ID 取得
  protected function GetID() { return $this->GetActor()->user_no; }

  //ユーザ名取得
  protected function GetUname($uname = null) {
    return is_null($uname) ? $this->GetActor()->uname : $uname;
  }

  //データ初期化
  protected function InitStack($name = null) {
    $data = is_null($name) ? $this->role : $name;
    if (! isset(RoleManager::$get->$data)) RoleManager::$get->$data = array();
  }

  //データ取得
  protected function GetStack($name = null, $fill = false) {
    $stack = RoleManager::GetStack(is_null($name) ? $this->role : $name);
    return isset($stack) ? $stack : ($fill ? array() : null);
  }

  //データセット
  protected function SetStack($data, $role = null) {
    RoleManager::SetStack(is_null($role) ? $this->role : $role, $data);
  }

  //データ追加
  protected function AddStack($data, $role = null, $uname = null) {
    RoleManager::$get->{is_null($role) ? $this->role : $role}[$this->GetUname($uname)] = $data;
  }

  //同一ユーザ判定
  protected function IsActor($uname) { return $this->GetActor()->IsSame($uname); }

  //発動日判定
  protected function IsDoom() {
    return $this->GetActor()->GetDoomDate($this->role) == DB::$ROOM->date;
  }

  //投票能力判定
  function IsVote() { return ! is_null($this->action); }

  //-- 役職情報表示 --//
  //役職情報表示
  function OutputAbility() {
    if ($this->IgnoreAbility()) return;
    $this->OutputImage();
    $this->OutputPartner();
    $this->OutputResult();
    if ($this->IsVote() && DB::$ROOM->IsNight()) $this->OutputAction();
  }

  //役職情報表示判定
  protected function IgnoreAbility() { return false; }

  //役職画像表示
  protected function OutputImage() {
    Image::Role()->Output(isset($this->display_role) ? $this->display_role : $this->role);
  }

  //仲間情報表示
  protected function OutputPartner() {}

  //能力結果表示
  protected function OutputResult() {}

  //能力発動結果表示
  protected function OutputAbilityResult($action) {
    $header = null;
    $footer = 'result_';
    $limit  = false;
    switch ($action) {
    case 'MAGE_RESULT':
    case 'CHILD_FOX_RESULT':
      $type   = 'mage';
      $header = 'mage_result';
      $limit  = true;
      break;

    case 'VOODOO_KILLER_SUCCESS':
      $type   = 'mage';
      $footer = 'voodoo_killer_';
      $limit  = true;
      break;

    case 'NECROMANCER_RESULT':
    case 'SOUL_NECROMANCER_RESULT':
    case 'PSYCHO_NECROMANCER_RESULT':
    case 'EMBALM_NECROMANCER_RESULT':
    case 'ATTEMPT_NECROMANCER_RESULT':
    case 'DUMMY_NECROMANCER_RESULT':
    case 'MIMIC_WIZARD_RESULT':
    case 'SPIRITISM_WIZARD_RESULT':
    case 'MONK_FOX_RESULT':
      $type = 'necromancer';
      break;

    case 'EMISSARY_NECROMANCER_RESULT':
      $type   = 'priest';
      $header = 'emissary_necromancer_header';
      $footer = 'priest_footer';
      break;

    case 'MEDIUM_RESULT':
      $type   = 'necromancer';
      $header = 'medium';
      break;

    case 'PRIEST_RESULT':
    case 'DUMMY_PRIEST_RESULT':
    case 'PRIEST_JEALOUSY_RESULT':
      $type   = 'priest';
      $header = 'priest_header';
      $footer = 'priest_footer';
      break;

    case 'BISHOP_PRIEST_RESULT':
      $type   = 'priest';
      $header = 'bishop_priest_header';
      $footer = 'priest_footer';
      break;

    case 'DOWSER_PRIEST_RESULT':
      $type   = 'priest';
      $header = 'dowser_priest_header';
      $footer = 'dowser_priest_footer';
      break;

    case 'WEATHER_PRIEST_RESULT':
      $type   = 'weather_priest';
      $header = 'weather_priest_header';
      break;

    case 'CRISIS_PRIEST_RESULT':
      $type   = 'crisis_priest';
      $header = 'side_';
      $footer = 'crisis_priest_result';
      break;

    case 'HOLY_PRIEST_RESULT':
      $type   = 'priest';
      $header = 'holy_priest_header';
      $footer = 'dowser_priest_footer';
      $limit  = true;
      break;

    case 'BORDER_PRIEST_RESULT':
      $type   = 'priest';
      $header = 'border_priest_header';
      $footer = 'priest_footer';
      $limit  = true;
      break;

    case 'GUARD_SUCCESS':
    case 'GUARD_HUNTED':
      $type   = 'mage';
      $footer = 'guard_';
      $limit  = true;
      break;

    case 'REPORTER_SUCCESS':
      $type   = 'reporter';
      $header = 'reporter_result_header';
      $footer = 'reporter_result_footer';
      $limit  = true;
      break;

    case 'ANTI_VOODOO_SUCCESS':
      $type   = 'mage';
      $footer = 'anti_voodoo_';
      $limit  = true;
      break;

    case 'POISON_CAT_RESULT':
      $type   = 'mage';
      $footer = 'poison_cat_';
      $limit  = true;
      break;

    case 'PHARMACIST_RESULT':
      $type   = 'mage';
      $footer = 'pharmacist_';
      $limit  = true;
      break;

    case 'ASSASSIN_RESULT':
      $type   = 'mage';
      $header = 'assassin_result';
      $limit  = true;
      break;

    case 'CLAIRVOYANCE_RESULT':
      $type   = 'reporter';
      $header = 'clairvoyance_result_header';
      $footer = 'clairvoyance_result_footer';
      $limit  = true;
      break;

    case 'SEX_WOLF_RESULT':
    case 'SHARP_WOLF_RESULT':
    case 'TONGUE_WOLF_RESULT':
      $type   = 'mage';
      $header = 'wolf_result';
      $limit  = true;
      break;

    case 'FOX_EAT':
      $type   = 'fox';
      $header = 'fox_';
      $limit  = true;
      break;

    case 'VAMPIRE_RESULT':
      $type   = 'mage';
      $header = 'vampire_result';
      $limit  = true;
      break;

    case 'MANIA_RESULT':
    case 'PATRON_RESULT':
      $type  = 'mage';
      $limit = true;
      break;

    case 'SYMPATHY_RESULT':
      $type   = 'mage';
      $header = 'sympathy_result';
      $limit  = ! DB::$SELF->IsRole('ark_angel');
      break;

    case 'PRESAGE_RESULT':
      $type   = 'reporter';
      $header = 'presage_result_header';
      $footer = 'reporter_result_footer';
      $limit  = true;
      break;

    default:
      return false;
    }

    $target_date = DB::$ROOM->date - 1;
    if (DB::$ROOM->test_mode) {
      $stack = RQ::GetTest()->result_ability;
      $stack = array_key_exists($target_date, $stack) ? $stack[$target_date] : array();
      $stack = array_key_exists($action, $stack) ? $stack[$action] : array();
      //Text::p($stack, $user_no);
      if ($limit) {
	$limit_stack = array();
	foreach ($stack as $list) {
	  if ($list['user_no'] == DB::$SELF->user_no) $limit_stack[] = $list;
	}
	$stack = $limit_stack;
	//Text::p($stack, $user_no);
      }
      $result_list = $stack;
    }
    else {
      $str = 'SELECT DISTINCT target, result FROM result_ability WHERE room_no = %d ' .
	"AND date = %d AND type = '%s'";
      $query = sprintf($str, DB::$ROOM->id, $target_date, $action);
      if ($limit) $query .= sprintf(' AND user_no = %d', DB::$SELF->user_no);
      $result_list = DB::FetchAssoc($query);
    }
    //Text::p($result_list);

    switch ($type) {
    case 'mage':
    case 'guard':
      foreach ($result_list as $result) {
	RoleHTML::OutputAbilityResult($header, $result['target'], $footer . $result['result']);
      }
      break;

    case 'necromancer':
      if (is_null($header)) $header = 'necromancer';
      foreach ($result_list as $result) {
	$target = $result['target'];
	RoleHTML::OutputAbilityResult($header . '_result', $target, $footer . $result['result']);
      }
      break;

    case 'priest':
      foreach ($result_list as $result) {
	RoleHTML::OutputAbilityResult($header, $result['result'], $footer);
      }
      break;

    case 'weather_priest':
      foreach ($result_list as $result) {
	RoleHTML::OutputAbilityResult($header, null, $result['result']);
      }
      break;

    case 'crisis_priest':
      foreach ($result_list as $result) {
	RoleHTML::OutputAbilityResult($header . $result['result'], null, $footer);
      }
      break;

    case 'reporter':
      foreach ($result_list as $result) {
	$target = $result['target'] . ' さんは ' . $result['result'];
	RoleHTML::OutputAbilityResult($header, $target, $footer);
      }
      break;

    case 'fox':
      foreach ($result_list as $result) {
	RoleHTML::OutputAbilityResult($header . $result['result'], null);
      }
      break;
    }
  }

  //投票能力表示
  function OutputAction() {}

  //-- 発言処理 --//
  //閲覧者取得
  protected function GetViewer() { return $this->GetStack('viewer'); }

  //閲覧者情報取得
  protected function GetTalkFlag($data) { return $this->GetStack('builder')->flag->$data; }

  //-- 処刑投票処理 --//
  //実ユーザ判定
  protected function IsRealActor() {
    return DB::$USER->ByRealUname($this->GetUname())->IsRole(true, $this->role);
  }

  //生存仲間判定
  protected function IsLivePartner() {
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      if (DB::$USER->ByID($id)->IsLive(true)) return true;
    }
    return false;
  }

  protected function SuddenDeathKill($id) {
    DB::$USER->SuddenDeath($id, 'SUDDEN_DEATH', $this->sudden_death);
  }

  //-- 処刑集計処理 --//
  //処刑者ユーザ名取得
  protected function GetVoteKill() { return $this->GetStack('vote_kill_uname'); }

  //処刑実行判定
  protected function IsVoteKill() { return $this->GetVoteKill() != ''; }

  //処刑者判定
  protected function IsVoted($uname = null) {
    return $this->GetVoteKill() == $this->GetUname($uname);
  }

  //得票者名取得
  protected function GetVotedUname($uname = null) {
    return array_keys($this->GetStack('target'), $this->GetUname($uname));
  }

  //投票先ユーザ名取得
  protected function GetVoteTargetUname($uname = null) {
    $stack = $this->GetStack('target');
    return $stack[$this->GetUname($uname)];
  }

  //投票者ユーザ取得
  protected function GetVoteUser($uname = null) {
    return DB::$USER->ByRealUname($this->GetVoteTargetUname($uname));
  }

  //-- 投票データ表示 (夜) --//
  //投票データセット (夜)
  function SetVoteNight() {
    if (is_null($this->action)) {
      VoteHTML::OutputResult('夜：あなたは投票できません');
    }
    else {
      if (! is_null($str = $this->IgnoreVote())) VoteHTML::OutputResult('夜：' . $str);
      foreach (array('', 'not_') as $header) {
	foreach (array('action', 'submit') as $data) {
	  $this->SetStack($this->{$header . $data}, $header . $data);
	}
      }
    }
  }

  //投票スキップ判定
  function IgnoreVote() { return $this->IsVote() ? null : $this->ignore_message; }

  //-- 投票画面表示 (夜) --//
  //投票対象ユーザ取得
  function GetVoteTargetUser() { return DB::$USER->rows; }

  //投票のアイコンパス取得
  function GetVoteIconPath(User $user, $live) {
    return $live ? Icon::GetFile($user->icon_filename) : Icon::GetDead();
  }

  //投票のチェックボックス取得
  function GetVoteCheckbox(User $user, $id, $live) {
    return $this->IsVoteCheckbox($user, $live) ?
      $this->GetVoteCheckboxHeader() . ' id="' . $id . '" value="' . $id . '">'."\n" : '';
  }

  //投票対象判定
  protected function IsVoteCheckbox(User $user, $live) {
    return $live && ! $this->IsActor($user->uname);
  }

  //投票のチェックボックスヘッダ取得
  function GetVoteCheckboxHeader() { return '<input type="radio" name="target_no"'; }

  //-- 投票処理 (夜) --//
  //未投票チェック
  function IsFinishVote(array $list) {
    if (! $this->IsVote()) return true;
    $id = $this->GetID();
    return (isset($list[$this->not_action]) && array_key_exists($id, $list[$this->not_action])) ||
      isset($list[$this->action][$id]);
  }

  //投票結果チェック (夜)
  function CheckVoteNight() {
    $this->SetStack(RQ::$get->situation, 'message');
    if (! is_null($str = $this->VoteNight())) {
      VoteHTML::OutputResult('夜：投票先が正しくありません<br>'."\n" . $str);
    }
  }

  //投票処理 (夜)
  function VoteNight() {
    $user = DB::$USER->ByID($this->GetVoteNightTarget());
    $live = DB::$USER->IsVirtualLive($user->user_no); //仮想的な生死を判定
    if (! is_null($str = $this->IgnoreVoteNight($user, $live))) return $str;
    $this->SetStack(DB::$USER->ByReal($user->user_no)->user_no, 'target_no');
    $this->SetStack($user->handle_name, 'target_handle');
    return null;
  }

  //投票対象者取得 (夜)
  function GetVoteNightTarget() { return RQ::$get->target_no; }

  //投票スキップ判定 (夜)
  function IgnoreVoteNight(User $user, $live) {
    return ! $live || $this->IsActor($user->uname) ? '自分・死者には投票できません' : null;
  }

  //-- 投票集計処理 (夜) --//
  //成功データ追加
  protected function AddSuccess($target, $data = null, $null = false) {
    RoleManager::$get->{is_null($data) ? $this->role : $data}[$target] = $null ? null : true;
  }

  //投票者取得
  protected function GetVoter() { return $this->GetStack('voter'); }

  //襲撃人狼取得
  protected function GetWolfVoter() { return $this->GetStack('voted_wolf'); }

  //人狼襲撃対象者取得
  protected function GetWolfTarget() { return $this->GetStack('wolf_target'); }

  //-- 勝敗判定 --//
  //勝利判定
  function Win($winner) { return true; }

  //生存判定
  protected function IsLive($strict = false) { return $this->GetActor()->IsLive($strict); }

  //死亡判定
  protected function IsDead($strict = false) { return $this->GetActor()->IsDead($strict); }
}

//-- 発言処理クラス (Role 拡張) --//
class RoleTalk {
  //置換処理
  static function Convert(&$say) {
    if ($say == '') return null; //リロード時なら処理スキップ
    //文字数・行数チェック
    if (strlen($say) > GameConfig::LIMIT_SAY ||
	substr_count($say, "\n") >= GameConfig::LIMIT_SAY_LINE) {
      $say = '';
      return false;
    }
    //発言置換モード
    if (GameConfig::REPLACE_TALK) $say = strtr($say, GameConfig::$replace_talk_list);

    //死者・ゲームプレイ中以外なら以降はスキップ
    if (DB::$SELF->IsDead() || ! DB::$ROOM->IsPlaying()) return null;
    //if (DB::$SELF->IsDead()) return false; //テスト用

    RoleManager::$get->say = $say;
    RoleManager::$actor = ($virtual = DB::$USER->ByVirtual(DB::$SELF->user_no)); //仮想ユーザを取得
    do { //発言置換処理
      foreach (RoleManager::Load('say_convert_virtual') as $filter) {
	if ($filter->ConvertSay()) break 2;
      }
      RoleManager::$actor = DB::$SELF;
      foreach (RoleManager::Load('say_convert') as $filter) {
	if ($filter->ConvertSay()) break 2;
      }
    } while (false);

    foreach ($virtual->GetPartner('bad_status', true) as $id => $date) { //妖精の処理
      if ($date != DB::$ROOM->date) continue;
      RoleManager::$actor = DB::$USER->ByID($id);
      foreach (RoleManager::Load('say_bad_status') as $filter) $filter->ConvertSay();
    }

    RoleManager::$actor = $virtual;
    foreach (RoleManager::Load('say') as $filter) $filter->ConvertSay(); //他のサブ役職の処理
    $say = RoleManager::$get->say;
    unset(RoleManager::$get->say);
    return true;
  }

  //発言を DB に登録する
  static function Save($say, $scene, $location = null, $spend_time = 0, $update = false) {
    //声の大きさを決定
    $voice = RQ::$get->font_type;
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsLive()) {
      RoleManager::$actor = DB::$USER->ByVirtual(DB::$SELF->user_no);
      foreach (RoleManager::Load('voice') as $filter) $filter->FilterVoice($voice, $say);
    }

    $uname = DB::$SELF->uname;
    if (DB::$ROOM->IsBeforeGame()) {
      DB::$ROOM->TalkBeforeGame($say, $uname, DB::$SELF->handle_name, DB::$SELF->color, $voice);
    }
    else {
      $role_id = DB::$ROOM->IsPlaying() ? DB::$SELF->role_id : null;
      DB::$ROOM->Talk($say, null, $uname, $scene, $location, $voice, $role_id, $spend_time);
    }
    if ($update) DB::$ROOM->UpdateTime();
  }
}

//-- HTML 生成クラス (Role 拡張) --//
class RoleHTML {
  //能力の種類とその説明を出力
  static function OutputAbility() {
    if (! DB::$ROOM->IsPlaying()) return false; //ゲーム中のみ表示する

    if (DB::$SELF->IsDead()) { //死亡したら口寄せ以外は表示しない
      echo '<span class="ability ability-dead">' . Message::$ability_dead . '</span><br>';
      if (DB::$SELF->IsRole('mind_evoke')) Image::Role()->Output('mind_evoke');
      if (DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOpenCast()) { //身代わり君のみ隠蔽情報を表示
	echo '<div class="system-vote">' . Message::$close_cast . '</div>'."\n";
      }
      return;
    }
    RoleManager::LoadMain(DB::$SELF)->OutputAbility(); //メイン役職

    //-- ここからサブ役職 --//
    foreach (RoleManager::Load('display_real') as $filter) $filter->OutputAbility();

    //-- ここからは憑依先の役職を表示 --//
    RoleManager::$actor = DB::$USER->ByVirtual(DB::$SELF->user_no);
    foreach (RoleManager::Load('display_virtual') as $filter) $filter->OutputAbility();

    //-- これ以降はサブ役職非公開オプションの影響を受ける --//
    if (DB::$ROOM->IsOption('secret_sub_role')) return;

    $stack = array();
    foreach (array('real', 'virtual', 'none') as $name) {
      $stack = array_merge($stack, RoleManager::${'display_' . $name . '_list'});
    }
    //Text::p($stack);
    $display_list = array_diff(array_keys(RoleData::$sub_role_list), $stack);
    $target_list  = array_intersect($display_list, array_slice(RoleManager::$actor->role_list, 1));
    //Text::p($target_list);
    foreach ($target_list as $role) Image::Role()->Output($role);
  }

  //仲間表示
  static function OutputPartner(array $list, $header, $footer = null) {
    if (count($list) < 1) return false; //仲間がいなければ表示しない
    $list[] = '</td>';
    $str = '<table class="ability-partner"><tr>'."\n" .
      Image::Role()->Generate($header, null, true) ."\n" .
      '<td>　' . implode('さん　', $list) ."\n";
    if ($footer) $str .= Image::Role()->Generate($footer, null, true) ."\n";
    echo $str . '</tr></table>'."\n";
  }

  //現在の憑依先表示
  static function OutputPossessed() {
    $type = 'possessed_target';
    if (is_null($stack = DB::$SELF->GetPartner($type))) return;

    $target = DB::$USER->ByID($stack[max(array_keys($stack))])->handle_name;
    if ($target != '') self::OutputAbilityResult('partner_header', $target, $type);
  }

  //夜の未投票メッセージ出力
  static function OutputVote($class, $sentence, $type, $not_type = '') {
    $stack = DB::$ROOM->test_mode ? array() : DB::$SELF->LoadVote($type, $not_type);
    if (count($stack) < 1) {
      $str = Message::${'ability_' . $sentence};
    }
    elseif ($type == 'WOLF_EAT' || $type == 'CUPID_DO' || $type == 'DUELIST_DO') {
      $str = '投票済み';
    }
    elseif ($type == 'SPREAD_WIZARD_DO') {
      $str_stack = array();
      foreach (explode(' ', $stack['target_no']) as $id) {
	$user = DB::$USER->ByVirtual($id);
	$str_stack[$user->user_no] = $user->handle_name;
      }
      ksort($str_stack);
      $str = implode('さん ', $str_stack) . 'さんに投票済み';
    }
    elseif ($not_type != '' && $stack['type'] == $not_type) {
      $str = 'キャンセル投票済み';
    }
    elseif ($type == 'POISON_CAT_DO' || $type == 'POSSESSED_DO') {
      $str = DB::$USER->ByID($stack['target_no'])->handle_name . 'さんに投票済み';
    }
    else {
      $str = DB::$USER->ByVirtual($stack['target_no'])->handle_name . 'さんに投票済み';
    }
    echo '<span class="ability ' . $class . '">' . $str . '</span><br>'."\n";
  }

  //能力発動結果を表示する
  static function OutputAbilityResult($header, $target, $footer = null) {
    $str = '<table class="ability-result"><tr>'."\n";
    if (isset($header)) $str .= Image::Role()->Generate($header, null, true) ."\n";
    if (isset($target)) $str .= '<td>' . $target . '</td>'."\n";
    if (isset($footer)) $str .= Image::Role()->Generate($footer, null, true) ."\n";
    echo $str . '</tr></table>'."\n";
  }
}
