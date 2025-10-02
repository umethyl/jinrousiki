<?php
//-- DB アクセス (JinrouCache 拡張) --//
final class JinrouCacheDB {
  //取得
  public static function Get() {
    self::Prepare(['content', 'expire', 'hash']);
    return DB::FetchAssoc(true);
  }

  //存在判定
  public static function Exists() {
    self::Prepare(['expire']);
    return DB::Exists();
  }

  //排他更新用ロック
  public static function Lock() {
    self::Prepare(['expire', 'hash'], true);
    return DB::FetchAssoc(true);
  }

  //新規作成
  public static function Insert($content) {
    $query = self::GetQueryUpdate()->Insert();
    $query->Into(['room_no', 'name', 'content', 'expire', 'hash'])->Duplicate();

    $filter = JinrouCacheManager::Load();
    $now    = Time::Get();
    $expire = $now + $filter->expire;
    $filter->next = $expire;
    $list = [
      $filter->room_no, $filter->GetName(true), $content, $expire, $filter->hash,
      $content, $expire, $filter->hash
    ];
    if (CacheConfig::DEBUG_MODE) {
      self::OutputTime($now, $expire, 'Insert');
    }

    DB::Prepare($query->Build(), $list);
    return DB::Execute();
  }

  //更新
  public static function Update($content) {
    $query = self::GetQueryUpdate()->Update()->Where(['room_no', 'name']);

    $filter = JinrouCacheManager::Load();
    $now    = Time::Get();
    $expire = $now + $filter->expire;
    $filter->next = $expire;
    $list = [$content, $expire, $filter->hash, $filter->room_no, $filter->GetName(true)];
    if (CacheConfig::DEBUG_MODE) {
      self::OutputTime($now, $expire, 'Updated');
    }

    DB::Prepare($query->Build(), $list);
    return DB::Execute();
  }

  //消去
  public static function Clear() {
    $query = self::GetQuery()->Delete()->WhereLower('exipre');
    DB::Prepare($query->Build(), [Time::Get() - CacheConfig::EXCEED]);
    return DB::Execute() && DB::Optimize('document_cache');
  }

  //共通 Query 取得
  private static function GetQuery() {
    return Query::Init()->Table('document_cache');
  }

  //共通 Query 取得 (UPDATE 用)
  private static function GetQueryUpdate() {
    return self::GetQuery()->Set(['content', 'expire', 'hash']);
  }

  //Prepare 処理
  private static function Prepare(array $column, $lock = false) {
    $query = self::GetQuery()->Select($column);
    $query->Where(['room_no', 'name'])->Lock($lock);
    DB::Prepare($query->Build(), JinrouCacheManager::Load()->GetKey());
  }

  //時刻出力 (デバッグ用)
  private static function OutputTime($now, $expire, $name) {
    JinrouCacheManager::OutputTime($now, $name);
    JinrouCacheManager::OutputTime($expire);
  }
}
