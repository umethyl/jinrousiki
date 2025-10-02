<?php
/*
  ◆権力者 (authority)
  ○仕様
  ・投票数：+1
  ・得票数補正：-2 (権力者と同じ人に投票)
*/
class Role_authority extends Role {
  public $vote_day_type = 'both';

  public function FilterVoteDo(&$count) { $count++; }
}
