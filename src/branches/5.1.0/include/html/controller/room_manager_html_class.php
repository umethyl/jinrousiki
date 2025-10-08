<?php
//-- HTML 生成クラス (RoomManager 拡張) --//
final class RoomManagerHTML {
  //村作成画面表示
  public static function OutputCreate() {
    //パラメータセット
    if (RoomOptionManager::IsChange()) {
      $url     = sprintf('?room_no=%d', RQ::Get()->room_no);
      $command = 'change_room';
      $submit  = RoomManagerMessage::SUBMIT_CHANGE;
    } else {
      $url     = '';
      $command = 'create_room';
      $submit  = RoomManagerMessage::SUBMIT_CREATE;
    }

    //村作成パスワード
    if (null === ServerConfig::ROOM_PASSWORD) {
      $password = '';
    } else {
      $label = 'room_password';
      $password = sprintf(self::GetCreatePassword(),
	$label, RoomManagerMessage::ROOM_PASSWORD, Message::COLON,
	$label, $label, Message::SPACER
      );
    }

    //出力
    Text::Printf(self::GetCreateHeader(), $url, $command);
    OptionForm::Output();
    Text::Printf(self::GetCreateFooter(), $password, $submit);
    if (RoomOptionManager::IsChange()) {
      HTML::OutputFooter();
    }
  }

  //村情報出力
  public static function OutputRoom(array $stack) {
    $ROOM = new Room();
    $ROOM->LoadData($stack);
    RoomOption::Load($stack);
    if (AdminConfig::$room_delete_enable) {
      $url    = URL::GetRoom('admin/room_delete', $ROOM->id);
      $delete = Text::QuoteBracket(HTML::GenerateLink($url, RoomManagerMessage::DELETE));
    } else {
      $delete = '';
    }

    switch ($ROOM->status) {
    case RoomStatus::WAITING:
      $status = RoomManagerMessage::WAITING;
      break;

    case RoomStatus::CLOSING:
      $status = RoomManagerMessage::CLOSING;
      break;

    case RoomStatus::PLAYING:
      $status = RoomManagerMessage::PLAYING;
      break;
    }

    Text::Printf(self::GetRoom(),
      $delete, URL::GetRoom('login', $ROOM->id),
      ImageManager::Room()->Generate($ROOM->status, $status),
      $ROOM->GenerateNumber(),  $ROOM->GenerateName(),  Text::BR,
      $ROOM->GenerateComment(), RoomOption::Generate(), Text::BR
    );
  }

  //部屋説明出力
  public static function OutputDescribe() {
    //表示情報取得
    $stack = [
      'game_option' => DB::$ROOM->game_option,
      'option_role' => DB::$ROOM->option_role,
      'max_user'    => DB::$ROOM->max_user
    ];
    RoomOption::Load($stack);

    HTML::OutputHeader(RoomManagerMessage::TITLE_DESCRIBE, 'info/info', true);
    Text::Printf(self::GetDescribe(),
      DB::$ROOM->GenerateNumber(), DB::$ROOM->GenerateName(), Text::BR,
      DB::$ROOM->GenerateComment(), RoomOption::Generate()
    );
    RoomOption::OutputCaption();
    HTML::OutputFooter();
  }

  //結果出力
  public static function OutputResult($type, $str = '') {
    $title  = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_INPUT);
    $header = Text::Join(
      RoomManagerMessage::ERROR_HEADER, RoomManagerMessage::ERROR_CHECK_LIST
    );

    switch ($type) {
    case 'empty':
      $stack = [$str . RoomManagerMessage::ERROR_INPUT_EMPTY];
      HTML::OutputResult($title, Text::Join($header, self::GenerateErrorList($stack)));
      break;

    case 'comment':
      $stack = [
	$str . RoomManagerMessage::ERROR_INPUT_LIMIT,
	$str . RoomManagerMessage::ERROR_INPUT_NG_WORD
      ];
      HTML::OutputResult($title, Text::Join($header, self::GenerateErrorList($stack)));
      break;

    case 'no_password':
      HTML::OutputResult($title, RoomManagerMessage::ERROR_INPUT_PASSWORD);
      break;

    case 'limit_over':
      $stack = [
	$str . RoomManagerMessage::ERROR_INPUT_EMPTY,
	$str . RoomManagerMessage::ERROR_INPUT_LIMIT_OVER
      ];
      HTML::OutputResult($title, Text::Join($header, self::GenerateErrorList($stack)));
      break;

    case 'time':
      $error_header = RoomManagerMessage::ERROR_INPUT_REAL_TIME_HEADER;
      $stack = [
	$error_header . RoomManagerMessage::ERROR_INPUT_EMPTY,
	$error_header . RoomManagerMessage::ERROR_INPUT_LIMIT_OVER,
	$error_header . RoomManagerMessage::ERROR_INPUT_REAL_TIME_EM,
	$error_header . RoomManagerMessage::ERROR_INPUT_REAL_TIME_NUMBER
      ];
      HTML::OutputResult($title, Text::Join($header, self::GenerateErrorList($stack)));
      break;

    case 'gm_logout':
      HTML::OutputResult($title, RoomManagerMessage::ERROR_INPUT_GM_LOGOUT);
      break;

    case 'busy':
      $title = sprintf(RoomManagerMessage::ERROR, Message::DB_ERROR);
      HTML::OutputResult($title, Message::DB_ERROR_LOAD);
      break;
    }
  }

  //エラーの説明リストを生成
  private static function GenerateErrorList(array $list) {
    $result = Text::LineFeed('<ul>');
    foreach ($list as $str) {
      $result .= Text::Format('<li>%s</li>', $str);
    }
    return Text::LineFeed($result . '</ul>');
  }

  //村作成画面パスワードタグ
  private static function GetCreatePassword() {
    return <<<EOF
<label for="%s">%s</label>%s<input type="password" id="%s" name="%s" size="20">%s
EOF;
  }

  //村作成画面ヘッダタグ
  private static function GetCreateHeader() {
    return <<<EOF
<form method="post" action="room_manager.php%s">
<input type="hidden" name="%s" value="on">
<table>
EOF;
  }

  //村作成画面フッタタグ
  private static function GetCreateFooter() {
    return <<<EOF
<tr><td id="make" colspan="2">%s<input type="submit" value=" %s "></td></tr>
</table>
</form>
EOF;
  }

  //村タグ
  private static function GetRoom() {
    return '%s<a href="%s">%s <span>[%s]</span>%s%s<div>%s %s</div></a>%s';
  }

  //部屋説明タグ
  private static function GetDescribe() {
    return <<<EOF
[%s] %s%s
<div>%s %s</div>
EOF;
  }
}
