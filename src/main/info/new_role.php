<?php
require_once('init.php');
HTML::OutputHeader(ServerConfig::TITLE . ' [新役職情報]', 'index', true);
Text::Output('<a href="../">TOP に戻る</a><br>');
Text::Output('<p>※「新役職について」は移動しました → <a href="new_role/">新役職情報</a></p>');
HTML::OutputFooter();
