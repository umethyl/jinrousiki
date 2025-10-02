<table id="download">
<caption>定置ファイル</caption>
<?php
$caption = <<<EOF
<tr class="caption">
  <td>ファイル</td>
  <td>拡張子</td>
  <td>サイズ</td>
  <td>説明</td>
  <td>作成者</td>
  <td>日時</td>
</tr>

EOF;
echo <<<EOF
<br>
※betaがついているバージョンは開発チーム内の情報交換用です。<br>
　基本的に安定性は保証されません。<br><br>
$caption
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

$array = array();
if($handle = opendir('html')){
  while (false !== ($file = readdir($handle))){
    if($file != '.' && $file != '..') array_push($array, $file);
  }
  closedir($handle);
}
if(count($array) < 1) return;
rsort($array);

echo '<table id="download">'."\n" . '<caption>アップロードされたファイル</caption>' . $caption;
foreach($array as $key => $file){
  echo '<tr>'."\n";
  if($html = file_get_contents('html/' . $file)){
    echo $html;
  }
  else{
    echo '<td colspan="6">読み込み失敗: ' . $file . '</td>'."\n";
  }
  echo '<tr>'."\n";
}
echo '</table>'."\n";
?>
