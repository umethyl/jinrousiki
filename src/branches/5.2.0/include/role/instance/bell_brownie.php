<?php
/*
  ◆住職 (bell_brownie)
  ○仕様
  ・座敷童(夜発動型)：鐘を鳴らす (死亡欄メッセージ)
*/
class Role_bell_brownie extends Role {
  //座敷童(夜発動型)
  public function BrownieNight() {
    return DB::$ROOM->StoreDead($this->GetBellCount(), DeadReason::BELL);
  }

  //鐘の鳴る回数取得
  private function GetBellCount() {
    switch ($this->GetBellType()) {
    case 1:
      return 1;

    case 2:
      return 108;

    case 3:
      return 65535;

    case 4:
      return DB::$ROOM->id;

    case 5:
      return DB::$ROOM->date + 1;

    case 6:
      return Text::Count(DB::$ROOM->name);

    case 7:
      return DB::$USER->CountLive();

    case 8:
      return DB::$USER->CountLiveWolf();

    case 9:
      return $this->GetLiveCampCount();

    case 10:
      return $this->GetBellID();

    case 11:
      return $this->GetBellSexCount();

    case 12:
      return Time::GetYear();

    case 13:
      return Time::GetMonth();

    case 14:
      return Time::GetDay();

    case 15:
      return Time::GetHour();

    default:
      return 0;
    }
  }

  //鐘の鳴る回数種別取得
  private function GetBellType() {
    $list = [
       1 => 10,
       2 =>  3,
       3 =>  1,
       4 =>  5,
       5 => 20,
       6 =>  3,
       7 => 10,
       8 =>  5,
       9 =>  5,
      10 =>  3,
      11 =>  9,
      12 =>  3,
      13 =>  3,
      14 => 10,
      15 => 10,
    ];
    return Lottery::Draw($list);
  }

  //生存者陣営数取得
  private function GetLiveCampCount() {
    $camp = [];
    foreach (DB::$USER->SearchLive() as $id => $name) {
      $camp[DB::$USER->ByID($id)->GetWinCamp()] = 0;
    }
    return count(array_keys($camp));
  }

  //生存住職のID取得
  private function GetBellID() {
    return $this->GetBellUser()->GetID();
  }

  //生存住職と同一性別人数取得
  private function GetBellSexCount() {
    $user  = $this->GetBellUser();
    $count = 0;
    foreach (DB::$USER->SearchLive() as $id => $name) {
      if (Sex::IsSame($user, DB::$USER->ByID($id))) {
	$count++;
      }
    }
    return $count;
  }

  //生存住職取得
  private function GetBellUser() {
    return Lottery::Get(DB::$USER->GetRoleUser($this->role));
  }
}
