<?php
//-- 村作成オプションの基底クラス --//
abstract class RoomOptionItem {
  public $name;
  public $class;
  public $enable;
  public $value;
  public $type;
  public $form_name;
  public $form_value;

  public function __construct() {
    $this->name = array_pop(explode('Option_', get_class($this)));

    $enable  = sprintf('%s_enable',  $this->name);
    $default = sprintf('default_%s', $this->name);
    $this->enable = isset(GameOptionConfig::$$enable) ? GameOptionConfig::$$enable : true;
    if (OptionManager::IsChange() && ($this->type == 'checkbox' || $this->type == 'limit_talk')) {
      $this->value = DB::$ROOM->IsOption($this->name);
    }
    elseif (isset(GameOptionConfig::$$default)) {
      $this->value = GameOptionConfig::$$default;
    }

    if (! isset($this->form_name))  $this->form_name  = $this->name;
    if (! isset($this->form_value)) $this->form_value = $this->value;
  }

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

  //キャプション取得 (村用)
  protected function GetRoomCaption () {
    return $this->GetCaption();
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
    return Image::Room()->Generate($this->name, $this->GetRoomCaption());
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
    $rand = Cast::Stack()->Get('rand');
    $list = Cast::Stack()->Get('fix_role');

    $list[array_pop($rand)] .= ' ' . $this->name . $str;

    Cast::Stack()->Set('rand', $rand);
    Cast::Stack()->Set('fix_role', $list);
    return $this->GetResultCastList();
  }

  //配役処理 (全員)
  final protected function CastAll() {
    $list = Cast::Stack()->Get('fix_role');
    foreach (array_keys($list) as $id) {
      if ($this->IgnoreCastAll($id)) continue;
      $list[$id] .= ' ' . $this->GetCastAllRole($id);
    }
    Cast::Stack()->Set('fix_role', $list);
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
    return array($this->name);
  }
}

//-- チェックボックス型 --//
abstract class CheckRoomOptionItem extends RoomOptionItem {
  public $group = RoomOption::ROLE_OPTION;
  public $type  = 'checkbox';
  public $form_value = 'on';

  public function LoadPost() {
    if ($this->IgnorePost()) return false;
    RQ::Get()->ParsePostOn($this->name);
    if (RQ::Get()->{$this->name}) $this->Set($this->name);
  }

  protected function GetRoomCaption() {
    $str = parent::GetRoomCaption();
    if (isset(CastConfig::${$this->name}) && is_int(CastConfig::${$this->name})) {
      $str .= sprintf(' (%d人～)', CastConfig::${$this->name});
    }
    return $str;
  }
}

//-- セレクタ型 --//
abstract class SelectorRoomOptionItem extends RoomOptionItem {
  public $group = RoomOption::ROLE_OPTION;
  public $type  = 'selector';
  public $label = 'モード名';
  public $conf_name;
  public $source;
  public $item_list;
  public $form_list = array();
  public $on_change = '';

  public function __construct() {
    parent::__construct();
    $this->source = sprintf('%s_list', $this->name);
  }

  public function LoadPost() {
    if ($this->IgnorePost()) return false;
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) return false;

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
    if (! isset($this->item_list)) {
      $this->item_list = array();
      $stack = is_array($this->conf_name) ? $this->conf_name : GameOptionConfig::${$this->source};
      if (isset($stack)) {
	foreach ($stack as $key => $value) {
	  if (is_string($key)) {
	    if ($this->IsEnable($key)) $this->item_list[$key] = $value;
	  }
	  elseif (is_string($value)) {
	    $item = OptionManager::GetClass($value);
	    if (isset($item) && $item->enable) $this->item_list[$item->name] = $item;
	  }
	  else {
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
      if ($type == 'int' && ! is_int($key)) continue;

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

//-- テキスト入力型 --//
abstract class TextRoomOptionItem extends RoomOptionItem {
  public $group = RoomOption::NOT_OPTION;
  public $type  = 'textbox';

  public function LoadPost() {
    if ($this->IgnorePost()) return false;
    RQ::Get()->ParsePost('Escape', $this->name);
  }
}
