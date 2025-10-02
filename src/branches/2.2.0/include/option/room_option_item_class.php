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

  function __construct() {
    $this->name = array_pop(explode('Option_', get_class($this)));

    $enable  = sprintf('%s_enable',  $this->name);
    $default = sprintf('default_%s', $this->name);
    $this->enable = isset(GameOptionConfig::$$enable) ? GameOptionConfig::$$enable : true;
    if (OptionManager::$change && $this->type == 'checkbox') {
      $this->value = DB::$ROOM->IsOption($this->name);
    }
    elseif (isset(GameOptionConfig::$$default)) {
      $this->value = GameOptionConfig::$$default;
    }

    if (! isset($this->form_name))  $this->form_name  = $this->name;
    if (! isset($this->form_value)) $this->form_value = $this->value;
  }

  //フォームデータ取得
  abstract function LoadPost();

  //オプション名取得
  function GetName() { return $this->GetCaption(); }

  //キャプション取得
  abstract function GetCaption();

  //説明文取得
  function GetExplain() { return $this->GetCaption(); }

  //村用画像生成
  function GenerateImage() {
    return Image::Room()->Generate($this->name, $this->GetRoomCaption());
  }

  //村用オプション説明メッセージ生成
  function GenerateRoomCaption() {
    $format  = '<div>%s：<a href="info/%s">%s</a>：%s</div>' . Text::LF;
    $image   = $this->GenerateImage();
    $url     = $this->GetURL();
    $caption = $this->GetRoomCaption();
    $explain = $this->GetExplain();
    return sprintf($format, $image, $url, $caption, $explain);
  }

  //配役処理 (一人限定)
  function CastOnce(array &$list, &$rand, $str = '') {
    $list[array_pop($rand)] .= ' ' . $this->name . $str;
    return array($this->name);
  }

  //配役処理 (全員)
  function CastAll(array &$list) {
    foreach (array_keys($list) as $id) {
      $list[$id] .= ' ' . $this->name;
    }
    return array($this->name);
  }

  //スタックからデータ取得
  protected function GetStack() { return RoomOption::$stack[$this->name]; }

  //キャプション取得 (村用)
  protected function GetRoomCaption () { return $this->GetCaption(); }

  //説明リンク取得
  protected function GetURL() { return 'game_option.php#' . $this->name; }
}

//-- チェックボックス型 --//
abstract class CheckRoomOptionItem extends RoomOptionItem {
  public $group = RoomOption::ROLE_OPTION;
  public $type  = 'checkbox';
  public $form_value = 'on';

  function LoadPost() {
    RQ::Get()->ParsePostOn($this->name);
    if (RQ::Get()->{$this->name}) array_push(RoomOption::${$this->group}, $this->name);
    return RQ::Get()->{$this->name};
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

  function __construct() {
    parent::__construct();
    $this->source = sprintf('%s_list', $this->name);
  }

  function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) return false;

    $post = RQ::Get()->{$this->name};
    $flag = in_array($post, $this->form_list);
    if ($flag) {
      RQ::Set($post, $flag);
      array_push(RoomOption::${$this->group}, $post);
    }
    RQ::Set($this->name, $flag);
  }

  //個別データ取得
  function GetItem() {
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

  function LoadPost() { RQ::Get()->ParsePost('Escape', $this->name); }
}
