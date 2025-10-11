<?php
//-- Twitter 投稿クラス --//
final class JinrouTwitter {
  const LIMIT  = 140; //制限文字数
  const ENCODE = 'UTF-8'; //文字コード
  const API    = 'https://api.twitter.com/1.1/statuses/update.json'; //Twitter API
  const SHORT  = 'http://tinyurl.com/api-create.php?url='; //短縮 URL

  //投稿処理
  public static function Send($id, $name, $comment) {
    if (TwitterConfig::DISABLE || ServerConfig::SECRET_ROOM) {
      return true;
    }

    $str = TwitterConfig::GenerateMessage($id, $name, $comment);
    $str = Text::Encode($str, self::ENCODE, ServerConfig::ENCODE);
    if (self::Over($str)) {
      $str = Text::Shrink($str, self::LIMIT - 1);
    }

    if (TwitterConfig::ADD_URL) {
      $url = ServerConfig::SITE_ROOT;
      if (TwitterConfig::DIRECT_URL) {
	$url .= URL::GetRoom('login', $id);
      }
      if (TwitterConfig::SHORT_URL) {
	$short_url = @file_get_contents(self::SHORT . $url);
	if ($short_url != '') {
	  $url = $short_url;
	}
      }
      if (false === self::Over($str . $url, 1)) {
	$str .= ' ' . $url;
      }
    }

    if (Text::Exists(TwitterConfig::HASH) && false === self::Over($str . TwitterConfig::HASH, 2)) {
      $str .= sprintf(' #%s', TwitterConfig::HASH);
    }

    //投稿
    $filter = new TwitterOAuth(
      TwitterConfig::KEY_CK, TwitterConfig::KEY_CS, TwitterConfig::KEY_AT, TwitterConfig::KEY_AS
    );
    $response = $filter->OAuthRequest(self::API, 'POST', ['status' => $str]);

    if (! (false === $response || strrpos($response, 'error'))) {
      return true;
    }

    //エラー処理
    Text::Output(TwitterMessage::FAILED, true);
    Text::p($str, '◆Tweet');
    return false;
  }

  //文字数制限チェック
  private static function Over($str, $add = 0) {
    return Text::Count($str) + $add > self::LIMIT;
  }
}
