<?php
//アイコン登録設定
class UserIconConfig {
  const DISABLE  = false; //アイコンのアップロードの停止設定 (true:停止する / false:しない)
  const LENGTH   = 30;    //アイコン名につけられる文字数(半角)
  const FILE     = 15360; //アップロードできるアイコンファイルの最大容量(単位：バイト)
  const WIDTH    = 45;    //アップロードできるアイコンの最大幅
  const HEIGHT   = 45;    //アップロードできるアイコンの最大高さ
  const NUMBER   = 1000;  //登録できるアイコンの最大数
  const COLUMN   = 4;     //一行に表示する個数
  const GERD     = 0;     //ゲルト君モード用のアイコン番号
  const PASSWORD = 'xxxx'; //アイコン編集パスワード
  const CAUTION  = ''; //注意事項 (空なら何も表示しない)
}
