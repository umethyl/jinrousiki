<?php
//-- アイコン変更コントローラー --//
final class IconEditController extends JinrouController {
  const URL = 'icon_view.php';

  protected static function Unusable() {
    return Security::IsInvalidReferer(self::URL);
  }

  protected static function OutputUnusableError() {
    self::OutputError(IconEditMessage::REFERER);
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

    if (RQ::Fetch()->Enable(RequestDataIcon::MULTI)) {
      $url = self::URL . URL::HEAD . URL::AddSwitch(RequestDataIcon::MULTI);
    } else {
      $url = URL::GetIcon(self::URL, $icon_no);
    }
    $link = LinkHTML::Generate($url, Message::BACK);

    if ($password != UserIconConfig::PASSWORD) { //パスワード照合
      self::OutputError(IconEditMessage::PASSWORD, $link);
    }

    //一括編集時にはアイコン名を変更しない
    if (RQ::Fetch()->Enable(RequestDataIcon::MULTI)) {
      //アイコン番号リストチェック
      $icon_no_list = [];
      foreach (Text::Parse($number_list, ',') as $value) {
	if (true === is_numeric($value)) {
	  $number = intval($value); //小数は変換されるがそこまではケアしない
	  if ($number < 1) {
	    self::OutputError(sprintf(IconEditMessage::NUMBER, $number), $link);
	  }
	  $icon_no_list[] = $number;
	} else {
	  self::OutputError(sprintf(IconEditMessage::NUMBER_FORMAT, $number_list), $link);
	}
      }
    } else {
      if (false === Text::Exists($icon_name)) { //空文字チェック
	self::OutputError(IconEditMessage::NAME, $link);
      }
    }

    //クエリ初期化
    if (RQ::Fetch()->Enable(RequestDataIcon::MULTI)) {
      $query = IconDB::GetQueryMultiUpdate($icon_no_list);
    } else {
      $query = IconDB::GetQueryUpdate();
    }
    $stack = [];

    //入力データの文字列長チェック
    foreach (UserIcon::ValidateText(IconEditMessage::TITLE, $link) as $key => $value) {
      if (RQ::Fetch()->Enable(RequestDataIcon::MULTI) && RequestDataIcon::NAME == $key) {
	continue;
      }

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

    if (RQ::Fetch()->Enable(RequestDataIcon::MULTI)) {
      //差分を正確に追跡するのは手間なので個別に判定する
      foreach ($icon_no_list as $number) {
	if (false === IconDB::Exists($number)) { //存在チェック
	  self::OutputError(sprintf(IconMessage::NOT_EXISTS, $number), $link);
	}

	if (true === IconDB::Using($number)) { //編集制限チェック
	  self::OutputError(IconMessage::USING, $link);
	}
      }
    } else {
      if (false === IconDB::Exists($icon_no)) { //存在チェック
	self::OutputError(sprintf(IconMessage::NOT_EXISTS, $icon_no), $link);
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
    }

    if (count($stack) < 1) { //変更が無いなら終了
      //一括編集以外では、通常はここには来ない。精度を上げる場合はDBから引いて比較すること
      self::OutputError(IconEditMessage::NO_CHANGE, $link);
    }

    $query->Set(array_keys($stack));
    if (RQ::Fetch()->Enable(RequestDataIcon::MULTI)) {
      $list = array_merge(array_values($stack), $icon_no_list);
    } else {
      $list = array_merge(array_values($stack), [$icon_no]);
    }
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
