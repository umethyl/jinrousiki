<?php
//-- HTML 生成クラス (LogList 拡張) --//
final class LogListHTML {
  //過去ログ一覧生成
  public static function Generate($page) {
    //-- 村数取得 --//
    $room_count = self::LoadRoomCount();

    //-- キャッシュ有効判定 --//
    $cache_flag = false;
    if (JinrouCacheManager::Enable(JinrouCacheManager::LOG_LIST)) {
      $cache_flag = self::EnableCache();
      if (true === $cache_flag) {
	$str = JinrouCacheManager::Get(JinrouCacheManager::LOG_LIST);
	if (true === isset($str)) {
	  return $str;
	}
      }
    }

    //-- ページリンクデータ生成 --//
    if (null !== RQ::Get(RequestDataLogRoom::REVERSE_LIST)) {
      $is_reverse = Switcher::IsOn(RQ::Get(RequestDataLogRoom::REVERSE_LIST));
    } else {
      $is_reverse = OldLogConfig::REVERSE;
    }
    $str = self::GenerateHeader(self::GetPageLinkBuilder($room_count, $is_reverse));

    //-- 個別村情報出力 --//
    //全部表示の場合、一ページで全部表示する。それ以外は設定した数毎に表示
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
	$base_url = URL::GetRoom('old_log');
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
	  $log_link  = self::GenerateWatchLogLink($base_url,  '(', '', ' )');
	} else {
	  $log_link  = LinkHTML::GenerateLog($base_url, true, '(', '', ' )');

	  $url       = $base_url . URL::AddSwitch(RequestDataLogRoom::ADD_ROLE);
	  $header    = Text::LF . OldLogMessage::ADD_ROLE . ' (';
	  $log_link .= LinkHTML::GenerateLog($url, false, $header, $vanish, ' )');
	}
      }

      $list = [
	'game_option' => DB::$ROOM->game_option,
	'option_role' => DB::$ROOM->option_role,
	'max_user'    => DB::$ROOM->max_user
      ];
      RoomOptionLoader::Load($list);
      RoomOptionLoader::SetStack();

      $str .= self::GenerateRoom($base_url, $view_url, $vanish, $login, $log_link);
    }

    $str .= TableHTML::TbodyFooter();
    $str .= TableHTML::Footer();
    $str .= DivHTML::Footer(true);
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
    $str .= DivHTML::Header(line: true);
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
    $str  = TableHTML::Header(line: true);
    $str .= TableHTML::Caption($builder->Generate());
    $str .= TableHTML::TheadHeader();
    $str .= TableHTML::TrHeader();
    $str .= TableHTML::Th(OldLogMessage::NUMBER);
    $str .= TableHTML::Th(OldLogMessage::NAME);
    $str .= TableHTML::Th(OldLogMessage::COUNT);
    $str .= TableHTML::Th(OldLogMessage::DATE);
    $str .= TableHTML::Th(OldLogMessage::WIN);
    $str .= TableHTML::TrFooter(true);
    $str .= TableHTML::TheadFooter();
    $str .= TableHTML::TbodyHeader();

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

  //個別村情報生成
  private static function GenerateRoom($base_url, $view_url, $vanish, $login, $log_link) {
    $str  = self::GenerateRoomUpper($view_url, $base_url, $vanish);
    $str .= self::GenerateRoomMiddle();
    $str .= self::GenerateRoomLower($login, $log_link, $vanish);

    return $str;
  }

  //個別村情報生成/上段
  private static function GenerateRoomUpper($view_url, $base_url, $vanish) {
    $str  = TableHTML::TrHeader(line: true);
    $str .= self::GenerateRoomHeader($view_url);
    $str .= self::GenerateRoomTitle($base_url, $vanish);
    $str .= self::GenerateRoomMaxUser();
    $str .= TableHTML::Td(DB::$ROOM->date, [HTML::CSS => 'upper'], true);
    $str .= self::GenerateRoomSide();
    $str .= TableHTML::TrFooter();

    return $str;
  }

  //個別村情報生成/上段/ヘッダ
  private static function GenerateRoomHeader(string $url) {
    $link = LinkHTML::Generate(URL::GetRoom('game_view') . $url, DB::$ROOM->id);

    return TableHTML::Td($link, [HTML::CSS => 'number', TableHTML::ATTR_ROW => 3], true);
  }

  //個別村情報生成/上段/村名
  private static function GenerateRoomTitle(string $url, string $vanish) {
    $link = LinkHTML::Generate($url, DB::$ROOM->GenerateName());

    return TableHTML::Td($link, [HTML::CSS => 'title' . $vanish], true);
  }

  //個別村情報生成/上段/最大人数
  private static function GenerateRoomMaxUser() {
    $count = DB::$ROOM->user_count;
    $image = ImageManager::Room()->GenerateMaxUser(DB::$ROOM->max_user);;

    return TableHTML::Td($count . ' ' . $image, [HTML::CSS => 'upper'], true);
  }

  //個別村情報生成/上段/勝利
  private static function GenerateRoomSide() {
    if (RQ::Fetch()->watch) {
      $winner = '-';
    } else {
      $winner = ImageManager::Winner()->Generate(DB::$ROOM->winner);
    }

    return TableHTML::Td($winner, [HTML::CSS => 'side'], true);
  }

  //個別村情報生成/中段
  private static function GenerateRoomMiddle() {
    $str  = TableHTML::TrHeader([HTML::CSS => 'list middle'], true);
    $str .= TableHTML::Td(DB::$ROOM->GenerateComment(), [HTML::CSS => 'comment side'], true);
    $str .= self::GenerateRoomTime();
    $str .= TableHTML::TrFooter();

    return $str;
  }

  //個別村情報生成/中段/日時
  private static function GenerateRoomTime() {
    if (DB::$ROOM->establish_datetime == '') {
      $str = '';
    } else {
      $str = Time::ConvertTimeStamp(DB::$ROOM->establish_datetime);
    }

    return TableHTML::Td($str, [HTML::CSS => 'time comment', TableHTML::ATTR_COL => 3], true);
  }

  //個別村情報生成/下段
  private static function GenerateRoomLower($login, $log_link, $vanish) {
    $link  = Text::LineFeed(Text::LF . $login . $log_link);
    $class = 'comment' . $vanish;

    $str  = TableHTML::TrHeader([HTML::CSS => 'lower list'], true);
    $str .= TableHTML::Td($link, [HTML::CSS => $class], true);
    $str .= TableHTML::Td(RoomOptionLoader::GenerateImage(), [TableHTML::ATTR_COL => 3], true);
    $str .= TableHTML::TrFooter();

    return $str;
  }
}
