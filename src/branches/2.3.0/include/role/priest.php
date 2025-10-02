<?php
/*
  ◆司祭 (priest)
  ○仕様
  ・司祭：村人陣営 (偶数日 / 4日目以降)
*/
class Role_priest extends Role {
  public $priest_type = 'human_side';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || DB::$ROOM->date % 2 == 1;
  }

  protected function OutputAddResult() {
    $this->OutputPriestResult();
  }

  //司祭結果表示 (Mixin あり)
  public function OutputPriestResult() {
    $role = $this->GetOutputPriestRole();
    $this->OutputAbilityResult($this->GetResult($role));
  }

  //司祭結果表示役職取得
  protected function GetOutputPriestRole() {
    return $this->role;
  }

  //イベント名取得
  final protected function GetResult($role = null) {
    if (is_null($role)) $role = $this->role;
    return strtoupper($role) . '_RESULT';
  }

  //司祭情報収集
  final public function AggregatePriest() {
    //-- 初期化 --//
    $flag = false;
    $data = new StdClass();
    $data->list  = array();
    $data->count = array('total' => 0, 'human' => 0, 'wolf' => 0, 'fox' => 0, 'lovers' => 0,
			 'human_side' => 0, 'dead' => 0, 'dream' => 0, 'sub_role' => 0);
    $this->SetStack($data);

    //-- 司祭系の出現判定 --//
    foreach (DB::$USER->role as $role => $stack) {
      $user = new User($role);
      if ($user->IsRoleGroup('priest')) {
	$filter = RoleManager::LoadMain($user);
	$filter->SetPriest();
	$flag |= $filter->IsAggregatePriest();
      }
    }
    $data = $this->GetStack();

    //-- 天候判定 --//
    if (DB::$ROOM->IsOption('weather') && DB::$ROOM->date % 3 == 1) {
      $role = 'weather_priest';
      $flag = true;
      $data->$role = true;
      if (! in_array($role, $data->list)) $data->list[] = $role;
    }

    //-- 司祭情報収集判定 --//
    if (! $flag) {
      $this->SetStack($data);
      return;
    }

    //-- 陣営情報収集 --//
    foreach (DB::$USER->rows as $user) {
      if ($user->IsDead(true)) {
	if (! $user->IsCamp('human', true)) $data->count['dead']++;
	continue;
      }
      $data->count['total']++;

      $dummy_user = new User($user->GetRole());
      if ($dummy_user->IsWolf()) {
	$data->count['wolf']++;
      }
      elseif ($dummy_user->IsFox()) {
	$data->count['fox']++;
      }
      else {
	$data->count['human']++;
	if ($dummy_user->IsCamp('human')) $data->count['human_side']++;
      }

      if ($user->IsLovers()) $data->count['lovers']++;

      if (in_array('dowser_priest', $data->list)) {
	$data->count['sub_role'] += count($dummy_user->role_list) - 1;
      }

      if (in_array('dummy_priest', $data->list) &&
	  ($user->IsRoleGroup('dummy') || $user->IsMainGroup('fairy'))) {
	$data->count['dream']++;
      }
    }

    //-- 人外勝利前日判定 --//
    if (in_array('crisis_priest', $data->list) || in_array('revive_priest', $data->list)) {
      if ($data->count['total'] - $data->count['lovers'] <= 2) {
	$data->crisis = 'lovers';
      }
      elseif ($data->count['human'] - $data->count['wolf'] <= 2 || $data->count['wolf'] == 1) {
	if ($data->count['lovers'] > 1) {
	  $data->crisis = 'lovers';
	}
	elseif ($data->count['fox'] > 0) {
	  $data->crisis = 'fox';
	}
	elseif ($data->count['human'] - $data->count['wolf'] <= 2) {
	  $data->crisis = 'wolf';
	}
      }
    }
    $this->SetStack($data);
  }

  //司祭能力発動情報セット
  public function SetPriest() {
    if ($this->IgnoreSetPriest()) return;
    $stack = $this->GetStack('priest');
    $stack->list[] = $this->role;
    $this->SetStack($stack, 'priest');
  }

  //司祭能力発動情報スキップ判定
  protected function IgnoreSetPriest() {
    return DB::$ROOM->date < 3 || DB::$ROOM->date % 2 == 0;
  }

  //司祭陣営情報セット判定
  public function IsAggregatePriest() {
    return true;
  }

  //司祭能力
  public function Priest() {
    if ($this->IgnorePriest()) return;
    $data  = $this->GetStack('priest');
    $role  = $this->GetPriestRole();
    $class = $this->GetClass($method = 'GetPriestType');
    DB::$ROOM->ResultAbility($this->GetResult($role), $data->count[$class->$method()]);
  }

  //司祭能力スキップ判定
  protected function IgnorePriest() {
    return false;
  }

  //司祭能力発動対象役職取得
  protected function GetPriestRole() {
    return $this->role;
  }

  //司祭能力対象取得
  public function GetPriestType() {
    return $this->priest_type;
  }
}
