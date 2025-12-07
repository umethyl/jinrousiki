<?php
//◆文字化け抑制◆//
//-- TOPページコントローラー --//
final class JinrouIndexController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'request_index';
  }

  protected static function EnableCommand() {
    return Number::Within(RQ::Fetch()->id, 0, count(TopPageConfig::$server_list));
  }

  protected static function RunCommand() {
    InfoHTML::OutputSharedRoom(RQ::Fetch()->id, true);
  }

  protected static function Output() {
    IndexHTML::Output();
  }
}
