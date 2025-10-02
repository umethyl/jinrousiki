<?php
//error_reporting(E_ALL);
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');

$disable = true; //使用時には false に変更する
if ($disable) {
  HTML::OutputResult('認証エラー', 'このスクリプトは使用できない設定になっています。');
}
Loader::LoadFile('test_class', 'cast_config');

//-- 仮想村データをセット --//
Loader::LoadRequest('RequestBaseGame', true);

DevRoom::Initialize(array('name' => '表示テスト村', 'scene' => 'day'));
DevUser::Initialize(11,
  array( 1 => 'mage',
	 2 => 'human',
	 3 => 'human',
	 4 => 'human',
	 5 => 'human',
	 6 => 'human',
	 7 => 'necromancer',
	 8 => 'guard',
	 9 => 'wolf',
	10 => 'wolf',
	11 => 'mad'));
DevUser::Complement();
//Text::p(RQ::GetTest()->test_users[10]);

//-- 設定調整 --//
#CastConfig::$decide = 11;
#RQ::GetTest()->test_users[3]->live = 'kick';

//-- データ収集 --//
//DB::Connect(); //DB接続 (必要なときだけ設定する)
DevRoom::Load();
DB::$ROOM->date = 1;
DB::$ROOM->scene = 'beforegame';
#DB::$ROOM->scene = 'day';
#DB::$ROOM->scene = 'night';
#DB::$ROOM->scene = 'aftergame';
switch (@$_GET['scene']) {
case 'beforegame':
case 'day':
case 'night':
  DB::$ROOM->scene = $_GET['scene'];
  break;
}
DevUser::Load();

//テストデータ設定
DB::$USER->rows[3]->live = 'dead';
DB::$USER->rows[7]->live = 'dead';
DB::$USER->rows[8]->live = 'dead';

if (false) {
  switch (intval($_GET['dummy_boy'])) {
  case '1':
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/icon/normal/dummy_boy/dummy_boy_01.jpg';
    break;

  case '2':
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/icon/normal/dummy_boy/dummy_boy_02.gif';
    break;

  case '3':
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/icon/normal/dummy_boy/gerd.jpg';
    break;
  }

  $dead_list = array();
  $dead = intval($_GET['dead']);
  if (array_key_exists($dead - 1, $dead_list)) {
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/normal/dead/' . $dead_list[$dead];
  }

  $wolf = intval($_GET['wolf']) - 1;
  switch ($wolf) {
  case '0':
    IconConfig::$dead = IconConfig::$wolf;
    break;

  case '1':
  case '2':
  case '3':
  case '4':
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/normal/wolf/wolf_0' . $wolf . '.gif';
    break;
  }

  $t_dummy_list = array();
  $t_dummy = is_null($_GET['t_dummy_boy']) ? -1 : intval($_GET['t_dummy_boy']);
  if (array_key_exists($t_dummy, $t_dummy_list)) {
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/icon/touhou/dummy_boy/' . $t_dummy_list[$t_dummy];
  }

  $t_wolf_list = array();
  $t_wolf = is_null($_GET['t_wolf']) ? -1 : intval($_GET['t_wolf']);
  if (array_key_exists($t_wolf, $t_wolf_list)) {
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/icon/touhou/wolf/' . $t_wolf_list[$t_wolf];
  }

  $t_dead_list = array();
  $t_dead = is_null($_GET['t_dead']) ? -1 : intval($_GET['t_dead']);
  if (array_key_exists($t_dead, $t_dead_list)) {
    IconConfig::$dead = JINRO_ROOT . '/dev/skin/icon/touhou/dead/' . $t_dead_list[$t_dead];
  }
}

//-- データ出力 --//
HTML::OutputHeader('表示テスト', 'game_play');
HTML::OutputBodyHeader(sprintf('%s/game_%s', JINRO_CSS, DB::$ROOM->scene));
//Text::p(DB::$ROOM->scene, $_GET['scene']);
GameHTML::OutputPlayer();
HTML::OutputFooter(true);

//Text::p(DB::$USER->rows[1]);
//Text::p($dead_list);
echo <<<EOF
[昼]：<br>
身代わり君：
<a href="view_test.php?dummy_boy=1">1</a> /
<a href="view_test.php?dummy_boy=2">2</a> /
<a href="view_test.php?dummy_boy=3">3</a><br>
人狼：
<a href="view_test.php?wolf=1">1</a> /
<a href="view_test.php?wolf=2">2</a> /
<a href="view_test.php?wolf=3">3</a> /
<a href="view_test.php?wolf=4">4</a> /
<a href="view_test.php?wolf=5">5</a><br>
死亡：
EOF;

foreach (array_keys($dead_list) as $id) {
  echo '<a href="view_test.php?dead=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
身代わり君(東方)：
EOF;
foreach (array_keys($t_dummy_list) as $id) {
  echo '<a href="view_test.php?t_dummy_boy=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
人狼(東方)：
EOF;
foreach (array_keys($t_wolf_list) as $id) {
  echo '<a href="view_test.php?t_wolf=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
死亡(東方)：
EOF;
foreach (array_keys($t_dead_list) as $id) {
  echo '<a href="view_test.php?t_dead=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
<br><br>
[夜]：<br>
身代わり君：
<a href="view_test.php?scene=night&dummy_boy=1">1</a> /
<a href="view_test.php?scene=night&dummy_boy=2">2</a> /
<a href="view_test.php?scene=night&dummy_boy=3">3</a><br>
人狼：
<a href="view_test.php?scene=night&wolf=1">1</a> /
<a href="view_test.php?scene=night&wolf=2">2</a> /
<a href="view_test.php?scene=night&wolf=3">3</a> /
<a href="view_test.php?scene=night&wolf=4">4</a> /
<a href="view_test.php?scene=night&wolf=5">5</a><br>
死亡：
EOF;
foreach (array_keys($dead_list) as $id) {
  echo '<a href="view_test.php?scene=night&dead=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
身代わり君(東方)：
EOF;
foreach (array_keys($t_dummy_list) as $id) {
  echo '<a href="view_test.php?scene=night&t_dummy_boy=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
人狼(東方)：
EOF;
foreach (array_keys($t_wolf_list) as $id) {
  echo '<a href="view_test.php?scene=night&t_wolf=' . $id . '">' . $id . '</a> /'."\n";
}

echo <<<EOF
<br>
死亡(東方)：
EOF;
foreach (array_keys($t_dead_list) as $id) {
  echo '<a href="view_test.php?scene=night&t_dead=' . $id . '">' . $id . '</a> /'."\n";
}

HTML::OutputFooter();
