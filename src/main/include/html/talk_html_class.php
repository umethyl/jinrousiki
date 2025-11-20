<?php
//-- HTML 生成クラス (Talk 拡張) --//
final class TalkHTML {
  /* 発言データ */
  //発言生成
  public static function Generate(array $list) {
    extract($list);
    return Text::Format(self::Get(),
      $talk_id, $row_class,
      $user_class, $symbol, $user_info,
      $say_class, $voice, $sentence
    );
  }

  //システムユーザ
  public static function GenerateSystem($sentence, $talk_id, $class) {
    return Text::Format(self::GetSystem(), $talk_id, $class, $sentence);
  }

  //システムメッセージ
  public static function GenerateSystemMessage($sentence, $talk_id, $class) {
    return Text::Format(self::GetSystemMessage(), $talk_id, $class, $sentence);
  }

  /* 個別データ */
  //ヘッダー生成
  public static function GenerateHeader($class, $id = null) {
    if (null === $id) {
      $attribute = [];
    } else {
      $attibute = [HTML::ID => $id];
    }
    if (DB::$ROOM->IsOn(RoomMode::AUTO_PLAY)) {
      $class .= ' hide';
    }
    $attribute[HTML::CSS] = $class;

    return TableHTML::Header($attribute, true);
  }

  //フッター生成
  public static function GenerateFooter() {
    return TableHTML::Footer();
  }

  //ユーザ名生成
  public static function GenerateSymbol($color) {
    return HTML::GenerateMessage(Message::SYMBOL, $color, 'font-size:100%');
  }

  //追加情報生成
  public static function GenerateInfo($sentence) {
    return HTML::GenerateSpan(Text::Quote($sentence));
  }

  //追加情報生成 (独り言)
  public static function GenerateSelfTalk() {
    return HTML::GenerateSpan(TalkMessage::SELF_TALK);
  }

  //時刻生成
  public static function GenerateTime($time) {
    return HTML::GenerateSpan(Text::Quote($time), TalkCSS::DATE);
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
