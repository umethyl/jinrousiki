<?php
/*
  ◆固定配役追加モード (topping)
*/
class Option_topping extends SelectorRoomOptionItem {
  function  __construct() {
    parent::__construct();
    $this->form_list = GameOptionConfig::${$this->source};
    if (OptionManager::$change && DB::$ROOM->IsOption($this->name)) {
      $this->value = DB::$ROOM->option_role->list[$this->name][0];
    }
  }

  function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) return false;

    $post = RQ::Get()->{$this->name};
    $flag = ! empty($post) && array_key_exists($post, $this->form_list);
    if ($flag) array_push(RoomOption::${$this->group}, sprintf('%s:%s', $this->name, $post));
    RQ::Set($this->name, $flag);
  }

  function GetCaption() { return '固定配役追加モード'; }

  function GetExplain() { return '固定配役に追加する役職セットです'; }

  function GenerateImage() {
    $str = $this->GetRoomImageFooter();
    return Image::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
  }

  function GenerateRoomCaption() {
    $format  = '<div>%s：<a href="info/%s">%s</a>：%s</div>' . Text::LF;
    $image   = $this->GenerateImage();
    $url     = sprintf('%s_%s', $this->GetURL(), $this->GetRoomType());
    $caption = parent::GetRoomCaption() . $this->GetRoomImageFooter();
    $explain = $this->GetExplain() . $this->GetRoomCaptionFooter();
    return sprintf($format, $image, $url, $caption, $explain);
  }

  protected function GetRoomCaption() {
    return parent::GetRoomCaption() . $this->GetRoomCaptionFooter();
  }

  protected function GetURL() { return 'chaos.php#' . $this->name; }

  //村用個別オプション取得
  protected function GetRoomType() { return array_shift($this->GetStack()); }

  //村用キャプション追加メッセージ取得
  protected function GetRoomCaptionFooter() {
    $item = $this->GetItem();
    return sprintf(' (Type%s)', $item[$this->GetRoomType()]);
  }

  //村用画像追加メッセージ取得
  protected function GetRoomImageFooter() {
    return sprintf('[%s]', strtoupper($this->GetRoomType()));
  }
}
