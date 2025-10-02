<?php
//-- HTML 生成クラス (Option 拡張) --//
class OptionHTML {
  //ゲームオプション画像出力
  public static function OutputImage($str) {
    HTML::OutputDiv(OptionMessage::GAME_OPTION . Message::COLON . $str, 'game-option');
  }

  //村用オプション説明メッセージ生成
  public static function GenerateRoomCaption($image, $url, $caption, $explain) {
    return Text::Format(self::GetRoomCaption(),
      $image, Message::COLON, $url, $caption, Message::COLON, $explain
    );
  }

  //村用オプション説明メッセージタグ
  private static function GetRoomCaption() {
    return '<div>%s%s<a href="info/%s">%s</a>%s%s</div>';
  }
}
