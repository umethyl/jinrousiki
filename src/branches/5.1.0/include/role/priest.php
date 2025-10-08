<?php
/*
  ◆司祭 (priest)
  ○仕様
  ・司祭：村人陣営 (偶数日 / 4日目以降)
*/
class Role_priest extends Role {
  protected function IgnoreResult() {
    return Number::Odd(DB::$ROOM->date, 3);
  }

  protected function OutputAddResult() {
    $this->OutputPriestResult();
  }

  //司祭結果表示 (Mixin あり)
  protected function OutputPriestResult() {
    RoleHTML::OutputResult($this->GetPriestResultType($this->GetPriestResultRole()));
  }

  //司祭結果表示能力取得
  final protected function GetPriestResultType($role = null) {
    if (null === $role) {
      $role = $this->role;
    }
    return strtoupper($role) . '_RESULT';
  }

  //司祭結果表示役職取得
  protected function GetPriestResultRole() {
    return $this->role;
  }

  //司祭情報収集
  final public function AggregatePriest() {
    //-- 初期化 --//
    $data = new stdClass();
    $data->list  = [];
    $data->type  = [];
    $data->count = ['total' => 0, 'human' => 0, 'wolf' => 0, 'fox' => 0, 'lovers' => 0];
    $this->SetStack($data); //オブジェクトなので Get/Set を都度する必要はない
    $flag = false;

    //-- 司祭系出現判定 --//
    foreach (DB::$USER->GetRole() as $role => $stack) {
      $user = new User($role);
      if ($user->IsRoleGroup('priest')) {
	$filter = RoleLoader::LoadMain($user);
	$filter->SetPriest();
	$flag |= $filter->IsAggregatePriestCamp();
      }
    }

    //-- 天候判定 --//
    if (DB::$ROOM->IsOption('full_weather') ||
	(DB::$ROOM->IsOption('weather') && Number::Multiple(DB::$ROOM->date, 3, 1))) {
      $role = 'weather_priest';
      $data->$role = true;
      ArrayFilter::Register($data->list, $role);
      ArrayFilter::Register($data->type, 'human_side');
      $flag = true;
    }

    //-- 司祭陣営情報収集判定 --//
    if (! $flag) {
      return;
    }

    //陣営情報収集リスト初期化
    foreach (['human_side', 'dead', 'sub_role', 'dream', 'tengu'] as $type) {
      if (in_array($type, $data->type)) {
	$data->count[$type] = 0;
      }
    }
    //Text::p($data, '◆Priest[Base]');

    //-- 陣営情報収集 --//
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsDead(true)) {
	if (isset($data->count['dead']) && ! $user->IsWinCamp(Camp::HUMAN)) {
	  $data->count['dead']++;
	}
	continue;
      }
      $data->count['total']++;

      $dummy_user = new User($user->GetRole());
      if ($dummy_user->IsMainGroup(CampGroup::WOLF)) {
	$data->count['wolf']++;
      } elseif (RoleUser::IsFoxCount($dummy_user)) {
	$data->count['fox']++;
      } else {
	$data->count['human']++;
	if (isset($data->count['human_side']) && $dummy_user->IsCamp(Camp::HUMAN)) {
	  $data->count['human_side']++;
	}
      }

      if ($user->IsRole('lovers')) {
	$data->count['lovers']++;
      }

      if (isset($data->count['sub_role'])) {
	$data->count['sub_role'] += $dummy_user->GetRoleCount(true);
      }

      if (isset($data->count['dream']) && RoleUser::IsDream($user)) {
	$data->count['dream']++;
      }

      if (isset($data->count['tengu']) && $user->IsLive(true) &&
	  ($user->IsWinCamp(Camp::HUMAN) || $user->IsWinCamp(Camp::WOLF))) {
	$data->count['tengu']++;
      }
    }
    //Text::p($data, '◆Priest[Count]');

    //-- 人外勝利前日判定 --//
    if (in_array('crisis_priest', $data->list) || in_array('revive_priest', $data->list)) {
      if ($data->count['total'] - $data->count['lovers'] <= 2) {
	$data->crisis = 'lovers';
      } elseif ($data->count['human'] - $data->count['wolf'] <= 2 || $data->count['wolf'] == 1) {
	if ($data->count['lovers'] > 1) {
	  $data->crisis = 'lovers';
	} elseif ($data->count['fox'] > 0) {
	  $data->crisis = 'fox';
	} elseif ($data->count['human'] - $data->count['wolf'] <= 2) {
	  $data->crisis = 'wolf';
	}
      }
    }

    $this->SetStack($data);
  }

  //司祭能力発動情報セット
  final protected function SetPriest() {
    if ($this->CallParent('IgnoreSetPriest') || $this->CallParent('IgnoreSetPriestEvent')) {
      return;
    }

    $stack = $this->GetStack('priest');
    $stack->list[] = $this->role;
    $stack->type[] = $this->CallParent('GetPriestType');
    $this->SetStack($stack, 'priest');
  }

  //司祭能力発動情報スキップ判定
  protected function IgnoreSetPriest() {
    return Number::Even(DB::$ROOM->date, 3);
  }

  //司祭能力発動情報スキップイベント判定
  protected function IgnoreSetPriestEvent() {
    return false;
  }

  //司祭能力対象取得
  protected function GetPriestType() {
    return 'human_side';
  }

  //司祭陣営情報収集実施判定
  protected function IsAggregatePriestCamp() {
    return true;
  }

  //司祭能力
  final public function Priest() {
    if ($this->IgnorePriest()) {
      return;
    }
    $this->PriestAction();
  }

  //司祭能力スキップ判定
  protected function IgnorePriest() {
    return false;
  }

  //司祭能力発動処理
  protected function PriestAction() {
    $data = $this->GetStack('priest');
    $role = $this->GetPriestRole();
    $type = $this->CallParent('GetPriestType');
    DB::$ROOM->StoreAbility($this->GetPriestResultType($role), $data->count[$type]);
  }

  //司祭能力発動対象役職取得
  protected function GetPriestRole() {
    return $this->role;
  }
}
