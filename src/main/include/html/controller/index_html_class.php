<?php
//-- HTML 生成クラス (index 拡張) --//
final class IndexHTML {
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
    JavaScriptHTML::Output('index');
    JavaScriptHTML::Output('room_manager');
    HTML::OutputBodyHeader();
    if (ServerConfig::BACK_PAGE != '') {
      LinkHTML::Output(ServerConfig::BACK_PAGE, Message::BACK, true);
    }
    LinkHTML::Output('./', self::GenerateHeaderImage());
    DivHTML::Output(ServerConfig::COMMENT, [HTML::CSS => 'comment']);
    JavaScriptHTML::OutputNoscript(TopPageMessage::CAUTION_JAVASCRIPT);
  }

  //ヘッダ画像生成
  private static function GenerateHeaderImage() {
    $path  = 'img/title/top.jpg';
    $title = ImageHTML::GenerateTitle(TopPageMessage::TITLE, TopPageMessage::TITLE);
    $css   = '';
    return ImageHTML::Generate($path, $title, $css);
  }

  //メニュー出力
  private static function OutputMenu() {
    Text::Output(TableHTML::GenerateHeader(null, true, 'main'));
    TableHTML::OutputTdHeader();
    include('top/menu.html');
    self::OutputMenuLink();
    self::OutputAddMenuLink();
    TableHTML::OutputTdFooter();
  }

  //メニュー交流リンク出力
  private static function OutputMenuLink() {
    if (count(MenuConfig::$list) < 1) {
      return;
    }

    self::OutputMenuLinkHeader(TopPageMessage::MENU_COMMUNICATION);
    self::OutputMenuLinkList(MenuConfig::$list);
    Text::Output(HTML::GenerateTagFooter('ul'));
  }

  //メニュー外部リンク出力
  private static function OutputAddMenuLink() {
    if (count(MenuConfig::$add_list) < 1) {
      return;
    }

    self::OutputMenuLinkHeader(TopPageMessage::MENU_OUTER);
    foreach (MenuConfig::$add_list as $group => $list) {
      self::OutputAddMenuLinkHeader($group);
      self::OutputMenuLinkList($list);
      Text::Output(HTML::GenerateTagFooter('ul'));
    }
    Text::Output(HTML::GenerateTagFooter('ul'));
  }

  //メニューリンクヘッダ出力
  private static function OutputMenuLinkHeader(string $str) {
    DivHTML::Output($str, [HTML::CSS => 'menu']);
    Text::Output(HTML::GenerateTagHeader('ul'));
  }

  //メニューリンクリスト出力
  private static function OutputMenuLinkList(array $list) {
    $tag = 'li';
    $css = 'menu-link';
    foreach ($list as $name => $url) {
      Text::Output('  ' . HTML::GenerateTag($tag, LinkHTML::Generate($url, $name), $css));
    }
  }

  //メニュー外部リンクヘッダ出力
  private static function OutputAddMenuLinkHeader(string $str) {
    $tag  = 'ul';
    $tag .= HTML::GenerateAttribute('class', 'submenu');
    $tag .= HTML::GenerateAttribute('onClick', 'fold_menu(this)');
    Text::Output(HTML::GenerateTagHeader($tag));

    $url = LinkHTML::Generate('javascript:void(0)', '▼' . $str);
    Text::Output('  ' . HTML::GenerateTag('li', $url, 'menu-name'));
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
    DivHTML::OutputHeader([HTML::CSS => $class]);
    include($file);
    DivHTML::OutputFooter();
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
    $str_stack = Text::Parse(Encoder::Convert($data, BBSConfig::ENCODE), Text::LF);
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
