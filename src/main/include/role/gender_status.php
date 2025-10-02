<?php
/*
  ◆性転換 (gender_status)
  ○仕様
  ・役職表示：発動日のみ
  ・性別判定：反転 (発動日限定)
*/
RoleLoader::LoadFile('male_status');
class Role_gender_status extends Role_male_status {
  protected function IgnoreAbility() {
    return $this->IgnoreGenderStatus($this->GetActor());
  }

  public function IgnoreGenderStatusDate() {
    return true;
  }

  protected function IgnoreGetSexList() {
    return $this->IgnoreAbility();
  }

  protected function FilterSexList(array $list) {
    if (count($list) > 0) {
      $sex = ArrayFilter::GetMaxKey($list); //現在の性別 (仕様上は DB::$ROOM->date と同値)
    } else {
      $sex = $this->GetActor()->sex;
    }
    $list[DB::$ROOM->date] = Sex::GetInversion($sex);
    return $list;
  }

  //適合性別取得 (表示用)
  public function GetDisplayGenderStatusSex(User $user) {
    if ($this->IgnoreGenderStatus($user)) {
      return null;
    } else {
      return RoleUser::GetSex($user);
    }
  }

  //性転換発動日判定
  private function IgnoreGenderStatus(User $user) {
    //死の宣告システムは最大値固定
    $list = $user->GetPartner($this->role);
    return false === ArrayFilter::IsInclude($list, DB::$ROOM->date);
  }
}
