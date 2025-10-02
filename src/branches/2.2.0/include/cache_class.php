<?php
//-- キャッシュコントロールクラス --//
class DocumentCache {
  private static $enable   = null; //有効設定
  private static $instance = null;

  public $room_no = 0;
  public $name    = null;
  public $expire  = 0;
  public $hash    = null;
  public $updated = false; //更新済み判定
  public $next    = null;

  //クラスの初期化
  private function __construct($name, $expire = 0) {
    $this->room_no = isset(DB::$ROOM) ? DB::$ROOM->id : 0;
    $this->name    = $name;
    $this->expire  = $expire;
    if (isset(DB::$ROOM) && isset(DB::$USER)) {
      $this->hash = md5(DB::$ROOM->scene . DB::$USER->GetUserCount());
    } else {
      $this->hash = null;
    }

    self::$instance = $this;
  }

  //クラスのロード
  static function Load($name, $expire) {
    if (is_null(self::$instance)) new self($name, $expire);
  }

  //有効判定
  static function Enable($type) {
    if (is_null(self::$enable)) { //未設定ならキャッシュする
      switch ($type) {
      case 'talk_view':
	$count  = CacheConfig::TALK_VIEW_COUNT;
	$enable = CacheConfig::ENABLE_TALK_VIEW && $count <= DB::$USER->GetUserCount();
	break;

      case 'talk_play':
	$count  = CacheConfig::TALK_PLAY_COUNT;
	$enable = CacheConfig::ENABLE_TALK_PLAY && $count <= DB::$USER->GetUserCount();
	break;

      case 'talk_heaven':
	$count  = CacheConfig::TALK_HEAVEN_COUNT;
	$enable = CacheConfig::ENABLE_TALK_HEAVEN && $count <= DB::$USER->GetUserCount();
	break;

      case 'old_log':
	$enable = CacheConfig::ENABLE_OLD_LOG;
	break;

      case 'old_log_list':
	$enable = CacheConfig::ENABLE_OLD_LOG_LIST;
	break;

      default:
	$enable = false;
	break;
      }
      self::$enable = CacheConfig::ENABLE && $enable;
    }

    return self::$enable;
  }

  //インスタンス取得
  static function Get() { return self::$instance; }

  //保存名取得
  static function GetName($hash = false) {
    return $hash ? md5(self::Get()->name) : self::Get()->name;
  }

  //汎用検索情報取得
  static function GetKey() { return array(self::Get()->room_no, self::GetName(true)); }

  //保存情報取得
  static function GetData($serialize = false) {
    $data = DocumentCacheDB::Get();
    if (self::IsExpire($data)) return null;

    self::Get()->updated = true;
    self::Get()->next    = $data['expire'];
    if (CacheConfig::DEBUG_MODE) self::OutputTime($data['expire']);
    $content = gzinflate($data['content']);

    return $serialize ? unserialize($content) : $content;
  }

  //会話情報取得
  static function GetTalk($force = false, $heaven = false) {
    if (! $force) {
      $filter = self::GetData(true);
      if (isset($filter) && $filter instanceOf TalkBuilder) return $filter;
    }

    return $heaven ? Talk::GetHeaven() : Talk::Get();
  }

  //保存処理
  static function Save($object, $serialize = false, $force = false) {
    if (! $force && self::Get()->updated) return;

    $content = gzdeflate($serialize ? serialize($object) : $object);
    if (DocumentCacheDB::Exists()) { //存在するならロックする
      DB::Transaction();
      $data = DocumentCacheDB::Lock();
      if (! $force && ! self::IsExpire($data)) {
	self::Get()->next = $data['expire'];
	return DB::Rollback();
      }
      DocumentCacheDB::Update($content);
      return DB::Commit();
    }
    else {
      return DocumentCacheDB::Insert($content);
    }
  }

  //キャッシュ情報出力
  static function Output($type) {
    $format = '<div class="talk-cache">次回キャッシュ更新時刻：%s (%s)</div>';
    switch ($type) {
    case 'talk_view':
      $str = 'シーン変更でリセットされます';
      break;

    case 'talk_play':
    case 'talk_heaven':
      $str = 'シーン変更・発言更新でリセットされます';
      break;

    default:
      $str = '';
      break;
    }
    printf($format, Time::GetDate('Y-m-d H:i:s', self::Get()->next), $str);
  }

  //時刻出力
  static function OutputTime($time, $name = 'Next Update') {
    Text::p($name, Time::GetDate('Y-m-d H:i:s', $time));
  }

  //有効期限切れ判定
  private static function IsExpire($data) {
    if (is_null($data) || Time::Get() > $data['expire']) return true;
    return isset(self::Get()->hash) && isset($data['hash']) && self::Get()->hash != $data['hash'];
  }
}

//-- DB アクセス (DocumentCache 拡張) --//
class DocumentCacheDB {
  //存在チェック
  static function Exists() {
    $query = 'SELECT expire FROM document_cache WHERE room_no = ? AND name = ?';
    DB::Prepare($query, DocumentCache::GetKey());
    return DB::Count() > 0;
  }

  //取得
  static function Get() {
    $query = 'SELECT content, expire, hash FROM document_cache WHERE room_no = ? AND name = ?';
    DB::Prepare($query, DocumentCache::GetKey());
    return DB::FetchAssoc(true);
  }

  //排他更新用ロック
  static function Lock() {
    $query = 'SELECT expire, hash FROM document_cache WHERE room_no = ? AND name = ? FOR UPDATE';
    DB::Prepare($query, DocumentCache::GetKey());
    return DB::FetchAssoc(true);
  }

  //新規作成
  static function Insert($content) {
    $query = <<<EOF
INSERT INTO document_cache (room_no, name, content, expire, hash) VALUES (?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE content = ?, expire = ?, hash = ?
EOF;
    $filter = DocumentCache::Get();
    $now    = Time::Get();
    $expire = $now + $filter->expire;
    $filter->next = $expire;
    if (CacheConfig::DEBUG_MODE) self::OutputTime($now, $expire, 'Insert');
    $list = array($filter->room_no, $filter->GetName(true), $content, $expire, $filter->hash,
		  $content, $expire, $filter->hash);

    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //更新
  static function Update($content) {
    $query = <<<EOF
Update document_cache Set content = ?, expire = ?, hash = ? WHERE room_no = ? AND name = ?
EOF;
    $filter = DocumentCache::Get();
    $now    = Time::Get();
    $expire = $now + $filter->expire;
    $filter->next = $expire;
    if (CacheConfig::DEBUG_MODE) self::OutputTime($now, $expire, 'Updated');
    $list = array($content, $expire, $filter->hash, $filter->room_no, $filter->GetName(true));

    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //消去
  static function Clean() {
    $query = 'DELETE FROM document_cache WHERE expire < ?';
    DB::Prepare($query, array(Time::Get() - CacheConfig::EXCEED));
    return DB::Execute() && DB::Optimize('document_cache');
  }

  //時刻出力 (デバッグ用)
  private static function OutputTime($now, $expire, $name) {
    DocumentCache::OutputTime($now, $name);
    DocumentCache::OutputTime($expire);
  }
}
