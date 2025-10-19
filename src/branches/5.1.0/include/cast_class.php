<?php
//-- 配役基礎クラス --//
final class Cast extends StackStaticManager {
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
  const DUMMY     = 'dummy';     //身代わり君配役制限候補
  const SUM       = 'sum';       //役職別人数

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
    $role_list = ArrayFilter::Get(CastConfig::$role_list, $user_count);
    if (null === $role_list) { //配役リスト設定存在判定
      self::OutputError(sprintf(VoteMessage::NO_CAST_LIST, $user_count));
    }
    //Text::p($role_list, '◆RoleList [CastConfig]');
    //Text::p(DB::$ROOM->option_list, '◆OptionList');

    //基礎配役オプション取得 (適用されなかった場合は通常村用オプションを適用する)
    $filter = OptionManager::GetCastBase($user_count);
    if (null === $filter) {
      OptionManager::FilterCastAddRole($role_list, $user_count, OptionFilterData::$cast_add_role);
    } else {
      $role_list = $filter->GetCastRole($user_count);
    }
    //Text::p($role_list, '◆RoleList [Option]');

    //村人置換村の処理
    if ((null === $filter) || true === $filter->EnableReplaceRole()) {
      self::ReplaceRole($role_list);
    }
    //Text::p($role_list, '◆RoleList [ReplaceRole]');
    //Text::p(Cast::Stack()->Get(self::DUMMY), '◆DummyBoyCastLimit');

    if (false === is_array($role_list)) { //配役リスト存在判定
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

  //身代わり君の基礎配役対象外役職リスト取得
  public static function GetDisableCastDummyBoyRoleBaseList() {
    $stack = CastConfig::$disable_cast_dummy_boy_role_list; //サーバ個別設定を取得
    array_push($stack, 'wolf', 'fox'); //常時対象外の役職を追加
    return $stack;
  }

  //身代わり君の配役対象外役職リスト取得
  public static function GetDisableCastDummyBoyRoleList() {
    $stack = self::GetDisableCastDummyBoyRoleBaseList();

    //身代わり君配役対象外役職オプション判定
    foreach (OptionFilterData::$disable_cast_dummy_boy_role as $option) {
      if (DB::$ROOM->IsOption($option)) {
	$role = OptionLoader::Load($option)->GetDisableCastDummyBoyRole();
	ArrayFilter::Register($stack, $role);
      }
    }
    //Text::p($stack, '◆DisableCastDummyBoyRole');

    return $stack;
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
    $filter = OptionManager::GetCastMessageFilter();

    //通知なし判定
    if ($filter->IgnoreCastMessage()) {
      return $filter->GetCastMessage();
    }

    //-- メイン役職 --//
    $main_header    = $filter->GetCastMessageMainHeader();
    $main_footer    = $filter->GetCastMessageMainFooter();
    $main_role_list = $filter->GetCastMessageMainRoleList($role_count_list);

    //-- サブ役職 --//
    $sub_footer     = $filter->GetCastMessageSubFooter();
    $sub_role_list  = $filter->GetCastMessageSubRoleList($role_count_list);

    //-- 出力メッセージ生成 --//
    $stack = [];
    foreach (RoleDataManager::GetDiff($main_role_list) as $role => $name) {
      if (true === $css) {
	$name = RoleDataHTML::GenerateMain($role);
      }
      $stack[] = $name . $main_footer . $main_role_list[$role];
    }

    foreach (RoleDataManager::GetDiff($sub_role_list, true) as $role => $name) {
      $stack[] = Text::Quote($name . $sub_footer . $sub_role_list[$role]);
    }
    return $main_header . ArrayFilter::Concat($stack, Message::SPACER);
  }

  //変数初期化
  private static function InitStack() {
    $stack = self::Stack();
    $stack->Init(self::UNAME);
    $stack->Init(self::CAST);
    $stack->Init(self::REMAIN);
    $stack->Init(self::DUMMY);
    $stack->Set(self::USER, DB::$USER->SearchLive());
    $stack->Set(self::ROLE, self::Get($stack->Get(self::COUNT)));
    //$stack->p(self::USER, '◆Uname');
    //$stack->p(self::ROLE, '◆Role');
  }

  //身代わり君配役
  private static function CastDummyBoy() {
    if (false === DB::$ROOM->IsDummyBoy()) {
      return;
    }

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
    $filter = OptionManager::GetFilter('cast_dummy_boy_fix_role');
    if (null !== $filter) {
      $fix_role = $filter->GetCastDummyBoyFixRole($role_list);
      //Text::p($fix_role, '◆DummyBoy: [fix_role]');
      if (true === isset($fix_role)) {
	$key = array_search($fix_role, $role_list);
	if (false !== $key) {
	  self::Stack()->Add(self::CAST, $fix_role);
	  self::Stack()->DeleteKey(self::ROLE, $key);
	}
	return;
      }
    }

    //対象外役職セット
    $disable_role_list = self::GetDisableCastDummyBoyRoleList(); //身代わり君の対象外役職リスト

    //身代わり君配役制限の調整
    $filter = OptionManager::GetFilter('dummy_boy_cast_limit');
    if (null !== $filter) {
      $filter->UpdateDummyBoyCastLimit($disable_role_list);
    }

    shuffle($role_list); //配列をシャッフル
    if (false === self::FixDummyBoyRole($role_list, $disable_role_list)) {
      //配役に失敗した場合は配役制限をリセットして再実施
      self::Stack()->Init(self::DUMMY);
      self::FixDummyBoyRole($role_list, $disable_role_list);
    }
  }

  //身代わり君の役職抽選処理
  private static function FixDummyBoyRole(array $role_list, array $disable_role_list) {
    $cast_limit_list = self::Stack()->Get(self::DUMMY);
    for ($i = count($role_list); $i > 0; $i--) {
      $role = array_shift($role_list); //配役リストから先頭を抜き出す
      if (true === in_array($role, $cast_limit_list)) {
	$role_list[] = $role; //配役リストの末尾に戻す
	continue;
      }

      foreach ($disable_role_list as $disable_role) {
	if (Text::Search($role, $disable_role)) {
	  $role_list[] = $role; //配役リストの末尾に戻す
	  continue 2;
	}
      }

      self::Stack()->Add(self::CAST, $role);
      self::Stack()->Set(self::ROLE, $role_list);
      return true;
    }
    return false;
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
    //特殊村用
    $stack->Set(self::WISH, OptionManager::ExistsWishRoleChaos() || DB::$ROOM->IsOption('step'));

    foreach ($stack->Get(self::USER) as $uname) {
      $role = self::GetWishRole($uname);   //希望役職を取得
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
  //希望なし > 希望参照確率判定 > 探偵村 + 探偵希望 > 特殊村 > 個別希望
  private static function GetWishRole($uname) {
    $role = DB::$USER->GetWishRole($uname); //希望役職を取得
    if ($role == '' || false === Lottery::Percent(CastConfig::WISH_ROLE_RATE)) {
      return null;
    } elseif (DB::$ROOM->IsOption('detective') && $role == 'detective_common') {
      return $role;
    } elseif (self::Stack()->Get(self::WISH)) { //特殊村はグループ単位で希望処理を行なう
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
    return (null === $role) ? false : array_search($role, self::Stack()->Get(self::ROLE));
  }

  //未決定者配役
  private static function CastRemain(array $uname_list) {
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

    OptionManager::CastUserSubRole();
    //self::Stack()->p(self::DELETE, '◆Delete');
    //self::Stack()->p(self::RAND,   '◆Rand');

    //闇鍋モード処理
    if (DB::$ROOM->IsOption('no_sub_role') || false === OptionManager::ExistsChaos()) {
      return;
    }

    //ランダムなサブ役職のコードリストを作成
    $filter = OptionManager::GetFilter('cast_user_chaos_sub_role');
    if (null === $filter) {
      $sub_role_list = RoleDataManager::GetList(true);
    } else {
      $sub_role_list = $filter->GetCastUserChaosSubRoleList();
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
      if (count($rand_list) < 1) {
	break;
      }

      $id = array_shift($rand_list);
      if ($fix_uname_list[$id] == GM::DUMMY_BOY) {
	$rand_list[] = $id;
	if (count($rand_list) == 1) {
	  break;
	}
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
	  $role   = Text::CutPop($option);
	} elseif ($order == 0) { //村人置換
	  $target = Text::CutPop($option, '_', 2);
	  $role   = 'human';
	} else { //共有者・狂人・キューピッド置換
	  $target = Text::CutPop($option, '_', 2);
	  $group  = RoleDataManager::GetGroup($target);
	  $role   = ($group == 'angel') ? 'cupid' : Text::CutPop($target);
	}

	$count = ArrayFilter::GetInt($list, $role);
	if (OptionManager::EnableGerd($role)) { //ゲルト君モード補正
	  $count--;
	}

	if ($count > 0) {
	  //Text::p($count, sprintf('◆ReplaceRole [%s -> %s]', $role, $target));
	  ArrayFilter::Replace($list, $role, $target, $count); //置換処理
	}
      }
    }
  }

  //エラーメッセージ出力
  private static function OutputError($str) {
    $reset = (false === DB::$ROOM->IsTest());
    VoteHTML::OutputResult(sprintf(VoteMessage::ERROR_CAST, $str), $reset);
  }
}
