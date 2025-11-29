<?php
//-- HTML 生成クラス (RoomError 拡張) --//
final class RoomErrorHTML {
  //出力
  public static function Output(string $type, string $str = '') {
    $title  = sprintf(RoomErrorMessage::TITLE, RoomErrorMessage::INPUT);
    $header = Text::Join(
      RoomErrorMessage::INPUT_HEADER, RoomErrorMessage::INPUT_CHECK_LIST
    );

    switch ($type) {
    case RoomError::EMPTY:
      $stack = [$str . RoomErrorMessage::INPUT_EMPTY];
      HTML::OutputResult($title, Text::Join($header, self::GenerateList($stack)));
      break;

    case RoomError::COMMENT:
      $stack = [
	$str . RoomErrorMessage::INPUT_LIMIT,
	$str . RoomErrorMessage::INPUT_NG_WORD
      ];
      HTML::OutputResult($title, Text::Join($header, self::GenerateList($stack)));
      break;

    case RoomError::INPUT:
      $stack = [
	$str . RoomErrorMessage::INPUT_EMPTY,
	$str . RoomErrorMessage::INPUT_LIMIT_OVER
      ];
      HTML::OutputResult($title, Text::Join($header, self::GenerateList($stack)));
      break;

    case RoomError::TIME:
      $error_header = RoomErrorMessage::INPUT_REAL_TIME_HEADER;
      $stack = [
	$error_header . RoomErrorMessage::INPUT_EMPTY,
	$error_header . RoomErrorMessage::INPUT_LIMIT_OVER,
	$error_header . RoomErrorMessage::INPUT_REAL_TIME_EM,
	$error_header . RoomErrorMessage::INPUT_REAL_TIME_NUMBER
      ];
      HTML::OutputResult($title, Text::Join($header, self::GenerateList($stack)));
      break;

    case RoomError::USER:
      HTML::OutputResult($title, RoomErrorMessage::INPUT_MAX_USER);
      break;

    case RoomError::PASSWORD:
      HTML::OutputResult($title, RoomErrorMessage::INPUT_PASSWORD);
      break;

    case RoomError::BUSY:
      $title = sprintf(RoomErrorMessage::TITLE, Message::DB_ERROR);
      HTML::OutputResult($title, Message::DB_ERROR_LOAD);
      break;
    }
  }

  //エラーの説明リストを生成
  private static function GenerateList(array $list) {
    $result = Text::LineFeed('<ul>');
    foreach ($list as $str) {
      $result .= Text::Format('<li>%s</li>', $str);
    }
    return Text::LineFeed($result . '</ul>');
  }
}
