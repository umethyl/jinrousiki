<?php
/*
  ◆配役を通知する (セレクタ)
*/
class Option_chaos_open_cast extends OptionSelector {
  public $type = OptionFormType::GROUP;

  protected function LoadFormList() {
    foreach (['camp', 'role', 'full'] as $name) {
      $option = sprintf('%s_%s', $this->name, $name);
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
      ''     => OptionLoader::Load('chaos_open_cast_none'),
      'camp' => OptionLoader::Load('chaos_open_cast_camp'),
      'role' => OptionLoader::Load('chaos_open_cast_role'),
      'full' => OptionLoader::Load('chaos_open_cast_full')
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
    return '配役を通知する';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }
}
