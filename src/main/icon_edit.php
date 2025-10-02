<?php
require_once('include/init.php');
$INIT_CONF->LoadFile('icon_functions');
$INIT_CONF->LoadRequest('RequestIconEdit'); //引数を取得
EditIcon();

//-- 関数 --//
function EditIcon(){
  global $DB_CONF, $USER_ICON, $ICON_CONF, $RQ_ARGS;

  $title = 'ユーザアイコン編集';
  if(CheckReferer('icon_view.php')){ //リファラチェック
    OutputActionResult($title, '無効なアクセスです');
  }

  extract($RQ_ARGS->ToArray()); //引数を展開
  $back_url = "<br>\n".'<a href="icon_view.php?icon_no=' . $icon_no . '">戻る</a>';

  //入力データチェック
  if(strlen($icon_name) < 1) OutputActionResult($title, 'アイコン名が空欄になっています');
  if($password != $USER_ICON->password) OutputActionResult($title, 'パスワードが違います');
  $query_stack = array();

  $DB_CONF->Connect(); //DB 接続
  $query_header = 'SELECT COUNT(icon_no) FROM user_icon WHERE ';

  //アイコンの名前が既に登録されていないかチェック
  if(FetchResult($query_header . 'icon_no = ' . $icon_no) < 1){
    OutputActionResult($title, '無効なアイコン番号です：' . $icon_no);
  }

  //アイコン名の文字列長のチェック
  $text_list = array('icon_name'  => 'アイコン名',
		     'appearance' => '出典',
		     'category'   => 'カテゴリ',
		     'author'     => 'アイコンの作者');
  foreach($text_list as $text => $label){
    $value = $RQ_ARGS->$text;
    if(strlen($value) > $USER_ICON->name){
      OutputActionResult($title, $label . ': ' . $USER_ICON->MaxNameLength());
    }
    $query_stack[] = "{$text} = " . (strlen($value) > 0 ? "'{$value}'" : 'NULL');
  }

  //アイコンの名前が既に登録されていないかチェック
  if(strlen($icon_name) > 0 &&
     FetchResult("{$query_header} icon_name = '{$icon_name}' AND icon_no <> {$icon_no}") > 0){
    OutputActionResult($title, 'アイコン名 "' . $icon_name . '" は既に登録されています');
  }

  if(strlen($color) > 0){ //色指定のチェック
    $color = CheckColorString($color, $title, $back_url);
    $query_stack[] = "color = '{$color}'";
  }

  //非表示フラグチェック
  if(FetchResult("{$query_header} icon_no = {$icon_no} AND disable = TRUE") > 0 !== $disable){
    $query_stack[] = 'disable = ' . ($disable ? 'TRUE' : 'FALSE');
  }

  if(count($query_stack) < 1){
    OutputActionResult($title, '変更内容はありません');
  }
  $query = 'UPDATE user_icon SET ' . implode(', ', $query_stack) . ' WHERE icon_no = ' . $icon_no;
  //OutputActionResult($title, $query); //テスト用

  if(! mysql_query('LOCK TABLES user_icon WRITE')){ //user_icon テーブルをロック
    $str = "サーバが混雑しています。<br>\n時間を置いてから再登録をお願いします。";
    OutputActionResult($title, $str);
  }
  SendQuery($query, true);
  OutputActionResult($title, '編集完了', 'icon_view.php?icon_no=' . $icon_no, true);
}
