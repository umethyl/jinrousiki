<?php
//JinrouLogger::Get()->Output();

/* ROOM */
//foreach (DB::$ROOM as $name => $class) Text::p($class, "◆Room [{$name}]");
//foreach (DB::$ROOM->Stack() as $key => $data) Text::p($data, "◆Room/Stack [{$key}]");
//Text::p(DB::$ROOM->Flag(), '◆Flag');
//DB::$ROOM->Stack()->p('event', '◆Event');
//Text::p(DB::$ROOM->game_option, '◆GameOption');

/* USER */
//Text::p(DB::$USER->GetRole(), '◆ROOM/Role');
//Text::v(DB::$USER->IsOpenCast(), '◆OpenCast');
//foreach (DB::$USER->Get() as $user) {
//  Text::p($user->Stack(), "◆{$user->uname} [{$user->main_role}]");
//}

/* ROLE */
//foreach (RoleManager::Stack() as $name => $class) Text::p($class, "◆Role [${name}]");

/* ライブラリロード情報 */
//Loader::OutputFile();
//OptionLoader::OutputFile();
//EventLoader::OutputFile();
//RoleLoader::OutputFile();
//Text::p(array_keys(RoleLoader::$class), '◆Role [class]');

/* 役職判定情報 */
//self::OutputDistinguishMage();
//foreach (RoleData::$list as $role => $v) RoleLoader::LoadMain(new User($role))->OutputAbility();

/* 配役情報 */
//Loader::LoadFile('chaos_config');
//Text::p(Lottery::ToProbability(ChaosConfig::$chaos_hyper_random_role_list));	//確率
//Text::p(array_sum(ChaosConfig::$chaos_hyper_random_role_list), '◆Sum');	//確率合計
//self::OutputChaosSumGroup();							//系列合計
//Text::p(ChaosConfig::$role_group_rate_list);					//系列係数

/* 天候情報 */
//Text::p(Lottery::ToProbability(GameConfig::$weather_list));

/* DB テスト */
//Loader::LoadFile('room_manager_db_class');
//DB::Connect(); DB::d();
//$q = Query::Init()->Table('user_entry')->Select(['user_no'])->Where(['room_no']);
//$q->p();
