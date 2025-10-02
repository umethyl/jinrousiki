<?php
//-- キャッシュマネージャ --//
class JinrouCacheManager {
  const TALK_VIEW	= 'talk_view';
  const TALK_PLAY	= 'talk_play';
  const TALK_HEAVEN	= 'talk_heaven';
  const LOG		= 'old_log';
  const LOG_LIST	= 'old_log_list';

  //クラスのロード
  static function Load($name = null, $expire = null) {
    static $instance;

    if (is_null($instance)) {
      $instance = new JinrouCache($name, $expire);
    }
    return $instance;
  }

  //有効判定
  static function Enable($type) {
    static $flag;

    if (is_null($flag)) { //未設定ならキャッシュする
      switch ($type) {
      case self::TALK_VIEW:
	$count  = CacheConfig::TALK_VIEW_COUNT;
	$enable = CacheConfig::ENABLE_TALK_VIEW && $count <= DB::$USER->Count();
	break;

      case self::TALK_PLAY:
	$count  = CacheConfig::TALK_PLAY_COUNT;
	$enable = CacheConfig::ENABLE_TALK_PLAY && $count <= DB::$USER->Count();
	break;

      case self::TALK_HEAVEN:
	$count  = CacheConfig::TALK_HEAVEN_COUNT;
	$enable = CacheConfig::ENABLE_TALK_HEAVEN && $count <= DB::$USER->Count();
	break;

      case self::LOG:
	$enable = CacheConfig::ENABLE_OLD_LOG;
	break;

      case self::LOG_LIST:
	$enable = CacheConfig::ENABLE_OLD_LOG_LIST;
	break;

      default:
	$enable = false;
	break;
      }
      $flag = CacheConfig::ENABLE && $enable;
    }
    return $flag;
  }

  //キャッシュ取得
  public static function Get($type) {
    switch ($type) {
    case self::TALK_VIEW:
      self::Load($type, CacheConfig::TALK_VIEW_EXPIRE);
      $filter = self::FetchTalk();
      self::Save($filter, true);
      self::Output($type);
      return $filter;

    case self::TALK_PLAY:
      $cache_name = $type;
      if (RQ::Get()->icon) {
	$cache_name .= '_icon';
      }
      if (RQ::Get()->name) {
	$cache_name .= '_name';
      }
      self::Load($cache_name, CacheConfig::TALK_PLAY_EXPIRE);
      $filter = self::FetchTalk(Talk::Stack()->Get(Talk::UPDATE));
      self::Save($filter, true, Talk::Stack()->Get(Talk::UPDATE));
      self::Output($type);
      return $filter;

    case self::TALK_HEAVEN:
      $cache_name = $type;
      if (RQ::Get()->icon) {
	$cache_name .= '_icon';
      }
      self::Load($cache_name, CacheConfig::TALK_HEAVEN_EXPIRE);
      $filter = self::FetchTalk(Talk::Stack()->Get(Talk::UPDATE), true);
      self::Save($filter, true, Talk::Stack()->Get(Talk::UPDATE));
      self::Output($type);
      return $filter;

    case self::LOG:
      self::Load($type . '/' . print_r(RQ::Get(), true), CacheConfig::OLD_LOG_EXPIRE);
      return self::Fetch();

    case self::LOG_LIST:
      self::Load($type, CacheConfig::OLD_LOG_LIST_EXPIRE);
      return self::Fetch();
    }
  }

  //保存情報取得
  public static function Fetch($serialize = false) {
    $data = JinrouCacheDB::Get();
    if (self::Expire($data)) return null;

    self::Load()->updated = true;
    self::Load()->next    = $data['expire'];
    if (CacheConfig::DEBUG_MODE) self::OutputTime($data['expire']);
    $content = gzinflate($data['content']);

    return $serialize ? unserialize($content) : $content;
  }

  //会話情報取得
  public static function FetchTalk($force = false, $heaven = false) {
    if (! $force) {
      $filter = self::Fetch(true);
      if (isset($filter) && $filter instanceOf TalkBuilder) return $filter;
    }

    return $heaven ? Talk::FetchHeaven() : Talk::Fetch();
  }

  //保存処理
  public static function Save($object, $serialize = false, $force = false) {
    if (! $force && self::Load()->updated) return;

    $content = gzdeflate($serialize ? serialize($object) : $object);
    if (JinrouCacheDB::Exists()) { //存在するならロックする
      DB::Transaction();
      $data = JinrouCacheDB::Lock();
      if (! $force && ! self::Expire($data)) {
	self::Load()->next = $data['expire'];
	return DB::Rollback();
      }
      JinrouCacheDB::Update($content);
      return DB::Commit();
    } else {
      return JinrouCacheDB::Insert($content);
    }
  }

  //キャッシュ情報出力
  public static function Output($type) {
    $str = CacheMessage::RELOAD . Time::GetDateTime(self::Load()->next);
    switch ($type) {
    case self::TALK_VIEW:
      $str .= ' ' . Text::Quote(CacheMessage::RELOAD_TALK_VIEW);
      break;

    case self::TALK_PLAY:
    case self::TALK_HEAVEN:
      $str .= ' ' . Text::Quote(CacheMessage::RELOAD_TALK_PLAY);
      break;
    }
    HTML::OutputDiv($str, 'talk-cache');
  }

  //時刻出力
  public static function OutputTime($time, $name = 'Next Update') {
    Text::p($name, Time::GetDateTime($time));
  }

  //有効期限切れ判定
  private static function Expire($data) {
    if (is_null($data) || Time::Get() > $data['expire']) return true;
    return isset(self::Load()->hash) && isset($data['hash']) && self::Load()->hash != $data['hash'];
  }
}

//-- キャッシュコントロールクラス --//
class JinrouCache {
  public $room_no = 0;
  public $name    = null;
  public $expire  = 0;
  public $hash    = null;
  public $updated = false; //更新済み判定
  public $next    = null;

  //クラスの初期化
  public function __construct($name, $expire = 0) {
    $this->room_no = DB::ExistsRoom() ? DB::$ROOM->id : 0;
    $this->name    = $name;
    $this->expire  = $expire;
    if (DB::ExistsRoom() && DB::ExistsUser()) {
      $this->hash = md5(DB::$ROOM->scene . DB::$USER->Count());
    } else {
      $this->hash = null;
    }
  }

  //保存名取得
  public function GetName($hash = false) {
    return $hash ? md5($this->name) : $this->name;
  }

  //汎用検索情報取得
  public function GetKey() {
    return array($this->room_no, $this->GetName(true));
  }
}
