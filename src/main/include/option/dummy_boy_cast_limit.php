<?php
/*
  ◆身代わり君配役制限 (dummy_boy_cast_limit)
*/
class Option_dummy_boy_cast_limit extends OptionCheckbox {
  public $group = OptionGroup::ROLE;

  public function GetCaption() {
    return '身代わり君配役制限';
  }

  public function GetExplain() {
    return '身代わり君が追加役職設定された役職を引きにくくなります';
  }

  //配役リストを参照して配役制限リストの調整を行う
  public function UpdateDummyBoyCastLimit(array $stack) {
    $role_count_list = array_count_values(Cast::Stack()->Get(Cast::ROLE));
    $limit_list = [];
    foreach (Cast::Stack()->Get(Cast::DUMMY) as $role) {
      if (true === in_array($role, $stack)) { //システム設定枠は除外する
	//Text::p($role, '◆Include Config');
      } elseif (ArrayFilter::GetInt($role_count_list, $role) == 1) { //複数配役されているなら除外
	ArrayFilter::Register($limit_list, $role);
      }
    }
    Cast::Stack()->Set(Cast::DUMMY, $limit_list);
    //Cast::Stack()->p(Cast::DUMMY, '◆DummyBoyCastLimit [Update]');
  }
}
