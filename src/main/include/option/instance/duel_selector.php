<?php
/*
  ◆決闘村 (セレクタ)
  ○仕様
  ・モードリスト：GameOptionCofing::$duel_list
*/
class Option_duel_selector extends OptionSelector {
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
    return '決闘村';
  }

  public function GetExplain() {
    return '短期決戦を想定した専用配役セットです';
  }

  public function GenerateImage() {
    $str  = $this->GetRoomImageFooter();
    $name = Text::CutPick($this->name);
    return ImageManager::Room()->Generate($name, $this->GetRoomCaption()) . $str;
  }

  public function GenerateRoomCaption() {
    $image   = $this->GenerateImage();
    $url     = sprintf('%s_%s', $this->GetURL(), $this->GetRoomType());
    $caption = $this->GetCaption() . $this->GetRoomImageFooter();
    $explain = $this->GetExplain() . $this->GetRoomCaptionFooter();
    return OptionHTML::GenerateRoomCaption($image, $url, $caption, $explain);
  }

  //村用個別オプション取得
  protected function GetRoomType() {
    return ArrayFilter::Pick($this->GetStack());
  }

  protected function GetRoomCaptionFooter() {
    $item = $this->GetItem();
    return $this->GetRoomCaptionConfig('Type%s', $item[$this->GetRoomType()]);
  }

  //村用画像追加メッセージ取得
  protected function GetRoomImageFooter() {
    return Text::QuoteBracket(strtoupper($this->GetRoomType()));
  }
}
