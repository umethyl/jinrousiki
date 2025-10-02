<?php
//-- HTML 生成クラス (index 拡張) --//
class IndexHTML {
  const BACK_PAGE   = "<a href=\"%s\">←戻る</a><br>\n";
  const MENU_HEADER = "<div class=\"menu\">%s</div>\n<ul>\n";
  const MENU_GROUP  = "  <li class=\"menu-name\"><a href=\"javascript:void(0)\">▼%s</a></li>\n";
  const MENU_LINK   = "  <li class=\"menu-link\"><a href=\"%s\">%s</a></li>\n";
  const MENU_FOOTER = "</ul>\n";
  const SUB_MENU    = "<ul class=\"submenu\" onClick=\"fold_menu(this)\">\n";
  const BBS_TITLE   = '<a href="%s%sl50">告知スレッド情報</a>';
  const BBS_URL     = '%s%sl%dn';
  const BBS_RES     = "<dt>%s : <span>%s</span> : %s ID : %s</dt>\n<dd>%s</dd>";
  const VERSION     = 'Powered by %s %s from %s';
  const ADMIN       = "<br>\nFounded by: %s";
  const FOOTER      = "<div id=\"footer\">\n%s\n</div>\n";

  //ヘッダー出力
  static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . ServerConfig::COMMENT, 'index');
    HTML::OutputJavaScript('index');
    HTML::OutputJavaScript('room_manager');
    if (ServerConfig::BACK_PAGE != '') printf(self::BACK_PAGE, ServerConfig::BACK_PAGE);
  }

  //メニュー出力
  static function OutputMenu() {
    $str = sprintf(self::MENU_HEADER, '交流用サイト');
    foreach (MenuConfig::$list as $name => $url) $str .= sprintf(self::MENU_LINK, $url, $name);
    $str .= self::MENU_FOOTER;

    if (count(MenuConfig::$add_list) > 0) {
      $str .= sprintf(self::MENU_HEADER, '外部リンク');
      foreach (MenuConfig::$add_list as $group => $list) {
	$str .= self::SUB_MENU;
	$str .= sprintf(self::MENU_GROUP, $group);
	foreach ($list as $name => $url) $str .= sprintf(self::MENU_LINK, $url, $name);
	$str .= self::MENU_FOOTER;
      }
      $str .= self::MENU_FOOTER;
    }
    echo $str;
  }

  //掲示板情報出力
  static function OutputBBS() {
    if (BBSConfig::DISABLE) return;
    if (! ExternalLinkBuilder::CheckConnection(BBSConfig::RAW_URL)) {
      $title = sprintf(self::BBS_TITLE, BBSConfig::VIEW_URL, BBSConfig::THREAD);
      $str   = ExternalLinkBuilder::GenerateTimeOut(BBSConfig::RAW_URL);
      echo ExternalLinkBuilder::Generate($title, $str);
      return;
    }

    //スレッド情報を取得
    $url = sprintf(self::BBS_URL, BBSConfig::RAW_URL, BBSConfig::THREAD, BBSConfig::SIZE);
    if (($data = @file_get_contents($url)) == '') return;
    if (BBSConfig::ENCODE != ServerConfig::ENCODE) {
      $data = mb_convert_encoding($data, ServerConfig::ENCODE, BBSConfig::ENCODE);
    }

    $str = '';
    $str_stack = explode("\n", $data);
    array_pop($str_stack);
    foreach ($str_stack as $res_stack) {
      $res = explode('<>', $res_stack);
      $str .= sprintf(self::BBS_RES, $res[0], $res[1], $res[3], $res[6], $res[4]);
    }
    $title = sprintf(self::BBS_TITLE, BBSConfig::VIEW_URL, BBSConfig::THREAD);
    echo ExternalLinkBuilder::Generate($title, $str);
  }

  //フッター出力
  static function OutputFooter() {
    $str = sprintf(self::VERSION, ScriptInfo::PACKAGE, ScriptInfo::VERSION, ScriptInfo::DEVELOPER);
    if (ServerConfig::ADMIN) $str .= sprintf(self::ADMIN, ServerConfig::ADMIN);
    printf(self::FOOTER, $str);
    HTML::OutputFooter();
  }
}
