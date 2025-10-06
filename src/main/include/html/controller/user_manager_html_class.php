<?php
//-- HTML 生成クラス (UserManager 拡張) --//
final class UserManagerHTML {
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

  //エラーバックリンク
  public static function GenerateError($url) {
    $stack = RQ::Get()->GetIgnoreError();
    $str   = HTML::GenerateFormHeader($url, Message::BACK);
    foreach (RQ::Get() as $key => $value) {
      if (in_array($key, $stack)) {
        continue;
      }
      $str .= self::GenerateHidden($key, $value);
    }
    return $str . HTML::GenerateFormFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    $url = URL::GetRoom('user_manager');
    if (self::IsEditMode()) {
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
    TableHTML::OutputTrHeader();
    TableHTML::OutputTdHeader();
    TableHTML::OutputHeader('input', false);
    self::OutputFormUname();
    self::OutputFormHandleName();
    self::OutputFormPassword();
    self::OutputFormSex();
    self::OutputFormProfile();
    self::OutputFormWishRole();
    self::OutputFormSubmit();
    TableHTML::OutputFooter(false);
    TableHTML::OutputTdFooter();
    TableHTML::OutputTrFooter();
  }

  //ユーザ名フォーム出力
  private static function OutputFormUname() {
    TableHTML::OutputTrHeader();
    Text::Printf(self::GetFormUnameHeader(), self::PATH, UserManagerMessage::UNAME);
    if (self::IsEditMode()) { //登録情報変更モード時はユーザ名は変更不可
      $str  = UserManagerMessage::UNAME_EXPLAIN_HEADER . Text::BR;
      $str .= UserManagerMessage::UNAME_EXPLAIN_FOOTER;

      TableHTML::OutputTd(RQ::Get()->uname);
      TableHTML::OutputTd($str, 'explain');
    } elseif (GameConfig::TRIP) { //トリップ対応
      Text::Printf(self::GetFormUnameWithTrip(),
	RQ::Get()->uname, Message::TRIP_KEY, RQ::Get()->trip,
	UserManagerMessage::UNAME_EXPLAIN_HEADER,
	UserManagerMessage::UNAME_EXPLAIN_FOOTER, Text::BR,
	Message::TRIP_KEY, UserManagerMessage::TRIP,
	OptionManager::GetUserEntryUnameWarning()
      );
    } else {
      Text::Printf(self::GetFormUnameWithoutTrip(),
	RQ::Get()->uname,
	UserManagerMessage::UNAME_EXPLAIN_HEADER, Text::BR,
	UserManagerMessage::UNAME_EXPLAIN_FOOTER,
	UserManagerMessage::DISABLE_TRIP
      );
    }
    TableHTML::OutputTrFooter();
  }

  //HN フォーム出力
  private static function OutputFormHandleName() {
    Text::Printf(self::GetFormHandleName(),
      self::PATH, UserManagerMessage::HANDLE_NAME,
      RQ::Get()->handle_name, UserManagerMessage::HANDLE_NAME_EXPLAIN
    );
  }

  //パスワードフォーム出力
  private static function OutputFormPassword() {
    if (self::IsEditMode()) {
      return;
    }

    Text::Printf(self::GetFormPassword(),
      self::PATH, UserManagerMessage::PASSWORD,
      UserManagerMessage::PASSWORD_EXPLAIN, UserManagerMessage::PASSWORD_CAUTION
    );
  }

  //性別フォーム出力
  private static function OutputFormSex() {
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

    Text::Printf(self::GetFormSex(),
      self::PATH, UserManagerMessage::SEX,
      self::PATH, Message::MALE,   $male,
      self::PATH, Message::FEMALE, $female,
      UserManagerMessage::SEX_EXPLAIN
    );
  }

  //プロフィールフォーム出力
  private static function OutputFormProfile() {
    Text::Printf(self::GetFormProfile(),
      self::PATH, UserManagerMessage::PROFILE, RQ::Get()->profile,
      UserManagerMessage::PROFILE_EXPLAIN
    );
  }

  //希望役職選択フォーム出力
  private static function OutputFormWishRole() {
    if (DB::$ROOM->IsOption('wish_role')) {
      self::OutputFormWishRoleEnableHeader();
      self::OutputFormWishRoleEnable();
      self::OutputFormWishRoleEnableFooter();
    } else {
      self::OutputFormWishRoleNone();
    }
  }

  //希望役職選択有効フォームヘッダー出力
  private static function OutputFormWishRoleEnableHeader() {
    Text::Printf(self::GetFormWishRoleHeader(), self::PATH, UserManagerMessage::WISH_ROLE);
  }

  //希望役職選択有効フォーム出力
  private static function OutputFormWishRoleEnable() {
    $stack      = OptionManager::GetWishRoleList();
    $check_role = in_array(RQ::Get()->role, $stack) ? RQ::Get()->role : 'none';
    $count      = 0;
    foreach ($stack as $role) {
      TableHTML::OutputFold($count++, 4);
      if ($role == 'none') {
	$alt = UserManagerMessage::WISH_ROLE_ALT . UserManagerMessage::WISH_ROLE_NONE;
      } else {
	$alt = UserManagerMessage::WISH_ROLE_ALT . RoleDataManager::GetName($role);
      }
      $checked = HTML::GenerateChecked($check_role == $role);
      Text::Printf(self::GetFormWishRoleButton(),
	$role, $role, $role, $checked, self::PATH, $role, $alt
      );
    }
  }

  //希望役職選択有効フォームフッター出力
  private static function OutputFormWishRoleEnableFooter() {
    Text::Output(self::GetFormWishRoleFooter());
  }

  //希望役職選択無効フォーム出力
  private static function OutputFormWishRoleNone() {
    Text::Output(self::GetFormWishRoleNone());
  }

  //登録ボタン出力
  private static function OutputFormSubmit() {
    Text::Printf(self::GetFormSubmit(),
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

  //ユーザ名フォームタグヘッダ
  private static function GetFormUnameHeader() {
    return <<<EOF
<td class="img"><label for="uname"><img src="%s/uname.gif" alt="%s"></label></td>
EOF;
  }

  //ユーザ名フォームタグ (トリップあり)
  private static function GetFormUnameWithTrip() {
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
  private static function GetFormUnameWithoutTrip() {
    return <<<EOF
<td><input type="text" id="uname" name="uname" size="30" maxlength="30" value="%s"></td>
<td class="explain">%s%s%s(<span>%s</span>)</td>
EOF;
  }

  //HN フォームタグ
  private static function GetFormHandleName() {
    return <<<EOF
<tr>
<td class="img"><label for="handle_name"><img src="%s/handle_name.gif" alt="%s"></label></td>
<td><input type="text" id="handle_name" name="handle_name" size="30" maxlength="30" value="%s"></td>
<td class="explain">%s</td>
</tr>
EOF;
  }

  //パスワードフォームタグ
  private static function GetFormPassword() {
    return <<<EOF
<tr>
<td class="img"><label for="password"><img src="%s/password.gif" alt="%s"></label></td>
<td><input type="password" id="password" name="password" size="30" maxlength="30" value=""></td>
<td class="explain">%s<br> (<span>%s</span>)</td>
</tr>
EOF;
  }

  //性別フォームタグ
  private static function GetFormSex() {
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
  private static function GetFormProfile() {
    return <<<EOF
<tr>
<td class="img"><label for="profile"><img src="%s/profile.gif" alt="%s"></label></td>
<td><textarea id="profile" name="profile" cols="20" rows="2">%s</textarea></td>
<td class="explain">%s</td>
</tr>
EOF;
  }

  //希望役職選択フォームタグ (ヘッダ)
  private static function GetFormWishRoleHeader() {
    return <<<EOF
<tr>
<td class="role"><img src="%s/role.gif" alt="%s"></td>
<td colspan="2"><table>
<tr>
EOF;
  }

  //希望役職選択フォームタグ (ボタン)
  private static function GetFormWishRoleButton() {
    return <<<EOF
<td><label for="%s"><input type="radio" id="%s" name="role" value="%s"%s><img src="%s/role_%s.gif" alt="%s"></label></td>
EOF;
  }

  //希望役職選択フォームタグ (フッタ)
  private static function GetFormWishRoleFooter() {
    return <<<EOF
</tr>
</table></td>
</tr>
EOF;
  }

  //希望役職選択フォームタグ (なし)
  private static function GetFormWishRoleNone() {
    return '<tr><td><input type="hidden" name="role" value="none"></td></tr>';
  }

  //登録ボタンタグ
  private static function GetFormSubmit() {
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

  //登録情報変更モード判定
  private static function IsEditMode() {
    return RQ::Get()->user_no > 0;
  }
}
