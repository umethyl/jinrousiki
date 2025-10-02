<?php
//-- DB アクセス (JinrouCache 拡張) --//
class JinrouCacheDB {
  //取得
  public static function Get() {
    self::Prepare(array('content', 'expire', 'hash'));
    return DB::FetchAssoc(true);
  }

  //存在判定
  public static function Exists() {
    self::Prepare('expire');
    return DB::Exists();
  }

  //排他更新用ロック
  public static function Lock() {
    self::Prepare(array('expire', 'hash'), true);
    return DB::FetchAssoc(true);
  }

  //新規作成
  public static function Insert($content) {
    $query = <<<EOF
INSERT INTO document_cache (room_no, name, content, expire, hash) VALUES (?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
EOF;
    $filter = JinrouCacheManager::Load();
    $now    = Time::Get();
    $expire = $now + $filter->expire;
    $filter->next = $expire;
    if (CacheConfig::DEBUG_MODE) self::OutputTime($now, $expire, 'Insert');
    $list = array(
      $filter->room_no, $filter->GetName(true), $content, $expire, $filter->hash,
      $content, $expire, $filter->hash
    );

    DB::Prepare($query . self::SetUpdate(), $list);
    return DB::Execute();
  }

  //更新
  public static function Update($content) {
    $query  = 'UPDATE document_cache SET' . self::SetUpdate() . self::SetWhere();
    $filter = JinrouCacheManager::Load();
    $now    = Time::Get();
    $expire = $now + $filter->expire;
    $filter->next = $expire;
    if (CacheConfig::DEBUG_MODE) self::OutputTime($now, $expire, 'Updated');
    $list = array($content, $expire, $filter->hash, $filter->room_no, $filter->GetName(true));

    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //消去
  public static function Clear() {
    $query = 'DELETE FROM document_cache WHERE expire < ?';
    DB::Prepare($query, array(Time::Get() - CacheConfig::EXCEED));
    return DB::Execute() && DB::Optimize('document_cache');
  }

  //共通 WHERE 句生成
  private static function SetWhere() {
    return DB::SetWhere(array('room_no', 'name'));
  }

  //共通 UPDATE 句生成
  private static function SetUpdate() {
    return ' content = ?, expire = ?, hash = ?';
  }

  //Prepare 処理
  private static function Prepare($column, $lock = false) {
    $query = DB::SetSelect('document_cache', $column) . self::SetWhere() . DB::SetLock($lock);
    DB::Prepare($query, JinrouCacheManager::Load()->GetKey());
  }

  //時刻出力 (デバッグ用)
  private static function OutputTime($now, $expire, $name) {
    JinrouCacheManager::OutputTime($now, $name);
    JinrouCacheManager::OutputTime($expire);
  }
}
