<?php
/*
  ◆固定配役追加モード (topping)
*/
class Option_topping extends OptionSelector {
  use OptionChaosRole;
  use OptionChaosTopping;

  protected function LoadFormList() {
    $this->form_list = GameOptionConfig::${$this->source};
  }

  protected function LoadValue() {
    if (RoomOptionManager::IsChange() && DB::$ROOM->IsOption($this->name)) {
      $this->value = DB::$ROOM->option_role->list[$this->name][0];
    }
  }

  public function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (null === RQ::Get()->{$this->name}) {
      return false;
    }

    $post = RQ::Get()->{$this->name};
    $flag = (false === empty($post)) && isset($this->form_list[$post]);
    if (true === $flag) {
      array_push(RoomOptionLoader::${$this->group}, sprintf('%s:%s', $this->name, $post));
    }
    RQ::Set($this->name, $flag);
  }

  public function GetCaption() {
    return '固定配役追加モード';
  }

  public function GetExplain() {
    return '固定配役に追加する役職セットです';
  }

  protected function GetRoomCaptionFooter() {
    $item = $this->GetItem();
    return $this->GetRoomCaptionConfig('Type%s', $item[$this->GetRoomType()]);
  }
}
