<?php
/*
  ◆アイコン一覧 (icon_view)
  ○仕様
    icon_no, category, appearance, author : アイコン情報 (user_icon)
*/
RQ::LoadFile('request_icon');
class Request_icon_view extends RequestIcon {
  public function __construct() {
    $this->GetIconData();
    $this->ParseGetOn(RequestDataIcon::MULTI);
    $this->ParseGetInt(RequestDataIcon::ID);
    $this->ParseGetData(
      RequestDataIcon::CATEGORY, RequestDataIcon::APPEARANCE, RequestDataIcon::AUTHOR
    );
    $this->room_no = null;
  }
}
