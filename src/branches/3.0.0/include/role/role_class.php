<?php
//-- 役職コントローラークラス --//
class RoleManager {
  const PATH = '%s/role/%s.php';
  static $file  = array(); //ロード済みファイル
  static $class = array(); //ロード済みクラス
  static $actor; //対象ユーザ
  private static $stack; //スタックデータ

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
    if (is_null($name)) return false;
    if (in_array($name, self::$file)) return true;
    if (! file_exists($file = self::GetPath($name))) return false;
    require_once($file);
    self::$file[] = $name;
    return true;
  }

  //フィルタ用クラスロード
  static function LoadFilter($type) {
    return self::GetFilter(self::GetList($type));
  }

  //メイン役職クラスロード
  static function LoadMain(User $user) {
    self::SetActor($user);
    return self::Load('main_role', true);
  }

  //個別クラスロード (Mixin 用)
  static function LoadMix($name) {
    if (! self::LoadFile($name)) return null;
    $class = 'Role_' . $name;
    return new $class();
  }

  //スタックロード
  static function LoadStack() {
    self::$stack = new Stack();
  }

  //スタック取得
  static function Stack() {
    return self::$stack;
  }

  //個別クラス取得 ($actor を参照していない事を確認すること)
  static function GetClass($role) {
    return (self::LoadFile($role) && self::LoadClass($role)) ? self::$class[$role] : null;
  }

  //ユーザ取得
  static function GetActor() {
    return self::$actor;
  }

  //クラスセット
  static function SetClass($role) {
    return self::LoadFile($role) && self::LoadClass($role);
  }

  //ユーザセット
  static function SetActor(User $user) {
    self::$actor = $user;
  }

  //クラスのロード済み判定
  private static function IsClass($role) {
    return array_key_exists($role, self::$class) && is_object(self::$class[$role]);
  }

  //ファイルパス取得
  private static function GetPath($name) {
    return sprintf(self::PATH, JINROU_INC, $name);
  }

  //役職リスト取得
  private static function GetList($type) {
    return $type == 'main_role' ? array(self::$actor->GetMainRole(true)) : RoleFilterData::$$type;
  }

  //役職リストに応じたクラスリスト取得
  private static function GetFilter(array $list) {
    $stack = array();
    foreach ($list as $role) { //順番依存があるので配列関数を使わないで処理する
      if (self::IsClass($role)) $stack[] = self::$class[$role];
    }
    return $stack;
  }

  //クラスロード
  private static function LoadClass($role) {
    if (is_null($role)) return false;
    if (! self::IsClass($role)) {
      $class_name = 'Role_' . $role;
      self::$class[$role] = new $class_name();
    }
    return true;
  }
}

//-- 役職フィルタデータベース --//
class RoleFilterData {
  //常時表示サブ役職 (本体 / 順番依存あり)
  static $display_real = array(
    'copied', 'copied_trick', 'copied_basic', 'copied_nymph', 'copied_soul', 'copied_teller',
    'lost_ability', 'muster_ability', 'lovers', 'sweet_status', 'challenge_lovers', 'vega_lovers',
    'fake_lovers', 'possessed_exchange', 'letter_exchange', 'joker', 'rival', 'death_note');

  //常時表示サブ役職 (仮想 / 順番依存あり)
  static $display_virtual = array(
    'death_selected', 'febris', 'frostbite', 'death_warrant', 'day_voter', 'wirepuller_luck',
    'occupied_luck', 'tengu_voice', 'mind_open', 'mind_read', 'mind_evoke', 'mind_lonely',
    'mind_receiver', 'mind_friend', 'mind_sympathy', 'mind_sheep', 'mind_presage', 'wisp',
    'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp', 'sheep_wisp', 'aspirator');

  //非表示サブ役職 (呼び出し抑制用)
  static $display_none = array(
    'decide', 'plague', 'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'critical_voter',
    'critical_luck', 'confession', 'enemy', 'supported', 'infected', 'psycho_infected',
    'possessed_target', 'possessed', 'bad_status', 'protected', 'penetration', 'changed_disguise',
    'changed_therian', 'changed_vindictive');

  //初期配役抑制役職
  static $disable_cast = array(
    'febris', 'frostbite', 'death_warrant', 'panelist', 'cute_camouflage', 'confession',
    'day_voter', 'wirepuller_luck', 'occupied_luck', 'tengu_voice', 'mind_read', 'mind_receiver',
    'mind_friend', 'mind_sympathy', 'mind_evoke', 'mind_presage', 'mind_lonely', 'mind_sheep',
    'sheep_wisp', 'lovers', 'challenge_lovers', 'vega_lovers', 'fake_lovers', 'possessed_exchange',
    'letter_exchange', 'joker', 'rival', 'enemy', 'supported', 'death_note', 'death_selected',
    'possessed_target', 'possessed', 'infected', 'psycho_infected', 'bad_status', 'sweet_status',
    'protected', 'penetration', 'aspirator', 'lost_ability', 'muster_ability', 'changed_disguise',
    'changed_therian', 'changed_vindictive', 'copied', 'copied_trick', 'copied_basic',
    'copied_nymph', 'copied_soul', 'copied_teller');

  //発言表示
  static $talk = array('blinder', 'earplug', 'speaker');

  //発言表示 (囁き)
  static $talk_whisper = array('lovers');

  //発言表示 (妖狐)
  static $talk_fox = array('wise_wolf', 'wise_ogre');

  //発言表示 (独り言)
  static $talk_self = array('silver_wolf', 'howl_fox', 'mind_lonely', 'lovers');

  //発言表示 (耳鳴)
  static $talk_ringing = array('whisper_ringing', 'howl_ringing');

  //閲覧判定
  static $mind_read = array(
    'leader_common', 'whisper_scanner', 'howl_scanner', 'telepath_scanner', 'minstrel_cupid',
    'mind_read', 'mind_friend', 'mind_open');

  //閲覧判定 (能動型)
  static $mind_read_active = array('mind_receiver');

  //閲覧判定 (憑依型)
  static $mind_read_possessed = array('possessed_wolf', 'possessed_mad', 'possessed_fox');

  //発言置換 (仮想 / 順番依存あり)
  static $say_convert_virtual = array('confession', 'cute_camouflage', 'gentleman', 'lady');

  //発言置換 (本体)
  static $say_convert = array('suspect', 'cute_mage', 'cute_wolf', 'cute_fox',
			      'cute_chiroptera', 'cute_avenger');

  //悪戯発言変換
  static $say_bad_status = array('fairy', 'spring_fairy', 'summer_fairy', 'autumn_fairy',
				 'winter_fairy', 'greater_fairy');

  //発言変換 (順番依存あり)
  static $say = array('passion', 'actor', 'liar', 'rainbow', 'weekly', 'grassy', 'invisible',
		      'side_reverse', 'line_reverse', 'mower', 'silent');

  //声量
  static $voice = array('strong_voice', 'normal_voice', 'weak_voice', 'inside_voice',
			'outside_voice', 'upper_voice', 'downer_voice', 'random_voice',
			'tengu_voice');

  //霊界遺言登録
  static $heaven_last_words = array('mind_evoke');

  //死因閲覧
  static $show_reason = array('yama_necromancer');

  //蘇生失敗閲覧
  static $show_revive_failed = array('attempt_necromancer', 'vajra_yaksa');

  //人狼襲撃失敗閲覧
  static $show_wolf_failed = array('eye_scanner', 'eye_wolf');

  //処刑投票 (メイン)
  static $vote_do_main = array(
    'human', 'elder', 'scripter', 'eccentricer', 'elder_guard', 'critical_common', 'ascetic_wolf',
    'elder_wolf', 'possessed_mad', 'elder_fox', 'elder_chiroptera', 'critical_duelist',
    'cowboy_duelist');

  //処刑投票 (サブ)
  static $vote_do_sub = array(
    'authority', 'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter',
    'day_voter', 'wirepuller_luck', 'watcher', 'panelist', 'vega_lovers');

  //処刑得票 (メイン)
  static $vote_poll_main = array('critical_common', 'critical_patron');

  //処刑得票 (サブ)
  static $vote_poll_sub = array(
    'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck', 'random_luck',
    'occupied_luck', 'wirepuller_luck', 'vega_lovers');

  //処刑投票能力者 (メイン)
  static $vote_day_main = array(
    'saint', 'executor', 'bacchus_medium', 'seal_medium', 'trap_common', 'spell_common',
    'pharmacist', 'cure_pharmacist', 'revive_pharmacist', 'alchemy_pharmacist',
    'centaurus_pharmacist', 'jealousy', 'divorce_jealousy', 'miasma_jealousy', 'critical_jealousy',
    'thunder_brownie', 'harvest_brownie', 'maple_brownie', 'cursed_brownie',  'disguise_wolf',
    'purple_wolf', 'snow_wolf', 'corpse_courier_mad', 'amaze_mad', 'agitate_mad', 'miasma_mad',
    'critical_mad', 'fire_mad', 'follow_mad', 'purple_fox', 'snow_fox', 'critical_fox',
    'fire_depraver', 'sweet_cupid', 'snow_cupid', 'quiz', 'step_vampire', 'cowboy_duelist',
    'sea_duelist', 'cursed_avenger', 'critical_avenger');

  //処刑投票能力者 (サブ)
  static $vote_day_sub = array('impatience', 'decide', 'plague',
    'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'authority', 'rebel', 'vega_lovers');

  //処刑投票補正能力者
  static $vote_correct = array('cowboy_duelist', 'rebel');

  //処刑者決定 (順番依存あり)
  static $vote_kill = array('decide', 'bad_luck', 'counter_decide', 'dropout', 'impatience',
			    'vega_lovers', 'good_luck', 'plague', 'quiz', 'executor', 'saint',
			    'agitate_mad');

  //毒能力鑑定
  static $distinguish_poison = array('pharmacist', 'alchemy_pharmacist');

  //解毒判定
  static $detox = array('pharmacist', 'cure_pharmacist', 'alchemy_pharmacist');

  //処刑者カウンター
  static $vote_kill_counter = array('brownie', 'sun_brownie', 'doom_doll', 'miasma_fox',
				    'mirror_fairy');

  //処刑投票能力処理 (順番依存あり)
  static $vote_action = array(
    'seal_medium', 'bacchus_medium', 'cowboy_duelist', 'sea_duelist', 'centaurus_pharmacist',
    'spell_common', 'miasma_jealousy', 'critical_jealousy', 'corpse_courier_mad', 'amaze_mad',
    'miasma_mad', 'fire_mad', 'fire_depraver', 'critical_mad', 'critical_fox', 'critical_avenger',
    'purple_wolf', 'purple_fox', 'cursed_avenger', 'sweet_cupid', 'snow_cupid', 'step_vampire',
    'disguise_wolf');

  //霊能
  static $necromancer = array(
    'necromancer', 'soul_necromancer', 'psycho_necromancer', 'embalm_necromancer',
    'emissary_necromancer', 'dummy_necromancer', 'monk_fox');

  //得票カウンター
  static $voted_reaction = array('trap_common', 'jealousy');

  //落雷判定
  static $thunderbolt = array('thunder_brownie');

  //ショック死 (メイン)
  static $sudden_death_main = array('eclipse_medium', 'cursed_angel', 'doom_chiroptera');

  //ショック死 (サブ / 順番依存あり)
  static $sudden_death_sub = array(
    'challenge_lovers', 'febris', 'frostbite', 'death_warrant', 'panelist', 'chicken', 'rabbit',
    'perverseness', 'flattery', 'celibacy', 'nervy', 'androphobia', 'gynophobia', 'impatience');

  //ショック死抑制
  static $cure = array('cure_pharmacist', 'revive_pharmacist');

  //道連れ
  static $followed = array('follow_mad');

  //処刑得票カウンター
  static $vote_kill_reaction = array('divorce_jealousy', 'harvest_brownie', 'maple_brownie',
				     'cursed_brownie', 'snow_wolf', 'snow_fox');

  //処刑キャンセル
  static $vote_cancel = array('prince');

  //人狼襲撃耐性 (順番依存あり)
  static $wolf_eat_resist = array(
    'challenge_lovers', 'vega_lovers', 'protected', 'sacrifice_angel', 'doom_vampire',
    'sacrifice_patron', 'sacrifice_mania', 'tough', 'fend_guard', 'awake_wizard',
    'ascetic_assassin');

  //人狼襲撃得票カウンター (+ 身代わり能力者)
  static $wolf_eat_reaction = array(
    'therian_mad', 'immolate_mad', 'sacrifice_common', 'doll_master', 'toy_doll_master',
    'revive_doll_master', 'serve_doll_master', 'sacrifice_fox', 'sacrifice_vampire',
    'boss_chiroptera', 'sacrifice_ogre');

  //人狼襲撃カウンター
  static $wolf_eat_counter = array(
    'ghost_common', 'presage_scanner', 'cursed_brownie', 'sun_brownie', 'history_brownie',
    'miasma_fox', 'revive_mania', 'mind_sheep');

  //毒回避判定
  static $avoid_poison = array('poison_vampire', 'horse_ogre', 'plumage_patron');

  //襲撃毒死回避
  static $avoid_poison_eat = array('guide_poison', 'poison_jealousy', 'poison_wolf');

  //罠
  static $trap = array('trap_mad', 'snow_trap_mad');

  //護衛
  static $guard = array('guard', 'barrier_wizard', 'barrier_brownie');

  //対暗殺護衛
  static $guard_assassin = array('gatekeeper_guard');

  //対夢食い護衛
  static $guard_dream = array('dummy_guard');

  //厄払い
  static $guard_curse = array('anti_voodoo');

  //呪殺身代わり
  static $sacrifice_mage = array('sacrifice_depraver');

  //復活
  static $resurrect = array(
    'revive_pharmacist', 'revive_brownie', 'revive_doll', 'revive_mad', 'revive_cupid',
    'scarlet_vampire', 'revive_ogre', 'revive_avenger', 'resurrect_mania');

  //天人帰還
  static $priest_return = array('revive_priest');

  //恋人抽選
  static $lottery_lovers = array('altair_cupid', 'letter_cupid', 'exchange_angel');

  //時間差コピー
  static $delay_copy = array('soul_mania', 'dummy_mania');

  //霊能 (夜発動型)
  static $necromancer_night = array('attempt_necromancer');

  //人狼襲撃失敗カウンター
  static $wolf_eat_failed_counter = array('wanderer_guard');

  //投票型子狐系判定
  static $vote_child_fox = array('child_fox', 'sex_fox', 'stargazer_fox', 'jammer_fox');

  //蘇生制限判定
  static $revive_limited = array('detective_common', 'scarlet_vampire', 'resurrect_mania');

  //憑依能力者判定
  static $possessed_group = array('possessed_wolf', 'possessed_mad', 'possessed_fox');

  //憑依制限判定
  static $possessed_limited = array(
    'detective_common', 'revive_priest', 'revive_pharmacist', 'revive_brownie', 'revive_doll',
    'revive_wolf', 'revive_mad', 'revive_cupid', 'scarlet_vampire', 'revive_ogre',
    'revive_avenger', 'resurrect_mania');

  //暗殺反射判定 (常時反射)
  static $reflect_assassin = array('reflect_guard', 'detective_common', 'cursed_fox',
				   'soul_vampire');

  //遺言制限判定
  static $last_words_limited = array('reporter', 'soul_assassin', 'evoke_scanner', 'no_last_words');

  //イベントセット
  static $event_virtual = array(
    'liar', 'gentleman', 'lady', 'strong_voice', 'weak_voice', 'no_last_words', 'whisper_ringing',
    'howl_ringing', 'sweet_ringing', 'deep_sleep', 'mind_open');

  //イベントセット (昼限定)
  static $event_virtual_day = array(
    'actor', 'passion', 'rainbow', 'grassy', 'invisible', 'side_reverse', 'line_reverse',
    'confession', 'critical_voter', 'critical_luck', 'blinder', 'earplug', 'silent', 'mower');

  //イベントセット (悪戯 / 順番依存あり)
  static $event_bad_status = array('shadow_fairy', 'enchant_mad');

  //特殊勝敗判定 (ジョーカー系)
  static $joker = array('joker', 'rival');
}

//-- 役職の基底クラス --//
abstract class Role {
  public $role;

  public function __construct() {
    $this->role = array_pop(explode('Role_', get_class($this)));
    //Text::p(get_class_vars(get_class($this)), "◆{$this->role}");
  }

  //-- 基礎関数 --//
  //Mixin 呼び出し
  public function __call($name, $args) {
    if (! isset($this->mix_in)) return $this->ReturnError($name, 'Mixin');

    $filter = $this->GetMethod($name);
    if (is_null($filter)) return $this->ReturnError($name, 'Method');

    return call_user_func_array(array($filter, $name), $args);
  }

  //プロパティ呼び出し
  public function __get($name) {
    switch ($name) {
    case 'action':
    case 'not_action':
    case 'add_action':
    case 'submit':
    case 'not_submit':
    case 'add_submit':
      $this->$name = null;
      return;

    case 'mix_in':
    case 'mix_in_list':
    case 'method_list':
      $this->$name = array();
      return;

    default:
      $this->ReturnError($name, 'Property');
      return null;
    }
  }

  //クラス名取得
  final protected function GetClassName() {
    return 'Role_' . $this->role;
  }

  //-- Mixin 関連 --//
  //Mixin 呼び出しエラー
  final protected function ReturnError($name, $type) {
    switch ($type) {
    case 'Property':
      $target = sprintf('$%s', $name);
      break;

    default:
      $target = sprintf('%s()', $name);
      break;
    }
    $format = '%sError: %s not found: %s: %s';
    Text::p(sprintf($format, Message::SYMBOL, $type, get_class($this), $target));
    return false;
  }

  //Mixin ロード
  final protected function LoadMix($name) {
    $filter = RoleManager::LoadMix($name);
    $filter->role = $this->role;
    if (isset($this->display_role)) $filter->display_role = $this->display_role;
    return $filter;
  }

  //Mixin 登録
  final public function AddMix($name) {
    if (! isset($this->mix_in_list[$name])) {
      //Text::p($name, "◆{$this->role}");
      $this->mix_in_list[$name] = $this->LoadMix($name);
    }
    return $this;
  }

  //親クラス取得 (Mixin 用)
  final protected function GetParent($method) {
    $class = $this->GetClassName();
    if ($class == get_class($this)) return $this;
    return method_exists($class, $method) ? RoleManager::GetClass($this->role) : $this;
  }

  //親クラス実行
  final protected function CallParent($method) {
    //Text::p($method, "◆CallParent [{$this->role}]");
    return $this->GetParent($method)->$method();
  }

  //Mixin クラス取得
  final protected function GetMix($name) {
    return isset($this->mix_in_list[$name]) ? $this->mix_in_list[$name] : null;
  }

  //Mixin メソッド保持クラス取得
  final protected function GetMethod($name) {
    if (isset($this->method_list[$name])) {
      //Text::t($name . '/' . $this->method_list[$name], "◆Method/Cache [{$this->role}]");
      return $this->GetMix($this->method_list[$name]);
    }
    //Text::t($name, "◆Method/Search [{$this->role}]");

    foreach ($this->mix_in as $role) {
      $filter = $this->AddMix($role)->GetMix($role);
      if (method_exists($filter, $name)) {
	$this->method_list[$name] = $role;
	return $filter;
      }
    }
    return null;
  }

  //プロパティ取得 (Mixin 用)
  final protected function GetProperty($name) {
    if ($this->GetClassName() != get_class($this)) {
      $filter = RoleManager::GetClass($this->role);
      if (isset($filter->$name)) return $filter->$name;
    }
    return isset($this->$name) ? $this->$name : null;
  }

  //投票用 Mixin 存在判定
  final protected function ExistVoteMix() {
    if (! isset($this->mix_in) || ! isset($this->mix_in['vote'])) return false;
    $this->AddMix($this->mix_in['vote']);
    return true;
  }

  //投票用 Mixin 取得
  final protected function GetVoteMix() {
    return $this->GetMix($this->mix_in['vote']);
  }

  //投票用 Mixin 実行
  final protected function CallVoteMix($name) {
    return $this->GetVoteMix()->$name();
  }

  //-- 汎用関数 --//
  //ユーザ取得
  final protected function GetActor() {
    return RoleManager::GetActor();
  }

  //ユーザ ID 取得
  final protected function GetID() {
    return $this->GetActor()->id;
  }

  //ユーザ名取得
  final protected function GetUname($uname = null) {
    return is_null($uname) ? $this->GetActor()->uname : $uname;
  }

  //データ取得
  final protected function GetStack($name = null, $fill = false) {
    $stack = RoleManager::Stack()->Get(is_null($name) ? $this->role : $name);
    return isset($stack) ? $stack : ($fill ? array() : null);
  }

  //データセット
  final protected function SetStack($data, $role = null) {
    RoleManager::Stack()->Set(is_null($role) ? $this->role : $role, $data);
  }

  //データ初期化
  final protected function InitStack($name = null) {
    $data  = is_null($name) ? $this->role : $name;
    $stack = RoleManager::Stack()->Get($data);
    if (! isset($stack)) RoleManager::Stack()->Init($data);
  }

  //データ追加
  final protected function AddStack($data, $role = null, $id = null) {
    if (is_null($id)) $id = $this->GetID();
    $name  = is_null($role) ? $this->role : $role;
    $stack = RoleManager::Stack()->Get($name);
    $stack[$id] = $data;
    RoleManager::Stack()->Set($name, $stack);
  }

  //データ追加 (Uname 用)
  final protected function AddStackName($data, $role = null, $uname = null) {
    $name  = is_null($role) ? $this->role : $role;
    $stack = RoleManager::Stack()->Get($name);
    $stack[$this->GetUname($uname)] = $data;
    RoleManager::Stack()->Set($name, $stack);
  }

  //同一ユーザ判定
  final protected function IsActor(User $user) {
    return $this->GetActor()->IsSame($user);
  }

  //発動日判定
  final protected function IsDoom() {
    return $this->GetActor()->GetDoomDate($this->role) == DB::$ROOM->date;
  }

  //投票能力判定
  final public function IsVote() {
    if ($this->ExistVoteMix()) return $this->CallVoteMix(__FUNCTION__);
    return ! is_null($this->action) && $this->IsVoteDate() && $this->IsAddVote();
  }

  //投票可能日判定
  final protected function IsVoteDate() {
    $type = isset($this->action_date_type) ? $this->action_date_type : null;
    switch ($type) {
    case 'first':
      return DB::$ROOM->IsDate(1);

    case 'after':
      return DB::$ROOM->date > 1;

    default:
      return true;
    }
  }

  //投票能力追加判定
  protected function IsAddVote() {
    return true;
  }

  //-- 役職情報表示 --//
  //役職情報表示
  final public function OutputAbility() {
    if ($this->IgnoreAbility()) return;
    $this->OutputImage();
    $this->OutputPartner();
    $this->OutputResult();
    if (DB::$ROOM->IsNight() && $this->IsVote()) $this->OutputAction();
  }

  //役職情報表示判定
  protected function IgnoreAbility() {
    return false;
  }

  //役職画像表示
  final protected function OutputImage() {
    if ($this->IgnoreImage()) return;
    Image::Role()->Output($this->GetImage());
  }

  //役職画像表示判定
  protected function IgnoreImage() {
    return false;
  }

  //役職画像表示対象取得
  protected function GetImage() {
    return isset($this->display_role) ? $this->display_role : $this->role;
  }

  //仲間情報表示
  protected function OutputPartner() {}

  //能力結果表示
  final protected function OutputResult() {
    if ($this->IgnoreResult()) return;
    if (isset($this->result)) $this->OutputAbilityResult($this->result);
    $this->OutputAddResult();
  }

  //能力結果表示判定
  protected function IgnoreResult() {
    return false;
  }

  //追加結果表示
  protected function OutputAddResult() {}

  //能力発動結果表示
  final protected function OutputAbilityResult($action) {
    $header = null;
    $footer = 'result_';
    $limit  = false;
    $uniq   = false;
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

    case 'PRIEST_TENGU_RESULT':
      $type   = 'priest';
      $header = 'priest_tengu_header';
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
    case 'GUARD_PENETRATION':
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
      $uniq   = true;
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
      $uniq   = true;
      break;

    case 'PRESAGE_RESULT':
      $type   = 'reporter';
      $header = 'presage_result_header';
      $footer = 'reporter_result_footer';
      $limit  = true;
      break;

    case 'TENGU_RESULT':
      $type   = 'mage';
      $header = 'tengu_result';
      $limit  = true;
      break;

    case 'TENGU_CAMP_RESULT':
      $type = 'weather_priest';
      break;

    default:
      if (DB::$ROOM->IsTest()) Text::p($action, '◆Invalid Action');
      return false;
    }

    $target_date = DB::$ROOM->date - 1;
    if (DB::$ROOM->IsTest()) {
      $result_list = DevRoom::GetAbility($target_date, $action, $limit);
    } else {
      $result_list = SystemMessageDB::GetAbility($target_date, $action, $limit);
    }
    //Text::p($result_list, '◆Result');

    switch ($type) {
    case 'mage':
    case 'guard':
      if ($uniq) $stack = array();
      foreach ($result_list as $result) {
	if ($uniq && in_array($result['target'], $stack)) continue;
	RoleHTML::OutputAbilityResult($header, $result['target'], $footer . $result['result']);
	if ($uniq) $stack[] = $result['target'];
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
      if ($uniq) $stack = array();
      foreach ($result_list as $result) {
	if ($uniq && in_array($result['result'], $stack)) continue;
	$target = $result['target'] . ' さんは ' . $result['result'];
	RoleHTML::OutputAbilityResult($header, $target, $footer);
	if ($uniq) $stack[] = $result['result'];
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
  public function OutputAction() {
    if ($this->ExistVoteMix()) return $this->CallVoteMix(__FUNCTION__);
  }

  //-- 発言処理 --//
  //閲覧者取得
  final protected function GetViewer() {
    return $this->GetStack('viewer');
  }

  //閲覧者情報取得
  final protected function GetTalkFlag($data) {
    return $this->GetStack('builder')->flag->$data;
  }

  //-- 処刑投票処理 --//
  //実ユーザ判定
  final protected function IsRealActor() {
    return DB::$USER->ByRealUname($this->GetUname())->IsRole(true, $this->role);
  }

  //生存仲間判定
  final protected function IsLivePartner() {
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      if (DB::$USER->ByID($id)->IsLive(true)) return true;
    }
    return false;
  }

  //突然死処理
  final protected function SuddenDeathKill($id) {
    DB::$USER->SuddenDeath($id, 'SUDDEN_DEATH', $this->sudden_death);
  }

  //-- 処刑集計処理 --//
  final public function SetVoteDay($uname) {
    switch ($this->vote_day_type) {
    case 'target':
      $this->SetStack($uname);
      break;

    case 'self':
      $this->SetStack($this->GetUname());
      break;

    case 'stack':
      $this->AddStackName($uname);
      break;

    case 'init':
      $this->InitStack();
      $this->AddStackName($uname);
      break;

    case 'both':
      $this->SetStack($this->GetUname());
      $this->SetStack($uname, $this->role . '_uname');
      break;
    }
  }

  //処刑者ユーザ名取得
  final protected function GetVoteKill() {
    return $this->GetStack('vote_kill_uname');
  }

  //処刑実行判定
  final protected function IsVoteKill() {
    return ! is_null($this->GetVoteKill());
  }

  //処刑者判定
  final protected function IsVoted($uname = null) {
    return $this->GetVoteKill() == $this->GetUname($uname);
  }

  //得票者名取得
  final protected function GetVotedUname($uname = null) {
    return array_keys($this->GetStack('vote_target'), $this->GetUname($uname));
  }

  //投票先ユーザ名取得
  final protected function GetVoteTargetUname($uname = null) {
    $stack = $this->GetStack('vote_target');
    return $stack[$this->GetUname($uname)];
  }

  //投票者ユーザ取得
  final protected function GetVoteUser($uname = null) {
    return DB::$USER->ByRealUname($this->GetVoteTargetUname($uname));
  }

  //-- 投票データ表示 (夜) --//
  //投票データセット (夜)
  public function SetVoteNight() {
    if ($this->ExistVoteMix()) {
      return $this->CallVoteMix(__FUNCTION__);
    }
    elseif (is_null($this->action)) {
      VoteHTML::OutputResult(VoteRoleMessage::NO_ACTION);
    }
    else {
      $str = $this->IgnoreVote();
      if (is_null($str)) $str = $this->IgnoreVoteFilter(); //null なら追加判定
      if (! is_null($str)) VoteHTML::OutputResult($str);

      foreach (array('', 'not_', 'add_') as $header) {
	foreach (array('action', 'submit') as $data) {
	  $this->SetStack($this->{$header . $data}, $header . $data);
	}
      }
      $this->SetVoteNightFilter();
    }
  }

  //投票スキップ判定
  final protected function IgnoreVote() {
    return $this->IsVote() ? null : $this->GetIgnoreMessage();
  }

  //投票無効メッセージ取得
  protected function GetIgnoreMessage() {
    if (! $this->IsAddVote()) { //投票能力追加判定で無効の場合は追加判定からメッセージを取得する
      return $this->IgnoreVoteFilter();
    }

    $type = isset($this->action_date_type) ? $this->action_date_type : null;
    switch ($type) {
    case 'first':
      return VoteRoleMessage::POSSIBLE_ONLY_FIRST_DAY;

    case 'after':
      return VoteRoleMessage::IMPOSSIBLE_FIRST_DAY;

    default:
      return true;
    }
  }

  //投票スキップ追加判定
  protected function IgnoreVoteFilter() {
    return null;
  }

  //投票データセット (夜) 追加処理
  protected function SetVoteNightFilter() {}

  //-- 投票画面表示 (夜) --//
  //投票対象ユーザ取得
  public function GetVoteTargetUser() {
    return DB::$USER->rows;
  }

  //投票のアイコンパス取得
  public function GetVoteIconPath(User $user, $live) {
    if ($this->ExistVoteMix()) {
      $name = __FUNCTION__;
      return $this->GetVoteMix()->$name($user, $live);
    }
    return $live ? Icon::GetFile($user->icon_filename) : Icon::GetDead();
  }

  //投票のチェックボックス取得
  public function GetVoteCheckbox(User $user, $id, $live) {
    if ($this->ExistVoteMix()) {
      $name = __FUNCTION__;
      return $this->GetVoteMix()->$name($user, $id, $live);
    }
    if (! $this->IsVoteCheckbox($user, $live)) return '';
    $checked = $this->IsVoteCheckboxChecked($user) ? ' checked' : '';
    $str     = sprintf(' id="%d" value="%d"%s>', $id, $id, $checked);
    return $this->GetVoteCheckboxHeader() . $str . Text::LF;
  }

  //投票対象判定
  public function IsVoteCheckbox(User $user, $live) {
    return $live && ! $this->IsActor($user);
  }

  //投票対象自動チェック判定
  protected function IsVoteCheckboxChecked(User $user) {
    return false;
  }

  //投票のチェックボックスヘッダ取得
  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('radio');
  }

  //-- 投票処理 (夜) --//
  //未投票チェック
  public function IsFinishVote(array $list) {
    if ($this->ExistVoteMix()) {
      $name = __FUNCTION__;
      return $this->GetVoteMix()->$name($list);
    }
    return ! $this->IsVote() || $this->IgnoreFinishVote() || $this->ExistsAction($list);
  }

  //未投票チェックスキップ判定
  protected function IgnoreFinishVote() {
    return false;
  }

  //投票コマンド存在判定
  protected function ExistsAction(array $list) {
    $list = $this->ExistsActionFilter($list);
    $id   = $this->GetID();
    return isset($list[$this->action][$id]) ||
      (isset($list[$this->not_action]) && array_key_exists($id, $list[$this->not_action]));
  }

  //投票コマンド存在判定前フィルタ
  protected function ExistsActionFilter(array $list) {
    return $list;
  }

  //投票結果チェック (夜)
  public function CheckVoteNight() {
    if ($this->ExistVoteMix()) return $this->CallVoteMix(__FUNCTION__);

    $this->SetStack(RQ::Get()->situation, 'message');
    $str = $this->VoteNight();
    if (! is_null($str)) {
      VoteHTML::OutputResult(VoteRoleMessage::INVALID_TARGET . Text::BRLF . $str);
    }
  }

  //投票処理 (夜)
  final protected function VoteNight() {
    $stack = RQ::Get()->target_no;
    if (is_array($stack)) {
      $str = $this->CheckVoteNightTarget($stack);
      if (! is_null($str)) return $str;

      $str = $this->SetVoteNightUserList($stack);
      if (! is_null($str)) return $str;

      $this->CallParent('VoteNightAction');
    }
    else {
      $user = DB::$USER->ByID($stack);
      $live = DB::$USER->IsVirtualLive($user->id); //生死判定は仮想を使う
      $str  = $this->IgnoreVoteNight($user, $live);
      if (! is_null($str)) return $str;

      //憑依者がすでに死んでいたら元の投票先を見る (死者投票型対応)
      $real = DB::$USER->ByReal($user->id);
      $target = $real->IsLive() ? $real : $user;
      $this->SetStack($target->id, 'target_no');
      $this->SetStack($user->handle_name, 'target_handle');
    }
    return null;
  }

  //投票対象チェック
  public function CheckVoteNightTarget(array $list) {
    $count = $this->GetVoteNightNeedCount();
    if (count($list) != $count) return sprintf(VoteRoleMessage::INVALID_TARGET_COUNT, $count);
    return null;
  }

  //所要投票人数取得
  protected function GetVoteNightNeedCount() {
    return 2;
  }

  //投票対象者セット (夜)
  public function SetVoteNightUserList(array $list) {
    return null;
  }

  //投票追加処理 (夜)
  public function VoteNightAction() {}

  //投票スキップ判定 (夜)
  public function IgnoreVoteNight(User $user, $live) {
    if (! $live) return VoteRoleMessage::TARGET_DEAD;
    return $this->IsActor($user) ? VoteRoleMessage::TARGET_MYSELF : null;
  }

  //-- 投票集計処理 (夜) --//
  //成功データ追加
  protected function AddSuccess($target, $data = null, $null = false) {
    $name  = is_null($data) ? $this->role : $data;
    $stack = RoleManager::Stack()->Get($name);
    $stack[$target] = $null ? null : true;
    RoleManager::Stack()->Set($name, $stack);
  }

  //投票者取得
  protected function GetVoter() {
    return $this->GetStack('voter');
  }

  //襲撃人狼取得
  protected function GetWolfVoter() {
    return $this->GetStack('voted_wolf');
  }

  //人狼襲撃対象者取得
  protected function GetWolfTarget() {
    return $this->GetStack('wolf_target');
  }

  //-- 勝敗判定 --//
  //勝利判定
  public function Win($winner) {
    return true;
  }

  //生存判定
  protected function IsLive($strict = false) {
    return $this->GetActor()->IsLive($strict);
  }

  //死亡判定
  protected function IsDead($strict = false) {
    return $this->GetActor()->IsDead($strict);
  }
}

//-- 発言処理クラス (Role 拡張) --//
class RoleTalk {
  //location 判定
  static function GetLocation(User $user, User $real) {
    if (DB::$ROOM->IsEvent('blind_talk_night')) { //天候：風雨
      return TalkLocation::MONOLOGUE;
    }
    elseif ($user->IsWolf(true)) { //人狼
      //犬神判定
      return $real->IsRole('possessed_mad') ? TalkLocation::MONOLOGUE : TalkLocation::WOLF;
    }
    elseif ($user->IsRole('whisper_mad')) { //囁き狂人
      //犬神判定
      return $real->IsRole('possessed_mad') ? TalkLocation::MONOLOGUE : TalkLocation::MAD;
    }
    elseif ($user->IsCommon(true)) { //共有者
      return TalkLocation::COMMON;
    }
    elseif ($user->IsFox(true)) { //妖狐
      return TalkLocation::FOX;
    }
    else { //独り言
      return TalkLocation::MONOLOGUE;
    }
  }

  //置換処理
  static function Convert(&$say) {
    if ($say == '') return null; //リロード時なら処理スキップ
    //文字数・行数チェック
    if (strlen($say) > GameConfig::LIMIT_SAY ||
	substr_count($say, Text::LF) >= GameConfig::LIMIT_SAY_LINE) {
      $say = '';
      return false;
    }
    //発言置換モード
    if (GameConfig::REPLACE_TALK) $say = strtr($say, GameConfig::$replace_talk_list);

    //死者・ゲームプレイ中以外なら以降はスキップ
    if (DB::$SELF->IsDead() || ! DB::$ROOM->IsPlaying()) return null;
    //if (DB::$SELF->IsDead()) return false; //テスト用

    $virtual = DB::$SELF->GetVirtual(); //仮想ユーザを取得
    RoleManager::Stack()->Set('say', $say);
    do { //発言置換処理
      RoleManager::SetActor($virtual);
      foreach (RoleManager::Load('say_convert_virtual') as $filter) {
	if ($filter->ConvertSay()) break 2;
      }

      RoleManager::SetActor(DB::$SELF);
      foreach (RoleManager::Load('say_convert') as $filter) {
	if ($filter->ConvertSay()) break 2;
      }
    } while (false);

    foreach ($virtual->GetPartner('bad_status', true) as $id => $date) { //妖精の処理
      if ($date != DB::$ROOM->date) continue;
      RoleManager::SetActor(DB::$USER->ByID($id));
      foreach (RoleManager::Load('say_bad_status') as $filter) {
	$filter->ConvertSay();
      }
    }

    RoleManager::SetActor($virtual);
    foreach (RoleManager::Load('say') as $filter) {
      $filter->ConvertSay(); //他のサブ役職の処理
    }
    $say = RoleManager::Stack()->Get('say');
    RoleManager::Stack()->Clear('say');
    return true;
  }

  //発言を DB に登録する
  static function Save($say, $scene, $location = null, $spend_time = 0, $update = false) {
    //声の大きさを決定
    $voice = RQ::Get()->font_type;
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsLive()) {
      RoleManager::SetActor(DB::$SELF->GetVirtual());
      foreach (RoleManager::Load('voice') as $filter) {
	$filter->FilterVoice($voice, $say);
      }
    }

    $uname = DB::$SELF->uname;
    if (DB::$ROOM->IsBeforeGame()) {
      DB::$ROOM->TalkBeforeGame($say, $uname, DB::$SELF->handle_name, DB::$SELF->color, $voice);
    }
    else {
      $role_id = DB::$ROOM->IsPlaying() ? DB::$SELF->role_id : null;
      DB::$ROOM->Talk($say, null, $uname, $scene, $location, $voice, $role_id, $spend_time);
    }
    if ($update) RoomDB::UpdateTime();
  }
}

//-- HTML 生成クラス (Role 拡張) --//
class RoleHTML {
  //能力の種類とその説明を出力
  static function OutputAbility() {
    if (! DB::$ROOM->IsPlaying()) return false; //ゲーム中のみ表示する

    if (DB::$SELF->IsDead()) { //死亡したら口寄せ以外は表示しない
      echo '<span class="ability ability-dead">' . RoleAbilityMessage::$dead . '</span>' . Text::BR;
      if (DB::$SELF->IsRole('mind_evoke')) Image::Role()->Output('mind_evoke');
      if (DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOpenCast()) { //身代わり君のみ隠蔽情報を表示
	GameHTML::OutputVoteAnnounce(GameMessage::CLOSE_CAST);
      }
      return;
    }
    RoleManager::LoadMain(DB::$SELF)->OutputAbility(); //メイン役職

    //-- ここからサブ役職 --//
    foreach (RoleManager::Load('display_real') as $filter) $filter->OutputAbility();

    //-- ここからは憑依先の役職を表示 --//
    RoleManager::SetActor(DB::$SELF->GetVirtual());
    foreach (RoleManager::Load('display_virtual') as $filter) $filter->OutputAbility();

    //-- これ以降はサブ役職非公開オプションの影響を受ける --//
    if (DB::$ROOM->IsOption('secret_sub_role')) return;

    foreach (RoleData::GetDisplayList(RoleManager::GetActor()->role_list) as $role) {
      Image::Role()->Output($role);
    }
  }

  //仲間表示
  static function OutputPartner(array $list, $header, $footer = null) {
    if (count($list) < 1) return; //仲間がいなければ表示しない

    $list[] = '</td>';
    $stack = array(
      '<table class="ability-partner"><tr>',
      Image::Role()->Generate($header, null, true),
      '<td>' . Message::SPACER . implode(RoleAbilityMessage::HONORIFIC . Message::SPACER, $list)
    );
    if ($footer) $stack[] = Image::Role()->Generate($footer, null, true);
    $stack[] = '</tr></table>';
    Text::Output(implode(Text::LF, $stack));
  }

  //現在の憑依先表示
  static function OutputPossessed() {
    $type = 'possessed_target';
    if (is_null($stack = DB::$SELF->GetPartner($type))) return;

    $target = DB::$USER->ByID($stack[max(array_keys($stack))])->handle_name;
    if ($target != '') self::OutputAbilityResult('partner_header', $target, $type);
  }

  //処刑投票メッセージ出力
  static function OutputVoteKill() {
    if (DB::$ROOM->date < 2 || ! DB::$ROOM->IsDay() || DB::$SELF->IsDead()) return; //スキップ判定

    $format = '<div class="self-vote">%s%s</div>' . Text::LF;
    $vote_count = sprintf(RoleAbilityMessage::VOTE_COUNT, DB::$ROOM->revote_count + 1);
    if (is_null(DB::$SELF->target_no)) {
      printf($format, $vote_count, HTML::GenerateWarning(RoleAbilityMessage::NOT_VOTED));

      $str = RoleAbilityMessage::$vote_kill;
      printf('<span class="ability vote-do">%s</span>' . Text::BRLF, $str);
    }
    else {
      $user = DB::$USER->ByVirtual(DB::$SELF->target_no);
      printf($format, $vote_count, $user->handle_name . RoleAbilityMessage::SETTLE_VOTED);
    }
  }

  //夜の未投票メッセージ出力
  static function OutputVote($class, $sentence, $type, $not_type = '') {
    $stack = DB::$ROOM->IsTest() ? array() : DB::$SELF->LoadVote($type, $not_type);
    if (count($stack) < 1) {
      $str = RoleAbilityMessage::$$sentence;
    } else {
      $str = self::GetVoteMessage($stack, $type, $not_type);
    }
    printf('<span class="ability %s">%s</span>' . Text::BRLF, $class, $str);
  }

  //能力発動結果を表示する
  static function OutputAbilityResult($header, $target, $footer = null) {
    $str = '<table class="ability-result"><tr>' . Text::LF;
    if (isset($header)) $str .= Image::Role()->Generate($header, null, true) . Text::LF;
    if (isset($target)) $str .= '<td>' . $target . '</td>' . Text::LF;
    if (isset($footer)) $str .= Image::Role()->Generate($footer, null, true) . Text::LF;
    Text::Output($str . '</tr></table>');
  }

  //投票のチェックボックスヘッダ取得
  static function GetVoteCheckboxHeader($type) {
    switch ($type) {
    case 'radio':
      return '<input type="radio" name="target_no"';

    case 'checkbox':
      return '<input type="checkbox" name="target_no[]"';
    }
  }

  //夜の投票済みメッセージを取得
  private static function GetVoteMessage(array $stack, $type, $not_type = '') {
    switch ($type) {
    case 'WOLF_EAT':
    case 'STEP_WOLF_EAT':
    case 'SILENT_WOLF_EAT':
    case 'CUPID_DO':
    case 'DUELIST_DO':
      return RoleAbilityMessage::VOTED;

    case 'STEP_MAGE_DO':
    case 'STEP_GUARD_DO':
    case 'STEP_ASSASSIN_DO':
    case 'STEP_SCANNER_DO':
    case 'SPREAD_WIZARD_DO':
    case 'STEP_VAMPIRE_DO':
      return self::GetMultiVoteMessage($stack);

    case 'STEP_DO':
      if ($not_type != '' && $stack['type'] == $not_type) {
	return RoleAbilityMessage::CANCEL_VOTED;
      }
      return self::GetMultiVoteMessage($stack);

    case 'POISON_CAT_DO':
    case 'POSSESSED_DO':
      if ($not_type != '' && $stack['type'] == $not_type) {
	return RoleAbilityMessage::CANCEL_VOTED;
      }
      $user = DB::$USER->ByID($stack['target_no']);
      return $user->handle_name . RoleAbilityMessage::SETTLE_VOTED;

    default:
      if ($not_type != '' && $stack['type'] == $not_type) {
	return RoleAbilityMessage::CANCEL_VOTED;
      }
      $user = DB::$USER->ByVirtual($stack['target_no']);
      return $user->handle_name . RoleAbilityMessage::SETTLE_VOTED;
    }
  }

  //夜の投票済みメッセージを取得 (複数投票型)
  private static function GetMultiVoteMessage(array $stack) {
    $str_stack = array();
    foreach (explode(' ', $stack['target_no']) as $id) {
      $user = DB::$USER->ByVirtual($id);
      $str_stack[$user->id] = $user->handle_name;
    }
    ksort($str_stack);
    $str = RoleAbilityMessage::HONORIFIC . ' ';
    return implode($str, $str_stack) . RoleAbilityMessage::SETTLE_VOTED;
  }
}
