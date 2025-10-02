<?php
//-- 管理用クラス --//
class JinrouAdmin {
  //村削除
  static function DeleteRoom() {
    if (! ServerConfig::DEBUG_MODE) {
      HTML::OutputResult('認証エラー', 'このスクリプトは使用できない設定になっています。');
    }
    extract($_GET, EXTR_PREFIX_ALL, 'unsafe');
    $room_no = intval($unsafe_room_no);
    $title   = '部屋削除[エラー]';
    if ($room_no < 1) HTML::OutputResult($title, '無効な村番号です。');

    DB::Connect();
    if (DB::Lock('room') && DB::DeleteRoom($room_no)) {
      DB::Optimize();
      $str = $room_no . ' 番地を削除しました。トップページに戻ります。';
      HTML::OutputResult('部屋削除', $str, '../');
    }
    else {
      HTML::OutputResult($title, $room_no . ' 番地の削除に失敗しました。');
    }
  }

  //アイコン削除
  static function DeleteIcon() {
    if (! ServerConfig::DEBUG_MODE) {
      HTML::OutputResult('認証エラー', 'このスクリプトは使用できない設定になっています。');
    }
    extract($_GET, EXTR_PREFIX_ALL, 'unsafe');
    $icon_no = intval($unsafe_icon_no);
    $title   = 'アイコン削除[エラー]';
    if ($icon_no < 1) HTML::OutputResult($title, '無効なアイコン番号です。');

    Loader::LoadFile('icon_functions');
    DB::Connect();
    $error = "サーバが混雑しています。<br>\n時間を置いてから再度アクセスしてください。";
    if (! DB::Lock('icon')) HTML::OutputResult($title, $error); //トランザクション開始
    if (IconDB::IsUsing($icon_no)) { //使用中判定
      HTML::OutputResult($title, '募集中・プレイ中の村で使用されているアイコンは削除できません。');
    }

    $file = IconDB::GetFile($icon_no);
    if ($file === false || is_null($file)) HTML::OutputResult($title, 'ファイルが存在しません');

    if (IconDB::Delete($icon_no, $file)) {
      $url = '../icon_upload.php';
      $str = '削除完了：登録ページに飛びます。<br>'."\n" .
	'切り替わらないなら <a href="' . $url . '">ここ</a> 。';
      HTML::OutputResult('アイコン削除完了', $str, $url);
    }
    else {
      HTML::OutputResult($title, $error);
    }
  }

  //ログ生成
  static function GenerateLog() {
    $format = sprintf('../log_test/%s', RQ::Get()->prefix) . '%d%s.html';
    $footer = HTML::FOOTER . "\n";
    for ($i = RQ::Get()->min_room_no; $i <= RQ::Get()->max_room_no; $i++) {
      RQ::Set('room_no', $i);
      foreach (array(false, true) as $flag) {
	RQ::Set('reverse_log', $flag);

	DB::$ROOM = new Room(RQ::Get());
	DB::$ROOM->log_mode  = true;
	DB::$ROOM->last_date = DB::$ROOM->date;

	DB::$USER = new UserData(RQ::Get());
	DB::$SELF = new User();

	$file = sprintf($format, $i, $flag ? 'r' : '');
	file_put_contents($file, OldLogHTML::Generate() . $footer);
      }
    }

    $format = '%d 番地から %d 番地までを HTML 化しました';
    $str = sprintf($format, RQ::Get()->min_room_no, RQ::Get()->max_room_no);
    HTML::OutputResult('ログ生成', $str);
  }

  //ログ削除
  static function DeleteLog($from, $to) {
    DB::Connect(RQ::Get()->db_no);
    HTML::OutputHeader('DB削除モード', null, true);
    for ($i = $from; $i <= $to; $i++) {
      DB::DeleteRoom($i);
      printf('%d 番地を削除しました<br>', $i);
    }
    DB::Optimize();
    HTML::OutputFooter(true);
  }
}
