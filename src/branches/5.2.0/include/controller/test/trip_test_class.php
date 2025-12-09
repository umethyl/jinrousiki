<?php
//-- ◆文字化け抑制◆ --//
//-- トリップテストコントローラー --//
final class TripTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function LoadRequestExtra() {
    DevHTML::LoadRequest();
  }

  protected static function OutputRunHeader() {
    HTML::OutputHeader(TripTestMessage::TITLE, null, true);
    TripTestHTML::OutputForm();
  }

  protected static function EnableCommand() {
    return DevHTML::IsExecute();
  }

  protected static function RunCommand() {
    $key = 'trip';
    RQ::Fetch()->ParsePost('Trip', $key);
    Text::p(RQ::Get($key), TripTestMessage::RESULT);
  }

  protected static function OutputRunFooter() {
    HTML::OutputFooter();
  }
}
