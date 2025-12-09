<?php
//-- HTML 生成クラス (RoomEntry 拡張) --//
final class RoomEntryHTML {
  //出力
  public static function Output() {
    //パラメータセット
    if (RoomOptionManager::IsChange()) {
      $url     = sprintf('?room_no=%d', RQ::Get(RequestDataGame::ID));
      $command = 'change_room';
      $submit  = RoomEntryMessage::SUBMIT_CHANGE;
    } else {
      $url     = '';
      $command = 'create_room';
      $submit  = RoomEntryMessage::SUBMIT_CREATE;
    }

    //村作成パスワード
    if (null === ServerConfig::ROOM_PASSWORD) {
      $password = '';
    } else {
      $label = 'room_password';
      $password = sprintf(self::GetPassword(),
	$label, RoomEntryMessage::ROOM_PASSWORD, Message::COLON,
	$label, $label, Message::SPACER
      );
    }

    //出力
    Text::Printf(self::GetHeader(), $url, $command);
    OptionForm::Output();
    Text::Printf(self::GetFooter(), $password, $submit);
    if (RoomOptionManager::IsChange()) {
      HTML::OutputFooter();
    }
  }

  //パスワードタグ
  private static function GetPassword() {
    return <<<EOF
<label for="%s">%s</label>%s<input type="password" id="%s" name="%s" size="20">%s
EOF;
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<form method="post" action="room_manager.php%s">
<input type="hidden" name="%s" value="on">
<table>
EOF;
  }

  //フッタタグ
  private static function GetFooter() {
    return <<<EOF
<tr><td id="make" colspan="2">%s<input type="submit" value=" %s "></td></tr>
</table>
</form>
EOF;
  }
}
