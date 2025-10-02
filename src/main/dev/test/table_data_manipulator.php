<?php
/*
  このファイルはデータベース書き換え作業支援関数を集めたものです
  管理者が必要に応じて編集し、アップロードした後にブラウザで当ファイルに
  アクセス、という使い方を想定しています。

  開発者のテスト用コードそのままなので要注意！
*/
require_once('init.php');

$disable = true; //false にすると使用可能になる
if ($disable) HTML::OutputUnusableError();

DB::Connect();
HTML::OutputHeader('Test Tools', null, true);

//UpdateIconInfo('category', '初期設定', 1, 10);
//UpdateIconInfo('appearance', '初期設定', 1, 10);
//UpdateIconInfo('category', '東方Project', 11, 78);
//UpdateIconInfo('appearance', '東方紅魔郷', 13, 21);
//UpdateIconInfo('appearance', '東方妖々夢', 22, 33);
//UpdateIconInfo('appearance', '東方萃夢想', 34);
//UpdateIconInfo('appearance', '東方永夜抄', 35, 42);
//UpdateIconInfo('appearance', '東方花映塚', 43, 47);
//UpdateIconInfo('appearance', '東方風神録', 48, 55);
//UpdateIconInfo('appearance', '東方緋想天', 56, 57);
//UpdateIconInfo('appearance', '東方地霊殿', 58, 65);
//UpdateIconInfo('appearance', '東方香霖堂', 66, 67);
//UpdateIconInfo('appearance', '東方三月精', 68, 70);
//UpdateIconInfo('appearance', '東方求聞史紀', 71);
//UpdateIconInfo('appearance', '東方儚月抄', 72);
//UpdateIconInfo('appearance', '秘封倶楽部', 76, 77);
//UpdateIconInfo('appearance', '東方靈異伝', 91, 92);
//UpdateIconInfo('appearance', '東方夢時空', 181);
//UpdateIconInfo('appearance', '東方怪綺談', 185, 186);
//UpdateIconInfo('author', '夏蛍', 12, 77);
//OutputExportIconTable();
//DB::Commit();
HTML::OutputFooter();
//UpdateRoomInfo('room_name', 'テスト', 1);
//HTML::OutputResult('処理完了', '処理完了。');

//-- 関数 --//
//ファイルの IO テスト
function OpenFile($file) {
  Text::p(file_get_contents(JINROU_ROOT . '/' . $file));
}

/*
  Ver. 1.4.0 beta3 より実装されたユーザアイコンテーブルの追加情報入力支援関数
  type:[appearance / category / author] (出典 / カテゴリ / 作者)
  value: 入力内容
  from / to: 入力対象アイコン (from ～ to まで)
*/
function UpdateIconInfo($type, $value, $from, $to = null) {
  $query = isset($to) ? "{$from} <= icon_no AND icon_no <= {$to}" : "icon_no = {$from}";
  DB::Prepare("UPDATE user_icon SET {$type} = '{$value}' WHERE {$query}");
  DB::Execute();
}

//村情報再編集関数 (文字化け対策用)
/*
  item  : DB 項目名
  value : 入力内容
  id    : 村番号
*/
function UpdateRoomInfo($item, $value, $id) {
  DB::Prepare("UPDATE room SET {$item} = '{$value}' WHERE room_no = {$id}");
  DB::Execute();
}

function OutputExportIconTable() {
  $query = 'SELECT * FROM user_icon ORDER BY icon_no';
  $str = 'INSERT INTO `user_icon` (`icon_no`, `icon_name`, `icon_filename`, `icon_width`, ' .
    '`icon_height`, `color`, `session_id`, `appearance`, `category`, `author`, `regist_date`, ' .
    '`disable`) VALUES'."\n".'<br>';
  DB::Prepare($query);
  foreach (DB::FetchAssoc(true) as $stack) {
    extract($stack);
    if ($icon_no <= 10) continue;
    $date = is_null($regist_date) ? 'NULL' : "'$regist_date'";
    $bool = is_null($disable) ? 'NULL' : "'$disable'";
    $str .= "({$icon_no}, '{$icon_name}', '{$icon_filename}', {$icon_width}, " .
      "{$icon_height}, '{$color}', NULL, '{$appearance}', '{$category}', '{$author}', {$date}, " .
      "$bool),\n<br>";
  }
  echo $str;
}
