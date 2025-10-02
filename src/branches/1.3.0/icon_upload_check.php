<?php
require_once(dirname(__FILE__) . '/include/functions.php');
require_once(dirname(__FILE__) . '/include/icon_data_check.php');
if(FindDangerValue($_FILES)) die;

//セッション開始(ヘッダを送る前に開始しておく)
session_start();
session_regenerate_id(); //セッションを新しく作る
$session_id = session_id();

// エラーページ用タイトル
$title = 'アイコン登録エラー';

// リファラチェック
$icon_upload_page_url = $site_root . 'icon_upload.php';
if(strncmp(@$_SERVER['HTTP_REFERER'], $icon_upload_page_url , strlen($icon_upload_page_url)) != 0){
  OutputActionResult($title, '無効なアクセスです。');
}
EncodePostData(); //ポストされた文字列を全てエンコードする

//アイコン名が空白かチェック
$name = $_POST['name'];
if($name == '') OutputActionResult($title, 'アイコン名を入力してください。');

//アイコン名の文字列長のチェック
$name_length = strlen($name);
if($name_length > $USER_ICON->name){
  OutputActionResult($title, IconNameMaxLength());
}

//ファイルサイズのチェック
if($_FILES['file']['size'] == 0 || $_FILES['file']['size'] > $USER_ICON->size){
  OutputActionResult($title, 'ファイルサイズは ' . IconFileSizeMax());
}

//ファイルの種類のチェック
switch($_FILES['file']['type']){
case 'image/jpeg':
case 'image/pjpeg':
  $ext = '.jpg';
  break;

case 'image/gif':
  $ext = '.gif';
  break;

case 'image/png':
case 'image/x-png':
  $ext = '.png';
  break;

default:
  OutputActionResult($title, $_FILES['file']['type'] .
		     ' : jpg, gif, png 以外のファイルは登録できません。');
  break;
}

//色指定のチェック
$color = $_POST['color'];
if(strlen($color) != 7 && ! preg_match('/^#[0123456789abcdefABCDEF]{6}/', $color)){
  OutputActionResult($title,
		     '色指定が正しくありません。<br>'."\n" .
		     '指定は (例：#6699CC) のように RGB 16進数指定で行ってください。<br>'."\n" .
		     '送信された色指定 → <span class="color">' . $color . '</span>');
}

//アイコンの高さと幅をチェック
list($width, $height) = getimagesize($_FILES['file']['tmp_name']);
if($width > $USER_ICON->width || $height > $USER_ICON->height){
  OutputActionResult($title, 'アイコンは ' . IconSizeMax() . ' しか登録できません。<br>'."\n" .
		     '送信されたファイル → <span class="color">幅 ' . $width .
		     ', 高さ ' . $height . '</span>');
}

$dbHandle = ConnectDatabase(); //DB 接続

//アイコンの名前が既に登録されていないかチェック
$sql = mysql_query('SELECT icon_name FROM user_icon');
if(in_array($name, mysql_fetch_assoc($sql))){
  OutputActionResult($title, 'そのアイコン名は既に登録されています');
}
EscapeStrings($name);

if(! mysql_query('LOCK TABLES user_icon WRITE')){ //user_icon テーブルをロック
  OutputActionResult($title, 'サーバが混雑しています。<br>'."\n" .
		     '時間を置いてから再登録をお願いします。');
}

//アイコン登録数が最大値を超えてないかチェック
//現在登録されているアイコンナンバーを降順に取得
$sql = mysql_query('SELECT icon_no FROM user_icon ORDER BY icon_no DESC');
$array = mysql_fetch_assoc($sql);
$icon_no = $array['icon_no'] + 1; //一番大きなNo + 1
if($icon_no >= $USER_ICON->number) OutputActionResult($title, 'これ以上登録できません', '', true);

//ファイル名の桁をそろえる
$file_name = sprintf("%03s%s", $icon_no, $ext);

//アップロードされたファイルのエラーチェック
if($_FILES['upfile']['error'][$i] != 0){
  OutputActionResult($title, '何かアップロードエラーが発生しました。<br>'."\n" .
		     '再度実行してください。', '', true);
}

//ファイルをテンポラリからコピー
if(! move_uploaded_file($_FILES['file']['tmp_name'], $ICON_CONF->path . '/' . $file_name)){
  OutputActionResult($title, '登録に失敗しました。<br>'."\n" .
		     '再度実行してください。', '', true);
}

//データベースに登録
mysql_query("INSERT INTO user_icon(icon_no, icon_name, icon_filename, icon_width, icon_height,
		color, session_id)
		VALUES($icon_no, '$name', '$file_name', $width, $height, '$color', '$session_id')");
mysql_query('COMMIT'); //一応コミット
mysql_query('UNLOCK TABLES'); //ロック解除
DisconnectDatabase($dbHandle);

//確認ページを出力
OutputHTMLHeader('ユーザアイコンアップロード処理[確認]', 'icon_upload_check');
echo <<<EOF
</head>
<body>
<p>ファイルをアップロードしました。<br>今だけやりなおしできます</p>
<img src="{$ICON_CONF->path}/$file_name" width="$width" height="$height"><br>
<table>
<tr><td>No. $icon_no <font color="$color">◆</font>$color<br></td></tr>
<tr><td>よろしいですか？</td></tr>
<tr><td><form method="POST" action="icon_upload_finish.php">
  <input type="hidden" name="entry" value="cancel">
  <input type="hidden" name="icon_no" value="$icon_no">
  <input type="submit" value="やりなおし">
</form></td>
<td><form method="POST" action="icon_upload_finish.php">
  <input type="hidden" name="entry" value="success">
  <input type="hidden" name="icon_no" value="$icon_no">
  <input type="submit" value="登録完了">
</form></td></tr></table>
</body></html>

EOF;
?>
