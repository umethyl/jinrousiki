<?php
//-- 観戦画面出力クラス --//
class GameView {
  //出力
  static function Output() {
    //-- データ収集 --//
    DB::Connect();
    DB::$ROOM = new Room(RQ::Get()); //村情報を取得
    DB::$ROOM->view_mode   = true;
    DB::$ROOM->system_time = Time::Get();

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsFinished()) {
      Loader::LoadFile('winner_message');
    } else {
      Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
    }

    //ユーザ情報を取得
    if (DB::$ROOM->IsBeforeGame()) RQ::Set('retrive_type', DB::$ROOM->scene);
    DB::$USER = new UserData(RQ::Get());
    DB::$SELF = new User();

    //-- 出力 --//
    self::OutputHeader();
    self::OutputLink();
    self::OutputLoginForm();

    if (! DB::$ROOM->IsFinished()) RoomOption::Output(); //ゲームオプション
    self::OutputTimeTable();

    GameHTML::OutputPlayer();
    if (DB::$ROOM->IsFinished()) Winner::Output();
    if (DB::$ROOM->IsPlaying())  GameHTML::OutputRevote();
    if (DocumentCache::Enable('talk_view')) {
      DocumentCache::Load('game_view/talk', CacheConfig::TALK_VIEW_EXPIRE);
      $filter = DocumentCache::GetTalk();
      DocumentCache::Save($filter, true);
      DocumentCache::Output('talk_view');
    } else {
      $filter = Talk::Get();
    }
    $filter->Output();
    GameHTML::OutputLastWords();
    GameHTML::OutputDead();
    GameHTML::OutputVote();
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    $str = HTML::GenerateHeader(ServerConfig::TITLE . '[観戦]', 'game_view');
    if (GameConfig::AUTO_RELOAD && RQ::Get()->auto_reload > 0) { //自動更新
      $str .= sprintf('<meta http-equiv="Refresh" content="%d">', RQ::Get()->auto_reload);
      $str .= Text::LF;
    }
    $str .= DB::$ROOM->GenerateCSS(); //シーンに合わせた文字色と背景色 CSS をロード
    $str .= '</head>' . Text::LF;

    if (DB::$ROOM->IsPlaying() && DB::$ROOM->IsRealTime()) {
      $str .= '<body onLoad="output_realtime();">';
    } else {
      $str .= '<body>';
    }

    Text::Output($str);
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
    $str .= '">[更新]</a>' . Text::LF;
    if (GameConfig::AUTO_RELOAD) $str .= GameHTML::GenerateAutoReloadLink($url); //自動更新設定

    //別ページ
    $str .= sprintf('%s" target="_blank">別ページ</a>', $url);
    $str .= Text::LF;
    $str .= '<a href="./">[戻る]</a>';
    if (DB::$ROOM->IsFinished()) $str .= GameHTML::GenerateLogLink(); //ログ

    Text::Output($str . '</td></tr></table>');
  }

  //ログインフォーム出力
  private static function OutputLoginForm() {
    $format = <<<EOF
<table class="login"><tr>
<td><form method="post" action="login.php?room_no=%d">
<label for="uname">ユーザ名</label><input type="text" id="uname" name="uname" size="20" value="">%s
<label for="login_password">パスワード</label><input type="password" class="login-password" id="login_password" name="password" size="20" value="">
<input type="hidden" name="login_manually" value="on">
<input type="submit" value="ログイン">
</form></td>

EOF;
    if (GameConfig::TRIP) { //トリップ対応
      $trip = <<<EOF

<label for="trip">＃</label><input type="text" id="trip" name="trip" size="15" maxlength="15" value="">
EOF;
    } else {
      $trip = '';
    }
    $str = sprintf($format, DB::$ROOM->id, $trip);

    if (DB::$ROOM->IsBeforeGame()) { //登録画面リンク
      $user_entry = <<<EOF
<td class="login-link">
<a href="user_manager.php?room_no=%d"><span>[住民登録]</span></a>
</td>

EOF;
      $str .= sprintf($user_entry, DB::$ROOM->id);
    }

    Text::Output($str . '</tr></table>');
  }

  //タイムテーブル出力
  private static function OutputTimeTable() {
    $str = GameHTML::GenerateTimeTable(); //経過日数と生存人数

    switch (DB::$ROOM->scene) {
    case 'day':
      $time_message = '日没まで ';
      break;

    case 'night':
      $time_message = '夜明けまで ';
      break;
    }

    if (DB::$ROOM->IsPlaying()) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制
	$str .= GameTime::GenerateTimer(GameTime::GetRealPass($left_time));
	$str .= '<td class="real-time"><form name="realtime_form">' . Text::LF;
	$str .= '<input type="text" name="output_realtime" size="60" readonly>' . Text::LF;
	$str .= '</form></td>' . Text::LF;
      }
      else { //会話で時間経過制
	$left_talk_time = GameTime::GetTalkPass($left_time);
	if ($left_talk_time) {
	  $format = '<td>%s%s</td>' . Text::LF;
	  $str .= sprintf($format, $time_message, $left_talk_time);
	}
      }
    }
    $str .= '</tr></table>' . Text::LF;

    if (DB::$ROOM->IsPlaying()) {
      $format = '<div class="system-vote">%s</div>' . Text::LF;
      if ($left_time == 0) {
	$str .= sprintf($format, $time_message . Message::$vote_announce);
      }
      elseif (DB::$ROOM->IsEvent('wait_morning')) {
	$str .= sprintf($format, Message::$wait_morning);
      }
    }

    echo $str;
  }
}
