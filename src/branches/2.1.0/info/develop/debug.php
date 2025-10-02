<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('info_functions');
InfoHTML::OutputHeader('デバッグ情報', 1, 'debug');
?>
<p>
<a href="debug_1.4.php">～ 1.4</a>
<a href="debug_1.5.php">1.5</a>
</p>
</body>
</html>
