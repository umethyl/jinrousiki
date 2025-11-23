<?php
//-- ◆文字化け抑制◆ --//
//-- トリップテストコントローラー --//
final class TripTestController extends JinrouTestController {
  protected static function LoadRequestExtra() {
    RQ::Fetch()->ParsePostOn('execute');
  }

  protected static function OutputHeader() {
    HTML::OutputHeader(TripTestMessage::TITLE, null, true);
    TripTestHTML::OutputForm();
  }

  protected static function IsExecute() {
    return DevHTML::IsExecute();
  }

  protected static function RunTest() {
    $key = 'trip';
    RQ::Fetch()->ParsePost('Trip', $key);
    Text::p(RQ::Get($key), TripTestMessage::RESULT);
  }
}
