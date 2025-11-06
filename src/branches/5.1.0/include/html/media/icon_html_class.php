<?php
//-- HTML 生成クラス (アイコン拡張) --//
final class IconHTML {
  //アイコン情報出力
  public static function Output(string $url) {
    /*
      初回表示前に検索条件をリセットする
      TODO: リファラーをチェックすることで GET リクエストによる取得にも対処できる
      現時点では GET で直接検索を試みたユーザーのセッション情報まで配慮していないが、
      いずれ必要になるかも知れない (enogu)
    */
    if (null === RQ::Fetch()->page) {
      Session::Clear('icon_view');
    }
    self::OutputConcrete($url);
  }

  //アイコン情報を収集して表示する
  private static function OutputConcrete(string $base_url) {
    //-- 検索フォームヘッダ出力 --//
    Text::Output(self::GetSearchHeader());

    //-- セレクタ出力 --//
    $query      = Query::Init()->Table('user_icon')->Select()->WhereUpper('icon_no');
    $list       = [0];
    $sql_stack  = [];
    $url_option = [];
    if ($base_url == 'user_manager') {
      $query->WhereNotTrue('disable');
    }

    $selector_list = [
      RequestDataIcon::CATEGORY   => IconMessage::CATEGORY,
      RequestDataIcon::APPEARANCE => IconMessage::APPEARANCE,
      RequestDataIcon::AUTHOR     => IconMessage::AUTHOR
    ];
    foreach ($selector_list as $request => $message) {
      $stack = self::OutputSelector($request, $message);
      if (0 < count($stack)) {
	foreach ($stack as $data) {
	  $url_option[] = URL::ConvertList($request, $data);
	}
	ArrayFilter::AddMerge($list, IconDB::SetQueryIn($query, $request, $stack));
      }
    }

    $stack = self::OutputSelector(RequestDataIcon::KEYWORD, IconMessage::KEYWORD);
    if (0 < count($stack)) {
      $keyword = $stack[0];
      ArrayFilter::AddMerge($list, IconDB::SetQueryLike($query, $keyword));
    } else {
      $keyword = '';
    }

    //-- 検索フォームフッタ出力 --//
    Text::Printf(self::GetSearchFooter(),
      UserIconConfig::COLUMN * 2,
      FormHTML::Checked(RQ::Fetch()->sort_by_name), IconMessage::SORT_BY_NAME,
      IconMessage::KEYWORD_INPUT, $keyword, IconMessage::SEARCH
    );

    //検索結果の表示
    if (empty(RQ::Get(RequestDataGame::ID))) {
      $method = 'OutputDetailForIconView';
      $edit_url = URL::GenerateSwitch('icon_view', RequestDataIcon::MULTI);
      Text::Printf(self::GetCaption(),
	IconMessage::APPEARANCE, IconMessage::CATEGORY, IconMessage::AUTHOR,
	IconMessage::SEARCH_EXPLAIN, LinkHTML::Generate($edit_url, IconMessage::MULTI_EDIT)
      );
    } elseif (null !== RQ::Get(RequestDataGame::ID)) {
      $method = 'OutputDetailForUserEntry';
      Text::Output(self::GetCaptionForUserEntry());
    } else {
      $method = null;
    }

    //ユーザアイコンのテーブルから一覧を取得
    $CONF = new stdClass();
    $CONF->view       = IconConfig::VIEW;
    $CONF->page       = IconConfig::PAGE;
    $CONF->url        = $base_url;
    $CONF->count      = IconDB::Count($query, $list);
    $CONF->current    = RQ::Fetch()->page;
    $CONF->option     = $url_option;
    $CONF->attributes = ['onClick' => 'return "return submit_icon_search(\'$page\');";'];
    if (RQ::Get(RequestDataGame::ID) > 0) {
      $CONF->option[] = URL::ConvertInt(RequestDataGame::ID, RQ::Get(RequestDataGame::ID));
    }
    if (RQ::Fetch()->icon_no > 0) {
      $CONF->option[] = URL::ConvertInt(RequestDataIcon::ID, RQ::Fetch()->icon_no);
    }
    printf('<td colspan="%d" class="page-link">', UserIconConfig::COLUMN * 2);
    self::OutputPageLink($CONF);
    Text::Output(self::GetSearchResultHeader());

    //アイコン情報の表示
    if (isset($method)) {
      $column = 0;
      foreach (IconDB::GetList($query, $list) as $icon_info) {
	self::$method($icon_info, 162);
	TableHTML::OutputFold(++$column, UserIconConfig::COLUMN);
      }
    }
    Text::Output(self::GetSearchResultFooter());
  }

  //セレクタ出力
  private static function OutputSelector($type, $caption) {
    Session::Init('icon_view', $type);
    $str    = '';
    $list   = IconDB::Search($type);
    $target = Session::Get('icon_view', $type);
    array_unshift($list, '__null__');
    foreach ($list as $name) {
      if ($name == '__null__') {
	$space = IconMessage::NOTHING;
      } elseif (Text::Exists($name)) {
	$space = $name;
      } else {
	$space = IconMessage::SPACE;
      }
      $selected = FormHTML::Selected(in_array($name, $target));
      $str .= Text::Format(self::GetSelectorOption(), $name, $selected, $space);
    }
    Text::Printf(self::GetSelector(), $type, $caption, $type, Message::FORM_ALL, $str);

    return in_array('__all__', $target) ? [] : $target;
  }

  //アイコン詳細画面 (IconView 用)
  private static function OutputDetailForIconView(array $icon_list, $cell_width) {
    extract($icon_list);
    $edit_url = URL::GenerateInt('icon_view', RequestDataIcon::ID, $icon_no);
    if ($disable > 0) {
      $icon_name = HTML::GenerateTag('s', $icon_name);
    }
    $data = '';
    if (false === empty($appearance)) {
      $data .= HTML::GenerateTag('li', Text::QuoteBracket('S') . ' ' . $appearance);
    }
    if (false === empty($category)) {
      $data .= HTML::GenerateTag('li', Text::QuoteBracket('C') . ' ' . $category);
    }
    if (false === empty($author)) {
      $data .= HTML::GenerateTag('li', Text::QuoteBracket('A') . ' ' . $author);
    }

    Text::Printf(self::GetDetailForIconView(),
      $edit_url, $icon_width + 6,
      Icon::GetFile($icon_filename), $icon_name, $icon_width, $icon_height, $color,
      $cell_width - $icon_width,
      $edit_url, $icon_no,
      $edit_url, $icon_name,
      $color, Message::SYMBOL, $color, $data
    );
  }

  //アイコン詳細画面 (UserEntry 用)
  private static function OutputDetailForUserEntry(array $icon_list, $cell_width) {
    extract($icon_list);
    $wrapper_width = $icon_width + 6;
    $info_width    = $cell_width - $wrapper_width;

    Text::Printf(self::GetDetailForUserEntry(),
      $icon_no, Icon::GetFile($icon_filename), $icon_name, $icon_width, $icon_height, $color,
      $icon_no, $icon_no, $icon_no,
      $color, Message::SYMBOL, $icon_name
    );
  }

  //ページ送り用のリンクタグを出力する (PageLinkBuilder と統合できるかも)
  private static function OutputPageLink(stdClass $CONFIG) {
    $page_count = ceil($CONFIG->count / $CONFIG->view);
    if ($CONFIG->current == 'all') {
      $current    = 1;
      $start_page = 1;
    } else {
      $current    = $CONFIG->current;
      $start_page = $CONFIG->current;
    }
    if ($page_count - $current < $CONFIG->page) {
      $start_page = max(1, $page_count - $CONFIG->page + 1);
    }
    $end_page = min($page_count, $current + $CONFIG->page - 1);

    $url_stack = [Text::QuoteBracket(isset($CONFIG->title) ? $CONFIG->title : 'Page')];
    //表示ページ数調整 (先頭側)
    if ($CONFIG->current > 1 && $page_count > $CONFIG->page) {
      $url_stack[] = self::GeneratePageLink($CONFIG, 1, Text::QuoteBracket(1) . '...');
      $url_stack[] = self::GeneratePageLink($CONFIG, $start_page - 1, '&lt;&lt;');
    }

    for ($page_number = $start_page; $page_number <= $end_page; $page_number++) {
      $url_stack[] = self::GeneratePageLink($CONFIG, $page_number);
    }

    //表示ページ数調整 (終末側)
    if ($page_number <= $page_count) {
      $str = Text::QuoteBracket($page_count);
      $url_stack[] = self::GeneratePageLink($CONFIG, $page_number, '&gt;&gt;');
      $url_stack[] = self::GeneratePageLink($CONFIG, $page_count, '...' . $str);
    }
    $url_stack[] = self::GeneratePageLink($CONFIG, 'all');

    echo ArrayFilter::Concat($url_stack);
  }

  //ページ送り用のリンクタグを作成する
  private static function GeneratePageLink(stdClass $CONFIG, $page, $title = null) {
    if ($page == $CONFIG->current) {
      return Text::QuoteBracket($page);
    }

    $option = (isset($CONFIG->page_type) ? $CONFIG->page_type : 'page') . '=' . $page;
    $list   = $CONFIG->option;
    array_unshift($list, $option);
    $url = $CONFIG->url . URL::GetExt() . URL::Concat($list);
    $attributes = [];
    if (isset($CONFIG->attributes)) {
      foreach ($CONFIG->attributes as $attr => $value) {
	$attributes[] = $attr . '="'. eval($value) . '"';
      }
    }
    if (null === $title) {
      $title = Text::QuoteBracket($page);
    }

    return sprintf('<a href="%s" %s>%s</a>', $url, ArrayFilter::Concat($attributes), $title);
  }

  //検索フォームヘッダタグ
  private static function GetSearchHeader() {
    return <<<EOF
<form method="get" id="icon_search">
<table class="selector">
<tr>
EOF;
  }

  //セレクタタグ
  private static function GetSelector() {
    return <<<EOF
<td>
<label for="%s[]">%s</label><br>
<select name="%s[]" size="6" multiple>
<option value="__all__">%s</option>
%s</select>
</td>
EOF;
  }

  //セレクタオプションタグ
  private static function GetSelectorOption() {
    return '<option value="%s"%s>%s</option>';
  }

  //検索フォームフッタタグ
  private static function GetSearchFooter() {
    return <<<EOF
</tr>
<tr>
<td colspan="%s">
<label for="sort_by_name"><input id="sort_by_name" name="sort_by_name" type="checkbox" value="on"%s>%s</label>
<label for="keyword">%s<input id="keyword" name="keyword" type="text" value="%s"></label>
<input id="page" name="page" type="hidden" value="1">
<input id="search" name="search" type="submit" value="%s">
</td></tr></table>
EOF;
  }

  //キャプションタグ
  private static function GetCaption() {
    return <<<EOF
<table>
<caption>
[S] %s / [C] %s / [A] %s<br>
%s / %s
</caption>
<thead><tr>
EOF;
  }

  //キャプションタグ (UserEntry 用)
  private static function GetCaptionForUserEntry() {
    return '<thead><tr>';
  }

  //検索結果ヘッダタグ
  private static function GetSearchResultHeader() {
    return <<<EOF
</td>
</tr></thead>
<tbody>
<tr>
EOF;
  }

  //検索結果フッタタグ
  private static function GetSearchResultFooter() {
    return <<<EOF
</tr>
</tbody>
</table>
</form>
EOF;
  }

  //アイコン詳細画面タグ (IconView 用)
  private static function GetDetailForIconView() {
    return <<<EOF
<td class="icon-details">
<a href="%s" class="icon_wrapper" style="width:%dpx">
<img src="%s" alt="%s" width="%d" height="%d" style="border:3px solid %s;">
</a>
</td>
<td class="icon-details">
<ul style="width:%dpx;">
<li><a href="%s">No. %d</a></li>
<li><a href="%s">%s</a></li>
<li><span style="color:%s;">%s</span>%s</li>
%s</ul>
</td>
EOF;
  }

  //アイコン詳細画面タグ (UserEntry 用)
  private static function GetDetailForUserEntry() {
    return <<<EOF
<td class="icon_details"><label for="icon_%d"><img src="%s" alt="%s" width="%d" height="%d" style="border:3px solid %s;"><br clear="all">
<input type="radio" id="icon_%d" name="icon_no" value="%d"> No. %d<br>
<span style="color:%s;">%s</span>%s</label></td>
EOF;
  }
}
