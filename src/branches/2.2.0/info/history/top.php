<?php
//-- ◆ 文字化け抑制 --//
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('info_functions');
InfoHTML::OutputHeader('過去の Information', 1, 'history_top');
?>
<!-- Information の改訂履歴を残したい場合はこのページを使用してください -->
</body>
</html>
