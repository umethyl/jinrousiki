<?php
//-- 性別関連クラス --//
final class Sex {
  const MALE   = 'male';
  const FEMALE = 'female';

  //定数・表示変換リスト取得
  public static function GetList() {
    return [self::MALE => Message::MALE, self::FEMALE => Message::FEMALE];
  }

  //性別リスト存在判定
  public static function Exists($sex) {
    return array_key_exists($sex, self::GetList());
  }

  //取得
  public static function Get(User $user) {
    return RoleUser::GetSex($user);
  }

  //反転取得
  public static function GetInversion($sex) {
    return ($sex === self::MALE) ? self::FEMALE : self::MALE;
  }

  //鑑定
  public static function Distinguish(User $user) {
    return 'sex_' . self::Get($user);
  }

  //男性判定
  public static function IsMale(User $user) {
    return self::Get($user) === self::MALE;
  }

  //女性判定
  public static function IsFemale(User $user) {
    return self::Get($user) === self::FEMALE;
  }

  //同姓判定
  public static function IsSame(User $a, User $b) {
    return self::Get($a) === self::Get($b);
  }

  //性転換
  public static function Exchange(User $user) {
    $role = self::GetInversion(self::Get($user)) . '_status';
    $user->AddDoom(1, $role);
  }
}
