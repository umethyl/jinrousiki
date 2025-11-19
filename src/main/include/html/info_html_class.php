<?php
//-- HTML 生成クラス (Info 拡張) --//
final class InfoHTML {
  //HTMLファイルロード
  public static function Load($name, $path = '') {
    include(sprintf('%s/info/%s%s.html', JINROU_INC, $path, $name));
  }

  //ヘッダ出力
  public static function OutputHeader($title, $level = 0, $css = 'info') {
    HTML::OutputHeader(Text::QuoteBracket($title), 'info/' . $css, true);
    Text::Printf(self::GetHeader(),
      $title, str_repeat('../', $level + 1), $level == 0 ? './' : str_repeat('../', $level),
      InfoMessage::TITLE_TOP
    );
  }

  //フレーム出力
  public static function OutputFrame($url) {
    FrameHTML::OutputCol([180, '*']);
    FrameHTML::OutputSrc(['menu' => 'menu.php', 'body' => $url . URL::EXT]);
  }

  //サイドメニュー出力
  public static function OutputMenu($title, $path = '') {
    HTML::OutputHeader(self::GenerateTitle($title, InfoMessage::TITLE_MENU), 'info/menu', true);
    DivHTML::Output($title, [HTML::CSS => 'menu']);
    self::Load('menu', $path);
    HTML::OutputFooter();
  }

  //新役職情報移動出力
  public static function OutputMoveRole() {
    $title = sprintf('%s %s', ServerConfig::TITLE, Text::QuoteBracket(InfoMessage::TITLE_ROLE));
    $str   = '※「新役職について」は移動しました → ';

    HTML::OutputHeader($title, 'index', true);
    LinkHTML::Output('../', '← TOP', true);
    HTML::OutputP($str . LinkHTML::Generate('new_role/', InfoMessage::TITLE_ROLE));
    HTML::OutputFooter();
  }

  //新役職情報出力
  public static function OutputRole($title, $name) {
    self::OutputRoleHeader($title);
    self::Load($name, 'new_role/');
    HTML::OutputFooter();
  }

  //新役職情報ヘッダ出力
  public static function OutputRoleHeader($title) {
    HTML::OutputHeader(self::GenerateTitle(InfoMessage::TITLE_ROLE, $title), 'new_role', true);
    if ($title == InfoMessage::TITLE_ROLE_SUMMARY) {
      return;
    }

    Text::Printf(self::GetRoleHeader(),
      $title, InfoMessage::TITLE_TOP, InfoMessage::TITLE_MENU, InfoMessage::TITLE_ROLE_SUMMARY
    );
  }

  //オプション出力
  public static function OutputOption($option, $name, $version) {
    Text::Printf(self::GetOption(),
      $option, $name, GameOptionConfig::${$option.'_list'}[$name], $version
    );
  }

  //オプションリスト出力
  public static function OutputOptionList($option, array $list) {
    foreach ($list as $name) {
      LinkHTML::Output('#' . $option . '_' . $name, GameOptionConfig::${$option.'_list'}[$name]);
    }
  }

  //履歴ページ出力
  public static function OutputHistory($title, $css, $name) {
    InfoHTML::OutputHeader($title, 1, $css);
    self::Load($name, 'history/');
    HTML::OutputFooter();
  }

  //開発ページ出力
  public static function OutputDevelop($title, $css, $name, $version = null, $url = null) {
    self::OutputHeader($title, 1, $css);
    if (isset($version)) {
      HTML::OutputP(LinkHTML::Generate($url, InfoMessage::TITLE_LATEST));
    }
    self::Load(Text::AddFooter($name, $version), 'develop/');
    HTML::OutputFooter();
  }

  //開発履歴出力
  public static function OutputDevelopHistory($version = null, $prefix = null) {
    $str   = isset($prefix) ? $prefix . $version : $version;
    $title = Text::AddFooter(InfoMessage::TITLE_DEVELOP_HISTORY, $str, ' / ');
    self::OutputDevelop($title, 'develop_history', 'history', $version, 'history.php');
  }

  //デバッグ情報出力
  public static function OutputDevelopDebug($version = null) {
    $title = Text::AddFooter(InfoMessage::TITLE_DEVELOP_DEBUG, $version, ' / ');
    self::OutputDevelop($title, 'debug', 'debug', $version, 'debug.php');
  }

  //カテゴリ別ページ内リンク出力
  public static function OutputCategory(array $list) {
    foreach ($list as $name) {
      LinkHTML::Output('#' . $name, OptionManager::GenerateCaption($name));
    }
  }

  //配役テーブル出力
  public static function OutputCast($min = 0, $max = null) {
    //設定されている役職名を取得
    $stack = [];
    foreach (CastConfig::$role_list as $key => $value) {
      if ($key < $min) {
	continue;
      }

      ArrayFilter::AddMerge($stack, array_keys($value));
      if ($key == $max) {
	break;
      }
    }
    $role_list = RoleDataManager::Sort(array_unique($stack)); //表示順を決定

    $header = TableHTML::GenerateTrHeader() . TableHTML::GenerateTh(InfoMessage::POPULATION);
    foreach ($role_list as $role) {
      $header .= RoleDataHTML::GenerateMain($role, 'th');
    }
    $header .= TableHTML::GenerateTrFooter();
    Text::Output(Text::LineFeed(TableHTML::GenerateHeader('member', false)) . $header);

    //人数毎の配役を表示
    foreach (CastConfig::$role_list as $key => $value) {
      if ($key < $min) {
	continue;
      }

      $str = TableHTML::GenerateTd(HTML::GenerateTag('b', $key));
      foreach ($role_list as $role) {
	$str .= TableHTML::GenerateTd(ArrayFilter::GetInt($value, $role));
      }
      TableHTML::OutputTr($str);
      if ($key == $max) {
	break;
      }

      if ($key % 20 == 0) {
	Text::Output($header);
      }
    }
    TableHTML::OutputFooter(false);
  }

  //他のサーバの部屋画面ロード用データを出力
  public static function OutputSharedRoomList($top = false) {
    if (true === $top) {
      if (TopPageConfig::DISABLE_SHARED_SERVER) {
	return false;
      }

      $stack   = TopPageConfig::$server_list;
      $arg_url = 'index';
    } else {
      if (SharedServerConfig::DISABLE) {
	return false;
      }

      $stack   = SharedServerConfig::$server_list;
      $arg_url = 'shared_room';
    }

    $str   = JavaScriptHTML::Load('shared_room');
    $count = 0;
    foreach ($stack as $server => $array) {
      $count++;
      extract($array);
      if (true === $disable) {
	continue;
      }

      $str .= Text::Format(self::GetSharedRoom(),
	$count, JavaScriptHTML::GenerateHeader(), $count, $count, $arg_url,
	JavaScriptHTML::GenerateFooter()
      );
    }
    echo $str;
  }

  //他のサーバの部屋画面を出力
  public static function OutputSharedRoom($id, $top = false) {
    if (true === $top) {
      if (TopPageConfig::DISABLE_SHARED_SERVER) {
	return false;
      }

      $stack = TopPageConfig::$server_list;
    } else {
      if (SharedServerConfig::DISABLE) {
	return false;
      }

      $stack = SharedServerConfig::$server_list;
    }

    $count = 0;
    foreach ($stack as $server => $array) {
      if (++$count == $id) {
	break;
      }
    }
    extract($array);
    if (true === $disable) {
      return false;
    }

    $title = sprintf('%s (<a href="%s">%s</a>)', InfoMessage::GAME_LIST, $url, $name);

    if (! ExternalLinkBuilder::IsConnect($url)) { //サーバ通信状態チェック
      ExternalLinkBuilder::OutputTimeOut($title, $url);
      return false;
    }

    //部屋情報を取得
    $data = @file_get_contents($url.'room_manager.php');
    if ($data == '') {
      return false;
    }

    $data = Encoder::BOM(Encoder::Convert($data, $encode));
    if ($separator != '') {
      $split_list = mb_split($separator, $data);
      $data = array_pop($split_list);
    }
    if ($footer != '') {
      $position = mb_strrpos($data, $footer);
      if (false === $position) {
	return false;
      }
      $data = Text::Shrink($data, $position + Text::Count($footer));
    }
    if ($data == '') {
      return false;
    }

    $replace_list = ['href="' => 'href="' . $url, 'src="'  => 'src="' . $url];
    $data = strtr($data, $replace_list);
    ExternalLinkBuilder::Output($title, $data);
  }

  //サブタイトル付タイトル生成
  private static function GenerateTitle($main, $sub) {
    return $main . ' - ' . Text::QuoteBracket($sub);
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<h1>%s</h1>
<p>
<a target="_top" href="%s">&lt;= TOP</a>
<a target="_top" href="%s">← %s</a>
</p>
EOF;
  }

  //オプションタグ
  private static function GetOption() {
    return '<h3 id="%s_%s">%s [%s]</h3>';
  }

  //役職情報ヘッダタグ
  private static function GetRoleHeader() {
    return <<<EOF
<h1>%s</h1>
<p>
<a target="_top" href="../">&lt;= %s</a>
<a target="_top" href="./">&lt;- %s</a>
<a href="summary.php">← %s</a>
</p>
EOF;
  }

  //関連サーバロード用タグ
  private static function GetSharedRoom() {
    return <<<EOF
<div id="server%d"></div>
%soutput_shared_room(%d, "server%d", "%s");
%s
EOF;
  }
}
