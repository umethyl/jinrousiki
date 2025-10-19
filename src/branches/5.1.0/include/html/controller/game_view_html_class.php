<?php
//-- HTML 生成クラス (GameView 拡張) --//
final class GameViewHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    self::OutputLink();
    self::OutputLoginForm();

    if (false === DB::$ROOM->IsFinished()) {
      RoomOptionLoader::Output();
    }
    self::OutputTimeTable();

    GameHTML::OutputPlayer();
    if (DB::$ROOM->IsFinished()) {
      Winner::Output();
    }
    if (DB::$ROOM->IsPlaying()) {
      GameHTML::OutputRevote();
    }

    self::OutputTalk();
    GameHTML::OutputLastWords();
    GameHTML::OutputDead();
    GameHTML::OutputVote();
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . GameViewMessage::TITLE, 'game_view');

    DB::$ROOM->OutputCSS();
    GameHTML::OutputNoCacheHeader();
    if (GameConfig::AUTO_RELOAD && RQ::Get()->auto_reload > 0) { //自動更新
      GameHTML::OutputAutoReloadHeader();
    }

    if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsRealTime()) {
      GameHTML::OutputTimer(GameTime::GetRealPass($left_time));
      $on_load = 'output_realtime();';
    } else {
      $on_load = null;
    }
    HTML::OutputBodyHeader(null, $on_load);
  }

  //リンク出力
  private static function OutputLink() {
    $url = URL::GetRoom('game_view');
    if (DB::$ROOM->IsFinished()) {
      $link = GameHTML::GenerateLogLink();
    } elseif (DB::$ROOM->IsPlaying()) {
      $header = GameHTML::GenerateGameLogLinkListHeader();
      $link   = $header . GameHTML::GenerateGameLogLinkList(URL::GetRoom('game_log'));
    } else {
      $link = '';
    }

    Text::Printf(self::GetLink(),
      RoomHTML::GenerateTitle(),
      $url, RQ::Get()->ToURL(RequestDataGame::RELOAD, true), GameViewMessage::RELOAD,
      GameConfig::AUTO_RELOAD ? GameHTML::GenerateAutoReloadLink('<a href="' . $url) : '',
      $url, GameViewMessage::BLANK, GameViewMessage::BACK, $link
    );
  }

  //ログインフォーム出力
  private static function OutputLoginForm() {
    $trip = GameConfig::TRIP ? Text::Format(self::GetTripForm(), Message::TRIP_KEY) : '';
    if (DB::$ROOM->IsBeforeGame()) {
      $entry = Text::Format(self::GetEntry(),
        DB::$ROOM->IsClosing() ? GameMessage::CLOSING . ' ' : '',
        URL::GetRoom('user_manager'), GameViewMessage::ENTRY
      );
    } else {
      $entry = '';
    }

    Text::Printf(self::GetLoginForm(),
      URL::GetRoom('login'), GameViewMessage::UNAME, $trip, GameViewMessage::PASSWORD,
      GameViewMessage::SUBMIT, $entry
    );
  }

  //タイムテーブル出力
  private static function OutputTimeTable() {
    GameHTML::OutputTimeTable();
    if (DB::$ROOM->IsPlaying()) {
      GameHTML::OutputTimePass($left_time);
      TableHTML::OutputFooter();

      if (DB::$ROOM->IsEvent('wait_morning')) {
	GameHTML::OutputVoteAnnounce(GameMessage::WAIT_MORNING);
      } elseif ($left_time == 0) {
	GameHTML::OutputVoteAnnounce();
      }
    } else {
      TableHTML::OutputFooter();
    }
  }

  //会話出力
  private static function OutputTalk() {
    if (JinrouCacheManager::Enable(JinrouCacheManager::TALK_VIEW)) {
      $filter = JinrouCacheManager::Get(JinrouCacheManager::TALK_VIEW);
    } else {
      $filter = Talk::Fetch();
    }
    $filter->Output();
  }

  //リンクタグ
  private static function GetLink() {
    return <<<EOF
<table id="game_top" class="login"><tr>
%s<td class="login-link"><a href="%s%s">%s</a>
%s<a href="%s" target="_blank">%s</a>
<a href="./">%s</a>%s</td>
</tr></table>
EOF;
  }

  //ログインフォームタグ
  private static function GetLoginForm() {
    return <<<EOF
<table class="login"><tr>
<td><form method="post" action="%s">
<label for="uname">%s</label><input type="text" id="uname" name="uname" size="20" value="">
%s<label for="login_password">%s</label><input type="password" class="login-password" id="login_password" name="password" size="20" value="">
<input type="hidden" name="login_manually" value="on">
<input type="submit" value="%s">
</form></td>
%s</tr></table>
EOF;
  }

  //トリップフォームタグ
  private static function GetTripForm() {
    return <<<EOF
<label for="trip">%s</label><input type="text" id="trip" name="trip" size="15" maxlength="15" value="">
EOF;
  }

  //登録画面リンクタグ
  private static function GetEntry() {
    return <<<EOF
<td class="login-link"><span class="closing">%s</span><a href="%s"><span>%s</span></a></td>
EOF;
  }
}
