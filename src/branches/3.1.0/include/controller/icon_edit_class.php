<?php
//-- アイコン変更処理クラス --//
class IconEdit {
  const URL = 'icon_view.php';

  //実行
  public static function Execute() {
    self::Load();
    self::Edit();
  }

  //データロード
  private static function Load() {
    if (Security::CheckReferer(self::URL)) { //リファラチェック
      self::Output(IconEditMessage::REFERER);
    }
    Loader::LoadRequest('icon_edit');
  }

  //変更処理
  private static function Edit() {
    //入力データチェック
    extract(RQ::ToArray()); //引数を展開
    $url = sprintf('<a href="%s?icon_no=%d">%s</a>', self::URL, $icon_no, Message::BACK);

    if ($password != UserIconConfig::PASSWORD) { //パスワード照合
      self::Output(IconEditMessage::PASSWORD, $url);
    }

    if (! Text::Exists($icon_name)) { //空文字チェック
      self::Output(IconEditMessage::NAME, $url);
    }

    //アイコン名の文字列長のチェック
    $query_stack = array();
    foreach (UserIcon::CheckText(IconEditMessage::TITLE, $url) as $key => $value) {
      $query_stack[] = sprintf('%s = %s', $key, is_null($value) ? 'NULL' : "'{$value}'");
    }

    if (Text::Exists($color)) { //色指定のチェック
      $color = UserIcon::CheckColor($color, IconEditMessage::TITLE, $url);
      $query_stack[] = sprintf("color = '%s'", $color);
    }

    //トランザクション開始
    DB::Connect();
    if (! DB::Lock('icon')) {
      self::Output(IconEditMessage::LOCK . Message::DB_ERROR_LOAD, $url);
    }

    if (! IconDB::Exists($icon_no)) { //存在チェック
      self::Output(sprintf(IconEditMessage::NOT_EXISTS, $icon_no), $url);
    }

    if (IconDB::Duplicate($icon_no, $icon_name)) { //アイコン名重複チェック
      self::Output(sprintf(IconEditMessage::DUPLICATE, $icon_name), $url);
    }

    if (IconDB::Using($icon_no)) { //編集制限チェック
      self::Output(IconEditMessage::USING, $url);
    }

    if (IconDB::Disable($icon_no) !== $disable) { //非表示フラグチェック
      $query_stack[] = sprintf('disable = %s', $disable ? 'TRUE' : 'FALSE');
    }

    if (count($query_stack) < 1) { //変更が無いなら終了
      self::Output(IconEditMessage::NO_CHANGE, $url);
    }
    $query = ArrayFilter::ToCSV($query_stack);
    //self::Output($query, $url); //テスト用

    if (IconDB::Update($icon_no, $query) && DB::Commit()) {
      $str = sprintf('%s?icon_no=%d', self::URL, $icon_no);
      HTML::OutputResult(IconEditMessage::TITLE, IconEditMessage::SUCCESS, $str);
    } else {
      self::Output(IconEditMessage::UPDATE . Message::DB_ERROR_LOAD, $url);
    }
  }

  //エラー処理
  private static function Output($str, $url = null) {
    if (isset($url)) $str = Text::Concat($str, $url);
    HTML::OutputResult(IconEditMessage::TITLE, $str);
  }
}
