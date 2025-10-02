<?php
//-- base --//
#RQ::AddTestRoom('game_option', 'gray_random');
#RQ::AddTestRoom('game_option', 'step');
#RQ::AddTestRoom('game_option', 'quiz');
#RQ::AddTestRoom('option_role', 'gerd');
#RQ::AddTestRoom('option_role', 'disable_gerd:7');

//-- add_role --//
#RQ::AddTestRoom('option_role', 'poison');
#RQ::AddTestRoom('option_role', 'cupid');
#RQ::AddTestRoom('option_role', 'medium');
#RQ::AddTestRoom('option_role', 'mania');

//-- add_sub_role --//
#RQ::AddTestRoom('option_role', 'decide');
#RQ::AddTestRoom('option_role', 'gentleman');
RQ::AddTestRoom('option_role', 'sudden_death');

//-- special --//
#RQ::AddTestRoom('game_option', 'blinder');
RQ::AddTestRoom('option_role', 'joker');
#RQ::AddTestRoom('option_role', 'detective');

//-- replace --//
#RQ::AddTestRoom('option_role', 'replace_human');
#RQ::AddTestRoom('option_role', 'full_mania');

//-- duel --//
#RQ::AddTestRoom('game_option', 'duel');

//-- chaos --//
#RQ::AddTestRoom('game_option', 'chaosfull');
RQ::AddTestRoom('game_option', 'chaos_hyper');
#RQ::AddTestRoom('option_role', 'chaos_open_cast');
#RQ::AddTestRoom('option_role', 'chaos_open_cast_role');
RQ::AddTestRoom('option_role', 'chaos_open_cast_camp');
#RQ::AddTestRoom('option_role', 'sub_role_limit_easy');
#RQ::AddTestRoom('option_role', 'sub_role_limit_normal');
#RQ::AddTestRoom('option_role', 'sub_role_limit_hard');
