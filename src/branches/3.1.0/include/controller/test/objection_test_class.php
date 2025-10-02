<?php
//-- 異議ありテスト --//
class ObjectionTest {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    DevHTML::LoadRequest();
  }

  //出力
  private static function Output() {
    HTML::OutputHeader(ObjectionTestMessage::TITLE, null, true);
    ObjectionTestHTML::OutputForm(self::GetList());
    if (DevHTML::IsExecute()) self::RunTest();
    HTML::OutputFooter();
  }

  //テスト実行
  private static function RunTest() {
    Loader::LoadFile('sound_class');
    $id  = 'set_objection';
    RQ::Get()->ParsePostData($id);
    $key = RQ::Get()->$id;
    if (in_array($key, self::GetList())) {
      Text::p(ObjectionTestMessage::$$key);
      SoundHTML::Output($key);
    }
  }

  //音声リスト取得
  private static function GetList() {
    return array(
      'entry', 'full', 'morning', 'night', 'vote_success', 'revote', 'novote', 'alert',
      'objection_male', 'objection_female'
    );
  }
}
