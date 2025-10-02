<?php
//-- HTML 生成クラス (ソースダウンロード拡張) --//
class SrcHTML {
  //Index ページ出力
  static function Output() {
    $caption = self::GetCaption();

    HTML::OutputHeader('開発版ソースダウンロード', 'src', true);
    Text::Output('<a href="../">←戻る</a>', true);
    echo <<<EOF
<p>
※「alpha」が付いているバージョンは、ほとんどテストを行っていません。取り扱い要注意。
</p>
<p>
※「beta」が付いているバージョンは開発チーム内の情報交換用です。基本的に安定性は保証されません。
</p>
<p>
※Ver. 1.4.0β18 よりからは <a href="http://sourceforge.jp/projects/jinrousiki/">SourceForge</a> にパッケージをアップロードしています。
</p>
<table id="download">
<caption>定置ファイル</caption>
{$caption}
<tr>
<td class="link"><a href="fix/jinrousiki-1.3.1.zip">Ver. 1.3.1</a></td>
<td class="type">zip</td>
<td class="size">1.27 Mbyte</td>
<td class="explain">Ver. 1.3.1　PHPの浮動小数点数に関するバグに対応</td>
<td class="name">埋めチル</td>
<td class="date">2011/01/09</td>
</tr>
<tr>
<td class="link"><a href="fix/jinro_php_1.3.0.zip">Ver. 1.3.0</a></td>
<td class="type">zip</td>
<td class="size">1.25 Mbyte</td>
<td class="explain">Ver. 1.3.0　正式リリース。過去ログのTITLE変更、「」追加オプション実装</td>
<td class="name">お肉</td>
<td class="date">2009/07/11</td>
</tr>
<tr>
<td class="link"><a href="fix/jinro_php_1.2.3.UTF-8.zip">Ver. 1.2.3.UTF-8</a></td>
<td class="type">zip</td>
<td class="size">1.19 Mbyte</td>
<td class="explain">Ver. 1.2.2 の UTF-8 対応版（文字コード変更、旧ログ化ける）</td>
<td class="name">ねこねこ</td>
<td class="date">2009/06/23</td>
</tr>
<tr>
<td class="link"><a href="fix/jinro_php_1.2.2a.zip">Ver. 1.2.2a</a></td>
<td class="type">zip</td>
<td class="size">1.21 Mbyte</td>
<td class="explain">ソースコード Ver. 1.2.2a</td>
<td class="name">埋めチル</td>
<td class="date">2009/06/04</td>
</tr>
<tr>
<td class="link"><a href="fix/jinro_php_1.2.1.zip">Ver. 1.2.1</a>
<td class="type">zip</td>
<td class="size">1.19 Mbyte</td>
<td class="explain">ソースコード Ver. 1.2.1</td>
<td class="name">お肉</td>
<td class="date">2009/04/15</td>
</tr>
</table>

EOF;
    self::OutputUploadFile();
    echo <<<EOF
<form method="POST" action="upload.php" enctype="multipart/form-data">
<table id="upload">
<tr>
  <td><label>ファイル選択</label></td>
  <td><input type="file" name="file" size="60"></td>
</tr>
<tr>
  <td><label>ファイル名</label></td>
  <td><input type="text" name="name" maxlength="20" size="20"></td>
</tr>
<tr>
  <td><label>ファイルの説明</label></td>
  <td><input type="text" name="caption" maxlength="80" size="80"></td>
</tr>
<tr>
  <td><label>作成者名</label></td>
  <td><input type="text" name="user" maxlength="20" size="20"></td>
</tr>
<tr>
  <td><label>パスワード</label></td>
  <td><input type="password" name="password" maxlength="20" size="20"></td>
</tr>
<tr>
  <td><input type="submit" value="アップロード"></td>
  <td><label>対応拡張子は zip, lzh のみ</label></td>
</tr>
</table>
</form>

EOF;
    HTML::OutputFooter();
  }

  //アップロード処理
  static function Upload() {
    if (SourceUploadConfig::DISABLE) {
      HTML::OutputResult('ファイルアップロード', '現在アップロードは停止しています。');
    }

    Loader::LoadRequest('RequestSrcUpload');
    foreach (RQ::$get as $key => $value) { //引数のエラーチェック
      if (is_object($value)) continue;
      $label = SourceUploadConfig::$form_list[$key]['label'];
      $size  = SourceUploadConfig::$form_list[$key]['size'];

      //未入力チェック
      if ($value == '') self::OutputResult(sprintf('<span>%s</span>が未入力です。', $label));

      if (strlen($value) > $size) { //文字列長チェック
	$format = '<span>%s</span>は <span>%s</span> 文字以下にしてください。';
	self::OutputResult(sprintf($format, $label, $size));
      }
    }

    if (RQ::$get->password == SourceUploadConfig::PASSWORD) { //パスワードのチェック
      self::OutputResult('パスワード認証エラー。');
    }

    //ファイルの種類のチェック
    //Text::p(RQ::$get->file);
    $file_name = strtolower(trim(RQ::$get->file->name));
    $file_type = RQ::$get->file->type;
    if (! (preg_match('/application\/(octet-stream|zip|lzh|lha|x-zip-compressed)/i', $file_type) &&
	   preg_match('/^.*\.(zip|lzh)$/', $file_name))) {
      Text::p(RQ::$get->file);
      $str = sprintf("<span>%s</span> : <span>%s</span><br>\n", $file_name, $file_type);
      self::OutputResult($str . 'zip/lzh 以外のファイルはアップロードできません。');
    }

    //ファイルサイズのチェック
    $file_size = RQ::$get->file->size;
    if ($file_size == 0 || $file_size > SourceUploadConfig::MAX_SIZE) {
      $str = sprintf('ファイルサイズは <span>%dbyte</span> まで。', SourceUploadConfig::MAX_SIZE);
      self::OutputResult($str);
    }

    //ファイル番号の取得
    $number = (int)file_get_contents('file/number.txt');
    $footer = '<br>時間をおいてからアップロードしなおしてください。';
    if (! ($io = fopen('file/number.txt', 'wb+'))) { //ファイルオープン
      self::OutputResult('ファイルの IO エラーです。' . $footer);
    }
    stream_set_write_buffer($io, 0); //バッファを 0 に指定 (排他制御の保証)

    if (! flock($io, LOCK_EX)) { //ファイルのロック
      fclose($io);
      self::OutputResult('ファイルのロックエラーです。' . $footer);
    }
    rewind($io); //ファイルポインタを先頭に移動
    fwrite($io, $number + 1); //インクリメントして書き込み

    flock($io, LOCK_UN); //ロック解除
    fclose($io); //ファイルのクローズ

    //HTMLソースを出力
    $number = sprintf('%04d', $number); //桁揃え
    $ext    = substr($file_name, -3); //拡張子
    $time   = Time::GetDate('Y/m/d (D) H:i:s', Time::Get()); //日時
    if ($file_size > 1024 * 1024) { // Mbyte
      $file_size = sprintf('%.2f', $file_size / (1024 * 1024)) . ' Mbyte';
    }
    elseif ($file_size > 1024) { // Kbyte
      $file_size = sprintf('%.2f', $file_size / 1024) . ' Kbyte';
    }
    else {
      $file_size = sprintf('%.2f', $file_size) . ' byte';
    }

    $html = <<<EOF
<td class="link"><a href="file/{$number}.{$ext}">{RQ::$get->name}</a></td>
<td class="type">$ext</td>
<td class="size">$file_size</td>
<td class="explain">{RQ::$get->caption}</td>
<td class="name">{RQ::$get->user}</td>
<td class="date">$time</td>

EOF;

    if (! ($io = fopen('html/' . $number . '.html', 'wb+'))) { //ファイルオープン
      self::OutputResult('ファイルの IO エラーです。' . $footer);
    }
    stream_set_write_buffer($io, 0); //バッファを 0 に指定 (排他制御の保証)

    if (! flock($io, LOCK_EX)) { //ファイルのロック
      fclose($io);
      self::OutputResult('ファイルのロックエラーです。' . $footer);
    }
    rewind($io); //ファイルポインタを先頭に移動
    fwrite($io, $html); //書き込み

    flock($io, LOCK_UN); //ロック解除
    fclose($io); //ファイルのクローズ

    //ファイルのコピー
    if (move_uploaded_file(RQ::$get->file->tmp_name, 'file/' . $number . '.' . $ext)) {
      self::OutputResult('ファイルのアップロードに成功しました。');
    } else {
      self::OutputResult('ファイルのコピー失敗。' . $footer);
    }
  }

  //キャプション取得
  private static function GetCaption() {
    return <<<EOF
<tr class="caption">
<td>ファイル</td>
<td>拡張子</td>
<td>サイズ</td>
<td>説明</td>
<td>作成者</td>
<td>日時</td>
</tr>
EOF;
  }

  //アップロードされたファイル出力
  private static function OutputUploadFile() {
    $stack = array();
    if ($handle = opendir('html')) {
      while (($file = readdir($handle)) !== false) {
	if ($file != '.' && $file != '..' && $file != 'index.html') $stack[] = $file;
      }
      closedir($handle);
    }
    if (count($stack) < 1) return;
    rsort($stack);

    $str = '<table id="download">'."\n".'<caption>アップロードされたファイル</caption>';
    $str .= self::GetCaption();
    foreach ($stack as $file) {
      $str .= '<tr>'."\n";
      if ($html = file_get_contents('html/' . $file)) {
	$str .= $html;
      } else {
	$str .= '<td colspan="6">読み込み失敗: ' . $file . '</td>'."\n";
      }
      $str .= '<tr>'."\n";
    }
    echo $str . '</table>'."\n";
  }

  //結果出力
  private static function OutputResult($body) {
    HTML::OutputHeader('ファイルアップロード処理', 'src', true);
    echo $body . '<br><br>' . "\n" . '<a href="./">←戻る</a>'."\n";
    HTML::OutputFooter(true);
  }
}