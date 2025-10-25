<?php
/*
  ◆サブ役職制限 (セレクタ)
*/
class Option_sub_role_limit extends OptionSelector {
  public $type = OptionFormType::GROUP;

  protected function LoadFormList() {
    $stack = ['no_sub_role' => 'no_sub_role'];
    foreach (['easy', 'normal', 'hard'] as $name) {
      $stack[$name] = sprintf('%s_%s', $this->name, $name);
    }

    foreach ($stack as $name => $option) {
      $filter = OptionLoader::Load($option);
      if (isset($filter) && $filter->enable) {
	$this->form_list[$option] = $name;
      }
    }
  }

  protected function LoadValue() {
    if (RoomOptionManager::IsChange()) {
      $this->SetFormValue('key');
    }
  }

  public function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (null === RQ::Get()->{$this->name}) {
      return false;
    }

    $post = RQ::Get()->{$this->name};
    foreach ($this->form_list as $option => $value) {
      if ($value == $post) {
	RQ::Set($option, true);
	array_push(RoomOptionLoader::${$this->group}, $option);
	break;
      }
    }
  }

  public function GetItem() {
    $stack = [
      'no_sub_role' => OptionLoader::Load('no_sub_role'),
      'easy'        => OptionLoader::Load('sub_role_limit_easy'),
      'normal'      => OptionLoader::Load('sub_role_limit_normal'),
      'hard'        => OptionLoader::Load('sub_role_limit_hard'),
      ''            => OptionLoader::Load('sub_role_limit_none')
    ];
    foreach ($stack as $key => $item) {
      $item->form_name  = $this->form_name;
      $item->form_value = $key;
    }
    if (isset($stack[$this->value])) {
      $stack[$this->value]->value = true;
    }

    return $stack;
  }

  public function GetCaption() {
    return 'サブ役職制限';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }
}
