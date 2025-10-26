<?php
/*
  ◆村人登録 (user_manager)
  ○仕様
*/
RQ::LoadFile('request_icon');
class Request_user_manager extends RequestIcon {
  public function __construct() {
    Encoder::Post();
    $this->ParseGetRoomNo();
    $this->ParseGetInt(RequestDataUser::ID);
    $this->ParsePostInt(RequestDataIcon::ID);
    $this->ParsePostOn(RequestDataUser::LOGIN);
    $this->ParsePostStr(RequestDataUser::PASSWORD);
    $this->ParsePostData(
      RequestDataUser::TRIP, RequestDataUser::SEX, RequestDataUser::PROFILE, RequestDataUser::ROLE
    );
    $this->ParsePost('Exists', 'entry');
    $this->GetIconData();
    Text::Escape($this->profile, false);
    if ($this->entry) {
      $this->ParsePost('Trip', RequestDataUser::UNAME, RequestDataUser::HN);
    } else {
      $this->ParsePostStr(RequestDataUser::UNAME, RequestDataUser::TRIP, RequestDataUser::HN);
    }
  }

  //バックリンクに含めないデータを返す
  public function GetIgnoreError() {
    return [
      'entry',
      RequestDataIcon::CATEGORY,
      RequestDataIcon::APPEARANCE,
      RequestDataIcon::AUTHOR
    ];
  }

  //DB情報からポストデータを登録する
  public function StorePost(array $stack) {
    foreach ($stack as $key => $value) {
      if (property_exists($this, $key)) {
        RQ::Set($key, $value);
      }
    }
  }
}
