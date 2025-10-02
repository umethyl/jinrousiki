<?php
//-- ユーザ登録コントローラー --//
class UserManager {
  //実行処理
  static function Execute() {
    DB::Connect();
    Session::Start();
    RQ::Get()->entry ? self::Entry() : self::Output();
    DB::Disconnect();
  }

  //ユーザ登録
  private static function Entry() {
    extract(RQ::ToArray()); //引数を展開
    $url = sprintf('user_manager.php?room_no=%d', $room_no); //ベースバックリンク
    if ($user_no > 0) $url .= sprintf('&user_no=%d', $user_no); //登録情報変更モード
    $back_url = Text::BRLF . HTML::GenerateLink($url, Message::BACK); //バックリンク
    if (GameConfig::TRIP && $trip != '') {
      $trip = Text::Trip('#' . $trip); //トリップ変換
      $uname .= $trip;
    } else {
      $trip = ''; //ブラックリストチェック用にトリップを初期化
    }

    //ブラックリストチェック
    if (! ServerConfig::DEBUG_MODE && Security::IsLoginBlackList($trip)) {
      self::OutputError(UserManagerMessage::BLACK_LIST_TITLE, UserManagerMessage::BLACK_LIST);
    }

    //記入漏れチェック
    $title = UserManagerMessage::ERROR_INPUT;
    $str   = UserManagerMessage::ERROR_INPUT_TEXT  . $back_url;
    $empty = UserManagerMessage::ERROR_INPUT_EMPTY . $back_url;
    if ($user_no < 1) {
      if ($uname     == '') self::OutputError($title, UserManagerMessage::UNAME    . $str);
      if ($password  == '') self::OutputError($title, UserManagerMessage::PASSWORD . $str);
    }
    if ($handle_name == '') self::OutputError($title, UserManagerMessage::HANDLE_NAME . $str);
    if ($profile     == '') self::OutputError($title, UserManagerMessage::PROFILE     . $str);
    if (empty($sex))        self::OutputError($title, UserManagerMessage::SEX         . $empty);
    if (empty($role))       self::OutputError($title, UserManagerMessage::WISH_ROLE   . $empty);
    if (! is_int($icon_no)) self::OutputError($title, UserManagerMessage::ICON_NUMBER . $empty);

    //文字数制限チェック
    $format = UserManagerMessage::ERROR_TEXT_LIMIT . $back_url;
    $limit_list = array(
      array('str'    => $uname,
	    'name'   => UserManagerMessage::UNAME,
	    'config' => GameConfig::LIMIT_UNAME),
      array('str'    => $handle_name,
	    'name'   => UserManagerMessage::HANDLE_NAME,
	    'config' => GameConfig::LIMIT_UNAME),
      array('str'    => $profile,
	    'name'   => UserManagerMessage::PROFILE,
	    'config' => GameConfig::LIMIT_PROFILE)
    );
    foreach ($limit_list as $limit) {
      if (strlen($limit['str']) > $limit['config']) {
	self::OutputError($title, sprintf($format, $limit['name'], $limit['config']));
      }
    }

    //例外チェック
    if ($uname == GM::SYSTEM || $uname == GM::DUMMY_BOY) {
      self::OutputError($title, sprintf(UserManagerMessage::CHECK_UNAME . $back_url, $uname));
    }
    if ($user_no < 1 && self::IsSystemName($handle_name)) {
      $format = UserManagerMessage::CHECK_HANDLE_NAME;
      self::OutputError($title, sprintf($format . $back_url, $handle_name));
    }
    if (! Sex::Exists($sex)) {
      self::OutputError($title, UserManagerMessage::CHECK_SEX . $back_url);
    }
    if ($icon_no < ($user_no > 0 ? 0 : 1) || ! IconDB::IsEnable($icon_no)) {
      /* ロック前なのでスキマが存在するが、実用性を考慮してここで判定する */
      self::OutputError($title, UserManagerMessage::CHECK_ICON . $back_url);
    }

    if (! DB::Transaction()) { //トランザクション開始
      self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD . $back_url);
    }

    DB::SetRoom(RoomDataDB::LoadEntryUser($room_no)); //現在の村情報を取得 (ロック付き)
    if (! DB::$ROOM->IsWaiting()) { //ゲーム開始判定
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::PLAYING);
    }
    DB::$ROOM->ParseOption(true);

    //DB から現在のユーザ情報を取得 (ロック付き)
    RQ::Load('RequestBase', true);
    RQ::Set('room_no', $room_no);
    RQ::Get('retrieve_type', 'entry_user');
    DB::LoadUser();

    //希望役職チェック
    $stack = DB::$ROOM->IsOption('wish_role') ? OptionManager::GetWishRole() : array('none');
    if (! in_array($role, $stack)) {
      self::OutputError($title, UserManagerMessage::CHECK_WISH_ROLE . $back_url);
    }

    $user_count = DB::$USER->GetUserCount(); //現在の KICK されていない住人の数を取得
    if ($user_no < 1 && $user_count >= DB::$ROOM->max_user) { //定員オーバー判定
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::MAX_USER);
    }

    //重複チェック (比較演算子は大文字・小文字を区別しないのでクエリで直に判定する)
    $footer = Text::BRLF . UserManagerMessage::ERROR_INPUT_FOOTER . $back_url;

    if ($user_no > 0) { //登録情報変更モード
      $title  = UserManagerMessage::CHANGE;
      $target = UserDB::Load($user_no);
      if ($target->session_id != Session::GetID()) {
	self::OutputError(Message::SESSION_ERROR, UserManagerMessage::SESSION);
      }
      $target->room_no = RQ::Get()->room_no;

      if (! $target->IsDummyBoy() && self::IsSystemName($handle_name)) {
	$format = UserManagerMessage::CHECK_HANDLE_NAME;
	self::OutputError($title, sprintf($format . $back_url, $handle_name));
      }

      if (UserDB::IsDuplicateName($user_no, $handle_name)) {
	self::OutputError($title, UserManagerMessage::DUPLICATE_NAME . $footer);
      }

      $str   = sprintf(UserManagerMessage::CHANGE_HEADER, $target->handle_name);
      $stack = array();
      if ($target->handle_name != $handle_name) {
	$stack['handle_name'] = $handle_name;
	$format = Text::LF . UserManagerMessage::CHANGE_NAME;
	$str .= sprintf($format, $target->handle_name, $handle_name);
      }
      if ($target->icon_no != $icon_no) {
	if (! $target->IsDummyBoy() && $icon_no == 0) {
	  self::OutputError($title, UserManagerMessage::CHECK_ICON . $back_url);
	}
	$stack['icon_no'] = $icon_no;
	$format    = Text::LF . UserManagerMessage::CHANGE_ICON;
	$icon_name = IconDB::GetName($icon_no);
	$str .= sprintf($format, $target->icon_no, $target->icon_name, $icon_no, $icon_name);
      }
      foreach (array('sex', 'profile', 'role') as $value) {
	if ($target->$value != $$value) $stack[$value] = $$value;
      }
      //Text::p($stack);

      if (count($stack) < 1) {
	self::OutputError($title, UserManagerMessage::CHANGE_NONE . $back_url);
      }
      DB::$ROOM->TalkBeforeGame($str, $target->uname, $target->handle_name, $target->color);

      if ($target->UpdateList($stack) && DB::Commit()) {
	self::OutputError($title, HTML::GenerateCloseWindow(UserManagerMessage::CHANGE_SUCCESS));
      } else {
	self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD . $back_url);
      }
    }

    //ユーザ名・村人名
    if (DB::$ROOM->IsOption('necessary_name') && strpos($uname, Message::TRIP) === 0) {
      self::OutputError($title, UserManagerMessage::ERROR_INPUT_UNAME . $back_url);
    }
    if (DB::$ROOM->IsOption('necessary_trip') && strpos($uname, Message::TRIP) === false) {
      self::OutputError($title, UserManagerMessage::ERROR_INPUT_TRIP . $back_url);
    }

    if (UserDB::IsKick($uname)) { //キックされた人と同じユーザ名
      self::OutputError($title, UserManagerMessage::ERROR_INPUT_KICK . $footer);
    }

    $title = UserManagerMessage::DUPLICATE; //多重登録判定
    if (UserDB::IsDuplicate($uname, $handle_name)) { //ユーザ名・村人名重複
      self::OutputError($title, UserManagerMessage::DUPLICATE_NAME . $footer);
    }

    //IP アドレスチェック
    if (! ServerConfig::DEBUG_MODE && GameConfig::LIMIT_IP && UserDB::IsDuplicateIP()) {
      self::OutputError($title, UserManagerMessage::DUPLICATE_IP);
    }

    //DB にユーザデータを登録
    $list = array(
      'room_no'     => $room_no,
      'user_no'     => count(DB::$USER->name) + 1, //KICK された住人も含めた新しい番号を振る
      'uname'       => $uname,
      'handle_name' => $handle_name,
      'icon_no'     => $icon_no,
      'profile'     => $profile,
      'sex'         => $sex,
      'password'    => $password,
      'role'        => $role
    );

    if (UserDB::Insert($list)) {
      JinrouCookie::Initialize(); //クッキーの初期化
      DB::$ROOM->Talk(sprintf(TalkMessage::ENTRY_USER, $handle_name)); //入村メッセージ
      RoomDB::UpdateTime();
      DB::Commit();

      $url  = sprintf('game_frame.php?room_no=%d', $room_no);
      $str  = sprintf(UserManagerMessage::ENTRY, ++$user_count);
      $jump = sprintf(Message::JUMP, $url);
      HTML::OutputResult(UserManagerMessage::TITLE, $str . Text::BR . $jump, $url);
    }
    else {
      self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD);
    }
  }

  //ユーザ登録画面表示
  private static function Output() {
    if (RQ::Get()->user_no > 0) { //登録情報変更モード
      $stack = UserDB::Get();
      if ($stack['session_id'] != Session::GetID()) {
	self::OutputError(Message::SESSION_ERROR, UserManagerMessage::SESSION);
      }
      foreach ($stack as $key => $value) {
	if (array_key_exists($key, RQ::Get())) RQ::Set($key, $value);
      }
    }

    DB::SetRoom(RoomDataDB::LoadEntryUserPage());
    if (is_null(DB::$ROOM->id)) {
      $str = sprintf(UserManagerMessage::NOT_EXISTS, RQ::Get()->room_no);
      self::OutputError(UserManagerMessage::LOGIN, $str);
    }
    if (DB::$ROOM->IsFinished()) {
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::FINISHED);
    }
    if (! DB::$ROOM->IsWaiting()) {
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::PLAYING);
    }
    DB::$ROOM->ParseOption(true);

    UserManagerHTML::Output();
  }

  //エラー出力
  private static function OutputError($title, $body) {
    HTML::OutputResult(sprintf(UserManagerMessage::ERROR, $title), $body);
  }

  //システムユーザ名判定
  private static function IsSystemName($name) {
    return $name == Message::SYSTEM || $name == Message::DUMMY_BOY;
  }
}

//-- HTML 生成クラス (UserManager 拡張) --//
class UserManagerHTML {
  const PATH = 'img/entry_user';

  //出力
  static function Output() {
    HTML::OutputHeader(ServerConfig::TITLE . UserManagerMessage::TITLE, 'entry_user');
    HTML::OutputJavaScript('submit_icon_search');
    HTML::OutputBodyHeader();
    self::OutputHeader();
    self::OutputForm();
    self::OutputIcon();
    self::OutputFooter();
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    echo HTML::GenerateLink('./', Message::BACK) . Text::BRLF;

    $format = <<<EOF
<form method="post" action="%s">
<div align="center">
<table class="main">
<tr><td><img src="%s/title.gif" alt="%s"></td></tr>
<tr><td class="title">%s<img src="%s/top.gif" alt="%s"></td></tr>
<tr><td class="number">%s [%s]</td></tr>
EOF;
    $url = sprintf('user_manager.php?room_no=%d', DB::$ROOM->id);
    if (RQ::Get()->user_no > 0) $url .= sprintf('&user_no=%d', RQ::Get()->user_no);

    printf($format . Text::LF,
	   $url, self::PATH, UserManagerMessage::ENTRY_TITLE,
	   DB::$ROOM->GenerateName(), self::PATH, UserManagerMessage::ENTRY_ROOM,
	   DB::$ROOM->GenerateComment(), DB::$ROOM->GenerateNumber());
  }

  //フォーム出力
  private static function OutputForm() {
    Text::Output('<tr><td><table class="input">');
    self::OutputUname();
    self::OutputHandleName();
    self::OutputPassword();
    self::OutputSex();
    self::OutputProfile();
    self::OutputWishRole();
    self::OutputSubmit();
    Text::Output('</table></td></tr>');
  }

  //ユーザ名フォーム出力
  private static function OutputUname() {
    $format = <<<EOF
<tr>
<td class="img"><label for="uname"><img src="%s/uname.gif" alt="%s"></label></td>
EOF;
    printf($format . Text::LF, self::PATH, UserManagerMessage::UNAME);

    if (RQ::Get()->user_no > 0) { //登録情報変更モード時はユーザ名は変更不可
      $format = <<<EOF
<td>%s</td>
<td class="explain">%s<br>%s</td>
EOF;
      printf($format . Text::LF, RQ::Get()->uname,
	     UserManagerMessage::UNAME_EXPLAIN_HEADER, UserManagerMessage::UNAME_EXPLAIN_FOOTER);
    }
    elseif (GameConfig::TRIP) { //トリップ対応
      $format = <<<EOF
<td><input type="text" id="uname" name="uname" size="30" maxlength="30" value="%s"></td>
<td><label for="trip">%s</label> <input type="text" id="trip" name="trip" size="15" maxlength="15" value="%s"></td>
</tr>
<tr>
<td></td>
<td colspan="2" class="explain">%s</td>
EOF;
      $str = UserManagerMessage::UNAME_EXPLAIN_HEADER . UserManagerMessage::UNAME_EXPLAIN_FOOTER .
	Text::BR . Message::TRIP_KEY . UserManagerMessage::TRIP;
      if (DB::$ROOM->IsOption('necessary_name') && DB::$ROOM->IsOption('necessary_trip')) {
	$str .= sprintf(Text::BR . '<span>%s</span>', UserManagerMessage::NECESSARY_NAME_TRIP);
      } elseif (DB::$ROOM->IsOption('necessary_name')) {
	$str .= sprintf(Text::BR . '<span>%s</span>', UserManagerMessage::NECESSARY_NAME);
      } elseif (DB::$ROOM->IsOption('necessary_trip')) {
	$str .= sprintf(Text::BR . '<span>%s</span>', UserManagerMessage::NECESSARY_TRIP);
      }
      printf($format . Text::LF, RQ::Get()->uname, Message::TRIP_KEY, RQ::Get()->trip, $str);
    }
    else {
      $format = <<<EOF
<td><input type="text" id="uname" name="uname" size="30" maxlength="30" value="%s"></td>
<td class="explain">%s<br>%s(<span>%s</span>)</td>
EOF;
      printf($format . Text::LF, RQ::Get()->uname, UserManagerMessage::UNAME_EXPLAIN_HEADER,
	     UserManagerMessage::UNAME_EXPLAIN_FOOTER, UserManagerMessage::DISABLE_TRIP);
    }

    Text::Output('</tr>');
  }

  //HN フォーム出力
  private static function OutputHandleName() {
    $format = <<<EOF
<tr>
<td class="img"><label for="handle_name"><img src="%s/handle_name.gif" alt="%s"></label></td>
<td><input type="text" id="handle_name" name="handle_name" size="30" maxlength="30" value="%s"></td>
<td class="explain">%s</td>
</tr>
EOF;
    printf($format . Text::LF, self::PATH, UserManagerMessage::HANDLE_NAME,
	   RQ::Get()->handle_name, UserManagerMessage::HANDLE_NAME_EXPLAIN);
  }

  //パスワードフォーム出力
  private static function OutputPassword() {
    if (RQ::Get()->user_no > 0) return;
    $format = <<<EOF
<tr>
<td class="img"><label for="password"><img src="%s/password.gif" alt="%s"></label></td>
<td><input type="password" id="password" name="password" size="30" maxlength="30" value=""></td>
<td class="explain">%s<br> (<span>%s</span>)</td>
</tr>
EOF;
    printf($format . Text::LF, self::PATH, UserManagerMessage::PASSWORD,
	   UserManagerMessage::PASSWORD_EXPLAIN, UserManagerMessage::PASSWORD_CAUTION);
  }

  //性別フォーム出力
  private static function OutputSex() {
    $format = <<<EOF
<tr>
<td class="img"><img src="%s/sex.gif" alt="%s"></td>
<td class="img">
<label for="male"><img src="%s/sex_male.gif" alt="%s"><input type="radio" id="male" name="sex" value="male"%s></label>
<label for="female"><img src="%s/sex_female.gif" alt="%s"><input type="radio" id="female" name="sex" value="female"%s></label>
</td>
<td class="explain">%s</td>
</tr>
EOF;

    $male   = '';
    $female = '';
    switch (RQ::Get()->sex) {
    case Sex::MALE:
      $male = ' checked';
      break;

    case Sex::FEMALE:
      $female = ' checked';
      break;
    }

    printf($format . Text::LF,
	   self::PATH, UserManagerMessage::SEX,
	   self::PATH, Message::MALE,   $male,
	   self::PATH, Message::FEMALE, $female,
	   UserManagerMessage::SEX_EXPLAIN);
  }

  //プロフィールフォーム出力
  private static function OutputProfile() {
    $format = <<<EOF
<tr>
<td class="img"><label for="profile"><img src="%s/profile.gif" alt="%s"></label></td>
<td colspan="2"><textarea id="profile" name="profile" cols="30" rows="2">%s</textarea></td>
</tr>
EOF;
    printf($format . Text::LF, self::PATH, UserManagerMessage::PROFILE, RQ::Get()->profile);
  }

  //希望役職選択フォーム出力
  private static function OutputWishRole() {
    if (! DB::$ROOM->IsOption('wish_role')) {
      Text::Output('<tr><td><input type="hidden" name="role" value="none"></td></tr>');
      return;
    }

    $format = <<<EOF
<tr>
<td class="role"><img src="%s/role.gif" alt="%s"></td>
<td colspan="2">
<table>
<tr>
EOF;
    printf($format . Text::LF, self::PATH, UserManagerMessage::WISH_ROLE);

    $format = <<<EOF
<td><label for="%s"><input type="radio" id="%s" name="role" value="%s"%s><img src="%s/role_%s.gif" alt="%s"></label></td>
EOF;

    $stack = OptionManager::GetWishRole();
    $count = 0;
    $check_role = in_array(RQ::Get()->role, $stack) ? RQ::Get()->role : 'none';
    foreach ($stack as $role) {
      if ($count > 0 && $count % 4 == 0) Text::Output(Text::TR); //4個ごとに改行
      $count++;
      if ($role == 'none') {
	$alt = UserManagerMessage::WISH_ROLE_ALT . UserManagerMessage::WISH_ROLE_NONE;
      } else {
	$alt = UserManagerMessage::WISH_ROLE_ALT . RoleData::GetName($role);
      }
      $checked = $check_role == $role ? ' checked' : '';
      printf($format . Text::LF, $role, $role, $role, $checked, self::PATH, $role, $alt);
    }
    Text::Output('</tr>' . Text::LF . '</table>' . Text::LF . '</td>' . Text::LF . '</tr>');
  }

  //登録ボタン出力
  private static function OutputSubmit() {
    $format = <<<EOF
<tr>
<td class="submit" colspan="3">
<span class="explain">%s</span>
<input type="submit" id="entry" name="entry" value="%s"></td>
</tr>
EOF;
    printf($format . Text::LF, UserManagerMessage::SUBMIT_EXPLAIN, UserManagerMessage::SUBMIT);
  }

  //アイコン選択フォーム出力
  private static function OutputIcon() {
    $format = <<<EOF
<tr><td>
<fieldset><legend><img src="%s/icon.gif" alt="%s"></legend>
<table class="icon">
<caption>%s</caption>
<tr><td colspan="4">
<input id="fix_number" type="radio" name="icon_no"%s><label for="fix_number">%s</label>
<input type="text" name="icon_no" size="10px" value="%s">(%s)
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

    printf($format . Text::LF, self::PATH, UserManagerMessage::ICON,
	   UserManagerMessage::ICON_EXPLAIN, $checked, UserManagerMessage::ICON_FIX,
	   $icon_no, UserManagerMessage::ICON_FIX_EXPLAIN);
    IconHTML::Output('user_manager');
    echo <<<EOF
</td></tr></table>
</fieldset>
</td></tr>

EOF;
  }

  //フッタ出力
  private static function OutputFooter() {
    Text::Output('</table></div></form>');
  }
}
