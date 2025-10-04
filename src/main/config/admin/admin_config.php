<?php
//-- 管理用ツール設定 --//
final class AdminConfig {
  /* 全体設定 */
  const ENABLE = false;	//有効設定 (true:有効にする / false:しない)

  /* 個別設定 */
  //-- デバッグモード (ServerConfig::DEBUG_MODE) --//
  public static $notice_debug= true;	//有効時の警告メッセージ (true:表示する / false:しない)

  //-- 初期設定 (setup) --//
  public static $setup_enable = false;	//有効設定 (true:有効にする / false:しない)
  public static $notice_setup = true;	//有効時の警告メッセージ (true:表示する / false:しない)

  //-- 部屋削除 (room_delete) --//
  public static $room_delete_enable = false;	//有効設定 (true:有効にする / false:しない)
  public static $notice_room_delete = true;	//有効時の警告メッセージ (true:表示する / false:しない)

  //-- アイコン削除 (icon_delete) --//
  public static $icon_delete_enable = false;	//有効設定 (true:有効にする / false:しない)
  public static $notice_icon_delete = true;	//有効時の警告メッセージ (true:表示する / false:しない)

  //-- ログ削除 (log_delete) --//
  //-- 未完成につき、使用しないこと --//
  public static $log_delete_enable = false;	//有効設定 (true:有効にする / false:しない)
  public static $notice_log_delete = true;	//有効時の警告メッセージ (true:表示する / false:しない)

  //-- ログのHTML化 (generate_html_log) --//
  /* 詳細設定は generate_html_log_config.php で行ってください */
  public static $generate_html_log_enable = false;	//有効設定 (true:有効にする / false:しない)
  public static $notice_generate_html_log = true;	//有効時の警告メッセージ (true:表示する / false:しない)

  //-- 配役テストツール (role_test) --//
  public static $role_test_enable = false;	//有効設定 (true:有効にする / false:しない)
  public static $notice_role_test = true;	//有効時の警告メッセージ (true:表示する / false:しない)
}
