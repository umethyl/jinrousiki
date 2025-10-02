<?php
//-- ◆ 文字化け抑制 --//
require_once('init.php');
HTML::OutputFrameHeader('[新役職情報]');
echo <<< EOF
<frameset cols="170, *" border="1" frameborder="1" framespacing="1" bordercolor="#C0C0C0">
<frame name="menu" src="menu.php">
<frame name="body" src="summary.php">

EOF;
HTML::OutputFrameFooter();
