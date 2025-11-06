<?php
//◆文字化け抑制◆//
//-- TOPページコントローラー --//
final class JinrouIndexController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'request_index';
  }

  protected static function Output() {
    if (Number::Within(RQ::Fetch()->id, 0, count(TopPageConfig::$server_list))) {
      InfoHTML::OutputSharedRoom(RQ::Fetch()->id, true);
    } else {
      IndexHTML::Output();
    }
  }
}
