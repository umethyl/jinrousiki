<?php
require_once(dirname(__FILE__) . '/../include/functions.php');
if(FindDangerValue($_FILES)) die;

EncodePostData();

//変数をセット
$post = array('name'     => $_POST['name'],
	      'caption'  => $_POST['caption'],
	      'user'     => $_POST['user'],
	      'password' => $_POST['password']);
$label = array('name'     => 'ファイル名',
	       'caption'  => 'ファイルの説明',
	       'user'     => '作成者名',
	       'password' => 'パスワード');
$size = array('name'     => 20,
	      'caption'  => 80,
	      'user'     => 20,
	      'password' => 20);

//引数のエラーチェック
foreach($post as $key => $value){
  //未入力チェック
  if($value == '') OutputUploadResult('<span>' . $label[$key] . '</span> が未入力です。');

  //文字列長チェック
  if(strlen($value) > $size[$key]){
    OutputUploadResult('<span>' . $label[$key] . '</span> は ' .
		       '<span>' . $size[$key] . '</span> 文字以下にしてください。');
  }

  //エスケープ処理
  EscapeStrings($value);
}

//パスワードのチェック
if($post['password'] != $src_upload_password) OutputUploadResult('パスワード認証エラー。');

//ファイルの種類のチェック
$file_name = strtolower(trim($_FILES['file']['name']));
$file_type = $_FILES['file']['type'];
if(! (preg_match('/application\/(octet-stream|zip|lzh|lha|x-zip-compressed)/i', $file_type) &&
      preg_match('/^.*\.(zip|lzh)$/', $file_name))){
  OutputUploadResult('<span>' . $file_name . '</span> : <span>' . $file_type . '</span><br>'."\n".
		     'zip/lzh 以外のファイルはアップロードできません。');
}

//ファイルサイズのチェック
$file_size = $_FILES['file']['size'];
if($file_size == 0 || $file_size > 10 * 1024 * 1024){ //setting.php で設定できるようにする
  OutputUploadResult('ファイルサイズは <span>10 Mbyte</span> まで。');
}


//ファイル番号の取得
$number = (int)file_get_contents('file/number.txt');
if(! ($io = fopen('file/number.txt', 'wb+'))){ //ファイルオープン
  OutputUploadResult('ファイルの IO エラーです。<br>' .
		     '時間をおいてからアップロードしなおしてください。');
}
stream_set_write_buffer($io, 0); //バッファを 0 に指定 (排他制御の保証)

if(! flock($io, LOCK_EX)){ //ファイルのロック
  fclose($io);
  OutputUploadResult('ファイルのロックエラーです。<br>' .
		     '時間をおいてからアップロードしなおしてください。');
}
rewind($io); //ファイルポインタを先頭に移動
fwrite($io, $number + 1); //インクリメントして書き込み

flock($io, LOCK_UN); //ロック解除
fclose($io); //ファイルのクローズ

//HTMLソースを出力
$number = sprintf("%04d", $number); //桁揃え
$ext    = substr($file_name, -3); //拡張子
$time   = gmdate('Y/m/d (D) H:i:s', TZTime()); //日時
if($file_size > 1024 * 1024) // Mbyte
  $file_size = sprintf('%.2f', $file_size / (1024 * 1024)) . ' Mbyte';
elseif($file_size > 1024) // Kbyte
  $file_size = sprintf('%.2f', $file_size / 1024) . ' Kbyte';
else
  $file_size = sprintf('%.2f', $file_size) . ' byte';

$html = <<<EOF
<td class="link"><a href="file/{$number}.{$ext}">{$post['name']}</a></td>
<td class="type">$ext</td>
<td class="size">$file_size</td>
<td class="explain">{$post['caption']}</td>
<td class="name">{$post['user']}</td>
<td class="date">$time</td>

EOF;

if(! ($io = fopen('html/' . $number . '.html', 'wb+'))){ //ファイルオープン
  OutputUploadResult('ファイルの IO エラーです。<br>' .
		     '時間をおいてからアップロードしなおしてください。');
}
stream_set_write_buffer($io, 0); //バッファを 0 に指定 (排他制御の保証)

if(! flock($io, LOCK_EX)){ //ファイルのロック
  fclose($io);
  OutputUploadResult('ファイルのロックエラーです。<br>' .
		     '時間をおいてからアップロードしなおしてください。');
}
rewind($io); //ファイルポインタを先頭に移動
fwrite($io, $html); //書き込み

flock($io, LOCK_UN); //ロック解除
fclose($io); //ファイルのクローズ

//ファイルのコピー
if(move_uploaded_file($_FILES['file']['tmp_name'], 'file/' . $number . '.' . $ext)){
  OutputUploadResult('ファイルのアップロードに成功しました。');
}
else{
  OutputUploadResult('ファイルのコピー失敗。<br>' .
		     '時間をおいてからアップロードしなおしてください。');
}

// 関数 //
//結果出力
function OutputUploadResult($body){
  OutputHTMLHeader('ファイルアップロード処理', 'src', '../css');
  echo '</head><body>'."\n" . $body . '<br><br>'."\n" .
    '<a href="index.php">←戻る</a>'."\n";
  OutputHTMLFooter(true);
}
?>
