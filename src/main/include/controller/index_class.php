<?php
//◆文字化け抑制◆//
//-- TOPページコントローラー --//
final class JinrouIndexController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('request_index');
  }

  protected static function Output() {
    if (0 < RQ::Get()->id && RQ::Get()->id <= count(TopPageConfig::$server_list)) {
      InfoHTML::OutputSharedRoom(RQ::Get()->id, true);
    } else {
      IndexHTML::Output();
    }
  }
}
