<?php
//-- Twitter 投稿クラス --//
class JinroTwitter {
  //投稿処理
  static function Send($id, $name, $comment) {
    if (TwitterConfig::DISABLE || ServerConfig::SECRET_ROOM) return;

    $message = TwitterConfig::GenerateMessage($id, $name, $comment);
    if (ServerConfig::ENCODE != 'UTF-8') { //Twitter は UTF-8
      $message = mb_convert_encoding($message, 'UTF-8', ServerConfig::ENCODE);
    }
    if (mb_strlen($message) > 140) $message = mb_substr($message, 0, 139);

    if (TwitterConfig::ADD_URL) {
      $url = ServerConfig::SITE_ROOT;
      if (TwitterConfig::DIRECT_URL) $url .= 'login.php?room_no=' . $id;
      if (TwitterConfig::SHORT_URL) {
	$short_url = @file_get_contents('http://tinyurl.com/api-create.php?url=' . $url);
	if ($short_url != '') $url = $short_url;
      }
      if (mb_strlen($message . $url) + 1 < 140) $message .= ' ' . $url;
    }
    if (0 < strlen(TwitterConfig::HASH) && mb_strlen($message . TwitterConfig::HASH) + 2 < 140) {
      $message .= sprintf(' #%s', TwitterConfig::HASH);
    }

    //投稿
    $to  = new TwitterOAuth(TwitterConfig::KEY_CK, TwitterConfig::KEY_CS,
			    TwitterConfig::KEY_AT, TwitterConfig::KEY_AS);
    $url = 'https://api.twitter.com/1/statuses/update.json';
    $response = $to->OAuthRequest($url, 'POST', array('status' => $message));

    if (! ($response === false || strrpos($response, 'error'))) return true;
    //エラー処理
    $sentence = 'Twitter への投稿に失敗しました。<br>'."\n" . 'メッセージ：' . $message;
    Text::p($sentence);
    return false;
  }
}
