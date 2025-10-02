<?php
//-- アイコンアップロード処理クラス --//
class IconUpload {
  const URL = 'icon_upload.php';

  //実行
  public static function Execute() {
    self::Load();
    isset(RQ::Get()->command) ? self::Upload() : self::Output();
  }

  //データロード
  private static function Load() {
    if (UserIconConfig::DISABLE) {
      HTML::OutputResult(IconUploadMessage::TITLE, IconUploadMessage::DISABLE);
    }
    Loader::LoadRequest('icon_upload');
  }

  //登録
  private static function Upload() {
    if (Security::CheckReferer(self::URL)) { //リファラチェック
      HTML::OutputResult(IconUploadMessage::TITLE, IconUploadMessage::REFERER);
    }

    switch (RQ::Get()->command) {
    case 'upload':
      break;

    case 'success': //セッション ID 情報を DB から削除
      self::UploadSuccess();
      break;

    case 'cancel': //アイコン削除
      self::UploadCancel();
      break;

    default:
      self::OutputResult(IconUploadMessage::COMMAND);
      break;
    }

    //アップロードされたファイルのエラーチェック
    if (@$_FILES['upfile']['error'][$i] != 0) {
      self::OutputResult(Text::Concat(IconUploadMessage::FILE_UPLOAD, IconUploadMessage::RETRY));
    }
    extract(RQ::ToArray()); //引数を展開
    $back_url = self::GetURL();

    if ($icon_name == '') { //空白チェック
      self::OutputResult(IconUploadMessage::NAME);
    }
    UserIcon::CheckText(IconUploadMessage::TITLE, $back_url); //アイコン名の文字列長のチェック
    $color = UserIcon::CheckColor($color, IconUploadMessage::TITLE, $back_url); //色指定のチェック

    //ファイルサイズのチェック
    if ($size == 0) {
      self::OutputResult(IconUploadMessage::FILE_EMPTY);
    }
    if ($size > UserIconConfig::FILE) {
      self::OutputResult(IconUploadMessage::FILE_SIZE. UserIcon::GetFileLimit());
    }

    //ファイルの種類のチェック
    switch ($type) {
    case 'image/jpeg':
    case 'image/pjpeg':
      $ext = 'jpg';
      break;

    case 'image/gif':
      $ext = 'gif';
      break;

    case 'image/png':
    case 'image/x-png':
      $ext = 'png';
      break;

    default:
      self::OutputResult($type . IconUploadMessage::FILE_FORMAT);
      break;
    }

    //アイコンの高さと幅をチェック
    list($width, $height) = getimagesize($tmp_name);
    if ($width > UserIconConfig::WIDTH || $height > UserIconConfig::HEIGHT) {
      $str = Text::Concat(
	sprintf(IconUploadMessage::SIZE_LIMIT, UserIcon::GetSizeLimit()),
	sprintf(IconUploadMessage::UPLOAD_SIZE, $width, $height)
      );
      self::OutputResult($str);
    }

    DB::Connect();
    if (! DB::Lock('icon')) { //トランザクション開始
      self::OutputResult(Message::DB_ERROR_LOAD);
    }

    if (IconDB::Over()) { //登録数上限チェック
      HTML::OutputResult(IconUploadMessage::TITLE, IconUploadMessage::OVER);
    }

    if (IconDB::ExistsName($icon_name)) { //アイコン名チェック
      self::OutputResult(sprintf(IconUploadMessage::DUPLICATE, $icon_name));
    }

    $icon_no = IconDB::GetNext(); //次のアイコン番号取得
    if ($icon_no === false) self::OutputResult(Message::DB_ERROR_LOAD); //負荷エラー対策

    //ファイルをテンポラリからコピー
    $file_name = sprintf('%03s.%s', $icon_no, $ext); //ファイル名の桁を揃える
    if (! move_uploaded_file($tmp_name, Icon::GetFile($file_name))) {
      self::OutputResult(Text::Concat(IconUploadMessage::FILE_COPY, IconUploadMessage::RETRY));
    }

    //データベースに登録
    $data = '';
    $session_id = Session::Reset(); //セッション ID を取得
    $items  = 'icon_no, icon_name, icon_filename, icon_width, icon_height, color, ' .
      'session_id, regist_date';
    $values = "{$icon_no}, '{$icon_name}', '{$file_name}', {$width}, {$height}, '{$color}', " .
      "'{$session_id}', NOW()";

    if ($appearance != '') {
      $data   .= Text::BR . '[S]' . $appearance;
      $items  .= ', appearance';
      $values .= ", '{$appearance}'";
    }
    if ($category != '') {
      $data   .= Text::BR . '[C]' . $category;
      $items  .= ', category';
      $values .= ", '{$category}'";
    }
    if ($author != '') {
      $data   .= Text::BR . '[A]' . $author;
      $items  .= ', author';
      $values .= ", '{$author}'";
    }

    if (DB::Insert('user_icon', $items, $values)) {
      DB::Commit();
      DB::Disconnect();
    } else {
      self::OutputResult(Message::DB_ERROR_LOAD);
    }

    $list  = array();
    $stack = array(
      RequestDataIcon::ID, RequestDataIcon::NAME, RequestDataIcon::COLOR,
      'file_name', 'width', 'height', 'data'
    );
    foreach ($stack as $key) {
      $list[$key] = $$key;
    }
    self::OutputConfirm($list);
  }

  //登録完了
  private static function UploadSuccess() {
    $url = 'icon_view.php';
    $str = IconUploadMessage::SUCCESS . IconUploadMessage::JUMP_VIEW;

    DB::Connect();
    if (! IconDB::ClearSession(RQ::Get()->icon_no)) {
      $str = Text::Concat($str, IconUploadMessage::SESSION_DELETE);
    }
    $str = Text::Concat($str, URL::GetJump($url));
    HTML::OutputResult(IconUploadMessage::SUCCESS, $str, $url);
  }

  //登録キャンセル
  private static function UploadCancel() {
     DB::Connect();
    if (! DB::Lock('icon')) { //トランザクション開始
      self::OutputResult(IconUploadMessage::DB_ERROR);
    }

    //アイコンのファイル名と登録時のセッション ID を取得
    $stack = IconDB::GetSession(RQ::Get()->icon_no);
    if (count($stack) < 1) { //アイコン情報取得エラー
      self::OutputResult(IconUploadMessage::DB_ERROR);
    }
    extract($stack);

    if ($session_id != Session::GetID()) { //セッション ID 確認
      self::OutputResult(IconUploadMessage::SESSION);
    }

    if (! IconDB::Delete(RQ::Get()->icon_no, $icon_filename)) { //削除処理
      self::OutputResult(IconUploadMessage::DB_ERROR);
    }
    DB::Disconnect();

    $url = 'icon_upload.php';
    $str = Text::Concat(
      IconUploadMessage::DELETE . IconUploadMessage::JUMP_UPLOAD, URL::GetJump($url)
    );
    HTML::OutputResult(IconUploadMessage::DELETE, $str, $url);
  }

  //出力
  private static function Output() {
    HTML::OutputHeader(IconUploadMessage::TITLE, 'icon_upload', true);
    IconUploadHTML::Output();
    HTML::OutputFooter();
  }

  //確認画面出力
  private static function OutputConfirm(array $list) {
    $title = IconUploadMessage::TITLE . IconUploadMessage::CHECK;
    HTML::OutputHeader($title, 'icon_upload_check', true);
    IconUploadHTML::OutputConfirm($list);
    HTML::OutputFooter();
  }

  //エラー処理
  private static function OutputResult($str) {
    HTML::OutputResult(IconUploadMessage::TITLE, $str . self::GetURL(true));
  }

  //バックリンク取得
  private static function GetURL($return = false) {
    $url = HTML::GenerateLink(self::URL, Message::BACK);
    return $return ? Text::BRLF . $url : $url;
  }
}
