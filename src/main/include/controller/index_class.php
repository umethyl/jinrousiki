<?php
//-- TOPページコントローラー --//
class JinrouIndex {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('request_index');
  }

  //出力
  private static function Output() {
    if (0 < RQ::Get()->id && RQ::Get()->id <= count(TopPageConfig::$server_list)) {
      InfoHTML::OutputSharedRoom(RQ::Get()->id, true);
    } else {
      IndexHTML::Output();
    }
  }
}
