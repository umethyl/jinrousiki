<?php
//-- HTML 生成クラス (GamePlay 拡張) --//
class GamePlayHTML {
  //更新ボタン出力
  public static function OutputReloadButton($url) {
    Text::Printf(self::GetReloadButton(), $url, GamePlayMessage::RELOAD);
  }

  //ログリンク出力
  public static function OutputLogLink($header, $scene, $caption, $date = null) {
    if (true === isset($date)) {
      $header .= URL::GetAddInt(RequestDataGameLog::DATE, $date);
      $str = $date . Text::Quote($caption);
    } else {
      $str = $caption;
    }
    $header .= URL::GetAddString(RequestDataGameLog::SCENE, $scene);
    Text::Printf($header . '">%s</a>', $str);
  }

  //ヘッダーリンク出力
  public static function OutputHeaderLink($url, $add_url, $type = null) {
    if (true === is_null($type)) {
      $type = $url;
    }
    Text::Printf(self::GetHeaderLink(), $url, $add_url, self::GetHeaderStr($type));
  }

  //ヘッダーリンク出力 (スイッチ)
  public static function OutputHeaderSwitchLink($url, $type) {
    if (RQ::Get()->$type) {
      $switch = Switcher::ON;
    } else {
      $url   .= URL::GetSwitch($type);
      $switch = Switcher::OFF;
    }
    Text::Printf(self::GetHeaderSwitchLink(), $url, $switch, self::GetHeaderStr($type));
  }

  //ヘッダーリンク出力 (リスト)
  public static function OutputHeaderListLink($url, $type) {
    if (RQ::Get()->$type) {
      $switch = Switcher::OFF;
    } else {
      $url   .= URL::GetSwitch($type);
      $switch = Switcher::ON;
    }
    GameHTML::OutputHeaderLink($url, self::GetHeaderStr($type . '_' . $switch));
  }

  //注意事項出力 (ヘッダ)
  public static function OutputHeaderCaution() {
    Text::Printf(self::GetHeaderCaution(),
      GamePlayMessage::BEFOREGAME_CAUTION, GamePlayMessage::BEFOREGAME_VOTE
    );
  }

  //時間設定出力
  public static function OutputTimeSetting() {
    echo TableHTML::GenerateTdHeader('real-time');
    if (DB::$ROOM->IsRealTime()) { //実時間の制限時間を取得
      printf(GamePlayMessage::REAL_TIME, DB::$ROOM->real_time->day, DB::$ROOM->real_time->night);
    }
    printf(GamePlayMessage::SUDDEN_DEATH, Time::Convert(TimeConfig::SUDDEN_DEATH));
    TableHTML::OutputTdFooter();
  }

  //発言数出力
  public static function OutputTalkCount() {
    Text::Printf(self::GetTalkCount(),
      GamePlayMessage::TALK_COUNT, Message::COLON,
      DB::$SELF->GetTalkCount(), DB::$ROOM->GetLimitTalk()
    );
  }

  //異議ありボタン出力
  public static function OutputObjection($url) {
    Text::Printf(self::GetObjection(),
      $url, RequestDataTalk::OBJECTION, Switcher::ON,
      Objection::GetImage(), GamePlayMessage::OBJECTION, Objection::Count()
    );
  }

  //未投票突然死メッセージ出力
  public static function OutputSuddenDeathAnnounce($str) {
    HTML::OutputDiv($str, 'system-sudden-death');
  }

  //役職能力出力
  public static function OutputAbility() {
    if (false === DB::$ROOM->IsPlaying()) { //スキップ判定
      return;
    }

    HTML::OutputDivHeader('ability-elements');
    RoleHTML::OutputAbility();
    HTML::OutputDivFooter();
  }

  //投票情報出力
  public static function OutputVote() {
    if (false === DB::$ROOM->IsPlaying()) { //スキップ判定
      return;
    }

    HTML::OutputDivHeader('vote-elements');
    RoleHTML::OutputVoteKill();
    if (DB::$ROOM->IsPlaying()) {
      GameHTML::OutputRevote();
    }
    if (DB::$ROOM->IsQuiz() && DB::$ROOM->IsDay() && DB::$SELF->IsDummyBoy()) {
      GamePlayHTML::OutputQuizVote();
    }
    HTML::OutputDivFooter();
  }

  //投票結果出力 (クイズ村 GM 専用)
  public static function OutputQuizVote() {
    $stack = [];
    foreach (SystemMessageDB::GetQuizVote() as $key => $list) {
      $stack[$list['target_no']][] = $key;
    }
    ksort($stack);

    $header = sprintf(self::GetQuizVoteHeader(),
      GamePlayMessage::QUIZ_VOTED_NAME, GamePlayMessage::QUIZ_VOTED_COUNT
    );
    $table_stack = array(TableHTML::GenerateHeader('vote-list'), $header);

    $format = self::GetQuizVote();
    foreach ($stack as $id => $list) {
      $user = DB::$USER->ByID($id);
      $table_stack[] = sprintf($format, $user->handle_name, count($list), GameMessage::VOTE_UNIT);
    }
    $table_stack[] = TableHTML::GenerateFooter(false);
    Text::Output(ArrayFilter::Concat($table_stack, Text::LF));
  }

  //自分の遺言出力
  public static function OutputSelfLastWords($str) {
    Text::Printf(self::GetSelfLastWords(), GamePlayMessage::LAST_WORDS, $str);
  }

  //シーン情報出力 (非同期用)
  public static function OutputSceneAsync() {
    Text::Printf(self::GetSceneAsync(), DB::$ROOM->date, DB::$ROOM->scene, GameTime::GetPass());
  }

  //URLヘッダタグ
  public static function GetURLHeader() {
    return '<a target="_top" href="game_frame.php';
  }

  //ログリンクヘッダタグ
  public static function GetLogLinkHeader() {
    return '<a target="_blank" href="game_log.php%s';
  }

  //ログリンクテーブル td タグ
  public static function GetLogLinkTableTd() {
    return TableHTML::GenerateTdHeader('view-option');
  }

  //更新ボタンタグ
  private static function GetReloadButton() {
    return <<<EOF
<form method="post" action="%s" name="reload_middle_frame" target="middle">
<input type="submit" value="%s">
</form>
EOF;
  }

  //ヘッダメッセージ取得
  private static function GetHeaderStr($type) {
    return GamePlayMessage::${'header_' . $type};
  }

  //ヘッダーリンクタグ
  private static function GetHeaderLink() {
    return '<a target="_blank" href="%s.php%s">%s</a>';
  }

  //ヘッダーリンクタグ (スイッチ)
  private static function GetHeaderSwitchLink() {
    return '[%s" class="option-%s">%s</a>]';
  }

  //注意事項タグ (ヘッダ)
  private static function GetHeaderCaution() {
    return <<<EOF
<div class="caution">
%s<span>%s</span>
</div>
EOF;
  }

  //発言数タグ
  private static function GetTalkCount() {
    return '<td>%s%s(%d/%d)</td>';
  }

  //「異議」ありボタンタグ
  private static function GetObjection() {
    return <<<EOF
<td class="objection"><form method="post" action="%s">
<input type="hidden" name="%s" value="%s">
<input type="image" name="objection_image" src="%s" alt="%s">
(%d)</form></td>
EOF;
  }

  //投票結果タグ (クイズ村専用 / ヘッダ)
  private static function GetQuizVoteHeader() {
    return '<tr class="vote-name"><td>%s</td><td>%s</td></tr>';
  }

  //投票結果タグ (クイズ村専用)
  private static function GetQuizVote() {
    return '<tr><td class="vote-name">%s</td><td class="vote-times">%d %s</td></tr>';
  }

  //自分の遺言タグ
  private static function GetSelfLastWords() {
    return <<<EOF
<table class="lastwords"><tr>
<td class="lastwords-title">%s</td>
<td class="lastwords-body">%s</td>
</tr></table>
EOF;
  }

  //シーン情報タグ (非同期用)
  public static function GetSceneAsync() {
    return <<<EOF
<ul>
  <li class="status" id="date">%d</li>
  <li class="status" id="scene">%s</li>
  <li class="status" id="end_date">%s</li>
</ul>
EOF;
  }
}
