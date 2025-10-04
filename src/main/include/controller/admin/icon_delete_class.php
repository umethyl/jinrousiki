<?php
//--  アイコン削除(管理用)コントローラー --//
final class JinrouAdminIconDeleteController extends JinrouController {
  protected static function Start() {
    if (true !== ServerConfig::DEBUG_MODE) {
      HTML::OutputUnusableError();
    }
  }

  protected static function LoadRequest() {
    RQ::LoadRequest();
    RQ::Get()->ParseGetInt(RequestDataIcon::ID);

    //-- Validate --//
    $icon_no = RQ::Get()->icon_no;
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
    $icon_no = RQ::Get()->icon_no;
    if (false === DB::Lock('icon')) {
      self::OutputError(Message::DB_ERROR_LOAD);
    }

    if (IconDB::Using($icon_no)) { //使用中判定
      self::OutputError(IconMessage::USING);
    }

    $file = IconDB::GetFile($icon_no); //存在判定
    if (false === $file || null === $file) {
      self::OutputError(AdminMessage::DELETE_ICON_NOT_EXISTS);
    }

    //-- Execute --//
    if (IconDB::Delete($icon_no, $file)) {
      $url = '../icon_upload.php';
      $str = Text::Join(AdminMessage::DELETE_ICON_SUCCESS, URL::GetJump($url));
      HTML::OutputResult(AdminMessage::DELETE_ICON, $str, $url);
    } else {
      self::OutputError(Message::DB_ERROR_LOAD);
    }
  }

  //エラー出力
  private static function OutputError(string $str) {
    HTML::OutputResult(AdminMessage::DELETE_ICON . ' ' . Message::ERROR_TITLE, $str);
  }
}
