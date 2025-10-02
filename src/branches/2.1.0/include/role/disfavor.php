<?php
/*
  ◆不人気 (disfavor)
  ○仕様
  ・得票数：+1
*/
class Role_disfavor extends Role {
  function FilterVotePoll(&$number) { $number++; }
}
