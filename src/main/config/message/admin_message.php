<?php
//-- JinrouAdmin 専用メッセージ --//
class AdminMessage {
  const EXPLAIN = '有効になっている管理機能が表示されます。<br>警告メッセージが不要な場合は警告メッセージ表示設定をOFFにしてください。';
  const NOTICE_ENABLE = ' が有効になっています。';

  public static $debug             = 'デバッグモード (ServerConfig::DEBUG_MODE)';
  public static $setup             = '初期設定 (setup)';
  public static $room_delete       = '部屋削除 (room_delete)';
  public static $icon_delete       = 'アイコン削除 (icon_delete)';
  public static $log_delete        = 'ログ削除 (log_delete)';
  public static $generate_html_log = 'ログのHTML化 (generate_html_log)';
  public static $role_test         = '配役テスト (role_test)';
}
