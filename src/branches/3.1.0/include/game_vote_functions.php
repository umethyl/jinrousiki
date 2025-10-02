<?php
//-- 投票処理基礎クラス --//
class VoteBase {
  //投票コマンドチェック
  protected static function CheckSituation($situation) {
    if (! Security::CheckHash(DB::$ROOM->id)) HTML::OutputUnusableError(); //CSRF対策

    if (is_array($situation)) {
      if (in_array(RQ::Get()->situation, $situation)) return true;
    } else {
      if (RQ::Get()->situation == $situation) return true;
    }
    VoteHTML::OutputResult(VoteMessage::INVALID_SITUATION);
  }

  //音声用データセット
  protected static function FilterSound() {
    if (RQ::Get()->play_sound) {
      Loader::LoadFile('cookie_class');
      JinrouCookie::SetVote(DB::$ROOM->scene);
    }
  }
}

//-- 投票処理クラス (ゲーム開始) --//
class VoteGameStart extends VoteBase {
  //実行処理
  public static function Execute() {
    self::CheckSituation(VoteAction::GAME_START);
    self::FilterDummyBoy();
    self::Load();
    self::Vote();
  }

  //集計処理
  public static function Aggregate($force_start = false) {
    Cast::Stack()->Set(Cast::FORCE, $force_start);
    if (! self::Check()) return false;

    //-- 配役決定ルーチン --//
    DB::$ROOM->LoadOption(); //配役設定オプションの情報を取得
    //Text::p(DB::$ROOM->option_role, '◆OptionRole');
    //Text::p(DB::$ROOM->option_list, '◆OptionList');

    Cast::Execute();
    //Cast::Stack()->p(Cast::UNAME, '◆Uname/End');
    //Cast::Stack()->p(Cast::CAST, '◆Role/End');
    //RoomDB::DeleteVote(); return false; //テスト用

    Cast::Save();
    DB::$USER->UpdateKick(); //KICK の後処理
    DB::$ROOM->Start();
    return true;
  }

  //身代わり君処理
  private static function FilterDummyBoy() {
    if (! DB::$SELF->IsDummyBoy(true)) return; //出題者はスキップ

    if (GameConfig::POWER_GM) { //強権モードによる強制開始処理
      $str = self::Aggregate(true) ? VoteMessage::SUCCESS : VoteMessage::GAME_START_SHORTAGE;
      DB::Commit();
      self::Output($str);
    } else {
      self::Output(VoteMessage::GAME_START_DUMMY_BOY);
    }
  }

  //投票情報ロード
  private static function Load() {
    DB::$ROOM->LoadVote();
  }

  //投票処理
  private static function Vote() {
    if (DB::$SELF->ExistsVote()) {
      self::Output(VoteMessage::ALREADY_GAME_START);
    } elseif (DB::$SELF->Vote(VoteAction::GAME_START)) {
      self::Aggregate();
      DB::Commit();
      self::FilterSound();
      self::Output(VoteMessage::SUCCESS);
    } else {
      self::Output(VoteMessage::DB_ERROR);
    }
  }

  //投票数チェック
  private static function Check() {
    $user_count = DB::$USER->Count(); //ユーザ総数を取得
    $vote_count = self::CountVote($user_count);

    //規定人数に足りないか、全員投票していなければ処理終了
    if ($vote_count != $user_count || $vote_count < ArrayFilter::GetMin(CastConfig::$role_list)) {
      return false;
    }
    Cast::Stack()->Set(Cast::COUNT, $user_count);
    return true;
  }

  //投票数取得
  private static function CountVote($user_count) {
    if (DB::$ROOM->IsTest()) return $user_count;

    self::CheckSituation(VoteAction::GAME_START);
    if (Cast::Stack()->Get(Cast::FORCE)) return $user_count; //強制開始モード時はスキップ

    $count = DB::$ROOM->LoadVote(); //投票情報をロード (ロック前の情報は使わない事)
    //クイズ村以外の身代わり君を加算
    if (DB::$ROOM->IsDummyBoy() && ! DB::$ROOM->IsQuiz()) $count++;
    return $count;
  }

  //結果出力
  private static function Output($str) {
    VoteHTML::OutputResult(VoteMessage::GAME_START_TITLE . $str);
  }
}

//-- 投票処理クラス (キック) --//
class VoteKick extends VoteBase {
  //実行処理
  public static function Execute() {
    self::CheckSituation(VoteAction::KICK);
    self::Vote();
  }

  //投票処理
  private static function Vote() {
    $target = self::Load();
    if (DB::$SELF->Vote(VoteAction::KICK, $target->id)) {
      DB::$ROOM->Talk($target->handle_name, VoteAction::KICK, DB::$SELF->uname); //投票通知
      $vote_count = self::Aggregate($target); //集計処理
      DB::Commit();
      $format = VoteMessage::SUCCESS . VoteMessage::KICK_SUCCESS;
      self::Output(sprintf($format, $target->handle_name, $vote_count, GameConfig::KICK));
    } else {
      self::Output(VoteMessage::DB_ERROR);
    }
  }

  //データロード
  private static function Load() {
    $target = DB::$USER->ByID(RQ::Get()->target_no); //投票先ユーザ
    self::Check($target);

    DB::$ROOM->LoadVote(true); //投票情報ロード
    $stack = DB::$ROOM->Stack()->GetKey('vote', DB::$SELF->id);
    if (! is_null($stack) && in_array($target->id, $stack)) {
      self::Output($target->handle_name . VoteMessage::ALREADY_KICK);
    }
    return $target;
  }

  //投票先チェック
  private static function Check(User $target) {
    if (is_null($target->id) || $target->live == UserLive::KICK) {
      self::Output(VoteMessage::KICK_EMPTY);
    } elseif ($target->IsDummyBoy()) {
      self::Output(VoteMessage::KICK_DUMMY_BOY);
    } elseif (! GameConfig::SELF_KICK && $target->IsSelf()) {
      self::Output(VoteMessage::KICK_SELF);
    }
  }

  //集計処理 (返り値 : $target への投票合計数)
  private static function Aggregate(User $target) {
    self::CheckSituation(VoteAction::KICK); //コマンドチェック

    //投票先への合計投票数を取得
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

  //結果出力
  private static function Output($str) {
    VoteHTML::OutputResult(VoteMessage::KICK_TITLE . $str);
  }
}

//-- 投票処理クラス (昼) --//
class VoteDay extends VoteBase {
  //実行処理
  public static function Execute() {
    self::CheckSituation(VoteAction::VOTE_KILL);
    self::Load();
    self::Vote();
  }

  //集計処理
  public static function Aggregate() {
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
    RoleVote::VoteKillCorrect();

    //-- 処刑者決定 --//
    self::SaveResultVote();
    self::DecideVoteKill();
    //RoleManager::Stack()->p('vote_kill_uname', '◆VoteTarget');

    //-- 処刑実行処理 --//
    if (! RoleManager::Stack()->IsEmpty('vote_kill_uname')) {
      self::VoteKill(); //処刑実行

      //-- 毒関連能力の処理 --//
      RoleVote::SetDetox();
      self::FilterVoteKillPoison();
      //RoleManager::Stack()->p('pharmacist_result', '◆EndDetox');

      RoleVote::VoteKillCounter();
      RoleVote::VoteKillAction();
      RoleVote::Necromancer();
    }

    RoleVote::VotePollReaction();
    self::FilterSuddenDeath();
    RoleVote::VoteKillFollowed();
    self::FilterSaveResult();
    RoleVote::Followed();

    if (! RoleManager::Stack()->IsEmpty('vote_kill_uname')) { //夜に切り替え
      self::ChangeNight();
      if (DB::$ROOM->IsTest()) return RoleManager::Stack()->Get('vote_message');
      DB::$ROOM->SkipNight();
    } else { //再投票処理
      if (DB::$ROOM->IsTest()) return RoleManager::Stack()->Get('vote_message');
      self::Revote();
    }
    foreach (DB::$USER->Get() as $user) $user->UpdatePlayer(); //player 更新
    RoomDB::UpdateTime(); //最終書き込み時刻を更新
  }

  //データロード
  private static function Load() {
    RoleManager::Stack()->Set('target', DB::$USER->ByReal(RQ::Get()->target_no));
    self::CheckTarget();
    EventManager::VoteDuel();
    self::CheckVote();
  }

  //投票処理
  private static function Vote() {
    //-- 初期化 --//
    RoleManager::Stack()->Set('vote_number', 1);

    //-- 投票数補正 --//
    RoleVote::VoteDoMain();
    RoleVote::VoteDoSub();
    EventManager::VoteDo();

    //-- 処刑処理 --//
    $target      = RoleManager::Stack()->Get('target');
    $vote_number = max(0, RoleManager::Stack()->Get('vote_number'));
    if (! DB::$SELF->Vote(VoteAction::VOTE_KILL, $target->id, $vote_number)) {
      VoteHTML::OutputResult(VoteMessage::DB_ERROR);
    }
    if (DB::$ROOM->IsTest()) return true;

    //-- システムメッセージ --//
    DB::$ROOM->Talk($target->GetName(), VoteAction::VOTE, DB::$SELF->uname);

    //-- 集計処理 --//
    self::Aggregate();
    DB::Commit();
    self::FilterSound();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }

  //投票先チェック
  private static function CheckTarget() {
    $target = RoleManager::Stack()->Get('target');
    if (is_null($target->id)) {
      VoteHTML::OutputResult(VoteMessage::INVALID_VOTE);
    } elseif ($target->IsSelf()) {
      VoteHTML::OutputResult(VoteMessage::VOTE_SELF);
    } elseif ($target->IsDead()) {
      VoteHTML::OutputResult(VoteMessage::VOTE_DEAD);
    }
  }

  //投票チェック
  private static function CheckVote() {
    if (DB::$ROOM->IsTest()) {
      if (isset(RQ::GetTest()->vote->day[DB::$SELF->uname])) {
	Text::p(DB::$SELF->uname, '★AlreadyVoted');
	return false;
      } else {
	return true;
      }
    } elseif (DB::$ROOM->revote_count != RQ::Get()->revote_count) {
      VoteHTML::OutputResult(VoteMessage::INVALID_COUNT);
    } elseif (UserDB::IsVoteKill()) {
      VoteHTML::OutputResult(VoteMessage::ALREADY_VOTE);
    }
  }

  //沈黙禁止処理
  private static function FilterNoSilence() {
    if (DB::$ROOM->IsOption('no_silence')) {
      OptionLoader::Load('no_silence')->SilenceKill();
    }
  }

  //集計実行判定
  private static function CheckAggregate() {
    if (! DB::$ROOM->IsTest()) self::CheckSituation(VoteAction::VOTE_KILL); //コマンドチェック

    $user_list  = DB::$USER->SearchLive(true); //生存者
    $vote_count = DB::$ROOM->LoadVote();       //投票数
    if (DB::$ROOM->IsOption('no_silence')) {   //沈黙死した人の投票を除く
      $vote_count -= OptionLoader::Load('no_silence')->CountSilence();
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
    if (DB::$ROOM->IsOption('joker')) RoleLoader::Load('joker')->InitializeJoker();
  }

  //初期得票データ収集
  private static function InitVoteCount() {
    $stack = array(); //得票リスト (ユーザ名 => 投票数)
    $no_silence = DB::$ROOM->IsOption('no_silence');
    foreach (DB::$ROOM->Stack()->Get('vote') as $id => $list) {
      $target_id = $list['target_no'];
      if ($no_silence && DB::$USER->ByReal($target_id)->IsOn(UserMode::SUICIDE)) { //沈黙死判定
	continue;
      }
      ArrayFilter::Add($stack, DB::$USER->ByVirtual($target_id)->uname, $list['vote_number']);
    }
    RoleManager::Stack()->Set('vote_count', $stack);
  }

  //個別の投票データ収集
  private static function InitVoteData() {
    //-- 変数初期化 --//
    $no_silence        = DB::$ROOM->IsOption('no_silence'); //沈黙禁止
    $live_uname_list   = array(); //生存者リスト (ユーザ名)
    $vote_target_list  = array(); //投票リスト (ユーザ名 => 投票先ユーザ名)
    $vote_message_list = array(); //システムメッセージ用 (ユーザID => array())
    $vote_count_list   = RoleManager::Stack()->Get('vote_count');

    foreach (RoleManager::Stack()->Get('user_list') as $id => $uname) {
      $list      = DB::$ROOM->Stack()->GetKey('vote', $id);			//投票データ
      $virtual   = DB::$USER->ByVirtual($id);					//仮想ユーザ
      $target    = DB::$USER->ByVirtual($list['target_no']);			//投票先の仮想ユーザ
      $real      = DB::$USER->ByReal($virtual->id);				//実ユーザ
      $vote      = ArrayFilter::GetInt($list, 'vote_number');			//投票数
      $base_poll = ArrayFilter::GetInt($vote_count_list, $virtual->uname);	//得票数 (補正前)
      RoleManager::Stack()->Set('vote_poll', $base_poll);

      //-- 得票数補正 --//
      RoleVote::VotePollMain($real);
      RoleVote::VotePollSub($virtual);
      $poll = max(0, RoleManager::Stack()->Get('vote_poll'));

      //-- リストにデータを追加 --//
      $live_uname_list[$virtual->id]     = $virtual->uname;
      $vote_target_list[$virtual->uname] = $target->uname;
      $vote_count_list[$virtual->uname]  = $poll;
      $vote_message_list[$virtual->id]   = array(
	'target_name' => $target->handle_name,
	'vote'        => $vote,
	'poll'        => $poll
      );
      RoleVote::VoteKillWizard($real); //処刑魔法発動

      if ($no_silence && $target->GetReal()->IsOn(UserMode::SUICIDE)) { //沈黙死スキップ判定
	//Text::p($target->uname, '◆Skip [suicide]');
	continue;
      }

      //-- 処刑投票能力 --//
      RoleVote::VoteKillMain($real, $target);
      RoleVote::VoteKillSub($virtual, $target);
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

  //投票結果登録
  private static function SaveResultVote() {
    if (! DB::$ROOM->IsTest()) {
      $vote_count = DB::$ROOM->revote_count + 1;
      $items = 'room_no, date, count, handle_name, target_name, vote, poll';
      $values_header = sprintf('%d, %d, %d, ', DB::$ROOM->id, DB::$ROOM->date, $vote_count);
    }

    //タブ区切りのデータをシステムメッセージに登録
    $max_poll = 0; //最多得票数
    foreach (RoleManager::Stack()->Get('vote_message') as $uname => $stack) {
      extract($stack); //配列を展開
      $max_poll = max($poll, $max_poll); //最大得票数を更新
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
    $stack    = RoleManager::Stack()->GetKeyList('vote_count', $max_poll);
    RoleManager::Stack()->Set('max_voted', $stack);
    RoleManager::Stack()->Set('vote_kill_uname', null); //処刑者 (ユーザ名)
    //Text::p($stack, '◆MaxVoted');

    if (count($stack) == 1) { //一人だけなら決定
      RoleManager::Stack()->Set('vote_kill_uname', array_shift($stack));
    } else { //処刑者決定能力判定
      RoleManager::Stack()->Set('vote_possible', $stack);
      RoleVote::DecideVoteKill();
      EventManager::DecideVoteKill();
    }
  }

  //処刑実行
  private static function VoteKill() {
    $uname  = RoleManager::Stack()->Get('vote_kill_uname'); //ユーザ情報を取得
    $target = DB::$USER->ByRealUname($uname);
    DB::$USER->Kill($target->id, DeadReason::VOTE_KILLED); //処刑処理
    RoleManager::Stack()->Set('vote_kill_user', $target);

    //処刑者を生存者リストから除く
    $stack = RoleManager::Stack()->Get('live_uname');
    ArrayFilter::Delete($stack, $uname);
    RoleManager::Stack()->Set('live_uname', $stack);
  }

  //処刑者の毒処理
  private static function FilterVoteKillPoison() {
    //スキップ判定 (毒発動 > 解毒)
    if (! RoleUser::IsPoison(RoleManager::Stack()->Get('vote_kill_user'))) {
      return;
    } elseif (RoleVote::Detox()) {
      return;
    }

    //毒死候補者選出
    $stack = RoleVote::GetVoteKillPoisonTarget();
    //Text::p($stack, '◆Target [poison]');
    if (count($stack) < 1) return;

    $user = DB::$USER->ByID(Lottery::Get($stack)); //対象者を決定
    if (RoleVote::ResistVoteKillPoison($user)) return; //抗毒判定
    DB::$USER->Kill($user->id, DeadReason::POISON_DEAD); //死亡処理
    RoleVote::ChainPoison($user); //連毒
  }

  //ショック死処理
  private static function FilterSuddenDeath() {
    //判定用データを登録 (投票者対象ユーザ名 => 人数)
    $stack = array_count_values(RoleManager::Stack()->Get('vote_target'));
    RoleManager::Stack()->Set('count', $stack);
    //RoleManager::Stack()->p('count', '◆count');

    //青天の霹靂発動判定
    RoleVote::SetThunderbolt();
    //RoleManager::Stack()->p('thunderbolt', '◆ThunderboltTarget');

    foreach (RoleManager::Stack()->Get('live_uname') as $uname) {
      $user = DB::$USER->ByUname($uname); //live_uname は仮想ユーザ名
      $user->cured_flag = false;
      RoleLoader::SetActor($user);

      //ショック死判定 (青天の霹靂 > サブ > メイン > 天狗陣営)
      $type = in_array($uname, RoleManager::Stack()->Get('thunderbolt')) ? 'THUNDERBOLT' : null;
      RoleManager::Stack()->Set('sudden_death', $type);
      RoleVote::SuddenDeathSub();
      RoleVote::SuddenDeathMain();
      RoleVote::SuddenDeathTengu($user);
      if (RoleManager::Stack()->IsEmpty('sudden_death')) continue;

      //治療判定
      RoleVote::Cure();
      if ($user->cured_flag) continue;

      //ショック死処理
      $type = RoleManager::Stack()->Get('sudden_death');
      DB::$USER->SuddenDeath($user->id, DeadReason::SUDDEN_DEATH, $type);
    }
  }

  //薬師系の鑑定結果を登録
  private static function FilterSaveResult() {
    $role = 'pharmacist';
    $name = $role . '_result';
    //RoleManager::Stack()->p($name, "◆Result [{$role}]");
    if (RoleManager::Stack()->Exists($name)) RoleLoader::Load($role)->SaveResult();
    RoleManager::Stack()->Clear($name);
  }

  //夜に切り替え
  private static function ChangeNight() {
    RoleVote::VoteKillReaction();
    EventManager::VoteKillAction();
    RoleVote::VoteKillCancel();

    if ($joker_flag = DB::$ROOM->IsOption('joker')) { //ジョーカー移動判定
      $joker_filter = RoleLoader::Load('joker');
      $joker_flag   = $joker_filter->SetJoker();
    }

    DB::$ROOM->ChangeNight();
    if (Winner::Check()) { //勝敗判定
      if ($joker_flag) $joker_filter->FinishJoker();
    } else {
      if ($joker_flag) $joker_filter->ResetVoteJoker();
      self::InsertRandomMessage(); //ランダムメッセージ
    }
  }

  //再投票処理
  private static function Revote() {
    //処刑投票回数を増やす
    DB::$ROOM->revote_count++;
    RoomDB::UpdateVoteCount(true);
    DB::$ROOM->Talk(sprintf(VoteMessage::REVOTE, DB::$ROOM->revote_count)); //システムメッセージ

    if (Winner::Check(true) && DB::$ROOM->IsOption('joker')) { //勝敗判定＆ジョーカー処理
      RoleLoader::Load('joker')->FinishDrawJoker();
    }
  }

  //ランダムメッセージ挿入
  private static function InsertRandomMessage() {
    if (GameConfig::RANDOM_MESSAGE) {
      DB::$ROOM->Talk(Lottery::Get(Message::$random_message_list));
    }
  }
}

//-- 投票処理クラス (夜) --//
class VoteNight extends VoteBase {
  //実行処理
  public static function Execute() {
    self::Load();
    self::Vote();
  }

  //役職クラス取得
  public static function GetFilter() {
    if (DB::$SELF->IsDummyBoy()) VoteHTML::OutputResult(VoteMessage::DUMMY_BOY_NIGHT);
    foreach (array('', 'not_') as $header) {   //データを初期化
      foreach (array('action', 'submit') as $data) {
	RoleManager::Stack()->Set($header . $data, null);
      }
    }

    $death_note = false;
    foreach (RoleLoader::LoadUser(DB::$SELF, 'death_note') as $filter) {
      if (! $filter->IsVoteDeathNote()) continue;
      //Text::p(DB::$SELF->uname, "◆{$filter->role}");
      if (DB::$ROOM->IsTest() || ! self::CheckSelfVote($filter->action, $filter->not_action)) {
	$death_note = true;
	break;
      }
    }

    if (! $death_note) {
      $filter = RoleLoader::LoadMain(DB::$SELF);
    }
    $filter->SetVoteNight();

    return $filter;
  }

  //投票済みチェック
  public static function CheckVote($action, $not_action = '') {
    if (self::CheckSelfVote($action, $not_action)) {
      VoteHTML::OutputResult(VoteMessage::ALREAY_VOTE_NIGHT);
    }
  }

  //集計処理
  public static function Aggregate($skip = false) {
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
      self::LoadExit();
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

      //-- 恋人 --//
      self::FilterLotteryLovers();

      //-- 決闘者 --//
      self::FilterDuelist();

      //-- 天狗 --//
      self::FilterSetTenguCamp();
    } else {
      //-- 尾行レイヤー --//
      self::FilterReport();
    }

    //-- 反魂レイヤー --//
    self::FilterResurrect();

    if (DB::$ROOM->date > 1) {
      self::FilterReverseResurrect();

      //-- 蘇生レイヤー --//
      if (! DB::$ROOM->IsOpenCast()) {
	self::FilterGrave();
	self::FilterRevive();
      }

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
    RoleVote::Followed();
    self::FilterLastWords();

    //-- 司祭レイヤー --//
    if (DB::$ROOM->date > 1) {
      self::FilterNecromancerNight();
    }
    self::FilterPriest();
    self::FilterWolfEatFailedCounter();

    //-- 日付変更処理 --//
    $status = DB::$ROOM->ChangeDate();
    if (DB::$ROOM->IsTest() || ! $status) {
      self::ResetJoker();
    }
    self::ResetDeathNote();
    self::SaveEvent();

    return $status;
  }

  //スタックロード
  private static function Stack() {
    static $stack;

    if (is_null($stack)) {
      $stack = new Stack();
    }
    return $stack;
  }

  //データロード
  private static function Load() {
    self::Stack()->Set('filter', self::GetFilter());
    self::Stack()->Set('not_action', false);
    self::Check();
    //self::Stack()->p('filter', '◆Filter');
  }

  //投票先チェック
  private static function Check() {
    if (empty(RQ::Get()->situation)) {
      VoteHTML::OutputResult(VoteMessage::VOTE_NIGHT_EMPTY);
    } elseif (RQ::Get()->situation == RoleManager::Stack()->Get('not_action')) {
      self::Stack()->Set('not_action', true);
    } elseif (RQ::Get()->situation != RoleManager::Stack()->Get('action')) {
      VoteHTML::OutputResult(VoteMessage::INVALID_VOTE_NIGHT);
    } else {
      $add_action = RoleManager::Stack()->Get('add_action');
      if (RQ::Get()->add_action && isset($add_action)) {
	RQ::Set(RequestDataVote::SITUATION, $add_action);
      }
    }

    if (! DB::$ROOM->IsTest()) {
      self::CheckVote(RQ::Get()->situation); //投票済みチェック
    }
  }

  //投票処理
  private static function Vote() {
    if (self::Stack()->Get('not_action')) { //投票キャンセルタイプは何もしない
      if (! DB::$SELF->Vote(RQ::Get()->situation)) {
	VoteHTML::OutputResult(VoteMessage::DB_ERROR);
      }
      $str    = '';
      $action = RQ::Get()->situation;
    } else {
      self::Stack()->Get('filter')->CheckVoteNight();
      //RoleManager::Stack()->p();
      $target = RoleManager::Stack()->Get(RequestDataVote::TARGET);
      if (! DB::$SELF->Vote(RQ::Get()->situation, $target)) {
	VoteHTML::OutputResult(VoteMessage::DB_ERROR);
      }
      $str    = RoleManager::Stack()->Get('target_handle');
      $action = RoleManager::Stack()->Get('message');
    }
    DB::$ROOM->Talk($str, $action, DB::$SELF->uname, '', null, null, DB::$SELF->role_id);

    if (DB::$ROOM->IsTest()) return;
    self::Aggregate(); //集計処理
    foreach (DB::$USER->Get() as $user) $user->UpdatePlayer(); //player 更新
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
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

    RoleManager::SetVoteData($vote_data);
    if (RoleManager::Stack()->Get('skip')) return true;

    foreach (DB::$USER->Get() as $user) { //未投票チェック
      if (RoleUser::IsNoVote($user, $vote_data)) {
	if (DB::$ROOM->IsTest()) Text::p($user->uname, "★NoVote [{$user->main_role}]");
	return false;
      }
    }
    return true;
  }

  //投票データ初期化
  private static function InitVote() {
    //処理対象コマンドチェック
    $stack = VoteActionGroup::$init;
    if (DB::$ROOM->IsDate(1)) {
      ArrayFilter::Merge($stack, VoteActionGroup::$init_first);
    } else {
      ArrayFilter::Merge($stack, VoteActionGroup::$init_after);
    }
    $vote_data = RoleManager::GetVoteData();
    ArrayFilter::Initialize($vote_data, $stack);
    //Text::p($vote_data, '◆VoteData [init]');

    RoleManager::SetVoteData($vote_data);
  }

  //変数の初期化
  private static function InitStack() {
    $stack = array(
      RoleVoteTarget::TRAP,
      RoleVoteTarget::SNOW_TRAP,
      RoleVoteTarget::GUARD,
      RoleVoteTarget::GATEKEEPER_GUARD,
      RoleVoteTarget::DUMMY_GUARD,
      RoleVoteTarget::BARRIER_WIZARD,
      RoleVoteTarget::ESCAPER,
      RoleVoteTarget::SACRIFICE,
      RoleVoteTarget::REVERSE_ASSASSIN,
      RoleVoteTarget::ANTI_VOODOO,
      RoleVoteSuccess::TRAPPED,
      RoleVoteSuccess::FROSTBITE,
      RoleVoteSuccess::POSSESSED,
      RoleVoteSuccess::ANTI_VOODOO
    );
    foreach ($stack as $name) RoleManager::Stack()->Init($name);
  }

  //天候の処理
  private static function FilterWeather() {
    $stack = EventManager::SealVoteNight();
    //Text::p($stack, '◆VoteData [seal]');
    if (count($stack) < 1) return;

    $vote_data = RoleManager::GetVoteData();
    ArrayFilter::Reset($vote_data, $stack);
    //Text::p($vote_data, '◆VoteData [weather]');

    RoleManager::SetVoteData($vote_data);
  }

  //魔法使い系の振り替え処理
  private static function FilterWizard() {
    if (DB::$ROOM->date < 2) return;

    $action    = VoteAction::WIZARD;
    $vote_data = RoleManager::GetVoteData();
    if (count($vote_data[$action]) < 1) return;

    foreach ($vote_data[$action] as $id => $target_id) {
      $action = RoleLoader::LoadMain(DB::$USER->ByID($id))->SetWizard();
      //Text::p(RoleLoader::GetActor()->virtual_role, "◆Wizard: {$id}: {$action}");
      $vote_data[$action][$id] = $target_id;
    }
    RoleManager::SetVoteData($vote_data);
  }

  //足音レイヤー処理
  private static function FilterStep() {
    if (DB::$ROOM->IsEvent('no_step')) return; //地吹雪は無効

    $stack = VoteActionGroup::$step;
    if (DB::$ROOM->date > 1) {
      ArrayFilter::Merge($stack, VoteActionGroup::$step_after);
    }

    $vote_data = RoleManager::GetVoteData();
    foreach ($stack as $action) { //足音処理
      RoleVote::FilterNight($vote_data[$action], 'Step', 'none', 'multi');
    }

    if (DB::$ROOM->IsDate(1)) {
      foreach (RoleFilterData::$step_copy as $role) { //コピー型の処理
	foreach (DB::$USER->GetRoleUser($role) as $user) {
	  if (! $user->IsDummyBoy()) RoleLoader::LoadMain($user)->Step();
	}
      }
    }

    EventManager::Step(); //天候処理
    foreach ($vote_data[VoteAction::SILENT_WOLF] as $id => $target_id) { //ステルス投票カウントアップ
      DB::$USER->ByID($id)->LostAbility();
    }
  }

  //人狼の情報収集
  private static function LoadWolf() {
    $vote_data   = RoleManager::GetVoteData();
    $wolf_target = null;
    foreach (VoteActionGroup::$wolf as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	switch ($action) {
	case VoteAction::WOLF:
	  $wolf_target = DB::$USER->ByID($target_id);
	  break;

	case VoteAction::STEP_WOLF:
	case VoteAction::SILENT_WOLF:
	  $wolf_target = DB::$USER->ByID(Text::Cut($target_id, ' ')); //響狼は最終投票者
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
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::TRAP], 'SetTrap'); //設置処理

    $role = 'trap_wolf'; //狡狼の自動設置処理 (無効天候あり)
    if (DB::$ROOM->date > 2 && EventManager::IsSetTrap() && DB::$USER->IsAppear($role)) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	if ($user->IsLive()) RoleLoader::LoadMain($user)->SetAutoTrap();
      }
    }

    if (RoleManager::Stack()->Exists(RoleVoteTarget::TRAP)) {
      RoleLoader::Load('trap_mad');
    }
    foreach (RoleLoader::LoadFilter('trap') as $filter) { //罠能力者の罠判定
      $filter->TrapToTrap();
    }
    //RoleManager::Stack()->p(RoleVoteTarget::TRAP,       '◆Target [trap]');
    //RoleManager::Stack()->p(RoleVoteTarget::SNOW_TRAP,  '◆Target [snow_trap]');
    //RoleManager::Stack()->p(RoleVoteSuccess::TRAPPED,   '◆Trap [trap]');
    //RoleManager::Stack()->p(RoleVoteSuccess::FROSTBITE, '◆Trap [frostbite]');
  }

  //護衛能力者の情報収集
  private static function LoadGuard() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::GUARD],   'SetGuard'); //護衛能力者
    RoleVote::FilterNight($vote_data[VoteAction::STEP_GUARD], 'SetGuard', 'none', 'step'); //山立
    if (RoleManager::Stack()->Exists(RoleVoteTarget::GUARD)) {
      RoleLoader::Load('guard');
    }
    //RoleManager::Stack()->p(RoleVoteTarget::GUARD,            '◆Target [guard]');
    //RoleManager::Stack()->p(RoleVoteTarget::GATEKEEPER_GUARD, '◆Target [gatekeeper_guard]');
    //RoleManager::Stack()->p(RoleVoteTarget::DUMMY_GUARD,      '◆Target [dummy_guard]');

    foreach ($vote_data[VoteAction::SPREAD_WIZARD] as $id => $target_list) { //結界師
      RoleLoader::LoadMain(DB::$USER->ByID($id))->SetWizardGuard($target_list);
    }
    //RoleManager::Stack()->p(RoleVoteTarget::BARRIER_WIZARD, '◆Target [barrier]');
  }

  //離脱能力者の情報収集
  private static function LoadExit() {
    $vote_data = RoleManager::GetVoteData();
    foreach ($vote_data[VoteAction::EXIT_DO] as $id => $target_id) {
      RoleLoader::LoadMain(DB::$USER->ByID($id))->ExecuteExit();
    }
  }

  //逃亡者系の情報収集
  private static function LoadEscape() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::ESCAPE], 'Escape');
    //RoleManager::Stack()->p(RoleVoteTarget::ESCAPER, '◆Target [escaper]');
  }

  //人狼襲撃処理
  private static function FilterWolfEat() {
    RoleLoader::Load('wolf')->WolfEat();
    //RoleManager::Stack()->p(RoleVoteSuccess::POSSESSED, '◆Possessed [wolf]');
  }

  //デスノートの処理
  private static function FilterDeathNote() {
    $vote_data = RoleManager::GetVoteData();
    foreach ($vote_data[VoteAction::DEATH_NOTE] as $id => $target_id) {
      if (DB::$USER->ByID($id)->IsDead(true)) continue; //直前に死んでいたら無効
      DB::$USER->Kill($target_id, DeadReason::ASSASSIN_KILLED);
    }
  }

  //狩人系の狩り判定
  private static function FilterHunt() {
    if (DB::$ROOM->IsEvent('no_hunt')) return; //川霧ならスキップ
    RoleVote::FilterNight(RoleManager::Stack()->Get(RoleVoteTarget::GUARD), 'Hunt');
  }

  //罠死処理
  private static function FilterDelayTrapKill() {
    foreach (RoleLoader::LoadFilter('trap') as $filter) {
      $filter->DelayTrapKill();
    }
  }

  //吸血処理
  private static function FilterVampire() {
    $role = 'vampire';
    $name = RoleVoteSuccess::VAMPIRE_KILL;
    RoleManager::Stack()->Init($role); //吸血対象者リスト
    RoleManager::Stack()->Init($name); //吸血死対象者リスト

    $method = 'SetInfect';
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::VAMPIRE], $method); //吸血鬼の処理
    RoleVote::FilterNightStep($vote_data[VoteAction::STEP_VAMPIRE], $method); //文武王の処理
    self::FilterDelayTrapKill(); //罠死処理

    foreach (RoleFilterData::$guard_finish_action as $actor_role) {//護衛判定後処理
      if (RoleManager::Stack()->Exists($actor_role)) {
	RoleLoader::Load($actor_role)->GuardFinishAction();
      }
    }

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    if (RoleManager::Stack()->Exists($role) || RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load($role)->VampireKill();
    }
    RoleManager::Stack()->Clear($role);
    RoleManager::Stack()->Clear($name);
  }

  //暗殺処理
  private static function FilterAssassin() {
    $role = 'assassin';
    RoleManager::Stack()->Init($role); //暗殺対象者リスト

    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::ASSASSIN], 'SetAssassin');  //暗殺能力者の処理
    //風神の処理
    RoleVote::FilterNight($vote_data[VoteAction::STEP_ASSASSIN], 'SetStepAssassin', null, 'multi');
    self::FilterDelayTrapKill(); //罠死処理

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($role)) RoleLoader::Load($role)->AssassinKill();
    RoleManager::Stack()->Clear($role);
  }

  //人攫い処理
  private static function FilterOgre() {
    $role = 'ogre';
    RoleManager::Stack()->Init($role); //人攫い対象者リスト

    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::OGRE], 'SetOgreAssassin'); //鬼の処理
    self::FilterDelayTrapKill(); //罠死処理

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($role)) RoleLoader::Load($role)->OgreAssassinKill();
    RoleManager::Stack()->Clear($role);
  }

  //オシラ遊びの処理
  private static function FilterDeathSelected() {
    $role = 'death_selected';
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsDead(true)) continue;
      if ($user->GetVirtual()->IsDoomRole($role)) {
	DB::$USER->Kill($user->id, DeadReason::PRIEST_RETURNED);
      }
    }
  }

  //反魂師の暗殺処理
  private static function FilterReverseAssassin() {
    $role = 'reverse_assassin';
    $name = 'reverse';
    RoleManager::Stack()->Init($name); //反魂対象リスト
    if (RoleManager::Stack()->Exists(RoleVoteTarget::REVERSE_ASSASSIN)) {
      RoleLoader::Load($role)->AssassinKill();
    }
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    RoleManager::Stack()->Clear(RoleVoteTarget::REVERSE_ASSASSIN);
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
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::DREAM], 'DreamEat');
  }

  //夢狩り処理
  private static function FilterDreamHunt() {
    $hunted_list = array(); //狩り成功者リスト
    $filter_list = RoleLoader::LoadFilter('guard_dream');
    foreach ($filter_list as $filter) $filter->DreamGuard($hunted_list);
    foreach ($filter_list as $filter) $filter->DreamHunt($hunted_list);
  }

  //厄神の情報収集
  private static function LoadAntiVoodoo() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::ANTI_VOODOO], 'SetGuard');
    //RoleManager::Stack()->p(RoleVoteTarget::ANTI_VOODOO, '◆Target [anti_voodoo]');
  }

  //陰陽師の処理
  private static function FilterVoodooKiller() {
    $role = 'voodoo_killer';
    $name = RoleVoteSuccess::VOODOO_KILLER;
    RoleManager::Stack()->Init($role); //解呪対象リスト
    RoleManager::Stack()->Init($name); //解呪成功者対象リスト

    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::VOODOO_KILLER], 'MageVoodoo');
    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    //RoleManager::Stack()->p($name, "◆Success [{$role}]");
  }

  //呪術系能力者の情報収集
  private static function LoadVoodoo() {
    $name = 'voodoo';
    RoleManager::Stack()->Init($name); //呪術対象リスト

    $vote_data = RoleManager::GetVoteData();
    foreach (VoteActionGroup::$voodoo as $action) {
      RoleVote::FilterNight($vote_data[$action], 'SetVoodoo');
    }
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    //RoleManager::Stack()->p(RoleVoteSuccess::VOODOO_KILLER, "◆Success [voodoo_killer/{$name}]");
    //RoleManager::Stack()->p(RoleVoteSuccess::ANTI_VOODOO, "◆Success [anti_voodoo/{$name}]");

    //呪術系能力者の対象先が重なった場合は呪返しを受ける
    if (RoleManager::Stack()->Exists($name)) RoleLoader::Load('voodoo_mad')->VoodooToVoodoo();
  }

  //占い妨害能力者の情報収集
  private static function LoadJammer() {
    $name = 'jammer';
    RoleManager::Stack()->Init($name); //占い妨害対象リスト

    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::JAMMER], 'SetJammer');
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    //RoleManager::Stack()->p(RoleVoteSuccess::ANTI_VOODOO, "◆Success [anti_voodoo/{$name}]");
  }

  //占い処理
  private static function FilterMage() {
    $name = RoleVoteSuccess::PHANTOM;
    RoleManager::Stack()->Init($name);   //幻系の発動者リスト
    RoleManager::Stack()->Init('mage_kill'); //呪殺対象者リスト

    //占い系の処理
    $vote_data = RoleManager::GetVoteData();
    foreach (VoteActionGroup::$mage as $action) {
      RoleVote::FilterNight($vote_data[$action], 'Mage');
    }
    RoleVote::FilterNightStep($vote_data[VoteAction::STEP_MAGE], 'Mage'); //審神者の処理

    //幻系の能力失効処理
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    foreach (RoleManager::Stack()->GetKeyList($name) as $id) {
      DB::$USER->ByID($id)->LostAbility();
    }
    RoleManager::Stack()->Clear($name);

    //天候判定
    EventManager::TenguKill();
    EventManager::FairyMage();
  }

  //呪殺処理
  private static function FilterMageKill() {
    $name = 'mage_kill';
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
    if (RoleManager::Stack()->Exists($name)) RoleLoader::Load('mage')->MageKill();
    RoleManager::Stack()->Clear($name);
  }

  //さとり系の処理
  private static function FilterMindScan() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::SCAN], 'MindScan');
  }

  //神話マニアの処理
  private static function FilterCopy() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::MANIA], 'Copy');
  }

  //天人の帰還処理
  private static function FilterPriestReturn() {
    if (DB::$ROOM->IsOpenCast()) return;

    foreach (RoleFilterData::$priest_return as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	RoleLoader::LoadMain($user)->PriestReturn();
      }
    }
  }

  //恋人抽選処理
  private static function FilterLotteryLovers() {
    foreach (RoleFilterData::$lottery_lovers as $role) {
      if (DB::$USER->IsAppear($role)) RoleLoader::Load($role)->LotteryLovers();
    }
  }

  //決闘者陣営の処理
  private static function FilterDuelist() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::DUELIST], 'DuelistAction', null, 'direct');
  }

  //天狗所属陣営判定処理
  private static function FilterSetTenguCamp() {
    RoleLoader::Load('tengu')->SetWinCamp();
  }

  //ブン屋・猩々・雷神
  private static function FilterReport() {
    $vote_data = RoleManager::GetVoteData();
    foreach (VoteActionGroup::$report as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効

	switch ($action) {
	case VoteAction::STEP_SCAN: //雷神
	  $target_list = Text::Parse($target_id);
	  foreach (RoleLoader::LoadFilter('trap') as $filter) { //罠判定
	    foreach ($target_list as $target_id) {
	      if ($filter->DelayTrap($user, $target_id)) continue 4;
	    }
	  }
	  RoleLoader::LoadMain($user)->StepScan($target_list);
	  break;

	default:
	  foreach (RoleLoader::LoadFilter('trap') as $filter) { //罠判定
	    if ($filter->TrapKill($user, $target_id)) continue 3;
	  }
	  RoleLoader::LoadMain($user)->Report(DB::$USER->ByID($target_id));
	  break;
	}
      }

      if ($action == VoteAction::STEP_SCAN) { //遅行罠死処理 (凍傷型は無効)
	self::FilterDelayTrapKill();
      }
    }
  }

  //反魂処理
  private static function FilterResurrect() {
    if (DB::$ROOM->IsEvent('no_revive')) return; //快晴なら無効

    $actor = RoleManager::Stack()->Get('wolf_target');
    foreach (RoleLoader::LoadUser($actor, 'resurrect') as $filter) {
      $filter->Resurrect();
    }

    foreach (DB::$USER->GetRoleUser('revive_wolf') as $user) { //仙狼の処理
      RoleLoader::LoadMain($user)->Resurrect();
    }
  }

  //反魂師の反魂処理
  private static function FilterReverseResurrect() {
    $role = 'reverse_assassin';
    $name = 'reverse';
    if (RoleManager::Stack()->Exists($name)) RoleLoader::Load($role)->Resurrect();
    RoleManager::Stack()->Clear($name);
  }

  //死者妨害能力者の処理
  private static function FilterGrave() {
    $name = 'grave';
    RoleManager::Stack()->Init($name); //死者妨害リスト
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::GRAVE], 'SetGrave', 'inactive');
    //RoleManager::Stack()->p($name, "◆Target [{$name}]");
  }

  //蘇生能力者の処理
  private static function FilterRevive() {
    $action    = VoteAction::REVIVE;
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[$action], 'Revive', 'inactive');
    foreach (RoleFilterData::$revive_cancel as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	//未投票者のみ・直前に死んでいたら無効
	if (isset($vote_data[$action][$user->id]) || $user->IsDead(true)) continue;
	RoleLoader::LoadMain($user)->ReviveCancelAction();
      }
    }
  }

  //憑依能力者の情報収集
  private static function LoadPossessed() {
    $role = 'possessed_mad';
    $name = 'possessed_dead';
    RoleManager::Stack()->Init($name); //有効憑依情報リスト

    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::POSSESSED], 'SetPossessedDead', 'inactive');
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) RoleLoader::Load($role)->SetPossessed();
    RoleManager::Stack()->Clear($name);
    //RoleManager::Stack()->p(RoleVoteSuccess::POSSESSED, '◆Possessed [mad/fox]');
  }

  //憑依処理
  private static function FilterPossessed() {
    $role = 'possessed_wolf';
    $name = RoleVoteSuccess::POSSESSED;
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) RoleLoader::Load($role)->Possessed();
    RoleManager::Stack()->Clear($name);
  }

  //陰陽師・厄神の成功結果登録
  private static function SaveSuccess() {
    if (DB::$ROOM->IsOption('seal_message')) return;

    $stack = array(
      'voodoo_killer' => RoleVoteSuccess::VOODOO_KILLER,
      'anti_voodoo'   => RoleVoteSuccess::ANTI_VOODOO
    );
    foreach ($stack as $role => $name) {
      //RoleManager::Stack()->p($name, "◆Success [{$role}]");
      if (RoleManager::Stack()->Exists($name)) RoleLoader::Load($role)->SaveSuccess();
      RoleManager::Stack()->Clear($name);
    }
  }

  //時間差コピー能力者のコピー処理
  private static function FilterDelayCopy() {
    foreach (RoleFilterData::$delay_copy as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	if ($user->IsDummyBoy()) continue;
	if (is_null($id = $user->GetMainRoleTarget())) continue;
	RoleLoader::LoadMain($user)->DelayCopy(DB::$USER->ByID($id));
      }
    }
  }

  //昼狐の変化処理
  private static function FilterChange() {
    foreach (DB::$USER->GetRoleUser('vindictive_fox') as $user) {
      RoleLoader::LoadMain($user)->Change();
    }
  }

  //特殊遺言処理
  private static function FilterLastWords() {
    RoleLoader::Load('letter_exchange')->UpdateLastWords();
  }

  //霊能 (夜発動型)
  private static function FilterNecromancerNight() {
    foreach (RoleFilterData::$necromancer_night as $role) {
      if (DB::$USER->IsAppear($role)) {
	RoleLoader::Load($role)->NecromancerNight();
      }
    }
  }

  //司祭の処理
  private static function FilterPriest() {
    $role = 'priest';
    RoleLoader::Load($role)->AggregatePriest();
    //RoleManager::Stack()->p($role, "◆List [{$role}]");
    //Text::p(RoleManager::Stack()->Get($role)->list,   "◆List [{$role}]");
    //Text::p(RoleManager::Stack()->Get($role)->count,  '◆List [live]');
    //Text::p(RoleManager::Stack()->Get($role)->crisis, '◆List [crisis]');
    foreach (RoleManager::Stack()->Get($role)->list as $role) {
      RoleLoader::Load($role)->Priest();
    }
  }

  //人狼襲撃失敗カウンター処理
  private static function FilterWolfEatFailedCounter() {
    if (RoleManager::Stack()->Get('wolf_target')->IsDead(true)) return;
    foreach (RoleFilterData::$wolf_eat_failed_counter as $role) {
      if (DB::$USER->IsAppear($role)) {
	RoleLoader::Load($role)->WolfEatFailedCounter();
      }
    }
  }

  //ジョーカー再配布
  private static function ResetJoker() {
    if (DB::$ROOM->IsOption('joker')) {
      RoleLoader::Load('joker')->ResetJoker(true);
    }
  }

  //デスノート再配布
  private static function ResetDeathNote() {
    if (DB::$ROOM->IsOption('death_note')) {
      RoleLoader::Load('death_note')->ResetDeathNote();
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
	$type = EventType::SAME_FACE;
	$str  = $wolf_target->id;
	break;

      default:
	$type = EventType::EVENT;
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
  public static function Execute() {
    //-- 無効判定 --//
    self::CheckSituation(VoteAction::HEAVEN); //コマンドチェック
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
  public static function ResetTime() {
    self::CheckSituation(VoteAction::RESET_TIME); //コマンドチェック

    //-- 投票処理 --//
    RoomDB::UpdateTime(); //更新時間リセット

    //システムメッセージ
    $str = VoteMessage::RESET_TIME_SUCCESS;
    DB::$ROOM->Talk($str, null, DB::$SELF->uname, DB::$ROOM->scene, GM::DUMMY_BOY);
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }
}
