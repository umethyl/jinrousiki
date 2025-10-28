<?php
/*
  ◆icon 用共通クラス (Icon)
  ○仕様
    icon_no, icon_name, category, appearance, author, color : アイコン情報 (user_icon)
    search, keyword, sort_by_name : 検索情報
    page : ページ番号
*/
class RequestIcon extends Request {
  public function __construct() {
    Encoder::Post();
    $this->ParsePostInt(RequestDataIcon::ID);
    $this->ParsePostStr(
      RequestDataIcon::NAME,   RequestDataIcon::CATEGORY, RequestDataIcon::APPEARANCE,
      RequestDataIcon::AUTHOR, RequestDataIcon::COLOR
    );
    $this->ParsePost('Exists', 'search');
  }

  protected function GetIconData() {
    $this->ParseRequest('IsOn', 'sort_by_name');
    $this->ParseRequest('Escape',
      RequestDataIcon::CATEGORY, RequestDataIcon::APPEARANCE, RequestDataIcon::AUTHOR,
      RequestDataIcon::KEYWORD
    );
    $this->ParseRequest('Exists', 'search');
    $this->ParseRequest('SetPage', 'page');
  }
}
