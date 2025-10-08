<?php
//-- アイコン変更コントローラー --//
final class IconEditController extends JinrouController {
  const URL = 'icon_view.php';

  protected static function Start() {
    if (Security::IsInvalidReferer(self::URL)) {
      self::OutputError(IconEditMessage::REFERER);
    }
  }

  protected static function GetLoadRequest() {
    return 'icon_edit';
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
    //入力データチェック
    extract(RQ::ToArray()); //引数を展開
    $url  = self::URL . URL::GetHeaderInt('icon_no', $icon_no);
    $link = HTML::GenerateLink($url, Message::BACK);

    if ($password != UserIconConfig::PASSWORD) { //パスワード照合
      self::OutputError(IconEditMessage::PASSWORD, $link);
    }

    if (false === Text::Exists($icon_name)) { //空文字チェック
      self::OutputError(IconEditMessage::NAME, $link);
    }

    //アイコン名の文字列長チェック
    $query = IconDB::GetQueryUpdate();
    $stack = [];
    foreach (UserIcon::ValidateText(IconEditMessage::TITLE, $link) as $key => $value) {
      if (null === $value) {
	$query->SetNull($key);
      } else {
	$stack[$key] = $value;
      }
    }

    if (true === Text::Exists($color)) { //色指定チェック
      $stack['color'] = UserIcon::ValidateColor($color, IconEditMessage::TITLE, $link);
    }

    //トランザクション開始
    DB::Connect();
    if (false === DB::Lock('icon')) {
      self::OutputError(IconEditMessage::LOCK . Message::DB_ERROR_LOAD, $link);
    }

    if (false === IconDB::Exists($icon_no)) { //存在チェック
      self::OutputError(sprintf(IconEditMessage::NOT_EXISTS, $icon_no), $link);
    }

    if (true === IconDB::Duplicate($icon_no, $icon_name)) { //アイコン名重複チェック
      self::OutputError(sprintf(IconEditMessage::DUPLICATE, $icon_name), $link);
    }

    if (true === IconDB::Using($icon_no)) { //編集制限チェック
      self::OutputError(IconMessage::USING, $link);
    }

    //非表示フラグチェック
    //論理フラグとDBの組み合わせをチェックして変更がある時だけセットする
    if (true === $disable) { //表示 -> 非表示
      if (true === IconDB::Enable($icon_no)) {
	$query->SetData('disable', Query::ENABLE);
      }
    } elseif (false === $disable) { //非表示 -> 表示
      if (true === IconDB::Disable($icon_no)) {
	$query->SetData('disable', Query::DISABLE);
      }
    }
    $query->Set(array_keys($stack));

    if (count($stack) < 1) { //変更が無いなら終了
      //現状はここに入ることは事実上無い。精度を上げる場合はDBから引いて比較すること
      self::OutputError(IconEditMessage::NO_CHANGE, $link);
    }
    $list = array_merge(array_values($stack), [$icon_no]);
    //self::OutputError($query->p() . print_r($list, true), $link); //テスト用

    if (IconDB::Update($query, $list) && DB::Commit()) {
      HTML::OutputResult(IconEditMessage::TITLE, IconEditMessage::SUCCESS, $url);
    } else {
      self::OutputError(IconEditMessage::UPDATE . Message::DB_ERROR_LOAD, $link);
    }
  }

  //エラー出力
  private static function OutputError($str, $link = null) {
    if (null !== $link) {
      $str = Text::Join($str, $link);
    }
    HTML::OutputResult(IconEditMessage::TITLE, $str);
  }
}
