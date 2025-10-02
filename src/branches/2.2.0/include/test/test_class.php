<?php
//-- 村配役テスト --//
class CastTest {
  static function Output() {
    HTML::OutputHeader('配役テスト', 'game_play', true);
    GameHTML::OutputPlayer();
    Vote::AggregateGameStart();
    DB::$ROOM->date++;
    DB::$ROOM->scene = 'night';
    foreach (DB::$USER->rows as $user) $user->Reparse();
    GameHTML::OutputPlayer();
    HTML::OutputFooter();
  }
}

//-- オプション配役テスト --//
class RoleTest {
  const LABEL = '<input type="radio" id="%s" name="%s" value="%s"%s><label for="%s">%s</label>';
  const BOX   = '<input type="checkbox" id="%s" name="%s" value="on"><label for="%s">%s</label>';

  //出力
  static function Output() {
    DevHTML::OutputFormHeader('配役テストツール', 'role_test.php');
    self::OutputForm();
    if (DevHTML::IsExecute()) self::Execute();
    HTML::OutputFooter(true);
  }

  //フォーム出力
  private static function OutputForm() {
    $id    = 'game_option';
    $stack = array(
      'normal' => '普通', 'chaos' => '闇鍋', 'chaosfull' => '真・闇鍋', 'chaos_hyper' => '超・闇鍋',
      'chaos_verso' => '裏・闇鍋', 'duel' => '決闘', 'duel_auto_open_cast' => '自動公開決闘',
      'duel_not_open_cast' => '非公開決闘', 'gray_random' => 'グレラン', 'step' => '足音',
      'quiz' => 'クイズ');
    RQ::Get()->ParsePostData($id);
    $checked_key = array_key_exists(RQ::Get()->$id, $stack) ? RQ::Get()->$id : 'chaos_hyper';
    foreach ($stack as $key => $value) {
      $label   = $id . '_' . $key;
      $checked = $checked_key == $key ? ' checked' : '';
      Text::Output(sprintf(self::LABEL, $label, $id, $key, $checked, $label, $value));
    }
    Text::d();
    foreach (array('replace_human', 'change_common', 'change_mad', 'change_cupid') as $option) {
      $count = 0;
      RQ::Get()->ParsePostData($option);
      foreach (GameOptionConfig::${$option.'_selector_list'} as $key => $mode) {
	if (++$count % 10 == 0) Text::d();
	if (is_int($key)) {
	  $value   = $mode;
	  $checked = RQ::Get()->$option == $mode ? ' checked' : '';
	  $name    = OptionManager::GenerateCaption($mode);
	} else {
	  $value   = '';
	  $checked = RQ::Get()->$option == '' ? ' checked' : '';
	  $name    = $mode;
	}
	$label = $option . (is_int($key) ? '_' . $key : '');
	Text::Output(sprintf(self::LABEL, $label, $option, $value, $checked, $label, $name));
      }
      Text::d();
    }

    foreach (array('topping', 'boost_rate') as $option) {
      $count = -1;
      RQ::Get()->ParsePostData($option);
      foreach (GameOptionConfig::${$option.'_list'} as $key => $mode) {
	if (++$count % 9 == 0 && $count > 0) Text::d();
	$label   = $option . (is_int($key) ? '_' . $key : '');
	$checked = RQ::Get()->$option == $key ? ' checked' : '';
	Text::Output(sprintf(self::LABEL, $label, $option, $key, $checked, $label, $mode));
      }
      Text::d();
    }

    $stack = array(
      'gerd' => 'ゲルト君', 'poison' => '毒', 'assassin' => '暗殺', 'wolf' => '人狼',
      'boss_wolf' => '白狼', 'poison_wolf' => '毒狼', 'tongue_wolf' => '舌禍狼',
      'possessed_wolf' => '憑狼', 'sirius_wolf' => '天狼', 'fox' => '妖狐', 'child_fox' => '子狐',
      'cupid' => 'QP', 'medium' => '巫女', 'mania' => 'マニア', 'detective' => '探偵',
      'festival' => 'お祭り', 'limit_off' => 'リミッタオフ');
    $count = 0;
    foreach ($stack as $option => $name) {
      if (++$count % 14 == 0) Text::d();
      $id = 'option_' . $option;
      Text::Output(sprintf(self::BOX, $id, $option, $id, $name));
    }
    Text::Output('</form>');
  }

  //実行
  private static function Execute() {
    RQ::InitTestRoom();
    $stack = new StdClass();
    $stack->game_option = array('dummy_boy');
    $stack->option_role = array();

    switch (RQ::Get()->game_option) { //メインオプション
    case 'chaos':
    case 'chaosfull':
    case 'chaos_hyper':
    case 'chaos_verso':
    case 'duel':
    case 'gray_random':
    case 'step':
    case 'quiz':
      $stack->game_option[] = RQ::Get()->game_option;
      break;

    case 'duel_auto_open_cast':
      $stack->game_option[] = 'duel';
      $stack->option_role[] = 'auto_open_cast';
      break;

    case 'duel_not_open_cast':
      $stack->game_option[] = 'duel';
      $stack->option_role[] = 'not_open_cast';
      break;
    }

    //置換系
    foreach (array('replace_human', 'change_common', 'change_mad', 'change_cupid') as $option) {
      RQ::Get()->ParsePostData($option);
      if (empty(RQ::Get()->$option)) continue;
      $list = $option . '_selector_list';
      if (array_search(RQ::Get()->$option, GameOptionConfig::$$list) !== false) {
	$stack->option_role[] = RQ::Get()->$option;
      }
    }

    //闇鍋用オプション
    foreach (array('topping', 'boost_rate') as $option) {
      RQ::Get()->ParsePostData($option);
      if (empty(RQ::Get()->$option)) continue;
      if (array_key_exists(RQ::Get()->$option, GameOptionConfig::${$option.'_list'})) {
	$stack->option_role[] = $option . ':' . RQ::Get()->$option;
      }
    }

    //普通村向けオプション
    $option_stack = array(
      'gerd', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
      'possessed_wolf', 'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania',
      'detective');
    RQ::Get()->Parse('post', 'IsOn', $option_stack);
    foreach ($option_stack as $option) {
      if (RQ::Get()->$option) $stack->option_role[] = $option;
    }

    foreach (array('festival') as $option) { //特殊村
      RQ::Get()->ParsePostOn($option);
      if (RQ::Get()->$option) $stack->game_option[] = $option;
    }
    RQ::Get()->ParsePostOn('limit_off');
    if (RQ::Get()->limit_off) ChaosConfig::$role_group_rate_list = array();

    DevRoom::Cast($stack);
  }
}

//-- 裏・闇鍋モードテスト --//
class ChaosVersoTest {
  //出力
  static function Output() {
    DevHTML::OutputFormHeader('裏・闇鍋モード配役テスト', 'chaos_verso.php');
    Text::Output('</form>');
    if (DevHTML::IsExecute()) self::Execute();
    HTML::OutputFooter(true);
  }

  //実行
  private static function Execute() {
    RQ::InitTestRoom();
    $stack = new StdClass();
    $stack->game_option = array('chaos_verso');
    $stack->option_role = array();
    DevRoom::Cast($stack);
  }
}

//-- 役職名表示 --//
class NameTest {
  const LABEL = '<input type="radio" name="type" id="%s" value="%s"><label for="%s">%s</label>';

  //出力
  static function Output() {
    DevHTML::LoadRequest();
    HTML::OutputHeader('役職名表示', 'test/name', true);
    $stack = self::OutputForm();
    if (DevHTML::IsExecute()) self::Execute($stack);
    HTML::OutputFooter();
  }

  //フォーム出力
  private static function OutputForm() {
    echo <<<EOF
<form method="post" action="name_test.php">
<input type="hidden" name="execute" value="on">
<input type="submit" value=" 実 行 "><br>
<input type="radio" name="type" value="all-all" checked>全て

EOF;

    $stack = new StdClass();
    foreach (RoleData::GetList() as $role) { //役職データ収集
      $stack->group[RoleData::GetGroup($role)][]     = $role;
      $stack->camp[RoleData::GetCamp($role, true)][] = $role;
    }
    $count = 0;
    foreach (array('camp' => '陣営', 'group' => '系') as $type => $name) {
      foreach (array_keys($stack->$type) as $role) {
	$count++;
	if ($count > 0 && $count % 9 == 0) Text::d();
	$value = $role . '-' . $type;
	$label = RoleData::GetName($role) . $name;
	Text::Output(sprintf(self::LABEL, $value, $value, $value, $label));
      }
    }
    Text::Output('</form>');

    return $stack;
  }

  //実行
  private static function Execute(StdClass $role_data) {
    RQ::Get()->ParsePostData('type');
    list($role, $type) = explode('-', RQ::Get()->type);
    switch ($type) {
    case 'all':
      $stack = RoleData::GetList();
      break;

    case 'camp':
    case 'group':
      $stack = $role_data->{$type}[$role];
      break;

    default:
      return;
    }
    foreach ($stack as $role) Text::d(RoleDataHTML::GenerateMain($role));
  }
}

//-- 異議ありテスト --//
class ObjectionTest {
  const URL   = 'objection_test.php';
  const RESET = '<p><a href="%s">リセット</a></p>';

  //出力
  static function Output() {
    DevHTML::LoadRequest();
    HTML::OutputHeader('異議ありテスト', null, true);
    $stack = self::OutputForm();
    if (DevHTML::IsExecute()) self::Execute($stack);
    HTML::OutputFooter();
  }

  //フォーム出力
  private static function OutputForm() {
    $form = <<<EOF
<tr><td class="objection"><form method="post" action="%s">
<input type="hidden" name="execute" value="on">
<input type="hidden" name="set_objection" value="%s">
<input type="image" name="objimage" src="%s" border="0"> (%s)
</form></td></tr>

EOF;
    $image = JINRO_ROOT . '/' . GameConfig::OBJECTION_IMAGE;
    $stack = array(
      'entry'            => '入村',
      'full'             => '定員',
      'morning'          => '夜明け',
      'revote'           => '再投票',
      'novote'           => '未投票告知',
      'alert'            => '未投票警告',
      'objection_male'   => '異議あり(男)',
      'objection_female' => '異議あり(女)');

    printf(self::RESET, self::URL);
    Text::Output();
    Text::Output('<table>');
    foreach ($stack as $key => $value) printf($form, self::URL, $key, $image, $value);
    Text::Output('</table>');

    return $stack;
  }

  //実行
  private static function Execute(array $stack) {
    $id = 'set_objection';
    RQ::Get()->ParsePostData($id);
    $key = RQ::Get()->$id;
    if (array_key_exists($key, $stack)) {
      Text::p($stack[$key]);
      Sound::Output($key);
    }
  }
}

//-- トリップテスト --//
class TripTest {
  //出力
  static function Output() {
    DevHTML::LoadRequest();
    HTML::OutputHeader('トリップテスト', null, true);
    self::OutputForm();
    if (DevHTML::IsExecute()) self::Execute();
    HTML::OutputFooter();
  }

  //フォーム出力
  private static function OutputForm() {
    echo <<<EOF
<form method="post" action="trip_test.php">
<input type="hidden" name="execute" value="on">
<label for="trip">トリップキー:</label> <input type="text" id="trip" name="trip" size="20" value="">
</form>

EOF;
  }

  //実行
  private static function Execute() {
    RQ::Get()->ParsePost('Trip', 'trip');
    Text::p(RQ::Get()->trip, '変換結果');
  }
}

//-- Twitter 投稿テスト --//
class TwitterTest {
  //出力
  static function Output() {
    DevHTML::LoadRequest();
    HTML::OutputHeader('Twitter 投稿テスト', null, true);
    self::OutputForm();
    if (DevHTML::IsExecute()) self::Execute();
    HTML::OutputFooter();
  }

  //フォーム出力
  private static function OutputForm() {
    echo <<<EOF
<form method="post" action="twitter_test.php">
<input type="hidden" name="execute" value="on">
<table border="0">
<tr><td><label>番地</label></td><td><input type="text" name="number" size="5" value="1"></td></tr>
<tr><td><label>名前</label></td><td><input type="text" name="name" size="30" value=""></td></tr>
<tr><td><label>コメント</label></td><td><input type="text" name="comment" size="30" value=""></td></tr>
<tr><td colspan="2"><input type="submit" value=" 実 行 "></td></tr>
</table>
</form>

EOF;
  }

  //実行
  private static function Execute() {
    RQ::Get()->ParsePostInt('number');
    RQ::Get()->ParsePostData('name', 'comment');
    if (JinrouTwitter::Send(RQ::Get()->number, RQ::Get()->name, RQ::Get()->comment)) {
      Text::d('Twitter 投稿成功');
    }
  }
}

//投票テスト
class VoteTest {
  //投票画面出力
  static function OutputVote() {
    Loader::LoadFile('vote_message');

    $stack = new RequestGameVote();
    RQ::Set('vote',       $stack->vote);
    RQ::Set('target_no',  $stack->target_no);
    RQ::Set('situation',  $stack->situation);
    RQ::Set('add_action', $stack->add_action);
    RQ::Set('back_url',   '<a href="vote_test.php">戻る</a>');

    if (RQ::Get()->vote) { //投票処理
      HTML::OutputHeader('投票テスト', 'game_play', true); //HTMLヘッダ
      if (RQ::Get()->target_no == 0) { //空投票検出
	HTML::OutputResult('空投票', '投票先を指定してください');
      }
      elseif (DB::$ROOM->IsDay()) { //昼の処刑投票処理
	//Vote::VoteDay();
      }
      elseif (DB::$ROOM->IsNight()) { //夜の投票処理
	Vote::VoteNight();
      }
      else { //ここに来たらロジックエラー
	VoteHTML::OutputError('投票コマンドエラー', '投票先を指定してください');
      }
    }
    else {
      RQ::Set('post_url', 'vote_test.php');
      DB::$SELF->last_load_scene = DB::$ROOM->scene;

      if (DB::$SELF->IsDead()) {
	DB::$SELF->IsDummyBoy() ? VoteHTML::OutputDummyBoy() : VoteHTML::OutputHeaven();
      }
      else {
	switch (DB::$ROOM->scene) {
	case 'beforegame':
	  VoteHTML::OutputBeforeGame();
	  break;

	case 'day':
	  VoteHTML::OutputDay();
	  break;

	case 'night':
	  VoteHTML::OutputNight();
	  break;

	default: //ここに来たらロジックエラー
	  VoteHTML::OutputError('投票シーンエラー');
	  break;
	}
      }
    }
    DB::$SELF = DB::$USER->ByID(1);
    GameHTML::OutputPlayer();
    HTML::OutputFooter(true);
  }

  //配役情報出力
  static function OutputCast() {
    Loader::LoadFile('chaos_config');

    HTML::OutputHeader('投票テスト', 'game_play', true);
    Text::Output('<table border="1" cellspacing="0">');
    echo '<tr><th>人口</th>';
    foreach (ChaosConfig::$role_group_rate_list as $group => $rate) {
      $role = RoleData::GetGroup($group);
      printf('<th class="%s">%s</th>', RoleData::GetCSS($role), RoleData::GetShortName($role));
    }
    Text::Output('</tr>');
    for ($i = 8; $i <= 40; $i++) {
      printf('<tr align="right"><td><strong>%d</strong></td>', $i);
      foreach (ChaosConfig::$role_group_rate_list as $rate) {
	printf('<td>%d</td>', round($i / $rate));
      }
      Text::Output('</tr>');
    }
    Text::Output('</table>');
    HTML::OutputFooter(true);
  }

  //発言出力
  static function OutputTalk() {
    Loader::LoadFile('talk_class');

    RQ::Set('add_role', false);
    RQ::GetTest()->talk = array();
    foreach (RQ::GetTest()->talk_data->{DB::$ROOM->scene} as $stack) {
      RQ::GetTest()->talk[] = new TalkParser($stack);
    }

    HTML::OutputHeader('投票テスト', 'game_play');
    echo DB::$ROOM->GenerateCSS();
    HTML::OutputBodyHeader();
    //Text::p(RQ::GetTest()->talk);
    GameHTML::OutputPlayer();
    if (DB::$SELF->id > 0) RoleHTML::OutputAbility();
    Talk::Output();
    HTML::OutputFooter(true);
  }

  //役職画像出力
  static function OutputImage(array $list) {
    HTML::OutputHeader('投票テスト', 'game_play', true);
    if ($list['main']) {
      foreach (RoleData::GetList() as $role) {
	if (Image::Role()->Exists($role)) Image::Role()->Output($role);
      }
    }
    if ($list['sub']) {
      foreach (RoleData::GetList(true) as $role) {
	if (Image::Role()->Exists($role)) Image::Role()->Output($role);
      }
    }
    if ($list['result']) {
      foreach (RoleData::GetList() as $role) {
	Image::Role()->Output('result_'.$role);
      }
    }
    if ($list['weather']) {
      foreach (WeatherData::$list as $stack) {
	Image::Role()->Output('prediction_weather_'.$stack['event']);
      }
    }
    HTML::OutputFooter(true);
  }
}

//足音投票テスト
class StepVoteTest {
  static function Output() {
    Loader::LoadFile('vote_message');

    $stack = new RequestGameVote();
    RQ::Set('vote',       $stack->vote);
    RQ::Set('target_no',  $stack->target_no);
    RQ::Set('situation',  $stack->situation);
    RQ::Set('add_action', $stack->add_action);
    RQ::Set('back_url',   '<a href="step_vote_test.php">戻る</a>');

    if (RQ::Get()->vote) { //投票処理
      HTML::OutputHeader('投票テスト', 'game_play', true); //HTMLヘッダ
      if (RQ::Get()->target_no == 0) { //空投票検出
	HTML::OutputResult('空投票', '投票先を指定してください');
      }
      elseif (DB::$ROOM->IsDay()) { //昼の処刑投票処理
	//Vote::VoteDay();
      }
      elseif (DB::$ROOM->IsNight()) { //夜の投票処理
	Vote::VoteNight();
      }
      else { //ここに来たらロジックエラー
	VoteHTML::OutputError('投票コマンドエラー', '投票先を指定してください');
      }
    }
    else {
      RQ::Set('post_url', 'step_vote_test.php');
      DB::$SELF->last_load_scene = DB::$ROOM->scene;

      if (DB::$SELF->IsDead()) {
	DB::$SELF->IsDummyBoy() ? VoteHTML::OutputDummyBoy() : VoteHTML::OutputHeaven();
      }
      else {
	switch(DB::$ROOM->scene) {
	case 'beforegame':
	  VoteHTML::OutputBeforeGame();
	  break;

	case 'day':
	  VoteHTML::OutputDay();
	  break;

	case 'night':
	  VoteHTML::OutputNight();
	  break;

	default: //ここに来たらロジックエラー
	  VoteHTML::OutputError('投票シーンエラー');
	  break;
	}
      }
    }
    DB::$SELF = DB::$USER->ByID(1);
    GameHTML::OutputPlayer();
    HTML::OutputFooter(true);
  }
}
