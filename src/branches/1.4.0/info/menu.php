<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
OutputHTMLHeader($SERVER_CONF->title . $SERVER_CONF->comment, 'info_menu');
?>
</head>
<body>
<div class="menu">情報一覧</div>
<ul>
<li><a href="../" target="_top">← TOP</a></li>
<li>★☆★☆★☆★</li>

<li><a href="script_info.php" target="body">特徴と仕様</a></li>
<li><a href="rule.php" target="body">ゲームのルール</a></li>
<li><a href="cast.php" target="body">配役一覧</a></li>
<li><a href="game_option.php" target="body">ゲームオプション</a></li>
<li><a href="chaos.php" target="body">闇鍋モード</a></li>
<li><a href="new_role/" target="_top">新役職情報</a></li>
<li><a href="spec.php" target="body">詳細な仕様</a></li>
<li><a href="shared_room.php" target="body">関連サーバ村情報</a></li>
<li><a href="copyright.php" target="body">謝辞・素材</a></li>
<li>★☆★☆★☆★</li>

<li><a href="history/" target="body">サーバ更新履歴</a></li>
<li><a href="history/top.php" target="body">TOPページ更新履歴</a></li>
<li>★☆★☆★☆★</li>

<li><a href="develop/" target="body">開発者向け情報</a></li>
<li><a href="develop/history.php" target="body">開発履歴</a></li>
<li><a href="develop/debug.php" target="body">デバッグ情報</a></li>
<li><a href="http://sourceforge.jp/projects/jinrousiki/" target="_top">SourceForge</a></li>
<li><a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1240771280/l50" target="_top">開発・バグ報告スレ</a></li>
<li><a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/l50" target="_top">新役職提案スレ</a></li>
</ul>
</body></html>
