<?php
//-- トリップテストコントローラー --//
final class TripTestController extends JinrouTestController {
  protected static function LoadRequest() {
    DevHTML::LoadRequest();
  }

  protected static function Output() {
    HTML::OutputHeader(TripTestMessage::TITLE, null, true);
    TripTestHTML::OutputForm();
    if (DevHTML::IsExecute()) {
      self::RunTest();
    }
    HTML::OutputFooter();
  }

  //テスト実行
  private static function RunTest() {
    $key = 'trip';
    RQ::Get()->ParsePost('Trip', $key);
    Text::p(RQ::Get()->$key, TripTestMessage::RESULT);
  }
}
