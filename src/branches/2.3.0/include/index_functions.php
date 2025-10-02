<?php
//-- HTML 生成クラス (index 拡張) --//
class IndexHTML {
  //実行
  static function Execute() {
    if (0 < RQ::Get()->id && RQ::Get()->id <= count(TopPageConfig::$server_list)) {
      InfoHTML::OutputSharedRoom(RQ::Get()->id, true);
    } else {
      self::Output();
    }
  }

  //出力
  private static function Output() {
    self::OutputHeader();
    self::OutputMenu();
    self::OutputBody();
    self::OutputFooter();
  }

  //ヘッダー出力
  private static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . ServerConfig::COMMENT, 'index');
    HTML::OutputJavaScript('index');
    HTML::OutputJavaScript('room_manager');
    HTML::OutputBodyHeader();

    if (ServerConfig::BACK_PAGE != '') {
      echo HTML::GenerateLink(ServerConfig::BACK_PAGE, Message::BACK) . Text::BRLF;
    }
    $format = <<<EOF
<a href="./"><img src="img/title/top.jpg" title="%s" alt="%s"></a>
<div class="comment">%s</div>
<noscript>%s</noscript>
EOF;

    printf($format . Text::LF, TopPageMessage::TITLE, TopPageMessage::TITLE,
	   ServerConfig::COMMENT, TopPageMessage::CAUTION_JAVASCRIPT);
  }

  //メニュー出力
  private static function OutputMenu() {
    Text::Output('<table id="main"><tr>' . Text::LF . '<td>');
    include_once('top/menu.html');

    $tag_header = '<div class="menu">%s</div>%s<ul>' . Text::LF;
    $tag_link   = '  <li class="menu-link"><a href="%s">%s</a></li>' . Text::LF;
    $tag_footer = '</ul>' . Text::LF;

    $str = sprintf($tag_header, TopPageMessage::MENU_COMMUNICATION, Text::LF);
    foreach (MenuConfig::$list as $name => $url) {
      $str .= sprintf($tag_link, $url, $name);
    }
    $str .= $tag_footer;

    if (count(MenuConfig::$add_list) > 0) {
      $tag_menu  = '<ul class="submenu" onClick="fold_menu(this)">' . Text::LF;
      $tag_group = '  <li class="menu-name"><a href="javascript:void(0)">▼%s</a></li>' . Text::LF;

      $str .= sprintf($tag_header, TopPageMessage::MENU_OUTER, Text::LF);
      foreach (MenuConfig::$add_list as $group => $list) {
	$str .= $tag_menu . sprintf($tag_group, $group);
	foreach ($list as $name => $url) {
	  $str .= sprintf($tag_link, $url, $name);
	}
	$str .= $tag_footer;
      }
      $str .= $tag_footer;
    }
    echo $str;
  }

  //メイン情報出力
  private static function OutputBody() {
    Text::Output('</td>' . Text::LF . '<td>');
    self::OutputInformation();
    self::OutputGameList();
    if (! TopPageConfig::DISABLE_SHARED_SERVER) InfoHTML::OutputSharedRoomList(true);
    self::OutputBBS();
    self::OutputCreateRoom();
    Text::Output('</td>' . Text::LF . '</tr></table>');
  }

  //情報一覧出力
  private static function OutputInformation() {
    $format = <<<EOF
  <fieldset>
    <legend>%s</legend>
    <div class="information">
EOF;
    printf($format, TopPageMessage::INFORMATION);
    include_once('top/information.html');
    Text::Output('</div>' . Text::LF . '  </fieldset>');
  }

  //ゲーム一覧出力
  private static function OutputGameList() {
    $format = <<<EOF
  <fieldset>
    <legend>%s</legend>
    <div class="game-list">
EOF;
    printf($format, TopPageMessage::GAME_LIST);
    include_once('room_manager.php');
    Text::Output('</div>' . Text::LF . '  </fieldset>');
  }

  //掲示板情報出力
  private static function OutputBBS() {
    if (BBSConfig::DISABLE) return;
    if (! ExternalLinkBuilder::CheckConnection(BBSConfig::RAW_URL)) {
      $title = sprintf(TopPageMessage::BBS_TITLE, BBSConfig::VIEW_URL, BBSConfig::THREAD);
      ExternalLinkBuilder::OutputTimeOut($title, BBSConfig::RAW_URL);
      return;
    }

    //スレッド情報を取得
    $url = sprintf('%s%sl%dn', BBSConfig::RAW_URL, BBSConfig::THREAD, BBSConfig::SIZE);
    if (($data = @file_get_contents($url)) == '') return;
    if (BBSConfig::ENCODE != ServerConfig::ENCODE) {
      $data = mb_convert_encoding($data, ServerConfig::ENCODE, BBSConfig::ENCODE);
    }

    $format = "<dt>%s : <span>%s</span> : %s ID : %s</dt>\n<dd>%s</dd>";
    $str = '';
    $str_stack = explode(Text::LF, $data);
    array_pop($str_stack);
    foreach ($str_stack as $res_stack) {
      $res = explode('<>', $res_stack);
      $str .= sprintf($format, $res[0], $res[1], $res[3], $res[6], $res[4]);
    }
    $title = sprintf(TopPageMessage::BBS_TITLE, BBSConfig::VIEW_URL, BBSConfig::THREAD);
    ExternalLinkBuilder::Output($title, $str);
  }

  //村作成フォーム出力
  private static function OutputCreateRoom() {
    $format = <<<EOF
  <fieldset>
    <legend>%s</legend>
EOF;
    printf($format, TopPageMessage::CREATE_ROOM);
    RoomManager::OutputCreate();
    Text::Output('</fieldset>');
  }

  //フッター出力
  private static function OutputFooter() {
    $format = 'Powered by %s %s from %s';
    $str    = sprintf($format, ScriptInfo::PACKAGE, ScriptInfo::VERSION, ScriptInfo::DEVELOPER);
    if (ServerConfig::ADMIN) {
      $str .= sprintf(Text::BRLF . 'Founded by: %s', ServerConfig::ADMIN);
    }
    printf('<div id="footer">%s%s%s</div>' . Text::LF, Text::LF, $str, Text::LF);
    HTML::OutputFooter();
  }
}
