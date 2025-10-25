<?php
/*
  ◆トリップ必須 (necessary_trip)
  ○仕様
  ・ユーザー登録：トリップ設定必須
*/
class Option_necessary_trip extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  protected function Ignore() {
    return false === GameConfig::TRIP;
  }

  protected function IgnorePost() {
    return $this->Ignore();
  }

  public function GetCaption() {
    return 'トリップ必須';
  }

  public function GetExplain() {
    return 'ユーザ登録名にトリップが必須です';
  }

  //ユーザー名入力欄注意事項取得
  public function GetUserEntryUnameWarning() {
    //ユーザ名必須と同時設定時の処理は necessary_name に委譲
    return Text::BR . HTML::GenerateSpan(UserManagerMessage::NECESSARY_TRIP);
  }

  //ユーザー名入力エラーチェック
  public function ValidateUserEntryUname($uname) {
    if (false === Text::Search($uname, Message::TRIP)) {
      throw new UnexpectedValueException(UserManagerMessage::ERROR_INPUT_TRIP);
    }
  }
}
