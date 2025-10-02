<?php
//-- アイコン変更処理クラス --//
class IconEdit {
  const TITLE  = 'ユーザアイコン編集';
  const URL    = 'icon_view.php';
  const BACK   = "<br>\n<a href=\"%s?icon_no=%d\">戻る</a>";

  static function Execute() {
    //リファラチェック
    if (Security::CheckReferer(self::URL)) HTML::OutputResult(self::TITLE, '無効なアクセスです');

    //入力データチェック
    extract(RQ::ToArray()); //引数を展開
    $back_url = sprintf(self::BACK, self::URL, $icon_no);
    if ($password != UserIconConfig::PASSWORD) {
      HTML::OutputResult(self::TITLE, 'パスワードが違います。' . $back_url);
    }

    //アイコン名の文字列長のチェック
    if (strlen($icon_name) < 1) {
      HTML::OutputResult(self::TITLE, 'アイコン名が空欄になっています。' . $back_url);
    }
    $query_stack = array();
    foreach (UserIcon::CheckText(self::TITLE, $back_url) as $key => $value) {
      $query_stack[] = sprintf('%s = %s', $key, is_null($value) ? 'NULL' : "'{$value}'");
    }

    if (strlen($color) > 0) { //色指定のチェック
      $color = UserIcon::CheckColor($color, self::TITLE, $back_url);
      $query_stack[] = sprintf("color = '%s'", $color);
    }

    //トランザクション開始
    DB::Connect();
    $lock = "サーバが混雑しています。<br>\n時間を置いてから再登録をお願いします。" . $back_url;
    if (! DB::Lock('icon')) HTML::OutputResult(self::TITLE, $lock);

    if (! IconDB::Exists($icon_no)) { //存在チェック
      HTML::OutputResult(self::TITLE, '無効なアイコン番号です：' . $icon_no . $back_url);
    }

    if (IconDB::IsDuplicate($icon_no, $icon_name)) { //アイコン名重複チェック
      $str = sprintf('アイコン名 "%s" は既に登録されています。', $icon_name);
      HTML::OutputResult(self::TITLE, $str . $back_url);
    }

    if (IconDB::IsUsing($icon_no)) { //編集制限チェック
      $str = '募集中・プレイ中の村で使用されているアイコンは編集できません。';
      HTML::OutputResult(self::TITLE, $str . $back_url);
    }

    if (IconDB::IsDisable($icon_no) !== $disable) { //非表示フラグチェック
      $query_stack[] = sprintf('disable = %s', $disable ? 'TRUE' : 'FALSE');
    }

    if (count($query_stack) < 1) {
      HTML::OutputResult(self::TITLE, '変更内容はありません' . $back_url);
    }
    $query = implode(', ', $query_stack);
    //HTML::OutputResult(self::TITLE, $query . $back_url); //テスト用

    if (IconDB::Update($icon_no, $query)) {
      HTML::OutputResult(self::TITLE, '編集完了', sprintf('icon_view.php?icon_no=%d', $icon_no));
    } else {
      HTML::OutputResult(self::TITLE, $lock);
    }
  }
}
