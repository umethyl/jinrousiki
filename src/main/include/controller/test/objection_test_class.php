<?php
//-- 異議ありテストコントローラー --//
final class ObjectionTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function LoadRequestExtra() {
    DevHTML::LoadRequest();
  }

  protected static function OutputRunHeader() {
    HTML::OutputHeader(ObjectionTestMessage::TITLE, null, true);
    ObjectionTestHTML::OutputForm(self::GetList());
  }

  protected static function EnableCommand() {
    return DevHTML::IsExecute();
  }

  protected static function RunCommand() {
    $id  = RequestDataTalk::OBJECTION;
    RQ::Fetch()->ParsePostData($id);
    $key = RQ::Get($id);
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

  protected static function OutputRunFooter() {
    HTML::OutputFooter();
  }
}
