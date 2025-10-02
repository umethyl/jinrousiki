<?php
//-- データベース設定 --//
class DatabaseConfig {
  //データベースサーバのホスト名 hostname:port
  //ポート番号を省略するとデフォルトポートがセットされます。(MySQL:3306)
  const HOST     = 'localhost';
  const NAME     = 'jinrou';	//データベース名
  const USER     = 'xxxx';	//ユーザ名
  const PASSWORD = 'xxxxxxxx';	//パスワード
  const ENCODE   = 'utf8';	//文字コード
  const DISABLE  = false;	// true にすると DB 接続をしません

  //サブデータベースのリスト (サーバによってはサブのデータベースを作れないので注意)
  /*
    過去ログ表示専用です。old_log.php の引数に db_no=[数字] を追加すると
    設定したサブのデータベースに切り替えることができます。
    例) $name_list = array('log_a', 'log_b');
        old_log.php?db_no=2 => log_b のデータベースのログを表示
  */
  static $name_list = array();
}
