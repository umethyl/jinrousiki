<?php
//-- 告知スレッド表示設定 --//
class BBSConfig {
  const DISABLE  = true; //表示無効設定 (true:無効にする / false:しない)
  const TITLE    = '告知スレッド情報'; //表示名
  const RAW_URL  = 'http://jbbs.livedoor.jp/bbs/rawmode.cgi'; //データ取得用 URL
  const VIEW_URL = 'http://jbbs.livedoor.jp/bbs/read.cgi'; //表示用 url
  const THREAD   = '/game/43883/1275564772/'; //スレッドのアドレス
  const ENCODE   = 'EUC-JP'; //スレッドの文字コード
  const SIZE     = 5; //表示するレスの数
}
