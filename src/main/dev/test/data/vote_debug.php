<?php
//Text::p(Loader::$file, '◆Loader');
//foreach (DB::$ROOM as $name => $class) Text::p($class, "◆Room [{$name}]");
//foreach (DB::$ROOM->Stack() as $key => $data) Text::p($data, "◆Stack [{$key}]");
//Text::p(DB::$ROOM->Flag(), '◆Flag');
//DB::$ROOM->Stack()->p('event', '◆Event');
//Text::p(DB::$ROOM->game_option, '◆GameOption');
//Text::p(DB::$USER->GetRole(), '◆ROOM/Role');
//Text::v(DB::$USER->IsOpenCast(), '◆OpenCast');
//Text::p(RoleLoader::$file, '◆Role [file]');
//Text::p(array_keys(RoleLoader::$class), '◆Role [class]');
//foreach (RoleLoader::$class as $name => $class) Text::p($class, "◆Role [{$name}]");
//JinrouLogger::Get()->Output();

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

/* User::$stack */
//foreach (DB::$USER->Get() as $user) {
//  Text::p($user->Stack(), "◆{$user->uname} [{$user->main_role}]");
//}

/* DB テスト */
//DB::Connect(); DB::d();
//Text::p($stack, '◆Stack');
