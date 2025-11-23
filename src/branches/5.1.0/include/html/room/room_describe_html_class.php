<?php
//-- HTML 生成クラス (RoomDescribe 拡張) --//
final class RoomDescribeHTML {
  //出力
  public static function Output() {
    HTML::OutputHeader(RoomManagerMessage::TITLE_DESCRIBE, 'info/info', true);
    self::OutputTitle();
    DivHTML::Output(DB::$ROOM->GenerateComment() . ' ' . RoomOptionLoader::Generate());
    RoomOptionLoader::OutputCaption();
    HTML::OutputFooter();
  }

  //タイトル出力
  private static function OutputTitle() {
    $number = DB::$ROOM->GenerateNumber();
    $name   = DB::$ROOM->GenerateName();
    Text::Output(Text::QuoteBracket($number) . ' ' . $name . Text::BR);
  }
}
