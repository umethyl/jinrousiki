<?php
//-- オプションローダー --//
class OptionLoader extends LoadManager {
  const PATH = '%s/option/%s.php';
  const CLASS_PREFIX = 'Option_';
  protected static $file  = [];
  protected static $class = [];
}

//-- オプションマネージャ --//
class OptionManager {
  //スタック取得
  public static function Stack() {
    static $stack;

    if (is_null($stack)) {
      $stack = new Stack();
    }
    return $stack;
  }

  //村オプション変更判定
  public static function IsChange() {
    if (self::Stack()->IsEmpty('change')) {
      self::Stack()->Set('change', false);
    }
    return self::Stack()->Get('change');
  }

  //オプション存在判定
  public static function Exists($type) {
    foreach (OptionFilterData::$$type as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return true;
      }
    }
    return false;
  }

  //オプション存在判定 (村人置換村)
  public static function ExistsReplaceHuman() {
    return self::Exists('group_replace_human');
  }

  //オプション存在判定 (闇鍋モード)
  public static function ExistsChaos() {
    return self::Exists('group_chaos');
  }

  //オプション存在判定 (闇鍋式希望制)
  public static function ExistsWishRoleChaos() {
    return self::ExistsReplaceHuman() || self::ExistsChaos() ||
      self::Exists('group_wish_role_chaos');
  }

  //オプションクラスロード
  public static function GetFilter($type) {
    foreach (OptionFilterData::$$type as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return OptionLoader::Load($option);
      }
    }
    return null;
  }

  //オプション名生成
  public static function GenerateCaption($name) {
    return OptionLoader::LoadFile($name) ? OptionLoader::Load($name)->GetName() : '';
  }

  //オプション名出力
  public static function OutputCaption($name) {
    echo self::GenerateCaption($name);
  }

  //オプション説明出力
  public static function OutputExplain($name) {
    echo OptionLoader::LoadFile($name) ? OptionLoader::Load($name)->GetExplain() : '';
  }

  //-- Cast --//
  //基礎配役取得
  public static function GetCastBase($user_count) {
    foreach (OptionFilterData::$cast_base as $option) {
      if (false === DB::$ROOM->IsOption($option)) {
	continue;
      }

      $filter = OptionLoader::Load($option);
      if (true === $filter->EnableCast($user_count)) {
	return $filter;
      }
    }
    return null;
  }

  //追加配役 (普通村)
  public static function FilterCastAddRole(array &$list, $count) {
    foreach (OptionFilterData::$cast_add_role as $option) {
      if (DB::$ROOM->IsOption($option) && OptionLoader::LoadFile($option)) {
	OptionLoader::Load($option)->FilterCastAddRole($list, $count);
      }
    }
  }

  //追加配役 (闇鍋固定枠)
  public static function FilterCastChaosFixRole(array &$list) {
    foreach (OptionFilterData::$cast_chaos_fix_role as $option) {
      if (DB::$ROOM->IsOption($option)) {
	OptionLoader::Load($option)->FilterCastChaosFixRole($list);
      }
    }
  }

  //配役 (役職置換)
  public static function CastRoleReplace(array &$list, $base, $target) {
    if (ArrayFilter::GetInt($list, $base) < 1) {
      return false;
    }
    ArrayFilter::Replace($list, $base, $target);
    return true;
  }

  //身代わり君配役制限役職登録
  public static function StoreDummyBoyCastLimit(array $role_list) {
    $option = 'dummy_boy_cast_limit';
    if (false === DB::$ROOM->IsOption($option)) {
      return false;
    }

    foreach ($role_list as $role) {
      Cast::Stack()->Register(Cast::DUMMY, $role);
    }
    //Cast::Stack()->p(Cast::DUMMY, '◆Store');
  }

  //ゲルト君モード有効判定
  public static function EnableGerd($role = 'human') {
    $option = 'gerd';
    return DB::$ROOM->IsOption($option) && OptionLoader::Load($option)->EnableGerd($role);
  }

  //ユーザーサブ役職配役処理
  public static function CastUserSubRole() {
    $stack = Cast::Stack()->Get(Cast::DELETE);
    foreach (OptionFilterData::$cast_user_sub_role as $option) {
      if (DB::$ROOM->IsOption($option) && OptionLoader::LoadFile($option)) {
	ArrayFilter::AddMerge($stack, OptionLoader::Load($option)->CastUserSubRole());
      }
    }
    Cast::Stack()->Set(Cast::DELETE, $stack);
  }

  //配役一覧出力用フィルター取得
  public static function GetCastMessageFilter() {
    //闇鍋モード判定
    if (OptionManager::ExistsChaos()) {
      foreach (OptionFilterData::$cast_message as $option) {
	if (DB::$ROOM->IsOption($option)) {
	  return OptionLoader::Load($option);
	}
      }

      //通知オプションが存在しない場合は通知なし
      return OptionLoader::Load('chaos_open_cast_none');
    } else {
      //通常村は完全公開
      return OptionLoader::Load('chaos_open_cast_full');
    }
  }

  //-- Room --//
  //霊界公開判定
  public static function IsRoomOpenCast() {
    //便宜上常時公開設定もオプションクラスは実装しているが、システム上はオプション未設定になる
    foreach (OptionFilterData::$room_open_cast as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return OptionLoader::Load($option)->IsRoomOpenCast();
      }
    }
    return true;
  }

  //ゲーム開始時シーン取得
  public static function GetRoomGameStartScene() {
    foreach (OptionFilterData::$room_game_start_scene as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return OptionLoader::Load($option)->GetRoomGameStartScene();
      }
    }
    return RoomScene::NIGHT;
  }
}

//-- オプションパーサ --//
class OptionParser {
  public $row;
  public $list = [];

  public function __construct($data) {
    $this->row  = $data;
    $this->list = self::Parse($this->row);
  }

  //取得
  public static function Get($game_option, $option_role = '') {
    return array_merge(self::Parse($game_option), self::Parse($option_role));
  }

  //パース
  private static function Parse($data) {
    $list = [];
    foreach (Text::Parse($data) as $option) {
      if (empty($option)) {
	continue;
      }

      $stack = Text::Parse($option, ':');
      $list[$stack[0]] = count($stack) > 1 ? array_slice($stack, 1) : true;
    }
    return $list;
  }
}

//-- オプションの基底クラス --//
abstract class Option {
  public $name;		//オプション名
  public $enable;	//有効判定 (サーバ設定)
  public $value;	//設定値
  public $group;	//オプショングループ
  public $type;		//オプション設定種別
  public $form_name;	//フォーム表示名
  public $form_value;	//フォーム表示設定値

  public function __construct() {
    if (true === $this->Ignore()) {
      return false;
    }

    $this->name = Text::CutPop(get_class($this), OptionLoader::CLASS_PREFIX);

    $enable  = sprintf('%s_enable',  $this->name);
    $default = sprintf('default_%s', $this->name);
    $this->enable = isset(GameOptionConfig::$$enable) ? GameOptionConfig::$$enable : true;
    if (false === isset($this->form_name)) {
      $this->form_name  = $this->name;
    }
    if (false === isset($this->form_value)) {
      $this->form_value = $this->value;
    }

    if (OptionManager::IsChange()) {
      switch ($this->type) {
      case OptionFormType::CHECKBOX:
      case OptionFormType::LIMITED_CHECKBOX:
      case OptionFormType::REALTIME:
	$this->value = DB::$ROOM->IsOption($this->name);
	break;
      }
    } elseif (isset(GameOptionConfig::$$default)) {
      $this->value = GameOptionConfig::$$default;
    }
    $this->LoadSource();
    $this->LoadFormList();
    $this->LoadValue();
    $this->LoadConfName();
    $this->FilterEnable();
  }

  //コンストラクタスキップ判定
  protected function Ignore() {
    return false;
  }

  //関連データロード
  protected function LoadSource() {}

  //フォームリストロード
  protected function LoadFormList() {}

  //初期値ロード
  protected function LoadValue() {}

  //セレクタ名ロード
  protected function LoadConfName() {}

  //無効上書き判定
  protected function FilterEnable() {}

  //フォームデータ取得
  abstract public function LoadPost();

  //フォームデータ取得スキップ判定
  protected function IgnorePost() {
    return false;
  }

  //スタックからデータ取得
  final protected function GetStack() {
    return RoomOption::$stack[$this->name];
  }

  //オプション名取得
  public function GetName() {
    return $this->GetCaption();
  }

  //キャプション取得
  abstract public function GetCaption();

  //説明文取得
  public function GetExplain() {
    return $this->GetCaption();
  }

  //村用キャプション取得
  final protected function GetRoomCaption() {
    return $this->GetCaption() . $this->GetRoomCaptionFooter();
  }

  //村用キャプションフッター取得
  protected function GetRoomCaptionFooter() {
    return '';
  }

  //個別設定値テキスト取得
  final protected function GetRoomCaptionConfig(...$stack) {
    $format = array_shift($stack);
    return ' ' . Text::Quote(vsprintf($format, $stack));
  }

  //説明リンク取得
  protected function GetURL() {
    return 'game_option.php#' . $this->name;
  }

  //オプション登録
  final protected function Set($name) {
    RoomOption::Set($this->group, $name);
  }

  //村用画像生成
  public function GenerateImage() {
    return ImageManager::Room()->Generate($this->name, $this->GetRoomCaption());
  }

  //村用オプション説明メッセージ生成
  public function GenerateRoomCaption() {
    $image   = $this->GenerateImage();
    $url     = $this->GetURL();
    $caption = $this->GetRoomCaption();
    $explain = $this->GetExplain();
    return OptionHTML::GenerateRoomCaption($image, $url, $caption, $explain);
  }

  //ユーザーサブ役職配役処理
  public function CastUserSubRole() {
    return $this->CastUserSubRoleAll();
  }

  //ユーザーサブ役職配役 (全員)
  final protected function CastUserSubRoleAll() {
    $list = Cast::Stack()->Get(Cast::CAST);
    foreach (array_keys($list) as $id) {
      if ($this->IgnoreCastUserSubRoleAll($id)) {
	continue;
      }
      $list[$id] .= ' ' . $this->GetCastUserSubRoleAllRole($id);
    }
    Cast::Stack()->Set(Cast::CAST, $list);
    return $this->GetResultCastUserSubRoleList();
  }

  //配役スキップ判定 (ユーザーサブ役職配役/全員)
  protected function IgnoreCastUserSubRoleAll($id) {
    return false;
  }

  //役職取得 (ユーザーサブ役職配役/全員)
  protected function GetCastUserSubRoleAllRole($id) {
    return $this->name;
  }

  //ユーザーサブ役職配役 (一人限定)
  final protected function CastUserSubRoleOnce($str = '') {
    $rand = Cast::Stack()->Get(Cast::RAND);
    $list = Cast::Stack()->Get(Cast::CAST);

    $list[array_pop($rand)] .= ' ' . $this->name . $str;

    Cast::Stack()->Set(Cast::RAND, $rand);
    Cast::Stack()->Set(Cast::CAST, $list);
    return $this->GetResultCastUserSubRoleList();
  }

  //配役済みユーザーサブ役職リスト取得
  protected function GetResultCastUserSubRoleList() {
    return [$this->name];
  }
}

//-- チェックボックス型 --//
abstract class OptionCheckbox extends Option {
  public $group = OptionGroup::ROLE;
  public $type  = OptionFormType::CHECKBOX;
  public $form_value = Switcher::ON;

  public function LoadPost() {
    if (true === $this->IgnorePost()) {
      return false;
    }

    RQ::Get()->ParsePostOn($this->name);
    if (RQ::Get()->{$this->name}) {
      $this->Set($this->name);
    }
  }

  protected function GetRoomCaptionFooter() {
    if (isset(CastConfig::${$this->name}) && is_int(CastConfig::${$this->name})) {
      return $this->GetRoomCaptionConfig('%d人～', CastConfig::${$this->name});
    } else {
      return '';
    }
  }
}

//-- チェックボックス型(制限付き) --//
abstract class OptionLimitedCheckbox extends OptionCheckbox {
  public $type = OptionFormType::LIMITED_CHECKBOX;

  public function LoadPost() {
    if (true === $this->IgnorePost()) {
      return false;
    }

    RQ::Get()->ParsePostOn($this->name);
    if (false === RQ::Get()->{$this->name}) {
      return false;
    }

    $post = sprintf('%s_count', $this->name);
    RQ::Get()->ParsePostInt($post);
    $count = RQ::Get()->$post;
    if ($count < 1 || 99 < $count) {
      RoomManagerHTML::OutputResult('limit_over', $this->GetName());
    }
    $this->Set(sprintf('%s:%d', $this->name, $count));
  }

  public function GenerateImage() {
    $str = sprintf('[%d]', ArrayFilter::Pick($this->GetStack()));
    return ImageManager::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
  }

  public function GenerateRoomCaption() {
    $image   = $this->GenerateImage();
    $url     = $this->GetURL();
    $caption = $this->GetCaption();
    $explain = $this->GetExplain() . $this->GetRoomCaptionFooter();
    return OptionHTML::GenerateRoomCaption($image, $url, $caption, $explain);
  }

  //制限設定値取得
  final public function GetLimitedCount() {
    if (OptionManager::IsChange() && DB::$ROOM->IsOption($this->name)) {
      switch ($this->group) {
      case OptionGroup::GAME:
	$list = DB::$ROOM->game_option->list;
	break;

      case OptionGroup::ROLE:
	$list = DB::$ROOM->option_role->list;
	break;
      }
      return ArrayFilter::Pick($list[$this->name]);
    } else {
      return $this->GetDefaultLimitedCount();
    }
  }

  //制限の初期設定値取得
  protected function GetDefaultLimitedCount() {
    return 0;
  }

  //制限値設定フォームメッセージ取得
  public function GetLimitedFormCaption() {
    return '';
  }

  //村用キャプション追加メッセージ取得
  protected function GetRoomCaptionFooter() {
    $format = $this->GetRoomCaptionFooterFormat();
    return $this->GetRoomCaptionConfig($format, ArrayFilter::Pick($this->GetStack()));
  }

  //村用キャプション追加メッセージフォーマット取得
  protected function GetRoomCaptionFooterFormat() {
    return '%s';
  }
}

//-- チェックボックス型(特殊配役型) --//
abstract class OptionCastCheckbox extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  //配役有効判定
  public function EnableCast($user_count) {
    return true;
  }

  //配役取得
  public function GetCastRole($user_count) {
    return $this->FilterCast($user_count, $this->GetFilterCastRoleList());
  }

  //配役フィルタリングリスト取得
  protected function GetFilterCastRoleList() {
    return [];
  }

  //配役フィルタリング処理
  final protected function FilterCast($count, array $filter) {
    $stack = [];
    foreach (CastConfig::$role_list[$count] as $key => $value) {
      $role = 'human';
      foreach ($filter as $set_role => $target_role) {
	if (Text::Search($key, $target_role)) {
	  $role = is_int($set_role) ? $target_role : $set_role;
	  break;
	}
      }
      ArrayFilter::Add($stack, $role, $value);
    }
    return $this->ReplaceFilterCast($stack);
  }

  //配役フィルタリング置換処理
  protected function ReplaceFilterCast(array $role_list) {
    return $role_list;
  }

  //村人置換有効判定
  public function EnableReplaceRole() {
    return true;
  }
}

//-- テキスト入力型 --//
abstract class OptionText extends Option {
  public $group = OptionGroup::NONE;
  public $type  = OptionFormType::TEXT;

  public function LoadPost() {
    if (true === $this->IgnorePost()) {
      return false;
    }

    RQ::Get()->ParsePost('Escape', $this->name);
  }
}

//-- セレクタ型 --//
abstract class OptionSelector extends Option {
  public $group = OptionGroup::ROLE;
  public $type  = OptionFormType::SELECTOR;
  public $label = 'モード名';
  public $source;
  public $conf_name;
  public $item_list;
  public $form_list = [];
  public $on_change = '';

  protected function LoadSource() {
    $this->source = sprintf('%s_list', $this->name);
  }

  public function LoadPost() {
    if (true === $this->IgnorePost()) {
      return false;
    }

    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) {
      return false;
    }

    $post = RQ::Get()->{$this->name};
    $flag = in_array($post, $this->form_list);
    if (true === $flag) {
      RQ::Set($post, $flag);
      $this->Set($post);
    }
    RQ::Set($this->name, $flag);
  }

  //個別データ取得
  public function GetItem() {
    if (false === isset($this->item_list)) {
      $this->item_list = [];
      $stack = is_array($this->conf_name) ? $this->conf_name : GameOptionConfig::${$this->source};
      if (isset($stack)) {
	foreach ($stack as $key => $value) {
	  if (is_string($key)) {
	    if ($this->IsEnable($key)) {
	      $this->item_list[$key] = $value;
	    }
	  } elseif (is_string($value)) {
	    $item = OptionLoader::Load($value);
	    if (isset($item) && $item->enable) {
	      $this->item_list[$item->name] = $item;
	    }
	  } else {
	    $this->item_list[] = $value;
	  }
	}
      }
    }

    return $this->item_list;
  }

  //選択値セット
  protected function SetFormValue($type) {
    foreach ($this->form_list as $key => $value) {
      if ($type == 'int' && false === is_int($key)) {
	continue;
      }

      if (DB::$ROOM->IsOption($type == 'key' ? $key : $value)) {
	$this->value = $value;
	break;
      }
    }
  }

  //有効判定
  private function IsEnable($name) {
    $enable = sprintf('%s_enable', $name);
    return isset(GameOptionConfig::$$enable) ? GameOptionConfig::$$enable : true;
  }
}
