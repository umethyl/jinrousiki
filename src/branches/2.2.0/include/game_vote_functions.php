<?php
//-- 投票処理クラス --//
class Vote {
  //夜の投票のフィルタ取得
  static function GetNightFilter() {
    if (DB::$SELF->IsDummyBoy()) VoteHTML::OutputResult('夜：身代わり君の投票は無効です');
    foreach (array('', 'not_') as $header) {   //データを初期化
      foreach (array('action', 'submit') as $data) RoleManager::SetStack($header . $data, null);
    }
    if ($death_note = DB::$SELF->IsDoomRole('death_note')) { //デスノート
      /*
	配役設定上、初日に配布されることはなく、バグで配布された場合でも
	集計処理は実施されないので、ここではそのまま投票させておく。
	逆にスキップ判定を実施した場合、初日投票能力者が詰む。
      */
      //if (DB::$ROOM->IsDate(1)) VoteHTML::OutputResult('夜：初日は暗殺できません');
      if (DB::$ROOM->test_mode ||
	  ! self::CheckSelfVoteNight('DEATH_NOTE_DO', 'DEATH_NOTE_NOT_DO')) {
	$filter = RoleManager::LoadMain(new User('mage')); //上記のバグ対策用 (本来は assassin 相当)
	RoleManager::SetActor(DB::$SELF); //同一ユーザ判定用
	RoleManager::SetStack('action',     'DEATH_NOTE_DO');
	RoleManager::SetStack('not_action', 'DEATH_NOTE_NOT_DO');
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
  static function CheckVoteNight($action, $not_action = '') {
    if (self::CheckSelfVoteNight($action, $not_action)) VoteHTML::OutputResult('夜：投票済み');
  }

  //役職の人数通知リストを作成する
  static function GenerateRoleNameList(array $role_count_list, $css = false) {
    $chaos = DB::$ROOM->IsOption('chaos_open_cast_camp') ? 'camp' :
      (DB::$ROOM->IsOption('chaos_open_cast_role') ? 'role' : null);
    switch ($chaos) {
    case 'camp':
      $header = '出現陣営：';
      $main_type = '陣営';
      $main_role_list = array();
      foreach ($role_count_list as $role => $count) {
	if (RoleData::IsMain($role)) {
	  @$main_role_list[RoleData::GetCamp($role, true)] += $count;
	}
      }
      break;

    case 'role':
      $header = '出現役職種：';
      $main_type = '系';
      $main_role_list = array();
      foreach ($role_count_list as $role => $count) {
	if (RoleData::IsMain($role)) {
	  @$main_role_list[RoleData::GetGroup($role)] += $count;
	}
      }
      break;

    default:
      $header = '出現役職：';
      $main_type = '';
      $main_role_list = $role_count_list;
      break;
    }

    switch ($chaos) {
    case 'camp':
    case 'role':
      $sub_type = '系';
      $sub_role_list = array();
      foreach ($role_count_list as $role => $count) {
	if (! RoleData::IsSub($role)) continue;
	foreach (RoleData::$sub_role_group_list as $list) {
	  if (in_array($key, $list)) $sub_role_list[$list[0]] += $count;
	}
      }
      break;

    default:
      $sub_type = '';
      $sub_role_list = $role_count_list;
      break;
    }

    $stack = array();
    foreach (RoleData::GetDiff($main_role_list) as $role => $name) {
      if ($css) $name = RoleDataHTML::GenerateMain($role);
      $stack[] = $name . $main_type . $main_role_list[$role];
    }

    foreach (RoleData::GetDiff($sub_role_list, true) as $role => $name) {
      $stack[] = '(' . $name . $sub_type . $sub_role_list[$role] . ')';
    }
    return $header . implode('　', $stack);
  }

  //ゲーム開始投票
  static function VoteGameStart() {
    self::CheckSituation('GAMESTART');
    $str = 'ゲーム開始';
    if (DB::$SELF->IsDummyBoy(true)) { //出題者以外の身代わり君
      if (GameConfig::POWER_GM) { //強権モードによる強制開始処理
	if (! self::AggregateGameStart(true)) $str .= '：開始人数に達していません。';
	DB::Commit();
	VoteHTML::OutputResult($str);
      }
      else {
	VoteHTML::OutputResult($str . '：身代わり君は投票不要です');
      }
    }

    //投票済みチェック
    DB::$ROOM->LoadVote();
    if (in_array(DB::$SELF->id, DB::$ROOM->vote)) VoteHTML::OutputResult($str . '：投票済みです');

    if (DB::$SELF->Vote('GAMESTART')) { //投票処理
      self::AggregateGameStart(); //集計処理
      DB::Commit();
      VoteHTML::OutputResult($str . '：投票完了');
    }
    else {
      VoteHTML::OutputResult($str . '：データベースエラー');
    }
  }

  //Kick 投票
  static function VoteKick() {
    self::CheckSituation('KICK_DO'); //コマンドチェック
    $str = 'Kick 投票：';
    $target = DB::$USER->ByID(RQ::Get()->target_no); //投票先のユーザ情報を取得
    if (is_null($target->id) || $target->live == 'kick') {
      VoteHTML::OutputResult($str . '投票先が指定されていないか、すでに Kick されています');
    }
    if ($target->IsDummyBoy()) VoteHTML::OutputResult($str . '身代わり君には投票できません');
    if (! GameConfig::SELF_KICK && $target->IsSelf()) {
      VoteHTML::OutputResult($str . '自分には投票できません');
    }

    DB::$ROOM->LoadVote(true); //投票情報をロード
    $id = DB::$SELF->id;
    if (isset(DB::$ROOM->vote[$id]) && in_array($target->id, DB::$ROOM->vote[$id])) {
      VoteHTML::OutputResult($str . $target->handle_name . ' さんへ Kick 投票済み');
    }

    if (DB::$SELF->Vote('KICK_DO', $target->id)) { //投票処理
      DB::$ROOM->Talk($target->handle_name, 'KICK_DO', DB::$SELF->uname); //投票通知
      $vote_count = self::AggregateKick($target); //集計処理
      DB::Commit();
      $format = '投票完了：%s さん：%d 人目 (Kick するには %d 人以上の投票が必要です)';
      $str .= sprintf($format, $target->handle_name, $vote_count, GameConfig::KICK);
      VoteHTML::OutputResult($str);
    }
    else {
      VoteHTML::OutputResult($str . 'データベースエラー');
    }
  }

  //昼の投票
  static function VoteDay() {
    self::CheckSituation('VOTE_KILL'); //コマンドチェック
    $target = DB::$USER->ByReal(RQ::Get()->target_no); //投票先のユーザ情報を取得
    if (is_null($target->id)) VoteHTML::OutputResult('処刑：無効な投票先です');
    if ($target->IsSelf()) VoteHTML::OutputResult('処刑：自分には投票できません');
    if ($target->IsDead()) VoteHTML::OutputResult('処刑：死者には投票できません');

    //特殊イベントを取得
    $vote_duel = isset(DB::$ROOM->event->vote_duel) ? DB::$ROOM->event->vote_duel : null;
    if (is_array($vote_duel) && ! in_array(RQ::Get()->target_no, $vote_duel)) {
      VoteHTML::OutputResult('処刑：決選投票対象者以外には投票できません');
    }

    //投票済みチェック
    if (DB::$ROOM->test_mode) {
      if (array_key_exists(DB::$SELF->uname, RQ::GetTest()->vote->day)) {
	Text::p(DB::$SELF->uname, 'AlreadyVoted');
	return false;
      }
    }
    elseif (UserDB::IsVoteKill()) {
      VoteHTML::OutputResult('処刑：投票済み');
    }

    //-- 投票処理 --//
    $vote_number = 1; //投票数を初期化

    //メイン役職の補正
    RoleManager::SetActor(DB::$SELF); //投票者をセット
    foreach (RoleManager::Load('vote_do_main') as $filter) $filter->FilterVoteDo($vote_number);

    //サブ役職の補正
    if (! DB::$ROOM->IsEvent('no_authority')) { //蜃気楼ならスキップ
      RoleManager::SetActor(DB::$SELF->GetVirtual()); //仮想投票者をセット
      foreach (RoleManager::Load('vote_do_sub') as $filter) $filter->FilterVoteDo($vote_number);
    }

    //天候補正
    if (DB::$ROOM->IsEvent('hyper_random_voter')) $vote_number += Lottery::GetRange(0, 5);
    if ($vote_number < 0) $vote_number = 0; //マイナス補正

    if (! DB::$SELF->Vote('VOTE_KILL', $target->id, $vote_number)) { //投票処理
      VoteHTML::OutputResult('データベースエラー');
    }

    //システムメッセージ
    if (DB::$ROOM->test_mode) return true;
    DB::$ROOM->Talk($target->GetName(), 'VOTE_DO', DB::$SELF->uname);

    self::AggregateDay(); //集計処理
    DB::Commit();
    VoteHTML::OutputResult('投票完了');
  }

  //夜の投票
  static function VoteNight() {
    //-- イベント名と役職の整合チェック --//
    $filter = self::GetNightFilter();
    if (empty(RQ::Get()->situation)) {
      VoteHTML::OutputResult('夜：投票イベントが空です');
    }
    elseif (RQ::Get()->situation == RoleManager::GetStack('not_action')) {
      $not_action = true;
    }
    elseif (RQ::Get()->situation != RoleManager::GetStack('action')) {
      VoteHTML::OutputResult('夜：投票イベントが一致しません');
    }
    else {
      $add_action = RoleManager::GetStack('add_action');
      if (RQ::Get()->add_action && isset($add_action)) RQ::Set('situation', $add_action);
      $not_action = false;
    }
    //Text::p($filter);
    if (! DB::$ROOM->test_mode) self::CheckVoteNight(RQ::Get()->situation); //投票済みチェック

    //-- 投票処理 --//
    if ($not_action) { //投票キャンセルタイプは何もしない
      //投票処理
      if (! DB::$SELF->Vote(RQ::Get()->situation)) VoteHTML::OutputResult('データベースエラー');
      $id = DB::$SELF->role_id;
      DB::$ROOM->Talk('', RQ::Get()->situation, DB::$SELF->uname, '', null, null, $id);
    }
    else {
      $filter->CheckVoteNight();
      //RoleManager::p();
      if (! DB::$SELF->Vote(RQ::Get()->situation, RoleManager::GetStack('target_no'))) {
	VoteHTML::OutputResult('データベースエラー'); //投票処理
      }
      $str    = RoleManager::GetStack('target_handle');
      $action = RoleManager::GetStack('message');
      DB::$ROOM->Talk($str, $action, DB::$SELF->uname, '', null, null, DB::$SELF->role_id);
    }
    if (DB::$ROOM->test_mode) return;
    self::AggregateNight(); //集計処理
    foreach (DB::$USER->rows as $user) $user->UpdatePlayer(); //player 更新
    DB::Commit();
    VoteHTML::OutputResult('投票完了');
  }

  //死者の投票
  static function VoteHeaven() {
    self::CheckSituation('REVIVE_REFUSE'); //コマンドチェック
    if (DB::$SELF->IsDrop())     VoteHTML::OutputResult('蘇生辞退：投票済み'); //投票済みチェック
    if (DB::$ROOM->IsOpenCast()) VoteHTML::OutputResult('蘇生辞退：投票不要です'); //霊界公開判定

    //-- 投票処理 --//
    if (! DB::$SELF->Update('live', 'drop')) VoteHTML::OutputResult('データベースエラー');

    //システムメッセージ
    $str = 'システム：' . DB::$SELF->handle_name . 'さんは蘇生を辞退しました。';
    DB::$ROOM->Talk($str, null, DB::$SELF->uname, 'heaven', null, 'normal');
    DB::Commit();
    VoteHTML::OutputResult('投票完了');
  }

  //最終更新時刻リセット投票 (身代わり君専用)
  static function VoteResetTime() {
    self::CheckSituation('RESET_TIME'); //コマンドチェック

    //-- 投票処理 --//
    RoomDB::UpdateTime(); //更新時間リセット

    //システムメッセージ
    $str = 'システム：投票制限時間をリセットしました。';
    DB::$ROOM->Talk($str, null, DB::$SELF->uname, DB::$ROOM->scene, 'dummy_boy');
    DB::Commit();
    VoteHTML::OutputResult('投票完了');
  }

  //ゲーム開始投票集計処理
  static function AggregateGameStart($force_start = false) {
    $user_count = DB::$USER->GetUserCount(); //ユーザ総数を取得
    if (DB::$ROOM->test_mode) {
      $vote_count = $user_count;
    }
    else {
      self::CheckSituation('GAMESTART');

      //投票総数を取得
      if ($force_start) { //強制開始モード時はスキップ
	$vote_count = $user_count;
      }
      else {
	$vote_count = DB::$ROOM->LoadVote(); //投票情報をロード (ロック前の情報は使わない事)
	//クイズ村以外の身代わり君の分を加算
	if (DB::$ROOM->IsDummyBoy() && ! DB::$ROOM->IsQuiz()) $vote_count++;
      }
    }

    //規定人数に足りないか、全員投票していなければ処理終了
    if ($vote_count != $user_count || $vote_count < min(array_keys(CastConfig::$role_list))) {
      return false;
    }

    //-- 配役決定ルーチン --//
    DB::$ROOM->LoadOption(); //配役設定オプションの情報を取得
    //Text::p(DB::$ROOM->option_role, 'OptionRole');
    //Text::p(DB::$ROOM->option_list, 'OptionList');

    //配役決定用変数をセット
    $uname_list        = DB::$USER->GetLivingUsers(); //ユーザ名の配列
    $role_list         = Cast::Get($user_count); //役職リストを取得
    $fix_uname_list    = array(); //役職の決定したユーザ名を格納する
    $fix_role_list     = array(); //ユーザ名に対応する役職
    $remain_uname_list = array(); //希望の役職になれなかったユーザ名を一時的に格納
    //Text::p($uname_list, 'Uname');
    //Text::p($role_list, 'Role');

    //エラーメッセージ
    $error        = 'ゲームスタート[配役設定エラー]：%s。<br>管理者に問い合わせて下さい。';
    $reset_flag   = ! DB::$ROOM->test_mode;

    if (DB::$ROOM->IsDummyBoy()) { //身代わり君の役職を決定
      Cast::SetDummyBoy($fix_role_list, $role_list);
      if (count($fix_role_list) < 1) {
	VoteHTML::OutputResult(sprintf($error, '身代わり君に役が与えられていません'), $reset_flag);
      }
      $fix_uname_list[] = 'dummy_boy'; //決定済みリストに身代わり君を追加
      unset($uname_list[array_search('dummy_boy', $uname_list)]); //身代わり君を削除
      //Text::p($fix_role_list, 'dummy_boy');
    }

    shuffle($uname_list); //ユーザリストをランダムに取得
    //Text::p($uname_list, 'ShuffleUname');

    //希望役職を参照して一次配役を行う
    if (DB::$ROOM->IsOption('wish_role')) { //役職希望制の場合
      $wish_group = DB::$ROOM->IsChaosWish(); //特殊村用
      foreach ($uname_list as $uname) {
	do {
	  $role = DB::$USER->GetRole($uname); //希望役職を取得
	  if ($role == '' || ! Lottery::Percent(CastConfig::WISH_ROLE_RATE)) break;
	  $fix_role = $role;

	  if ($wish_group) { //特殊村はグループ単位で希望処理を行なう
	    $stack = array();
	    foreach ($role_list as $stack_role) {
	      if ($role == RoleData::GetGroup($stack_role)) $stack[] = $stack_role;
	    }
	    $fix_role = Lottery::Get($stack);
	  }
	  //希望役職の存在チェック
	  if (($role_key = array_search($fix_role, $role_list)) === false) break;

	  //希望役職があれば決定
	  $fix_uname_list[] = $uname;
	  $fix_role_list[]  = $fix_role;
	  unset($role_list[$role_key]);
	  continue 2;
	} while (false);
	$remain_uname_list[] = $uname; //決まらなかった場合は未決定リスト行き
      }
    }
    else {
      shuffle($role_list); //配列をシャッフル
      $fix_uname_list = array_merge($fix_uname_list, $uname_list);
      $fix_role_list  = array_merge($fix_role_list, $role_list);
      $role_list = array(); //残り配役リストをリセット
    }

    //一次配役の結果を検証
    $remain_uname_list_count = count($remain_uname_list); //未決定者の人数
    $role_list_count         = count($role_list); //残り配役数
    if ($remain_uname_list_count != $role_list_count) {
      $format = '配役未決定者の人数 (%d) と残り配役の数 (%d) が一致していません';
      $str    = sprintf($format, $remain_uname_list_count, $role_list_count);
      VoteHTML::OutputResult(sprintf($error, $str), $reset_flag);
    }

    //未決定者を二次配役
    if ($remain_uname_list_count > 0) {
      shuffle($role_list); //配列をシャッフル
      $fix_uname_list = array_merge($fix_uname_list, $remain_uname_list);
      $fix_role_list  = array_merge($fix_role_list, $role_list);
      $role_list      = array(); //残り配役リストをリセット
    }

    //二次配役の結果を検証
    $fix_uname_list_count = count($fix_uname_list); //決定者の人数
    if ($user_count != $fix_uname_list_count) {
      $format = '村人の人数 (%d) と配役決定者の人数 (%d) が一致していません';
      $str    = sprintf($format, $user_count, $fix_uname_list_count);
      VoteHTML::OutputResult(sprintf($error, $str), $reset_flag);
    }

    $fix_role_list_count = count($fix_role_list); //配役の数
    if ($fix_uname_list_count != $fix_role_list_count) {
      $format = '配役決定者の人数 (%d) と配役の数 (%d) が一致していません';
      $str    = sprintf($format, $fix_uname_list_count, $fix_role_list_count);
      VoteHTML::OutputResult(sprintf($error, $str), $reset_flag);
    }

    $role_list_count = count($role_list); //残り配役数
    if ($role_list_count > 0) {
      $format = '配役リストに余り (%d) があります';
      $str    = sprintf($format, $role_list_count);
      VoteHTML::OutputResult(sprintf($error, $str), $reset_flag);
    }

    //兼任となる役職の設定
    //オプションでつけるサブ役職
    RoleManager::SetStack('user_count', $user_count);
    RoleManager::SetStack('uname_list', $fix_uname_list);
    Cast::SetSubRole($fix_role_list);

    /*
    if (DB::$ROOM->IsOption('festival')) { //お祭り村 (内容は管理人が自由にカスタムする)
      $role = 'nervy';
      for ($i = 0; $i < $user_count; $i++) { //全員に自信家をつける
        $fix_role_list[$i] .= ' ' . $role;
      }
    }
    */
    //テスト用
    //Text::p($fix_uname_list); Text::p($fix_role_list); RoomDB::DeleteVote(); return false;

    //役職をDBに更新
    $role_count_list = array();
    $detective_list  = array();
    $is_detective    = DB::$ROOM->IsOption('detective');
    if (DB::$ROOM->IsOption('joker')) $role_count_list['joker'] = 1; //joker[2] 対策
    for ($i = 0; $i < $user_count; $i++) {
      $role = $fix_role_list[$i];
      $user = DB::$USER->ByUname($fix_uname_list[$i]);
      $user->ChangeRole($role);
      $stack = explode(' ', $role);
      foreach ($stack as $role) @$role_count_list[$role]++;
      if ($is_detective && in_array('detective_common', $stack)) $detective_list[] = $user;
    }

    //KICK の後処理
    $id = 1;
    foreach (DB::$USER->rows as $user) {
      if ($user->id != $id) {
	$user->UpdateID($id);
	$user->id = $id;
      }
      $id++;
    }
    foreach (DB::$USER->kick as $user) $user->UpdateID(-1);

    //役職リスト通知
    if (DB::$ROOM->IsOptionGroup('chaos')) {
      $sentence = DB::$ROOM->IsOptionGroup('chaos_open_cast') ?
	self::GenerateRoleNameList($role_count_list) : Message::$chaos;
    }
    else {
      $sentence = self::GenerateRoleNameList($role_count_list);
    }

    //ゲーム開始
    DB::$ROOM->date++;
    DB::$ROOM->scene = DB::$ROOM->IsOption('open_day') ? 'day' : 'night';
    foreach (DB::$USER->rows as $user) $user->UpdatePlayer(); //player 登録
    if (! DB::$ROOM->test_mode) {
      RoomDB::Start();
      //JinrouRSS::Update(); //RSS機能はテスト中
    }
    DB::$ROOM->Talk($sentence);
    if ($is_detective && count($detective_list) > 0) { //探偵村の指名
      $detective_user = Lottery::Get($detective_list);
      DB::$ROOM->Talk('探偵は ' . $detective_user->handle_name . ' さんです');
      if (DB::$ROOM->IsOption('gm_login') && DB::$ROOM->IsOption('not_open_cast') &&
	  $user_count > 7) {
	$detective_user->ToDead(); //霊界探偵モードなら探偵を霊界に送る
      }
    }
    if (DB::$ROOM->test_mode) return true;

    RoomDB::UpdateTime(); //最終書き込み時刻を更新
    Winner::Check(); //配役時に勝敗が決定している可能性があるので勝敗判定を行う
    return true;
  }

  //昼の投票集計処理
  static function AggregateDay() {
    //-- 投票処理実行判定 --//
    if (! DB::$ROOM->test_mode) self::CheckSituation('VOTE_KILL'); //コマンドチェック
    $user_list = DB::$USER->GetLivingUsers(); //生存者を取得
    if (DB::$ROOM->LoadVote() != count($user_list)) return false; //投票数と照合

    //-- 初期化処理 --//
    $live_uname_list   = array(); //生存者リスト (ユーザ名)
    $vote_message_list = array(); //システムメッセージ用 (ユーザID => array())
    $vote_target_list  = array(); //投票リスト (ユーザ名 => 投票先ユーザ名)
    $vote_count_list   = array(); //得票リスト (ユーザ名 => 投票数)
    RoleManager::InitStack('pharmacist_result'); //薬師系の鑑定結果

    //現在のジョーカー所持者の ID
    if (DB::$ROOM->IsOption('joker')) RoleManager::SetStack('joker_id', DB::$USER->SetJoker());

    //-- 投票データ収集 --//
    //Text::p(DB::$ROOM->vote);
    foreach (DB::$ROOM->vote as $id => $list) { //初期得票データを収集
      $target_uname = DB::$USER->ByVirtual($list['target_no'])->uname;
      if (! isset($vote_count_list[$target_uname])) $vote_count_list[$target_uname] = 0;
      $vote_count_list[$target_uname] += $list['vote_number'];
    }
    //Text::p($vote_count_list, 'VoteCountBase');

    foreach ($user_list as $id => $uname) { //個別の投票データを収集
      $list   = DB::$ROOM->vote[$id]; //投票データ
      $user   = DB::$USER->ByVirtual($id); //仮想ユーザを取得
      $target = DB::$USER->ByVirtual($list['target_no']); //投票先の仮想ユーザ
      $vote   = @(int)$list['vote_number']; //投票数
      $poll   = @(int)$vote_count_list[$user->uname]; //得票数

      //得票補正 (メイン役職)
      RoleManager::SetActor(DB::$USER->ByReal($user->id));
      foreach (RoleManager::Load('vote_poll_main') as $filter) $filter->FilterVotePoll($poll);

      RoleManager::SetActor($user);
      if (! DB::$ROOM->IsEvent('no_authority')) { //得票補正 (サブ役職 / 蜃気楼ならスキップ)
	foreach (RoleManager::Load('vote_poll_sub') as $filter) $filter->FilterVotePoll($poll);
      }
      if ($poll < 0) $poll = 0; //マイナス補正

      //リストにデータを追加
      $live_uname_list[$user->id]     = $user->uname;
      $vote_target_list[$user->uname] = $target->uname;
      $vote_count_list[$user->uname]  = $poll;
      $vote_message_list[$user->id]   = array('target_name' => $target->handle_name,
					      'vote' => $vote, 'poll' => $poll);
      if (DB::$USER->ByReal($user->id)->IsRole('philosophy_wizard')) { //賢者の魔法発動
	RoleManager::LoadMain($user)->SetWizard();
	//Text::p($user->virtual_role, '◆Wizard: ' . $user->uname);
      }
      foreach (RoleManager::Load('vote_day', false, true) as $filter) {
	$filter->SetVoteDay($target->uname);
      }
    }
    RoleManager::SetStack('target', $vote_target_list);
    //RoleManager::p(null, 'RoleStack');
    //Text::p($vote_message_list, 'VoteMessage');
    ksort($vote_message_list); //投票順をソート (憑依対応)
    $stack = array();
    foreach ($vote_message_list as $id => $list) {
      $stack[DB::$USER->ByID($id)->uname] = $list;
    }
    $vote_message_list = $stack;
    //Text::p($vote_message_list, 'VoteMessage [sort]');

    //-- 投票数補正処理 --//
    //Text::p($vote_count_list, 'VoteCount');
    foreach (RoleManager::LoadFilter('vote_correct') as $filter) {
      $filter->VoteCorrect($vote_message_list, $vote_count_list);
    }

    //-- 投票結果登録 --//
    $max_poll   = 0; //最多得票数
    $vote_count = DB::$ROOM->revote_count + 1;
    $items = 'room_no, date, count, handle_name, target_name, vote, poll';
    $values_header = sprintf('%d, %d, %d, ', DB::$ROOM->id, DB::$ROOM->date, $vote_count);

    //タブ区切りのデータをシステムメッセージに登録
    foreach ($vote_message_list as $uname => $stack) {
      extract($stack); //配列を展開
      if ($poll > $max_poll) $max_poll = $poll; //最大得票数を更新
      if (DB::$ROOM->test_mode) continue;
      $handle_name = DB::$USER->ByUname($uname)->handle_name; //憑依追跡済み
      $values = $values_header . "'{$handle_name}', '{$target_name}', {$vote}, {$poll}";
      DB::Insert('result_vote_kill', $items, $values);
    }

    //-- 処刑者決定処理 --//
    RoleManager::SetStack('vote_kill_uname', ''); //処刑者 (ユーザ名)
    //最大得票数のユーザ名 (処刑候補者) のリストを取得
    RoleManager::SetStack('max_voted', array_keys($vote_count_list, $max_poll));
    $stack = RoleManager::GetStack('max_voted');
    //Text::p($stack, 'MaxVoted');
    if (count($stack) == 1) { //一人だけなら決定
      RoleManager::SetStack('vote_kill_uname', array_shift($stack));
    }
    else { //決定能力者判定
      RoleManager::SetStack('vote_possible', $stack);
      foreach (RoleManager::LoadFilter('vote_kill') as $filter) $filter->DecideVoteKill();
      if (RoleManager::IsEmpty('vote_kill_uname') && DB::$ROOM->IsOption('settle')) { //決着村
	$vote_kill_uname = Lottery::Get(RoleManager::GetStack('vote_possible'));
	RoleManager::SetStack('vote_kill_uname', $vote_kill_uname);
      }
    }
    //RoleManager::p('vote_kill_uname', 'VoteTarget');

    if (! RoleManager::IsEmpty('vote_kill_uname')) { //-- 処刑実行処理 --//
      //-- 処刑者情報収取 --//
      $uname = RoleManager::GetStack('vote_kill_uname'); //ユーザ情報を取得
      $vote_target = DB::$USER->ByRealUname($uname);
      DB::$USER->Kill($vote_target->id, 'VOTE_KILLED'); //処刑処理
      //処刑者を生存者リストから除く
      unset($live_uname_list[array_search($uname, $live_uname_list)]);
      $voter_list = array_keys($vote_target_list, $vote_target->uname); //投票した人を取得

      //薬師系の毒鑑定情報収集
      foreach (RoleManager::LoadFilter('distinguish_poison') as $filter) $filter->SetDetox();

      do { //-- 処刑者の毒処理 --//
	if (! $vote_target->IsPoison()) break; //毒能力の発動判定

	//薬師系の解毒判定 (夢毒者は対象外)
	$role  = 'alchemy_pharmacist'; //錬金術師
	$actor = $vote_target->GetVirtual();  //投票データは仮想ユーザ
	$actor->detox = false;
	$actor->$role = false;
	RoleManager::SetActor($actor);
	if (! $vote_target->IsRole('dummy_poison')) {
	  foreach (RoleManager::LoadFilter('detox') as $filter) $filter->Detox();
	  if (RoleManager::GetActor()->detox) break;
	}

	//毒の対象オプションをチェックして初期候補者リストを作成後に対象者を取得
	$stack = GameConfig::POISON_ONLY_VOTER ? $voter_list : $live_uname_list;
	if (RoleManager::GetActor()->$role || DB::$ROOM->IsEvent($role)) {
	  $user = new User($role);
	} else {
	  $user = $vote_target;
	}
	$poison_target_list = RoleManager::LoadMain($user)->GetPoisonVoteTarget($stack);
	//Text::p($poison_target_list, '◆Target [poison]');
	if (count($poison_target_list) < 1) break;

	$poison_target = DB::$USER->ByID(Lottery::Get($poison_target_list)); //対象者を決定
	if ($poison_target->IsActive('resist_wolf')) { //抗毒判定
	  $poison_target->LostAbility();
	  break;
	}
	DB::$USER->Kill($poison_target->id, 'POISON_DEAD'); //死亡処理

	$role = 'chain_poison'; //連毒者の処理
	if ($poison_target->IsRole($role)) RoleManager::GetClass($role)->Poison($poison_target);
      } while (false);
      //RoleManager::p('pharmacist_result', 'EndDetox');

      //-- 処刑者カウンター処理 --//
      RoleManager::SetActor($vote_target);
      foreach (RoleManager::Load('vote_kill_counter') as $filter) {
	$filter->VoteKillCounter($voter_list);
      }

      //-- 特殊投票発動者の処理 --//
      $vote_target->stolen_flag = false;
      foreach (RoleManager::LoadFilter('vote_action') as $filter) $filter->VoteAction();

      //-- 霊能者系の処理 --//
      //火車の妨害判定
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
      //Text::p($role_flag, 'ROLE_FLAG');

      if (! DB::$ROOM->IsEvent('new_moon')) { //新月ならスキップ
	$role = 'mimic_wizard';
	if (isset($role_flag->$role)) { //物真似師の処理
	  RoleManager::GetClass($role)->Necromancer($vote_target, $stolen_flag);
	}

	$role = 'spiritism_wizard';
	if (isset($role_flag->$role)) {  //交霊術師の処理
	  $filter = RoleManager::LoadMain(new User($role)); //$actor 参照あり
	  $wizard_flag->{$filter->SetWizard()} = true;
	  $wizard_action = 'SPIRITISM_WIZARD_RESULT';
	  if (isset($wizard_flag->sex_necromancer)) {
	    $result = $filter->Necromancer($vote_target, $stolen_flag);
	    DB::$ROOM->ResultAbility($wizard_action, $result, $vote_target->GetName());
	  }
	}
      }

      $name = $vote_target->GetName();
      foreach (RoleFilterData::$necromancer as $role) {
	if ($role_flag->$role || $wizard_flag->$role) {
	  $str = RoleManager::GetClass($role)->Necromancer($vote_target, $stolen_flag);
	  if (is_null($str)) continue;
	  if ($role_flag->$role) {
	    DB::$ROOM->ResultAbility(strtoupper($role . '_result'), $str, $name);
	  }
	  if ($wizard_flag->$role) DB::$ROOM->ResultAbility($wizard_action, $str, $name);
	}
      }
    }

    //-- 得票カウンター処理 --//
    foreach (RoleManager::LoadFilter('voted_reaction') as $filter) $filter->VotedReaction();

    //-- ショック死処理 --//
    //判定用データを登録 (投票者対象ユーザ名 => 人数)
    RoleManager::SetStack('count', array_count_values($vote_target_list));
    //RoleManager::p('count', 'count');

    RoleManager::InitStack('thunderbolt'); //青天の霹靂判定用
    if (DB::$ROOM->IsEvent('thunderbolt')) {
      RoleManager::GetClass('thunder_brownie')->SetThunderboltTarget($user_list);
    }
    else {
      foreach (RoleManager::LoadFilter('thunderbolt') as $filter) {
	$filter->SetThunderbolt($user_list);
      }
    }
    //RoleManager::p('thunderbolt', 'ThunderboltTarget');

    foreach ($live_uname_list as $uname) {
      $actor = DB::$USER->ByUname($uname); //$live_uname_list は仮想ユーザ名
      $actor->cured_flag = false;
      RoleManager::SetActor($actor);
      $data = in_array($uname, RoleManager::GetStack('thunderbolt')) ? 'THUNDERBOLT' : '';
      RoleManager::SetStack('sudden_death', $data);
      if (! DB::$ROOM->IsEvent('no_sudden_death')) { //凪ならスキップ
	foreach (RoleManager::Load('sudden_death_sub') as $filter) $filter->SuddenDeath();
      }
      foreach (RoleManager::Load('sudden_death_main') as $filter) $filter->SuddenDeath();
      if (RoleManager::IsEmpty('sudden_death')) continue;

      foreach (RoleManager::LoadFilter('cure') as $filter) $filter->Cure(); //薬師系の治療判定
      if (! RoleManager::GetActor()->cured_flag) {
	$id = RoleManager::GetActor()->id;
	DB::$USER->SuddenDeath($id, 'SUDDEN_DEATH', RoleManager::GetStack('sudden_death'));
      }
    }

    //道連れ処理
    foreach (RoleManager::LoadFilter('followed') as $filter) $filter->Followed($user_list);

    $role = 'pharmacist'; //薬師系の鑑定結果を登録
    $name = $role . '_result';
    //RoleManager::p($name, "◆Result [{$role}]");
    if (count(RoleManager::GetStack($name)) > 0) RoleManager::GetClass($role)->SaveResult();
    RoleManager::UnsetStack($name);

    RoleManager::GetClass('lovers')->Followed(); //恋人後追い処理
    RoleManager::GetClass('medium')->InsertResult(); //巫女のシステムメッセージ

    if (! RoleManager::IsEmpty('vote_kill_uname')) { //夜に切り替え
      //-- 処刑得票カウンターの処理 --//
      foreach (RoleManager::LoadFilter('vote_kill_reaction') as $filter) {
	$filter->VoteKillReaction();
      }

      if (DB::$ROOM->IsEvent('frostbite')) { //-- 雪の処理 --//
	$stack = array();
	foreach ($user_list as $id => $uname) {
	  $user = DB::$USER->ByID($id);
	  if ($user->IsLive(true) && ! $user->IsAvoid(true)) $stack[] = $user->id;
	}
	//Text::p($stack, '◆Target [frostbite]');
	DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, 'frostbite');
      }
      elseif (DB::$ROOM->IsEvent('psycho_infected')) { //-- 濃霧の処理 --//
	$stack = array();
	foreach ($user_list as $id => $uname) {
	  $user = DB::$USER->ByID($id);
	  if ($user->IsLive(true) && ! $user->IsAvoid(true) &&
	      ! $user->IsRole('psycho_infected') && ! $user->IsCamp('vampire')) {
	    $stack[] = $user->id;
	  }
	}
	//Text::p($stack, '◆Target [psycho_infected]');
	DB::$USER->ByID(Lottery::Get($stack))->AddRole('psycho_infected');
      }

      if ($joker_flag = DB::$ROOM->IsOption('joker')) { //ジョーカー移動判定
	$joker_filter = RoleManager::GetClass('joker');
	$joker_flag   = $joker_filter->SetJoker();
      }

      DB::$ROOM->ChangeNight();
      if (Winner::Check()) {
	if ($joker_flag) $joker_filter->FinishJoker();
      }
      else {
	if ($joker_flag) $joker_filter->ResetJoker();
	self::InsertRandomMessage(); //ランダムメッセージ
      }
      if (DB::$ROOM->test_mode) return $vote_message_list;
      DB::$ROOM->SkipNight();
    }
    else { //再投票処理
      if (DB::$ROOM->test_mode) return $vote_message_list;

      //処刑投票回数を増やす
      DB::$ROOM->revote_count++;
      RoomDB::UpdateVoteCount(true);
      //システムメッセージ
      DB::$ROOM->Talk(sprintf('再投票になりました( %d 回目)', DB::$ROOM->revote_count));

      if (Winner::Check(true) && DB::$ROOM->IsOption('joker')) { //勝敗判定＆ジョーカー処理
	DB::$USER->ByID(RoleManager::GetStack('joker_id'))->AddJoker();
      }
    }
    foreach (DB::$USER->rows as $user) $user->UpdatePlayer(); //player 更新
    RoomDB::UpdateTime(); //最終書き込み時刻を更新
  }

  //夜の集計処理
  static function AggregateNight($skip = false) {
    DB::$ROOM->LoadVote(); //投票情報を取得
    //Text::p(DB::$ROOM->vote, 'VoteRow');

    $vote_data = DB::$ROOM->ParseVote(); //コマンド毎に分割
    //Text::p($vote_data, 'VoteData');

    if (! $skip) {
      foreach (DB::$USER->rows as $user) { //未投票チェック
	if ($user->CheckVote($vote_data) === false) {
	  if (DB::$ROOM->test_mode) Text::p($user->uname, $user->main_role);
	  return false;
	}
      }
    }

    //処理対象コマンドチェック
    $stack = array('MAGE_DO', 'STEP_MAGE_DO', 'VOODOO_KILLER_DO', 'MIND_SCANNER_DO', 'WOLF_EAT',
		   'STEP_WOLF_EAT', 'SILENT_WOLF_EAT', 'JAMMER_MAD_DO', 'VOODOO_MAD_DO', 'STEP_DO',
		   'VOODOO_FOX_DO', 'CHILD_FOX_DO', 'FAIRY_DO');
    if (DB::$ROOM->IsDate(1)) {
      $stack[] = 'MANIA_DO';
    }
    else {
      array_push($stack, 'GUARD_DO', 'STEP_GUARD_DO', 'ANTI_VOODOO_DO', 'REPORTER_DO',
		 'POISON_CAT_DO', 'ASSASSIN_DO', 'WIZARD_DO', 'SPREAD_WIZARD_DO', 'ESCAPE_DO',
		 'DREAM_EAT', 'TRAP_MAD_DO', 'POSSESSED_DO', 'VAMPIRE_DO', 'STEP_VAMPIRE_DO',
		 'OGRE_DO', 'DEATH_NOTE_DO');
    }
    foreach ($stack as $action) {
      if (! isset($vote_data[$action])) $vote_data[$action] = array();
    }
    //Text::p($vote_data, 'VoteData: Fill');

    //-- 変数の初期化 --//
    $stack = array('trap', 'trapped', 'snow_trap', 'frostbite', 'guard', 'gatekeeper_guard',
		   'dummy_guard', 'barrier_wizard', 'escaper', 'sacrifice', 'anti_voodoo',
		   'anti_voodoo_success', 'reverse_assassin', 'possessed');
    foreach ($stack as $name) RoleManager::InitStack($name);

    //-- 天候の処理 --//
    $stack = array();
    if (DB::$ROOM->IsEvent('full_moon')) { //満月
      array_push($stack, 'GUARD_DO', 'STEP_GUARD_DO', 'ANTI_VOODOO_DO', 'REPORTER_DO',
		 'JAMMER_MAD_DO', 'VOODOO_MAD_DO', 'VOODOO_FOX_DO');
    }
    elseif (DB::$ROOM->IsEvent('new_moon')) { //新月
      $skip = true; //影響範囲に注意
      array_push($stack, 'MAGE_DO', 'STEP_MAGE_DO', 'VOODOO_KILLER_DO', 'WIZARD_DO',
		 'SPREAD_WIZARD_DO', 'CHILD_FOX_DO', 'VAMPIRE_DO', 'STEP_VAMPIRE_DO', 'FAIRY_DO');
    }
    elseif (DB::$ROOM->IsEvent('no_contact')) { //花曇 (さとり系に注意)
      $skip = true; //影響範囲に注意
      array_push($stack, 'STEP_GUARD_DO', 'REPORTER_DO', 'ASSASSIN_DO', 'MIND_SCANNER_DO',
		 'ESCAPE_DO', 'TRAP_MAD_DO', 'VAMPIRE_DO', 'STEP_VAMPIRE_DO', 'OGRE_DO');
    }
    elseif (DB::$ROOM->IsEvent('no_trap')) { //雪明り
      $stack[] = 'TRAP_MAD_DO';
    }
    elseif (DB::$ROOM->IsEvent('no_dream')) { //熱帯夜
      $stack[] = 'DREAM_EAT';
    }
    foreach ($stack as $action) $vote_data[$action] = array();

    //-- 魔法使い系の振り替え処理 --//
    if (DB::$ROOM->date > 1) {
      foreach ($vote_data['WIZARD_DO'] as $id => $target_id) {
	$action = RoleManager::LoadMain(DB::$USER->ByID($id))->SetWizard();
	//Text::p(RoleManager::GetActor()->virtual_role, "Wizard: {$id}: {$action}");
	$vote_data[$action][$id] = $target_id;
      }
    }
    RoleManager::SetStack('vote_data', $vote_data);

    //-- 足音レイヤー --//
    if (! DB::$ROOM->IsEvent('no_step')) { //地吹雪は無効
      $stack = array('STEP_MAGE_DO', 'STEP_WOLF_EAT', 'STEP_DO');
      if (DB::$ROOM->date > 1) array_push($stack, 'STEP_GUARD_DO', 'STEP_VAMPIRE_DO');
      foreach ($stack as $action) { //足音処理
	foreach ($vote_data[$action] as $id => $target_id) {
	  RoleManager::LoadMain(DB::$USER->ByID($id))->Step(explode(' ', $target_id));
	}
      }

      if (DB::$ROOM->IsEvent('random_step')) { //霜柱の処理
	$stack = array();
	foreach (DB::$USER->rows as $user) {
	  if (DB::$USER->IsVirtualLive($user->id)) $stack[] = $user->id;
	}
	//Text::p($stack, '◆random_step');
	shuffle($stack);
	$count = 0;
	foreach ($stack as $id) {
	  if (! Lottery::Percent(20)) continue;
	  DB::$ROOM->ResultDead($id, 'STEP');
	  if (++$count > 2) break;
	}
      }

      foreach ($vote_data['SILENT_WOLF_EAT'] as $id => $target_id) { //ステルス投票カウントアップ
	DB::$USER->ByID($id)->LostAbility();
      }
    }

    //-- 接触レイヤー --//
    $wolf_target = null;
    foreach (array('WOLF_EAT', 'STEP_WOLF_EAT', 'SILENT_WOLF_EAT') as $action) { //人狼の情報収集
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
    RoleManager::SetStack('wolf_target', $wolf_target);
    RoleManager::SetStack('voted_wolf',  $voted_wolf);

    if (DB::$ROOM->date > 1) {
      foreach ($vote_data['TRAP_MAD_DO'] as $id => $target_id) { //罠能力者の設置処理
	RoleManager::LoadMain(DB::$USER->ByID($id))->SetTrap(DB::$USER->ByID($target_id));
      }

      $role = 'trap_wolf'; //狡狼の自動設置処理 (花曇・雪明りは無効)
      if (DB::$ROOM->date > 2 && ! DB::$ROOM->IsEvent('no_contact') &&
	  ! DB::$ROOM->IsEvent('no_trap') && DB::$USER->IsAppear($role)) {
	foreach (DB::$USER->role[$role] as $id) {
	  $user = DB::$USER->ByID($id);
	  if ($user->IsLive()) RoleManager::LoadMain($user)->SetTrap();
	}
      }

      if (count(RoleManager::GetStack('trap')) > 0) RoleManager::SetClass('trap_mad');
      foreach (RoleManager::LoadFilter('trap') as $filter) $filter->TrapToTrap(); //罠能力者の罠判定
      //RoleManager::p('trap',      '◆Target [trap]');
      //RoleManager::p('trapped',   '◆Trap [trap]');
      //RoleManager::p('snow_trap', '◆Target [snow_trap]');
      //RoleManager::p('frostbite', '◆Trap [frostbite]');

      foreach ($vote_data['GUARD_DO'] as $id => $target_id) { //護衛能力者の情報収集
	RoleManager::LoadMain(DB::$USER->ByID($id))->SetGuard(DB::$USER->ByID($target_id));
      }

      foreach ($vote_data['STEP_GUARD_DO'] as $id => $target_id) { //山立の情報収集
	$target = DB::$USER->ByID(array_pop(explode(' ', $target_id)));
	RoleManager::LoadMain(DB::$USER->ByID($id))->SetGuard($target);
      }
      if (count(RoleManager::GetStack('guard')) > 0) RoleManager::SetClass('guard');
      //RoleManager::p('guard',       '◆Target [guard]');
      //RoleManager::p('dummy_guard', '◆Target [dummy_guard]');

      foreach ($vote_data['SPREAD_WIZARD_DO'] as $id => $target_list) { //結界師の情報収集
	RoleManager::LoadMain(DB::$USER->ByID($id))->SetGuard($target_list);
      }
      //RoleManager::p('barrier_wizard', '◆Target [barrier]');

      foreach ($vote_data['ESCAPE_DO'] as $id => $target_id) { //逃亡者系の情報収集
	RoleManager::LoadMain(DB::$USER->ByID($id))->Escape(DB::$USER->ByID($target_id));
      }
      //RoleManager::p('escaper', '◆Target [escaper]');
    }

    //-- 人狼の襲撃成功判定 --//
    RoleManager::GetClass('wolf')->WolfEat($skip);
    //RoleManager::p('possessed', '◆Possessed [wolf]');

    if (DB::$ROOM->date > 1) {
      foreach ($vote_data['DEATH_NOTE_DO'] as $id => $target_id) { //デスノートの処理
	if (DB::$USER->ByID($id)->IsDead(true)) continue; //直前に死んでいたら無効
	DB::$USER->Kill($target_id, 'ASSASSIN_KILLED');
      }

      if (! DB::$ROOM->IsEvent('no_hunt')) { //川霧ならスキップ
	foreach (RoleManager::GetStack('guard') as $id => $target_id) { //狩人系の狩り判定
	  $user = DB::$USER->ByID($id);
	  if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	  RoleManager::LoadMain($user)->Hunt(DB::$USER->ByID($target_id));
	}
      }
      foreach (RoleManager::LoadFilter('trap') as $filter) $filter->DelayTrapKill(); //罠死処理

      //-- 吸血 --//
      $role = 'vampire';
      $name = $role . '_kill';
      RoleManager::InitStack($role); //吸血対象者リスト
      RoleManager::InitStack($name); //吸血死対象者リスト
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
      //RoleManager::p($role, "◆Target [{$role}]");
      //RoleManager::p($name, "◆Target [{$name}]");
      if (count(RoleManager::GetStack($role)) > 0 || count(RoleManager::GetStack($name)) > 0) {
	RoleManager::GetClass($role)->VampireKill();
      }
      RoleManager::UnsetStack($role);
      RoleManager::UnsetStack($name);

      //-- 暗殺 --//
      $role = 'assassin';
      RoleManager::InitStack($role); //暗殺対象者リスト
      foreach ($vote_data['ASSASSIN_DO'] as $id => $target_id) { //暗殺能力者の処理
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->SetAssassin(DB::$USER->ByID($target_id));
      }
      //RoleManager::p($role, "◆Target [{$role}]");
      if (RoleManager::ExistStack($role)) RoleManager::GetClass($role)->AssassinKill();
      RoleManager::UnsetStack($role);

      //-- 人攫い --//
      $role = 'ogre';
      RoleManager::InitStack($role); //人攫い対象者リスト
      foreach ($vote_data['OGRE_DO'] as $id => $target_id) { //鬼の処理
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->SetAssassin(DB::$USER->ByID($target_id));
      }
      foreach (RoleManager::LoadFilter('trap') as $filter) $filter->DelayTrapKill(); //罠死処理
      //RoleManager::p($role, "◆Target [{$role}]");
      if (RoleManager::ExistStack($role)) RoleManager::GetClass($role)->AssassinKill();
      RoleManager::UnsetStack($role);

      //オシラ遊びの処理
      $role = 'death_selected';
      foreach (DB::$USER->rows as $user) {
	if ($user->IsDead(true)) continue;
	if ($user->GetVirtual()->IsDoomRole($role)) DB::$USER->Kill($user->id, 'PRIEST_RETURNED');
      }

      $role = 'reverse_assassin'; //反魂師の暗殺処理
      $name = 'reverse';
      RoleManager::InitStack($name); //反魂対象リスト
      if (RoleManager::ExistStack($role)) RoleManager::GetClass($role)->AssassinKill();
      //RoleManager::p($name, "◆Target [{$name}]");
      RoleManager::UnsetStack($role);

      $role = 'frostbite';
      //RoleManager::p($role, "◆Target [{$role}]");
      foreach (RoleManager::GetStack($role) as $id => $flag) { //凍傷処理
	$target = DB::$USER->ByID($id);
	if ($target->IsLive(true)) $target->AddDoom(1, $role);
      }
      RoleManager::UnsetStack($role);

      //-- 夢レイヤー --//
      foreach ($vote_data['DREAM_EAT'] as $id => $target_id) { //獏の処理
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->DreamEat(DB::$USER->ByID($target_id));
      }

      $hunted_list = array(); //狩り成功者リスト
      foreach (RoleManager::LoadFilter('guard_dream') as $filter) $filter->DreamGuard($hunted_list);
      foreach (RoleManager::LoadFilter('guard_dream') as $filter) $filter->DreamHunt($hunted_list);
      unset($hunted_list);

      //-- 呪いレイヤー --//
      $role = 'anti_voodoo';
      foreach ($vote_data['ANTI_VOODOO_DO'] as $id => $target_id) { //厄神の情報収集
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->SetGuard(DB::$USER->ByID($target_id));
      }
      //RoleManager::p($role, "◆Target [{$role}]");
    }

    $role = 'voodoo_killer';
    $name = $role . '_success';
    RoleManager::InitStack($role); //陰陽師の解呪対象リスト
    RoleManager::InitStack($name); //陰陽師の解呪成功者対象リスト
    foreach ($vote_data['VOODOO_KILLER_DO'] as $id => $target_id) { //陰陽師の情報収集
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->Mage(DB::$USER->ByID($target_id));
    }
    //RoleManager::p($role, "◆Target [{$role}]");
    //RoleManager::p($name, "◆Success [{$role}]");

    //呪術系能力者の処理
    $name = 'voodoo';
    RoleManager::InitStack($name); //呪術対象リスト
    foreach (array('VOODOO_MAD_DO', 'VOODOO_FOX_DO') as $action) {
      foreach ($vote_data[$action] as $id => $target_id) {
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->SetVoodoo(DB::$USER->ByID($target_id));
      }
    }
    //RoleManager::p($name, "◆Target [{$name}]");
    //RoleManager::p('voodoo_killer_success', "◆Success [voodoo_killer/{$name}]");
    //RoleManager::p('anti_voodoo_success',   "◆Success [anti_voodoo/{$name}]");

    //呪術系能力者の対象先が重なった場合は呪返しを受ける
    if (RoleManager::ExistStack($name)) RoleManager::GetClass('voodoo_mad')->VoodooToVoodoo();

    //-- 占いレイヤー --//
    $name = 'jammer';
    RoleManager::InitStack($name); //占い妨害対象リスト
    foreach ($vote_data['JAMMER_MAD_DO'] as $id => $target_id) { //占い妨害能力者の処理
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効
      RoleManager::LoadMain($user)->SetJammer(DB::$USER->ByID($target_id));
    }
    //RoleManager::p($name, "◆Target [{$name}]");
    //RoleManager::p('anti_voodoo_success',   "◆Success [anti_voodoo/{$name}]");

    $name = 'phantom';
    RoleManager::InitStack($name); //幻系の発動者リスト
    //占い系の処理
    foreach (array('MAGE_DO', 'CHILD_FOX_DO', 'FAIRY_DO') as $action) {
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
    //RoleManager::p($name, "◆Target [{$name}]");
    foreach (array_keys(RoleManager::GetStack($name)) as $id) DB::$USER->ByID($id)->LostAbility();
    RoleManager::UnsetStack($name);

    if (DB::$ROOM->IsDate(1)) {
      //-- コピーレイヤー --//
      foreach ($vote_data['MIND_SCANNER_DO'] as $id => $target_id) { //さとり系の処理
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->MindScan(DB::$USER->ByID($target_id));
      }

      foreach ($vote_data['MANIA_DO'] as $id => $target_id) { //神話マニアの処理
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	RoleManager::LoadMain($user)->Copy(DB::$USER->ByID($target_id));
      }

      if (! DB::$ROOM->IsOpenCast()) {
	foreach (DB::$USER->rows as $user) { //天人の帰還処理
	  if ($user->IsRole('revive_priest')) RoleManager::LoadMain($user)->PriestReturn();
	}
      }
      RoleManager::GetClass('exchange_angel')->Exchange(); //魂移使の処理
    }
    else {
      //-- 尾行レイヤー --//
      foreach (array('REPORTER_DO', 'MIND_SCANNER_DO') as $action) { //ブン屋・猩々
	foreach ($vote_data[$action] as $id => $target_id) {
	  $user = DB::$USER->ByID($id);
	  if ($user->IsDead(true)) continue; //直前に死んでいたら無効

	  foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
	    if ($filter->TrapKill($user, $target_id)) continue 2;
	  }
	  RoleManager::LoadMain($user)->Report(DB::$USER->ByID($target_id));
	}
      }
    }

    //-- 反魂レイヤー --//
    if (! DB::$ROOM->IsEvent('no_revive')) { //快晴なら無効
      RoleManager::SetActor(RoleManager::GetStack('wolf_target'));
      foreach (RoleManager::Load('resurrect') as $filter) $filter->Resurrect();

      foreach (DB::$USER->rows as $user) { //仙狼の処理
	if ($user->IsRole('revive_wolf')) RoleManager::LoadMain($user)->Resurrect();
      }
    }

    if (DB::$ROOM->date > 1) {
      $role = 'reverse_assassin';  //反魂師の反魂処理
      $name = 'reverse';
      if (RoleManager::ExistStack($name)) RoleManager::GetClass($role)->Resurrect();
      RoleManager::UnsetStack($name);

      //-- 蘇生レイヤー --//
      if (! DB::$ROOM->IsOpenCast()) {
	foreach ($vote_data['POISON_CAT_DO'] as $id => $target_id) { //蘇生能力者の処理
	  $user = DB::$USER->ByID($id);
	  if ($user->IsDead(true)) continue; //直前に死んでいたら無効
	  RoleManager::LoadMain($user)->Revive(DB::$USER->ByID($target_id));
	}
      }

      //-- 憑依レイヤー --//
      //憑依能力者の処理
      $role = 'possessed_mad';
      $name = 'possessed_dead';
      RoleManager::InitStack($name); //有効憑依情報リスト
      foreach ($vote_data['POSSESSED_DO'] as $id => $target_id) {
	$user = DB::$USER->ByID($id);
	if ($user->IsDead(true) || $user->revive_flag) continue; //直前に死亡・蘇生なら無効
	RoleManager::LoadMain($user)->SetPossessed(DB::$USER->ByID($target_id));
      }
      //RoleManager::p($name, "◆Target [{$role}]");
      if (RoleManager::ExistStack($name)) RoleManager::GetClass($role)->Possessed();
      RoleManager::UnsetStack($name);
      //RoleManager::p('possessed', '◆Possessed [mad/fox]');
    }

    //-- 憑依処理 --//
    $role = 'possessed_wolf';
    $name = 'possessed';
    //RoleManager::p($name, "◆Target [{$role}]");
    if (RoleManager::ExistStack($name)) RoleManager::GetClass($role)->Possessed();
    RoleManager::UnsetStack($name);

    if (! DB::$ROOM->IsOption('seal_message')) {  //陰陽師・厄神の成功結果登録
      foreach (array('voodoo_killer', 'anti_voodoo') as $role) {
	$name = $role . '_success';
	//RoleManager::p($name, "◆Success [{$role}]");
	if (RoleManager::ExistStack($name)) RoleManager::GetClass($role)->SaveSuccess();
	RoleManager::UnsetStack($name);
      }
    }

    switch (DB::$ROOM->date) { //変化系能力者の処理
    case 3: //覚醒者・夢語部のコピー処理
      foreach (DB::$USER->rows as $user) {
	if ($user->IsDummyBoy() || ! $user->IsRole('soul_mania', 'dummy_mania')) continue;
	if (is_null($id = $user->GetMainRoleTarget())) continue;
	RoleManager::LoadMain($user)->DelayCopy(DB::$USER->ById($id));
      }
      break;

    case 4: //昼狐の変化処理
      foreach (DB::$USER->rows as $user) {
	if ($user->IsRole('vindictive_fox')) RoleManager::LoadMain($user)->Change();
      }
    }

    RoleManager::GetClass('lovers')->Followed(); //恋人後追い処理
    RoleManager::GetClass('medium')->InsertResult(); //巫女のシステムメッセージ

    //-- 司祭レイヤー --//
    $role_flag = new StdClass(); //役職出現判定フラグを初期化
    foreach (DB::$USER->rows as $user) { //生存者 + 能力発動前の天人を検出
      if ($user->IsDummyBoy()) continue;
      if (($user->IsLive(true) && ! $user->IsRole('revive_priest')) ||
	  (! DB::$ROOM->IsOpenCast() && $user->IsActive('revive_priest'))) {
	$role_flag->{$user->main_role}[] = $user->id;
      }
    }
    //Text::p($role_flag);

    $role = 'attempt_necromancer'; //蟲姫の処理
    if (DB::$ROOM->date > 1 && isset($role_flag->$role) && count($role_flag->$role) > 0) {
      RoleManager::GetClass($role)->Necromancer($wolf_target, $vote_data);
    }

    $role = 'priest';
    RoleManager::GetClass($role)->AggregatePriest($role_flag);
    //RoleManager::p($role, '◆List [priest]');
    //Text::p(RoleManager::GetStack($role)->list,   '◆List [priest]');
    //Text::p(RoleManager::GetStack($role)->count,  '◆List [live]');
    //Text::p(RoleManager::GetStack($role)->crisis, '◆List [crisis]');
    foreach (RoleManager::GetStack($role)->list as $role) {
      RoleManager::GetClass($role)->Priest($role_flag);
    }

    $status = DB::$ROOM->ChangeDate();
    if (DB::$ROOM->test_mode || ! $status) DB::$USER->ResetJoker(true); //ジョーカー再配布
    if (DB::$ROOM->IsOption('death_note')) DB::$USER->ResetDeathNote(); //デスノート再配布

    $stack = RoleManager::GetStack('event');
    if (isset($stack)) { //イベント登録
      //Text::p($stack, '◆Event');
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
    return $status;
  }

  //投票コマンドチェック
  private static function CheckSituation($applay_situation) {
    if (is_array($applay_situation)) {
      if (in_array(RQ::Get()->situation, $applay_situation)) return true;
    }
    elseif (RQ::Get()->situation == $applay_situation) {
      return true;
    }
    VoteHTML::OutputResult('無効な投票です');
  }

  //夜の自分の投票済みチェック
  private static function CheckSelfVoteNight($situation, $not_situation = '') {
    return count(DB::$SELF->LoadVote($situation, $not_situation)) > 0;
  }

  //Kick 投票の集計処理 ($target : 対象 HN, 返り値 : 対象 HN の投票合計数)
  private static function AggregateKick(User $target) {
    self::CheckSituation('KICK_DO'); //コマンドチェック

    //今回投票した相手にすでに投票している人数を取得
    $vote_count = 1;
    foreach (DB::$ROOM->vote as $stack) {
      if (in_array($target->id, $stack)) $vote_count++;
    }

    //規定数以上の投票があった / キッカーが身代わり君 / 自己 KICK が有効の場合に処理
    if ($vote_count >= GameConfig::KICK || DB::$SELF->IsDummyBoy() ||
	(GameConfig::SELF_KICK && $target->IsSelf())) {
      UserDB::Kick($target->id);

      //通知処理
      DB::$ROOM->Talk($target->handle_name . Message::$kick_out);
      DB::$ROOM->Talk(Message::$vote_reset);

      RoomDB::UpdateVoteCount(); //投票リセット処理
    }
    return $vote_count;
  }

  //ランダムメッセージを挿入する
  private static function InsertRandomMessage() {
    if (GameConfig::RANDOM_MESSAGE) DB::$ROOM->Talk(Lottery::Get(Message::$random_message_list));
  }
}

//-- HTML 生成クラス (投票拡張) --//
class VoteHTML {
  const ERROR  = '投票エラー [%s]';
  const RESULT = "<div id=\"game_top\" align=\"center\">%s<br>\n%s</div>";

  //結果出力
  static function OutputResult($str, $reset = false) {
    if ($reset) RoomDB::DeleteVote(); //今までの投票を全部削除
    HTML::OutputResult(ServerConfig::TITLE . ' [投票結果]', self::GenerateResult($str));
  }

  //エラーページ出力
  static function OutputError($title, $str = null) {
    if (is_null($str)) $str = 'プログラムエラーです。管理者に問い合わせてください。';
    HTML::OutputResult(sprintf(self::ERROR, $title), self::GenerateResult($str));
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

      $checkbox = ! $user->IsDummyBoy() && (GameConfig::SELF_KICK || ! $user->IsSelf()) ?
	$header . $id . '" value="' . $id . '">' . Text::LF : '';
      echo $user->GenerateVoteTag($path . $user->icon_filename, $checkbox);
    }

    $str = <<<EOF
</tr></table>
<span class="vote-message">* Kick するには %d 人の投票が必要です</span>
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
    printf($str, GameConfig::KICK, RQ::Get()->back_url, VoteMessage::$KICK_DO, RQ::Get()->post_url,
	   VoteMessage::$GAME_START);
    if (! DB::$ROOM->test_mode) HTML::OutputFooter(true);
  }

  //昼の投票ページを出力する
  static function OutputDay() {
    self::CheckScene(); //投票シーンチェック
    if (DB::$ROOM->IsDate(1)) self::OutputResult('処刑：初日は投票不要です');

    //投票済みチェック
    if (! DB::$ROOM->test_mode && UserDB::IsVoteKill()) self::OutputResult('処刑：投票済み');

    //特殊イベントを参照して投票対象をセット
    if (isset(DB::$ROOM->event->vote_duel) && is_array(DB::$ROOM->event->vote_duel)) {
      $user_stack = array();
      foreach (DB::$ROOM->event->vote_duel as $id) {
	$user_stack[$id] = DB::$USER->rows[$id];
      }
    }
    else {
      $user_stack = DB::$USER->rows;
    }
    $virtual_self = DB::$SELF->GetVirtual(); //仮想投票者を取得

    self::OutputHeader();
    $str = <<<EOF
<input type="hidden" name="situation" value="VOTE_KILL">
<input type="hidden" name="revote_count" value="%d">
<table class="vote-page"><tr>

EOF;
    printf($str, DB::$ROOM->revote_count);

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
      $checkbox = ($is_live && ! $user->IsSame($virtual_self)) ?
	$checkbox_header . $id . '" value="' . $id . '">' : '';
      echo $user->GenerateVoteTag($path, $checkbox);
    }

    $str = <<<EOF
</tr></table>
<span class="vote-message">* 投票先の変更はできません。慎重に！</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td><input type="submit" value="%s"></td>
</tr></table></div>
</form>

EOF;
    printf($str, RQ::Get()->back_url, VoteMessage::$VOTE_DO);
    if (! DB::$ROOM->test_mode) HTML::OutputFooter(true);
  }

  //夜の投票ページを出力する
  static function OutputNight() {
    self::CheckScene(); //投票シーンチェック
    //-- 投票済みチェック --//
    $filter = Vote::GetNightFilter();
    if (! DB::$ROOM->test_mode) {
      Vote::CheckVoteNight(RoleManager::GetStack('action'), RoleManager::GetStack('not_action'));
    }

    self::OutputHeader();
    //Text::p($filter);
    //RoleManager::p();
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
    if (is_null(RoleManager::GetStack('submit'))) {
      RoleManager::SetStack('submit', RoleManager::GetStack('action'));
    }
    $submit = strtoupper(RoleManager::GetStack('submit'));
    printf($format, VoteMessage::$CAUTION, RQ::Get()->back_url, RoleManager::GetStack('action'),
	   VoteMessage::$$submit);

    $add_action = RoleManager::GetStack('add_action');
    if (isset($add_action)) {
      $format = <<<EOF
<td class="add-action">
<input type="checkbox" name="add_action" id="add_action" value="on">
<label for="add_action">%s</label>
</td>
</form>
EOF;
      if (is_null(RoleManager::GetStack('add_submit'))) {
	RoleManager::SetStack('add_submit', RoleManager::GetStack('add_action'));
      }
      $add_submit = strtoupper(RoleManager::GetStack('add_submit'));
      printf($format, VoteMessage::$$add_submit);
    } else {
      Text::Output('</form>');
    }

    $not_action = RoleManager::GetStack('not_action');
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
      if (is_null(RoleManager::GetStack('not_submit'))) {
	RoleManager::SetStack('not_submit', RoleManager::GetStack('not_action'));
      }
      $not_submit = strtoupper(RoleManager::GetStack('not_submit'));
      printf($format, RQ::Get()->post_url, RoleManager::GetStack('not_action'), DB::$SELF->id,
	     VoteMessage::$$not_submit);
    }

    Text::Output('</tr></table></div>');
    if (! DB::$ROOM->test_mode) HTML::OutputFooter(true);
  }

  //死者の投票ページ出力
  static function OutputHeaven() {
    //投票済みチェック
    if (DB::$SELF->IsDrop())     self::OutputResult('蘇生辞退：投票済み');
    if (DB::$ROOM->IsOpenCast()) self::OutputResult('蘇生辞退：投票不要です');

    self::OutputHeader();
    $str = <<<EOF
<input type="hidden" name="situation" value="REVIVE_REFUSE">
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td><input type="submit" value="%s"></form></td>
</tr></table></div>

EOF;
    printf($str, VoteMessage::$CAUTION, RQ::Get()->back_url, VoteMessage::$REVIVE_REFUSE);
    HTML::OutputFooter(true);
  }

  //身代わり君 (霊界) の投票ページ出力
  static function OutputDummyBoy() {
    self::OutputHeader();
    $str = <<<EOF
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td>
<input type="hidden" name="situation" value="RESET_TIME">
<input type="submit" value="%s"></form>
</td>

EOF;
    printf($str, VoteMessage::$CAUTION, RQ::Get()->back_url, VoteMessage::$RESET_TIME);

    //蘇生辞退ボタン表示判定
    if (! DB::$SELF->IsDrop() && DB::$ROOM->IsOption('not_open_cast') &&
	! DB::$ROOM->IsOpenCast()) {
      $str = <<<EOF
<td>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="situation" value="REVIVE_REFUSE">
<input type="submit" value="%s">
</form>
</td>

EOF;
      printf($str, RQ::Get()->post_url, VoteMessage::$REVIVE_REFUSE);
    }
    Text::Output('</tr></table></div>');
    if (! DB::$ROOM->test_mode) HTML::OutputFooter(true);
  }

  //シーンの一致チェック
  private static function CheckScene() {
    if (! DB::$SELF->CheckScene()) self::OutputResult('戻ってリロードしてください');
  }

  //結果生成
  private static function GenerateResult($str) {
    return sprintf(self::RESULT, $str, RQ::Get()->back_url);
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(ServerConfig::TITLE . ' [投票]', 'game');
    HTML::OutputCSS(sprintf('%s/game_vote', JINRO_CSS));
    Text::Output('<link rel="stylesheet" id="scene">');
    $css = empty(DB::$ROOM->scene) ? null : sprintf('%s/game_%s', JINRO_CSS, DB::$ROOM->scene);
    HTML::OutputBodyHeader($css);
    $str = <<<EOF
<a id="game_top"></a>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">

EOF;
    printf($str, RQ::Get()->post_url);
  }
}
