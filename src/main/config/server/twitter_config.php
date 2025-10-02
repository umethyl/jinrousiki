<?php
//-- Twitter 投稿設定 --//
class TwitterConfig {
  const DISABLE = true; //Twitter 投稿停止設定 (true:停止する / false:しない)
  const SERVER = 'localhost'; //サーバ名
  const HASH   = ''; //ハッシュタグ (任意、「#」は不要)
  const ADD_URL    = false; //サーバの URL 追加設定 (true:追加する/false:しない)
  const DIRECT_URL = false; //村への直リンク追加設定 (要：$add_url:true / true: 追加する/false しない)
  const SHORT_URL  = false; //TinyURL を用いた URL 短縮処理設定 (true:行う / false:行わない)
  const KEY_CK = 'xxxx'; //Consumer key
  const KEY_CS = 'xxxx'; //Consumer secret
  const KEY_AT = 'xxxx'; //Access Token
  const KEY_AS = 'xxxx'; //Access Token Secret

  //-- 関数 --//
  //メッセージのセット
  static function GenerateMessage($id, $name, $comment) {
    $format = "【%s】%d番地に%s村\n～%s～ が建ちました";
    return sprintf($format, self::SERVER, $id, $name, $comment);
  }
}
