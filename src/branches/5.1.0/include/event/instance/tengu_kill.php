<?php
/*
  ◆天候：白羽の矢 (tengu_kill)
  ○仕様
  ・神隠し：大魔縁効果発動
*/
class Event_tengu_kill extends Event {
  public function TenguKill() {
    RoleLoader::Load('involve_tengu')->InvolveTenguKill();
  }
}
