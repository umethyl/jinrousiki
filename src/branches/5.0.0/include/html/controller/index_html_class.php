<?php
//-- HTML 生成クラス (index 拡張) --//
class IndexHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    self::OutputMenu();
    self::OutputBody();
    self::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . ServerConfig::COMMENT, 'index');
    HTML::OutputJavaScript('index');
    HTML::OutputJavaScript('room_manager');
    HTML::OutputBodyHeader();
    if (ServerConfig::BACK_PAGE != '') {
      HTML::OutputLink(ServerConfig::BACK_PAGE, Message::BACK, true);
    }
    Text::Printf(self::GetTitle(),
      TopPageMessage::TITLE, TopPageMessage::TITLE,
      ServerConfig::COMMENT, TopPageMessage::CAUTION_JAVASCRIPT
    );
  }

  //メニュー出力
  private static function OutputMenu() {
    Text::Output(self::GetMainHeader());
    include('top/menu.html');
    self::OutputMenuLink();
    self::OutputMenuAddLink();
    TableHTML::OutputTdFooter();
  }

  //メニュー交流リンク出力
  private static function OutputMenuLink() {
    if (count(MenuConfig::$list) < 1) {
      return;
    }

    Text::Printf(self::GetMenu(),
      TopPageMessage::MENU_COMMUNICATION, self::GenerateMenuLink(MenuConfig::$list)
    );
  }

  //メニュー外部リンク出力
  private static function OutputMenuAddLink() {
    if (count(MenuConfig::$add_list) < 1) {
      return;
    }

    $tag = self::GetSubMenu();
    $str = '';
    foreach (MenuConfig::$add_list as $group => $list) {
      $str .= Text::Format($tag, $group, self::GenerateMenuLink($list));
    }
    Text::Printf(self::GetMenu(), TopPageMessage::MENU_OUTER, $str);
  }

  //メイン情報出力
  private static function OutputBody() {
    TableHTML::OutputTdHeader();
    self::OutputField(TopPageMessage::INFORMATION, 'information', 'top/information.html');
    self::OutputNotice();
    self::OutputField(TopPageMessage::GAME_LIST,   'game-list',   'room_manager.php');
    if (false === TopPageConfig::DISABLE_SHARED_SERVER) {
      InfoHTML::OutputSharedRoomList(true);
    }
    self::OutputBBS();
    self::OutputCreateRoom();
    TableHTML::OutputTdFooter();
    TableHTML::OutputFooter();
  }

  //一覧出力
  private static function OutputField($title, $class, $file) {
    HTML::OutputFieldsetHeader($title);
    HTML::OutputDivHeader($class);
    include($file);
    HTML::OutputDivFooter();
    HTML::OutputFieldsetFooter();
  }

  //警告メッセージ出力
  private static function OutputNotice() {
    JinrouAdmin::OutputNoticeMessage();
  }

  //掲示板情報出力
  private static function OutputBBS() {
    if (BBSConfig::DISABLE) {
      return;
    }
    if (! ExternalLinkBuilder::IsConnect(BBSConfig::RAW_URL)) {
      $title = sprintf(TopPageMessage::BBS_TITLE, BBSConfig::VIEW_URL, BBSConfig::THREAD);
      ExternalLinkBuilder::OutputTimeOut($title, BBSConfig::RAW_URL);
      return;
    }

    //スレッド情報を取得
    $url  = sprintf('%s%sl%dn', BBSConfig::RAW_URL, BBSConfig::THREAD, BBSConfig::SIZE);
    $data = @file_get_contents($url);
    if ($data == '') {
      return;
    }

    $format = self::GetBBS();
    $str = '';
    $str_stack = Text::Parse(Text::Encode($data, BBSConfig::ENCODE), Text::LF);
    array_pop($str_stack);
    foreach ($str_stack as $res_stack) {
      $res = Text::Parse($res_stack, '<>');
      $str .= sprintf($format, $res[0], $res[1], $res[3], $res[6], $res[4]);
    }
    $title = sprintf(TopPageMessage::BBS_TITLE, BBSConfig::VIEW_URL, BBSConfig::THREAD);
    ExternalLinkBuilder::Output($title, $str);
  }

  //村作成フォーム出力
  private static function OutputCreateRoom() {
    HTML::OutputFieldsetHeader(TopPageMessage::CREATE_ROOM);
    RoomManagerController::OutputCreate();
    HTML::OutputFieldsetFooter();
  }

  //メニューリンク生成
  private static function GenerateMenuLink(array $list) {
    $tag = self::GetMenuLink();
    $str = '';
    foreach ($list as $name => $url) {
      $str .= Text::Format($tag, $url, $name);
    }
    return $str;
  }

  //フッター出力
  private static function OutputFooter() {
    if (ServerConfig::ADMIN) {
      $str = sprintf(Text::BRLF . 'Founded by: %s', ServerConfig::ADMIN);
    } else {
      $str = '';
    }

    Text::Printf(self::GetFooter(),
      ScriptInfo::PACKAGE, ScriptInfo::VERSION, ScriptInfo::DEVELOPER, $str
    );
    HTML::OutputFooter();
  }

  //タイトル画像タグ
  private static function GetTitle() {
    return <<<EOF
<a href="./"><img src="img/title/top.jpg" alt="%s" title="%s"></a>
<div class="comment">%s</div>
<noscript>%s</noscript>
EOF;
  }

  //メインテーブルヘッダタグ
  private static function GetMainHeader() {
    return <<<EOF
<table id="main"><tr>
<td>
EOF;
  }

  //メニュータグ
  private static function GetMenu() {
    return <<<EOF
<div class="menu">%s</div>
<ul>
%s</ul>
EOF;
  }

  //メニューリンクタグ
  private static function GetMenuLink() {
    return '  <li class="menu-link"><a href="%s">%s</a></li>';
  }

  //サブメニュータグ
  private static function GetSubMenu() {
    return <<<EOF
<ul class="submenu" onClick="fold_menu(this)">
  <li class="menu-name"><a href="javascript:void(0)">▼%s</a></li>
%s</ul>
EOF;
  }

  //掲示板タグ
  private static function GetBBS() {
    return <<<EOF
<dt>%s : <span>%s</span> : %s ID : %s</dt>
<dd>%s</dd>
EOF;
  }

  //フッタタグ
  private static function GetFooter() {
    return <<<EOF
<div id="footer">
Powered by %s %s from %s%s
</div>
EOF;
  }
}
