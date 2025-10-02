<?php
/*
  ◆男性転換 (male_status)
  ○仕様
  ・役職表示：適合日のみ
  ・性転換性別：男性
*/
class Role_male_status extends Role {
  protected function IgnoreAbility() {
    $target_date = 0;
    $target_sex  = null;
    //関連役職群から現在に一番近い発動日の役職を選出する
    foreach (RoleLoader::LoadType('gender_status') as $filter) {
      if ($filter->IgnoreGenderStatusDate()) {
	continue;
      }

      $date = $filter->GetGenderStatusDate();
      //Text::p($date, "◆Date [{$filter->role}]");
      if ($target_date < $date && $date <= DB::$ROOM->date) {
	$target_date = $date;
	$target_sex  = $filter->GetFilterSex();
      }
    }
    //Text::p($target_sex, "◆Sex [{$this->role}]");
    return $this->GetFilterSex() !== $target_sex;
  }

  //性適合性転換発動日取得スキップ判定 (役職表示用)
  public function IgnoreGenderStatusDate() {
    return false;
  }

  //適合性転換発動日取得
  final protected function GetGenderStatusDate() {
    $target_date = 0;
    foreach ($this->GetActor()->GetPartner($this->role) as $date) {
      if ($date > DB::$ROOM->date) {
	break;
      } else {
	$target_date = $date;
      }
    }
    return $target_date;
  }

  //性転換リスト生成
  public function GetSexList(array $list) {
    if ($this->IgnoreGetSexList()) {
      return $list;
    } else {
      return $this->FilterSexList($list);
    }
  }

  //性転換リスト生成スキップ判定
  protected function IgnoreGetSexList() {
    return false;
  }

  //性転換処理
  protected function FilterSexList(array $list) {
    //転換日リスト
    //Text::p($this->GetActor()->GetPartner($this->role), "◆Target [{$this->role}]");

    //ログ整合を意識して、現在の日数に適合する転換日と性別の配列を作る
    //現行仕様上、同じ日に男性/女性転換同時付与は起こらない
    $date = $this->GetGenderStatusDate();
    if ($date > 0) {
      $list[$date] = $this->GetFilterSex();
    }
    return $list;
  }

  //性転換性別取得
  protected function GetFilterSex() {
    return Sex::MALE;
  }
}
