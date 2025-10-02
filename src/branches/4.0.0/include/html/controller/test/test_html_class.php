<?php
//-- HTML 生成クラス (テスト拡張) --//
class DevHTML {
  //共通リクエストロード
  public static function LoadRequest() {
    Loader::LoadRequest();
    RQ::Get()->ParsePostOn('execute');
  }

  //実行判定
  public static function IsExecute() {
    return RQ::Get()->execute;
  }

  //配役テストヘッダ出力
  public static function OutputRoleTestHeader($title, $url) {
    self::LoadRequest();
    HTML::OutputHeader($title, 'test/role', true);
    HTML::OutputFormHeader($url);

    $id_u = 'user_count';
    $id_t = 'try_count';
    foreach ([$id_u => 20, $id_t => 100] as $key => $value) {
      RQ::Get()->ParsePostInt($key);
      $$key = RQ::Get()->$key > 0 ? RQ::Get()->$key : $value;
    }

    Text::Printf(self::GetRoleTestHeader(),
      $id_u, TestMessage::ROLE_USER,  $id_u, $id_u, $$id_u,
      $id_t, TestMessage::ROLE_COUNT, $id_t, $id_t, $$id_t
    );
  }

  //ラジオ型フォーム出力
  public static function OutputRadio($id, $name, $value, $checked, $label) {
    Text::Printf(self::GetInputForm(),
      OptionFormType::RADIO, $id, $name, $value, $checked, $id, $label
    );
  }

  //チェックボックス型フォーム出力
  public static function OutputCheckbox($id, $name, $label) {
    Text::Printf(self::GetInputForm(),
      OptionFormType::CHECKBOX, $id, $name, Switcher::ON, HTML::GenerateChecked(false), $id, $label
    );
  }

  //配役テストヘッダタグ
  private static function GetRoleTestHeader() {
    return <<<EOF
<label for="%s">%s</label><input type="text" id="%s" name="%s" size="2" value="%s">
<label for="%s">%s</label><input type="text" id="%s" name="%s" size="2" value="%s">
<br>
EOF;
  }

  //フォームタグ
  private static function GetInputForm() {
    return '<input type="%s" id="%s" name="%s" value="%s"%s><label for="%s">%s</label>';
  }
}
