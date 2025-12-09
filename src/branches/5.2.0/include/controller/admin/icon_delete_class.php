<?php
//--  アイコン削除(管理用)コントローラー --//
final class JinrouAdminIconDeleteController extends JinrouController {
  protected static function IsAdmin() {
    return true;
  }

  protected static function GetAdminType() {
    return 'icon_delete';
  }

  protected static function LoadRequestExtra() {
    RQ::Fetch()->ParseGetInt(RequestDataIcon::ID);

    //-- Validate --//
    $icon_no = RQ::Fetch()->icon_no;
    if ($icon_no < 1) {
      self::OutputError(sprintf(IconMessage::NOT_EXISTS, $icon_no));
    }
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
    //-- Validate --//
    $icon_no = RQ::Fetch()->icon_no;
    if (false === DB::Lock('icon')) {
      self::OutputError(Message::DB_ERROR_LOAD);
    }

    if (IconDB::Using($icon_no)) { //使用中判定
      self::OutputError(IconMessage::USING);
    }

    $file = IconDB::GetFile($icon_no); //存在判定
    if (false === $file || null === $file) {
      self::OutputError(IconDeleteMessage::NOT_EXISTS);
    }

    //-- Execute --//
    if (IconDB::Delete($icon_no, $file)) {
      $url = '../icon_upload.php';
      $str = Text::Join(IconDeleteMessage::SUCCESS, URL::GetJump($url));
      HTML::OutputResult(IconDeleteMessage::TITLE, $str, $url);
    } else {
      self::OutputError(Message::DB_ERROR_LOAD);
    }
  }

  //エラー出力
  private static function OutputError(string $str) {
    HTML::OutputResult(IconDeleteMessage::TITLE . ' ' . Message::ERROR_TITLE, $str);
  }
}
