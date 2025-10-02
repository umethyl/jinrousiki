<?php
require_once(dirname(__FILE__) . '/include/functions.php');
OutputHTMLHeader('ユーザアイコン一覧', 'icon_view')
?>
</head>
<body>
<a href="index.php">←戻る</a><br>
<img class="title" src="img/icon_view_title.jpg"><br>
<div class="link"><a href="icon_upload.php">→アイコン登録</a></div>

<fieldset><legend>ユーザアイコン一覧</legend>
<table><tr>
<?php
$dbHandle = ConnectDatabase(true); //DB 接続

//ユーザアイコンのテーブルから一覧を取得
$list = mysql_query("SELECT icon_name, icon_filename, icon_width, icon_height, color
 		     FROM user_icon WHERE icon_no > 0 ORDER BY icon_no");
$count = mysql_num_rows($list); //アイテムの個数を取得

//表の出力
for($i=0; $i < $count; $i++){
  //5個ごとに改行
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
<td class="name">$name<br><font color="$color">◆</font>$color</td>

EOF;
}

DisconnectDatabase($dbHandle);
?>
</tr></table>
</fieldset>
</body>
</html>
