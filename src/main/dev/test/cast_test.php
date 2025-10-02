<?php
require_once('init.php');
Loader::LoadFile('test_class', 'chaos_config', 'cast_class', 'room_option_class');
Loader::LoadRequest('RequestBaseGame', true);

//-- 仮想村データをセット --//
DevRoom::Initialize(array('name' => '配役テスト村'));
#RQ::AddTestRoom('game_option', 'quiz');
#RQ::AddTestRoom('game_option', 'chaosfull');
RQ::AddTestRoom('game_option', 'chaos_hyper');
#RQ::AddTestRoom('game_option', 'blinder');
#RQ::AddTestRoom('option_role', 'gerd');
#RQ::AddTestRoom('option_role', 'poison cupid medium mania');
RQ::AddTestRoom('option_role', 'decide');
#RQ::AddTestRoom('option_role', 'detective');
RQ::AddTestRoom('option_role', 'joker');
#RQ::AddTestRoom('option_role', 'gentleman');
#RQ::AddTestRoom('option_role', 'sudden_death');
#RQ::AddTestRoom('option_role', 'replace_human');
#RQ::AddTestRoom('option_role', 'full_mania');
RQ::AddTestRoom('option_role', 'chaos_open_cast');
#RQ::AddTestRoom('option_role', 'chaos_open_cast_role');
#RQ::AddTestRoom('option_role', 'chaos_open_cast_camp');
#RQ::AddTestRoom('option_role', 'sub_role_limit_easy');
#RQ::AddTestRoom('option_role', 'sub_role_limit_normal');
#RQ::AddTestRoom('option_role', 'sub_role_limit_hard');

DevUser::Initialize(22,
  array( 1 => '',
	 2 => 'human',
	 3 => 'fox',
	 4 => 'mage',
	 5 => 'cupid',
	 6 => 'assassin',
	 7 => 'guard',
	 8 => 'possessed_wolf',
	 9 => 'mad',
	10 => 'duelist',
	11 => 'fox',
	12 => '',
	13 => 'wizard',
	14 => 'mage',
	15 => 'mad',
	16 => 'wolf',
	17 => 'medium',
	18 => 'guard',
	19 => 'poison',
	20 => 'vampire',
	21 => 'ogre',
	22 => '',));
DevUser::Complement();

//Text::p(RQ::GetTest()->test_users[22]);

//-- 設定調整 --//
#CastConfig::$decide = 11;
#RQ::GetTest()->test_users[3]->live = 'kick';

//-- データロード --//
//DB::Connect(); //DB接続 (必要なときだけ設定する)
DevRoom::Load();
DevUser::Load();

//-- データ出力 --//
CastTest::Output();
