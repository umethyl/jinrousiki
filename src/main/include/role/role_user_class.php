<?php
//-- 個別ユーザクラス (Role 拡張) --//
//-- ◆文字化け抑制◆ --//
final class RoleUser {
  //-- 陣営判定 --//
  //所属陣営取得
  public static function GetCamp(User $user, $type, $reparse) {
    if ($type == 'win_camp' && self::IsContainLovers($user)) { //恋人判定 (勝利陣営)
      return Camp::LOVERS;
    }

    $target = $user;
    $stack  = [];
    while ($target->IsMainGroup(CampGroup::UNKNOWN_MANIA)) { //鵺系ならコピー先を辿る
      $id = $target->GetMainRoleTarget();
      if ((null === $id) || in_array($id, $stack)) {
	break;
      }

      $stack[] = $id;
      $target  = $reparse ? DB::$USER->ByID($id)->GetReparse() : DB::$USER->ByID($id);
    }

    if (self::IsDelayCopy($target)) { //時間差コピー能力者ならコピー先を辿る
      $id = $target->GetMainRoleTarget();
      if (null !== $id) {
	$target = $reparse ? DB::$USER->ByID($id)->GetReparse() : DB::$USER->ByID($id);
	if ($target->IsRoleGroup('mania', 'copied')) { //神話マニア陣営なら元に戻す
	  $target = $user;
	}
      }
    }

    return $target->DistinguishCamp();
  }

  //-- 生存カウント判定 --//
  //人外カウント
  public static function IsInhuman(User $user) {
    return $user->IsMainGroup(CampGroup::WOLF) || self::IsFoxCount($user);
  }

  //妖狐カウント
  public static function IsFoxCount(User $user) {
    return $user->IsMainGroup(CampGroup::FOX, CampGroup::CHILD_FOX);
  }

  //-- 性別判定 --//
  public static function GetSex(User $user, $display = false) {
    //表示用は日数単位でキャッシュする
    if (true === $display) {
      $stack = $user->Stack();
      $sex_stack = $stack->Get('gender_status');
      //Text::p($sex_stack, "◆GetSex/Stack [{$user->uname}]");
      if (null === $sex_stack) {
	$stack->Init('gender_status');
      } elseif (ArrayFilter::IsKey($sex_stack, DB::$ROOM->date)) {
	$sex_list = ArrayFilter::GetList($sex_stack, DB::$ROOM->date);
	return count($sex_list) > 0 ? ArrayFilter::GetMaxKey($sex_list) : null;
      }
    }

    $actor = RoleLoader::GetActor(); //現在の $actor を確保して判定後に再設定する
    $list = [];
    foreach (RoleLoader::LoadUser($user, 'gender_status') as $filter) {
      if (true === $display && $filter->IgnoreGenderStatusDate()) {
	continue;
      }
      //Text::p($list, "◆GetSex [{$filter->role}]");
      $list = $filter->GetSexList($list);
    }
    //Text::p($list, "◆GetSex [FilterEnd]");

    if (true === $display) {
      $stack->Set('gender_status', [DB::$ROOM->date => $list]);
    }
    if (isset($actor)) { //$actor が存在していたら再設定する。
      RoleLoader::SetActor($actor);
    }

    if (count($list) > 0) {
      return ArrayFilter::GetMaxKey($list);
    } elseif (true === $display) {
      return null;
    } else {
      return $user->sex;
    }
  }

  //性転換の性別取得
  public static function GetGenderStatus(User $user) {
    $role = 'gender_status';
    return RoleLoader::Load($role)->GetDisplayGenderStatusSex($user);
  }

  //-- 役職判定 --//
  //恋人表記
  public static function IsContainLovers(User $user) {
    return $user->IsRole(RoleFilterData::$lovers);
  }

  //ジョーカー所持
  public static function IsJoker(User $user) {
    $role = 'joker';
    return $user->IsRole($role) && RoleLoader::Load($role)->IsJoker($user);
  }

  //-- 仲間判定 --//
  //共有者系
  public static function IsCommon(User $user) {
    return $user->IsMainGroup(CampGroup::COMMON) && false === $user->IsRole('dummy_common');
  }

  //人狼系
  public static function IsWolf(User $user) {
    return $user->IsMainGroup(CampGroup::WOLF) && false === self::IsLonely($user);
  }

  //妖狐系
  public static function IsFox(User $user) {
    return $user->IsMainGroup(CampGroup::FOX) && false === self::IsLonely($user);
  }

  //孤立系
  public static function IsLonely(User $user) {
    return $user->IsRole('mind_lonely') || $user->IsRoleGroup('silver');
  }

  //-- 独り言変換判定 --//
  //共有者の囁き
  public static function CommonWhisper(User $user) {
    return false === DB::$SELF->IsRole('dummy_common');
  }

  //人狼の遠吠え
  public static function WolfHowl(User $user) {
    return false === DB::$SELF->IsRole('mind_scanner');
  }

  //囁耳鳴
  public static function WhisperRinging(User $user) {
    return $user->IsRole('whisper_ringing');
  }

  //吠耳鳴
  public static function HowlRinging(User $user) {
    return $user->IsRole('howl_ringing');
  }

  //恋耳鳴
  public static function SweetRinging(User $user) {
    //メイン役職判定
    if (DB::$SELF->IsRole(RoleFilterData::$talk_sweet_ringing)) {
      return true;
    }
    return $user->IsRole('sweet_ringing');
  }

  //-- 能力判定 --//
  //時間差コピー能力者
  public static function IsDelayCopy(User $user) {
    return $user->IsRole(RoleFilterData::$delay_copy);
  }

  //蘇生能力者
  public static function IsRevive(User $user) {
    return $user->IsActive() &&
      ($user->IsMainGroup(CampGroup::POISON_CAT) ||
       $user->IsRole('revive_medium', 'revive_doll_master', 'revive_fox'));
  }

  //毒能力者
  public static function IsPoison(User $user) {
    if (DB::$ROOM->IsEvent('no_poison')) { //無効判定
      return false;
    }

    return $user->IsRoleGroup('poison') && RoleLoader::Load($user->main_role)->IsPoison();
  }

  //憑依能力者 (被憑依者とコード上で区別するための関数)
  public static function IsPossessed(User $user) {
    return $user->IsRole(RoleFilterData::$possessed_group);
  }

  //憑依対象者
  public static function IsPossessedTarget(User $user) {
    return array_key_exists($user->id, RoleManager::Stack()->Get(RoleVoteSuccess::POSSESSED));
  }

  //夢能力者
  public static function IsDream(User $user) {
    return $user->IsRoleGroup('dummy') || $user->IsMainGroup(CampGroup::FAIRY);
  }

  //夢能力対象者
  public static function IsDreamTarget(User $user) {
    return $user->IsRole('dream_eater_mad') || $user->IsMainGroup(CampGroup::FAIRY);
  }

  //-- 耐性判定 --//
  //暗殺反射
  public static function IsReflectAssassin(User $user) {
    if (DB::$ROOM->IsEvent('no_reflect_assassin') || $user->IsDead(true)) { //無効判定
      return false;
    }

    //常時反射
    if ($user->IsRole(RoleFilterData::$reflect_assassin) ||
	self::IsSiriusWolf($user, false) || self::AvoidLovers($user)) {
      return true;
    }

    //確率反射 (祟神 > 鬼陣営)
    if ($user->IsRole('cursed_brownie')) {
      $rate = 30;
    } elseif ($user->IsMainCamp(Camp::OGRE)) {
      //天候判定
      if (DB::$ROOM->IsEvent('full_ogre')) {
	return true;
      }
      if (DB::$ROOM->IsEvent('seal_ogre')) {
	return false;
      }

      $rate = RoleLoader::Load($user->main_role)->GetReflectAssassinRate();
    } else {
      $rate = 0;
    }

    if (DB::$ROOM->IsEvent('boost_reflect')) { //天候補正
      $rate += 30;
    }
    //Text::p($rate, sprintf('◆rate / %s [reflect]', $user->uname));
    if ($rate < 1) {
      return false;
    }

    return $rate >= 100 || Lottery::Percent($rate);
  }

  //呪返し
  public static function IsCursed(User $user) {
    if (DB::$ROOM->IsEvent('no_cursed')) { //無効判定
      return false;
    }
    return $user->IsLiveRoleGroup('cursed');
  }

  //覚醒天狼
  public static function IsSiriusWolf(User $user, $full = true) {
    $role = 'sirius_wolf';
    if (false === $user->IsRole($role)) {
      return false;
    }

    if (RoleManager::Stack()->IsEmpty($role)) {
      $stack = RoleLoader::Load($role)->GetAbilitySiriusWolf();
    } else {
      $stack = RoleManager::Stack()->Get($role);
    }
    return $stack[true === $full ? 'full' : Switcher::ON];
  }

  //難題
  public static function IsChallengeLovers(User $user) {
    return Number::InRange(DB::$ROOM->date, 1, 5) && $user->IsRole('challenge_lovers');
  }

  //特殊耐性
  public static function Avoid(User $user, $quiz = false) {
    $stack = ['detective_common'];
    if ($quiz) {
      $stack[] = 'quiz';
    }
    return $user->IsRole($stack) || self::IsSiriusWolf($user) || self::AvoidLovers($user);
  }

  //特殊恋人耐性
  public static function AvoidLovers(User $user, $strict = false) {
    return (false === $strict && self::IsChallengeLovers($user)) || $user->IsRole('vega_lovers');
  }

  //-- 制限判定 --//
  //蘇生制限
  public static function LimitedRevive(User $user) {
    return $user->IsDrop() || $user->IsOn(UserMode::POSSESSED_RESET) ||
      $user->IsMainGroup(CampGroup::POISON_CAT, CampGroup::DEPRAVER) ||
      $user->IsRoleGroup('revive') || $user->IsRole('lovers') ||
      $user->IsRole(RoleFilterData::$limited_revive) || self::IsDelayCopy($user);
  }

  //憑依制限
  public static function LimitedPossessed(User $user) {
    return self::IsPossessed($user) || $user->IsRole(RoleFilterData::$limited_possessed);
  }

  //遺言制限
  public static function LimitedLastWords(User $user) {
    $stack = RoleFilterData::$limited_last_words;
    return $user->IsMainGroup(CampGroup::ESCAPER) || $user->IsRole($stack);
  }

  //遺言登録制限
  public static function LimitedStoreLastWords(User $user) {
    $stack = RoleFilterData::$limited_store_last_words;
    return self::LimitedLastWords($user) || $user->IsRole($stack);
  }

  //-- 行動判定 --//
  //夜投票完了
  public static function CompletedVoteNight(User $user, array $list) {
    if ($user->IsDummyBoy() || $user->IsDead()) {
      return true;
    }

    foreach (RoleLoader::LoadUser($user, 'death_note') as $filter) {
      if (false === $filter->CompletedVoteNight($list)) {
	return false;
      }
    }
    return RoleLoader::LoadMain($user)->CompletedVoteNight($list);
  }

  //夜投票未完了
  public static function ImcompletedVoteNight(User $user, array $list) {
    return false === self::CompletedVoteNight($user, $list);
  }

  //罠発動
  public static function DelayTrap(User $user, $id) {
    //Text::p($user->uname, '◆RoleUser [DelayTrap]');
    foreach (RoleLoader::LoadFilter('trap') as $filter) {
      if ($filter->DelayTrap($user, $id)) {
	return true;
      }
    }
    return false;
  }

  //離脱
  public static function IsExit(User $user) {
    $role = 'spy_mad';
    return $user->IsRole($role) && self::IsExecute($user, RoleLoader::Load($role));
  }

  //逃亡
  public static function IsEscape(User $user) {
    $role = CampGroup::ESCAPER;
    return $user->IsMainGroup($role) && self::IsExecute($user, RoleLoader::Load($role));
  }

  //護衛
  public static function Guard(User $user) {
    //Text::p($user->uname, '◆RoleUser [Guard]');
    return RoleLoader::Load('guard')->Guard($user);
  }

  //護衛成功済み
  public static function GuardSuccess(User $user, $id) {
    $type  = 'guard_success';
    $stack = $user->Stack();
    if ($stack->IsEmpty($type)) {
      $stack->Init($type);
    }

    if ($stack->IsInclude($type, $id)) {
      return false;
    } else {
      $stack->Add($type, $id);
      return true;
    }
  }

  //対暗殺護衛
  public static function GuardAssassin(User $user) {
    $type  = 'guard_assassin';
    $stack = RoleManager::Stack();
    if ($stack->IsEmpty($type)) {
      $stack->Set($type, RoleLoader::LoadFilter($type));
    }

    foreach ($stack->Get($type) as $filter) {
      if ($filter->GuardAssassin($user->id)) {
	return true;
      }
    }
    return false;
  }

  //厄払い
  public static function GuardCurse(User $user, $curse = true) {
    $type  = 'guard_curse';
    $stack = RoleManager::Stack();
    if ($stack->IsEmpty($type)) {
      $stack->Set($type, RoleLoader::LoadFilter($type));
    }

    foreach ($stack->Get($type) as $filter) {
      if ($filter->IsGuard($user->id)) {
	return true;
      }
    }

    if (true === $curse) {
      DB::$USER->Kill($user->id, DeadReason::CURSED);
    }
    return false;
  }

  //行動判定
  private static function IsExecute(User $user, Role $filter) {
    $vote_data = RoleManager::GetVoteData();
    $stack     = $vote_data[$filter->action];
    //Text::p($stack, "◆Vote [{$filter->role}]");
    return isset($stack[$user->id]);
  }

  //-- 統計用 --//
  //変化形判定
  public static function IsChanged(User $user) {
    return $user->IsRoleGroup('copied', 'changed');
  }

  //変化形前役職情報
  public static function GetOrigin(User $user) {
    foreach (RoleFilterData::$origin_role as $change => $origin) {
      if ($user->IsRole($change)) {
	return [$change => $origin];
      }
    }
  }
}
