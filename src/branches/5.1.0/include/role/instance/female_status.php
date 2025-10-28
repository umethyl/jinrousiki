<?php
/*
  ◆女性転換 (female_status)
  ○仕様
  ・性転換性別：女性
*/
RoleLoader::LoadFile('male_status');
class Role_female_status extends Role_male_status {
  protected function GetFilterSex() {
    return Sex::FEMALE;
  }
}
