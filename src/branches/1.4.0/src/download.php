<p>
※「alpha」が付いているバージョンは、ほとんどテストを行っていません。取り扱い要注意。
</p>
<p>
※「beta」が付いているバージョンは開発チーム内の情報交換用です。基本的に安定性は保証されません。
</p>
<p>
※Ver. 1.4.0β18 よりからは <a href="http://sourceforge.jp/projects/jinrousiki/">SourceForge</a> にパッケージをアップロードしています。
</p>
<?php
$array = array();
if($handle = opendir('html')){
  while(($file = readdir($handle)) !== false){
    if($file != '.' && $file != '..' && $file != 'index.html') $array[] = $file;
  }
  closedir($handle);
}
if(count($array) < 1) return;
rsort($array);

$str = '<table id="download">'."\n" . '<caption>アップロードされたファイル</caption>' . $caption;
foreach($array as $key => $file){
  $str .= '<tr>'."\n";
  if($html = file_get_contents('html/' . $file)){
    $str .= $html;
  }
  else{
    $str .= '<td colspan="6">読み込み失敗: ' . $file . '</td>'."\n";
  }
  $str .= '<tr>'."\n";
}
echo $str . '</table>'."\n";
