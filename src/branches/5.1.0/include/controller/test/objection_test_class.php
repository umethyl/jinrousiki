<?php
//-- 異議ありテストコントローラー --//
final class ObjectionTestController extends JinrouTestController {
  protected static function LoadRequest() {
    DevHTML::LoadRequest();
  }

  protected static function OutputHeader() {
    HTML::OutputHeader(ObjectionTestMessage::TITLE, null, true);
    ObjectionTestHTML::OutputForm(self::GetList());
  }

  protected static function IsExecute() {
    return DevHTML::IsExecute();
  }

  protected static function RunTest() {
    $id  = RequestDataTalk::OBJECTION;
    RQ::Get()->ParsePostData($id);
    $key = RQ::Get()->$id;
    if (in_array($key, self::GetList())) {
      Text::p(ObjectionTestMessage::$$key);
      SoundHTML::Output($key);
    }
  }

  //音声リスト取得
  private static function GetList() {
    return [
      'entry', 'full', 'morning', 'night', 'vote_success', 'revote', 'novote', 'alert',
      'objection_male', 'objection_female'
    ];
  }
}
