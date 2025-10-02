<?php
//-- 配役基礎クラス --//
final class Cast {
  /* フラグ */
  const FORCE = 'force'; //強制開始モード
  const WISH  = 'wish';  //特殊村判定 (希望処理用)

  /* 基礎データ */
  const COUNT = 'count'; //ユーザ人数
  const USER  = 'user';  //ユーザ名一覧
  const ROLE  = 'role';  //配役リスト

  /* 一時データ */
  const UNAME     = 'uname';     //役職決定ユーザ名
  const CAST      = 'cast';      //ユーザ名に対応する役職
  const REMAIN    = 'remain';    //配役未決定ユーザ名
  const RAND      = 'rand';      //サブ役職配役用乱数
  const DELETE    = 'delete';    //ランダム配布除外リスト
  const DETECTIVE = 'detective'; //探偵候補
  const SUM       = 'sum';       //役職別人数

  //スタック取得
  public static function Stack() {
    static $stack;

    if (true === is_null($stack)) {
      $stack = new Stack();
    }
    return $stack;
  }

  //配役処理
  public static function Execute() {
    self::InitStack();
    self::CastDummyBoy();
    self::CastPrimary();
    self::CastSecondary();
    self::CastSubRole();
  }

  //人数とゲームオプションに応じた役職テーブルを返す
  public static function Get($user_count) {
    //人数に応じた配役リストを取得
    if (false === isset(CastConfig::$role_list[$user_count])) { //リストの有無をチェック
      self::OutputError(sprintf(VoteMessage::NO_CAST_LIST, $user_count));
    }
    $role_list = CastConfig::$role_list[$user_count];
    //Text::p(DB::$ROOM->option_list, '◆OptionList');

    //お祭り村
    if (DB::$ROOM->IsOption('festival') && isset(CastConfig::$festival_role_list[$user_count])) {
      $role_list = CastConfig::$festival_role_list[$user_count];
    } else {
      if (DB::$ROOM->IsOptionGroup('chaos')) { //闇鍋モード
	$role_list = self::SetChaos($user_count);
      } elseif (DB::$ROOM->IsOption('duel')) { //決闘村
	$role_list = self::SetDuel($user_count);
      } elseif (DB::$ROOM->IsOption('gray_random')) { //グレラン村
	$role_list = self::SetFilter($user_count, 'gray_random');
      } elseif (DB::$ROOM->IsOption('step')) { //足音村
	$role_list = self::SetFilter($user_count, 'step');
      } elseif (DB::$ROOM->IsQuiz()) { //クイズ村
	$role_list = self::SetFilter($user_count, 'quiz');
      } else { //通常村
	OptionManager::SetRole($role_list, $user_count);
      }
      //Text::p($role_list, '◆RoleList [normal]');
      self::ReplaceRole($role_list); //村人置換村
    }

    if (false === is_array($role_list)) {
      self::OutputError(VoteMessage::INVALID_CAST);
    }

    //役職名を格納した配列を生成
    $role_fill_list = [];
    foreach ($role_list as $role => $count) {
      if ($count < 0) { //人数をチェック
	self::OutputError(sprintf(VoteMessage::INVALID_ROLE_COUNT, $role));
      }
      for (; $count > 0; $count--) {
	$role_fill_list[] = $role;
      }
    }
    $role_count = count($role_fill_list);

    if ($role_count != $user_count) { //配列長をチェック
      $str = sprintf(VoteMessage::CAST_MISMATCH_COUNT, $user_count, $role_count);
      if (DB::$ROOM->IsTest()) {
	Text::p($str);
	return $role_fill_list;
      }
      self::OutputError($str);
    }

    return $role_fill_list;
  }

  //役職を DB に登録
  public static function Store() {
    $uname_list   = self::Stack()->Get(self::UNAME);
    $is_detective = DB::$ROOM->IsOption('detective');
    if (true === $is_detective) {
      $detective_list = [];
    }

    $stack = [];
    foreach (self::Stack()->Get(self::CAST) as $id => $fix_role) {
      $user = DB::$USER->ByUname($uname_list[$id]);
      $user->ChangeRole($fix_role);

      $role_list = Text::Parse($fix_role);
      foreach ($role_list as $role) {
	ArrayFilter::Add($stack, $role);
      }

      if (true === $is_detective && in_array('detective_common', $role_list)) {
	$detective_list[] = $user;
      }
    }

    if (DB::$ROOM->IsOption('joker')) { //joker[2] 対策
      unset($stack['joker[2]']);
      $stack['joker'] = 1;
    }
    self::Stack()->Set(self::SUM, $stack);
    if (true === $is_detective) {
      self::Stack()->Set(self::DETECTIVE, $detective_list);
    }
  }

  //配役人数通知リストを生成する
  public static function GenerateMessage(array $role_count_list, $css = false) {
    $filter = OptionManager::GetFilter('cast_message');

    //-- メイン役職 --//
    if (true === is_null($filter)) {
      $header         = VoteMessage::ROLE_HEADER;
      $main_type      = '';
      $main_role_list = $role_count_list;
    } else {
      $header         = $filter->GetCastMessageMainHeader();
      $main_type      = $filter->GetCastMessageMainType();
      $main_role_list = $filter->GetCastMessageMainRoleList($role_count_list);
    }

    //-- サブ役職 --//
    if (true === is_null($filter)) {
      $sub_type      = '';
      $sub_role_list = $role_count_list;
    } else {
      $sub_type      = $filter->GetCastMessageSubType();
      $sub_role_list = $filter->GetCastMessageSubRoleList($role_count_list);
    }

    //-- 出力メッセージ生成 --//
    $stack = [];
    foreach (RoleDataManager::GetDiff($main_role_list) as $role => $name) {
      if (true === $css) {
	$name = RoleDataHTML::GenerateMain($role);
      }
      $stack[] = $name . $main_type . $main_role_list[$role];
    }

    foreach (RoleDataManager::GetDiff($sub_role_list, true) as $role => $name) {
      $stack[] = Text::Quote($name . $sub_type . $sub_role_list[$role]);
    }
    return $header . ArrayFilter::Concat($stack, Message::SPACER);
  }

  //配役フィルタリング処理
  public static function FilterRole($count, array $filter) {
    $stack = [];
    foreach (CastConfig::$role_list[$count] as $key => $value) {
      $role = 'human';
      foreach ($filter as $set_role => $target_role) {
	if (Text::Search($key, $target_role)) {
	  $role = is_int($set_role) ? $target_role : $set_role;
	  break;
	}
      }
      ArrayFilter::Add($stack, $role, $value);
    }
    return $stack;
  }

  //変数初期化
  private static function InitStack() {
    $stack = self::Stack();
    $stack->Init(self::UNAME);
    $stack->Init(self::CAST);
    $stack->Init(self::REMAIN);
    $stack->Set(self::USER, DB::$USER->SearchLive());
    $stack->Set(self::ROLE, self::Get($stack->Get(self::COUNT)));
    //$stack->p(self::USER, '◆Uname');
    //$stack->p(self::ROLE, '◆Role');
  }

  //身代わり君配役
  private static function CastDummyBoy() {
    if (false === DB::$ROOM->IsDummyBoy()) return;

    self::SetDummyBoy();
    //self::Stack()->p(self::CAST, '◆dummy_boy');
    if (self::Stack()->Count(self::CAST) < 1) {
      self::OutputError(VoteMessage::NO_CAST_DUMMY_BOY);
    }

    self::Stack()->Add(self::UNAME,   GM::DUMMY_BOY); //決定済みリスト登録
    self::Stack()->Delete(self::USER, GM::DUMMY_BOY); //ユーザ名リストから削除
  }

  //身代わり君役職決定
  private static function SetDummyBoy() {
    $role_list = self::Stack()->Get(self::ROLE);

    //役職固定オプション判定
    $fix_role = null;
    if (DB::$ROOM->IsOption('gerd') && in_array('human', $role_list)) {
      $fix_role = 'human';
    } elseif (DB::$ROOM->IsQuiz()) {
      $fix_role = 'quiz';
    }

    if (isset($fix_role)) {
      $key = array_search($fix_role, $role_list);
      if (false !== $key) {
	self::Stack()->Add(self::CAST, $fix_role);
	self::Stack()->DeleteKey(self::ROLE, $key);
      }
      return;
    }

    shuffle($role_list); //配列をシャッフル
    $stack = self::GetDummyBoyRoleList(); //身代わり君の対象外役職リスト
    for ($i = count($role_list); $i > 0; $i--) {
      $role = array_shift($role_list); //配役リストから先頭を抜き出す
      foreach ($stack as $disable_role) {
	if (Text::Search($role, $disable_role)) {
	  $role_list[] = $role; //配役リストの末尾に戻す
	  continue 2;
	}
      }
      self::Stack()->Add(self::CAST, $role);
      self::Stack()->Set(self::ROLE, $role_list);
      break;
    }
  }

  //身代わり君の配役対象外役職リスト取得
  private static function GetDummyBoyRoleList() {
    $stack = CastConfig::$disable_dummy_boy_role_list; //サーバ個別設定を取得
    array_push($stack, 'wolf', 'fox'); //常時対象外の役職を追加

    //探偵村対応
    $role = 'detective_common';
    if (DB::$ROOM->IsOption('detective') && false === in_array($role, $stack)) {
      $stack[] = $role;
    }
    return $stack;
  }

  //一次配役
  private static function CastPrimary() {
    $stack = self::Stack();
    $stack->Shuffle(self::USER); //ユーザリストシャッフル
    //$stack->p(self::USER, '◆User/Shuffle');

    if (DB::$ROOM->IsOption('wish_role')) { //希望判定
      self::CastWishRole();
    } else {
      self::CastRemain($stack->Get(self::USER));
    }
    //$stack->p(self::UNAME, '◆Uname/1st');
    //$stack->p(self::CAST, '◆Role/1st');

    //配役結果チェック
    $remain = $stack->Count(self::REMAIN);
    $role   = $stack->Count(self::ROLE);
    if ($remain != $role) {
      self::OutputError(sprintf(VoteMessage::CAST_MISMATCH_REMAIN, $remain, $role));
    }
  }

  //希望制配役
  private static function CastWishRole() {
    $stack = self::Stack();
    $stack->Set(self::WISH, DB::$ROOM->IsChaosWish() || DB::$ROOM->IsOption('step')); //特殊村用

    foreach ($stack->Get(self::USER) as $uname) {
      $role = self::GetWishRole($uname); //希望役職を取得
      $key  = self::GetWishRoleKey($role); //希望役職存在判定
      if (false !== $key) {
	$stack->Add(self::UNAME, $uname);
	$stack->Add(self::CAST, $role);
	$stack->DeleteKey(self::ROLE, $key);
      } else {
	$stack->Add(self::REMAIN, $uname); //決まらなかった場合は未決定リスト行き
      }
    }

    $stack->Clear(self::WISH);
  }

  //希望役職取得
  private static function GetWishRole($uname) {
    $role = DB::$USER->GetWishRole($uname); //希望役職を取得
    if ($role == '' || false === Lottery::Percent(CastConfig::WISH_ROLE_RATE)) {
      return null;
    }

    if (self::Stack()->Get(self::WISH)) { //特殊村はグループ単位で希望処理を行なう
      $stack = [];
      foreach (self::Stack()->Get(self::ROLE) as $stack_role) {
	if ($role == RoleDataManager::GetGroup($stack_role)) {
	  $stack[] = $stack_role;
	}
      }
      return Lottery::Get($stack);
    } else {
      return $role;
    }
  }

  //希望役職判定
  private static function GetWishRoleKey($role) {
    return is_null($role) ? false : array_search($role, self::Stack()->Get(self::ROLE));
  }

  //未決定者配役
  public static function CastRemain(array $uname_list) {
    $stack = self::Stack();

    //全員配役決定済みに登録
    $stack->Set(self::UNAME, array_merge($stack->Get(self::UNAME), $uname_list));

    //配役はランダム配布
    $list = array_merge($stack->Get(self::CAST), Lottery::GetList($stack->Get(self::ROLE)));
    $stack->Set(self::CAST, $list);

    $stack->Init(self::ROLE); //残り配役リストをリセット
  }

  //二次配役
  private static function CastSecondary() {
    if (self::Stack()->Count(self::REMAIN) > 0) { //未決定者を配役
      self::CastRemain(self::Stack()->Get(self::REMAIN));
    }
    //self::Stack()->p(self::UNAME, '◆Uname/2nd');
    //self::Stack()->p(self::CAST, '◆Role/2nd');

    //-- 配役結果チェック --//
    //配役決定者
    $user_count = self::Stack()->Get(self::COUNT);
    $fix_uname  = self::Stack()->Count(self::UNAME);
    if ($user_count != $fix_uname) {
      self::OutputError(sprintf(VoteMessage::CAST_MISMATCH_USER, $user_count, $fix_uname));
    }

    //配役数
    $fix_role = self::Stack()->Count(self::CAST);
    if ($fix_uname != $fix_role) {
      self::OutputError(sprintf(VoteMessage::CAST_MISMATCH_ROLE, $fix_uname, $fix_role));
    }

    //残り配役数
    $role = self::Stack()->Count(self::ROLE);
    if ($role > 0) {
      self::OutputError(sprintf(VoteMessage::CAST_REMAIN_ROLE, $role));
    }
  }

  //サブ役職配役
  private static function CastSubRole() {
    //個人配布用乱数をセット
    $stack = self::Stack()->Get(self::CAST);
    self::Stack()->Set(self::RAND, Lottery::GetList(array_keys($stack)));
    //self::Stack()->p(self::RAND, '◆Rand/Base');

    //割り振り対象外役職のリスト
    self::Stack()->Set(self::DELETE, RoleFilterData::$disable_cast);
    //self::CastFixSubRole(); //テスト用

    OptionManager::Cast();
    //self::Stack()->p(self::DELETE, '◆Delete');
    //self::Stack()->p(self::RAND,   '◆Rand');

    //闇鍋モード処理
    if (DB::$ROOM->IsOption('no_sub_role') || false === DB::$ROOM->IsOptionGroup('chaos')) {
      return;
    }

    //ランダムなサブ役職のコードリストを作成
    $filter = OptionManager::GetFilter('cast_sub_role');
    if (is_null($filter)) {
      $sub_role_list = RoleDataManager::GetList(true);
    } else {
      $sub_role_list = $filter->GetCastSubRoleList();
    }
    $sub_role_list = array_diff($sub_role_list, self::Stack()->Get(self::DELETE));
    //Text::p($sub_role_list, '◆SubRole');

    shuffle($sub_role_list);
    $stack = self::Stack()->Get(self::CAST);
    foreach (self::Stack()->Get(self::RAND) as $id) {
      $stack[$id] .= ' ' . array_pop($sub_role_list);
    }
    self::Stack()->Set(self::CAST, $stack);
    //self::CastSpecialSubRole(); //管理人カスタム用
  }

  //固定サブ役職配役 (テスト用)
  private static function CastFixSubRole() {
    $stack = ['wisp', 'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp'];
    $rand_list      = self::Stack()->Get(self::RAND);
    $fix_uname_list = self::Stack()->Get(self::UNAME);
    $fix_role_list  = self::Stack()->Get(self::CAST);
    foreach ($stack as $role) {
      if (count($rand_list) < 1) break;

      $id = array_shift($rand_list);
      if ($fix_uname_list[$id] == GM::DUMMY_BOY) {
	$rand_list[] = $id;
	if (count($rand_list) == 1) break;
      } else {
	$fix_role_list[$id] .= ' ' . $role;
	self::Stack()->Add(self::DELETE, $role);
      }
    }
    //self::Stack()->p(self::DELETE, '◆Delete/Fix');
    self::Stack()->Set(self::RAND, $rand_list);
    self::Stack()->Set(self::CAST, $fix_role_list);
  }

  //固定サブ役職配役 (管理人カスタム用)
  private static function CastSpecialSubRole() {
    if (DB::$ROOM->IsOption('festival')) { //お祭り村
      /* 全員に自信家をつける */
      $role  = 'nervy';
      $stack = self::Stack()->Get(self::CAST);
      foreach (array_keys($stack) as $id) {
        $stack[$id] .= ' ' . $role;
      }
      self::Stack()->Set(self::CAST, $stack);
    }
  }

  //闇鍋モード配役処理
  private static function SetChaos($user_count) {
    //-- 種別検出 --//
    foreach (['chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso'] as $option) {
      if (DB::$ROOM->IsOption($option)) {
	$base_name   = $option;
	$chaos_verso = $option == 'chaos_verso';
	break;
      }
    }

    //-- 固定枠設定 --//
    $fix_role_list = ChaosConfig::${$base_name . '_fix_role_list'}; //個別設定
    OptionManager::FilterChaosFixRole($fix_role_list);
    //Text::p($fix_role_list, sprintf('◆Fix(%d)', array_sum($fix_role_list)));

    //-- ランダム枠決定 --//
    $random_role_list = []; //ランダム配役結果
    $boost_list = DB::$ROOM->GetOptionList('boost_rate'); //出現率補正リスト
    //Text::p($boost_list, '◆boost');

    //-- 最小出現補正 --//
    if (false === $chaos_verso) {
      $stack = []; //役職系統別配役数
      foreach ($fix_role_list as $role => $count) { //固定枠を系統別にカウント
	ArrayFilter::Add($stack, RoleDataManager::GetGroup($role), $count);
      }
      //Text::p($stack, '◆Min: Fix: Group');

      foreach (['wolf', 'fox'] as $role) {
	$name  = ChaosConfig::${sprintf('%s_%s_list', $base_name, $role)};
	$min   = ChaosConfig::${sprintf('min_%s_rate', $role)};
	$rate  = Lottery::GetChaos($name, $boost_list);
	$list  = Lottery::Generate($rate);
	$count = round($user_count / $min) - ArrayFilter::GetInt($stack, $role);
	Lottery::Add($random_role_list, $list, $count);
	//Lottery::ToProbability($rate); //テスト用
	//Text::p($list, "◆Min [{$count}]");
	//Text::p($random_role_list, "◆Min [{$role}]");
      }
    }
    //Text::p($random_role_list, sprintf('◆Min: Random(%d)', array_sum($random_role_list)));

    //-- ランダム配役 --//
    $name  = ChaosConfig::${$base_name . '_random_role_list'};
    $rate  = Lottery::GetChaos($name, $boost_list);
    $list  = Lottery::Generate($rate);
    $count = $user_count - (array_sum($random_role_list) + array_sum($fix_role_list));
    Lottery::Add($random_role_list, $list, $count);
    //Lottery::ToProbability($rate); //テスト用
    //Text::p(array_sum($rate), '◆Random: Total');
    //Text::p($list, "◆Random [{$count}]");

    //-- 補正処理 --//
    //固定とランダムを合計
    $role_list = $random_role_list;
    foreach ($fix_role_list as $role => $count) {
      ArrayFilter::Add($role_list, $role, $count);
    }
    //Text::p($role_list, sprintf('◆1st(%d)', array_sum($role_list)));

    //-- 上限補正 --//
    if (false === $chaos_verso) {
      //役職グループ毎に集計
      $total_stack  = []; //グループ別リスト (全配役)
      $random_stack = []; //グループ別リスト (ランダム)
      foreach ($role_list as $role => $count) {
	$total_stack[RoleDataManager::GetGroup($role)][$role] = $count;
      }
      foreach ($random_role_list as $role => $count) {
	$random_stack[RoleDataManager::GetGroup($role)][$role] = $count;
      }

      foreach (ChaosConfig::$role_group_rate_list as $group => $rate) {
	if (false === ArrayFilter::IsAssoc($random_stack, $group)) {
	  continue;
	}

	$target = $random_stack[$group];
	$count  = array_sum($total_stack[$group]) - round($user_count / $rate);
	//if ($count > 0) Text::p($count, "◆Calib [{$group}]"); //テスト用
	for (; $count > 0; $count--) {
	  if (array_sum($target) < 1) break;
	  //Text::p($target, sprintf('◆　　%d: before', $count));
	  arsort($target);
	  //Text::p($target, sprintf('◆　　%d: afetr', $count));
	  $key = key($target);
	  //Text::p($key, '◆　　target');
	  $target[$key]--;
	  ArrayFilter::Replace($role_list, $key, 'human');
	  //Text::p($target, sprintf('◆　　%d: delete', $count));

	  //0 になった役職はリストから除く
	  ArrayFilter::Sweep($role_list, $key);
	  ArrayFilter::Sweep($target, $key);
	}
      }
      //Text::p($role_list, sprintf('◆2nd(%d)', array_sum($role_list)));
    }

    //-- 身代わり君モード補正 --//
    if (DB::$ROOM->IsDummyBoy()) {
      $dummy_count   = $user_count; //身代わり君対象役職数
      $target_stack  = []; //補正対象リスト
      $disable_stack = self::GetDummyBoyRoleList(); //身代わり君の対象外役職リスト
      foreach ($role_list as $role => $count) { //対象役職の情報を収集
	foreach ($disable_stack as $disable_role) {
	  if (Text::Search($role, $disable_role)) {
	    $target_stack[$disable_role][$role] = $count;
	    $dummy_count -= $count;
	    break; //多重カウント防止 (例：poison_wolf)
	  }
	}
      }

      if ($dummy_count < 1) {
	//Text::p($target_stack, '◆Dummy');
	foreach ($target_stack as $role => $stack) { //対象役職からランダムに村人へ置換
	  //Text::p($stack, "◆　　$role");
	  //人狼・探偵村の探偵はゼロにしない
	  if (($role == 'wolf' || (DB::$ROOM->IsOption('detective') && $role == 'detective')) &&
	      array_sum($stack) < 2) {
	    continue;
	  }

	  arsort($stack);
	  //Text::p($stack, "◆　　list");
	  $key = key($stack);
	  //Text::p($key, "◆　　role");
	  ArrayFilter::Replace($role_list, $key, 'human');
	  ArrayFilter::Sweep($role_list, $key); //0 になった役職はリストから除く
	  break;
	}
	//Text::p($role_list, sprintf('◆3rd(%d)', array_sum($role_list)));
      }
    }

    //-- 村人上限補正 --//
    if (false === $chaos_verso && false === DB::$ROOM->IsReplaceHumanGroup() &&
	true === isset($role_list['human'])) {
      $role  = 'human';
      $count = $role_list[$role] - round($user_count / ChaosConfig::$max_human_rate);
      if (DB::$ROOM->IsOption('gerd')) {
	$count--;
      }
      if ($count > 0) {
	$name = ChaosConfig::${$base_name . '_replace_human_role_list'};
	$rate = Lottery::GetChaos($name, $boost_list);
	$list = Lottery::Generate($rate);
	Lottery::Add($role_list, $list, $count);
	//Lottery::ToProbability($rate); //テスト用
	//Text::p($list, "◆Human [{$count}]");
	$role_list[$role] -= $count;
	ArrayFilter::Sweep($role_list, $role); //0 になったらリストから除く
	//Text::p($role_list, sprintf('◆4th(%d)', array_sum($role_list)));
      }
    }

    return $role_list;
  }

  //決闘村の配役処理
  private static function SetDuel($user_count) {
    CastConfig::InitializeDuel($user_count);

    $stack = [];
    if ($user_count >= array_sum(CastConfig::$duel_fix_list)) {
      foreach (CastConfig::$duel_fix_list as $role => $count) {
	$stack[$role] = $count;
      }
    }

    asort(CastConfig::$duel_rate_list);
    $max_role   = ArrayFilter::PickKey(CastConfig::$duel_rate_list, true); //最大確率の役職
    $total_rate = array_sum(CastConfig::$duel_rate_list);
    $rest_count = $user_count - array_sum($stack);
    foreach (CastConfig::$duel_rate_list as $role => $rate) {
      if ($role != $max_role) {
	$stack[$role] = round($rest_count / $total_rate * $rate);
      }
    }
    $stack[$max_role] = $user_count - array_sum($stack); //端数対策

    CastConfig::FinalizeDuel($user_count, $stack);
    return $stack;
  }

  //特殊配役オプション
  private static function SetFilter($count, $option) {
    return OptionLoader::Load($option)->SetFilterRole($count);
  }

  //村人置換村の処理
  private static function ReplaceRole(array &$list) {
    $stack = [];
    foreach (array_keys(DB::$ROOM->option_role->list) as $option) { //処理順にオプションを登録
      if ($option == 'replace_human' || Text::IsPrefix($option, 'full_')) {
	$stack[0][] = $option;
      } elseif (Text::IsPrefix($option, 'change_')) {
	$stack[1][] = $option;
      }
    }

    foreach ($stack as $order => $option_list) {
      foreach ($option_list as $option) {
	if (isset(CastConfig::$replace_role_list[$option])) { //サーバ設定
	  $target = CastConfig::$replace_role_list[$option];
	  $role   = Text::Cut($option);
	} elseif ($order == 0) { //村人置換
	  $target = Text::Cut($option, '_', 2);
	  $role   = 'human';
	} else { //共有者・狂人・キューピッド置換
	  $target = Text::Cut($option, '_', 2);
	  $group  = RoleDataManager::GetGroup($target);
	  $role   = $group == 'angel' ? 'cupid' : Text::Cut($target);
	}

	$count = ArrayFilter::GetInt($list, $role);
	if ($role == 'human' && DB::$ROOM->IsOption('gerd')) { //ゲルト君モード
	  $count--;
	}
	if ($count > 0) {
	  ArrayFilter::Replace($list, $role, $target, $count); //置換処理
	}
      }
    }
  }

  //エラーメッセージ出力
  private static function OutputError($str) {
    VoteHTML::OutputResult(sprintf(VoteMessage::ERROR_CAST, $str), false === DB::$ROOM->IsTest());
  }
}
