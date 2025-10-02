<?php
//-- HTML 生成クラス (UserManager 拡張) --//
class UserManagerHTML {
  const PATH = 'img/entry_user';

  //出力
  public static function Output() {
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
    $url = URL::GetRoom('user_manager');
    if (RQ::Get()->user_no > 0) {
      $url .= RQ::Get()->ToURL(RequestDataUser::ID, true);
    }

    Text::Printf(self::GetHeader(),
      HTML::GenerateLink('./', Message::BACK), Text::BR,
      $url, self::PATH, UserManagerMessage::ENTRY_TITLE,
      DB::$ROOM->GenerateName(), self::PATH, UserManagerMessage::ENTRY_ROOM,
      DB::$ROOM->GenerateComment(), DB::$ROOM->GenerateNumber()
    );
  }

  //フォーム出力
  private static function OutputForm() {
    echo TableHTML::GenerateTrHeader() . TableHTML::GenerateTdHeader();
    TableHTML::OutputHeader('input', false);
    self::OutputUname();
    self::OutputHandleName();
    self::OutputPassword();
    self::OutputSex();
    self::OutputProfile();
    self::OutputWishRole();
    self::OutputSubmit();
    echo TableHTML::GenerateFooter() . TableHTML::GenerateTdFooter();
    TableHTML::OutputTrFooter();
  }

  //ユーザ名フォーム出力
  private static function OutputUname() {
    if (RQ::Get()->user_no > 0) { //登録情報変更モード時はユーザ名は変更不可
      $str = Text::Format(self::GetUnameEdit(),
	RQ::Get()->uname,
	UserManagerMessage::UNAME_EXPLAIN_HEADER, Text::BR,
	UserManagerMessage::UNAME_EXPLAIN_FOOTER
      );
    } elseif (GameConfig::TRIP) { //トリップ対応
      if (DB::$ROOM->IsOption('necessary_name') && DB::$ROOM->IsOption('necessary_trip')) {
	$warning = Text::BR . HTML::GenerateSpan(UserManagerMessage::NECESSARY_NAME_TRIP);
      } elseif (DB::$ROOM->IsOption('necessary_name')) {
	$warning = Text::BR . HTML::GenerateSpan(UserManagerMessage::NECESSARY_NAME);
      } elseif (DB::$ROOM->IsOption('necessary_trip')) {
	$warning = Text::BR . HTML::GenerateSpan(UserManagerMessage::NECESSARY_TRIP);
      } else {
	$warning = '';
      }
      $str = Text::Format(self::GetUnameTrip(),
	RQ::Get()->uname, Message::TRIP_KEY, RQ::Get()->trip,
	UserManagerMessage::UNAME_EXPLAIN_HEADER,
	UserManagerMessage::UNAME_EXPLAIN_FOOTER, Text::BR,
	Message::TRIP_KEY, UserManagerMessage::TRIP, $warning
      );
    } else {
      $str = Text::Format(self::GetUnameNoTrip(),
	RQ::Get()->uname,
	UserManagerMessage::UNAME_EXPLAIN_HEADER, Text::BR,
	UserManagerMessage::UNAME_EXPLAIN_FOOTER,
	UserManagerMessage::DISABLE_TRIP
      );
    }

    Text::Printf(self::GetUname(), self::PATH, UserManagerMessage::UNAME, $str);
  }

  //HN フォーム出力
  private static function OutputHandleName() {
    Text::Printf(self::GetHandleName(),
      self::PATH, UserManagerMessage::HANDLE_NAME,
      RQ::Get()->handle_name, UserManagerMessage::HANDLE_NAME_EXPLAIN
    );
  }

  //パスワードフォーム出力
  private static function OutputPassword() {
    if (RQ::Get()->user_no > 0) return;
    Text::Printf(
      self::GetPassword(),
      self::PATH, UserManagerMessage::PASSWORD,
      UserManagerMessage::PASSWORD_EXPLAIN, UserManagerMessage::PASSWORD_CAUTION
    );
  }

  //性別フォーム出力
  private static function OutputSex() {
    $male   = '';
    $female = '';
    switch (RQ::Get()->sex) {
    case Sex::MALE:
      $male = HTML::GenerateChecked(true);
      break;

    case Sex::FEMALE:
      $female = HTML::GenerateChecked(true);
      break;
    }

    Text::Printf(self::GetSex(),
      self::PATH, UserManagerMessage::SEX,
      self::PATH, Message::MALE,   $male,
      self::PATH, Message::FEMALE, $female,
      UserManagerMessage::SEX_EXPLAIN
    );
  }

  //プロフィールフォーム出力
  private static function OutputProfile() {
    Text::Printf(self::GetProfile(),
      self::PATH, UserManagerMessage::PROFILE, RQ::Get()->profile,
      UserManagerMessage::PROFILE_EXPLAIN
    );
  }

  //希望役職選択フォーム出力
  private static function OutputWishRole() {
    if (! DB::$ROOM->IsOption('wish_role')) {
      Text::Output(self::GetWishRoleNone());
      return;
    }
    Text::Printf(self::GetWishRoleHeader(), self::PATH, UserManagerMessage::WISH_ROLE);

    $stack = OptionLoader::Load('wish_role')->GetWishRole();
    $count = 0;
    $check_role = in_array(RQ::Get()->role, $stack) ? RQ::Get()->role : 'none';
    foreach ($stack as $role) {
      TableHTML::OutputFold($count++, 4);
      if ($role == 'none') {
	$alt = UserManagerMessage::WISH_ROLE_ALT . UserManagerMessage::WISH_ROLE_NONE;
      } else {
	$alt = UserManagerMessage::WISH_ROLE_ALT . RoleDataManager::GetName($role);
      }
      $checked = HTML::GenerateChecked($check_role == $role);
      Text::Printf(self::GetWishRoleButton(),
	$role, $role, $role, $checked, self::PATH, $role, $alt
      );
    }
    Text::Output(self::GetWishRoleFooter());
  }

  //登録ボタン出力
  private static function OutputSubmit() {
    Text::Printf(self::GetSubmit(),
      UserManagerMessage::SUBMIT_EXPLAIN, UserManagerMessage::SUBMIT,
      DB::$ROOM->IsClosing() ? GameMessage::CLOSING : ''
    );
  }

  //アイコン選択フォーム出力
  private static function OutputIcon() {
    if (isset(RQ::Get()->icon_no) && RQ::Get()->icon_no > (RQ::Get()->user_no > 0 ? -1 : 0)) {
      $checked = HTML::GenerateChecked(true);
      $icon_no = RQ::Get()->icon_no;
    } else {
      $checked = HTML::GenerateChecked(false);
      $icon_no = '';
    }

    Text::Printf(self::GetIconHeader(),
      self::PATH, UserManagerMessage::ICON, UserManagerMessage::ICON_EXPLAIN, $checked,
      UserManagerMessage::ICON_FIX, $icon_no, UserManagerMessage::ICON_FIX_EXPLAIN
    );
    IconHTML::Output('user_manager');
    Text::Output(self::GetIconFooter());
  }

  //フッタ出力
  private static function OutputFooter() {
    Text::Output(self::GetFooter());
  }

  //エラーバックリンク
  public static function GenerateError($url) {
    $stack = RQ::Get()->GetIgnoreError();
    $str   = HTML::GenerateFormHeader($url, Message::BACK);
    foreach (RQ::Get() as $key => $value) {
      if (in_array($key, $stack)) continue;
      $str .= self::GenerateHidden($key, $value);
    }
    return $str . HTML::GenerateFormFooter();
  }

  //hidden タグ生成
  private static function GenerateHidden($name, $value) {
    return Text::Format('<input type="hidden" name="%s" value="%s">', $name, $value);
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
%s%s
<form method="post" action="%s">
<div align="center">
<table class="main">
<tr><td><img src="%s/title.gif" alt="%s"></td></tr>
<tr><td class="title">%s<img src="%s/top.gif" alt="%s"></td></tr>
<tr><td class="number">%s [%s]</td></tr>
EOF;
  }

  //ユーザ名フォームタグ
  private static function GetUname() {
    return <<<EOF
<tr>
<td class="img"><label for="uname"><img src="%s/uname.gif" alt="%s"></label></td>
%s</tr>
EOF;
  }

  //ユーザ名フォームタグ (登録情報変更)
  private static function GetUnameEdit() {
    return <<<EOF
<td>%s</td>
<td class="explain">%s%s%s</td>
EOF;
  }

  //ユーザ名フォームタグ (トリップあり)
  private static function GetUnameTrip() {
    return <<<EOF
<td><input type="text" id="uname" name="uname" size="30" maxlength="30" value="%s"></td>
<td><label for="trip">%s</label> <input type="text" id="trip" name="trip" size="15" maxlength="15" value="%s"></td>
</tr>
<tr>
<td></td>
<td colspan="2" class="explain">%s%s%s%s%s%s</td>
EOF;
  }

  //ユーザ名フォームタグ (トリップなし)
  private static function GetUnameNoTrip() {
    return <<<EOF
<td><input type="text" id="uname" name="uname" size="30" maxlength="30" value="%s"></td>
<td class="explain">%s%s%s(<span>%s</span>)</td>
EOF;
  }

  //HN フォームタグ
  private static function GetHandleName() {
    return <<<EOF
<tr>
<td class="img"><label for="handle_name"><img src="%s/handle_name.gif" alt="%s"></label></td>
<td><input type="text" id="handle_name" name="handle_name" size="30" maxlength="30" value="%s"></td>
<td class="explain">%s</td>
</tr>
EOF;
  }

  //パスワードフォームタグ
  private static function GetPassword() {
    return <<<EOF
<tr>
<td class="img"><label for="password"><img src="%s/password.gif" alt="%s"></label></td>
<td><input type="password" id="password" name="password" size="30" maxlength="30" value=""></td>
<td class="explain">%s<br> (<span>%s</span>)</td>
</tr>
EOF;
  }

  //性別フォームタグ
  private static function GetSex() {
    return <<<EOF
<tr>
<td class="img"><img src="%s/sex.gif" alt="%s"></td>
<td class="img">
<label for="male"><img src="%s/sex_male.gif" alt="%s"><input type="radio" id="male" name="sex" value="male"%s></label>
<label for="female"><img src="%s/sex_female.gif" alt="%s"><input type="radio" id="female" name="sex" value="female"%s></label>
</td>
<td class="explain">%s</td>
</tr>
EOF;
  }

  //プロフィールフォームタグ
  private static function GetProfile() {
    return <<<EOF
<tr>
<td class="img"><label for="profile"><img src="%s/profile.gif" alt="%s"></label></td>
<td><textarea id="profile" name="profile" cols="20" rows="2">%s</textarea></td>
<td class="explain">%s</td>
</tr>
EOF;
  }

  //希望役職選択フォームタグ (ヘッダ)
  private static function GetWishRoleHeader() {
    return <<<EOF
<tr>
<td class="role"><img src="%s/role.gif" alt="%s"></td>
<td colspan="2"><table>
<tr>
EOF;
  }

  //希望役職選択フォームタグ (ボタン)
  private static function GetWishRoleButton() {
    return <<<EOF
<td><label for="%s"><input type="radio" id="%s" name="role" value="%s"%s><img src="%s/role_%s.gif" alt="%s"></label></td>
EOF;
  }

  //希望役職選択フォームタグ (フッタ)
  private static function GetWishRoleFooter() {
    return <<<EOF
</tr>
</table></td>
</tr>
EOF;
  }

  //希望役職選択フォームタグ (なし)
  private static function GetWishRoleNone() {
    return '<tr><td><input type="hidden" name="role" value="none"></td></tr>';
  }

  //登録ボタンタグ
  private static function GetSubmit() {
    return <<<EOF
<tr>
<td class="submit" colspan="3">
<span class="explain">%s</span>
<input type="submit" id="entry" name="entry" value="%s">
<span class="closing">%s</span>
</td>
</tr>
EOF;
  }

  //アイコン選択フォームタグ (ヘッダ)
  private static function GetIconHeader() {
    return <<<EOF
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
  }

  //アイコン選択フォームタグ (フッタ)
  private static function GetIconFooter() {
    return <<<EOF
</td></tr></table>
</fieldset>
</td></tr>
EOF;
  }

  //フッタタグ
  private static function GetFooter() {
    return '</table></div></form>';
  }
}
