<?php
//-- Twitter 投稿クラス --//
class JinrouTwitter {
  const LIMIT  = 140; //制限文字数
  const ENCODE = 'UTF-8'; //文字コード
  const API    = 'https://api.twitter.com/1.1/statuses/update.json'; //Twitter API
  const SHORT  = 'http://tinyurl.com/api-create.php?url='; //短縮 URL

  //投稿処理
  static function Send($id, $name, $comment) {
    if (TwitterConfig::DISABLE || ServerConfig::SECRET_ROOM) return true;

    $str = TwitterConfig::GenerateMessage($id, $name, $comment);
    if (ServerConfig::ENCODE != self::ENCODE) {
      $str = mb_convert_encoding($str, self::ENCODE, ServerConfig::ENCODE);
    }
    if (self::OverLimit($str)) $str = mb_substr($str, 0, self::LIMIT - 1);

    if (TwitterConfig::ADD_URL) {
      $url = ServerConfig::SITE_ROOT;
      if (TwitterConfig::DIRECT_URL) $url .= 'login.php?room_no=' . $id;
      if (TwitterConfig::SHORT_URL) {
	$short_url = @file_get_contents(self::SHORT . $url);
	if ($short_url != '') $url = $short_url;
      }
      if (! self::OverLimit($str . $url, 1)) $str .= ' ' . $url;
    }

    if (0 < strlen(TwitterConfig::HASH) && ! self::OverLimit($str . TwitterConfig::HASH, 2)) {
      $str .= sprintf(' #%s', TwitterConfig::HASH);
    }

    //投稿
    $to = new TwitterOAuth(TwitterConfig::KEY_CK, TwitterConfig::KEY_CS,
			   TwitterConfig::KEY_AT, TwitterConfig::KEY_AS);
    $response = $to->OAuthRequest(self::API, 'POST', array('status' => $str));

    if (! ($response === false || strrpos($response, 'error'))) return true;

    //エラー処理
    Text::Output('Twitter への投稿に失敗しました。', true);
    Text::p($str, 'メッセージ');
    return false;
  }

  //文字数制限チェック
  private static function OverLimit($str, $add = 0) {
    return mb_strlen($str) + $add > self::LIMIT;
  }
}
