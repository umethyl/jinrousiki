<?php
//-- HTML 生成クラス (Talk 拡張) --//
class TalkHTML {
  /* 発言データ */
  //発言生成
  public static function Generate(array $list) {
    extract($list);
    return Text::Format(self::Get(),
      $talk_id, $row_class,
      $user_class, $symbol, $user_info,
      $say_class, $voice, $str
    );
  }

  //システムユーザ
  public static function GenerateSystem($str, $talk_id, $class) {
    return Text::Format(self::GetSystem(), $talk_id, $class, $str);
  }

  //システムメッセージ
  public static function GenerateSystemMessage($str, $talk_id, $class) {
    return Text::Format(self::GetSystemMessage(), $talk_id, $class, $str);
  }

  /* 個別データ */
  //ヘッダー生成
  public static function GenerateHeader($class, $id = null) {
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) $class .= ' hide';
    return Text::Add(TableHTML::GenerateHeader($class, false, $id));
  }

  //フッター生成
  public static function GenerateFooter() {
    return Text::Add(TableHTML::GenerateFooter(false));
  }

  //ユーザ名生成
  public static function GenerateSymbol($color) {
    return '<font color="' . $color . '">' . Message::SYMBOL . '</font>';
  }

  //追加情報生成
  public static function GenerateInfo($str) {
    return HTML::GenerateSpan(Text::Quote($str));
  }

  //追加情報生成 (独り言)
  public static function GenerateSelfTalk() {
    return HTML::GenerateSpan(TalkMessage::SELF_TALK);
  }

  //時刻生成
  public static function GenerateTime($time) {
    return HTML::GenerateSpan(Text::Quote($time), 'date-time');
  }

  //発言タグ
  public static function Get() {
    return <<<EOF
<tr%s class="user-talk%s">
<td class="user-name%s">%s%s</td>
<td class="say%s %s">%s</td>
</tr>
EOF;
  }

  //システムユーザタグ
  private static function GetSystem() {
    return <<<EOF
<tr%s>
<td class="%s" colspan="2">%s</td>
</tr>
EOF;
  }

  //システムメッセージタグ
  private static function GetSystemMessage() {
    return <<<EOF
<tr%s class="system-message">
<td class="%s" colspan="2">%s</td>
</tr>
EOF;
  }
}
