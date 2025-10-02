<?php
//-- 観戦画面出力クラス --//
class GameView {
  //実行
  static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    DB::Connect(RQ::Get()->db_no);

    DB::LoadRoom(); //村情報
    DB::$ROOM->SetFlag('view');
    DB::$ROOM->system_time = Time::Get();

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsFinished()) { //勝敗結果表示
      Loader::LoadFile('winner_message');
    } else { //ゲームオプション表示
      Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
      if (DB::$ROOM->IsBeforeGame()) RQ::Set('retrieve_type', DB::$ROOM->scene);
    }

    DB::LoadUser(); //ユーザ情報
    DB::LoadViewer();
  }

  //出力
  private static function Output() {
    self::OutputHeader();
    self::OutputLink();
    self::OutputLoginForm();

    if (! DB::$ROOM->IsFinished()) RoomOption::Output();
    self::OutputTimeTable();

    GameHTML::OutputPlayer();
    if (DB::$ROOM->IsFinished()) Winner::Output();
    if (DB::$ROOM->IsPlaying())  GameHTML::OutputRevote();

    self::OutputTalk();
    GameHTML::OutputLastWords();
    GameHTML::OutputDead();
    GameHTML::OutputVote();
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . GameViewMessage::TITLE, 'game_view');

    echo DB::$ROOM->GenerateCSS(); //シーン別 CSS をロード
    if (GameConfig::AUTO_RELOAD && RQ::Get()->auto_reload > 0) { //自動更新
      GameHTML::OutputAutoReloadHeader();
    }

    if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsRealTime()) {
      GameTime::OutputTimer(GameTime::GetRealPass($left_time));
      $on_load = 'output_realtime();';
    } else {
      $on_load = null;
    }
    HTML::OutputBodyHeader(null, $on_load);
  }

  //リンク出力
  private static function OutputLink() {
    //タイトル
    $str  = '<table id="game_top" class="login"><tr>' . Text::LF;
    $str .= DB::$ROOM->GenerateTitleTag();
    $str .= '<td class="login-link">' . Text::LF;

    //更新
    $url  = sprintf('<a href="game_view.php?room_no=%d', RQ::Get()->room_no);
    $str .= $url;
    if (RQ::Get()->auto_reload > 0) $str .= sprintf('&auto_reload=%d', RQ::Get()->auto_reload);
    $str .= sprintf('">%s</a>' . Text::LF, GameViewMessage::RELOAD);
    if (GameConfig::AUTO_RELOAD) $str .= GameHTML::GenerateAutoReloadLink($url); //自動更新

    //移動用
    $str .= sprintf('%s" target="_blank">%s</a>' . Text::LF, $url, GameViewMessage::BLANK);
    $str .= sprintf('<a href="./">%s</a>', GameViewMessage::BACK);
    if (DB::$ROOM->IsFinished()) $str .= GameHTML::GenerateLogLink();

    Text::Output($str . '</td></tr></table>');
  }

  //ログインフォーム出力
  private static function OutputLoginForm() {
    $format = <<<EOF
<table class="login"><tr>
<td><form method="post" action="login.php?room_no=%d">
<label for="uname">%s</label><input type="text" id="uname" name="uname" size="20" value="">
%s<label for="login_password">%s</label><input type="password" class="login-password" id="login_password" name="password" size="20" value="">
<input type="hidden" name="login_manually" value="on">
<input type="submit" value="%s">
</form></td>
EOF;

    if (GameConfig::TRIP) { //トリップ対応
      $trip_format = <<<EOF
<label for="trip">%s</label><input type="text" id="trip" name="trip" size="15" maxlength="15" value="">
EOF;
      $trip = sprintf($trip_format . Text::LF, Message::TRIP_KEY);
    } else {
      $trip = '';
    }

    $str = sprintf($format . Text::LF, DB::$ROOM->id, GameViewMessage::UNAME, $trip,
		   GameViewMessage::PASSWORD, GameViewMessage::SUBMIT);

    if (DB::$ROOM->IsBeforeGame()) { //登録画面リンク
      $format = <<<EOF
<td class="login-link">
<a href="user_manager.php?room_no=%d"><span>%s</span></a>
</td>
EOF;
      $str .= sprintf($format . Text::LF, DB::$ROOM->id, GameViewMessage::ENTRY);
    }

    Text::Output($str . '</tr></table>');
  }

  //タイムテーブル出力
  private static function OutputTimeTable() {
    GameHTML::OutputTimeTable();
    if (DB::$ROOM->IsPlaying()) {
      GameHTML::OutputTimePass($left_time);
      Text::Output('</tr></table>');

      if (DB::$ROOM->IsEvent('wait_morning')) {
	GameHTML::OutputVoteAnnounce(GameMessage::WAIT_MORNING);
      }
      elseif ($left_time == 0) {
	GameHTML::OutputVoteAnnounce();
      }
    }
    else {
      Text::Output('</tr></table>');
    }
  }

  //会話出力
  private static function OutputTalk() {
    if (DocumentCache::Enable('talk_view')) {
      DocumentCache::Load('game_view/talk', CacheConfig::TALK_VIEW_EXPIRE);
      $filter = DocumentCache::GetTalk();
      DocumentCache::Save($filter, true);
      DocumentCache::Output('talk_view');
    } else {
      $filter = Talk::Get();
    }
    $filter->Output();
  }
}
