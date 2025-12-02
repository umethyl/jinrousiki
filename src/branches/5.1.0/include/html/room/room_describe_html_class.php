<?php
//-- HTML 生成クラス (RoomDescribe 拡張) --//
final class RoomDescribeHTML {
  //出力
  public static function Output() {
    HTML::OutputHeader(RoomDescribeMessage::TITLE, 'info/info', true);
    self::OutputTitle();
    RoomOptionLoader::OutputCaption();
    HTML::OutputFooter();
  }

  //タイトル出力
  private static function OutputTitle() {
    $number  = DB::$ROOM->GenerateNumber();
    $name    = DB::$ROOM->GenerateName();
    $comment = DB::$ROOM->GenerateComment();
    $option  = RoomOptionLoader::Generate();
    $list = [$name . ' ' . Text::QuoteBracket($number), $comment, $option];
    DivHTML::Output(ArrayFilter::Concat($list, Text::BR));
  }
}
