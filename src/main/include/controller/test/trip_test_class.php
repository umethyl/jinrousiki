<?php
//-- トリップテストコントローラー --//
final class TripTestController extends JinrouTestController {
  protected static function LoadRequest() {
    DevHTML::LoadRequest();
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
    RQ::Get()->ParsePost('Trip', $key);
    Text::p(RQ::Get()->$key, TripTestMessage::RESULT);
  }
}
