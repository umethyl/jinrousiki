<?php
//-- ページ送りリンク生成クラス --//
final class PageLinkBuilder extends stdClass {
  public function __construct($file, $page, $count, $title = 'Page', $type = 'page') {
    $this->view_total = $count;
    $this->view_page  = OldLogConfig::PAGE;
    $this->view_count = OldLogConfig::VIEW;
    $this->reverse    = OldLogConfig::REVERSE;

    $this->file   = $file;
    $this->url    = '<a href="' . $file . URL::GetExt();
    $this->title  = $title;
    $this->type   = $type;
    $this->option = [];
    $this->page   = new stdClass();
    $this->SetPage($page);
  }

  //オプションを追加する
  public function AddOption($type, $value = Swichter::ON) {
    $this->option[$type] = $type . '=' . $value;
  }

  //ページリンクを生成する
  public function Generate() {
    $url_stack = [Text::QuoteBracket($this->title)];
    if ($this->file == 'index') {
      $url_stack[] = Text::QuoteBracket(LinkHTML::Generate('index.html', 'new'));
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
      $value = Switcher::Get(false === $this->set_reverse);
      $this->AddOption(RequestDataLogRoom::REVERSE_LIST, $value);
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

      if (RQ::Fetch()->watch) {
	$this->AddOption(RequestDataLogRoom::REVERSE_LIST, Switcher::Get($this->set_reverse));
	$this->AddOption(RequestDataLogRoom::WATCH, Switcher::OFF);
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
    if ($page == $this->page->set && false === $force) {
      return Text::QuoteBracket($page);
    }

    if (null === $title) {
      $title = Text::QuoteBracket($page);
    }
    if ($this->file == 'index') {
      $footer = $page . '.html';
    } else {
      $list = $this->option;
      array_unshift($list, $this->type . '=' . $page);
      $footer = URL::Concat($list);
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
    $end = min($total, $start + $this->view_page - 1);

    $this->page->set   = $page;
    $this->page->total = $total;
    $this->page->start = $start;
    $this->page->end   = $end;
    //Text::p($this->page, '◆page');
  }
}
