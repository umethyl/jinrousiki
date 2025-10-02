<?php
//-- DB アクセス (アイコン拡張) --//
class IconDB {
  //アイコン存在チェック
  static function Exists($icon_no) {
    $format = 'SELECT icon_no FROM user_icon WHERE icon_no = %d';
    return DB::Count(sprintf($format, $icon_no)) > 0;
  }

  //アイコン名存在チェック
  static function ExistsName($icon_name) {
    $format = "SELECT icon_no FROM user_icon WHERE icon_name = '%s'";
    return DB::Count(sprintf($format, $icon_name)) > 0;
  }

  //アイコン名重複チェック
  static function IsDuplicate($icon_no, $icon_name) {
    $format = "SELECT icon_no FROM user_icon WHERE icon_no <> %d AND icon_name = '%s'";
    return DB::Count(sprintf($format, $icon_no, $icon_name)) > 0;
  }

  //非表示フラグチェック
  static function IsDisable($icon_no) {
    $format = 'SELECT icon_no FROM user_icon WHERE icon_no = %d AND disable = TRUE';
    return DB::Count(sprintf($format, $icon_no)) > 0;
  }

  //村で使用中のアイコンチェック
  static function IsUsing($icon_no) {
    $format = 'SELECT icon_no FROM user_icon ' .
      'INNER JOIN user_entry USING (icon_no) INNER JOIN room USING (room_no) ' .
      "WHERE icon_no = %d AND status IN ('waiting', 'playing')";
    return DB::Count(sprintf($format, $icon_no)) > 0;
  }

  //登録数上限チェック
  static function IsOver() {
    return DB::Count('SELECT icon_no FROM user_icon') >= UserIconConfig::NUMBER;
  }

  //アイコン情報取得
  static function Get($icon_no) {
    $format = 'SELECT * FROM user_icon WHERE icon_no = %d';
    return DB::FetchAssoc(sprintf($format, $icon_no));
  }

  //ファイル名取得
  static function GetFile($icon_no) {
    $format = 'SELECT icon_filename FROM user_icon WHERE icon_no = %d';
    return DB::FetchResult(sprintf($format, $icon_no));
  }

  //セッション情報取得
  static function GetSession($icon_no) {
    $format = 'SELECT icon_filename, session_id FROM user_icon WHERE icon_no = %d';
    return DB::FetchAssoc(sprintf($format, $icon_no), true);
  }

  //アイコン数取得
  static function GetCount(array $where) {
    $format  = 'SELECT icon_no FROM user_icon WHERE %s';
    $where[] = 'icon_no > 0';
    return DB::Count(sprintf($format, implode(' AND ', $where)));
  }

  //次のアイコン番号取得
  static function GetNumber() {
    return DB::FetchResult('SELECT MAX(icon_no) + 1 FROM user_icon');
  }

  //アイコンリスト取得
  static function GetList(array $where) {
    $format  = 'SELECT * FROM user_icon WHERE %s ORDER BY %s';
    $where[] = 'icon_no > 0';
    $sort    = RQ::$get->sort_by_name ? 'icon_name, icon_no' : 'icon_no, icon_name';
    $query   = sprintf($format, implode(' AND ', $where), $sort);
    if (RQ::$get->page != 'all') {
      $limit = max(0, IconConfig::VIEW * (RQ::$get->page - 1));
      $query .= sprintf(' LIMIT %d, %d', $limit, IconConfig::VIEW);
    }
    return DB::FetchAssoc($query);
  }

  //カテゴリ取得
  static function GetCategoryList($type) {
    $stack = array('SELECT', 'FROM user_icon WHERE', 'IS NOT NULL GROUP BY', 'ORDER BY icon_no');
    return DB::FetchArray(implode(" {$type} ", $stack));
  }

  //検索項目とタイトル、検索条件のセットから選択肢を抽出し、表示します。
  static function GetSelectionByType($type) {
    //選択状態の抽出
    $data   = RQ::$get->search ? RQ::$get->$type : Session::Get('icon_view', $type);
    $target = empty($data) ? array() : (is_array($data) ? $data : array($data));
    Session::Set('icon_view', $type, $target);
    if ($type == 'keyword') return $target;

    $format = 'SELECT DISTINCT %s FROM user_icon WHERE %s IS NOT NULL';
    return DB::FetchArray(sprintf($format, $type, $type));
  }

  //検索項目と検索値のセットから抽出条件を生成する
  static function GetInClause($type, array $list) {
    if (in_array('__null__', $list)) return $type . ' IS NULL';
    $stack = array();
    foreach ($list as $value) $stack[] = sprintf("'%s'", Text::Escape($value));
    return $type . sprintf(' IN (%s)', implode(',', $stack));
  }

  //アイコン情報更新
  static function Update($icon_no, $data) {
    $format = 'UPDATE user_icon SET %s WHERE icon_no = %d';
    return DB::ExecuteCommit(sprintf($format, $data, $icon_no));
  }

  //アイコン削除
  static function Delete($icon_no, $file) {
    $query = sprintf('DELETE FROM user_icon WHERE icon_no = %d', $icon_no);
    if (! DB::FetchBool($query)) return false; //削除処理
    unlink(Icon::GetFile($file)); //ファイル削除
    DB::Optimize('user_icon'); //テーブル最適化 + コミット
    return true;
  }

  //セッション削除
  static function ClearSession($icon_no) {
    $format = 'UPDATE user_icon SET session_id = NULL WHERE icon_no = %d';
    return DB::FetchBool(sprintf($format, $icon_no));
  }
}

//-- HTML 生成クラス (アイコン拡張) --//
class IconHTML {
  //アイコン情報出力
  static function Output($base_url = 'icon_view') {
    /*
      初回表示前に検索条件をリセットする
      TODO: リファラーをチェックすることで GET リクエストによる取得にも対処できる
      現時点では GET で直接検索を試みたユーザーのセッション情報まで配慮していないが、
      いずれ必要になるかも知れない (enogu)
    */
    if (is_null(RQ::$get->page)) Session::Clear('icon_view');

    //編集フォームの表示
    if ($base_url == 'icon_view') {
      $footer = "</fieldset>\n";
      if (RQ::$get->icon_no > 0) {
	$params = RQ::ToArray();
	unset($params['icon_no']);
	echo <<<HTML
<div class="link"><a href="icon_view.php">→アイコン一覧に戻る</a></div>
<fieldset><legend>アイコン設定の変更</legend>

HTML;
	self::OutputEdit(RQ::$get->icon_no);
	echo $footer;
      }
      else {
	echo "<fieldset><legend>ユーザアイコン一覧</legend>\n";
	self::OutputConcrete($base_url);
	echo $footer;
      }
    }
    else {
      self::OutputConcrete($base_url);
    }
  }

  //アイコン編集フォーム出力
  private function OutputEdit($icon_no) {
    $size = UserIcon::GetMaxLength();
    foreach (IconDB::Get($icon_no) as $stack) {
      extract($stack);
      $location = Icon::GetFile($icon_filename);
      $checked  = $disable > 0 ? ' checked' : '';
      echo <<<EOF
<form action="icon_edit.php" method="POST">
<input type="hidden" name="icon_no" value="{$icon_no}">
<table cellpadding="3">
<tr>
  <td rowspan="7"><img src="{$location}" style="border:3px solid {$color};"></td>
  <td><label for="name">アイコンの名前</label></td>
  <td><input type="text" id="name" name="icon_name" value="{$icon_name}" {$size}></td>
</tr>
<tr>
  <td><label for="appearance">出典</label></td>
  <td><input type="text" id="appearance" name="appearance" value="{$appearance}" {$size}></td>
</tr>
<tr>
  <td><label for="category">カテゴリ</label></td>
  <td><input type="text" id="category" name="category" value="{$category}" {$size}></td>
</tr>
<tr>
  <td><label for="author">アイコンの作者</label></td>
  <td><input type="text" id="author" name="author" value="{$author}" {$size}></td>
</tr>
<tr>
  <td><label for="color">アイコン枠の色</label></td>
  <td><input type="text" id="color" name="color" value="{$color}" size="10px" maxlength="7"> (例：#6699CC)</td>
</tr>
<tr>
  <td><label for="disable">非表示</label></td>
  <td><input type="checkbox" id="disable" name="disable" value="on"{$checked}></td>
</tr>
<tr>
  <td><label for="password">編集パスワード</label></td>
  <td><input type="password" id="password" name="password" size="20" value=""></td>
</tr>
<tr>
  <td colspan="3"><input type="submit" value="変更"></td>
</tr>
</table>
</form>

EOF;
    }
  }

  //アイコン情報を収集して表示する
  private function OutputConcrete($base_url = 'icon_view') {
    //-- ヘッダ出力 --//
    $colspan       = UserIconConfig::COLUMN * 2;
    $line_header   = sprintf('<tr><td colspan="%d">', $colspan);
    $line_footer   = '</td></tr>'."\n";
    $url_header    = sprintf('<a href="%sphp?', $base_url);
    $url_option    = array();
    $query_stack   = array();
    $category_list = IconDB::GetCategoryList('category');
    echo <<<EOF
<form id="icon_search" method="GET">
<table class="selector">
<tr>

EOF;
    //検索条件の表示
    $where = array();
    if ($base_url == 'user_manager') $where[] = "disable IS NOT TRUE";
    $stack = self::OutputByType('category', 'カテゴリ');
    if (0 < count($stack)) {
      foreach ($stack as $data) $url_option[] = sprintf("category[]={$data}");
      $where[] = IconDB::GetInClause('category', $stack);
    }

    $stack = self::OutputByType('appearance', '出典');
    if (0 < count($stack)) {
      foreach ($stack as $data) $url_option[] = "appearance[]={$data}";
      $where[] = IconDB::GetInClause('appearance', $stack);
    }

    $stack = self::OutputByType('author', 'アイコン作者');
    if (0 < count($stack)) {
      foreach ($stack as $data) $url_option[] = "author[]={$data}";
      $where[] = IconDB::GetInClause('author', $stack);
    }

    $stack = self::OutputByType('keyword', 'キーワード');
    if (0 < count($stack)) {
      $str = "LIKE '%{$stack[0]}%'";
      $where[] = "(category {$str} OR appearance {$str} OR author {$str} OR icon_name {$str})";
    }
    else {
      $stack = array('');
    }
    $keyword = $stack[0];

    $sort_by_name_checked = RQ::$get->sort_by_name ? ' checked' : '';
    echo <<<EOF
</tr>
<tr>
<td colspan="{$colspan}">
<label for="sort_by_name"><input id="sort_by_name" name="sort_by_name" type="checkbox" value="on"{$sort_by_name_checked}>名前順に並べ替える</label>
<label for="keyword">キーワード：<input id="keyword" name="keyword" type="text" value="{$keyword}"></label>
<input id="search" name="search" type="submit" value="検索">
<input id="page" name="page" type="hidden" value="1">
</td></tr></table>

EOF;

    //検索結果の表示
    if (empty(RQ::$get->room_no)) {
      $method = 'OutputDetailForIconView';
      echo <<<HTML
<table>
<caption>
[S] 出典 / [C] カテゴリ / [A] アイコンの作者<br>
アイコンをクリックすると編集できます (要パスワード)
</caption>
<thead>
<tr>

HTML;
    }
    elseif (isset(RQ::$get->room_no)) {
      $method = 'OutputDetailForUserEntry';
      echo <<<HTML
<table>
<caption>
あなたのアイコンを選択して下さい。
</caption>
<thead>
<tr>

HTML;
    }
    else {
      $method = null;
    }

    //ユーザアイコンのテーブルから一覧を取得
    $CONF = new StdClass();
    $CONF->view       = IconConfig::VIEW;
    $CONF->page       = IconConfig::PAGE;
    $CONF->url        = $base_url;
    $CONF->count      = IconDB::GetCount($where);
    $CONF->current    = RQ::$get->page;
    $CONF->option     = $url_option;
    $CONF->attributes = array('onclick' => 'return "return submit_icon_search(\'$page\');";');
    if (RQ::$get->room_no > 0) $CONF->option[] = 'room_no=' . RQ::$get->room_no;
    if (RQ::$get->icon_no > 0) $CONF->option[] = 'icon_no=' . RQ::$get->icon_no;
    printf('<td colspan="%d" class="page-link">', $colspan);
    self::OutputPageLink($CONF);
    echo <<<HTML
</td>
</tr>
</thead>
<tbody>
<tr>

HTML;

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
    echo <<<HTML
</tr>
</tbody>
</table>
</form>

HTML;
  }

  //検索項目とタイトル、検索条件のセットから選択肢を抽出し、表示する
  private function OutputByType($type, $caption) {
    $format = <<<EOF
<td>
<label for="%s[]">%s</label><br>
<select name="%s[]" size="6" multiple>
<option value="__all__">全て</option>%s
EOF;
    printf($format, $type, $caption, $type, "\n");

    $list = IconDB::GetSelectionByType($type);
    array_unshift($list, '__null__');

    $format = '<option value="%s"%s>%s</option>';
    $target = Session::Get('icon_view', $type);
    foreach ($list as $name) {
      printf($format,
	     $name, in_array($name, $target) ? ' selected' : '',
	     $name == '__null__' ? 'データ無し' : (strlen($name) > 0 ? $name : '空欄'));
    }
    echo "</select>\n</td>\n";

    return in_array('__all__', $target) ? array() : $target;
  }

  //アイコン詳細画面 (IconView 用)
  private function OutputDetailForIconView(array $icon_list, $cell_width) {
    extract($icon_list);
    $location      = Icon::GetFile($icon_filename);
    $wrapper_width = $icon_width + 6;
    $info_width    = $cell_width - $icon_width;
    $edit_url      = "icon_view.php?icon_no={$icon_no}";
    if ($disable > 0) $icon_name = sprintf('<s>%s</s>', $icon_name);
    echo <<<HTML
<td class="icon-details">
<label for="icon_{$icon_no}">
<a href="{$edit_url}" class="icon_wrapper" style="width:{$wrapper_width}px">
<img alt="{$icon_name}" src="{$location}" width="{$icon_width}" height="{$icon_height}" style="border:3px solid {$color};">
</a>
<ul style="width:{$info_width}px;">
<li><a href="{$edit_url}">No. {$icon_no}</a></li>
<li><a href="{$edit_url}">{$icon_name}</a></li>
<li><font color="{$color}">◆</font>{$color}</li>

HTML;

    $data = '';
    if (! empty($appearance)) $data .= '<li>[S]' . $appearance;
    if (! empty($category))   $data .= '<li>[C]' . $category;
    if (! empty($author))     $data .= '<li>[A]' . $author;
    echo $data;
    echo <<<HTML
</ul>
</label>
</td>

HTML;
  }

  //アイコン詳細画面 (UserEntry 用)
  private function OutputDetailForUserEntry(array $icon_list, $cell_width) {
    extract($icon_list);
    $location      = Icon::GetFile($icon_filename);
    $wrapper_width = $icon_width + 6;
    $info_width    = $cell_width - $wrapper_width;
    echo <<<HTML
<td class="icon_details"><label for="icon_{$icon_no}"><img alt="{$icon_name}" src="{$location}" width="{$icon_width}" height="{$icon_height}" style="border:3px solid {$color};"><br clear="all">
<input type="radio" id="icon_{$icon_no}" name="icon_no" value="{$icon_no}"> No. {$icon_no}<br>
<font color="{$color}">◆</font>{$icon_name}</label></td>

HTML;
  }

  //ページ送り用のリンクタグを出力する (PageLinkBuilder と統合できるかも)
  private function OutputPageLink(StdClass $CONFIG) {
    $page_count = ceil($CONFIG->count / $CONFIG->view);
    $start_page = $CONFIG->current== 'all' ? 1 : $CONFIG->current;
    if ($page_count - $CONFIG->current < $CONFIG->page) {
      $start_page = $page_count - $CONFIG->page + 1;
      if ($start_page < 1) $start_page = 1;
    }
    $end_page = $CONFIG->current + $CONFIG->page - 1;
    if ($end_page > $page_count) $end_page = $page_count;

    $url_stack = array('[' . (is_null($CONFIG->title) ? 'Page' : $CONFIG->title) . ']');
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

    if ($CONFIG->url == 'old_log') {
      $list = $CONFIG->option;
      $list['page'] = 'page=' . $CONFIG->current;
      $list['reverse'] = 'reverse=' . ($CONFIG->is_reverse ? 'off' : 'on');
      $url_stack[] = '[表示順]';
      $url_stack[] = $CONFIG->is_reverse ? '新↓古' : '古↓新';

      $url = $url_header . implode('&', $list) . '">';
      $name = ($CONFIG->is_reverse xor $CONFIG->reverse) ? '元に戻す' : '入れ替える';
      $url_stack[] =  $url . $name . '</a>';
    }
    echo implode(' ', $url_stack);
  }

  //ページ送り用のリンクタグを作成する
  private function GeneratePageLink(StdClass $CONFIG, $page, $title = null) {
    if ($page == $CONFIG->current) return sprintf('[%s]', $page);
    $option = (is_null($CONFIG->page_type) ? 'page' : $CONFIG->page_type) . '=' . $page;
    $list   = $CONFIG->option;
    array_unshift($list, $option);
    $url = $CONFIG->url . '.php?' . implode('&', $list);
    $attributes = array();
    if (isset($CONFIG->attributes)) {
      foreach($CONFIG->attributes as $attr => $value) {
	$attributes[] = $attr . '="'. eval($value) . '"';
      }
    }
    $attrs = implode(' ', $attributes);
    if (is_null($title)) $title = sprintf('[%s]', $page);
    return sprintf('<a href="%s" %s>%s</a>', $url, $attrs, $title);
  }
}
