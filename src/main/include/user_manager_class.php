<?php
//-- ユーザ登録コントローラー --//
class UserManager {
  //ユーザ登録
  static function Entry() {
    extract(RQ::ToArray()); //引数を展開
    $url = sprintf('user_manager.php?room_no=%d', $room_no); //ベースバックリンク
    if ($user_no > 0) $url .= sprintf('&user_no=%d', $user_no); //登録情報変更モード
    $back_url = sprintf('<br><a href="%s">戻る</a>', $url); //バックリンク
    if (GameConfig::TRIP && $trip != '') {
      $trip = Text::Trip('#' . $trip); //トリップ変換
      $uname .= $trip;
    } else {
      $trip = ''; //ブラックリストチェック用にトリップを初期化
    }

    //ブラックリストチェック
    if (! ServerConfig::DEBUG_MODE && Security::IsLoginBlackList($trip)) {
      HTML::OutputResult('村人登録 [入村制限]', '入村制限ホストです。');
    }

    //記入漏れチェック
    $title = '村人登録 [入力エラー]';
    $str   = 'が空です (空白と改行コードは自動で削除されます)。' . $back_url;
    $empty = 'が入力されていません。' . $back_url;
    if ($user_no < 1) {
      if ($uname     == '') HTML::OutputResult($title, 'ユーザ名'     . $str);
      if ($password  == '') HTML::OutputResult($title, 'パスワード'   . $str);
    }
    if ($handle_name == '') HTML::OutputResult($title, '村人の名前'   . $str);
    if ($profile     == '') HTML::OutputResult($title, 'プロフィール' . $str);
    if (! is_int($icon_no)) HTML::OutputResult($title, 'アイコン番号' . $empty);
    if (empty($sex))        HTML::OutputResult($title, '性別'         . $empty);

    //文字数制限チェック
    $format = '%sは%d文字まで' . $back_url;
    $limit_list = array(
      array('str' => $uname,       'name' => 'ユーザ名',     'config' => GameConfig::LIMIT_UNAME),
      array('str' => $handle_name, 'name' => '村人の名前',   'config' => GameConfig::LIMIT_UNAME),
      array('str' => $profile,     'name' => 'プロフィール', 'config' => GameConfig::LIMIT_PROFILE)
			);
    foreach ($limit_list as $limit) {
      if (strlen($limit['str']) > $limit['config']) {
	HTML::OutputResult($title, sprintf($format, $limit['name'], $limit['config']));
      }
    }

    //例外チェック
    if ($uname == 'dummy_boy' || $uname == 'system') {
      $format = 'ユーザ名「%s」は使用できません。%s';
      HTML::OutputResult($title, sprintf($format, $uname, $back_url));
    }
    if ($user_no < 1 && ($handle_name == '身代わり君' || $handle_name == 'システム')) {
      $format = '村人名「%s」は使用できません。%s';
      HTML::OutputResult($title, sprintf($format, $handle_name, $back_url));
    }
    if ($sex != 'male' && $sex != 'female') {
      HTML::OutputResult($title, '無効な性別です。' . $back_url);
    }
    if ($icon_no < ($user_no > 0 ? 0 : 1) || ! IconDB::IsEnable($icon_no)) {
      /* ロック前なのでスキマが存在するが、実用性を考慮してここで判定する */
      HTML::OutputResult($title, '無効なアイコン番号です' . $back_url);
    }

    if (! DB::Transaction()) { //トランザクション開始
      $str = 'サーバが混雑しています。<br>再度登録してください。' . $back_url;
      HTML::OutputResult('村人登録 [サーバエラー]', $str);
    }

    DB::$ROOM = RoomDataDB::LoadEntryUser($room_no); //現在の村情報を取得 (ロック付き)
    if (! DB::$ROOM->IsWaiting()) { //ゲーム開始判定
      HTML::OutputResult('村人登録 [入村不可]', 'すでにゲームが開始されています。');
    }
    DB::$ROOM->ParseOption(); //名前・トリップ必須オプション用

    //DB から現在のユーザ情報を取得 (ロック付き)
    RQ::Load('RequestBase', true);
    RQ::Get()->room_no      = $room_no;
    RQ::Get()->retrive_type = 'entry_user';
    DB::$USER = new UserData(RQ::Get());

    $user_count = DB::$USER->GetUserCount(); //現在の KICK されていない住人の数を取得
    if ($user_no < 1 && $user_count >= DB::$ROOM->max_user) { //定員オーバー判定
      HTML::OutputResult('村人登録 [入村不可]', '村が満員です。');
    }

    //重複チェック (比較演算子は大文字・小文字を区別しないのでクエリで直に判定する)
    $footer = '<br>別の名前にしてください。' . $back_url;

    if ($user_no > 0) { //登録情報変更モード
      $target = UserDB::Load($user_no);
      if ($target->session_id != Session::GetID()) {
	HTML::OutputResult('村人登録 [セッションエラー]', 'セッション ID が一致しません。');
      }
      $target->room_no = RQ::Get()->room_no;

      if (! $target->IsDummyBoy() && ($handle_name == '身代わり君' || $handle_name == 'システム')) {
	$format = '村人名「%s」は使用できません%s';
	HTML::OutputResult($title, sprintf($format, $handle_name, $back_url));
      }

      if (UserDB::IsDuplicateName($user_no, $handle_name)) {
	$str = '村人名が既に登録されています。';
	HTML::OutputResult('村人登録 [重複登録エラー]', $str . $footer);
      }

      $str   = sprintf('%s さんが登録情報を変更しました。', $target->handle_name);
      $stack = array();
      if ($target->handle_name != $handle_name) {
	$stack['handle_name'] = $handle_name;
	$str .= sprintf("\n村人の名前：%s → %s", $target->handle_name, $handle_name);
      }
      if ($target->icon_no != $icon_no) {
	if (! $target->IsDummyBoy() && $icon_no == 0) {
	  HTML::OutputResult($title, '無効なアイコン番号です' . $back_url);
	}
	$stack['icon_no'] = $icon_no;
	$format    = "\nアイコン：No. %d (%s) → No. %d (%s)";
	$icon_name = IconDB::GetName($icon_no);
	$str .= sprintf($format, $target->icon_no, $target->icon_name, $icon_no, $icon_name);
      }
      foreach (array('sex', 'profile', 'role') as $value) {
	if ($target->$value != $$value) $stack[$value] = $$value;
      }
      //Text::p($stack);

      if (count($stack) < 1) {
	$str = '変更点はありません。' . $back_url;
	HTML::OutputResult('村人登録 [登録情報変更]', $str);
      }
      DB::$ROOM->TalkBeforeGame($str, $target->uname, $target->handle_name, $target->color);

      if ($target->UpdateList($stack) && DB::Commit()) {
	UserManagerHTML::OutputChange();
      }
      else {
	$str = 'サーバが混雑しています。<br>再度登録してください。' . $back_url;
	HTML::OutputResult('村人登録 [サーバエラー]', $str);
      }
    }

    //ユーザ名・村人名
    if (DB::$ROOM->IsOption('necessary_name') && strpos($uname, '◆') === 0) {
      HTML::OutputResult($title, 'ユーザ名がありません (トリップのみは不可)');
    }
    if (DB::$ROOM->IsOption('necessary_trip') && strpos($uname, '◆') === false) {
      HTML::OutputResult($title, 'トリップがありません');
    }

    if (UserDB::IsKick($uname)) { //キックされた人と同じユーザ名
      $str = 'キックされた人と同じユーザ名は使用できません (村人名は可)。';
      HTML::OutputResult('村人登録 [キックされたユーザ]', $str . $footer);
    }

    if (UserDB::IsDuplicate($uname, $handle_name)) {
      $str = 'ユーザ名、または村人名が既に登録してあります。';
      HTML::OutputResult('村人登録 [重複登録エラー]', $str . $footer);
    }
    //HTML::OutputResult('トリップテスト', $uname.'<br>'.$handle_name.$back_url); //テスト用

    //IP アドレスチェック
    if (! ServerConfig::DEBUG_MODE && GameConfig::LIMIT_IP && UserDB::IsDuplicateIP()) {
      HTML::OutputResult('村人登録 [多重登録エラー]', '多重登録はできません。');
    }

    //DB にユーザデータを登録
    $user_no = count(DB::$USER->name) + 1; //KICK された住人も含めた新しい番号を振る
    if (DB::InsertUser($room_no, $uname, $handle_name, $password, $user_no, $icon_no, $profile,
		       $sex, $role, Session::GetID(true))) {
      //クッキーの初期化
      DB::$ROOM->system_time = Time::Get(); //現在時刻を取得
      $cookie_time = DB::$ROOM->system_time - 3600;
      setcookie('scene',      '', $cookie_time);
      setcookie('vote_times', '', $cookie_time);
      setcookie('objection',  '', $cookie_time);

      DB::$ROOM->Talk($handle_name . ' ' . Message::$entry_user); //入村メッセージ
      RoomDB::UpdateTime();
      DB::Commit();

      $url = sprintf('game_frame.php?room_no=%d', $room_no);
      $str = '%d 番目の村人登録完了、村の寄り合いページに飛びます。<br>' .
	'切り替わらないなら <a href="%s">ここ</a> 。';
      HTML::OutputResult('村人登録', sprintf($str, ++$user_count, $url), $url);
    }
    else {
      $str = 'データベースサーバが混雑しています。<br>時間を置いて再度登録してください。';
      HTML::OutputResult('村人登録 [データベースサーバエラー]', $str);
    }
  }

  //ユーザ登録画面表示
  static function Output() {
    if (RQ::Get()->user_no > 0) { //登録情報変更モード
      $stack = UserDB::Get();
      if ($stack['session_id'] != Session::GetID()) {
	HTML::OutputResult('村人登録 [セッションエラー]', 'セッション ID が一致しません');
      }
      foreach ($stack as $key => $value) {
	if (array_key_exists($key, RQ::Get())) RQ::Set($key, $value);
      }
    }

    DB::$ROOM = RoomDataDB::LoadEntryUserPage();
    $str = sprintf('%d 番地の村は', RQ::Get()->room_no);
    if (is_null(DB::$ROOM->id)) {
      HTML::OutputResult('村人登録 [村番号エラー]', $str . '存在しません');
    }
    if (DB::$ROOM->IsFinished()) {
      HTML::OutputResult('村人登録 [入村不可]', $str . '終了しました');
    }
    if (! DB::$ROOM->IsWaiting()) {
      HTML::OutputResult('村人登録 [入村不可]', $str . 'すでにゲームが開始されています。');
    }
    DB::$ROOM->ParseOption(true);

    UserManagerHTML::Output();
  }
}

//-- HTML 生成クラス (UserManager 拡張) --//
class UserManagerHTML {
  const PATH = 'img/entry_user';
  const UNAME_INPUT = '<td><input type="text" id="uname" name="uname" size="30" maxlength="30" value="%s"></td>';
  const UNAME_EXPLAIN_HEADER = '普段は表示されず、他のユーザ名がわかるのは';
  const UNAME_EXPLAIN_FOOTER = '死亡したときとゲーム終了後のみです';
  const UNAME_TRIP = '<br>＃の右側はトリップ専用入力欄です';

  //出力
  static function Output() {
    HTML::OutputHeader(ServerConfig::TITLE . '[村人登録]', 'entry_user');
    HTML::OutputJavaScript('submit_icon_search');
    HTML::OutputBodyHeader();
    self::OutputHeader();
    self::OutputForm();
    self::OutputWishRole();
    self::OutputSubmit();
    self::OutputIcon();
    Text::Output('</table></div></form>');
    HTML::OutputFooter();
  }

  //出力 (登録情報変更)
  static function OutputChange() {
    $str = <<<EOF
登録データを変更しました。<br>
<form method="post" action="#">
<input type="button" value="ウィンドウを閉じる" onClick="window.close()">
</form>
EOF;
    HTML::OutputResult('村人登録 [登録情報変更]', $str);
  }

  //ヘッダ出力
  private static function OutputHeader() {
    $format = <<<EOF
<a href="./">←戻る</a><br>
<form method="post" action="%s">
<div align="center">
<table class="main">
<tr><td><img src="%s/title.gif" alt="申請書"></td></tr>
<tr><td class="title">%s 村<img src="%s/top.gif" alt="への住民登録を申請します"></td></tr>
<tr><td class="number">～%s～ [%d 番地]</td></tr>

EOF;
    $url = sprintf('user_manager.php?room_no=%d', DB::$ROOM->id);
    if (RQ::Get()->user_no > 0) $url .= sprintf('&user_no=%d', RQ::Get()->user_no);

    printf($format,
	   $url, self::PATH, DB::$ROOM->name, self::PATH, DB::$ROOM->comment, DB::$ROOM->id);
  }

  //フォーム出力
  private static function OutputForm() {
    $format = <<<EOF
<tr><td>
<table class="input">
%s
<tr>
<td class="img"><label for="handle_name"><img src="%s/handle_name.gif" alt="村人の名前"></label></td>
<td><input type="text" id="handle_name" name="handle_name" size="30" maxlength="30" value="%s"></td>
<td class="explain">村で表示される名前です</td>
</tr>
%s
<tr>
<td class="img"><img src="%s/sex.gif" alt="性別"></td>
<td class="img">
<label for="male"><img src="%s/sex_male.gif" alt="男性"><input type="radio" id="male" name="sex" value="male"%s></label>
<label for="female"><img src="%s/sex_female.gif" alt="女性"><input type="radio" id="female" name="sex" value="female"%s></label>
</td>
<td class="explain">特に意味は無いかも……</td>
</tr>
<tr>
<td class="img"><label for="profile"><img src="%s/profile.gif" alt="プロフィール"></label></td>
<td colspan="2"><textarea id="profile" name="profile" cols="30" rows="2">%s</textarea></td>
</tr>
<tr>

EOF;

    $male   = '';
    $female = '';
    switch (RQ::Get()->sex) {
    case 'male':
      $male = ' checked';
      break;

    case 'female':
      $female = ' checked';
      break;
    }

    printf($format,
	   self::GenerateUname(), self::PATH, RQ::Get()->handle_name, self::GeneratePassword(),
	   self::PATH, self::PATH, $male, self::PATH, $female, self::PATH, RQ::Get()->profile);
  }

  //希望役職選択フォーム出力
  private static function OutputWishRole() {
    if (! DB::$ROOM->IsOption('wish_role')) {
      echo '<td><input type="hidden" name="role" value="none">';
      return;
    }

    $format = <<<EOF
<td class="role"><img src="%s/role.gif" alt="役割希望"></td>
<td colspan="2">

EOF;
    printf($format, self::PATH);

    $stack = array('none');
    if (DB::$ROOM->IsChaosWish()) {
      $stack = array_merge($stack, RoleData::GetGroupList());
    }
    elseif (DB::$ROOM->IsOption('gray_random')) {
      array_push($stack, 'human', 'wolf', 'mad', 'fox');
    }
    else {
      array_push($stack, 'human', 'wolf');
      if (DB::$ROOM->IsQuiz()) {
	array_push($stack, 'mad', 'common', 'fox');
      }
      else {
	array_push($stack, 'mage', 'necromancer', 'mad', 'guard', 'common');
	if (DB::$ROOM->IsOption('detective')) $stack[] = 'detective_common';
	$stack[] = 'fox';
      }
      foreach (array('poison', 'assassin', 'boss_wolf') as $role) {
	if (DB::$ROOM->IsOption($role)) $stack[] = $role;
      }
      if (DB::$ROOM->IsOption('poison_wolf')) array_push($stack, 'poison_wolf', 'pharmacist');
      foreach (array('possessed_wolf', 'sirius_wolf', 'child_fox', 'cupid') as $role) {
	if (DB::$ROOM->IsOption($role)) $stack[] = $role;
      }
      if (DB::$ROOM->IsOption('medium')) array_push($stack, 'medium', 'mind_cupid');
      if (DB::$ROOM->IsOptionGroup('mania') && ! in_array('mania', $stack)) $stack[] = 'mania';
    }

    echo "<table>\n<tr>";
    $format = <<<EOF
<td><label for="%s"><input type="radio" id="%s" name="role" value="%s"%s><img src="%s/role_%s.gif" alt="%s"></label></td>

EOF;
    $count = 0;
    foreach ($stack as $role) {
      if ($count > 0 && $count % 4 == 0) Text::Output(Text::TR); //4個ごとに改行
      $count++;
      $alt = '←' . ($role == 'none' ? '無し' : RoleData::GetName($role));
      $checked = RQ::Get()->role == $role ? ' checked' : '';
      printf($format, $role, $role, $role, $checked, self::PATH, $role, $alt);
    }
    echo "</tr>\n</table>";
  }

  //登録ボタン出力
  private static function OutputSubmit() {
    echo <<<EOF
</td></tr>
<tr>
<td class="submit" colspan="3">
<span class="explain">
ユーザ名、村人の名前、パスワードの前後の空白および改行コードは自動で削除されます
</span>
<input type="submit" id="entry" name="entry" value="村人登録申請"></td>
</tr>
</table>
</td></tr>
EOF;
  }

  //アイコン選択フォーム出力
  private static function OutputIcon() {
    $format = <<<EOF
<tr><td>
<fieldset><legend><img src="%s/icon.gif" alt="アイコン"></legend>
<table class="icon">
<caption>あなたのアイコンを選択して下さい</caption>
<tr><td colspan="4">
<input id="fix_number" type="radio" name="icon_no"%s><label for="fix_number">手入力</label>
<input type="text" name="icon_no" size="10px" value="%s">(半角英数で入力してください)
</td></tr>
<tr><td colspan="4">

EOF;
    if (isset(RQ::Get()->icon_no) && RQ::Get()->icon_no > (RQ::Get()->user_no > 0 ? -1 : 0)) {
      $checked = ' checked';
      $icon_no = RQ::Get()->icon_no;
    } else {
      $checked = '';
      $icon_no = '';
    }

    printf($format, self::PATH, $checked, $icon_no);
    IconHTML::Output('user_manager');
    echo <<<EOF
</td></tr></table>
</fieldset>
</td></tr>
EOF;
  }

  //ユーザ名フォーム生成
  private static function GenerateUname() {
    $format = <<<EOF
<tr>
<td class="img"><label for="uname"><img src="%s/uname.gif" alt="ユーザ名"></label></td>

EOF;

    if (RQ::Get()->user_no > 0) { //登録情報変更モード時はユーザ名は変更不可
      $format .= <<<EOF
<td>%s</td>
<td class="explain">%s</td>
</tr>
EOF;
      $str = self::UNAME_EXPLAIN_HEADER . '<br>' . self::UNAME_EXPLAIN_FOOTER;
      return sprintf($format, self::PATH, RQ::Get()->uname, $str);
    }
    elseif (GameConfig::TRIP) { //トリップ対応
      $format .= self::UNAME_INPUT . Text::LF . <<<EOF
<td><label for="trip">＃</label> <input type="text" id="trip" name="trip" size="15" maxlength="15" value="%s"></td>
</tr>
<tr>
<td></td>
<td colspan="2" class="explain">%s</td>
</tr>
EOF;
      $str = self::UNAME_EXPLAIN_HEADER . self::UNAME_EXPLAIN_FOOTER . self::UNAME_TRIP;
      if (DB::$ROOM->IsOption('necessary_name') && DB::$ROOM->IsOption('necessary_trip')) {
	$str .= '<br><span>必ずユーザ名・トリップの両方を入力してください</span>';
      } elseif (DB::$ROOM->IsOption('necessary_name')) {
	$str .= '<br><span>必ずユーザ名を入力してください</span>';
      } elseif (DB::$ROOM->IsOption('necessary_trip')) {
	$str .= '<br><span>必ずトリップを入力してください</span>';
      }
      return sprintf($format, self::PATH, RQ::Get()->uname, RQ::Get()->trip, $str);
    }
    else {
      $format .= self::UNAME_INPUT . Text::LF . <<<EOF
<td class="explain">%s(<span>トリップ使用不可</span>)</td>
</tr>
EOF;
      $str = self::UNAME_EXPLAIN_HEADER . Text::BR . self::UNAME_EXPLAIN_FOOTER;
      return sprintf($format, self::PATH, RQ::Get()->uname, $str);
    }
  }

  //パスワードフォーム生成
  private static function GeneratePassword() {
    if (RQ::Get()->user_no > 0) return '';
    $format = <<<EOF
<tr>
<td class="img"><label for="password"><img src="%s/password.gif" alt="パスワード"></label></td>
<td><input type="password" id="password" name="password" size="30" maxlength="30" value=""></td>
<td class="explain">セッションが切れた場合のログイン時に使います<br> (<span>暗号化されていないので要注意</span>)</td>
</tr>
EOF;
    return sprintf($format, self::PATH);
  }
}
