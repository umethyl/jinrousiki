<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');

$DISABLE_TWITTER_TEST = true; //false にすると使用可能になる
if ($DISABLE_TWITTER_TEST) {
  HTML::OutputResult('認証エラー', 'このスクリプトは使用できない設定になっています。');
}
Loader::LoadFile('test_class', 'twitter_class');
TwitterTest::Output();
