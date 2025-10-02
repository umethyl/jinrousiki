<?php
//-- DB アクセス (アイコン拡張) --//
class IconDB {
  //情報取得
  static function Get($icon_no) {
    self::Prepare($icon_no, '*');
    return DB::FetchAssoc(true);
  }

  //アイコン名取得
  static function GetName($icon_no) {
    self::Prepare($icon_no, 'icon_name');
    return DB::FetchResult();
  }

  //ファイル名取得
  static function GetFile($icon_no) {
    self::Prepare($icon_no, 'icon_filename');
    return DB::FetchResult();
  }

  //セッション情報取得
  static function GetSession($icon_no) {
    self::Prepare($icon_no, 'icon_filename, session_id');
    return DB::FetchAssoc(true);
  }

  //次のアイコン番号取得
  static function GetNext() {
    DB::Prepare(self::SetColumn('MAX(icon_no)'));
    return (int)DB::FetchResult() + 1;
  }

  //リスト取得
  static function GetList(array $where) {
    $format  = self::SetColumn('*') . ' WHERE %s ORDER BY %s';
    $where[] = 'icon_no > 0';
    $sort    = RQ::Get()->sort_by_name ? 'icon_name, icon_no' : 'icon_no, icon_name';
    $query   = sprintf($format, implode(' AND ', $where), $sort);
    if (RQ::Get()->page != 'all') {
      $limit = max(0, IconConfig::VIEW * (RQ::Get()->page - 1));
      $query .= sprintf(' LIMIT %d, %d', $limit, IconConfig::VIEW);
    }
    DB::Prepare($query);
    return DB::FetchAssoc();
  }

  //カテゴリ取得
  static function GetCategory() {
    $stack = array('SELECT', 'FROM user_icon WHERE', 'IS NOT NULL GROUP BY', 'ORDER BY icon_no');
    DB::Prepare(implode(' category ', $stack));
    return DB::FetchColumn();
  }

  //検索項目から情報取得
  static function GetSelectionByType($type) {
    //選択状態の抽出
    $data   = RQ::Get()->search ? RQ::Get()->$type : Session::Get('icon_view', $type);
    $target = empty($data) ? array() : (is_array($data) ? $data : array($data));
    Session::Set('icon_view', $type, $target);
    if ($type == 'keyword') return $target;

    $format = 'SELECT DISTINCT %s FROM user_icon WHERE %s IS NOT NULL';
    DB::Prepare(sprintf($format, $type, $type));
    return DB::FetchColumn();
  }

  //抽出条件生成
  static function GetInClause($type, array $list) {
    if (in_array('__null__', $list)) return $type . ' IS NULL';
    $stack = array();
    foreach ($list as $value) {
      $stack[] = sprintf("'%s'", Text::Escape($value));
    }
    return $type . sprintf(' IN (%s)', implode(',', $stack));
  }

  //アイコン数取得
  static function Count(array $where) {
    $where[] = 'icon_no > 0';
    DB::Prepare(sprintf(self::SetColumn('icon_no') . ' WHERE %s', implode(' AND ', $where)));
    return DB::Count();
  }

  //存在判定
  static function Exists($icon_no) {
    self::Prepare($icon_no, 'icon_no');
    return DB::Exists();
  }

  //アイコン名存在判定
  static function ExistsName($icon_name) {
    DB::Prepare(self::SetColumn('icon_no') . ' WHERE icon_name = ?', array($icon_name));
    return DB::Exists();
  }

  //アイコン名重複判定
  static function IsDuplicate($icon_no, $icon_name) {
    $query = self::SetColumn('icon_no') . ' WHERE icon_no <> ? AND icon_name = ?';
    DB::Prepare($query, array($icon_no, $icon_name));
    return DB::Exists();
  }

  //有効判定
  static function IsEnable($icon_no) {
    DB::Prepare(self::SetQuery('icon_no') . ' AND disable IS NOT TRUE', array($icon_no));
    return DB::Exists();
  }

  //無効判定
  static function IsDisable($icon_no) {
    DB::Prepare(self::SetQuery('icon_no') . ' AND disable IS TRUE', array($icon_no));
    return DB::Exists();
  }

  //村で使用中のアイコンチェック
  static function IsUsing($icon_no) {
    $query = <<<EOF
SELECT icon_no FROM user_icon
INNER JOIN user_entry USING (icon_no) INNER JOIN room USING (room_no)
WHERE icon_no = ? AND status IN (?, ?)
EOF;
    DB::Prepare($query, array($icon_no, RoomStatus::WAITING, RoomStatus::PLAYING));
    return DB::Exists();
  }

  //登録数上限チェック
  static function IsOver() {
    DB::Prepare(self::SetColumn('icon_no'));
    return DB::Count() >= UserIconConfig::NUMBER;
  }

  //アイコン情報更新
  static function Update($icon_no, $data) {
    DB::Prepare(sprintf('UPDATE user_icon SET %s WHERE icon_no = ?', $data), array($icon_no));
    return DB::FetchBool();
  }

  //アイコン削除
  static function Delete($icon_no, $file) {
    DB::Prepare('DELETE FROM user_icon WHERE icon_no = ?', array($icon_no));
    if (! DB::FetchBool()) return false; //レコード削除
    unlink(Icon::GetFile($file)); //ファイル削除
    DB::Optimize('user_icon'); //テーブル最適化 + コミット
    return true;
  }

  //セッション削除
  static function ClearSession($icon_no) {
    return self::Update($icon_no, 'session_id = NULL');
  }

  //基本 SELECT セット
  private static function SetColumn($column) {
    return sprintf('SELECT %s FROM user_icon', $column);
  }

  //基本 SQL セット
  private static function SetQuery($column) {
    return self::SetColumn($column) . ' WHERE icon_no = ?';
  }

  //Prepare 処理 (IconDB 用)
  private static function Prepare($icon_no, $column) {
    DB::Prepare(self::SetQuery($column), array($icon_no));
  }
}

//-- HTML 生成クラス (アイコン拡張) --//
class IconHTML {
  //アイコン情報出力
  static function Output($url = 'icon_view') {
    /*
      初回表示前に検索条件をリセットする
      TODO: リファラーをチェックすることで GET リクエストによる取得にも対処できる
      現時点では GET で直接検索を試みたユーザーのセッション情報まで配慮していないが、
      いずれ必要になるかも知れない (enogu)
    */
    if (is_null(RQ::Get()->page)) Session::Clear('icon_view');

    //編集フォームの表示
    if ($url == 'icon_view') {
      if (RQ::Get()->icon_no > 0) {
	$format = <<<EOF
<div class="link"><a href="icon_view.php">%s</a></div>
<fieldset><legend>%s</legend>
EOF;
	printf($format . Text::LF, IconMessage::BACK, IconMessage::EDIT);
	self::OutputEdit(RQ::Get()->icon_no);
	Text::Output('</fieldset>');
      }
      else {
	Text::Output(sprintf('<fieldset><legend>%s</legend>', IconMessage::TITLE));
	self::OutputConcrete($url);
	Text::Output('</fieldset>');
      }
    }
    else {
      self::OutputConcrete($url);
    }
  }

  //アイコン編集フォーム出力
  private static function OutputEdit($icon_no) {
    $stack = IconDB::Get($icon_no);
    if (count($stack) < 1) return;

    extract($stack);
    $url     = Icon::GetFile($icon_filename);
    $size    = UserIcon::GetMaxLength();
    $checked = $disable > 0 ? ' checked' : '';
    $format = <<<EOF
<form method="post" action="icon_edit.php">
<input type="hidden" name="icon_no" value="%d">
<table cellpadding="3">
<tr>
  <td rowspan="7"><img src="%s" style="border:3px solid %s;"></td>
  <td><label for="name">%s</label></td>
  <td><input type="text" id="name" name="icon_name" value="%s" %s></td>
</tr>
<tr>
  <td><label for="appearance">%s</label></td>
  <td><input type="text" id="appearance" name="appearance" value="%s" %s></td>
</tr>
<tr>
  <td><label for="category">%s</label></td>
  <td><input type="text" id="category" name="category" value="%s" %s></td>
</tr>
<tr>
  <td><label for="author">%s</label></td>
  <td><input type="text" id="author" name="author" value="%s" %s></td>
</tr>
<tr>
  <td><label for="color">%s</label></td>
  <td><input type="text" id="color" name="color" value="%s" size="10px" maxlength="7"> (%s)</td>
</tr>
<tr>
  <td><label for="disable">%s</label></td>
  <td><input type="checkbox" id="disable" name="disable" value="on"%s></td>
</tr>
<tr>
  <td><label for="password">%s</label></td>
  <td><input type="password" id="password" name="password" size="20" value=""></td>
</tr>
<tr>
  <td colspan="3"><input type="submit" value="%s"></td>
</tr>
</table>
</form>
EOF;

    printf($format . Text::LF,
	   $icon_no, $url, $color,
	   IconMessage::NAME,       $icon_name,  $size,
	   IconMessage::APPEARANCE, $appearance, $size,
	   IconMessage::CATEGORY,   $category,   $size,
	   IconMessage::AUTHOR,     $author,     $size,
	   IconMessage::COLOR,      $color,      IconMessage::EXAMPLE,
	   IconMessage::DISABLE,    $checked,
	   IconMessage::PASSWORD,
	   IconMessage::SUBMIT);
  }

  //アイコン情報を収集して表示する
  private static function OutputConcrete($base_url = 'icon_view') {
    //-- ヘッダ出力 --//
    $url_option    = array();
    $query_stack   = array();
    $category_list = IconDB::GetCategory();
    echo <<<EOF
<form method="get" id="icon_search">
<table class="selector">
<tr>

EOF;
    //検索条件の表示
    $where = array();
    if ($base_url == 'user_manager') $where[] = 'disable IS NOT TRUE';
    $stack = self::OutputByType('category', IconMessage::CATEGORY);
    if (0 < count($stack)) {
      foreach ($stack as $data) $url_option[] = sprintf("category[]={$data}");
      $where[] = IconDB::GetInClause('category', $stack);
    }

    $stack = self::OutputByType('appearance', IconMessage::APPEARANCE);
    if (0 < count($stack)) {
      foreach ($stack as $data) $url_option[] = "appearance[]={$data}";
      $where[] = IconDB::GetInClause('appearance', $stack);
    }

    $stack = self::OutputByType('author', IconMessage::AUTHOR);
    if (0 < count($stack)) {
      foreach ($stack as $data) $url_option[] = "author[]={$data}";
      $where[] = IconDB::GetInClause('author', $stack);
    }

    $stack = self::OutputByType('keyword', IconMessage::KEYWORD);
    if (0 < count($stack)) {
      $str = "LIKE '%{$stack[0]}%'";
      $where[] = "(category {$str} OR appearance {$str} OR author {$str} OR icon_name {$str})";
    }
    else {
      $stack = array('');
    }
    $keyword = $stack[0];

    $colspan = UserIconConfig::COLUMN * 2;
    $checked = RQ::Get()->sort_by_name ? ' checked' : '';
    $format  = <<<EOF
</tr>
<tr>
<td colspan="%s">
<label for="sort_by_name"><input id="sort_by_name" name="sort_by_name" type="checkbox" value="on"%s>%s</label>
<label for="keyword">%s<input id="keyword" name="keyword" type="text" value="%s"></label>
<input id="page" name="page" type="hidden" value="1">
<input id="search" name="search" type="submit" value="%s">
</td></tr></table>
EOF;

    printf($format . Text::LF,
	   $colspan,
	   $checked, IconMessage::SORT_BY_NAME, IconMessage::KEYWORD_INPUT, $keyword,
	   IconMessage::SEARCH);

    //検索結果の表示
    if (empty(RQ::Get()->room_no)) {
      $method = 'OutputDetailForIconView';
      $format = <<<EOF
<table>
<caption>
[S] %s / [C] %s / [A] %s<br>
%s
</caption>
<thead>
<tr>
EOF;
      printf($format . Text::LF,
	     IconMessage::APPEARANCE, IconMessage::CATEGORY, IconMessage::AUTHOR,
	     IconMessage::SEARCH_EXPLAIN);
    }
    elseif (isset(RQ::Get()->room_no)) {
      $method = 'OutputDetailForUserEntry';
      Text::Output('<thead><tr>');
    }
    else {
      $method = null;
    }

    //ユーザアイコンのテーブルから一覧を取得
    $CONF = new StdClass();
    $CONF->view       = IconConfig::VIEW;
    $CONF->page       = IconConfig::PAGE;
    $CONF->url        = $base_url;
    $CONF->count      = IconDB::Count($where);
    $CONF->current    = RQ::Get()->page;
    $CONF->option     = $url_option;
    $CONF->attributes = array('onclick' => 'return "return submit_icon_search(\'$page\');";');
    if (RQ::Get()->room_no > 0) $CONF->option[] = 'room_no=' . RQ::Get()->room_no;
    if (RQ::Get()->icon_no > 0) $CONF->option[] = 'icon_no=' . RQ::Get()->icon_no;
    printf('<td colspan="%d" class="page-link">', $colspan);
    self::OutputPageLink($CONF);
    echo <<<EOF
</td>
</tr>
</thead>
<tbody>
<tr>

EOF;

    //アイコン情報の表示
    if (isset($method)) {
      $column = 0;
      foreach (IconDB::GetList($where) as $icon_info) {
	self::$method($icon_info, 162);
	if (UserIconConfig::COLUMN <= ++$column) {
	  $column = 0;
	  echo '</tr><tr>';
	}
      }
    }
    echo <<<EOF
</tr>
</tbody>
</table>
</form>

EOF;
  }

  //検索項目とタイトル、検索条件のセットから選択肢を抽出し、表示する
  private static function OutputByType($type, $caption) {
    $format = <<<EOF
<td>
<label for="%s[]">%s</label><br>
<select name="%s[]" size="6" multiple>
<option value="__all__">%s</option>
EOF;
    printf($format . Text::LF, $type, $caption, $type, IconMessage::ALL);

    $list = IconDB::GetSelectionByType($type);
    array_unshift($list, '__null__');

    $format = '<option value="%s"%s>%s</option>';
    $target = Session::Get('icon_view', $type);
    foreach ($list as $name) {
      if ($name == '__null__') {
	$space = IconMessage::NOTHING;
      } elseif (strlen($name) > 0) {
	$space = $name;
      } else {
	$space = IconMessage::SPACE;
      }

      printf($format, $name, in_array($name, $target) ? ' selected' : '', $space);
    }
    Text::Output("</select>\n</td>");

    return in_array('__all__', $target) ? array() : $target;
  }

  //アイコン詳細画面 (IconView 用)
  private static function OutputDetailForIconView(array $icon_list, $cell_width) {
    extract($icon_list);
    $edit_url = sprintf('icon_view.php?icon_no=%d', $icon_no);
    if ($disable > 0) $icon_name = sprintf('<s>%s</s>', $icon_name);
    $format = <<<EOF
<td class="icon-details">
<a href="%s" class="icon_wrapper" style="width:%dpx">
<img alt="%s" src="%s" width="%d" height="%d" style="border:3px solid %s;">
</a>
</td>
<td class="icon-details">
<ul style="width:%dpx;">
<li><a href="%s">No. %d</a></li>
<li><a href="%s">%s</a></li>
<li><font color="%s">%s</font>%s</li>
EOF;

    printf($format . Text::LF,
	   $edit_url, $icon_width + 6,
	   $icon_name, Icon::GetFile($icon_filename), $icon_width, $icon_height, $color,
	   $cell_width - $icon_width,
	   $edit_url, $icon_no,
	   $edit_url, $icon_name,
	   $color, Message::SYMBOL, $color);

    $data = '';
    if (! empty($appearance)) $data .= '<li>[S]' . $appearance;
    if (! empty($category))   $data .= '<li>[C]' . $category;
    if (! empty($author))     $data .= '<li>[A]' . $author;
    echo $data;
    echo <<<EOF
</ul>
</td>

EOF;
  }

  //アイコン詳細画面 (UserEntry 用)
  private static function OutputDetailForUserEntry(array $icon_list, $cell_width) {
    extract($icon_list);
    $wrapper_width = $icon_width + 6;
    $info_width    = $cell_width - $wrapper_width;
    $format = <<<EOF
<td class="icon_details"><label for="icon_%d"><img alt="%s" src="%s" width="%d" height="%d" style="border:3px solid %s;"><br clear="all">
<input type="radio" id="icon_%d" name="icon_no" value="%d"> No. %d<br>
<font color="%s">%s</font>%s</label></td>
EOF;

    printf($format . Text::LF,
	   $icon_no, $icon_name, Icon::GetFile($icon_filename), $icon_width, $icon_height, $color,
	   $icon_no, $icon_no, $icon_no,
	   $color, Message::SYMBOL, $icon_name);
  }

  //ページ送り用のリンクタグを出力する (PageLinkBuilder と統合できるかも)
  private static function OutputPageLink(StdClass $CONFIG) {
    $page_count = ceil($CONFIG->count / $CONFIG->view);
    $start_page = $CONFIG->current== 'all' ? 1 : $CONFIG->current;
    if ($page_count - $CONFIG->current < $CONFIG->page) {
      $start_page = $page_count - $CONFIG->page + 1;
      if ($start_page < 1) $start_page = 1;
    }
    $end_page = $CONFIG->current + $CONFIG->page - 1;
    if ($end_page > $page_count) $end_page = $page_count;

    $url_stack = array('[' . (isset($CONFIG->title) ? $CONFIG->title : 'Page') . ']');
    $url_header = '<a href="' . $CONFIG->url . '.php?';

    if ($page_count > $CONFIG->page && $CONFIG->current> 1) {
      $url_stack[] = self::GeneratePageLink($CONFIG, 1, '[1]...');
      $url_stack[] = self::GeneratePageLink($CONFIG, $start_page - 1, '&lt;&lt;');
    }

    for ($page_number = $start_page; $page_number <= $end_page; $page_number++) {
      $url_stack[] = self::GeneratePageLink($CONFIG, $page_number);
    }

    if ($page_number <= $page_count) {
      $url_stack[] = self::GeneratePageLink($CONFIG, $page_number, '&gt;&gt;');
      $url_stack[] = self::GeneratePageLink($CONFIG, $page_count, '...[' . $page_count . ']');
    }
    $url_stack[] = self::GeneratePageLink($CONFIG, 'all');

    echo implode(' ', $url_stack);
  }

  //ページ送り用のリンクタグを作成する
  private static function GeneratePageLink(StdClass $CONFIG, $page, $title = null) {
    if ($page == $CONFIG->current) return sprintf('[%s]', $page);
    $option = (isset($CONFIG->page_type) ? $CONFIG->page_type : 'page') . '=' . $page;
    $list   = $CONFIG->option;
    array_unshift($list, $option);
    $url = $CONFIG->url . '.php?' . implode('&', $list);
    $attributes = array();
    if (isset($CONFIG->attributes)) {
      foreach ($CONFIG->attributes as $attr => $value) {
	$attributes[] = $attr . '="'. eval($value) . '"';
      }
    }
    $attrs = implode(' ', $attributes);
    if (is_null($title)) $title = sprintf('[%s]', $page);
    return sprintf('<a href="%s" %s>%s</a>', $url, $attrs, $title);
  }
}
