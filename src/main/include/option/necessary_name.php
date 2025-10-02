<?php
/*
  ◆ユーザ名必須 (necessary_name)
  ○仕様
  ・ユーザー登録：ユーザー名設定必須
*/
class Option_necessary_name extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  protected function Ignore() {
    return false === GameConfig::TRIP;
  }

  protected function IgnorePost() {
    return $this->Ignore();
  }

  public function GetCaption() {
    return 'ユーザ名必須';
  }

  public function GetExplain() {
    return 'トリップのみのユーザ名登録はできません';
  }

  //ユーザー名入力欄注意事項取得
  public function GetUserEntryUnameWarning() {
    //トリップ必須 (necessary_trip) と同時設定 > 単独設定
    if (DB::$ROOM->IsOption('necessary_trip')) {
      $message = UserManagerMessage::NECESSARY_NAME_TRIP;
    } else {
      $message = UserManagerMessage::NECESSARY_NAME;
    }
    return Text::BR . HTML::GenerateSpan($message);
  }

  //ユーザー名入力エラーチェック
  public function ValidateUserEntryUname($uname) {
    if (Text::IsPrefix($uname, Message::TRIP)) {
      throw new UnexpectedValueException(UserManagerMessage::ERROR_INPUT_UNAME);
    }
  }
}
