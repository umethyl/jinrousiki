<?php
//-- 投票処理基礎クラス --//
class VoteBase {
  //投票コマンドチェック
  protected static function CheckSituation($situation) {
    if (is_array($situation)) {
      if (in_array(RQ::Get()->situation, $situation)) return true;
    }
    elseif (RQ::Get()->situation == $situation) {
      return true;
    }
    VoteHTML::OutputResult(VoteMessage::INVALID_SITUATION);
  }
}

//-- 投票処理クラス (ゲーム開始) --//
class VoteGameStart extends VoteBase {
  //実行処理
  static function Execute() {
    self::CheckSituation('GAMESTART');
    self::FilterDummyBoy();
    self::Load();
    self::Vote();
  }

  //集計処理
  static function Aggregate($force_start = false) {
    self::LoadStack();
    Cast::Stack()->Set('force_start', $force_start);
    if (! self::Check()) return false;

    //-- 配役決定ルーチン --//
    DB::$ROOM->LoadOption(); //配役設定オプションの情報を取得
    //Text::p(DB::$ROOM->option_role, '◆OptionRole');
    //Text::p(DB::$ROOM->option_list, '◆OptionList');

    self::InitStack();    //配役決定用変数をセット
    self::SetDummyBoy();  //身代わり君配役処理
    self::SetPrimary();   //一次配役処理
    self::SetSecondary(); //二次配役処理
    self::SetSubRole();   //サブ役職配役処理

    //Cast::Stack()->p('fix_uname', '◆FixUname/End');
    //Cast::Stack()->p('fix_role', '◆FixRole/End');
    //RoomDB::DeleteVote(); return false; //テスト用

    self::SaveRole();
    DB::$USER->UpdateKick(); //KICK の後処理
    DB::$ROOM->Start();
    return true;
  }

  //身代わり君処理
  private static function FilterDummyBoy() {
    if (! DB::$SELF->IsDummyBoy(true)) return; //出題者以外の身代わり君

    $str = VoteMessage::GAME_START_TITLE;
    if (GameConfig::POWER_GM) { //強権モードによる強制開始処理
      if (! self::Aggregate(true)) $str .= VoteMessage::GAME_START_SHORTAGE;
      DB::Commit();
      VoteHTML::OutputResult($str);
    }
    else {
      VoteHTML::OutputResult($str . VoteMessage::GAME_START_DUMMY_BOY);
    }
  }

  //投票情報ロード
  private static function Load() {
    DB::$ROOM->LoadVote();
    if (DB::$SELF->ExistsVote()) { //投票済みチェック
      VoteHTML::OutputResult(VoteMessage::GAME_START_TITLE . VoteMessage::ALREADY_GAME_START);
    }
  }

  //投票処理
  private static function Vote() {
    if (DB::$SELF->Vote('GAMESTART')) {
      self::Aggregate();
      DB::Commit();
      VoteHTML::OutputResult(VoteMessage::GAME_START_TITLE . VoteMessage::SUCCESS);
    }
    else {
      VoteHTML::OutputResult(VoteMessage::GAME_START_TITLE . VoteMessage::DB_ERROR);
    }
  }

  //スタックセット
  private static function LoadStack() {
    OptionManager::LoadStack();
    Cast::LoadStack();
  }

  //投票数チェック
  private static function Check() {
    $user_count = DB::$USER->GetUserCount(); //ユーザ総数を取得
    $vote_count = self::GetVoteCount($user_count);

    //規定人数に足りないか、全員投票していなければ処理終了
    if ($vote_count != $user_count || $vote_count < min(array_keys(CastConfig::$role_list))) {
      return false;
    }
    Cast::Stack()->Set('user_count', $user_count);
    return true;
  }

  //投票数取得
  private static function GetVoteCount($user_count) {
    if (DB::$ROOM->IsTest()) return $user_count;

    self::CheckSituation('GAMESTART');
    if (Cast::Stack()->Get('force_start')) return $user_count; //強制開始モード時はスキップ

    $count = DB::$ROOM->LoadVote(); //投票情報をロード (ロック前の情報は使わない事)
    //クイズ村以外の身代わり君を加算
    if (DB::$ROOM->IsDummyBoy() && ! DB::$ROOM->IsQuiz()) $count++;
    return $count;
  }

  //変数初期化
  private static function InitStack() {
    $stack = Cast::Stack();
    $stack->Init('fix_uname'); //役職の決定したユーザ名
    $stack->Init('fix_role');  //ユーザ名に対応する役職
    $stack->Init('remain');    //配役未決定ユーザ名
    $stack->Set('uname', DB::$USER->GetLivingUsers()); //ユーザ名一覧
    $stack->Set('role', Cast::Get(Cast::Stack()->Get('user_count'))); //配役リスト
    //Cast::Stack()->p('uname', '◆Uname');
    //Cast::Stack()->p('role', '◆Role');
  }

  //身代わり君の役職を決定
  private static function SetDummyBoy() {
    if (! DB::$ROOM->IsDummyBoy()) return;

    Cast::SetDummyBoy();
    //Cast::Stack()->p('fix_role', '◆dummy_boy');
    if (Cast::Stack()->Count('fix_role') < 1) {
      $str = sprintf(VoteMessage::ERROR_CAST, VoteMessage::NO_CAST_DUMMY_BOY);
      VoteHTML::OutputResult($str, ! DB::$ROOM->IsTest());
    }

    Cast::Stack()->Add('fix_uname', GM::DUMMY_BOY); //決定済みリスト登録
    Cast::Stack()->Delete('uname', GM::DUMMY_BOY);  //ユーザ名リストから削除
  }

  //一次配役処理
  private static function SetPrimary() {
    Cast::Stack()->Shuffle('uname'); //ユーザリストシャッフル
    //Cast::Stack()->p('uname', '◆ShuffleUname');

    if (DB::$ROOM->IsOption('wish_role')) { //希望判定
      Cast::SetWishRole();
    } else {
      Cast::SetMergeRemain(Cast::Stack()->Get('uname'));
    }
    //Cast::Stack()->p('fix_uname', '◆FixUname/1st');
    //Cast::Stack()->p('fix_role', '◆FixRole/1st');

    //配役結果チェック
    $remain = Cast::Stack()->Count('remain');
    $role   = Cast::Stack()->Count('role');
    if ($remain != $role) {
      $str = sprintf(VoteMessage::CAST_MISMATCH_REMAIN, $remain, $role);
      VoteHTML::OutputResult(sprintf(VoteMessage::ERROR_CAST, $str), ! DB::$ROOM->IsTest());
    }
  }

  //二次配役処理
  private static function SetSecondary() {
    if (Cast::Stack()->Count('remain') > 0) { //未決定者を配役
      Cast::SetMergeRemain(Cast::Stack()->Get('remain'));
    }
    //Cast::Stack()->p('fix_uname', '◆FixUname/2nd');
    //Cast::Stack()->p('fix_role', '◆FixRole/2nd');

    //配役結果チェック
    $format     = VoteMessage::ERROR_CAST;
    $reset_flag = ! DB::$ROOM->IsTest();

    //配役決定者チェック
    $user_count = Cast::Stack()->Get('user_count');
    $fix_uname  = Cast::Stack()->Count('fix_uname');
    if ($user_count != $fix_uname) {
      $str = sprintf(VoteMessage::CAST_MISMATCH_USER, $user_count, $fix_uname);
      VoteHTML::OutputResult(sprintf($format, $str), $reset_flag);
    }

    //配役数チェック
    $fix_role = Cast::Stack()->Count('fix_role');
    if ($fix_uname != $fix_role) {
      $str = sprintf(VoteMessage::CAST_MISMATCH_ROLE, $fix_uname, $fix_role);
      VoteHTML::OutputResult(sprintf($format, $str), $reset_flag);
    }

    //残り配役数チェック
    $role = Cast::Stack()->Count('role');
    if ($role > 0) {
      $str = sprintf(VoteMessage::CAST_REMAIN_ROLE, $role);
      VoteHTML::OutputResult(sprintf($format, $str), $reset_flag);
    }
  }

  //サブ役職配役処理
  private static function SetSubRole() {
    Cast::SetSubRole(); //オプションでつけるサブ役職

    /*
    if (DB::$ROOM->IsOption('festival')) { //お祭り村 (内容は管理人が自由にカスタムする)
      $role  = 'nervy';
      $stack = Cast::Stack()->Get('fix_role');
      foreach (array_keys($stack) as $id) { //全員に自信家をつける
        $stack[$id] .= ' ' . $role;
      }
      Cast::Stack()->Set('fix_role', $stack);
    }
    */
  }

  //役職を DB に登録
  private static function SaveRole() {
    $fix_uname_list = Cast::Stack()->Get('fix_uname');
    $flag = DB::$ROOM->IsOption('detective');
    if ($flag) $detective_list = array();

    $stack = array();
    foreach (Cast::Stack()->Get('fix_role') as $id => $fix_role) {
      $user = DB::$USER->ByUname($fix_uname_list[$id]);
      $user->ChangeRole($fix_role);

      $role_list = explode(' ', $fix_role);
      foreach ($role_list as $role) {
	isset($stack[$role]) ? $stack[$role]++ : $stack[$role] = 1;
      }

      if ($flag && in_array('detective_common', $role_list)) $detective_list[] = $user;
    }

    if (DB::$ROOM->IsOption('joker')) { //joker[2] 対策
      unset($stack['joker[2]']);
      $stack['joker'] = 1;
    }
    Cast::Stack()->Set('role_count', $stack);
    if ($flag) Cast::Stack()->Set('detective', $detective_list);
  }
}

//-- 投票処理クラス (キック) --//
class VoteKick extends VoteBase {
  //実行処理
  static function Execute() {
    //-- データロード --//
    self::CheckSituation('KICK_DO'); //コマンドチェック
    $str = VoteMessage::KICK_TITLE;
    $target = DB::$USER->ByID(RQ::Get()->target_no); //投票先のユーザ情報を取得

    //-- 無効判定 --//
    if (is_null($target->id) || $target->live == UserLive::KICK) {
      VoteHTML::OutputResult($str . VoteMessage::KICK_EMPTY);
    }
    if ($target->IsDummyBoy()) VoteHTML::OutputResult($str . VoteMessage::KICK_DUMMY_BOY);
    if (! GameConfig::SELF_KICK && $target->IsSelf()) {
      VoteHTML::OutputResult($str . VoteMessage::KICK_SELF);
    }

    DB::$ROOM->LoadVote(true); //投票情報をロード
    $stack = DB::$ROOM->Stack()->GetKey('vote', DB::$SELF->id);
    if (! is_null($stack) && in_array($target->id, $stack)) {
      VoteHTML::OutputResult($str . $target->handle_name . VoteMessage::ALREADY_KICK);
    }

    //-- 投票処理 --//
    if (DB::$SELF->Vote('KICK_DO', $target->id)) { //投票処理
      DB::$ROOM->Talk($target->handle_name, 'KICK_DO', DB::$SELF->uname); //投票通知
      $vote_count = self::Aggregate($target); //集計処理
      DB::Commit();
      $format = VoteMessage::SUCCESS . VoteMessage::KICK_SUCCESS;
      $str .= sprintf($format, $target->handle_name, $vote_count, GameConfig::KICK);
      VoteHTML::OutputResult($str);
    }
    else {
      VoteHTML::OutputResult($str . VoteMessage::DB_ERROR);
    }
  }

  //集計処理 ($target : 対象 HN, 返り値 : 対象 HN の投票合計数)
  private static function Aggregate(User $target) {
    self::CheckSituation('KICK_DO'); //コマンドチェック

    //今回投票した相手にすでに投票している人数を取得
    $vote_count = 1;
    foreach (DB::$ROOM->Stack()->Get('vote') as $stack) {
      if (in_array($target->id, $stack)) $vote_count++;
    }

    //規定数以上の投票があった / キッカーが身代わり君 / 自己 KICK が有効の場合に処理
    if ($vote_count >= GameConfig::KICK || DB::$SELF->IsDummyBoy() ||
	(GameConfig::SELF_KICK && $target->IsSelf())) {
      UserDB::Kick($target->id);

      //通知処理
      DB::$ROOM->Talk($target->handle_name . TalkMessage::KICK_OUT);
      DB::$ROOM->Talk(GameMessage::VOTE_RESET);

      RoomDB::UpdateVoteCount(); //投票リセット処理
    }
    return $vote_count;
  }
}

//-- 投票処理クラス (昼) --//
class VoteDay extends VoteBase {
  //実行処理
  static function Execute() {
    //-- 投票データ収集 --//
    self::Load();
    self::Check();

    //-- 投票データチェック --//
    self::FilterVoteDuel();
    self::CheckAlreadyVote();

    //-- 投票処理 --//
    RoleManager::Stack()->Set('vote_number', 1); //投票数を初期化
    self::FilterVoteDoMain();
    self::FilterVoteDoSub();
    self::FilterVoteDoWeather();
    self::ExecuteVoteKill();

    //-- システムメッセージ --//
    if (DB::$ROOM->IsTest()) return true;
    DB::$ROOM->Talk(RoleManager::Stack()->Get('target')->GetName(), 'VOTE_DO', DB::$SELF->uname);

    //-- 集計処理 --//
    self::Aggregate();
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }

  //集計処理
  static function Aggregate() {
    //-- 沈黙禁止処理 --//
    self::FilterNoSilence();

    if (! self::CheckAggregate()) return false;
    //DB::$ROOM->Stack()->p('vote', '◆vote');
    //RoleManager::Stack()->p('user_list', '◆user_list');

    //-- 投票データ収集 --//
    self::InitStack();
    self::InitVoteCount();
    //RoleManager::Stack()->p('vote_count', '◆VoteCountBase');
    self::InitVoteData();
    self::FilterVoteCorrect(); //投票数補正

    //-- 処刑者決定 --//
    self::SaveResultVote();
    self::DecideVoteKill();
    //RoleManager::Stack()->p('vote_kill_uname', '◆VoteTarget');

    if (! RoleManager::Stack()->IsEmpty('vote_kill_uname')) { //-- 処刑実行処理 --//
      self::VoteKill(); //処刑実行

      //-- 毒関連能力の処理 --//
      self::FilterSetDetox();
      self::FilterPoison();
      //RoleManager::Stack()->p('pharmacist_result', '◆EndDetox');

      self::FilterVoteKillCounter(); //処刑者カウンター
      self::FilterVoteAction();      //特殊投票発動者
      self::FilterNecromancer();     //霊能
    }

    self::FilterVotedReaction(); //得票カウンター
    self::FilterSuddenDeath();   //ショック死
    self::FilterFollowed();      //道連れ
    self::FilterSaveResult();    //鑑定結果登録

    RoleManager::GetClass('lovers')->Followed();     //恋人後追い
    RoleManager::GetClass('medium')->InsertResult(); //巫女のシステムメッセージ

    if (! RoleManager::Stack()->IsEmpty('vote_kill_uname')) { //夜に切り替え
      //-- 処刑得票カウンター --//
      self::FilterVoteKillReaction();

      //-- 天候 --//
      self::FilterWeather();

      //-- 処刑キャンセル --//
      self::FilterVoteCancel();

      if ($joker_flag = DB::$ROOM->IsOption('joker')) { //ジョーカー移動判定
	$joker_filter = RoleManager::GetClass('joker');
	$joker_flag   = $joker_filter->SetJoker();
      }

      DB::$ROOM->ChangeNight();
      if (Winner::Check()) {
	if ($joker_flag) $joker_filter->FinishJoker();
      }
      else {
	if ($joker_flag) $joker_filter->ResetVoteJoker();
	self::InsertRandomMessage(); //ランダムメッセージ
      }
      if (DB::$ROOM->IsTest()) return RoleManager::Stack()->Get('vote_message');
      DB::$ROOM->SkipNight();
    }
    else { //再投票処理
      if (DB::$ROOM->IsTest()) return RoleManager::Stack()->Get('vote_message');

      //処刑投票回数を増やす
      DB::$ROOM->revote_count++;
      RoomDB::UpdateVoteCount(true);
      DB::$ROOM->Talk(sprintf(VoteMessage::REVOTE, DB::$ROOM->revote_count)); //システムメッセージ

      if (Winner::Check(true) && DB::$ROOM->IsOption('joker')) { //勝敗判定＆ジョーカー処理
	RoleManager::GetClass('joker')->FinishDrawJoker();
      }
    }
    foreach (DB::$USER->rows as $user) $user->UpdatePlayer(); //player 更新
    RoomDB::UpdateTime(); //最終書き込み時刻を更新
  }

  //処刑投票先のユーザ情報を取得
  private static function Load() {
    self::CheckSituation('VOTE_KILL'); //コマンドチェック
    RoleManager::Stack()->Set('target', DB::$USER->ByReal(RQ::Get()->target_no));
  }

  //無効判定
  private static function Check() {
    $target = RoleManager::Stack()->Get('target');
    if (is_null($target->id)) VoteHTML::OutputResult(VoteMessage::INVALID_VOTE);
    if ($target->IsSelf())    VoteHTML::OutputResult(VoteMessage::VOTE_SELF);
    if ($target->IsDead())    VoteHTML::OutputResult(VoteMessage::VOTE_DEAD);
  }

  //特殊イベント判定
  private static function FilterVoteDuel() {
    if (! DB::$ROOM->IsEvent('vote_duel')) return;
    if (! DB::$ROOM->Stack()->ExistsArray('vote_duel', RQ::Get()->target_no)) {
      VoteHTML::OutputResult(VoteMessage::VOTE_DUEL);
    }
  }

  //投票済みチェック
  private static function CheckAlreadyVote() {
    if (DB::$ROOM->IsTest()) {
      if (array_key_exists(DB::$SELF->uname, RQ::GetTest()->vote->day)) {
	Text::p(DB::$SELF->uname, '◆AlreadyVoted');
	return false;
      }
      return true;
    }

    if (DB::$ROOM->revote_count != RQ::Get()->revote_count) {
      VoteHTML::OutputResult(VoteMessage::INVALID_COUNT);
    }

    if (UserDB::IsVoteKill()) {
      VoteHTML::OutputResult(VoteMessage::ALREADY_VOTE);
    }
  }

  //投票数補正 (メイン役職)
  private static function FilterVoteDoMain() {
    RoleManager::SetActor(DB::$SELF); //投票者をセット
    foreach (RoleManager::Load('vote_do_main') as $filter) {
      $filter->FilterVoteDo();
    }
  }

  //投票数補正 (サブ役職)
  private static function FilterVoteDoSub() {
    if (DB::$ROOM->IsEvent('no_authority')) return; //蜃気楼ならスキップ

    RoleManager::SetActor(DB::$SELF->GetVirtual()); //仮想投票者をセット
    foreach (RoleManager::Load('vote_do_sub') as $filter) {
      $filter->FilterVoteDo();
    }
  }

  //投票数補正 (天候)
  private static function FilterVoteDoWeather() {
    if (DB::$ROOM->IsEvent('hyper_random_voter')) {
      $vote_number = RoleManager::Stack()->Get('vote_number') + Lottery::GetRange(0, 5);
      RoleManager::Stack()->Set('vote_number', $vote_number);
    }
  }

  //処刑投票実行処理
  private static function ExecuteVoteKill() {
    $vote_number = max(0, RoleManager::Stack()->Get('vote_number'));
    if (! DB::$SELF->Vote('VOTE_KILL', RoleManager::Stack()->Get('target')->id, $vote_number)) {
      VoteHTML::OutputResult(VoteMessage::DB_ERROR);
    }
  }

  //沈黙禁止処理
  private static function FilterNoSilence() {
    if (! DB::$ROOM->IsOption('no_silence')) return;

    if (DB::$ROOM->IsTest()) {
      $stack = RQ::GetTest()->talk_count;
    } else {
      //スキップ判定 (超過前 or 未投票発言者あり)
      if (GameTime::GetLeftTime() > 0 || TalkDB::GetNotVoteTalkUserCount() > 0) return;
      $stack = TalkDB::GetAllUserTalkCount();
    }

    foreach (DB::$USER->GetLivingUsers() as $id => $name) {
      if (isset($stack[$id]) && $stack[$id] > 0) continue;
      DB::$USER->SuddenDeath($id, 'SILENCE');
    }
  }

  //集計実行判定
  private static function CheckAggregate() {
    if (! DB::$ROOM->IsTest()) self::CheckSituation('VOTE_KILL'); //コマンドチェック
    $user_list  = DB::$USER->GetLivingUsers(true); //生存者を取得
    $vote_count = DB::$ROOM->LoadVote(); //投票数を取得
    if (DB::$ROOM->IsOption('no_silence')) {
      foreach (DB::$USER->rows as $user) { //沈黙死した人の投票を除く
	if ($user->suicide_flag && $user->ExistsVote()) {
	  DB::$ROOM->Stack()->DeleteKey('vote', $user->id);
	  $vote_count--;
	}
      }
    }
    if ($vote_count != count($user_list)) return false; //投票数と照合
    RoleManager::Stack()->Set('user_list', $user_list);
    return true;
  }

  //変数の初期化
  /*
    pharmacist_result //薬師系の鑑定結果
  */
  private static function InitStack() {
    $stack = array('pharmacist_result');
    foreach ($stack as $name) RoleManager::Stack()->Init($name);

    //現在のジョーカー所持者の ID
    if (DB::$ROOM->IsOption('joker')) RoleManager::GetClass('joker')->InitializeJoker();
  }

  //初期得票データ収集
  private static function InitVoteCount() {
    $stack = array(); //得票リスト (ユーザ名 => 投票数)
    $no_silence = DB::$ROOM->IsOption('no_silence');
    foreach (DB::$ROOM->Stack()->Get('vote') as $id => $list) {
      $target_id = $list['target_no'];
      if ($no_silence && DB::$USER->ByReal($target_id)->suicide_flag) { //沈黙死判定
	continue;
      }

      $target_uname = DB::$USER->ByVirtual($target_id)->uname;
      if (! isset($stack[$target_uname])) {
	$stack[$target_uname] = 0;
      }
      $stack[$target_uname] += $list['vote_number'];
    }
    RoleManager::Stack()->Set('vote_count', $stack);
  }

  //個別の投票データ収集
  private static function InitVoteData() {
    //変数初期化
    $no_silence        = DB::$ROOM->IsOption('no_silence'); //沈黙禁止
    $live_uname_list   = array(); //生存者リスト (ユーザ名)
    $vote_target_list  = array(); //投票リスト (ユーザ名 => 投票先ユーザ名)
    $vote_message_list = array(); //システムメッセージ用 (ユーザID => array())
    $vote_count_list   = RoleManager::Stack()->Get('vote_count');

    foreach (RoleManager::Stack()->Get('user_list') as $id => $uname) {
      $list   = DB::$ROOM->Stack()->GetKey('vote', $id); //投票データ
      $user   = DB::$USER->ByVirtual($id); //仮想ユーザ
      $target = DB::$USER->ByVirtual($list['target_no']); //投票先の仮想ユーザ
      $real   = DB::$USER->ByReal($user->id); //実ユーザ
      $vote   = @(int)$list['vote_number']; //投票数
      RoleManager::Stack()->Set('vote_poll', @(int)$vote_count_list[$user->uname]); //得票数

      //得票補正 (メイン役職)
      RoleManager::SetActor($real); //メイン役職は実ユーザ
      foreach (RoleManager::Load('vote_poll_main') as $filter) {
	$filter->FilterVotePoll();
      }

      RoleManager::SetActor($user); //サブ役職は仮想ユーザ
      if (! DB::$ROOM->IsEvent('no_authority')) { //得票補正 (サブ役職 / 蜃気楼ならスキップ)
	foreach (RoleManager::Load('vote_poll_sub') as $filter) {
	  $filter->FilterVotePoll();
	}
      }
      $poll = max(0, RoleManager::Stack()->Get('vote_poll'));

      //リストにデータを追加
      $live_uname_list[$user->id]     = $user->uname;
      $vote_target_list[$user->uname] = $target->uname;
      $vote_count_list[$user->uname]  = $poll;
      $vote_message_list[$user->id]   = array('target_name' => $target->handle_name,
					      'vote' => $vote, 'poll' => $poll);
      if ($real->IsRole('philosophy_wizard')) { //賢者の魔法発動
	RoleManager::LoadMain($real)->SetWizard();
	//Text::p($user->virtual_role, '◆Wizard: ' . $user->uname);
      }

      if ($no_silence && $target->GetReal()->suicide_flag) { //沈黙死スキップ判定
	//Text::p($target->uname, '◆Skip [suicide]');
	continue;
      }

      //処刑投票能力者 (メイン役職)
      RoleManager::SetActor($real); //メイン役職は実ユーザ
      foreach (RoleManager::Load('vote_day_main', false, true) as $filter) {
	$filter->SetVoteDay($target->uname);
      }

      //処刑投票能力者 (サブ役職)
      RoleManager::SetActor($user); //サブ役職は仮想ユーザ
      foreach (RoleManager::Load('vote_day_sub', false) as $filter) {
	$filter->SetVoteDay($target->uname);
      }
    }
    RoleManager::Stack()->Set('live_uname',  $live_uname_list);
    RoleManager::Stack()->Set('vote_count',  $vote_count_list);
    RoleManager::Stack()->Set('vote_target', $vote_target_list);
    //RoleManager::Stack()->p(null, '◆RoleStack');

    //Text::p($vote_message_list, '◆VoteMessage [base]');
    ksort($vote_message_list); //投票順をソート (憑依対応)
    $stack = array();
    foreach ($vote_message_list as $id => $list) {
      $stack[DB::$USER->ByID($id)->uname] = $list;
    }
    RoleManager::Stack()->Set('vote_message', $stack);
    //RoleManager::Stack()->p('vote_message', '◆VoteMessage [sort]');
  }

  //投票数補正
  private static function FilterVoteCorrect() {
    //RoleManager::Stack()->p('vote_count', '◆VoteCount');
    foreach (RoleManager::LoadFilter('vote_correct') as $filter) {
      $filter->VoteCorrect();
    }
  }

  //投票結果登録
  private static function SaveResultVote() {
    $max_poll = 0; //最多得票数
    if (! DB::$ROOM->IsTest()) {
      $vote_count = DB::$ROOM->revote_count + 1;
      $items = 'room_no, date, count, handle_name, target_name, vote, poll';
      $values_header = sprintf('%d, %d, %d, ', DB::$ROOM->id, DB::$ROOM->date, $vote_count);
    }

    //タブ区切りのデータをシステムメッセージに登録
    foreach (RoleManager::Stack()->Get('vote_message') as $uname => $stack) {
      extract($stack); //配列を展開
      if ($poll > $max_poll) $max_poll = $poll; //最大得票数を更新
      if (DB::$ROOM->IsTest()) continue;
      $handle_name = DB::$USER->ByUname($uname)->handle_name; //憑依追跡済み
      $values = $values_header . "'{$handle_name}', '{$target_name}', {$vote}, {$poll}";
      DB::Insert('result_vote_kill', $items, $values);
    }
    RoleManager::Stack()->Set('max_poll', $max_poll);
  }

  //処刑者決定処理
  private static function DecideVoteKill() {
    //最大得票数のユーザ名 (処刑候補者) のリストを取得
    $max_poll = RoleManager::Stack()->Get('max_poll');
    $stack    = array_keys(RoleManager::Stack()->Get('vote_count'), $max_poll);
    RoleManager::Stack()->Set('max_voted', $stack);
    RoleManager::Stack()->Set('vote_kill_uname', null); //処刑者 (ユーザ名)
    //Text::p($stack, '◆MaxVoted');

    if (count($stack) == 1) { //一人だけなら決定
      RoleManager::Stack()->Set('vote_kill_uname', array_shift($stack));
    }
    else { //決定能力者判定
      RoleManager::Stack()->Set('vote_possible', $stack);
      foreach (RoleManager::LoadFilter('vote_kill') as $filter) {
	$filter->DecideVoteKill();
      }

      //決着村・彩雲
      if (RoleManager::Stack()->IsEmpty('vote_kill_uname') &&
	  (DB::$ROOM->IsOption('settle') || DB::$ROOM->IsEvent('settle'))) {
	$vote_kill_uname = Lottery::Get(RoleManager::Stack()->Get('vote_possible'));
	RoleManager::Stack()->Set('vote_kill_uname', $vote_kill_uname);
      }
    }
  }

  //処刑実行
  private static function VoteKill() {
    $uname  = RoleManager::Stack()->Get('vote_kill_uname'); //ユーザ情報を取得
    $target = DB::$USER->ByRealUname($uname);
    DB::$USER->Kill($target->id, 'VOTE_KILLED'); //処刑処理
    RoleManager::Stack()->Set('vote_kill_user', $target);

    //処刑者を生存者リストから除く
    $stack = RoleManager::Stack()->Get('live_uname');
    unset($stack[array_search($uname, $stack)]);
    RoleManager::Stack()->Set('live_uname', $stack);
  }

  //薬師系の毒鑑定情報収集
  private static function FilterSetDetox() {
    foreach (RoleManager::LoadFilter('distinguish_poison') as $filter) {
      $filter->SetDetox();
    }
  }

  //処刑者の毒処理
  private static function FilterPoison() {
    $vote_target = RoleManager::Stack()->Get('vote_kill_user');
    if (! $vote_target->IsPoison()) return; //毒能力の発動判定

    //薬師系の解毒判定 (夢毒者は対象外)
    $role  = 'alchemy_pharmacist'; //錬金術師
    $actor = $vote_target->GetVirtual();  //投票データは仮想ユーザ
    $actor->detox = false;
    $actor->$role = false;
    RoleManager::SetActor($actor);
    if (! $vote_target->IsRole('dummy_poison')) {
      foreach (RoleManager::LoadFilter('detox') as $filter) {
	$filter->Detox();
      }
      if (RoleManager::GetActor()->detox) return;
    }

    //毒の対象オプションをチェックして初期候補者リストを作成後に対象者を取得
    if (GameConfig::POISON_ONLY_VOTER) { //投票した人限定
      $stack = array_keys(RoleManager::Stack()->Get('vote_target'), $vote_target->uname);
    } else {
      $stack = RoleManager::Stack()->Get('live_uname');
    }
    //Text::p($stack, '◆Target List [poison]');

    if (RoleManager::GetActor()->$role || DB::$ROOM->IsEvent($role)) {
      $user = new User($role);
    } else {
      $user = $vote_target;
    }
    $poison_target_list = RoleManager::LoadMain($user)->GetPoisonVoteTarget($stack);
    //Text::p($poison_target_list, '◆Target [poison]');
    if (count($poison_target_list) < 1) return;

    $poison_target = DB::$USER->ByID(Lottery::Get($poison_target_list)); //対象者を決定
    if ($poison_target->IsActive('resist_wolf')) { //抗毒判定
      $poison_target->LostAbility();
      return;
    }
    DB::$USER->Kill($poison_target->id, 'POISON_DEAD'); //死亡処理

    $role = 'chain_poison'; //連毒者の処理
    if ($poison_target->IsRole($role)) RoleManager::GetClass($role)->Poison($poison_target);
  }

  //処刑者カウンター処理
  private static function FilterVoteKillCounter() {
    $target = RoleManager::Stack()->Get('vote_kill_user');
    $stack  = array_keys(RoleManager::Stack()->Get('vote_target'), $target->uname); //投票者
    RoleManager::SetActor($target);
    foreach (RoleManager::Load('vote_kill_counter') as $filter) {
      $filter->VoteKillCounter($stack);
    }
  }

  //特殊投票発動者の処理
  private static function FilterVoteAction() {
    $target = RoleManager::Stack()->Get('vote_kill_user');
    $target->stolen_flag = false;
    foreach (RoleManager::LoadFilter('vote_action') as $filter) {
      $filter->VoteAction();
    }
  }

  //霊能者系の処理
  private static function FilterNecromancer() {
    //火車の妨害判定
    $vote_target = RoleManager::Stack()->Get('vote_kill_user');
    $stolen_flag = DB::$ROOM->IsEvent('corpse_courier_mad') || $vote_target->stolen_flag;

    $role_flag   = new StdClass();
    $wizard_flag = new StdClass();
    foreach (RoleFilterData::$necromancer as $role) { //対象役職を初期化
      $role_flag->$role   = false;
      $wizard_flag->$role = false;
    }
    foreach (DB::$USER->role as $role => $list) {
      if (RoleData::IsMain($role)) $role_flag->$role = true;
    }
    //Text::p($role_flag, '◆ROLE_FLAG');

    $role = 'mimic_wizard';
    if (isset($role_flag->$role)) { //物真似師の処理
      RoleManager::GetClass($role)->Necromancer($vote_target, $stolen_flag);
    }

    $role = 'spiritism_wizard';
    if (isset($role_flag->$role)) {  //交霊術師の処理
      $filter = RoleManager::LoadMain(new User($role)); //$actor 参照あり
      $wizard_flag->{$filter->SetWizard()} = true;
      $wizard_result = $filter->result_list[0];
      if (isset($wizard_flag->sex_necromancer)) {
	$result = $filter->Necromancer($vote_target, $stolen_flag);
	DB::$ROOM->ResultAbility($wizard_result, $result, $vote_target->GetName());
      }
    }

    $name = $vote_target->GetName();
    foreach (RoleFilterData::$necromancer as $role) {
      if ($role_flag->$role || $wizard_flag->$role) {
	$filter = RoleManager::GetClass($role);
	$result = $filter->Necromancer($vote_target, $stolen_flag);
	if (is_null($result)) continue;

	if ($role_flag->$role) {
	  DB::$ROOM->ResultAbility($filter->result, $result, $name);
	}

	if ($wizard_flag->$role) {
	  DB::$ROOM->ResultAbility($wizard_result, $result, $name);
	}
      }
    }
  }

  //得票カウンター処理
  private static function FilterVotedReaction() {
    foreach (RoleManager::LoadFilter('voted_reaction') as $filter) {
      $filter->VotedReaction();
    }
  }

  //ショック死処理
  private static function FilterSuddenDeath() {
    //判定用データを登録 (投票者対象ユーザ名 => 人数)
    $stack = array_count_values(RoleManager::Stack()->Get('vote_target'));
    RoleManager::Stack()->Set('count', $stack);
    //RoleManager::Stack()->p('count', '◆count');

    //青天の霹靂判定
    $user_list = RoleManager::Stack()->Get('user_list');
    RoleManager::Stack()->Init('thunderbolt');
    if (DB::$ROOM->IsEvent('thunderbolt')) {
      RoleManager::GetClass('thunder_brownie')->SetThunderboltTarget($user_list);
    }
    else {
      foreach (RoleManager::LoadFilter('thunderbolt') as $filter) {
	$filter->SetThunderbolt($user_list);
      }
    }
    //RoleManager::Stack()->p('thunderbolt', '◆ThunderboltTarget');

    foreach (RoleManager::Stack()->Get('live_uname') as $uname) {
      $actor = DB::$USER->ByUname($uname); //live_uname は仮想ユーザ名
      $actor->cured_flag = false;
      RoleManager::SetActor($actor);

      //青天の霹靂判定
      $data = in_array($uname, RoleManager::Stack()->Get('thunderbolt')) ? 'THUNDERBOLT' : null;
      RoleManager::Stack()->Set('sudden_death', $data);

      if (! DB::$ROOM->IsEvent('no_sudden_death')) { //凪ならスキップ
	foreach (RoleManager::Load('sudden_death_sub') as $filter) { //サブ役職判定
	  $filter->SuddenDeath();
	}
      }

      foreach (RoleManager::Load('sudden_death_main') as $filter) { //メイン役職判定
	$filter->SuddenDeath();
      }

      //天狗陣営の判定
      if ($actor->IsMainCamp('tengu')) RoleManager::LoadMain($actor)->SuddenDeath();

      if (RoleManager::Stack()->IsEmpty('sudden_death')) continue;

      foreach (RoleManager::LoadFilter('cure') as $filter) { //薬師系の治療判定
	$filter->Cure();
      }

      if (! RoleManager::GetActor()->cured_flag) {
	$id = RoleManager::GetActor()->id;
	DB::$USER->SuddenDeath($id, 'SUDDEN_DEATH', RoleManager::Stack()->Get('sudden_death'));
      }
    }
  }

  //道連れ処理
  private static function FilterFollowed() {
    $user_list = RoleManager::Stack()->Get('user_list');
    foreach (RoleManager::LoadFilter('followed') as $filter) {
      $filter->Followed($user_list);
    }
  }

  //薬師系の鑑定結果を登録
  private static function FilterSaveResult() {
    $role = 'pharmacist';
    $name = $role . '_result';
    //RoleManager::Stack()->p($name, "◆Result [{$role}]");
    if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass($role)->SaveResult();
    RoleManager::Stack()->Clear($name);
  }

  //処刑得票カウンターの処理
  private static function FilterVoteKillReaction() {
    foreach (RoleManager::LoadFilter('vote_kill_reaction') as $filter) {
      $filter->VoteKillReaction();
    }
  }

  //天候処理
  private static function FilterWeather() {
    if (DB::$ROOM->IsEvent('frostbite')) { //雪
      $stack = array();
      foreach (RoleManager::Stack()->Get('user_list') as $id => $uname) {
	$user = DB::$USER->ByID($id);
	if ($user->IsLive(true) && ! $user->IsAvoid(true)) $stack[] = $user->id;
      }
      //Text::p($stack, '◆Target [frostbite]');
      DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, 'frostbite');
    }
    elseif (DB::$ROOM->IsEvent('psycho_infected')) { //濃霧
      $stack = array();
      foreach (RoleManager::Stack()->Get('user_list') as $id => $uname) {
	$user = DB::$USER->ByID($id);
	if ($user->IsLive(true) && ! $user->IsAvoid(true) &&
	    ! $user->IsRole('psycho_infected') && ! $user->IsCamp('vampire')) {
	  $stack[] = $user->id;
	}
      }
      //Text::p($stack, '◆Target [psycho_infected]');
      DB::$USER->ByID(Lottery::Get($stack))->AddRole('psycho_infected');
    }
  }

  //処刑キャンセル処理
  private static function FilterVoteCancel() {
    $target = RoleManager::Stack()->Get('vote_kill_user');
    foreach (RoleFilterData::$vote_cancel as $role) {
      if ($target->IsRole($role)) RoleManager::GetClass($role)->VoteCancel($target);
    }
  }

  //ランダムメッセージを挿入する
  private static function InsertRandomMessage() {
    if (GameConfig::RANDOM_MESSAGE) {
      DB::$ROOM->Talk(Lottery::Get(Message::$random_message_list));
    }
  }
}

//-- 投票処理クラス (夜) --//
class VoteNight extends VoteBase {
  //実行処理
  static function Execute() {
    //-- イベント名と役職の整合チェック --//
    $filter = self::GetFilter();
    if (empty(RQ::Get()->situation)) {
      VoteHTML::OutputResult(VoteMessage::VOTE_NIGHT_EMPTY);
    }
    elseif (RQ::Get()->situation == RoleManager::Stack()->Get('not_action')) {
      $not_action = true;
    }
    elseif (RQ::Get()->situation != RoleManager::Stack()->Get('action')) {
      VoteHTML::OutputResult(VoteMessage::INVALID_VOTE_NIGHT);
    }
    else {
      $add_action = RoleManager::Stack()->Get('add_action');
      if (RQ::Get()->add_action && isset($add_action)) RQ::Set('situation', $add_action);
      $not_action = false;
    }
    //Text::p($filter);
    if (! DB::$ROOM->IsTest()) self::CheckVote(RQ::Get()->situation); //投票済みチェック

    //-- 投票処理 --//
    if ($not_action) { //投票キャンセルタイプは何もしない
      if (! DB::$SELF->Vote(RQ::Get()->situation)) { //投票処理
	VoteHTML::OutputResult(VoteMessage::DB_ERROR);
      }
      $id = DB::$SELF->role_id;
      DB::$ROOM->Talk('', RQ::Get()->situation, DB::$SELF->uname, '', null, null, $id);
    }
    else {
      $filter->CheckVoteNight();
      //RoleManager::Stack()->p();
      if (! DB::$SELF->Vote(RQ::Get()->situation, RoleManager::Stack()->Get('target_no'))) {
	VoteHTML::OutputResult(VoteMessage::DB_ERROR);
      }
      $str    = RoleManager::Stack()->Get('target_handle');
      $action = RoleManager::Stack()->Get('message');
      DB::$ROOM->Talk($str, $action, DB::$SELF->uname, '', null, null, DB::$SELF->role_id);
    }

    if (DB::$ROOM->IsTest()) return;
    self::Aggregate(); //集計処理
    foreach (DB::$USER->rows as $user) $user->UpdatePlayer(); //player 更新
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }

  //役職クラス取得
  static function GetFilter() {
    if (DB::$SELF->IsDummyBoy()) VoteHTML::OutputResult(VoteMessage::DUMMY_BOY_NIGHT);
    foreach (array('', 'not_') as $header) {   //データを初期化
      foreach (array('action', 'submit') as $data) {
	RoleManager::Stack()->Set($header . $data, null);
      }
    }

    if ($death_note = DB::$SELF->IsDoomRole('death_note')) { //デスノート
      /*
	配役設定上、初日に配布されることはなく、バグで配布された場合でも
	集計処理は実施されないので、ここではそのまま投票させておく。
	逆にスキップ判定を実施した場合、初日投票能力者が詰む。
      */
      //if (DB::$ROOM->IsDate(1)) VoteHTML::OutputResult('夜：初日は暗殺できません');
      if (DB::$ROOM->IsTest() || ! self::CheckSelfVote('DEATH_NOTE_DO', 'DEATH_NOTE_NOT_DO')) {
	$filter = RoleManager::LoadMain(new User('mage')); //上記のバグ対策用 (本来は assassin 相当)
	RoleManager::SetActor(DB::$SELF); //同一ユーザ判定用
	RoleManager::Stack()->Set('action',     'DEATH_NOTE_DO');
	RoleManager::Stack()->Set('not_action', 'DEATH_NOTE_NOT_DO');
      }
      else {
	$death_note = false;
      }
    }
    if (! $death_note) {
      $filter = RoleManager::LoadMain(DB::$SELF);
      $filter->SetVoteNight();
    }

    return $filter;
  }

  //投票済みチェック
  static function CheckVote($action, $not_action = '') {
    if (self::CheckSelfVote($action, $not_action)) {
      VoteHTML::OutputResult(VoteMessage::ALREAY_VOTE_NIGHT);
    }
  }

  //集計処理
  static function Aggregate($skip = false) {
    //-- 投票データ収集 --//
    RoleManager::Stack()->Set('skip', $skip);
    if (! self::LoadVote()) return false;

    self::InitVote();
    self::InitStack();
    self::FilterWeather();
    self::FilterWizard();

    //-- 足音レイヤー --//
    self::FilterStep();

    //-- 接触レイヤー --//
    self::LoadWolf();
    if (DB::$ROOM->date > 1) {
      self::LoadTrap();
      self::LoadGuard();
      self::LoadEscape();
    }

    self::FilterWolfEat();
    if (DB::$ROOM->date > 1) {
      self::FilterDeathNote();
      self::FilterHunt();
      self::FilterDelayTrapKill();
      self::FilterVampire();
      self::FilterAssassin();
      self::FilterOgre();
      self::FilterDeathSelected();
      self::FilterReverseAssassin();
      self::FilterFrostbite();

      //-- 夢レイヤー --//
      self::FilterDreamEat();
      self::FilterDreamHunt();

      //-- 呪いレイヤー --//
      self::LoadAntiVoodoo();
    }

    self::FilterVoodooKiller();
    self::LoadVoodoo();

    //-- 占いレイヤー --//
    self::LoadJammer();
    self::FilterMage();
    self::FilterMageKill();

    if (DB::$ROOM->IsDate(1)) {
      //-- 透視レイヤー --//
      self::FilterMindScan();

      //-- コピーレイヤー --//
      self::FilterCopy();

      //-- 帰還レイヤー --//
      self::FilterPriestReturn();

      self::FilterLotteryLovers(); //恋人抽選処理

      //-- 天狗 --//
      self::FilterSetTenguCamp();
    }
    else {
      //-- 尾行レイヤー --//
      self::FilterReport();
    }

    //-- 反魂レイヤー --//
    self::FilterResurrect();

    if (DB::$ROOM->date > 1) {
      self::FilterReverseResurrect();

      //-- 蘇生レイヤー --//
      self::FilterRevive();

      //-- 憑依レイヤー --//
      self::LoadPossessed();
    }
    self::FilterPossessed();

    self::SaveSuccess(); //成功結果記録

    //-- 変化レイヤー --//
    switch (DB::$ROOM->date) {
    case 3:
      self::FilterDelayCopy();
      break;

    case 4:
      self::FilterChange();
      break;
    }

    //-- 後追いレイヤー --//
    self::FilterFollowed();
    self::FilterLastWords();

    //-- 司祭レイヤー --//
    if (DB::$ROOM->date > 1) self::FilterNecromancerNight();
    self::FilterPriest();
    self::FilterWolfEatFailedCounter();

    //-- 日付変更処理 --//
    $status = DB::$ROOM->ChangeDate();
    if (DB::$ROOM->IsTest() || ! $status) self::ResetJoker();
    self::ResetDeathNote();
    self::SaveEvent();

    return $status;
  }

  //未投票チェック (本人)
  private static function CheckSelfVote($situation, $not_situation = '') {
    return count(DB::$SELF->LoadVote($situation, $not_situation)) > 0;
  }

  //投票情報取得
  private static function LoadVote() {
    DB::$ROOM->LoadVote(); //投票情報を取得
    //DB::$ROOM->Stack()->p('vote', '◆VoteRow');

    $vote_data = DB::$ROOM->ParseVote(); //コマンド毎に分割
    //Text::p($vote_data, '◆VoteData');

    RoleManager::Stack()->Set('vote_data', $vote_data);
    if (RoleManager::Stack()->Get('skip')) return true;

    foreach (DB::$USER->rows as $user) { //未投票チェック
      if ($user->CheckVote($vote_data) === false) {
	if (DB::$ROOM->IsTest()) Text::p($user->uname, $user->main_role);
	return false;
      }
    }
    return true;
  }

  //投票データ初期化
  private static function InitVote() {
    //処理対象コマンドチェック
    $stack = array('MAGE_DO', 'STEP_MAGE_DO', 'VOODOO_KILLER_DO', 'MIND_SCANNER_DO', 'WOLF_EAT',
		   'STEP_WOLF_EAT', 'SILENT_WOLF_EAT', 'JAMMER_MAD_DO', 'VOODOO_MAD_DO', 'STEP_DO',
		   'VOODOO_FOX_DO', 'CHILD_FOX_DO', 'FAIRY_DO', 'TENGU_DO');
    if (DB::$ROOM->IsDate(1)) {
      $stack[] = 'MANIA_DO';
    }
    else {
      array_push($stack, 'GUARD_DO', 'STEP_GUARD_DO', 'ANTI_VOODOO_DO', 'REPORTER_DO',
		 'STEP_SCANNER_DO', 'POISON_CAT_DO', 'ASSASSIN_DO', 'STEP_ASSASSIN_DO',
		 'WIZARD_DO', 'SPREAD_WIZARD_DO', 'ESCAPE_DO', 'DREAM_EAT', 'TRAP_MAD_DO',
		 'POSSESSED_DO', 'VAMPIRE_DO', 'STEP_VAMPIRE_DO', 'OGRE_DO', 'DEATH_NOTE_DO');
    }

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($stack as $action) {
      if (! isset($vote_data[$action])) $vote_data[$action] = array();
    }
    //Text::p($vote_data, '◆VoteData [init]');

    RoleManager::Stack()->Set('vote_data', $vote_data);
  }

  //変数の初期化
  private static function InitStack() {
    $stack = array('trap', 'trapped', 'snow_trap', 'frostbite', 'guard', 'gatekeeper_guard',
		   'dummy_guard', 'barrier_wizard', 'escaper', 'sacrifice', 'anti_voodoo',
		   'anti_voodoo_success', 'reverse_assassin', 'possessed');
    foreach ($stack as $name) RoleManager::Stack()->Init($name);
  }

  //天候の処理
  private static function FilterWeather() {
    $stack = array();
    if (DB::$ROOM->IsEvent('full_moon')) { //満月
      array_push($stack, 'GUARD_DO', 'STEP_GUARD_DO', 'ANTI_VOODOO_DO', 'REPORTER_DO',
		 'JAMMER_MAD_DO', 'VOODOO_MAD_DO', 'VOODOO_FOX_DO');
    }
    elseif (DB::$ROOM->IsEvent('new_moon')) { //新月
      RoleManager::Stack()->Set('skip', true); //影響範囲に注意
      array_push($stack, 'MAGE_DO', 'STEP_MAGE_DO', 'VOODOO_KILLER_DO', 'WIZARD_DO',
		 'SPREAD_WIZARD_DO', 'CHILD_FOX_DO', 'VAMPIRE_DO', 'STEP_VAMPIRE_DO',
		 'FAIRY_DO', 'TENGU_DO');
    }
    elseif (DB::$ROOM->IsEvent('no_contact')) { //花曇 (さとり系に注意)
      RoleManager::Stack()->Set('skip', true); //影響範囲に注意
      array_push($stack, 'STEP_GUARD_DO', 'REPORTER_DO', 'ASSASSIN_DO', 'STEP_ASSASSIN_DO',
		 'MIND_SCANNER_DO', 'STEP_SCANNER_DO', 'ESCAPE_DO', 'TRAP_MAD_DO', 'VAMPIRE_DO',
		 'STEP_VAMPIRE_DO', 'OGRE_DO');
    }
    elseif (DB::$ROOM->IsEvent('no_trap')) { //雪明り
      $stack[] = 'TRAP_MAD_DO';
    }
    elseif (DB::$ROOM->IsEvent('no_dream')) { //熱帯夜
      $stack[] = 'DREAM_EAT';
    }

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($stack as $action) {
      $vote_data[$action] = array();
    }
    //Text::p($vote_data, '◆VoteData [weather]');

    RoleManager::Stack()->Set('vote_data', $vote_data);
  }

  //魔法使い系の振り替え処理
  private static function FilterWizard() {
    if (DB::$ROOM->date < 2) return;

    $action    = 'WIZARD_DO';
    $vote_data = RoleManager::Stack()->Get('vote_data');
    if (count($vote_data[$action]) < 1) return;

    foreach ($vote_data[$action] as $id => $target_id) {
      $action = RoleManager::LoadMain(DB::$USER->ByID($id))->SetWizard();
      //Text::p(RoleManager::GetActor()->virtual_role, "◆Wizard: {$id}: {$action}");
      $vote_data[$action][$id] = $target_id;
    }
    RoleManager::Stack()->Set('vote_data', $vote_data);
  }

  //足音レイヤー処理
  private static function FilterStep() {
    if (DB::$ROOM->IsEvent('no_step')) return; //地吹雪は無効

    $stack = array('STEP_MAGE_DO', 'STEP_WOLF_EAT', 'STEP_DO');
    if (DB::$ROOM->date > 1) {
      array_push($stack, 'STEP_GUARD_DO', 'STEP_ASSASSIN_DO', 'STEP_VAMPIRE_DO');
    }

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($stack as $action) { //足音処理
      foreach ($vote_data[$action] as $id => $target_id) {
	RoleManager::LoadMain(DB::$USER->ByID($id))->Step(explode(' ', $target_id));
      }
    }

    if (DB::$ROOM->IsDate(1)) {
      foreach (array('lute_mania', 'harp_mania') as $role) { //コピー型の処理
	foreach (DB::$USER->GetRoleUser($role) as $user) {
	  if (! $user->IsDummyBoy()) RoleManager::LoadMain($user)->Step();
	}
      }
    }

    if (DB::$ROOM->IsEvent('random_step')) { //霜柱の処理
      $stack = array();
      foreach (DB::$USER->rows as $user) {
	if (DB::$USER->IsVirtualLive($user->id)) $stack[] = $user->id;
      }
      //Text::p($stack, '◆random_step');

      $count = 0;
      foreach (Lottery::GetList($stack) as $id) {
	if (! Lottery::Percent(20)) continue;
	DB::$ROOM->ResultDead($id, 'STEP');
	if (++$count > 2) break;
      }
    }

    foreach ($vote_data['SILENT_WOLF_EAT'] as $id => $target_id) { //ステルス投票カウントアップ
      DB::$USER->ByID($id)->LostAbility();
    }
  }

  //人狼の情報収集
  private static function LoadWolf() {
    $vote_data   = RoleManager::Stack()->Get('vote_data');
    $wolf_target = null;
    foreach (array('WOLF_EAT', 'STEP_WOLF_EAT', 'SILENT_WOLF_EAT') as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	switch ($action) {
	case 'WOLF_EAT':
	  $wolf_target = DB::$USER->ByID($target_id);
	  break;

	case 'STEP_WOLF_EAT':
	case 'SILENT_WOLF_EAT':
	  $wolf_target = DB::$USER->ByID(array_pop(explode(' ', $target_id))); //響狼は最終投票者
	  break;
	}
      }

      if (isset($wolf_target)) {
	$voted_wolf = DB::$USER->ByID($id);
	break;
      }
    }

    if (is_null($wolf_target)) {
      $wolf_target = new User();
      $voted_wolf  = new User();
    }

    RoleManager::Stack()->Set('wolf_target', $wolf_target);
    RoleManager::Stack()->Set('voted_wolf',  $voted_wolf);
  }

  //罠能力者の情報収集
  private static function LoadTrap() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['TRAP_MAD_DO'] as $id => $target_id) { //設置処理
      RoleManager::LoadMain(DB::$USER->ByID($id))->SetTrap(DB::$USER->ByID($target_id));
    }

    $role = 'trap_wolf'; //狡狼の自動設置処理 (花曇・雪明りは無効)
    if (DB::$ROOM->date > 2 && ! DB::$ROOM->IsEvent('no_contact') &&
	! DB::$ROOM->IsEvent('no_trap') && DB::$USER->IsAppear($role)) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	if ($user->IsLive()) RoleManager::LoadMain($user)->SetTrap();
      }
    }

    if (RoleManager::Stack()->Exists('trap')) RoleManager::SetClass('trap_mad');
    foreach (RoleManager::LoadFilter('trap') as $filter) $filter->TrapToTrap(); //罠能力者の罠判定
    //RoleManager::Stack()->p('trap',      '◆Target [trap]');
    //RoleManager::Stack()->p('snow_trap', '◆Target [snow_trap]');
    //RoleManager::Stack()->p('trapped',   '◆Trap [trap]');
    //RoleManager::Stack()->p('frostbite', '◆Trap [frostbite]');
  }

  //護衛能力者の情報収集
  private static function LoadGuard() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['GUARD_DO'] as $id => $target_id) { //護衛能力者の情報収集
      RoleManager::LoadMain(DB::$USER->ByID($id))->SetGuard(DB::$USER->ByID($target_id));
    }

    foreach ($vote_data['STEP_GUARD_DO'] as $id => $target_id) { //山立の情報収集
      $target = DB::$USER->ByID(array_pop(explode(' ', $target_id)));
      RoleManager::LoadMain(DB::$USER->ByID($id))->SetGuard($target);
    }
    if (RoleManager::Stack()->Exists('guard')) RoleManager::SetClass('guard');
    //RoleManager::Stack()->p('guard',            '◆Target [guard]');
    //RoleManager::Stack()->p('gatekeeper_guard', '◆Target [gatekeeper_guard]');
    //RoleManager::Stack()->p('dummy_guard',      '◆Target [dummy_guard]');

    foreach ($vote_data['SPREAD_WIZARD_DO'] as $id => $target_list) { //結界師の情報収集
      RoleManager::LoadMain(DB::$USER->ByID($id))->SetGuard($target_list);
    }
    //RoleManager::Stack()->p('barrier_wizard', '◆Target [barrier]');
  }

  //逃亡者系の情報収集
  private static function LoadEscape() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['ESCAPE_DO'] as $id => $target_id) {
      RoleManager::LoadMain(DB::$USER->ByID($id))->Escape(DB::$USER->ByID($target_id));
    }
    //RoleManager::Stack()->p('escaper', '◆Target [escaper]');
  }

  //人狼襲撃処理
  private static function FilterWolfEat() {
    RoleManager::GetClass('wolf')->WolfEat();
    //RoleManager::Stack()->p('possessed', '◆Possessed [wolf]');
  }

  //デスノートの処理
  private static function FilterDeathNote() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['DEATH_NOTE_DO'] as $id => $target_id) {
      if (DB::$USER->ByID($id)->IsDead(true)) continue; //直前に死んでいたら無効
      DB::$USER->Kill($target_id, 'ASSASSIN_KILLED');
    }
  }

  //狩人系の狩り判定
  private static function FilterHunt() {
    if (DB::$ROOM->IsEvent('no_hunt')) return; //川霧ならスキップ

    foreach (RoleManager::Stack()->Get('guard') as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->Hunt(DB::$USER->ByID($target_id));
    }
  }

  //罠死処理
  private static function FilterDelayTrapKill() {
    foreach (RoleManager::LoadFilter('trap') as $filter) {
      $filter->DelayTrapKill();
    }
  }

  //吸血処理
  private static function FilterVampire() {
    $role = 'vampire';
    $name = $role . '_kill';
    RoleManager::Stack()->Init($role); //吸血対象者リスト
    RoleManager::Stack()->Init($name); //吸血死対象者リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['VAMPIRE_DO'] as $id => $target_id) { //吸血鬼の処理
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetInfect(DB::$USER->ByID($target_id));
    }

    foreach ($vote_data['STEP_VAMPIRE_DO'] as $id => $target_id) { //文武王の情報収集
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      $target = DB::$USER->ByID(array_pop(explode(' ', $target_id)));
      RoleManager::LoadMain($user)->SetInfect($target);
    }

    foreach (RoleManager::LoadFilter('trap') as $filter) $filter->DelayTrapKill(); //罠死処理

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    if (RoleManager::Stack()->Exists($role) || RoleManager::Stack()->Exists($name)) {
      RoleManager::GetClass($role)->VampireKill();
    }
    RoleManager::Stack()->Clear($role);
    RoleManager::Stack()->Clear($name);
  }

  //暗殺処理
  private static function FilterAssassin() {
    $role = 'assassin';
    RoleManager::Stack()->Init($role); //暗殺対象者リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['ASSASSIN_DO'] as $id => $target_id) { //暗殺能力者の処理
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetAssassin(DB::$USER->ByID($target_id));
    }

    foreach ($vote_data['STEP_ASSASSIN_DO'] as $id => $target_id) { //封印の処理
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetStepAssassin(explode(' ', $target_id));
    }

    foreach (RoleManager::LoadFilter('trap') as $filter) $filter->DelayTrapKill(); //罠死処理

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($role)) RoleManager::GetClass($role)->AssassinKill();
    RoleManager::Stack()->Clear($role);
  }

  //人攫い処理
  private static function FilterOgre() {
    $role = 'ogre';
    RoleManager::Stack()->Init($role); //人攫い対象者リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['OGRE_DO'] as $id => $target_id) { //鬼の処理
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetAssassin(DB::$USER->ByID($target_id));
    }

    foreach (RoleManager::LoadFilter('trap') as $filter) $filter->DelayTrapKill(); //罠死処理

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($role)) RoleManager::GetClass($role)->AssassinKill();
    RoleManager::Stack()->Clear($role);
  }

  //オシラ遊びの処理
  private static function FilterDeathSelected() {
    $role = 'death_selected';
    foreach (DB::$USER->rows as $user) {
      if ($user->IsDead(true)) continue;
      if ($user->GetVirtual()->IsDoomRole($role)) DB::$USER->Kill($user->id, 'PRIEST_RETURNED');
    }
  }

  //反魂師の暗殺処理
  private static function FilterReverseAssassin() {
    $role = 'reverse_assassin';
    $name = 'reverse';
    RoleManager::Stack()->Init($name); //反魂対象リスト
    if (RoleManager::Stack()->Exists($role)) RoleManager::GetClass($role)->AssassinKill();
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    RoleManager::Stack()->Clear($role);
  }

  //凍傷処理
  private static function FilterFrostbite() {
    $role = 'frostbite';
    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    foreach (RoleManager::Stack()->Get($role) as $id => $flag) {
      $target = DB::$USER->ByID($id);
      if ($target->IsLive(true)) $target->AddDoom(1, $role);
    }
    RoleManager::Stack()->Clear($role);
  }

  //獏の処理
  private static function FilterDreamEat() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['DREAM_EAT'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->DreamEat(DB::$USER->ByID($target_id));
    }
  }

  //夢狩り処理
  private static function FilterDreamHunt() {
    $hunted_list = array(); //狩り成功者リスト
    $filter_list = RoleManager::LoadFilter('guard_dream');
    foreach ($filter_list as $filter) $filter->DreamGuard($hunted_list);
    foreach ($filter_list as $filter) $filter->DreamHunt($hunted_list);
  }

  //厄神の情報収集
  private static function LoadAntiVoodoo() {
    $role = 'anti_voodoo';
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['ANTI_VOODOO_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetGuard(DB::$USER->ByID($target_id));
    }
    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
  }

  //陰陽師の処理
  private static function FilterVoodooKiller() {
    $role = 'voodoo_killer';
    $name = $role . '_success';
    RoleManager::Stack()->Init($role); //解呪対象リスト
    RoleManager::Stack()->Init($name); //解呪成功者対象リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['VOODOO_KILLER_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->Mage(DB::$USER->ByID($target_id));
    }
    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    //RoleManager::Stack()->p($name, "◆Success [{$role}]");
  }

  //呪術系能力者の情報収集
  private static function LoadVoodoo() {
    $name = 'voodoo';
    RoleManager::Stack()->Init($name); //呪術対象リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach (array('VOODOO_MAD_DO', 'VOODOO_FOX_DO') as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->SetVoodoo(DB::$USER->ByID($target_id));
      }
    }
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    //RoleManager::Stack()->p('voodoo_killer_success', "◆Success [voodoo_killer/{$name}]");
    //RoleManager::Stack()->p('anti_voodoo_success',   "◆Success [anti_voodoo/{$name}]");

    //呪術系能力者の対象先が重なった場合は呪返しを受ける
    if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass('voodoo_mad')->VoodooToVoodoo();
  }

  //占い妨害能力者の情報収集
  private static function LoadJammer() {
    $name = 'jammer';
    RoleManager::Stack()->Init($name); //占い妨害対象リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['JAMMER_MAD_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetJammer(DB::$USER->ByID($target_id));
    }
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    //RoleManager::Stack()->p('anti_voodoo_success',   "◆Success [anti_voodoo/{$name}]");
  }

  //占い処理
  private static function FilterMage() {
    $name = 'phantom';
    RoleManager::Stack()->Init($name);   //幻系の発動者リスト
    RoleManager::Stack()->Init('mage_kill'); //呪殺対象者リスト

    //占い系の処理
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach (array('MAGE_DO', 'CHILD_FOX_DO', 'FAIRY_DO', 'TENGU_DO') as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->Mage(DB::$USER->ByID($target_id));
      }
    }
    foreach ($vote_data['STEP_MAGE_DO'] as $id => $target_id) { //審神者の処理
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->Mage(DB::$USER->ByID(array_pop(explode(' ', $target_id))));
    }

    //幻系の能力失効処理
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    foreach (array_keys(RoleManager::Stack()->Get($name)) as $id) {
      DB::$USER->ByID($id)->LostAbility();
    }
    RoleManager::Stack()->Clear($name);

    //天候判定
    if (DB::$ROOM->IsEvent('star_fairy')) {
      $role = 'star_fairy';
    } elseif (DB::$ROOM->IsEvent('flower_fairy')) {
      $role = 'flower_fairy';
    } else {
      return;
    }
    RoleManager::GetClass($role)->FairyEvent();
  }

  //呪殺処理
  private static function FilterMageKill() {
    $name = 'mage_kill';
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass('mage')->MageKill();
    RoleManager::Stack()->Clear($name);
  }

  //さとり系の処理
  private static function FilterMindScan() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['MIND_SCANNER_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->MindScan(DB::$USER->ByID($target_id));
    }
  }

  //神話マニアの処理
  private static function FilterCopy() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['MANIA_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->Copy(DB::$USER->ByID($target_id));
    }
  }

  //天人の帰還処理
  private static function FilterPriestReturn() {
    if (DB::$ROOM->IsOpenCast()) return;

    foreach (RoleFilterData::$priest_return as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	RoleManager::LoadMain($user)->PriestReturn();
      }
    }
  }

  //恋人抽選処理
  private static function FilterLotteryLovers() {
    foreach (RoleFilterData::$lottery_lovers as $role) {
      if (DB::$USER->IsAppear($role)) RoleManager::GetClass($role)->LotteryLovers();
    }
  }

  //天狗所属陣営判定処理
  private static function FilterSetTenguCamp() {
    $filter = RoleManager::GetClass('tengu');
    $filter->SetWinCamp();
  }

  //ブン屋・猩々・雷神
  private static function FilterReport() {
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach (array('REPORTER_DO', 'MIND_SCANNER_DO', 'STEP_SCANNER_DO') as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効

	switch ($action) {
	case 'STEP_SCANNER_DO': //雷神
	  $target_list = explode(' ', $target_id);
	  foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
	    foreach ($target_list as $target_id) {
	      if ($filter->DelayTrap($user, $target_id)) continue 4;
	    }
	  }
	  RoleManager::LoadMain($user)->StepScan($target_list);
	  break;

	default:
	  foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
	    if ($filter->TrapKill($user, $target_id)) continue 3;
	  }
	  RoleManager::LoadMain($user)->Report(DB::$USER->ByID($target_id));
	  break;
	}
      }

      if ($action == 'STEP_SCANNER_DO') {
	foreach (RoleManager::LoadFilter('trap') as $filter) $filter->DelayTrapKill(); //罠死処理
      }
    }
  }

  //反魂処理
  private static function FilterResurrect() {
    if (DB::$ROOM->IsEvent('no_revive')) return; //快晴なら無効

    RoleManager::SetActor(RoleManager::Stack()->Get('wolf_target'));
    foreach (RoleManager::Load('resurrect') as $filter) $filter->Resurrect();

    foreach (DB::$USER->GetRoleUser('revive_wolf') as $user) { //仙狼の処理
      RoleManager::LoadMain($user)->Resurrect();
    }
  }

  //反魂師の反魂処理
  private static function FilterReverseResurrect() {
    $role = 'reverse_assassin';
    $name = 'reverse';
    if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass($role)->Resurrect();
    RoleManager::Stack()->Clear($name);
  }

  //蘇生能力者の処理
  private static function FilterRevive() {
    if (DB::$ROOM->IsOpenCast()) return;

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['POISON_CAT_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->Revive(DB::$USER->ByID($target_id));
    }
  }

  //憑依能力者の情報収集
  private static function LoadPossessed() {
    $role = 'possessed_mad';
    $name = 'possessed_dead';
    RoleManager::Stack()->Init($name); //有効憑依情報リスト

    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach ($vote_data['POSSESSED_DO'] as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true) || $user->revive_flag) continue; //直前に死亡・蘇生なら無効
      RoleManager::LoadMain($user)->SetPossessed(DB::$USER->ByID($target_id));
    }
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass($role)->Possessed();
    RoleManager::Stack()->Clear($name);
    //RoleManager::Stack()->p('possessed', '◆Possessed [mad/fox]');
  }

  //憑依処理
  private static function FilterPossessed() {
    $role = 'possessed_wolf';
    $name = 'possessed';
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass($role)->Possessed();
    RoleManager::Stack()->Clear($name);
  }

  //陰陽師・厄神の成功結果登録
  private static function SaveSuccess() {
    if (DB::$ROOM->IsOption('seal_message')) return;

    foreach (array('voodoo_killer', 'anti_voodoo') as $role) {
      $name = $role . '_success';
      //RoleManager::Stack()->p($name, "◆Success [{$role}]");
      if (RoleManager::Stack()->Exists($name)) RoleManager::GetClass($role)->SaveSuccess();
      RoleManager::Stack()->Clear($name);
    }
  }

  //時間差コピー能力者のコピー処理
  private static function FilterDelayCopy() {
    foreach (RoleFilterData::$delay_copy as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	if ($user->IsDummyBoy()) continue;
	if (is_null($id = $user->GetMainRoleTarget())) continue;
	RoleManager::LoadMain($user)->DelayCopy(DB::$USER->ByID($id));
      }
    }
  }

  //昼狐の変化処理
  private static function FilterChange() {
    foreach (DB::$USER->GetRoleUser('vindictive_fox') as $user) {
      RoleManager::LoadMain($user)->Change();
    }
  }

  //後追い処理
  private static function FilterFollowed() {
    RoleManager::GetClass('lovers')->Followed();
    RoleManager::GetClass('medium')->InsertResult();
  }

  //特殊遺言処理
  private static function FilterLastWords() {
    RoleManager::GetClass('letter_exchange')->UpdateLastWords();
  }

  //霊能 (夜発動型)
  private static function FilterNecromancerNight() {
    foreach (RoleFilterData::$necromancer_night as $role) {
      if (DB::$USER->IsAppear($role)) {
	RoleManager::GetClass($role)->NecromancerNight();
      }
    }
  }

  //司祭の処理
  private static function FilterPriest() {
    $role = 'priest';
    RoleManager::GetClass($role)->AggregatePriest();
    //RoleManager::Stack()->p($role, "◆List [{$role}]");
    //Text::p(RoleManager::Stack()->Get($role)->list,   "◆List [{$role}]");
    //Text::p(RoleManager::Stack()->Get($role)->count,  '◆List [live]');
    //Text::p(RoleManager::Stack()->Get($role)->crisis, '◆List [crisis]');
    foreach (RoleManager::Stack()->Get($role)->list as $role) {
      RoleManager::GetClass($role)->Priest();
    }
  }

  //人狼襲撃失敗カウンター処理
  private static function FilterWolfEatFailedCounter() {
    if (RoleManager::Stack()->Get('wolf_target')->IsDead(true)) return;
    foreach (RoleFilterData::$wolf_eat_failed_counter as $role) {
      if (DB::$USER->IsAppear($role)) {
	RoleManager::GetClass($role)->WolfEatFailedCounter();
      }
    }
  }

  //ジョーカー再配布
  private static function ResetJoker() {
    if (DB::$ROOM->IsOption('joker')) {
      RoleManager::GetClass('joker')->ResetJoker(true);
    }
  }

  //デスノート再配布
  private static function ResetDeathNote() {
    if (DB::$ROOM->IsOption('death_note')) {
      RoleManager::GetClass('death_note')->ResetDeathNote();
    }
  }

  //イベント登録
  private static function SaveEvent() {
    $stack = RoleManager::Stack()->Get('event');
    //Text::p($stack, '◆Event');
    if (! isset($stack)) return;

    $wolf_target = RoleManager::Stack()->Get('wolf_target');
    foreach (array_unique($stack) as $event) {
      switch ($event) {
      case 'same_face':
	$type = 'SAME_FACE';
	$str  = $wolf_target->id;
	break;

      default:
	$type = 'EVENT';
	$str  = $event;
	break;
      }
      DB::$ROOM->SystemMessage($str, $type);
    }
  }
}

//-- 投票処理クラス (死者) --//
class VoteHeaven extends VoteBase {
  //実行処理
  static function Execute() {
    //-- 無効判定 --//
    self::CheckSituation('REVIVE_REFUSE'); //コマンドチェック
    if (DB::$SELF->IsDrop())     VoteHTML::OutputResult(VoteMessage::ALREADY_DROP);
    if (DB::$ROOM->IsOpenCast()) VoteHTML::OutputResult(VoteMessage::ALREADY_OPEN);

    //-- 投票処理 --//
    if (! DB::$SELF->UpdateLive(UserLive::DROP)) VoteHTML::OutputResult(VoteMessage::DB_ERROR);

    //システムメッセージ
    $str = sprintf(VoteMessage::REVIVE_REFUSE_SUCCESS, DB::$SELF->handle_name);
    DB::$ROOM->Talk($str, null, DB::$SELF->uname, RoomScene::HEAVEN, null, TalkVoice::NORMAL);
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }
}

//-- 投票処理クラス (身代わり君) --//
class VoteDummyBoy extends VoteBase {
  //最終更新時刻リセット
  static function ResetTime() {
    self::CheckSituation('RESET_TIME'); //コマンドチェック

    //-- 投票処理 --//
    RoomDB::UpdateTime(); //更新時間リセット

    //システムメッセージ
    $str = VoteMessage::RESET_TIME_SUCCESS;
    DB::$ROOM->Talk($str, null, DB::$SELF->uname, DB::$ROOM->scene, GM::DUMMY_BOY);
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }
}

//-- HTML 生成クラス (投票拡張) --//
class VoteHTML {
  //結果出力
  static function OutputResult($str, $reset = false) {
    if ($reset) RoomDB::DeleteVote(); //今までの投票を全部削除
    HTML::OutputResult(ServerConfig::TITLE . VoteMessage::RESULT, self::GenerateResult($str));
  }

  //エラーページ出力
  static function OutputError($title, $str = null) {
    if (is_null($str)) $str = VoteMessage::BUG;
    HTML::OutputResult(sprintf(VoteMessage::ERROR_TITLE, $title), self::GenerateResult($str));
  }

  //開始前の投票ページ出力
  static function OutputBeforeGame() {
    self::CheckScene(); //投票する状況があっているかチェック
    self::OutputHeader();
    Text::Output('<input type="hidden" name="situation" value="KICK_DO">');
    Text::Output('<table class="vote-page"><tr>');

    $count  = 0;
    $header = '<input type="radio" name="target_no" id="';
    $path   = Icon::GetPath();
    foreach (DB::$USER->rows as $id => $user) {
      if ($count > 0 && $count % 5 == 0) Text::Output(Text::TR); //5個ごとに改行
      $count++;

      if (! $user->IsDummyBoy() && (GameConfig::SELF_KICK || ! $user->IsSelf())) {
	$checkbox = $header . $id . '" value="' . $id . '">' . Text::LF;
      } else {
	$checkbox = '';
      }
      echo $user->GenerateVoteTag($path . $user->icon_filename, $checkbox);
    }

    $format = <<<EOF
</tr></table>
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td class="add-action"><input type="submit" value="%s"></form></td>
<td>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="situation" value="GAMESTART">
<input type="submit" value="%s">
</form>
</td>
</tr></table></div>
EOF;
    printf($format . Text::LF,
	   sprintf(VoteMessage::CAUTION_KICK, GameConfig::KICK),
	   RQ::Get()->back_url, VoteMessage::KICK_DO, RQ::Get()->post_url,
	   VoteMessage::GAME_START);
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //昼の投票ページを出力する
  static function OutputDay() {
    self::CheckScene(); //投票シーンチェック
    if (DB::$ROOM->IsDate(1)) self::OutputResult(VoteMessage::NEEDLESS_VOTE);
    if (! DB::$ROOM->IsTest() && UserDB::IsVoteKill()) { //投票済みチェック
      self::OutputResult(VoteMessage::ALREADY_VOTE);
    }

    //特殊イベントを参照して投票対象をセット
    if (DB::$ROOM->IsEvent('vote_duel')) {
      $user_stack = array();
      foreach (DB::$ROOM->Stack()->Get('vote_duel') as $id) {
	$user_stack[$id] = DB::$USER->rows[$id];
      }
    } else {
      $user_stack = DB::$USER->rows;
    }
    $virtual_self = DB::$SELF->GetVirtual(); //仮想投票者を取得

    self::OutputHeader();
    $format = <<<EOF
<input type="hidden" name="situation" value="VOTE_KILL">
<input type="hidden" name="revote_count" value="%d">
<table class="vote-page"><tr>
EOF;
    printf($format . Text::LF, DB::$ROOM->revote_count);

    $checkbox_header = Text::LF . '<input type="radio" name="target_no" id="';
    $count = 0;
    $base_path = Icon::GetPath();
    $dead_icon = Icon::GetDead();
    foreach ($user_stack as $id => $user) {
      if ($count > 0 && $count % 5 == 0) Text::Output(Text::TR); //5個ごとに改行
      $count++;
      $is_live = DB::$USER->IsVirtualLive($id);

      //生きていればユーザアイコン、死んでれば死亡アイコン
      $path = $is_live ? $base_path . $user->icon_filename : $dead_icon;
      if ($is_live && ! $user->IsSame($virtual_self)) {
	$checkbox = $checkbox_header . $id . '" value="' . $id . '">';
      } else {
	$checkbox = '';
      }
      echo $user->GenerateVoteTag($path, $checkbox);
    }

    $format = <<<EOF
</tr></table>
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td><input type="submit" value="%s"></td>
</tr></table></div>
</form>
EOF;
    printf($format . Text::LF, VoteMessage::CAUTION, RQ::Get()->back_url, VoteMessage::VOTE_DO);
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //夜の投票ページを出力する
  static function OutputNight() {
    self::CheckScene(); //投票シーンチェック
    //-- 投票済みチェック --//
    $filter = VoteNight::GetFilter();
    if (! DB::$ROOM->IsTest()) {
      $action     = RoleManager::Stack()->Get('action');
      $not_action = RoleManager::Stack()->Get('not_action');
      VoteNight::CheckVote($action, $not_action);
    }

    self::OutputHeader();
    //Text::p($filter);
    //RoleManager::Stack()->p();
    Text::Output('<table class="vote-page"><tr>');
    $count = 0;
    foreach ($filter->GetVoteTargetUser() as $id => $user) {
      if ($count > 0 && $count % 5 == 0) Text::Output(Text::TR); //5個ごとに改行
      $count++;
      $live = DB::$USER->IsVirtualLive($id);
      /*
	死者は死亡アイコン (蘇生能力者は死亡アイコンにしない)
	生存者はユーザアイコン (狼仲間なら狼アイコン)
      */
      $path     = $filter->GetVoteIconPath($user, $live);
      $checkbox = $filter->GetVoteCheckbox($user, $id, $live);
      echo $user->GenerateVoteTag($path, $checkbox);
    }

    $format = <<<EOF
</tr></table>
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<input type="hidden" name="situation" value="%s">
<td><input type="submit" value="%s"></td>
EOF;
    if (is_null(RoleManager::Stack()->Get('submit'))) {
      RoleManager::Stack()->Set('submit', RoleManager::Stack()->Get('action'));
    }
    $submit = strtoupper(RoleManager::Stack()->Get('submit'));
    printf($format . Text::LF, VoteMessage::CAUTION, RQ::Get()->back_url,
	   RoleManager::Stack()->Get('action'), VoteRoleMessage::$$submit);

    $add_action = RoleManager::Stack()->Get('add_action');
    if (isset($add_action)) {
      $format = <<<EOF
<td class="add-action">
<input type="checkbox" name="add_action" id="add_action" value="on">
<label for="add_action">%s</label>
</td>
</form>
EOF;
      if (is_null(RoleManager::Stack()->Get('add_submit'))) {
	RoleManager::Stack()->Set('add_submit', RoleManager::Stack()->Get('add_action'));
      }
      $add_submit = strtoupper(RoleManager::Stack()->Get('add_submit'));
      printf($format . Text::LF, VoteRoleMessage::$$add_submit);
    } else {
      Text::Output('</form>');
    }

    $not_action = RoleManager::Stack()->Get('not_action');
    if (isset($not_action)) {
      $format = <<<EOF
<td class="add-action">
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="situation" value="%s">
<input type="hidden" name="target_no" value="%d">
<input type="submit" value="%s"></form>
</td>
EOF;
      if (is_null(RoleManager::Stack()->Get('not_submit'))) {
	RoleManager::Stack()->Set('not_submit', RoleManager::Stack()->Get('not_action'));
      }
      $not_submit = strtoupper(RoleManager::Stack()->Get('not_submit'));
      printf($format . Text::LF, RQ::Get()->post_url, RoleManager::Stack()->Get('not_action'),
	     DB::$SELF->id, VoteRoleMessage::$$not_submit);
    }

    Text::Output('</tr></table></div>');
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //死者の投票ページ出力
  static function OutputHeaven() {
    //投票済みチェック
    if (DB::$SELF->IsDrop())     self::OutputResult(VoteMessage::ALREADY_DROP);
    if (DB::$ROOM->IsOpenCast()) self::OutputResult(VoteMessage::ALREADY_OPEN);

    self::OutputHeader();
    $format = <<<EOF
<input type="hidden" name="situation" value="REVIVE_REFUSE">
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td><input type="submit" value="%s"></form></td>
</tr></table></div>
EOF;
    printf($format . Text::LF,
	   VoteMessage::CAUTION, RQ::Get()->back_url, VoteMessage::REVIVE_REFUSE);
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //身代わり君 (霊界) の投票ページ出力
  static function OutputDummyBoy() {
    self::OutputHeader();
    $format = <<<EOF
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td>
<input type="hidden" name="situation" value="RESET_TIME">
<input type="submit" value="%s">
</form>
</td>
EOF;
    printf($format . Text::LF, VoteMessage::CAUTION, RQ::Get()->back_url, VoteMessage::RESET_TIME);

    //蘇生辞退ボタン表示判定
    if (! DB::$SELF->IsDrop() && DB::$ROOM->IsOption('not_open_cast') &&
	! DB::$ROOM->IsOpenCast()) {
      $format = <<<EOF
<td>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="situation" value="REVIVE_REFUSE">
<input type="submit" value="%s">
</form>
</td>
EOF;
      printf($format . Text::LF, RQ::Get()->post_url, VoteMessage::REVIVE_REFUSE);
    }
    Text::Output('</tr></table></div>');
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //シーンの一致チェック
  private static function CheckScene() {
    if (! DB::$SELF->CheckScene()) self::OutputResult(VoteMessage::RELOAD);
  }

  //結果生成
  private static function GenerateResult($str) {
    $format = '<div id="game_top" align="center">%s' . Text::BRLF . '%s</div>';
    return sprintf($format, $str, RQ::Get()->back_url);
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . VoteMessage::TITLE, 'game');
    HTML::OutputCSS(sprintf('%s/game_vote', JINROU_CSS));
    Text::Output('<link rel="stylesheet" id="scene">');
    $css = empty(DB::$ROOM->scene) ? null : sprintf('%s/game_%s', JINROU_CSS, DB::$ROOM->scene);
    HTML::OutputBodyHeader($css);
    $format = <<<EOF
<a id="game_top"></a>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
EOF;
    printf($format . Text::LF, RQ::Get()->post_url);
  }
}
