<?php
/*
  ◆霊界で配役を公開しない (セレクタ)
*/
class Option_not_open_cast_selector extends OptionSelector {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::GROUP;
  public $form_list = array('not_open_cast', 'auto_open_cast');

  protected function LoadValue() {
    $this->value = GameOptionConfig::$default_not_open_cast;
    if (OptionManager::IsChange()) $this->SetFormValue('value');
  }

  public function GetItem() {
    $stack = array('' => OptionLoader::Load('not_close_cast'));
    foreach ($this->form_list as $option) {
      $item = OptionLoader::Load($option);
      if ($item->enable) {
	$stack[$option] = $item;
      }
    }

    foreach ($stack as $form_value => $item) {
      $item->value      = false;
      $item->form_name  = $this->form_name;
      $item->form_value = $form_value;
    }

    if (isset($stack[$this->value])) { //チェック位置判定
      $stack[$this->value]->value = true;
    } else {
      $stack['']->value = true;
    }

    return $stack;
  }

  public function GetCaption() {
    return '霊界で配役を公開しない';
  }
}
