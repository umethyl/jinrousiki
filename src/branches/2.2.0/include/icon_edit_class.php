<?php
//-- アイコン変更処理クラス --//
class IconEdit {
  const TITLE = 'ユーザアイコン編集';
  const URL   = 'icon_view.php';

  //実行処理
  static function Execute() {
    //リファラチェック
    if (Security::CheckReferer(self::URL)) self::Output('無効なアクセスです');

    //入力データチェック
    extract(RQ::ToArray()); //引数を展開
    $url = sprintf('<a href="%s?icon_no=%d">戻る</a>', self::URL, $icon_no);
    if ($password != UserIconConfig::PASSWORD) self::Output('パスワードが違います。', $url);

    //アイコン名の文字列長のチェック
    if (strlen($icon_name) < 1) self::Output('アイコン名が空欄になっています。', $url);
    $query_stack = array();
    foreach (UserIcon::CheckText(self::TITLE, $url) as $key => $value) {
      $query_stack[] = sprintf('%s = %s', $key, is_null($value) ? 'NULL' : "'{$value}'");
    }

    if (strlen($color) > 0) { //色指定のチェック
      $color = UserIcon::CheckColor($color, self::TITLE, $url);
      $query_stack[] = sprintf("color = '%s'", $color);
    }

    //トランザクション開始
    DB::Connect();
    if (! DB::Lock('icon')) {
      $str = '[ロック失敗] サーバが混雑しています。時間を置いてから再登録をお願いします。';
      self::Output($str, $url);
    }
    if (! IconDB::Exists($icon_no)) { //存在チェック
      self::Output('無効なアイコン番号です：' . $icon_no, $url);
    }

    if (IconDB::IsDuplicate($icon_no, $icon_name)) { //アイコン名重複チェック
      $str = sprintf('アイコン名 "%s" は既に登録されています。', $icon_name);
      self::Output($str, $url);
    }

    if (IconDB::IsUsing($icon_no)) { //編集制限チェック
      $str = '募集中・プレイ中の村で使用されているアイコンは編集できません。';
      self::Output($str, $url);
    }

    if (IconDB::IsDisable($icon_no) !== $disable) { //非表示フラグチェック
      $query_stack[] = sprintf('disable = %s', $disable ? 'TRUE' : 'FALSE');
    }

    if (count($query_stack) < 1) self::Output('変更内容はありません', $url);
    $query = implode(', ', $query_stack);
    //self::Output($query, $url); //テスト用

    if (IconDB::Update($icon_no, $query)) {
      HTML::OutputResult(self::TITLE, '編集完了', sprintf('%s?icon_no=%d', self::URL, $icon_no));
    } else {
      $str = '[更新失敗] サーバが混雑しています。時間を置いてから再登録をお願いします。';
      self::Output($str, $url);
    }
  }

  //エラー処理
  private static function Output($str, $url = null) {
    if (isset($url)) $str = Text::Concat($str, $url);
    HTML::OutputResult(self::TITLE, $str);
  }
}
