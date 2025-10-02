<?php
//-- トリップテスト --//
class TripTest {
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
    HTML::OutputHeader(TripTestMessage::TITLE, null, true);
    TripTestHTML::OutputForm();
    if (DevHTML::IsExecute()) self::RunTest();
    HTML::OutputFooter();
  }

  //テスト実行
  private static function RunTest() {
    $key = 'trip';
    RQ::Get()->ParsePost('Trip', $key);
    Text::p(RQ::Get()->$key, TripTestMessage::RESULT);
  }
}
