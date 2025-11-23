<?php
//◆文字化け抑制◆//
//-- HTML 生成クラス (room 拡張) --//
final class RoomHTML {
  //タイトル生成
  public static function GenerateTitle() {
    return TableHTML::Td(self::GenerateTitleBase(), [HTML::CSS => 'room']);
  }

  //タイトル生成 (ログ用)
  public static function GenerateLogTitle() {
    return HTML::GenerateSpan(self::GenerateTitleBase(), 'room');
  }

  //タイトル出力
  public static function OutputTitle() {
    echo self::GenerateTitle();
  }

  //タイトルベース生成
  private static function GenerateTitleBase() {
    $name    = HTML::GenerateSpan(DB::$ROOM->GenerateName(), 'room-name');
    $number  = Text::QuoteBracket(DB::$ROOM->GenerateNumber());
    $comment = HTML::GenerateSpan(DB::$ROOM->GenerateComment(), 'room-comment');
    return $name . ' ' . $number . Text::BR . $comment;
  }
}
