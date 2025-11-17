<?php
//-- HTML 生成クラス (LogList 拡張) --//
final class LogListHTML {
  //過去ログ一覧生成
  public static function Generate($page) {
    //村数の確認
    $room_count = self::LoadRoomCount();

    $cache_flag = false; //キャッシュ有効判定
    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG_LIST)) {
      $cache_flag = self::EnableCache();
      if (true === $cache_flag) {
	$str = JinrouCacheManager::Get(JinrouCacheManager::LOG_LIST);
	if (true === isset($str)) {
	  return $str;
	}
      }
    }

    //ページリンクデータの生成
    if (null !== RQ::Get(RequestDataLogRoom::REVERSE_LIST)) {
      $is_reverse = Switcher::IsOn(RQ::Get(RequestDataLogRoom::REVERSE_LIST));
    } else {
      $is_reverse = OldLogConfig::REVERSE;
    }
    $str = self::GenerateHeader(self::GetPageLinkBuilder($room_count, $is_reverse));

    //全部表示の場合、一ページで全部表示する。それ以外は設定した数毎に表示
    $format = self::GetList();
    $current_time = Time::Get();
    foreach (RoomLoaderDB::GetFinished($is_reverse) as $room_no) {
      DB::SetRoom(RoomLoaderDB::LoadFinished($room_no));

      $vanish = DateBorder::On(0) ? ' vanish' : ''; //廃村判定
      if (RQ::Fetch()->generate_index) {
	$base_url = RQ::Fetch()->prefix . DB::$ROOM->id . '.html';
	$view_url = '';
	$login    = '';
	$log_link = sprintf('(<a href="%s%dr.html">%s</a>)',
	  RQ::Fetch()->prefix, DB::$ROOM->id, Message::LOG_REVERSE
	);
      } else {
	$base_url = URL::GetRoom('old_log');;
	if (URL::ExistsDB()) {
	  $view_url  = RQ::Fetch()->ToURL(RequestDataGame::DB, true);
	  $base_url .= $view_url;
	} else {
	  $view_url  = '';
	}
	if (RQ::Fetch()->watch) {
	  $base_url .= URL::AddSwitch(RequestDataLogRoom::WATCH);
	}

	if ($current_time - strtotime(DB::$ROOM->finish_datetime ?? 0) > RoomConfig::KEEP_SESSION) {
	  $login = '';
	} else {
	  $login = Text::LineFeed(LinkHTML::Generate(URL::GetRoom('login'), OldLogMessage::LOGIN));
	}

	if (RQ::Fetch()->watch) {
	  $log_link  = self::GenerateWatchLogLink($base_url, '(', '', ' )');
	} else {
	  $log_link  = LinkHTML::GenerateLog($base_url, true, '(', '', ' )');

	  $url       = $base_url . URL::AddSwitch(RequestDataLogRoom::ADD_ROLE);
	  $header    = Text::LF . OldLogMessage::ADD_ROLE . ' (';
	  $log_link .= LinkHTML::GenerateLog($url, false, $header, $vanish, ' )');
	}
      }

      if (DB::$ROOM->establish_datetime == '') {
	$establish = '';
      } else {
	$establish = Time::ConvertTimeStamp(DB::$ROOM->establish_datetime);
      }

      $list = [
	'game_option' => DB::$ROOM->game_option,
	'option_role' => DB::$ROOM->option_role,
	'max_user'    => DB::$ROOM->max_user
      ];
      RoomOptionLoader::Load($list);
      RoomOptionLoader::SetStack();

      $str .= Text::Format($format,
	URL::GetRoom('game_view'), $view_url,
	DB::$ROOM->id, $vanish, $base_url, DB::$ROOM->GenerateName(),
	DB::$ROOM->user_count, ImageManager::Room()->GenerateMaxUser(DB::$ROOM->max_user),
	DB::$ROOM->date,
	RQ::Fetch()->watch ? '-' : ImageManager::Winner()->Generate(DB::$ROOM->winner),
	DB::$ROOM->GenerateComment(), $establish, $vanish,
	$login, $log_link, RoomOptionLoader::GenerateImage()
      );
    }

    $str .= Text::LineFeed(self::GetFooter());
    if (true === $cache_flag) {
      JinrouCacheManager::Store($str);
    }
    return $str;
  }

  //過去ログ一覧表示
  public static function Output($page) {
    echo self::Generate($page);
  }

  //村数取得
  private static function LoadRoomCount() {
    $room_count = RoomLoaderDB::CountFinished();
    if ($room_count < 1) {
      $title = ServerConfig::TITLE . OldLogMessage::TITLE;
      $back  = LinkHTML::Generate('./', Message::BACK);
      HTML::OutputResult($title, Text::Join(OldLogMessage::NO_LOG, $back));
    }
    return $room_count;
  }

  //キャッシュ有効判定
  private static function EnableCache() {
    foreach (RQ::Fetch() as $key => $value) { //何か値がセットされていたら無効
      switch ($key) {
      case 'page':
	if ($value != 1) {
	  return false;
	}
	break;

      default:
	if (false === empty($value)) {
	  return false;
	}
	break;
      }
    }
    return true;
  }

  //ヘッダ生成
  private static function GenerateHeader(PageLinkBuilder $builder) {
    $str  = HTML::GenerateHeader(ServerConfig::TITLE . OldLogMessage::TITLE, 'old_log_list', true);
    $str .= self::GenerateTitle();
    $str .= DivHTML::GenerateHeader() . Text::LF;
    $str .= self::GenerateTableHeader($builder);

    return $str;
  }

  //タイトル生成
  private static function GenerateTitle() {
    if (RQ::Fetch()->generate_index) {
      $back = LinkHTML::Generate('../', Message::BACK);
      $url  = '../';
    } else {
      $back = LinkHTML::Generate('./', Message::BACK);
      $url  = '';
    }
    $path  = $url . 'img/title/old_log.jpg';
    $title = ImageHTML::GenerateTitle(OldLogMessage::TITLE, OldLogMessage::TITLE);

    return HTML::GenerateP($back) . ImageHTML::Generate($path, $title, '') . Text::BRLF;
  }

  //テーブルヘッダ生成
  private static function GenerateTableHeader(PageLinkBuilder $builder) {
    $str  = TableHTML::GenerateHeader(null, false) . Text::LF;
    $str .= TableHTML::GenerateCaption($builder->Generate());
    $str .= TableHTML::GenerateTheadHeader();
    $str .= TableHTML::GenerateTrHeader();
    $str .= TableHTML::GenerateTh(OldLogMessage::NUMBER);
    $str .= TableHTML::GenerateTh(OldLogMessage::NAME);
    $str .= TableHTML::GenerateTh(OldLogMessage::COUNT);
    $str .= TableHTML::GenerateTh(OldLogMessage::DATE);
    $str .= TableHTML::GenerateTh(OldLogMessage::WIN);
    $str .= TableHTML::GenerateTrFooter() . Text::LF;
    $str .= TableHTML::GenerateTheadFooter();
    $str .= TableHTML::GenerateTbodydHeader();

    return $str;
  }

  //PageLinkBuilder オブジェクト生成
  private static function GetPageLinkBuilder(int $room_count, bool $is_reverse) {
    if (RQ::Fetch()->generate_index) {
      $max = RQ::Fetch()->max_room_no;
      if (is_int($max) && Number::InRange($max, 0, $room_count)) {
	$room_count = $max;
      }
      $builder = new PageLinkBuilder('index', RQ::Fetch()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->url = '<a href="index';
    } else {
      $builder = new PageLinkBuilder('old_log', RQ::Fetch()->page, $room_count);
      $builder->set_reverse = $is_reverse;
      $builder->AddOption(RequestDataLogRoom::REVERSE_LIST, Switcher::Get($is_reverse));
      $builder->AddOption(RequestDataLogRoom::WATCH, Switcher::Get(RQ::Fetch()->watch));
      foreach (self::GetPageLinkBuilderOption() as $option) {
	if (RQ::Get($option)) {
	  $builder->AddOption($option, RQ::Get($option));
	}
      }

      if (URL::ExistsDB()) {
	$builder->AddOption(RequestDataGame::DB, RQ::Get(RequestDataGame::DB));
      }
    }
    return $builder;
  }

  //PageLinkBuilder オブジェクトにセットするオプション取得
  private static function GetPageLinkBuilderOption() {
    return [
      RequestDataLogRoom::NAME,
      RequestDataLogRoom::ROOM_NAME,
      RequestDataLogRoom::WINNER,
      RequestDataLogRoom::ROLE,
      RequestDataLogRoom::GAME_TYPE
    ];
  }

  //一覧個別村情報タグ
  private static function GetList() {
    return <<<EOF
<tr>
<td class="number" rowspan="3"><a href="%s%s">%d</a></td>
<td class="title%s"><a href="%s">%s</a></td>
<td class="upper">%d %s</td>
<td class="upper">%d</td>
<td class="side">%s</td>
</tr>
<tr class="list middle">
<td class="comment side">%s</td>
<td class="time comment" colspan="3">%s</td>
</tr>
<tr class="lower list">
<td class="comment%s">
%s%s
</td>
<td colspan="3">%s</td>
</tr>
EOF;
  }

  //フッタタグ
  private static function GetFooter() {
    return <<<EOF
</tbody>
</table>
</div>
EOF;
  }
}
