<?php
//-- 投票処理基礎クラス --//
abstract class VoteBase {
  const SITUATION = '';

  //実行処理
  public static function Execute() {
    self::ValidateSituation();
    static::Load();
    static::Vote();
  }

  //投票コマンドチェック
  final protected static function ValidateSituation() {
    if (Security::IsInvalidToken(DB::$ROOM->id)) { //CSRF対策
      HTML::OutputUnusableError();
    }

    if (static::SITUATION != RQ::Fetch()->situation) {
      VoteHTML::OutputResult(VoteMessage::INVALID_SITUATION);
    }
  }

  //データロード
  protected static function Load() {}

  //投票処理
  protected static function Vote() {}

  //音声用データセット
  protected static function FilterSound() {
    if (RQ::Fetch()->play_sound) {
      JinrouCookie::SetVote(DB::$ROOM->scene);
    }
  }
}

//-- 投票処理クラス (ゲーム開始) --//
final class VoteGameStart extends VoteBase {
  const SITUATION = VoteAction::GAME_START;

  protected static function Load() {
    self::FilterDummyBoy();
    DB::$ROOM->LoadVote();
  }

  protected static function Vote() {
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

  //集計処理
  public static function Aggregate($force_start = false) {
    Cast::Stack()->Set(Cast::FORCE, $force_start);
    if (self::IsInvalidVoteCount()) {
      return false;
    }

    //-- 配役決定ルーチン --//
    DB::$ROOM->LoadOption(); //配役設定オプションの情報を取得
    //Text::p(DB::$ROOM->option_role, '◆OptionRole');
    //Text::p(DB::$ROOM->option_list, '◆OptionList');

    Cast::Execute();
    //Cast::Stack()->p(Cast::UNAME, '◆Uname/End');
    //Cast::Stack()->p(Cast::CAST, '◆Role/End');
    //RoomDB::DeleteVote(); return false; //テスト用

    Cast::Store();
    DB::$USER->UpdateKick(); //KICK の後処理
    DB::$ROOM->Start();
    return true;
  }

  //身代わり君処理
  private static function FilterDummyBoy() {
    if (false === DB::$SELF->IsDummyBoy(true)) { //出題者はスキップ
      return;
    }

    if (GameConfig::POWER_GM) { //強権モードによる強制開始処理
      $str = self::Aggregate(true) ? VoteMessage::SUCCESS : VoteMessage::GAME_START_SHORTAGE;
      DB::Commit();
      self::Output($str);
    } else {
      self::Output(VoteMessage::GAME_START_DUMMY_BOY);
    }
  }

  //投票数チェック
  private static function IsInvalidVoteCount() {
    $user_count = DB::$USER->Count(); //ユーザ総数を取得
    $vote_count = self::CountVote($user_count);

    //規定人数に足りないか、全員投票していなければ無効
    if ($vote_count != $user_count || $vote_count < ArrayFilter::GetMin(CastConfig::$role_list)) {
      return true;
    }
    Cast::Stack()->Set(Cast::COUNT, $user_count);
    return false;
  }

  //投票人数取得
  private static function CountVote($user_count) {
    if (DB::$ROOM->IsTest()) {
      return $user_count;
    }

    self::ValidateSituation();
    if (Cast::Stack()->Get(Cast::FORCE)) { //強制開始モード時はスキップ
      return $user_count;
    }

    $count = DB::$ROOM->LoadVote(); //投票情報をロード (ロック前の情報は使わない事)
    if (DB::$ROOM->IsDummyBoy() && ! DB::$ROOM->IsQuiz()) { //クイズ村以外の身代わり君を加算
      $count++;
    }
    return $count;
  }

  //結果出力
  private static function Output($str) {
    VoteHTML::OutputResult(VoteMessage::GAME_START_TITLE . $str);
  }
}

//-- 投票処理クラス (キック) --//
final class VoteKick extends VoteBase {
  const SITUATION = VoteAction::KICK;

  protected static function Load() {
    $target = DB::$USER->ByID(RQ::Fetch()->target_no); //投票先ユーザ
    self::ValidateTarget($target);

    DB::$ROOM->LoadVote(true); //投票情報ロード
    $stack = DB::$ROOM->Stack()->GetKey('vote', DB::$SELF->id);
    if ((null !== $stack) && in_array($target->id, $stack)) {
      self::Output($target->handle_name . VoteMessage::ALREADY_KICK);
    }
    RoleManager::Stack()->Set(VoteKickElement::TARGET, $target);
  }

  protected static function Vote() {
    $target = RoleManager::Stack()->Get(VoteKickElement::TARGET);
    if (DB::$SELF->Vote(VoteAction::KICK, $target->id)) {
      //投票通知
      $talk = new RoomTalkStruct($target->handle_name);
      $talk->Set(TalkStruct::UNAME,  DB::$SELF->uname);
      $talk->Set(TalkStruct::ACTION, VoteAction::KICK);
      DB::$ROOM->Talk($talk);

      $vote_count = self::Aggregate($target); //集計処理
      DB::Commit();
      $format = VoteMessage::SUCCESS . VoteMessage::KICK_SUCCESS;
      self::Output(sprintf($format, $target->handle_name, $vote_count, GameConfig::KICK));
    } else {
      self::Output(VoteMessage::DB_ERROR);
    }
  }

  //投票先チェック
  private static function ValidateTarget(User $target) {
    if ((null === $target->id) || $target->live == UserLive::KICK) {
      self::Output(VoteMessage::KICK_EMPTY);
    } elseif ($target->IsDummyBoy()) {
      self::Output(VoteMessage::KICK_DUMMY_BOY);
    } elseif (! GameConfig::SELF_KICK && $target->IsSelf()) {
      self::Output(VoteMessage::KICK_SELF);
    }
  }

  //集計処理 (返り値 : $target への投票合計数)
  private static function Aggregate(User $target) {
    //投票先への合計投票数を取得
    $vote_count = 1;
    foreach (DB::$ROOM->Stack()->Get('vote') as $stack) {
      if (in_array($target->id, $stack)) {
	$vote_count++;
      }
    }

    //規定数以上の投票があった / キッカーが身代わり君 / 自己 KICK が有効の場合に処理
    if ($vote_count >= GameConfig::KICK || DB::$SELF->IsDummyBoy() ||
	(GameConfig::SELF_KICK && $target->IsSelf())) {
      UserDB::Kick($target->id);

      //通知処理
      RoomTalk::StoreSystem($target->handle_name . TalkMessage::KICK_OUT);
      RoomTalk::StoreSystem(GameMessage::VOTE_RESET);

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
final class VoteDay extends VoteBase {
  const SITUATION = VoteAction::VOTE_KILL;

  //データロード
  protected static function Load() {
    RoleManager::Stack()->Set(VoteDayElement::TARGET, DB::$USER->ByReal(RQ::Fetch()->target_no));
    self::ValidateTarget();
    EventManager::VoteDuel();
    self::ValidateVote();
  }

  //投票処理
  protected static function Vote() {
    //-- 初期化 --//
    RoleManager::Stack()->Set(VoteDayElement::VOTE_NUMBER, 1);

    //-- 投票数補正 --//
    RoleVote::VoteDoMain();
    RoleVote::VoteDoSub();
    EventManager::VoteDo();

    //-- 処刑処理 --//
    $target      = RoleManager::Stack()->Get(VoteDayElement::TARGET);
    $vote_number = max(0, RoleManager::Stack()->Get(VoteDayElement::VOTE_NUMBER));
    if (false === DB::$SELF->Vote(VoteAction::VOTE_KILL, $target->id, $vote_number)) {
      VoteHTML::OutputResult(VoteMessage::DB_ERROR);
    }
    if (DB::$ROOM->IsTest()) {
      return true;
    }

    //-- システムメッセージ --//
    $talk = new RoomTalkStruct($target->GetName());
    $talk->Set(TalkStruct::UNAME,  DB::$SELF->uname);
    $talk->Set(TalkStruct::ACTION, VoteAction::VOTE);
    DB::$ROOM->Talk($talk);

    //-- 集計処理 --//
    self::Aggregate();
    DB::Commit();
    self::FilterSound();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }

  //役職クラス取得
  public static function GetFilter() {
    //処刑に複合投票イベントが発生したら実装する
    return RoleLoader::LoadMain(DB::$SELF);
  }

  //集計処理
  public static function Aggregate() {
    //-- 沈黙禁止処理 --//
    self::FilterNoSilence();

    if (self::DisableAggregate()) {
      return false;
    }
    //DB::$ROOM->Stack()->p('vote', '◆vote');
    //RoleManager::Stack()->p(VoteDayElement::USER_LIST, '◆user_list');

    //-- 投票データ収集 --//
    self::InitStack();
    self::InitVoteCount();
    //RoleManager::Stack()->p(VoteDayElement::COUNT_LIST, '◆VoteCountBase');
    self::InitVoteData();
    RoleVote::VoteKillCorrect();

    //-- 処刑者決定 --//
    self::SaveResultVote();
    self::DecideVoteKill();
    //RoleManager::Stack()->p(VoteDayElement::VOTE_KILL, '◆VoteTarget');

    //-- 処刑実行処理 --//
    if (RoleManager::Stack()->Exists(VoteDayElement::VOTE_KILL)) {
      self::VoteKill(); //処刑実行

      //-- 毒能力 --//
      RoleVote::SetDetox();
      self::FilterVoteKillPoison();
      //RoleManager::Stack()->p('pharmacist_result', '◆EndDetox');

      //-- 処刑実行後能力 --//
      RoleVote::VoteKillCounter();
      RoleVote::VoteKillAction();
      RoleVote::Necromancer();
    }

    //-- 処刑得票能力 --//
    RoleVote::VotePollReaction();
    self::FilterSuddenDeath();
    RoleVote::VoteKillFollowed();
    self::FilterSavePharmacistResult();
    RoleVote::Followed();

    if (RoleManager::Stack()->Exists(VoteDayElement::VOTE_KILL)) { //夜に切り替え
      self::ChangeNight();
      DB::$ROOM->SkipNight();
      if (DB::$ROOM->IsTest()) {
	return RoleManager::Stack()->Get(VoteDayElement::MESSAGE_LIST);
      }
    } else { //再投票処理
      if (DB::$ROOM->IsTest()) {
	return RoleManager::Stack()->Get(VoteDayElement::MESSAGE_LIST);
      }
      self::Revote();
    }

    foreach (DB::$USER->Get() as $user) { //player 更新
      $user->UpdatePlayer();
    }
    RoomDB::UpdateTime(); //最終書き込み時刻を更新
  }

  //投票先チェック
  private static function ValidateTarget() {
    $target = RoleManager::Stack()->Get(VoteDayElement::TARGET);
    $filter = VoteDay::GetFilter();
    if (null === $target->id) {
      VoteHTML::OutputResult(VoteMessage::INVALID_VOTE);
    } elseif (false === $filter->IsVoteDayCheckBoxSelf() && $target->IsSelf()) {
      VoteHTML::OutputResult(VoteMessage::VOTE_SELF);
    } elseif ($target->IsDead()) {
      VoteHTML::OutputResult(VoteMessage::VOTE_DEAD);
    }
  }

  //投票チェック
  private static function ValidateVote() {
    if (DB::$ROOM->IsTest()) {
      if (isset(RQ::GetTest()->vote->day[DB::$SELF->uname])) {
	Text::p(DB::$SELF->uname, '★AlreadyVoted');
	return false;
      } else {
	return true;
      }
    } elseif (DB::$ROOM->revote_count != RQ::Fetch()->revote_count) {
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

  //集計実行無効判定
  private static function DisableAggregate() {
    if (false === DB::$ROOM->IsTest()) {
      self::ValidateSituation(); //コマンドチェック
    }

    $user_list  = DB::$USER->SearchLive(true); //生存者
    $vote_count = DB::$ROOM->LoadVote();       //投票数
    if (DB::$ROOM->IsOption('no_silence')) {   //沈黙死した人の投票を除く
      $vote_count -= OptionLoader::Load('no_silence')->CountSilence();
    }

    if ($vote_count == count($user_list)){ //投票数と照合
      RoleManager::Stack()->Set(VoteDayElement::USER_LIST, $user_list);
      return false;
    } else {
      return true;
    }
  }

  //変数の初期化
  /*
    pharmacist_result //薬師系の鑑定結果
  */
  private static function InitStack() {
    $stack = ['pharmacist_result'];
    foreach ($stack as $name) {
      RoleManager::Stack()->Init($name);
    }

    if (DB::$ROOM->IsOption('joker')) { //現在のジョーカー所持者情報
      RoleLoader::Load('joker')->InitializeJoker();
    }
  }

  //初期得票データ収集
  private static function InitVoteCount() {
    $stack = []; //得票リスト (ユーザ名 => 投票数)
    $no_silence = DB::$ROOM->IsOption('no_silence');
    foreach (DB::$ROOM->Stack()->Get('vote') as $id => $list) {
      $target_id = $list['target_no'];
      //沈黙死判定
      if (true === $no_silence && DB::$USER->ByReal($target_id)->IsOn(UserMode::SUICIDE)) {
	continue;
      }

      ArrayFilter::Add($stack, DB::$USER->ByVirtual($target_id)->uname, $list['vote_number']);
    }
    RoleManager::Stack()->Set(VoteDayElement::COUNT_LIST, $stack);
  }

  //個別の投票データ収集
  private static function InitVoteData() {
    //-- 変数初期化 --//
    $no_silence        = DB::$ROOM->IsOption('no_silence'); //沈黙禁止
    $live_uname_list   = []; //生存者リスト (ユーザ名)
    $vote_target_list  = []; //投票リスト (ユーザ名 => 投票先ユーザ名)
    $vote_message_list = []; //システムメッセージ用 (ユーザID => [])
    $vote_count_list   = RoleManager::Stack()->Get(VoteDayElement::COUNT_LIST);

    foreach (RoleManager::Stack()->Get(VoteDayElement::USER_LIST) as $id => $uname) {
      $list      = DB::$ROOM->Stack()->GetKey('vote', $id);			//投票データ
      $virtual   = DB::$USER->ByVirtual($id);					//仮想ユーザ
      $target    = DB::$USER->ByVirtual($list['target_no']);			//投票先の仮想ユーザ
      $real      = DB::$USER->ByReal($virtual->id);				//実ユーザ
      $vote      = ArrayFilter::GetInt($list, 'vote_number');			//投票数
      $base_poll = ArrayFilter::GetInt($vote_count_list, $virtual->uname);	//得票数 (補正前)
      RoleManager::Stack()->Set(VoteDayElement::POLL_NUMBER, $base_poll);

      //-- 得票数補正 --//
      RoleVote::VotePollMain($real);
      RoleVote::VotePollSub($virtual);
      $poll = max(0, RoleManager::Stack()->Get(VoteDayElement::POLL_NUMBER));

      //-- リストにデータを追加 --//
      $live_uname_list[$virtual->id]     = $virtual->uname;
      $vote_target_list[$virtual->uname] = $target->uname;
      $vote_count_list[$virtual->uname]  = $poll;
      $vote_message_list[$virtual->id]   = [
	'target_name' => $target->handle_name,
	'vote'        => $vote,
	'poll'        => $poll
      ];
      RoleVote::VoteKillWizard($real); //処刑魔法発動

      //沈黙死スキップ判定
      if (true === $no_silence && $target->GetReal()->IsOn(UserMode::SUICIDE)) {
	//Text::p($target->uname, '◆Skip [suicide]');
	continue;
      }

      //-- 処刑投票能力 --//
      RoleVote::VoteKillMain($real, $target);
      RoleVote::VoteKillSub($virtual, $target);
    }
    RoleManager::Stack()->Set(VoteDayElement::LIVE_LIST,   $live_uname_list);
    RoleManager::Stack()->Set(VoteDayElement::COUNT_LIST,  $vote_count_list);
    RoleManager::Stack()->Set(VoteDayElement::TARGET_LIST, $vote_target_list);
    //RoleManager::Stack()->p(null, '◆RoleStack');

    //Text::p($vote_message_list, '◆VoteMessage [base]');
    ksort($vote_message_list); //投票順をソート (憑依対応)
    $stack = [];
    foreach ($vote_message_list as $id => $list) {
      $stack[DB::$USER->ByID($id)->uname] = $list;
    }
    RoleManager::Stack()->Set(VoteDayElement::MESSAGE_LIST, $stack);
    //RoleManager::Stack()->p(VoteDayElement::MESSAGE_LIST, '◆VoteMessage [sort]');
  }

  //投票結果登録
  private static function SaveResultVote() {
    if (false === DB::$ROOM->IsTest()) {
      $base_list = [
	'room_no' => DB::$ROOM->id,
	'date'    => DB::$ROOM->date,
	'count'   => DB::$ROOM->revote_count + 1
      ];
    }

    //タブ区切りのデータをシステムメッセージに登録
    $max_poll = 0; //最多得票数
    foreach (RoleManager::Stack()->Get(VoteDayElement::MESSAGE_LIST) as $uname => $stack) {
      extract($stack); //配列を展開
      $max_poll = max($poll, $max_poll); //最大得票数を更新
      if (DB::$ROOM->IsTest()) {
	continue;
      }

      $list = $base_list;
      $list['handle_name'] = DB::$USER->ByUname($uname)->handle_name; //憑依追跡済み
      $list['target_name'] = $target_name;
      $list['vote']        = $vote;
      $list['poll']        = $poll;
      DB::Insert('result_vote_kill', $list);
    }

    //最大得票数のユーザ名 (処刑候補者リスト) を登録
    $max_voted_list = RoleManager::Stack()->GetKeyList(VoteDayElement::COUNT_LIST, $max_poll);
    RoleManager::Stack()->Set(VoteDayElement::MAX_VOTED, $max_voted_list);
  }

  //処刑者決定処理
  private static function DecideVoteKill() {
    RoleManager::Stack()->Set(VoteDayElement::VOTE_KILL, null); //処刑者初期化 (ユーザ名)
    $stack = RoleManager::Stack()->Get(VoteDayElement::MAX_VOTED); //処刑候補者リスト
    //Text::p($stack, '◆MaxVoted');

    if (count($stack) == 1) { //一人だけなら決定
      RoleManager::Stack()->Set(VoteDayElement::VOTE_KILL, array_shift($stack));
    } else { //処刑者決定能力判定
      RoleManager::Stack()->Set(VoteDayElement::VOTE_POSSIBLE, $stack);
      RoleVote::DecideVoteKill();
      EventManager::DecideVoteKill();
    }
  }

  //処刑実行
  private static function VoteKill() {
    $uname  = RoleManager::Stack()->Get(VoteDayElement::VOTE_KILL); //ユーザ情報を取得
    $target = DB::$USER->ByRealUname($uname);
    DB::$USER->Kill($target->id, DeadReason::VOTE_KILLED); //処刑処理
    RoleManager::Stack()->Set(VoteDayElement::VOTED_USER, $target);

    //自己処刑処理
    RoleLoader::LoadMain($target)->VoteKillSelfAction();

    //処刑者を生存者リストから除く
    $stack = RoleManager::Stack()->Get(VoteDayElement::LIVE_LIST);
    ArrayFilter::Delete($stack, $uname);
    RoleManager::Stack()->Set(VoteDayElement::LIVE_LIST, $stack);
  }

  //処刑者の毒処理
  private static function FilterVoteKillPoison() {
    //スキップ判定 (毒発動 > 解毒)
    if (false === RoleUser::IsPoison(RoleManager::Stack()->Get(VoteDayElement::VOTED_USER))) {
      return;
    } elseif (RoleVote::Detox()) {
      return;
    }

    //毒死候補者選出
    $stack = RoleVote::GetVoteKillPoisonTarget();
    //Text::p($stack, '◆Target [poison]');
    if (count($stack) < 1) {
      return;
    }

    $user = DB::$USER->ByID(Lottery::Get($stack)); //対象者を決定
    if (RoleVote::ResistVoteKillPoison($user)) { //抗毒判定
      return;
    }
    DB::$USER->Kill($user->id, DeadReason::POISON_DEAD); //死亡処理
    RoleVote::ChainPoison($user); //連毒
  }

  //ショック死処理
  private static function FilterSuddenDeath() {
    //判定用データを登録 (投票者対象ユーザ名 => 人数)
    $stack = array_count_values(RoleManager::Stack()->Get(VoteDayElement::TARGET_LIST));
    RoleManager::Stack()->Set(VoteDayElement::POLL_LIST, $stack);
    //RoleManager::Stack()->p(VoteDayElement::POLL_LIST, '◆Count [poll]');

    //青天の霹靂発動判定
    RoleVote::SetThunderbolt();
    //RoleManager::Stack()->p('thunderbolt', '◆ThunderboltTarget');

    foreach (RoleManager::Stack()->Get(VoteDayElement::LIVE_LIST) as $uname) {
      //初期化
      $user = DB::$USER->ByUname($uname); //live_uname は仮想ユーザ名
      $user->cured_flag = false;
      RoleLoader::SetActor($user);
      RoleManager::Stack()->Set(VoteDayElement::SUDDEN_DEATH, null);

      //ショック死判定 (青天の霹靂 > サブ > メイン > 天狗陣営)
      RoleVote::SuddenDeathThunderbolt();
      RoleVote::SuddenDeathSub();
      RoleVote::SuddenDeathMain();
      RoleVote::SuddenDeathTengu($user);
      if (RoleManager::Stack()->IsEmpty(VoteDayElement::SUDDEN_DEATH)) {
	continue;
      }

      //治療判定
      RoleVote::Cure();
      if (true === $user->cured_flag) {
	continue;
      }

      //ショック死処理
      $type = RoleManager::Stack()->Get(VoteDayElement::SUDDEN_DEATH);
      DB::$USER->SuddenDeath($user->id, DeadReason::SUDDEN_DEATH, $type);
    }
  }

  //薬師系の鑑定結果を登録
  private static function FilterSavePharmacistResult() {
    $role = 'pharmacist';
    $name = $role . '_result';
    //RoleManager::Stack()->p($name, "◆Result [{$role}]");
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load($role)->SavePharmacistResult();
    }
    RoleManager::Stack()->Clear($name);
  }

  //夜に切り替え
  private static function ChangeNight() {
    RoleVote::VoteKillReaction();
    EventManager::VoteKillAction();
    RoleVote::VoteKillCancel();

    if (DB::$ROOM->IsOption('joker')) { //ジョーカー移動判定
      $joker_filter = RoleLoader::Load('joker');
      $joker_flag   = $joker_filter->SetJoker();
    } else {
      $joker_flag = false;
    }

    DB::$ROOM->ChangeNight();
    if (Winner::Judge()) { //勝敗判定
      if (true === $joker_flag) {
	$joker_filter->FinishJoker();
      }
    } else {
      if (true === $joker_flag) {
	$joker_filter->ResetVoteJoker();
      }
      self::InsertRandomMessage(); //ランダムメッセージ
    }
  }

  //再投票処理
  private static function Revote() {
    //処刑投票回数を増やす
    DB::$ROOM->revote_count++;
    RoomDB::UpdateVoteCount(true);

    //システムメッセージ
    RoomTalk::StoreSystem(sprintf(VoteMessage::REVOTE, DB::$ROOM->revote_count));

    if (Winner::Judge(true)) { //勝敗判定
      if (DB::$ROOM->IsOption('joker')) { //ジョーカー処理
	RoleLoader::Load('joker')->FinishDrawJoker();
      }
    }
  }

  //ランダムメッセージ挿入
  private static function InsertRandomMessage() {
    if (GameConfig::RANDOM_MESSAGE) {
      RoomTalk::StoreSystem(Lottery::Get(Message::$random_message_list));
    }
  }
}

//-- 投票処理クラス (夜) --//
final class VoteNight extends VoteBase {
  //実行処理
  public static function Execute() {
    self::Load();
    self::Vote();
  }

  protected static function Load() {
    self::Stack()->Set('filter', self::GetFilter());
    self::Stack()->Set('not_action', false);
    self::ValidateTarget();
    //self::Stack()->p('filter', '◆Filter');
  }

  protected static function Vote() {
    if (self::Stack()->Get('not_action')) { //投票キャンセルタイプは何もしない
      if (false === DB::$SELF->Vote(RQ::Fetch()->situation)) {
	VoteHTML::OutputResult(VoteMessage::DB_ERROR);
      }
      $str    = '';
      $action = RQ::Fetch()->situation;
    } else {
      self::Stack()->Get('filter')->SetVoteNightTarget();
      //RoleManager::Stack()->p();
      $target = RoleManager::Stack()->Get(RequestDataVote::TARGET);
      if (false === DB::$SELF->Vote(RQ::Fetch()->situation, $target)) {
	VoteHTML::OutputResult(VoteMessage::DB_ERROR);
      }
      $str    = RoleManager::Stack()->Get('target_handle');
      $action = RoleManager::Stack()->Get('message');
    }
    $talk = new RoomTalkStruct($str);
    $talk->Set(TalkStruct::UNAME,   DB::$SELF->uname);
    $talk->Set(TalkStruct::ACTION,  $action);
    $talk->Set(TalkStruct::ROLE_ID, DB::$SELF->role_id);
    DB::$ROOM->Talk($talk);

    if (DB::$ROOM->IsTest()) {
      return;
    }
    self::Aggregate(); //集計処理
    foreach (DB::$USER->Get() as $user) { //player 更新
      $user->UpdatePlayer();
    }
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }

  //役職クラス取得
  public static function GetFilter() {
    if (DB::$SELF->IsDummyBoy()) { //身代わり君は投票しない
      VoteHTML::OutputResult(VoteMessage::DUMMY_BOY_NIGHT);
    }

    foreach (['', 'not_'] as $header) {   //データを初期化
      foreach (['action', 'submit'] as $data) {
	RoleManager::Stack()->Set($header . $data, null);
      }
    }

    $death_note = false;
    foreach (RoleLoader::LoadUser(DB::$SELF, 'death_note') as $filter) {
      if (false === $filter->IsVoteDeathNote()) {
	continue;
      }
      //Text::p(DB::$SELF->uname, "◆{$filter->role}");

      if (DB::$ROOM->IsTest() ||
	  false === self::IsSelfVoted($filter->action, $filter->not_action)) {
	$death_note = true;
	break;
      }
    }

    if (false === $death_note) {
      $filter = RoleLoader::LoadMain(DB::$SELF);
    }
    $filter->SetVoteNight();

    return $filter;
  }

  //投票済みチェック
  public static function ValidateVoted($action, $not_action = '') {
    if (self::IsSelfVoted($action, $not_action)) {
      VoteHTML::OutputResult(VoteMessage::ALREAY_VOTE_NIGHT);
    }
  }

  //集計処理
  public static function Aggregate($skip = false) {
    //-- 投票データ収集 --//
    RoleManager::Stack()->Set('skip', $skip);
    if (false === self::LoadVote()) {
      return false;
    }

    self::InitVote();
    self::InitStack();
    self::FilterWeather();
    self::FilterWizard();

    //-- 足音レイヤー --//
    self::FilterStep();

    //-- 接触レイヤー --//
    self::LoadWolf();
    if (DateBorder::Second()) {
      self::LoadTrap();
      self::LoadGuard();
      self::LoadExit();
      self::LoadEscape();
      self::LoadRiote();
    }

    self::FilterWolfEat();
    if (DateBorder::Second()) {
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

    //-- 透視レイヤー --//
    self::FilterMindScan();

    if (DateBorder::One()) {
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

    if (DateBorder::Second()) {
      self::FilterReverseResurrect();

      //-- 蘇生レイヤー --//
      if (false === DB::$ROOM->IsOpenCast()) {
	self::LoadGrave();
	self::FilterRevive();
      }
      self::FilterRiote();

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
    if (DateBorder::Second()) {
      self::FilterNecromancerNight();
    }
    self::FilterPriest();
    self::FilterWolfEatFailedCounter();

    //-- 日付変更処理 --//
    $status = DB::$ROOM->ChangeDate();
    if (DB::$ROOM->IsTest() || false === $status) {
      self::ResetJoker();
    }
    self::ResetDeathNote();
    self::SaveEvent();

    return $status;
  }

  //スタックロード
  private static function Stack() {
    static $stack;

    if (null === $stack) {
      $stack = new Stack();
    }
    return $stack;
  }

  //投票先チェック
  private static function ValidateTarget() {
    if (Security::IsInvalidToken(DB::$ROOM->id)) { //CSRF対策
      HTML::OutputUnusableError();
    } elseif (empty(RQ::Fetch()->situation)) {
      VoteHTML::OutputResult(VoteMessage::VOTE_NIGHT_EMPTY);
    } elseif (RQ::Fetch()->situation == RoleManager::Stack()->Get('not_action')) {
      self::Stack()->Set('not_action', true);
    } elseif (RQ::Fetch()->situation != RoleManager::Stack()->Get('action')) {
      VoteHTML::OutputResult(VoteMessage::INVALID_VOTE_NIGHT);
    } else {
      $add_action = RoleManager::Stack()->Get('add_action');
      if (RQ::Fetch()->add_action && isset($add_action)) {
	RQ::Set(RequestDataVote::SITUATION, $add_action);
      }
    }

    if (false === DB::$ROOM->IsTest()) {
      self::ValidateVoted(RQ::Fetch()->situation); //投票済みチェック
    }
  }

  //未投票チェック (本人)
  private static function IsSelfVoted($situation, $not_situation = '') {
    return count(DB::$SELF->LoadVote($situation, $not_situation)) > 0;
  }

  //投票情報取得
  private static function LoadVote() {
    DB::$ROOM->LoadVote(); //投票情報を取得
    //DB::$ROOM->Stack()->p('vote', '◆VoteRow');

    $vote_data = DB::$ROOM->ParseVote(); //コマンド毎に分割
    //Text::p($vote_data, '◆VoteData');

    RoleManager::SetVoteData($vote_data);
    if (RoleManager::Stack()->Get('skip')) {
      return true;
    }

    foreach (DB::$USER->Get() as $user) { //未投票チェック
      if (RoleUser::ImcompletedVoteNight($user, $vote_data)) {
	if (DB::$ROOM->IsTest()) {
	  Text::p($user->uname, "★NoVote [{$user->main_role}]");
	}
	return false;
      }
    }
    return true;
  }

  //投票データ初期化
  private static function InitVote() {
    //処理対象コマンドチェック
    $stack = VoteActionGroup::$init;
    if (DateBorder::One()) {
      ArrayFilter::AddMerge($stack, VoteActionGroup::$init_first);
    } else {
      ArrayFilter::AddMerge($stack, VoteActionGroup::$init_after);
    }
    $vote_data = RoleManager::GetVoteData();
    ArrayFilter::Initialize($vote_data, $stack);
    //Text::p($vote_data, '◆VoteData [init]');

    RoleManager::SetVoteData($vote_data);
  }

  //変数の初期化
  private static function InitStack() {
    $stack = [
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
    ];
    foreach ($stack as $name) {
      RoleManager::Stack()->Init($name);
    }
  }

  //天候の処理
  private static function FilterWeather() {
    $stack = EventManager::SealVoteNight();
    //Text::p($stack, '◆VoteData [seal]');
    if (count($stack) < 1) {
      return;
    }

    $vote_data = RoleManager::GetVoteData();
    ArrayFilter::Reset($vote_data, $stack);
    //Text::p($vote_data, '◆VoteData [weather]');

    RoleManager::SetVoteData($vote_data);
  }

  //魔法使い系の振り替え処理
  private static function FilterWizard() {
    if (DateBorder::PreTwo()) {
      return;
    }

    $action    = VoteAction::WIZARD;
    $vote_data = RoleManager::GetVoteData();
    if (count($vote_data[$action]) < 1) {
      return;
    }

    foreach ($vote_data[$action] as $id => $target_id) {
      $action = RoleLoader::LoadMain(DB::$USER->ByID($id))->SetWizard();
      //Text::p(RoleLoader::GetActor()->virtual_role, "◆Wizard: {$id}: {$action}");
      $vote_data[$action][$id] = $target_id;
    }
    RoleManager::SetVoteData($vote_data);
  }

  //足音レイヤー処理
  private static function FilterStep() {
    if (DB::$ROOM->IsEvent('no_step')) { //地吹雪は無効
      return;
    }

    $stack = VoteActionGroup::$step;
    if (DateBorder::Second()) {
      ArrayFilter::AddMerge($stack, VoteActionGroup::$step_after);
    }

    $vote_data = RoleManager::GetVoteData();
    foreach ($stack as $action) { //足音処理
      RoleVote::FilterNight($vote_data[$action], 'Step', 'none', 'multi');
    }

    if (DateBorder::One()) {
      foreach (RoleFilterData::$step_copy as $role) { //コピー型の処理
	foreach (DB::$USER->GetRoleUser($role) as $user) {
	  if (false === $user->IsDummyBoy()) {
	    RoleLoader::LoadMain($user)->Step();
	  }
	}
      }
    }

    EventManager::Step(); //天候処理
    //ステルス投票カウントアップ
    foreach ($vote_data[VoteAction::SILENT_WOLF] as $id => $target_id) {
      DB::$USER->ByID($id)->LostAbility();
    }
  }

  //人狼の情報収集
  private static function LoadWolf() {
    //天候などで投票情報が空になった場合はスキップ判定をセットしておくこと
    if (RoleManager::Stack()->Get('skip')) { //スキップ判定
      RoleLoader::Load('wolf')->SetSkipWolf();
      return true;
    }

    $vote_data = RoleManager::GetVoteData();
    foreach (VoteActionGroup::$wolf as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	RoleLoader::LoadMain(DB::$USER->ByID($id))->SetWolf($target_id);
      }
    }
  }

  //罠能力者の情報収集
  private static function LoadTrap() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::TRAP], 'SetTrap'); //設置処理

    $role = 'trap_wolf'; //狡狼の自動設置処理 (無効天候あり)
    if (DateBorder::Third() && EventManager::EnableTrap() && DB::$USER->IsAppear($role)) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	if ($user->IsLive()) {
	  RoleLoader::LoadMain($user)->SetAutoTrap();
	}
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
    $method = 'SetGuard';
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::GUARD],   $method); //護衛能力者
    RoleVote::FilterNight($vote_data[VoteAction::STEP_GUARD], $method, 'none', 'step'); //山立
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

  //逃亡能力者の情報収集
  private static function LoadEscape() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::ESCAPE], 'Escape');
    //RoleManager::Stack()->p(RoleVoteTarget::ESCAPER, '◆Target [escaper]');
  }

  //暴動能力者の情報収集
  private static function LoadRiote() {
    $name = RoleVoteTarget::RIOTE;
    RoleManager::Stack()->Init($name);
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNightSet($vote_data[VoteAction::RIOTE], 'SetRiote');
    //RoleManager::Stack()->p($name, '◆Target [riote]');
  }

  //人狼襲撃処理
  private static function FilterWolfEat() {
    RoleLoader::Load('wolf')->WolfEat();
    //RoleManager::Stack()->p(RoleVoteSuccess::POSSESSED, '◆Possessed [wolf]');
  }

  //デスノートの死亡処理
  private static function FilterDeathNote() {
    $vote_data = RoleManager::GetVoteData();
    $list      = $vote_data[VoteAction::DEATH_NOTE];
    if (count($list) > 0) {
      RoleLoader::Load('death_note')->DeathNoteKill($list);
    }
  }

  //狩り処理
  private static function FilterHunt() {
    if (DB::$ROOM->IsEvent('no_hunt')) { //川霧ならスキップ
      return;
    }
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

    foreach (RoleFilterData::$guard_finish_action as $actor_role) { //護衛判定後処理
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
    //暗殺能力者の処理
    RoleVote::FilterNight($vote_data[VoteAction::ASSASSIN], 'SetAssassin');
    //風神の処理
    RoleVote::FilterNight($vote_data[VoteAction::STEP_ASSASSIN], 'SetStepAssassin', null, 'multi');
    //直線暗殺(魔砲使い)の処理
    RoleVote::FilterNight($vote_data[VoteAction::SPARK_WIZARD], 'SetLineAssassin', null, 'multi');
    self::FilterDelayTrapKill(); //罠死処理

    //RoleManager::Stack()->p($role, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($role)) {
      RoleLoader::Load($role)->AssassinKill();
    }
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
    if (RoleManager::Stack()->Exists($role)) {
      RoleLoader::Load($role)->OgreAssassinKill();
    }
    RoleManager::Stack()->Clear($role);
  }

  //オシラ遊びの死亡処理
  private static function FilterDeathSelected() {
    RoleLoader::Load('death_selected')->DeathSelectedKill();
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
    RoleLoader::Load($role)->SetFrostbite();
    RoleManager::Stack()->Clear($role);
  }

  //獏の処理
  private static function FilterDreamEat() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::DREAM], 'DreamEat');
  }

  //夢狩り処理
  private static function FilterDreamHunt() {
    $hunted_list = []; //狩り成功者リスト
    $filter_list = RoleLoader::LoadFilter('guard_dream');
    foreach ($filter_list as $filter) {
      $filter->DreamGuard($hunted_list);
    }
    foreach ($filter_list as $filter) {
      $filter->DreamHunt($hunted_list);
    }
  }

  //厄神の情報収集
  private static function LoadAntiVoodoo() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::ANTI_VOODOO], 'SetVoodooGuard');
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

  //呪術能力者の情報収集
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

    //呪術能力者の対象が重なった場合は呪返しを受ける
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load('voodoo_mad')->VoodooToVoodoo();
    }
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
    RoleManager::Stack()->Init($name); //幻系の発動者リスト
    RoleManager::Stack()->Init('mage_kill'); //呪殺対象者リスト

    //占い系の処理
    $vote_data = RoleManager::GetVoteData();
    foreach (VoteActionGroup::$mage as $action) {
      RoleVote::FilterNight($vote_data[$action], 'Mage');
    }
    //足音占い(審神者)の処理
    RoleVote::FilterNightStep($vote_data[VoteAction::STEP_MAGE], 'Mage');

    if (DateBorder::Second()) {
      //範囲占い(魔女見習い)の処理
      RoleVote::FilterNight($vote_data[VoteAction::PLURAL_WIZARD], 'PluralMage', null, 'multi');
    }

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
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load('mage')->MageKill();
    }
    RoleManager::Stack()->Clear($name);
  }

  //さとり系の処理
  private static function FilterMindScan() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::SCAN], 'MindScan');
    if (DateBorder::Second()) { //雷神は二日目以降
      RoleVote::FilterNight($vote_data[VoteAction::STEP_SCAN], 'StepMindScan', null, 'multi');
      self::FilterDelayTrapKill(); //遅行罠死処理 (凍傷型は無効)
    }
  }

  //神話マニアの処理
  private static function FilterCopy() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::MANIA], 'Copy');
  }

  //天人の帰還処理
  private static function FilterPriestReturn() {
    if (DB::$ROOM->IsOpenCast()) {
      return;
    }

    foreach (RoleFilterData::$priest_return as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	RoleLoader::LoadMain($user)->PriestReturn();
      }
    }
  }

  //恋人抽選処理
  private static function FilterLotteryLovers() {
    foreach (RoleFilterData::$lottery_lovers as $role) {
      if (DB::$USER->IsAppear($role)) {
	RoleLoader::Load($role)->LotteryLovers();
      }
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

  //尾行処理
  private static function FilterReport() {
    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::REPORTER], 'Report');
  }

  //反魂処理
  private static function FilterResurrect() {
    if (DB::$ROOM->IsEvent('no_revive')) { //快晴なら無効
      return;
    }

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
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load($role)->Resurrect();
    }
    RoleManager::Stack()->Clear($name);
  }

  //死者妨害能力者の情報収集
  private static function LoadGrave() {
    $name = RoleVoteTarget::GRAVE;
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

    //蘇生身代わり
    foreach (RoleFilterData::$revive_sacrifice as $role) {
      if (DB::$USER->CountRole($role) > 0) {
	RoleLoader::Load($role)->ReviveSacrifice();
      }
    }

    //蘇生キャンセル後処理
    foreach (RoleFilterData::$revive_cancel as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	//未投票者のみ・直前に死んでいたら無効
	if (ArrayFilter::IsAssocKey($vote_data, $action, $user->id) || $user->IsDead(true)) {
	  continue;
	}
	RoleLoader::LoadMain($user)->ReviveCancelAction();
      }
    }
  }

  //暴動能力者の処理
  private static function FilterRiote() {
    $role = 'rioter_mad';
    $name = RoleVoteTarget::RIOTE;
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load($role)->Riote();
    }
    RoleManager::Stack()->Clear($name);
  }

  //憑依能力者の情報収集
  private static function LoadPossessed() {
    $role = 'possessed_mad';
    $name = 'possessed_dead';
    RoleManager::Stack()->Init($name); //有効憑依情報リスト

    $vote_data = RoleManager::GetVoteData();
    RoleVote::FilterNight($vote_data[VoteAction::POSSESSED], 'SetPossessedDead', 'inactive');
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load($role)->SetPossessed();
    }
    RoleManager::Stack()->Clear($name);
    //RoleManager::Stack()->p(RoleVoteSuccess::POSSESSED, '◆Possessed [mad/fox]');
  }

  //憑依処理
  private static function FilterPossessed() {
    $role = 'possessed_wolf';
    $name = RoleVoteSuccess::POSSESSED;
    //RoleManager::Stack()->p($name, "◆Target [{$role}]");
    if (RoleManager::Stack()->Exists($name)) {
      RoleLoader::Load($role)->Possessed();
    }
    RoleManager::Stack()->Clear($name);
  }

  //陰陽師・厄神の成功結果登録
  private static function SaveSuccess() {
    $stack = [
      'voodoo_killer' => RoleVoteSuccess::VOODOO_KILLER,
      'anti_voodoo'   => RoleVoteSuccess::ANTI_VOODOO
    ];
    foreach ($stack as $role => $name) {
      //RoleManager::Stack()->p($name, "◆Success [{$role}]");
      if (RoleManager::Stack()->Exists($name)) {
	RoleLoader::Load($role)->SaveSuccess();
      }
      RoleManager::Stack()->Clear($name);
    }
  }

  //時間差コピー能力者のコピー処理
  private static function FilterDelayCopy() {
    foreach (RoleFilterData::$delay_copy as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $user) {
	if ($user->IsDummyBoy()) {
	  continue;
	}

	$id = $user->GetMainRoleTarget();
	if (null === $id) {
	  continue;
	}
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
    if (RoleManager::Stack()->Get('wolf_target')->IsDead(true)) {
      return;
    }
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
    if (false === isset($stack)) {
      return;
    }

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
      DB::$ROOM->StoreEvent($str, $type);
    }
  }
}

//-- 投票処理クラス (死者) --//
final class VoteHeaven extends VoteBase {
  const SITUATION = VoteAction::HEAVEN;

  protected static function Load() {
    if (DB::$SELF->IsDrop()) {
      VoteHTML::OutputResult(VoteMessage::ALREADY_DROP);
    }
    if (DB::$ROOM->IsOpenCast()) {
      VoteHTML::OutputResult(VoteMessage::ALREADY_OPEN);
    }
  }

  protected static function Vote() {
    if (! DB::$SELF->UpdateLive(UserLive::DROP)) {
      VoteHTML::OutputResult(VoteMessage::DB_ERROR);
    }

    //システムメッセージ
    $talk = new RoomTalkStruct(sprintf(VoteMessage::REVIVE_REFUSE_SUCCESS, DB::$SELF->handle_name));
    $talk->Set(TalkStruct::SCENE,     RoomScene::HEAVEN);
    $talk->Set(TalkStruct::LOCATION,  null);
    $talk->Set(TalkStruct::UNAME,     DB::$SELF->uname);
    $talk->Set(TalkStruct::FONT_TYPE, TalkVoice::NORMAL);
    DB::$ROOM->Talk($talk);

    if (DB::$ROOM->IsTest()) {
      return;
    }
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }
}

//-- 投票処理クラス (強制突然死/GM機能) --//
final class VoteForceSuddenDeath extends VoteBase {
  const SITUATION = VoteAction::FORCE_SUDDEN_DEATH;

  protected static function Load() {
    $target = DB::$USER->ByID(RQ::Fetch()->target_no); //投票先ユーザ
    self::ValidateTarget($target);

    //処理は実ユーザーに対して行う
    RoleManager::Stack()->Set(VoteForceSuddenDeathElement::TARGET, $target->GetReal());
  }

  protected static function Vote() {
    $target = RoleManager::Stack()->Get(VoteForceSuddenDeathElement::TARGET);
    GameAction::SuddenDeath([$target->id], DeadReason::FORCE_SUDDEN_DEATH);
    if (DB::$ROOM->IsTest()) {
      return;
    }
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }

  //投票先チェック
  private static function ValidateTarget(User $target) {
    if (null === $target->id) {
      self::Output(VoteMessage::NO_TARGET);
    } elseif (false === DB::$USER->IsVirtualLive($target->id)) { //投票画面は仮想ユーザー
      self::Output(VoteMessage::FORCE_SUDDEN_DEATH_DEAD);
    } elseif ($target->GetReal()->IsDead()) { //実ユーザーも一応生死判定を行っておく
      self::Output(VoteMessage::INVALID_SITUATION);
    } elseif ($target->IsDummyBoy()) {
      self::Output(VoteMessage::FORCE_SUDDEN_DEATH_DUMMY_BOY);
    }
  }

  //結果出力
  private static function Output($str) {
    VoteHTML::OutputResult(VoteMessage::FORCE_SUDDEN_DEATH_TITLE . $str);
  }
}

//-- 投票処理クラス (超過時間リセット/GM機能) --//
final class VoteResetTime extends VoteBase {
  const SITUATION = VoteAction::RESET_TIME;

  protected static function Vote() {
    RoomDB::UpdateTime(); //更新時間リセット

    //システムメッセージ
    $talk = new RoomTalkStruct(VoteMessage::RESET_TIME_SUCCESS);
    $talk->Set(TalkStruct::LOCATION, GM::DUMMY_BOY);
    $talk->Set(TalkStruct::UNAME,    DB::$SELF->uname);
    DB::$ROOM->Talk($talk);

    if (DB::$ROOM->IsTest()) {
      return;
    }
    DB::Commit();
    VoteHTML::OutputResult(VoteMessage::SUCCESS);
  }
}
