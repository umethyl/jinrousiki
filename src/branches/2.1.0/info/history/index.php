<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('info_functions');
InfoHTML::OutputHeader('サーバ更新履歴', 1);
?>
<!-- サーバの更新履歴を残したい場合はこのページを使用してください -->
</body>
</html>
