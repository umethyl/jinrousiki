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

  //オプションクラスロード
  public static function GetFilter($type) {
    foreach (OptionFilterData::$$type as $option) {
      if (DB::$ROOM->IsOption($option)) {
	return OptionLoader::Load($option);
      }
    }
    return null;
  }

  //特殊普通村の配役処理
  public static function SetRole(array &$list, $count) {
    foreach (OptionFilterData::$add_role as $option) {
      if (DB::$ROOM->IsOption($option) && OptionLoader::LoadFile($option)) {
	OptionLoader::Load($option)->SetRole($list, $count);
      }
    }
  }

  //ユーザ配役処理
  public static function Cast() {
    $stack = Cast::Stack()->Get(Cast::DELETE);
    foreach (OptionFilterData::$add_sub_role as $option) {
      if (DB::$ROOM->IsOption($option) && OptionLoader::LoadFile($option)) {
	ArrayFilter::AddMerge($stack, OptionLoader::Load($option)->Cast());
      }
    }
    Cast::Stack()->Set(Cast::DELETE, $stack);
  }

  //役職置換処理
  public static function Replace(array &$list, $base, $target) {
    if (ArrayFilter::GetInt($list, $base) < 1) {
      return false;
    }
    ArrayFilter::Replace($list, $base, $target);
    return true;
  }

  //闇鍋固定枠追加
  public static function FilterChaosFixRole(array &$list) {
    foreach (OptionFilterData::$chaos_fix_role as $option) {
      if (DB::$ROOM->IsOption($option)) {
	OptionLoader::Load($option)->FilterChaosFixRole($list);
      }
    }
  }

  //ゲルト君モード有効判定
  public static function EnableGerd($role = 'human') {
    $option = 'gerd';
    return DB::$ROOM->IsOption($option) && OptionLoader::Load($option)->EnableGerd($role);
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
  public $name;
  public $class;
  public $enable;
  public $value;
  public $type;
  public $form_name;
  public $form_value;

  public function __construct() {
    if ($this->Ignore()) {
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

  //配役処理
  public function Cast() {
    return $this->CastAll();
  }

  //配役処理 (一人限定)
  final protected function CastOnce($str = '') {
    $rand = Cast::Stack()->Get(Cast::RAND);
    $list = Cast::Stack()->Get(Cast::CAST);

    $list[array_pop($rand)] .= ' ' . $this->name . $str;

    Cast::Stack()->Set(Cast::RAND, $rand);
    Cast::Stack()->Set(Cast::CAST, $list);
    return $this->GetResultCastList();
  }

  //配役処理 (全員)
  final protected function CastAll() {
    $list = Cast::Stack()->Get(Cast::CAST);
    foreach (array_keys($list) as $id) {
      if ($this->IgnoreCastAll($id)) {
	continue;
      }
      $list[$id] .= ' ' . $this->GetCastAllRole($id);
    }
    Cast::Stack()->Set(Cast::CAST, $list);
    return $this->GetResultCastList();
  }

  //配役スキップ判定
  protected function IgnoreCastAll($id) {
    return false;
  }

  //役職取得
  protected function GetCastAllRole($id) {
    return $this->name;
  }

  //配役済み役職リスト取得
  protected function GetResultCastList() {
    return [$this->name];
  }
}

//-- チェックボックス型 --//
abstract class OptionCheckbox extends Option {
  public $group = OptionGroup::ROLE;
  public $type  = OptionFormType::CHECKBOX;
  public $form_value = Switcher::ON;

  public function LoadPost() {
    if ($this->IgnorePost()) {
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

//-- テキスト入力型 --//
abstract class OptionText extends Option {
  public $group = OptionGroup::NONE;
  public $type  = OptionFormType::TEXT;

  public function LoadPost() {
    if ($this->IgnorePost()) {
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
  public $conf_name;
  public $source;
  public $item_list;
  public $form_list = [];
  public $on_change = '';

  protected function LoadSource() {
    $this->source = sprintf('%s_list', $this->name);
  }

  public function LoadPost() {
    if ($this->IgnorePost()) {
      return false;
    }

    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) {
      return false;
    }

    $post = RQ::Get()->{$this->name};
    $flag = in_array($post, $this->form_list);
    if ($flag) {
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
