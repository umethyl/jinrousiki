<?php
require_once(dirname(__FILE__) . '/include/functions.php');
OutputHTMLHeader('�桼�������������', 'icon_view')
?>
</head>
<body>
<a href="index.php">�����</a><br>
<img class="title" src="img/icon_view_title.jpg"><br>
<div class="link"><a href="icon_upload.php">������������Ͽ</a></div>

<fieldset><legend>�桼�������������</legend>
<table><tr>
<?php
$dbHandle = ConnectDatabase(true); //DB ��³

//�桼����������Υơ��֥뤫����������
$list = mysql_query("SELECT icon_name, icon_filename, icon_width, icon_height, color
 		     FROM user_icon WHERE icon_no > 0 ORDER BY icon_no");
$count = mysql_num_rows($list); //�����ƥ�θĿ������

//ɽ�ν���
for($i=0; $i < $count; $i++){
  //5�Ĥ��Ȥ˲���
  if($i > 0 && ($i % 5) == 0) echo '</tr><tr>'."\n";
  $array = mysql_fetch_assoc($list);
  $name     = $array['icon_name'];
  $filename = $array['icon_filename'];
  $width    = $array['icon_width'];
  $height   = $array['icon_height'];
  $color    = $array['color'];
  $location = $ICON_CONF -> path . '/' . $filename;

  echo <<< EOF
<td><img src="$location" width="$width" height="$height" style="border-color:$color;"></td>
<td class="name">$name<br><font color="$color">��</font>$color</td>

EOF;
}

DisconnectDatabase($dbHandle);
?>
</tr></table>
</fieldset>
</body>
</html>
