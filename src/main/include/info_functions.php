<?php
//-- Info 情報生成クラス --//
class Info {
  //村の最大人数設定出力
  static function OutputMaxUser() {
    $format = '%s のどれかを村に登録できる村人の最大人数として設定することができます。<br>' .
      "ただしゲームを開始するには最低 %s の村人が必要です。";
    $list = sprintf('[ %s人 ]', implode('人・', RoomConfig::$max_user_list));
    $min  = sprintf('[ %d人 ]', min(array_keys(CastConfig::$role_list)));
    printf($format, $list, $min);
  }

  //身代わり君がなれない役職のリスト出力
  static function OutputDisableDummyBoyRole() {
    $stack = array();
    foreach (array_merge(array('wolf', 'fox'), CastConfig::$disable_dummy_boy_role_list) as $role) {
      $stack[] = RoleData::$main_role_list[$role];
    }
    echo implode($stack, '・');
  }

  //リアルタイム制のアイコン出力
  static function OutputRealTime() {
    $format = 'リアルタイム制　昼：%d分　夜： %d分';
    $str = sprintf($format, TimeConfig::DEFAULT_DAY,  TimeConfig::DEFAULT_NIGHT);
    echo Image::Room()->Generate('real_time', $str);
  }

  //追加役職の人数と説明ページリンク出力
  static function OutputAddRole($role, $add = false) {
    $format = '村の人口が%d人以上になったら%s%sします';
    $str = RoleData::GenerateRoleLink($role);
    printf($format, CastConfig::$$role, $str, $add ? 'を追加' : 'が登場');
  }

  //村人置換系オプションのサーバ設定出力
  static function OutputReplaceRole($option) {
    $format = 'は管理人がカスタムすることを前提にしたオプションです<br>現在の初期設定は全員' .
      '%sになります';
    printf($format, RoleData::GenerateRoleLink(CastConfig::$replace_role_list[$option]));
  }
}

//-- 日時関連 (Info 拡張) --//
class InfoTime {
  public static $spend_day;      //非リアルタイム制の発言で消費される時間 (昼)
  public static $spend_night;    //非リアルタイム制の発言で消費される時間 (夜)
  public static $silence_day;    //非リアルタイム制の沈黙で経過する時間 (昼)
  public static $silence_night;  //非リアルタイム制の沈黙で経過する時間 (夜)
  public static $silence;        //非リアルタイム制の沈黙になるまでの時間
  public static $sudden_death;   //制限時間を消費後に突然死するまでの時間
  public static $alert;          //警告音開始
  public static $alert_distance; //警告音の間隔
  public static $die_room;       //自動廃村になるまでの時間
  public static $establish_wait; //次の村を立てられるまでの待ち時間

  function __construct() {
    $day_seconds   = floor(12 * 60 * 60 / TimeConfig::DAY);
    $night_seconds = floor( 6 * 60 * 60 / TimeConfig::NIGHT);

    self::$spend_day      = Time::Convert($day_seconds);
    self::$spend_night    = Time::Convert($night_seconds);
    self::$silence_day    = Time::Convert(TimeConfig::SILENCE_PASS * $day_seconds);
    self::$silence_night  = Time::Convert(TimeConfig::SILENCE_PASS * $night_seconds);
    self::$silence        = Time::Convert(TimeConfig::SILENCE);
    self::$sudden_death   = Time::Convert(TimeConfig::SUDDEN_DEATH);
    self::$alert          = Time::Convert(TimeConfig::ALERT);
    self::$alert_distance = Time::Convert(TimeConfig::ALERT_DISTANCE);
    self::$die_room       = Time::Convert(RoomConfig::DIE_ROOM);
    self::$establish_wait = Time::Convert(RoomConfig::ESTABLISH_WAIT);
  }
}

//-- HTML 生成クラス (Info 拡張) --//
class InfoHTML {
  //HTML ヘッダ出力
  static function OutputHeader($title, $level = 0, $css = 'info') {
    $top  = str_repeat('../', $level + 1);
    $info = $level == 0 ? './' : str_repeat('../', $level);
    HTML::OutputHeader(sprintf('[%s]', $title), 'info/' . $css, true);
    echo <<<EOF
<h1>{$title}</h1>
<p>
<a target="_top" href="{$top}">&lt;= TOP</a>
<a target="_top" href="{$info}">← 情報一覧</a>
</p>

EOF;
  }

  //役職情報ページ HTML ヘッダ出力
  static function OutputRoleHeader($title) {
    HTML::OutputHeader(sprintf('新役職情報 - [%s]', $title), 'new_role', true);
    echo <<<EOF
<h1>{$title}</h1>
<p>
<a target="_top" href="../">&lt;= 情報一覧</a>
<a target="_top" href="./">&lt;- メニュー</a>
<a href="summary.php">← 一覧表</a>
</p>

EOF;
  }

  //カテゴリ別ページ内リンク出力
  static function OutputCategory(array $list) {
    foreach ($list as $name) {
      printf("<a href=\"#%s\">%s</a>\n", $name, OptionManager::GenerateCaption($name));
    }
  }

  //配役テーブル出力
  static function OutputCast($min = 0, $max = null) {
    //設定されている役職名を取得
    $stack = array();
    foreach (CastConfig::$role_list as $key => $value) {
      if ($key < $min) continue;
      $stack = array_merge($stack, array_keys($value));
      if ($key == $max) break;
    }
    $role_list = RoleData::SortRole(array_unique($stack)); //表示順を決定

    $header = '<table class="member">';
    $str = '<tr><th>人口</th>';
    foreach ($role_list as $role) $str .= RoleData::GenerateMainRoleTag($role, 'th');
    $str .= '</tr>'."\n";
    echo $header . $str;

    //人数毎の配役を表示
    foreach (CastConfig::$role_list as $key => $value) {
      if ($key < $min) continue;
      $tag = sprintf('<td><strong>%s</strong></td>', $key);
      foreach ($role_list as $role) {
	$tag .= sprintf('<td>%d</td>', isset($value[$role]) ? $value[$role] : 0);
      }
      printf("<tr>%s</tr>\n", $tag);
      if ($key == $max) break;
      if ($key % 20 == 0) echo $str;
    }
    echo '</table>';
  }

  //お祭り村の配役リスト出力
  static function OutputFestival() {
    $stack  = CastConfig::$festival_role_list;
    $format = '%' . strlen(max(array_keys($stack))) . 's人：';
    $str    = '<pre>'."\n";
    ksort($stack); //人数順に並び替え
    foreach ($stack as $count => $list) {
      $order_stack = array();
      foreach (RoleData::SortRole(array_keys($list)) as $role) { //役職順に並び替え
	$order_stack[] = RoleData::$main_role_list[$role] . $list[$role];
      }
      $str .= sprintf($format, $count) . implode('　', $order_stack) . "\n";
    }
    echo $str . '</pre>'."\n";
  }

  //オプションリスト表示 (闇鍋モード用)
  static function OutputItem($option, $name, $version) {
    $format = "<h3 id=\"%s_%s\">%s [%s～]</h3>\n";
    printf($format, $option, $name, GameOptionConfig::${$option.'_list'}[$name], $version);
  }

  //個別オプション表示 (闇鍋モード用)
  static function OutputItemList($option, $list) {
    $format = "<a href=\"#%s_%s\">%s</a>\n";
    foreach ($list as $name) {
      printf($format, $option, $name, GameOptionConfig::${$option.'_list'}[$name]);
    }
  }

  //他のサーバの部屋画面ロード用データを出力
  static function OutputSharedRoomList() {
    if (SharedServerConfig::DISABLE) return false;

    $str = HTML::LoadJavaScript('shared_room');
    $count = 0;
    foreach (SharedServerConfig::$server_list as $server => $array) {
      $count++;
      extract($array);
      if ($disable) continue;

      $str .= <<<EOF
<div id="server{$count}"></div>
<script language="javascript"><!--
output_shared_room({$count}, "server{$count}");
--></script>

EOF;
    }
    echo $str;
  }

  //他のサーバの部屋画面を出力
  static function OutputSharedRoom($id) {
    if (SharedServerConfig::DISABLE) return false;

    $count = 0;
    foreach (SharedServerConfig::$server_list as $server => $array) {
      if (++$count == $id) break;
    }
    //Text::p($server, $id);
    extract($array);
    if ($disable) return false;

    if (! ExternalLinkBuilder::CheckConnection($url)) { //サーバ通信状態チェック
      $data = ExternalLinkBuilder::GenerateTimeOut($url);
      echo ExternalLinkBuilder::GenerateSharedServerRoom($name, $url, $data);
      return false;
    }

    //部屋情報を取得
    if (($data = @file_get_contents($url.'room_manager.php')) == '') return false;
    if ($encode != '' && $encode != ServerConfig::ENCODE) {
      $data = mb_convert_encoding($data, ServerConfig::ENCODE, $encode);
    }

    if (ord($data{0}) == '0xef' && ord($data{1}) == '0xbb' && ord($data{2}) == '0xbf') { //BOM 消去
      $data = substr($data, 3);
    }
    if ($separator != '') {
      $split_list = mb_split($separator, $data);
      $data = array_pop($split_list);
    }
    if ($footer != '') {
      if (($position = mb_strrpos($data, $footer)) === false) return false;
      $data = mb_substr($data, 0, $position + mb_strlen($footer));
    }
    if ($data == '') return false;

    $replace_list = array('href="' => 'href="' . $url, 'src="'  => 'src="' . $url);
    $data = strtr($data, $replace_list);
    echo ExternalLinkBuilder::GenerateSharedServerRoom($name, $url, $data);
  }

  //謝辞・素材情報出力
  static function OutputCopyright() {
    $stack = CopyrightConfig::$list;
    foreach (CopyrightConfig::$add_list as $class => $list) {
      $stack[$class] = array_key_exists($class, $stack) ?
	array_merge($stack[$class], $list) : $list;
    }

    foreach ($stack as $class => $list) {
      $str = '<h2>' . $class . "</h2>\n<ul>\n";
      foreach ($list as $name => $url) {
	$str .= '<li><a href="' . $url . '">' . $name . "</a></li>\n";
      }
      echo $str . "</ul>\n";
    }

    $str = <<<EOF
<h2>パッケージ情報</h2>
<ul>
<li>PHP Ver. %s</li>
<li>%s %s (Rev. %d)</li>
<li>LastUpdate: %s</li>
</ul>%s
EOF;
    printf($str, PHP_VERSION, ScriptInfo::PACKAGE, ScriptInfo::VERSION, ScriptInfo::REVISION,
	   ScriptInfo::LAST_UPDATE, "\n");
  }
}
