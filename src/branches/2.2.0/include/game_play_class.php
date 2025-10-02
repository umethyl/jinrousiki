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

    DB::$ROOM = new Room(RQ::Get()); //村情報をロード
    DB::$ROOM->dead_mode    = RQ::Get()->dead_mode;
    DB::$ROOM->heaven_mode  = RQ::Get()->heaven_mode;
    DB::$ROOM->system_time  = Time::Get();
    DB::$ROOM->sudden_death = 0; //突然死実行までの残り時間

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsFinished()) { //勝敗結果表示
      Loader::LoadFile('winner_message');
    }
    elseif (DB::$ROOM->IsBeforeGame()) { //ゲームオプション表示
      Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
      RQ::Set('retrive_type', DB::$ROOM->scene);
    }
    elseif (! DB::$ROOM->heaven_mode && DB::$ROOM->IsDay()) {
      RQ::Set('retrive_type', DB::$ROOM->scene);
    }

    DB::$USER = new UserData(RQ::Get()); //ユーザ情報をロード
    DB::$SELF = DB::$USER->BySession(); //自分の情報をロード

    //「異議」ありセット判定
    if (RQ::Get()->set_objection && DB::$SELF->objection < GameConfig::OBJECTION &&
	(DB::$ROOM->IsBeforeGame() || (DB::$ROOM->IsDay() && DB::$SELF->IsLive()))) {
      DB::$SELF->objection++;
      DB::$SELF->Update('objection', DB::$SELF->objection);
      DB::$ROOM->Talk('', 'OBJECTION', DB::$SELF->uname);
    }

    if (RQ::Get()->play_sound) { //音でお知らせ
      Loader::LoadFile('cookie_class');
      JinrouCookie::Set(); //クッキー情報セット
    }

    //-- 発言処理 --//
    $say_limit   = null;
    $update_talk = false; //発言更新判定 (キャッシュ用)
    if (! DB::$ROOM->dead_mode || DB::$ROOM->heaven_mode) { //発言が送信されるのは bottom フレーム
      $say_limit = RoleTalk::Convert(RQ::Get()->say); //発言置換処理

      if (RQ::Get()->say == '') {
	self::CheckSilence(); //発言が空ならゲーム停滞のチェック (沈黙、突然死)
      }
      elseif (RQ::Get()->last_words && (! DB::$SELF->IsDummyBoy() || DB::$ROOM->IsBeforeGame())) {
	self::SaveLastWords(RQ::Get()->say); //遺言登録 (細かい判定条件は関数内で行う)
	$update_talk = DB::$SELF->IsDummyBoy();
      }
      //死者 or 身代わり君 or 同一ゲームシーンなら書き込む
      elseif (DB::$SELF->IsDead() || DB::$SELF->IsDummyBoy() || DB::$SELF->CheckScene()) {
	self::Talk(RQ::Get()->say);
	$update_talk = true;
      }
      else {
	self::CheckSilence(); //発言ができない状態ならゲーム停滞チェック
      }

      //ゲームシーンを更新
      if (! DB::$SELF->CheckScene()) DB::$SELF->Update('last_load_scene', DB::$ROOM->scene);
    }
    //霊界の GM でも突然死タイマーを見れるようにする
    elseif (DB::$ROOM->dead_mode && DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      //経過時間を取得
      if (DB::$ROOM->IsRealTime()) {
	GameTime::GetRealPass($left_time);
      } else {
	GameTime::GetTalkPass($left_time, true);
      }

      if ($left_time == 0) DB::$ROOM->SetSuddenDeath(); //最終発言時刻からの差分を取得
    }

    //-- データ出力 --//
    GameHTML::OutputHeader('game_play');
    self::OutputHeader();
    if ($say_limit === false) printf(self::SAY_LIMIT, Message::$say_limit);
    if (! DB::$ROOM->heaven_mode) {
      if (! RQ::Get()->list_down) GameHTML::OutputPlayer();
      RoleHTML::OutputAbility();
      if (DB::$ROOM->date > 1 && DB::$ROOM->IsDay() && DB::$SELF->IsLive()) { //処刑投票メッセージ
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
      if (DB::$ROOM->IsQuiz() && DB::$ROOM->IsDay() && DB::$SELF->IsDummyBoy()) {
	GameHTML::OutputQuizVote();
      }
    }

    if (DB::$ROOM->heaven_mode && DB::$SELF->IsDead()) {
      $cache_type = 'talk_heaven';
      if (DocumentCache::Enable($cache_type)) {
	$cache_name = 'game_play/heaven' . (RQ::Get()->icon ? '_icon' : '');
	DocumentCache::Load($cache_name, CacheConfig::TALK_HEAVEN_EXPIRE);
	$filter = DocumentCache::GetTalk($update_talk, true);
	DocumentCache::Save($filter, true, $update_talk);
	DocumentCache::Output($cache_type);
      } else {
	$filter = Talk::GetHeaven();
      }
    } else {
      $cache_type = 'talk_play';
      if (! DB::$ROOM->IsPlaying() && DocumentCache::Enable($cache_type)) {
	$cache_name = 'game_play/talk' . (RQ::Get()->icon ? '_icon' : '') .
	  (RQ::Get()->name ? '_name' : '');
	DocumentCache::Load($cache_name, CacheConfig::TALK_PLAY_EXPIRE);
	$filter = DocumentCache::GetTalk($update_talk);
	DocumentCache::Save($filter, true, $update_talk);
	DocumentCache::Output($cache_type);
      } else {
	$filter = Talk::Get();
      }
    }
    $filter->Output();

    if (! DB::$ROOM->heaven_mode) {
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
      GameHTML::OutputVote();
      if (! DB::$ROOM->dead_mode) self::OutputLastWords();
      if (RQ::Get()->list_down) GameHTML::OutputPlayer();
    }
    HTML::OutputFooter();
  }

  //ゲーム停滞のチェック
  private static function CheckSilence() {
    if (! DB::$ROOM->IsPlaying()) return true; //スキップ判定

    //経過時間を取得
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      if ($left_time > 0) return true; //制限時間超過判定
    }
    else { //仮想時間制
      if (! DB::Transaction()) return false; //判定条件が全て DB なので即ロック

      //シーン再判定 (ロック付き)
      if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();
      $silence_pass_time = GameTime::GetTalkPass($left_time, true);

      if ($left_time > 0) { //制限時間超過判定
	if (RoomDB::GetTime() <= TimeConfig::SILENCE) return DB::Rollback(); //沈黙判定

	//沈黙メッセージを発行してリセット
	$str = '・・・・・・・・・・ ' . $silence_pass_time . ' ' . Message::$silence;
	DB::$ROOM->Talk($str, null, '', '', null, null, null, TimeConfig::SILENCE_PASS);
	return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback();
      }
    }

    //オープニングなら即座に夜に移行する
    if (DB::$ROOM->IsDate(1) && DB::$ROOM->IsDay() && DB::$ROOM->IsOption('open_day')) {
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! DB::Transaction()) return false;

	//シーン再判定 (ロック付き)
	if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();
      }
      DB::$ROOM->ChangeNight(); //夜に切り替え
      return RoomDB::UpdateTime() ? DB::Commit() : DB::Rollback(); //最終書き込み時刻を更新
    }

    if (! RoomDB::IsOvertimeAlert()) { //警告メッセージ出力判定
      if (DB::$ROOM->IsRealTime()) { //リアルタイム制はここでロック開始
	if (! DB::Transaction()) return false;

	//シーン再判定 (ロック付き)
	if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();
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
      if (RoomDB::GetScene() != DB::$ROOM->scene) return DB::Rollback();

      DB::$ROOM->SetSuddenDeath(); //制限時間を再計算
      if (DB::$ROOM->sudden_death > 0) return DB::Rollback();
    }

    if (abs(DB::$ROOM->sudden_death) > TimeConfig::SERVER_DISCONNECT) { //サーバダウン検出
      //突然死タイマーと警告出力判定をリセット
      return RoomDB::UpdateOvertimeAlert() ? DB::Commit() : DB::Rollback();
    }

    $novote_list = array(); //未投票者リスト
    DB::$ROOM->LoadVote(); //投票情報を取得
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->rows as $user) { //生存中の未投票者を取得
	if ($user->IsLive() && ! isset(DB::$ROOM->vote[$user->id])) {
	  $novote_list[] = $user->id;
	}
      }
    }
    elseif (DB::$ROOM->IsNight()) {
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      //Text::p($vote_data, 'Vote Data');
      foreach (DB::$USER->rows as $user) { //未投票チェック
	if ($user->CheckVote($vote_data) === false) $novote_list[] = $user->id;
      }
    }

    //未投票突然死処理
    foreach ($novote_list as $id) DB::$USER->SuddenDeath($id, 'NOVOTED');
    RoleManager::GetClass('lovers')->Followed(true);
    RoleManager::GetClass('medium')->InsertResult();

    DB::$ROOM->Talk(Message::$vote_reset); //投票リセットメッセージ
    RoomDB::ResetVote(); //投票リセット
    if (Winner::Check()) DB::$USER->ResetJoker(); //勝敗チェック
    return DB::Commit(); //ロック解除
  }

  //発言
  private static function Talk($say) {
    if (! DB::$ROOM->IsPlaying()) { //ゲーム開始前後
      return RoleTalk::Save($say, DB::$ROOM->scene, null, 0, true);
    }
    if (RQ::Get()->last_words && DB::$SELF->IsDummyBoy()) { //身代わり君のシステムメッセージ (遺言)
      return RoleTalk::Save($say, DB::$ROOM->scene, 'dummy_boy');
    }
    if (DB::$SELF->IsDead()) return RoleTalk::Save($say, 'heaven'); //死者の霊話

    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      GameTime::GetRealPass($left_time);
      $spend_time = 0; //会話で時間経過制の方は無効にする
    } else { //会話で時間経過制
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
    $user = DB::$SELF->GetVirtual(); //仮想ユーザを取得
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
  private static function SaveLastWords($say) {
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
  private static function OutputHeader() {
    self::SetURL();
    Text::Output('<table class="game-header"><tr>');
    $blank = 'target="_blank"';

    //ゲーム終了後・霊界
    if (DB::$ROOM->IsFinished() || (DB::$ROOM->heaven_mode && DB::$SELF->IsDead())) {
      echo DB::$ROOM->IsFinished() ? DB::$ROOM->GenerateTitleTag() :
	'<td>&lt;&lt;&lt;幽霊の間&gt;&gt;&gt;</td>' . Text::LF;

      //過去シーンのログへのリンク生成
      echo '<td class="view-option">ログ ';
      $header = sprintf('<a %s href="game_log.php%s', $blank, self::SelectURL(array()));
      $format = $header . '&date=%d&scene=%s">%d(%s)</a>' . Text::LF;

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
	  Text::Output('</td>' . Text::LF . '</tr></table>');
	  return;
	}
      }

      if (DB::$ROOM->IsFinished()) {
	if (DB::$ROOM->date > 0) {
	  printf($format, DB::$ROOM->date, 'day', DB::$ROOM->date, '昼');
	}
	if (TalkDB::ExistsLastNight()) {
	  printf($format, DB::$ROOM->date, 'night', DB::$ROOM->date, '夜');
	}

	$format = $header . '&scene=%s">(%s)</a>' . Text::LF;
	printf($format, 'aftergame', '後');
	printf($format, 'heaven',    '霊');
      }
    }
    else {
      echo DB::$ROOM->GenerateTitleTag() . '<td class="view-option">' . Text::LF;
      if (DB::$SELF->IsDead() && DB::$ROOM->dead_mode) { //死亡者の場合の、真ん中の全表示地上モード
	$format = <<<EOF
<form method="post" action="%s" name="reload_middle_frame" target="middle">
<input type="submit" value="更新">
</form>

EOF;
	$url = self::GetURL(array('dead_mode', 'heaven_mode'), 'game_play.php') . '&dead_mode=on';
	printf($format, $url);
      }
    }

    if (DB::$ROOM->IsFinished()) {
      echo Text::BR;
    }
    else { //ゲーム終了後は自動更新しない
      GameHTML::OutputAutoReloadLink(self::GetURL(array('auto_reload')));

      $format  = '[%s" class="option-%s">音</a>]' . Text::LF;
      $url     = self::GetURL(array('play_sound'));
      $add_url = '&play_sound=on';
      RQ::Get()->play_sound ? printf($format, $url, 'on') : printf($format, $url . $add_url, 'off');
    }

    //アイコン表示
    $format  = '[%s" class="option-%s">アイコン</a>]' . Text::LF;
    $url     = self::GetURL(array('icon'));
    $add_url = '&icon=on';
    RQ::Get()->icon ? printf($format, $url, 'on') : printf($format, $url . $add_url, 'off');

    if (DB::$ROOM->IsFinished()) { //ユーザ名表示
      $format  = '[%s" class="option-%s">名前</a>]' . Text::LF;
      $url     = self::GetURL(array('name'));
      $add_url = '&name=on';
      RQ::Get()->name ? printf($format, $url, 'on') : printf($format, $url . $add_url, 'off');
    }

    //プレイヤーリストの表示位置
    $format  = '%s">%sリスト</a>' . Text::LF;
    $url     = self::GetURL(array('list_down'));
    $add_url = '&list_down=on';
    RQ::Get()->list_down ? printf($format, $url, '↑') : printf($format, $url . $add_url, '↓');


    if (! DB::$ROOM->IsFinished()) { //オプションリンク
      $format = '<a %s href="room_manager.php?room_no=%d&describe_room=on">OP</a>' . Text::LF;
      printf($format, $blank, DB::$ROOM->id);
    }

    //別ページリンク
    $format = '<a %s href="game_play.php%s">別ページ</a>' . Text::LF;
    printf($format, $blank, self::SelectURL(array('list_down')));
    if (ServerConfig::DEBUG_MODE) {
      $format = '<a %s href="game_view.php?room_no=%d">観戦</a>' . Text::LF;
      printf($format, $blank, DB::$ROOM->id);
    }

    if (DB::$ROOM->IsFinished()) {
      GameHTML::OutputLogLink();
    }
    elseif (DB::$ROOM->IsBeforegame()) {
      $format = '<a %s href="user_manager.php%s&user_no=%d">登録情報変更</a>' . Text::LF;
      printf($format, $blank, self::SelectURL(array()), DB::$SELF->id);
      if (DB::$SELF->IsDummyBoy()) {
	$format = '<a %s href="room_manager.php?room_no=%d">村オプション変更</a>' . Text::LF;
	printf($format, $blank, DB::$ROOM->id);
      }
    }

    //音でお知らせ処理
    if (RQ::Get()->play_sound && (DB::$ROOM->IsBeforeGame() || DB::$ROOM->IsDay())) {
      if (DB::$ROOM->IsBeforeGame()) { //入村・満員
	if (JinrouCookie::$user_count > 0) {
	  $user_count = DB::$USER->GetUserCount();
	  $max_user   = RoomDB::Fetch('max_user');
	  if ($user_count == $max_user && JinrouCookie::$user_count != $max_user) {
	    Sound::Output('full');
	  } elseif (JinrouCookie::$user_count != $user_count) {
	    Sound::Output('entry');
	  }
	}
      }
      elseif (JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //夜明け
	Sound::Output('morning');
      }

      //「異議」あり
      $cookie = explode(',', JinrouCookie::$objection); //クッキーの値を配列に格納する
      $stack  = JinrouCookie::$objection_list;
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
    Text::Output("</td></tr>\n</table>");

    switch (DB::$ROOM->scene) {
    case 'beforegame': //開始前の注意を出力
      echo '<div class="caution">' . Text::LF;
      echo 'ゲームを開始するには全員がゲーム開始に投票する必要があります';
      echo '<span>(投票した人は村人リストの背景が赤くなります)</span>' . Text::LF;
      echo '</div>' . Text::LF;
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
	echo '<td class="real-time"><form name="realtime_form">' . Text::LF;
	echo '<input type="text" name="output_realtime" size="60" readonly>' . Text::LF;
	echo '</form></td>' . Text::LF;
      }
      else { //仮想時間制
	printf('<td>%s%s</td>' . Text::LF, $time_message, GameTime::GetTalkPass($left_time));
      }
    }

    //異議あり、のボタン(夜と死者モード以外)
    if (DB::$ROOM->IsBeforeGame() ||
	(DB::$ROOM->IsDay() && ! DB::$ROOM->dead_mode &&
	 ! DB::$ROOM->heaven_mode && $left_time > 0)) {
      $format = <<<EOF
<td class="objection"><form method="post" action="%s">
<input type="hidden" name="set_objection" value="on">
<input type="image" name="objimage" src="%s">
(%d)</form></td>

EOF;
      $list  = array('auto_reload', 'play_sound', 'icon', 'list_down');
      $url   = self::SelectURL($list, 'game_play.php');
      $image = GameConfig::OBJECTION_IMAGE;
      $count = GameConfig::OBJECTION - DB::$SELF->objection;
      printf($format, $url, $image, $count);
    }
    Text::Output('</tr></table>');

    if (! DB::$ROOM->IsPlaying()) return;

    $format = '<div class="system-vote">%s</div>' . Text::LF;
    if (DB::$ROOM->IsEvent('wait_morning')) {
      printf($format, Message::$wait_morning);
    }
    elseif ($left_time == 0) {
      printf($format, $time_message . Message::$vote_announce);
      if (DB::$ROOM->sudden_death > 0) {
	$time = Time::Convert(DB::$ROOM->sudden_death);
	if (DB::$ROOM->IsDay() || DB::$SELF->IsDummyBoy()) {
	  $voted = sprintf(' / 未投票：%d人', self::GetNovotedCount());
	} else {
	  $voted = '';
	}
	$time_format = '<div class="system-sudden-death">%s%s%s</div>' . Text::LF;
	printf($time_format, Message::$sudden_death_time, $time, $voted);
      }
    }
    elseif (DB::$SELF->IsDummyBoy()) {
      $count = self::GetNovotedCount();
      printf('<div class="system-sudden-death">未投票：%d人</div>' . Text::LF, $count);
    }

    if (DB::$SELF->IsDead() && ! DB::$ROOM->IsOpenCast()) {
      printf($format, Message::$close_cast);
    }
  }

  //自分の遺言出力
  private static function OutputLastWords() {
    if (DB::$ROOM->IsAfterGame()) return false; //ゲーム終了後は表示しない

    $str = UserDB::GetLastWords(DB::$SELF->id);
    if ($str == '') return false;

    Text::Line($str); //改行コードを変換
    if ($str == '') return false;

    echo <<<EOF
<table class="lastwords"><tr>
<td class="lastwords-title">自分の遺言</td>
<td class="lastwords-body">{$str}</td>
</tr></table>

EOF;
  }

  //リンク情報収集
  private static function SetURL() {
    self::$url_stack['room'] = '?room_no=' . DB::$ROOM->id;

    $url = RQ::Get()->auto_reload > 0 ? '&auto_reload=' . RQ::Get()->auto_reload : '';
    self::$url_stack['auto_reload'] = $url;

    foreach (array('play_sound', 'icon', 'name', 'list_down') as $name) {
      self::$url_stack[$name] = RQ::Get()->$name ? sprintf('&%s=on', $name) : '';
    }

    foreach (array('dead', 'heaven') as $name) {
      $mode = $name . '_mode';
      self::$url_stack[$mode] = DB::$ROOM->$mode ? sprintf('&%s=on', $mode) : '';
    }
  }

  //リンク情報取得 (差分型)
  private static function GetURL(array $list, $header = null) {
    $url = is_null($header) ? '<a target="_top" href="game_frame.php' : $header;
    foreach (array_diff(array_keys(self::$url_stack), $list) as $key) {
      $url .= self::$url_stack[$key];
    }
    return $url;
  }

  //リンク情報取得 (抽出型)
  private static function SelectURL(array $list, $header = null) {
    $url = (isset($header) ? $header : '') . self::$url_stack['room'];
    foreach ($list as $key) {
      $url .= self::$url_stack[$key];
    }
    return $url;
  }

  //未投票人数取得
  private static function GetNovotedCount() {
    $count = 0;
    if (DB::$ROOM->IsDay()) {
      foreach (DB::$USER->rows as $user) {
	if ($user->IsLive() && count($user->target_no) < 1) $count++;
      }
    }
    elseif (DB::$ROOM->IsNight() && DB::$SELF->IsDummyBoy()) { //身代わり君以外は不可
      if (! isset(DB::$ROOM->vote)) DB::$ROOM->LoadVote();
      $vote_data = DB::$ROOM->ParseVote(); //投票情報をパース
      //Text::p($vote_data, 'Vote Data');
      foreach (DB::$USER->rows as $user) { //未投票チェック
	if ($user->CheckVote($vote_data) === false) $count++;
      }
    }
    return $count;
  }
}
