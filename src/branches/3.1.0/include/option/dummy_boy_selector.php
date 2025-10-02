<?php
/*
  ◆初日の夜は身代わり君 (セレクタ)
*/
class Option_dummy_boy_selector extends OptionSelector {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::GROUP;
  public $form_list = array('dummy_boy' => Switcher::ON, 'gm_login' => 'gm_login');

  protected function LoadValue() {
    $this->value = GameOptionConfig::$default_dummy_boy;
  }

  public function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) return false;

    $post = RQ::Get()->{$this->name};
    foreach ($this->form_list as $option => $form_value) {
      if ($post == $form_value) {
	OptionLoader::Load($option)->LoadPost();
	break;
      }
    }
  }

  public function GetItem() {
    $stack = array();

    //-- 身代わり君なし --//
    $item = OptionLoader::Load('no_dummy_boy');
    if ($item->enable) {
      $this->UpdateForm($item, '');
      $stack[''] = $item;
    }

    //-- 身代わり君有り --//
    foreach ($this->form_list as $option => $form_value) {
      $item = OptionLoader::Load($option);
      if ($item->enable) {
	$stack[$form_value] = $item;
      }
    }

    foreach ($stack as $form_value => $item) {
      $this->UpdateForm($item, $form_value);
    }

    //-- 身代わり君変更 --//
    $option = 'gm_logout';
    $item   = OptionLoader::Load($option);
    if ($item->enable) {
      $this->UpdateForm($item, $option);
      $stack[$option] = $item;
    }

    //-- チェック位置判定 --//
    if (OptionManager::IsChange() && DB::$ROOM->IsOption('gm_login')) {
      $stack['gm_login']->value = true;
    } elseif (isset($stack[$this->value])) {
      $stack[$this->value]->value = true;
    } else {
      $stack['']->value = true;
    }

    return $stack;
  }

  public function GetCaption() {
    return '初日の夜は身代わり君';
  }

  public function GetExplain() {
    return '配役は<a href="info/rule.php">ルール</a>を確認して下さい';
  }

  private function UpdateForm(Option $item, $form_value) {
    $item->form_name  = $this->form_name;
    $item->form_value = $form_value;
  }
}
