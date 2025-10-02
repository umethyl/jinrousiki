<?php
require_once('include/init.php');
Loader::LoadFile('index_functions');
IndexHTML::OutputHeader();
?>
<a href="./"><img src="img/top_title.jpg" title="汝は人狼なりや？" alt="汝は人狼なりや？"></a>
<div class="comment"><?php echo ServerConfig::COMMENT; ?></div>
<noscript>&lt;&lt; JavaScriptを有効にしてください &gt;&gt;</noscript>
<table id="main"><tr>
<td>
  <div class="menu">メニュー</div>
  <ul>
    <li><a href="info/script_info.php">特徴と仕様</a></li>
    <li><a href="info/rule.php">ゲームのルール</a></li>
    <li><a href="info/chaos.php">闇鍋モード</a></li>
    <li><a href="info/new_role/">新役職情報</a></li>
    <li><a href="info/shared_room.php">関連サーバ村情報</a></li>
    <li><a href="info/">その他の情報一覧</a></li>
    <li>★☆★☆★☆★</li>
    <li><a href="old_log.php">ログ閲覧</a> (<a href="old_log.php?watch=on">勝敗隠蔽</a>)</li>
    <li><a href="log/">HTML化ログ</a></li>
    <li>★☆★☆★☆★</li>
    <li><a href="icon_view.php">アイコン一覧</a></li>
    <li><a href="icon_upload.php">アイコン登録</a></li>
    <li>★☆★☆★☆★</li>
    <li><a href="src/">ソースコードダウンロード</a></li>
  </ul>
  <?php IndexHTML::OutputMenu(); ?>
</td>
<td>
  <fieldset>
    <legend>Information <a href="info/history/top.php">～過去のinformationはこちら～</a></legend>
    <div class="information"><?php include_once('info/top.php'); ?></div>
  </fieldset>
  <fieldset>
    <legend>ゲーム一覧</legend>
    <div class="game-list"><?php include_once('room_manager.php'); ?></div>
  </fieldset>
  <?php IndexHTML::OutputBBS(); ?>
  <fieldset>
    <legend>村の作成</legend><?php RoomManager::OutputCreate(); ?>
  </fieldset>
</td>
</tr></table>
<?php
IndexHTML::OutputFooter();
