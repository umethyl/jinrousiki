<?php
require_once('init.php');
Loader::LoadFile('admin_class');

$disable = true; //使用時には false に変更する
if (true === $disable) {
  HTML::OutputUnusableError();
}

RQ::LoadRequest('RequestOldLog'); //引数を取得
RQ::Set('prefix', ''); //各ページの先頭につける文字列 (テスト / 上書き回避用)
RQ::Set('index_no', 8); //インデックスページの開始番号
RQ::Set('min_room_no', 351); //インデックス化する村の開始番号
RQ::Set('max_room_no', 383); //インデックス化する村の終了番号
RQ::Set(RequestDataLogRoom::ROLE,   true);
RQ::Set(RequestDataLogRoom::HEAVEN, true);
RQ::Set('generate_index', true);

//JinrouAdmin::DeleteLog(RQ::Get()->min_room_no, RQ::Get()->max_room_no); //部屋削除

DB::Connect(RQ::Get()->db_no);
//OldLogHTML::GenerateIndex(); //インデックスページ生成
//HTML::OutputFooter(true);

JinrouAdmin::GenerateLog();
