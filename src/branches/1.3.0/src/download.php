<table id="download">
<caption>���֥ե�����</caption>
<?php
$caption = <<<EOF
<tr class="caption">
  <td>�ե�����</td>
  <td>��ĥ��</td>
  <td>������</td>
  <td>����</td>
  <td>������</td>
  <td>����</td>
</tr>

EOF;
echo <<<EOF
<br>
��beta���Ĥ��Ƥ���С������ϳ�ȯ��������ξ�����ѤǤ���<br>
������Ū�˰��������ݾڤ���ޤ���<br><br>
$caption
<tr>
  <td class="link"><a href="fix/jinro_php_1.2.3.UTF-8.zip">Ver. 1.2.3.UTF-8</a></td>
  <td class="type">zip</td>
  <td class="size">1.19 Mbyte</td>
  <td class="explain">Ver. 1.2.2 �� UTF-8 �б��ǡ�ʸ���������ѹ�������������</td>
  <td class="name">�ͤ��ͤ�</td>
  <td class="date">2009/06/23</td>
</tr>
<tr>
  <td class="link"><a href="fix/jinro_php_1.2.2a.zip">Ver. 1.2.2a</a></td>
  <td class="type">zip</td>
  <td class="size">1.21 Mbyte</td>
  <td class="explain">������������ Ver. 1.2.2a</td>
  <td class="name">������</td>
  <td class="date">2009/06/04</td>
</tr>
<tr>
  <td class="link"><a href="fix/jinro_php_1.2.1.zip">Ver. 1.2.1</a>
  <td class="type">zip</td>
  <td class="size">1.19 Mbyte</td>
  <td class="explain">������������ Ver. 1.2.1</td>
  <td class="name">����</td>
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

echo '<table id="download">'."\n" . '<caption>���åץ��ɤ��줿�ե�����</caption>' . $caption;
foreach($array as $key => $file){
  echo '<tr>'."\n";
  if($html = file_get_contents('html/' . $file)){
    echo $html;
  }
  else{
    echo '<td colspan="6">�ɤ߹��߼���: ' . $file . '</td>'."\n";
  }
  echo '<tr>'."\n";
}
echo '</table>'."\n";
?>
