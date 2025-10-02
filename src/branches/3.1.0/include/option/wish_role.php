<?php
/*
  ◆役割希望制 (wish_role)
*/
class Option_wish_role extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '役割希望制';
  }

  public function GetExplain() {
    return '希望の役割を指定できますが、なれるかは運です';
  }

  //希望役職リスト取得
  public function GetWishRole() {
    $stack = array('none');
    if (! DB::$ROOM->IsOption($this->name)) return $stack;

    //固有判定
    if (DB::$ROOM->IsChaosWish()) {
      return array_merge($stack, RoleDataManager::GetGroupList());
    }

    if (DB::$ROOM->IsOption('gray_random')) {
      return array_merge($stack, OptionLoader::Load('gray_random')->GetWishRole());
    }

    //普通村ベース
    array_push($stack, 'human', 'wolf');
    if (DB::$ROOM->IsQuiz()) {
      ArrayFilter::Merge($stack, OptionLoader::Load('quiz')->GetWishRole());
    } else {
      array_push($stack, 'mage', 'necromancer', 'mad', 'guard', 'common');
      if (DB::$ROOM->IsOption('detective')) $stack[] = 'detective_common';
      $stack[] = 'fox';
    }

    //追加役職
    foreach (OptionFilterData::$add_wish_role as $option) {
      if (DB::$ROOM->IsOption($option)) {
	ArrayFilter::Merge($stack, OptionLoader::Load($option)->GetWishRole());
      }
    }

    if (DB::$ROOM->IsOptionGroup('mania') && ! in_array('mania', $stack)) {
      $stack[] = 'mania';
    }
    return $stack;
  }
}
