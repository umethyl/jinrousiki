<?php
//-- 異議ありテストコントローラー --//
final class ObjectionTestController extends JinrouController {
  protected static function Load() {
    DevHTML::LoadRequest();
  }

  protected static function Output() {
    HTML::OutputHeader(ObjectionTestMessage::TITLE, null, true);
    ObjectionTestHTML::OutputForm(self::GetList());
    if (DevHTML::IsExecute()) {
      self::RunTest();
    }
    HTML::OutputFooter();
  }

  //テスト実行
  private static function RunTest() {
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
