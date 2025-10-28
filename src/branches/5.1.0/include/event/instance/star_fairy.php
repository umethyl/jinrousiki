<?php
/*
  ◆天候：星空 (star_fairy)
  ○仕様
  ・悪戯 (妖精)：星妖精
*/
class Event_star_fairy extends Event {
  public function FairyMage() {
    RoleLoader::Load($this->name)->FairyEvent();
  }
}
