<?php
//-- GamePlay 出力クラス --//
class GamePlay {
  const VOTE_DAY  = "<div class=\"self-vote\">投票 %d 回目：%s</div>\n%s";
  const SAY_LIMIT = '<font color="#FF0000">%s</font><br>';
  const NOT_VOTE  = '<font color="#FF0000">まだ投票していません</font>';
  const VOTE_DO   = '<span class="ability vote-do">%s</span><br>';
  private static $url_stack = array();

  //出力
  static function Output() {
    //-- データ収集 --//
    DB::Connect();
    Session::CertifyGamePlay(); //セッション認証

    DB::$ROOM = new Room(RQ::$get); //村情報をロード
    DB::$ROOM->dead_mode    = RQ::$get->dead_mode;
    DB::$ROOM->heaven_mode  = RQ::$get->heaven_mode;
    DB::$ROOM->system_time  = Time::Get();
    DB::$ROOM->sudden_death = 0; //突然死実行までの残り時間

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsFinished()) { //勝敗結果表示
      Loader::LoadFile('winner_message');
    }
    elseif (DB::$ROOM->IsBeforeGame()) { //ゲームオプション表示
      Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
      RQ::$get->retrive_type = DB::$ROOM->scene;
    }
    elseif (! DB::$ROOM->heaven_mode && DB::$ROOM->IsDay()) {
      RQ::$get->retrive_type = DB::$ROOM->scene;
    }

    DB::$USER = new UserDataSet(RQ::$get); //ユーザ情報をロード
    DB::$SELF = DB::$USER->BySession(); //自分の情報をロード

    //「異議」ありセット判定
    if (RQ::$get->set_objection && DB::$SELF->objection < GameConfig::OBJECTION &&
	(DB::$ROOM->IsBeforeGame() || (DB::$SELF->IsLive() && DB::$ROOM->IsDay()))) {
      DB::$SELF->objection++;
      DB::$SELF->Update('objection', DB::$SELF->objection);
      DB::$ROOM->Talk('', 'OBJECTION', DB::$SELF->uname);
    }

    if (RQ::$get->play_sound) { //音でお知らせ
      Loader::LoadFile('cookie_class');
      JinroCookie::Set(); //クッキー情報セット
    }

    //-- 発言処理 --//
    $say_limit = null;
    if (! DB::$ROOM->dead_mode || DB::$ROOM->heaven_mode) { //発言が送信されるのは bottom フレーム
      $say_limit = RoleTalk::Convert(RQ::$get->say); //発言置換処理

      if (RQ::$get->say == '') {
	self::CheckSilence(); //発言が空ならゲーム停滞のチェック (沈黙、突然死)
      }
      elseif (RQ::$get->last_words && (! DB::$SELF->IsDummyBoy() || DB::$ROOM->IsBeforeGame())) {
	self::SaveLastWords(RQ::$get->say); //遺言登録 (細かい判定条件は関数内で行う)
      }
      //死者 or 身代わり君 or 同一ゲームシーンなら書き込む
      elseif (DB::$SELF->IsDead() || DB::$SELF->IsDummyBoy() ||
	      DB::$SELF->last_load_scene == DB::$ROOM->scene) {
	self::Talk(RQ::$get->say);
      }
      else {
	self::CheckSilence(); //発言ができない状態ならゲーム停滞チェック
      }

      //ゲームシーンを更新
      if (DB::$SELF->last_load_scene != DB::$ROOM->scene) {
	DB::$SELF->Update('last_load_scene', DB::$ROOM->scene);
      }
    }
    //霊界の GM でも突然死タイマーを見れるようにする
    elseif (DB::$ROOM->dead_mode && DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      //経過時間を取得
      DB::$ROOM->IsRealTime() ?
	GameTime::GetRealPass($left_time) :
	GameTime::GetTalkPass($left_time, true);

      if ($left_time == 0) DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得
    }

    //-- データ出力 --//
    GameHTML::OutputHeader('game_play');
    self::OutputHeader();
    if ($say_limit === false) printf(self::SAY_LIMIT, Message::$say_limit);
    if (! DB::$ROOM->heaven_mode) {
      if (! RQ::$get->list_down) GameHTML::OutputPlayer();
      RoleHTML::OutputAbility();
      if (DB::$ROOM->IsDay() && DB::$SELF->IsLive() && DB::$ROOM->date != 1) { //処刑投票メッセージ
	if (is_null(DB::$SELF->target_no)) {
	  $str  = self::NOT_VOTE;
	  $vote = sprintf(self::VOTE_DO, Message::$ability_vote);
	}
	else {
	  $str  = DB::$USER->ByVirtual(DB::$SELF->target_no)->handle_name . ' さんに投票済み';
	  $vote = '';
	}
	printf(self::VOTE_DAY, DB::$ROOM->revote_count + 1, $str, $vote);
      }
      if (DB::$ROOM->IsPlaying()) GameHTML::OutputRevote();
    }

    (DB::$SELF->IsDead() && DB::$ROOM->heaven_mode) ? Talk::OutputHeaven() : Talk::Output();

    if (! DB::$ROOM->heaven_mode) {
      if (DB::$SELF->IsDead()) GameHTML::OutputAbilityAction();
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
      GameHTML::OutputVote();
      if (! DB::$ROOM->dead_mode) self::OutputLastWords();
      if (RQ::$get->list_down) GameHTML::OutputPlayer();
    }
    HTML::OutputFooter();
  }

  //ゲーム停滞のチェック
  private function CheckSilence() {
    if (! DB::$ROOM->IsPlaying()) return true; //スキップ判定

    //経過時間を取得
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      if ($left_time > 0) return true; //制限時間超過判定
    }
    else { //仮想時間制
      if (! DB::Transaction()) return false; //判定条件が全て DB なので即ロック

      //シーン再判定 (ロック付き)
      if (DB::$ROOM->LoadScene() != DB::$ROOM->scene) return DB::Rollback();
      $silence_pass_time = GameTime::GetTalkPass($left_time, true);

      if ($left_time > 0) { //制限時間超過判定
	if (DB::$ROOM->LoadTime() <= TimeConfig::SILENCE) return DB::Rollback(); //沈黙判定

	//沈黙メッセージを発行してリセット
	$str = '・・・・・・・・・・ ' . $silence_pass_time . ' ' . Message::$silence;
	DB::$ROOM->Talk($str, null, '', '', null, null, null, TimeConfig::SILENCE_PASS);
	DB::$ROOM->UpdateTime();
	return DB::Commit();
      }
    }

    //オープニングなら即座に夜に移行する
    if (DB::$ROOM->date == 1 && DB::$ROOM->IsOption('open_day') && DB::$ROOM->IsDay()) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! DB::Transaction()) return false;

	//シーン再判定 (ロック付き)
	if (DB::$ROOM->LoadScene() != DB::$ROOM->scene) return DB::Rollback();
      }
      DB::$ROOM->ChangeNight(); //夜に切り替え
      DB::$ROOM->UpdateTime(); //最終書き込み時刻を更新
      return DB::Commit(); //ロック解除
    }

    if (! DB::$ROOM->IsOvertimeAlert()) { //警告メッセージ出力判定
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! DB::Transaction()) return false;

	//シーン再判定 (ロック付き)
	if (DB::$ROOM->LoadScene() != DB::$ROOM->scene) return DB::Rollback();
      }

      //警告メッセージを出力 (最終出力判定は呼び出し先で行う)
      $str = 'あと' . Time::Convert(TimeConfig::SUDDEN_DEATH) . 'で' .
	Message::$sudden_death_announce;
      if (DB::$ROOM->OvertimeAlert($str)) { //出力したら突然死タイマーをリセット
	DB::$ROOM->sudden_death = TimeConfig::SUDDEN_DEATH;
	return DB::Commit(); //ロック解除
      }
    }

    //最終発言時刻からの差分を取得
    /*  DB::$ROOM から推定値を計算する場合 (リアルタイム制限定 + 再投票などがあると大幅にずれる) */
    //DB::$ROOM->sudden_death = TimeConfig::SUDDEN_DEATH - (DB::$ROOM->system_time - $end_time);
    DB::$ROOM->SetSuddenDeath();

    //制限時間前ならスキップ (この段階でロックしているのは非リアルタイム制のみ)
    if (DB::$ROOM->sudden_death > 0) return DB::$ROOM->IsRealTime() || DB::Rollback();

    //制限時間を過ぎていたら未投票の人を突然死させる
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
      if (! DB::Transaction()) return false;

      //シーン再判定 (ロック付き)
      if (DB::$ROOM->LoadScene() != DB::$ROOM->scene) return DB::Rollback();

      DB::$ROOM->SetSuddenDeath(); //制限時間を再計算
      if (DB::$ROOM->sudden_death > 0) return DB::Rollback();
    }

    if (abs(DB::$ROOM->sudden_death) > TimeConfig::SERVER_DISCONNECT) { //サーバダウン検出
      DB::$ROOM->UpdateTime(); //突然死タイマーをリセット
      DB::$ROOM->UpdateOvertimeAlert(); //警告出力判定をリセット
      return DB::Commit(); //ロック解除
    }

    $novote_list = array(); //未投票者リスト
    DB::$ROOM->LoadVote(); //投票情報を取得
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->rows as $user) { //生存中の未投票者を取得
	if ($user->IsLive() && ! isset(DB::$ROOM->vote[$user->user_no])) {
	  $novote_list[] = $user->user_no;
	}
      }
    }
    elseif (DB::$ROOM->IsNight()) {
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      //Text::p($vote_data, 'Vote Data');
      foreach (DB::$USER->rows as $user) { //未投票チェック
	if ($user->CheckVote($vote_data) === false) $novote_list[] = $user->user_no;
      }
    }

    //未投票突然死処理
    foreach ($novote_list as $id) DB::$USER->SuddenDeath($id, 'NOVOTED');
    RoleManager::GetClass('lovers')->Followed(true);
    RoleManager::GetClass('medium')->InsertResult();

    DB::$ROOM->Talk(Message::$vote_reset); //投票リセットメッセージ
    DB::$ROOM->UpdateVoteCount(true); //投票回数を更新
    DB::$ROOM->UpdateTime(); //制限時間リセット
    //DB::$ROOM->DeleteVote(); //投票リセット
    if (Winner::Check()) DB::$USER->ResetJoker(); //勝敗チェック
    return DB::Commit(); //ロック解除
  }

  //発言
  private function Talk($say) {
    if (! DB::$ROOM->IsPlaying()) { //ゲーム開始前後
      return RoleTalk::Save($say, DB::$ROOM->scene, null, 0, true);
    }
    if (RQ::$get->last_words && DB::$SELF->IsDummyBoy()) { //身代わり君のシステムメッセージ (遺言)
      return RoleTalk::Save($say, DB::$ROOM->scene, 'dummy_boy');
    }
    if (DB::$SELF->IsDead()) return RoleTalk::Save($say, 'heaven'); //死者の霊話

    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      $spend_time = 0; //会話で時間経過制の方は無効にする
    }
    else { //会話で時間経過制
      GameTime::GetTalkPass($left_time); //経過時間の和
      $spend_time = min(4, max(1, floor(strlen($say) / 100))); //経過時間 (範囲は 1 - 4)
    }
    if ($left_time < 1) return; //制限時間外ならスキップ (ここに来るのは生存者のみのはず)

    if (DB::$ROOM->IsDay()) { //昼はそのまま発言
      if (DB::$ROOM->IsEvent('wait_morning')) return; //待機時間判定

      //山彦の処理
      if (DB::$SELF->IsRole('echo_brownie')) RoleManager::LoadMain(DB::$SELF)->EchoSay();
      return RoleTalk::Save($say, DB::$ROOM->scene, null, $spend_time, true);
    }

    //if (DB::$ROOM->IsNight()) { //夜は役職毎に分ける
    $user = DB::$USER->ByVirtual(DB::$SELF->user_no); //仮想ユーザを取得
    if (DB::$ROOM->IsEvent('blind_talk_night')) { //天候：風雨
      $location = 'self_talk';
    }
    elseif ($user->IsWolf(true)) { //人狼
      $location = DB::$SELF->IsRole('possessed_mad') ? 'self_talk' : 'wolf'; //犬神判定
    }
    elseif ($user->IsRole('whisper_mad')) { //囁き狂人
      $location = DB::$SELF->IsRole('possessed_mad') ? 'self_talk' : 'mad'; //犬神判定
    }
    elseif ($user->IsCommon(true)) { //共有者
      $location = 'common';
    }
    elseif ($user->IsFox(true)) { //妖狐
      $location = 'fox';
    }
    else { //独り言
      $location = 'self_talk';
    }

    $update = DB::$SELF->IsWolf(); //時間経過するのは人狼の発言のみ (本人判定)
    return RoleTalk::Save($say, DB::$ROOM->scene, $location, $update ? $spend_time : 0, $update);
  }

  //遺言登録
  private function SaveLastWords($say) {
    //スキップ判定
    if (DB::$ROOM->IsFinished() || (GameConfig::LIMIT_LAST_WORDS && DB::$ROOM->IsPlaying())) {
      return false;
    }

    if ($say == ' ') $say = null; //スペースだけなら「消去」
    if (DB::$SELF->IsLive()) { //登録しない役職をチェック
      if (! DB::$SELF->IsLastWordsLimited()) DB::$SELF->Update('last_words', $say);
    }
    elseif (DB::$SELF->IsDead() && DB::$SELF->IsRole('mind_evoke')) { //口寄せの処理
      //口寄せしているイタコすべての遺言を更新する
      foreach (DB::$SELF->GetPartner('mind_evoke') as $id) {
	$target = DB::$USER->ByID($id);
	if ($target->IsLive()) $target->Update('last_words', $say);
      }
    }
  }

  //ヘッダ出力
  private function OutputHeader() {
    self::SetURL();
    echo '<table class="game-header"><tr>'."\n";

    //ゲーム終了後・霊界
    if (DB::$ROOM->IsFinished() || (DB::$ROOM->heaven_mode && DB::$SELF->IsDead())) {
      echo DB::$ROOM->IsFinished() ? DB::$ROOM->GenerateTitleTag() :
	'<td>&lt;&lt;&lt;幽霊の間&gt;&gt;&gt;</td>'."\n";

      //過去シーンのログへのリンク生成
      echo '<td class="view-option">ログ ';
      $header = sprintf('<a target="_blank" href="game_log.php%s', self::SelectURL(array()));
      $format = $header . '&date=%d&scene=%s">%d(%s)</a>'."\n";

      printf($format, 0, 'beforegame', 0, '前');
      if (DB::$ROOM->date > 1) {
	if (DB::$ROOM->IsOption('open_day')) printf($format, 1, 'day', 1, '昼');
	printf($format, 1, 'night', 1, '夜');
	for ($i = 2; $i < DB::$ROOM->date; $i++) {
	  printf($format, $i, 'day',   $i, '昼');
	  printf($format, $i, 'night', $i, '夜');
	}

	if (DB::$ROOM->heaven_mode) {
	  if (DB::$ROOM->IsNight()) printf($format, $i, 'day',  $i, '昼');
	  echo "</td>\n</tr></table>\n";
	  return;
	}
      }

      if (DB::$ROOM->IsFinished()) {
	if (DB::$ROOM->date > 0) {
	  printf($format, DB::$ROOM->date, 'day', DB::$ROOM->date, '昼');
	}
	if (DB::$ROOM->LoadLastNightTalk() > 0) {
	  printf($format, DB::$ROOM->date, 'night', DB::$ROOM->date, '夜');
	}

	$format = $header . '&scene=%s">(%s)</a>'."\n";
	printf($format, 'aftergame', '後');
	printf($format, 'heaven',    '霊');
      }
    }
    else {
      echo DB::$ROOM->GenerateTitleTag() . '<td class="view-option">'."\n";
      if (DB::$SELF->IsDead() && DB::$ROOM->dead_mode) { //死亡者の場合の、真ん中の全表示地上モード
	$format = <<<EOF
<form method="POST" action="%s" name="reload_middle_frame" target="middle">
<input type="submit" value="更新">
</form>%s
EOF;
	$url = self::GetURL(array('dead_mode', 'heaven_mode'), 'game_play.php') . '&dead_mode=on';
	printf($format, $url, "\n");
      }
    }

    if (DB::$ROOM->IsFinished()) {
      echo '<br>';
    }
    else { //ゲーム終了後は自動更新しない
      GameHTML::OutputAutoReloadLink(self::GetURL(array('auto_reload')));

      $format  = "[%s\" class=\"option-%s\">音</a>]\n";
      $url     = self::GetURL(array('play_sound'));
      $add_url = '&play_sound=on';
      RQ::$get->play_sound ? printf($format, $url, 'on') : printf($format, $url . $add_url, 'off');
    }

    //アイコン表示
    $format = "[%s\" class=\"option-%s\">アイコン</a>]\n";
    $url    = self::GetURL(array('icon'));
    RQ::$get->icon ? printf($format, $url, 'on') : printf($format, $url . '&icon=on', 'off');

    if (DB::$ROOM->IsFinished()) { //ユーザ名表示
      $format = "[%s\" class=\"option-%s\">名前</a>]\n";
      $url    = self::GetURL(array('name'));
      RQ::$get->name ? printf($format, $url, 'on') : printf($format, $url . '&name=on', 'off');
    }

    //プレイヤーリストの表示位置
    $url = self::GetURL(array('list_down'));
    echo $url . sprintf("%sリスト</a>\n", RQ::$get->list_down ? '">↑' : '&list_down=on">↓');

    //別ページリンク
    $format = '<a target="_blank" href="game_play.php%s">別ページ</a>%s';
    printf($format, self::SelectURL(array('list_down')), "\n");

    if (DB::$ROOM->IsFinished()) {
      GameHTML::OutputLogLink();
    }
    elseif (DB::$ROOM->IsBeforegame()) {
      $format = '<a target="_blank" href="user_manager.php%s&user_no=%d">登録情報変更</a>'."\n";
      printf($format, self::SelectURL(array()), DB::$SELF->user_no);
      if (DB::$SELF->IsDummyBoy()) {
	$format = '<a target="_blank" href="room_manager.php?room_no=%d">村オプション変更</a>'."\n";
	printf($format, DB::$ROOM->id);
      }
    }

    //音でお知らせ処理
    if (RQ::$get->play_sound && (DB::$ROOM->IsBeforeGame() || DB::$ROOM->IsDay())) {
      if (DB::$ROOM->IsBeforeGame()) { //入村・満員
	if (JinroCookie::$user_count > 0) {
	  $user_count = DB::$USER->GetUserCount();
	  $max_user   = DB::$ROOM->LoadMaxUser();
	  if ($user_count == $max_user && JinroCookie::$user_count != $max_user) {
	    Sound::Output('full');
	  }
	  elseif (JinroCookie::$user_count != $user_count) {
	    Sound::Output('entry');
	  }
	}
      }
      elseif (JinroCookie::$scene != '' && JinroCookie::$scene != DB::$ROOM->scene) { //夜明け
	Sound::Output('morning');
      }

      //「異議」あり
      $cookie = explode(',', JinroCookie::$objection); //クッキーの値を配列に格納する
      $stack  = JinroCookie::$objection_list;
      $count  = count($stack);
      if (count($cookie) == $count) {
	for ($i = 0; $i < $count; $i++) { //差分を計算 (index は 0 から)
	  //差分があれば性別を確認して音を鳴らす
	  if (isset($cookie[$i]) && $stack[$i] > $cookie[$i]) {
	    Sound::Output('objection_' . DB::$USER->ByID($i + 1)->sex);
	  }
	}
      }
    }
    echo "</td></tr>\n</table>\n";

    switch (DB::$ROOM->scene) {
    case 'beforegame': //開始前の注意を出力
      echo '<div class="caution">'."\n";
      echo 'ゲームを開始するには全員がゲーム開始に投票する必要があります';
      echo '<span>(投票した人は村人リストの背景が赤くなります)</span>'."\n";
      echo '</div>'."\n";
      RoomOption::Output(); //ゲームオプション表示
      break;

    case 'day':
      $time_message = '日没まで ';
      break;

    case 'night':
      $time_message = '夜明けまで ';
      break;

    case 'aftergame': //勝敗結果を出力して処理終了
      Winner::Output();
      return;
    }

    GameHTML::OutputTimeTable(); //経過日数と生存人数を出力
    $left_time = 0;
    if (DB::$ROOM->IsBeforeGame()) {
      echo '<td class="real-time">';
      if (DB::$ROOM->IsRealTime()) { //実時間の制限時間を取得
	$format = '設定時間： 昼 <span>%d分</span> / 夜 <span>%d分</span>';
	printf($format, DB::$ROOM->real_time->day, DB::$ROOM->real_time->night);
      }
      printf('　突然死：<span>%s</span></td>', Time::Convert(TimeConfig::SUDDEN_DEATH));
    }
    elseif (DB::$ROOM->IsPlaying()) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制
	GameTime::GetRealPass($left_time);
	echo '<td class="real-time"><form name="realtime_form">'."\n";
	echo '<input type="text" name="output_realtime" size="60" readonly>'."\n";
	echo '</form></td>'."\n";
      }
      else { //仮想時間制
	printf("<td>%s%s</td>\n", $time_message, GameTime::GetTalkPass($left_time));
      }
    }

    //異議あり、のボタン(夜と死者モード以外)
    if (DB::$ROOM->IsBeforeGame() ||
	(DB::$ROOM->IsDay() && ! DB::$ROOM->dead_mode &&
	 ! DB::$ROOM->heaven_mode && $left_time > 0)) {
      $format = <<<EOF
<td class="objection"><form method="POST" action="%s">
<input type="hidden" name="set_objection" value="on">
<input type="image" name="objimage" src="%s">
(%d)</form></td>%s
EOF;
      $list  = array('auto_reload', 'play_sound', 'icon', 'list_down');
      $url   = self::SelectURL($list, 'game_play.php');
      $image = GameConfig::OBJECTION_IMAGE;
      $count = GameConfig::OBJECTION - DB::$SELF->objection;
      printf($format, $url, $image, $count, "\n");
    }
    echo "</tr></table>\n";

    if (! DB::$ROOM->IsPlaying()) return;

    $str = '<div class="system-vote">%s</div>'."\n";
    if ($left_time == 0) {
      printf($str, $time_message . Message::$vote_announce);
      if (DB::$ROOM->sudden_death > 0) {
	$time = Time::Convert(DB::$ROOM->sudden_death);
	if (DB::$ROOM->IsDay()) {
	  $count = 0;
	  foreach (DB::$USER->rows as $user) {
	    if (count($user->target_no) > 0) $count++;
	  }
	  $voted = sprintf(' / 投票済み：%d人', $count);
	}
	else {
	  $voted = '';
	}
	printf("%s%s%s<br>\n", Message::$sudden_death_time, $time, $voted);
      }
    }
    elseif (DB::$ROOM->IsEvent('wait_morning')) {
      printf($str, Message::$wait_morning);
    }

    if (DB::$SELF->IsDead() && ! DB::$ROOM->IsOpenCast()) {
      printf($str, Message::$close_cast);
    }
  }

  //自分の遺言出力
  private function OutputLastWords() {
    if (DB::$ROOM->IsAfterGame()) return false; //ゲーム終了後は表示しない

    $str = DB::$SELF->LoadLastWords();
    if ($str == '') return false;

    Text::ConvertLine($str); //改行コードを変換
    if ($str == '') return false;

    echo <<<EOF
<table class="lastwords"><tr>
<td class="lastwords-title">自分の遺言</td>
<td class="lastwords-body">{$str}</td>
</tr></table>

EOF;
  }

  //リンク情報収集
  private function SetURL() {
    self::$url_stack['room'] = '?room_no=' . DB::$ROOM->id;

    $url = RQ::$get->auto_reload > 0 ? '&auto_reload=' . RQ::$get->auto_reload : '';
    self::$url_stack['auto_reload'] = $url;

    foreach (array('play_sound', 'icon', 'name', 'list_down') as $name) {
      $url = RQ::$get->$name ? sprintf('&%s=on', $name) : '';
      self::$url_stack[$name] = $url;
    }

    foreach (array('dead', 'heaven') as $name) {
      $mode = $name . '_mode';
      self::$url_stack[$mode] = DB::$ROOM->$mode ? sprintf('&%s=on', $mode) : '';
    }
  }

  //リンク情報取得 (差分型)
  private function GetURL(array $list, $header = null) {
    $url = is_null($header) ? '<a target="_top" href="game_frame.php' : $header;
    foreach (array_diff(array_keys(self::$url_stack), $list) as $key) {
      $url .= self::$url_stack[$key];
    }
    return $url;
  }

  //リンク情報取得 (抽出型)
  private function SelectURL(array $list, $header = null) {
    $url = (isset($header) ? $header : '') . self::$url_stack['room'];
    foreach ($list as $key) $url .= self::$url_stack[$key];
    return $url;
  }
}
