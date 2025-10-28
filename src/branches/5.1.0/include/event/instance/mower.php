<?php
/*
  ◆天候：酸性雨 (mower)
  ○仕様
  ・イベント仮想役職：草刈 (昼限定)
*/
EventLoader::LoadFile('grassy');
class Event_mower extends Event_grassy {
}
