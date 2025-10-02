<?php
//-- ページ送りリンク生成クラス --//
class PageLinkBuilder {
  public function __construct($file, $page, $count, $title = 'Page', $type = 'page') {
    $this->view_total = $count;
    $this->view_page  = OldLogConfig::PAGE;
    $this->view_count = OldLogConfig::VIEW;
    $this->reverse    = OldLogConfig::REVERSE;

    $this->file   = $file;
    $this->url    = '<a href="' . $file . URL::GetExt();
    $this->title  = $title;
    $this->type   = $type;
    $this->option = array();
    $this->page   = new stdClass();
    $this->SetPage($page);
  }

  //オプションを追加する
  public function AddOption($type, $value = Swichter::ON) {
    $this->option[$type] = $type . '=' . $value;
  }

  //ページリンクを生成する
  public function Generate() {
    $url_stack = array(Text::QuoteBracket($this->title));
    if ($this->file == 'index') {
      $url_stack[] = '[<a href="index.html">new</a>]';
    }

    //表示ページ数調整 (先頭側)
    if ($this->page->start > 1 && $this->page->total > $this->view_page) {
      $url_stack[] = $this->GenerateTag(1, Text::QuoteBracket(1) . '...');
      $url_stack[] = $this->GenerateTag($this->page->start - 1, '&lt;&lt;');
    }

    for ($i = $this->page->start; $i <= $this->page->end; $i++) {
      $url_stack[] = $this->GenerateTag($i);
    }

    //表示ページ数調整 (終末側)
    if ($this->page->end < $this->page->total) {
      $str = Text::QuoteBracket($this->page->total);
      $url_stack[] = $this->GenerateTag($this->page->end + 1, '&gt;&gt;');
      $url_stack[] = $this->GenerateTag($this->page->total,   '...' . $str);
    }
    if ($this->file != 'index') {
      $url_stack[] = $this->GenerateTag('all');
    }

    if ($this->file == 'old_log') {
      $this->AddOption('reverse', Switcher::Get(! $this->set_reverse));
      $url_stack[] = OldLogMessage::LINK_ORDER;
      if ($this->set_reverse) {
	$url_stack[] = OldLogMessage::ORDER_REVERSE;
      } else {
	$url_stack[] = OldLogMessage::ORDER_NORMAL;
      }

      if ($this->set_reverse xor $this->reverse) {
	$name = OldLogMessage::ORDER_RESET;
      } else {
	$name = OldLogMessage::ORDER_CHANGE;
      }
      $url_stack[] =  $this->GenerateTag($this->page->set, $name, true);

      if (RQ::Get()->watch) {
	$this->AddOption('reverse', Switcher::Get($this->set_reverse));
	$this->AddOption('watch',   Switcher::OFF);
	$url_stack[] = $this->GenerateTag($this->page->set, OldLogMessage::LINK_WIN, true);
      }
    }
    return ArrayFilter::Concat($url_stack);
  }

  //ページリンクを出力する
  public function Output() {
    echo $this->Generate();
  }

  //ページ送り用のリンクタグを作成する
  protected function GenerateTag($page, $title = null, $force = false) {
    if ($page == $this->page->set && ! $force) return Text::QuoteBracket($page);
    if (is_null($title)) {
      $title = Text::QuoteBracket($page);
    }
    if ($this->file == 'index') {
      $footer = $page . '.html';
    } else {
      $list = $this->option;
      array_unshift($list, $this->type . '=' . $page);
      $footer = ArrayFilter::Concat($list, '&');
    }
    return $this->url . $footer . '">' . $title . '</a>';
  }

  //表示するページのアドレスをセット
  private function SetPage($page) {
    $total = ceil($this->view_total / $this->view_count);
    if ($page == 'all') {
      $start = 1;
    } else {
      $start = $page - floor($this->view_page / 2);
      if ($total - $start < $this->view_page) { //残りページが少ない場合は表示開始位置をずらす
	$start = $total - $this->view_page + 1;
      }
      $start = max(1, $start);
    }
    $end = $start + $this->view_page - 1;
    if ($end > $total) $end = $total;

    $this->page->set   = $page;
    $this->page->total = $total;
    $this->page->start = $start;
    $this->page->end   = $end;
    //Text::p($this->page, '◆page');
    $this->limit = $page == 'all' ? '' : $this->view_count * ($page - 1);
    $this->query = $page == 'all' ? '' : DB::SetLimit($this->limit, $this->view_count);
  }
}
