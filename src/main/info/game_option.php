<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('cast_config', 'role_data_class', 'room_option_class', 'info_functions');
InfoHTML::OutputHeader('ゲームオプション', 0, 'game_option');
?>
<p>
<a href="#basic_option">基本</a>
<a href="#dummy_boy_option">身代わり君</a>
<a href="#open_cast_option">霊界公開</a>
<a href="#add_role_option">追加役職</a>
<a href="#special_option">特殊村</a>
<a href="#special_role_option">特殊配役</a>
</p>

<h2 id="basic_option">基本設定</h2>
<p>
<?php InfoHTML::OutputCategory(array('wish_role', 'real_time', 'wait_morning', 'open_vote',
'settle')); ?>
</p>
<p>
<?php InfoHTML::OutputCategory(array('seal_message', 'open_day', 'necessary_name',
'necessary_trip')); ?>
</p>

<h3 id="wish_role"><?php OptionManager::OutputCaption('wish_role'); ?></h3>
<ul>
<li><?php OptionManager::OutputExplain('wish_role'); ?></li>
<li>村人登録 (プレイヤー登録) の際になりたい役職を選択することができます</li>
<li>オプションの組み合わせによって希望できる役職の数や種類が違います</li>
</ul>

<h3 id="real_time"><?php OptionManager::OutputCaption('real_time'); ?></h3>
<ul>
<li><?php OptionManager::OutputExplain('real_time'); ?></li>
<li>昼と夜を個別に設定できます → <a href="script_info.php#difference_real_time">初期設定</a></li>
</ul>

<h3 id="wait_morning"><?php OptionManager::OutputCaption('wait_morning'); ?> [Ver. 1.4.0 β17～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('wait_morning'); ?> → <a href="script_info.php#difference_wait_morning">待機時間設定</a></li>
<li>発言が制限されている間は画面の上方に「待機時間中です」という趣旨のメッセージが表示されます</li>
</ul>

<h3 id="open_vote"><?php OptionManager::OutputCaption('open_vote'); ?></h3>
<ul>
<li>昼の処刑投票数が公開されます</li>
<li><?php OptionManager::OutputExplain('open_vote'); ?></li>
</ul>

<h3 id="settle"><?php OptionManager::OutputCaption('settle'); ?> [Ver. 2.1.0 β3～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('settle'); ?></li>
<li><a href="spec.php#vote_day">処刑者決定</a>後でも引き分けだった場合に、最多得票者からランダムで処刑者が決定されます</li>
</ul>

<h3 id="seal_message"><?php OptionManager::OutputCaption('seal_message'); ?> [Ver. 1.5.0 β12～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('seal_message'); ?></li>
<li>対象となるのは以下です
  <ul>
    <li><a href="new_role/human.php#voodoo_killer">陰陽師</a>の解呪成功</li>
    <li><a href="new_role/human.php#guard_hunt">狩り</a>成功</li>
    <li><a href="new_role/human.php#anti_voodoo">厄神</a>の厄払い成功</li>
    <li><a href="new_role/wolf.php#sharp_wolf">鋭狼</a>の襲撃回避</li>
    <li><a href="new_role/fox.php">妖狐</a>への人狼襲撃</li>
    <li><a href="new_role/ability.php#guard">護衛能力者</a>の護衛成功</li>
    <li><a href="new_role/ability.php#revive_other">蘇生能力者</a>の蘇生結果</li>
  </ul>
</li>
</ul>

<h3 id="open_day"><?php OptionManager::OutputCaption('open_day'); ?> [Ver. 1.4.0 β12～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('open_day'); ?></li>
<li>自分の役職は分かりますが1日目昼は投票できません</li>
<li>制限時間を過ぎたら自動で夜に切り替わります (通常のゲーム開始相当)</li>
</ul>

<h3 id="necessary_name"><?php OptionManager::OutputCaption('necessary_name'); ?> [Ver. 2.1.0 α7～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('necessary_name'); ?></li>
<li><a href="script_info.php#difference_trip">トリップ</a>が使用可の時のみ有効です</li>
</ul>

<h3 id="necessary_trip"><?php OptionManager::OutputCaption('necessary_trip'); ?> [Ver. 2.1.0 α7～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('necessary_trip'); ?></li>
<li><a href="script_info.php#difference_trip">トリップ</a>が使用可の時のみ有効です</li>
</ul>


<h2 id="dummy_boy_option">身代わり君設定</h2>
<p>
<?php InfoHTML::OutputCategory(array('dummy_boy', 'gm_login', 'gerd')); ?>
</p>

<h3 id="dummy_boy"><?php OptionManager::OutputCaption('dummy_boy'); ?></h3>
<ul>
<li>初日の夜、身代わり君が狼に食べられます</li>
<li><a href="script_info.php#difference_dummy_boy">身代わり君がなれる役職</a>には制限があります</li>
<li>身代わり君は、基本的には能力は発動しません</li>
</ul>

<h3 id="gm_login"><?php OptionManager::OutputCaption('gm_login'); ?> [Ver. 1.4.0 α18～]</h3>
<ul>
<li>仮想 GM が身代わり君としてログインします → <a href="spec.php#dummy_boy">仕様</a></li>
<li>村を作成する際にログインパスワードの入力が必要です</li>
<li>身代わり君のユーザ名は「dummy_boy」です</li>
</ul>

<h3 id="gerd"><?php OptionManager::OutputCaption('gerd'); ?> [Ver. 1.4.0 β12～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('gerd'); ?></li>
<li><a href="#chaos"><?php OptionManager::OutputCaption('chaos'); ?></a>の固定配役に村人を一人追加します</li>
<li><a href="#replace_human"><?php OptionManager::OutputCaption('replace_human'); ?></a>オプションが付いていても村人を一人確保します</li>
<li><a href="#duel"><?php OptionManager::OutputCaption('duel'); ?></a>・<a href="#festival"><?php OptionManager::OutputCaption('festival'); ?></a>の配役は入れ替えません (最初から存在する場合のみ有効)</li>
</ul>


<h2 id="open_cast_option">霊界公開設定</h2>
<p>
<a href="#open_cast">常時霊界公開</a>
<?php InfoHTML::OutputCategory(array('not_open_cast', 'auto_open_cast')); ?>
</p>

<h3 id="open_cast">常時霊界公開</h3>
<ul>
<li>常に霊界で配役が公開されます</li>
<li>蘇生能力は無効になります</li>
<li>システム的にはこれが初期設定です (アイコン表示はありません)</li>
</ul>

<h3 id="not_open_cast"><?php OptionManager::OutputCaption('not_open_cast'); ?></h3>
<ul>
<li>誰がどの役職なのかゲーム終了まで公開されません</li>
<li>蘇生能力は有効になります</li>
<li><a href="spec.php#dummy_boy">身代わり君</a>が<a href="spec.php#revive_refuse">蘇生辞退</a>すると<a href="#auto_open_cast"><?php OptionManager::OutputCaption('auto_open_cast'); ?></a>相当になります</li>
</ul>
<h4>Ver. 1.5.0 β14～</h4>
<ul>
<li>身代わり君の蘇生辞退で自動公開モード相当に移行</li>
</ul>

<h3 id="auto_open_cast"><?php OptionManager::OutputCaption('auto_open_cast'); ?> [Ver. 1.4.0 β3～]</h3>
<ul>
<li>蘇生能力者などが能力を持っている間だけ霊界が非公開になります</li>
<li>非公開中の霊界モードには「隠蔽中」という趣旨のメッセージが画面に表示されます</li>
</ul>
<h4>Ver. 1.5.0 β6～</h4>
<ul>
<li>非公開中の霊界モードには「隠蔽中」という趣旨のメッセージが画面に表示されます</li>
</ul>


<h2 id="add_role_option">追加役職設定</h2>
<ul>
<li>置換元の役職が足りない場合は出現しないことがあります<br>
(例：村人1の場合、<a href="#poison"><?php OptionManager::OutputCaption('poison'); ?></a>は適用されない)</li>
</ul>
<p>
<?php InfoHTML::OutputCategory(array('poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf',
'tongue_wolf', 'possessed_wolf', 'sirius_wolf')); ?>
</p>
<p>
<?php InfoHTML::OutputCategory(array('fox', 'child_fox', 'cupid', 'medium', 'mania',
'decide', 'authority')); ?>
</p>

<h3 id="poison"><?php OptionManager::OutputCaption('poison'); ?></h3>
<ul>
<li><?php Info::OutputAddRole('poison'); ?></li>
<li><?php OptionManager::OutputExplain('poison'); ?></li>
</ul>

<h3 id="assassin"><?php OptionManager::OutputCaption('assassin'); ?> [Ver. 1.4.0 β4～]</h3>
<ul>
<li><?php Info::OutputAddRole('assassin'); ?></li>
<li><?php OptionManager::OutputExplain('assassin'); ?></li>
</ul>

<h3 id="wolf"><?php OptionManager::OutputCaption('wolf'); ?> [Ver. 1.5.0 β14～]</h3>
<ul>
<li><?php Info::OutputAddRole('wolf', true); ?></li>
<li><?php OptionManager::OutputExplain('wolf'); ?></li>
</ul>

<h3 id="boss_wolf"><?php OptionManager::OutputCaption('boss_wolf'); ?> [Ver. 1.4.0 α3-7～]</h3>
<ul>
<li><?php Info::OutputAddRole('boss_wolf'); ?></li>
<li><?php OptionManager::OutputExplain('boss_wolf'); ?></li>
</ul>

<h3 id="poison_wolf"><?php OptionManager::OutputCaption('poison_wolf'); ?> [Ver. 1.4.0 α14～]</h3>
<ul>
<li><?php Info::OutputAddRole('poison_wolf'); ?></li>
<li><?php OptionManager::OutputExplain('poison_wolf'); ?></li>
</ul>

<h3 id="tongue_wolf"><?php OptionManager::OutputCaption('tongue_wolf'); ?> [Ver. 2.1.0 β3～]</h3>
<ul>
<li><?php Info::OutputAddRole('tongue_wolf'); ?></li>
<li><?php OptionManager::OutputExplain('tongue_wolf'); ?></li>
</ul>

<h3 id="possessed_wolf"><?php OptionManager::OutputCaption('possessed_wolf'); ?> [Ver. 1.4.0 β4～]</h3>
<ul>
<li><?php Info::OutputAddRole('possessed_wolf'); ?></li>
<li><?php OptionManager::OutputExplain('possessed_wolf'); ?></li>
</ul>

<h3 id="sirius_wolf"><?php OptionManager::OutputCaption('sirius_wolf'); ?> [Ver. 1.4.0 β9～]</h3>
<ul>
<li><?php Info::OutputAddRole('sirius_wolf'); ?></li>
<li><?php OptionManager::OutputExplain('sirius_wolf'); ?></li>
</ul>

<h3 id="fox"><?php OptionManager::OutputCaption('fox'); ?> [Ver. 1.5.0 β12～]</h3>
<ul>
<li><?php Info::OutputAddRole('fox', true); ?></li>
<li><?php OptionManager::OutputExplain('fox'); ?></li>
</ul>

<h3 id="child_fox"><?php OptionManager::OutputCaption('child_fox'); ?> [Ver. 1.5.0 β12～]</h3>
<ul>
<li><?php Info::OutputAddRole('child_fox'); ?></li>
<li><?php OptionManager::OutputExplain('child_fox'); ?></li>
</ul>

<h3 id="cupid"><?php OptionManager::OutputCaption('cupid'); ?> [Ver. 1.2.0～]</h3>
<ul>
<li><?php Info::OutputAddRole('cupid'); ?></li>
<li><?php OptionManager::OutputExplain('cupid'); ?></li>
</ul>
<h4>Ver. 1.4.0 β17～</h4>
<ul>
<li>「14人」の固定出現を廃止</li>
</ul>

<h3 id="medium"><?php OptionManager::OutputCaption('medium'); ?> [Ver. 1.4.0 α14～]</h3>
<ul>
<li><?php Info::OutputAddRole('medium'); ?></li>
<li><?php OptionManager::OutputExplain('medium'); ?></li>
</ul>

<h3 id="mania"><?php OptionManager::OutputCaption('mania'); ?> [Ver. 1.4.0 α14～]</h3>
<ul>
<li><?php Info::OutputAddRole('mania') ?></li>
<li><?php OptionManager::OutputExplain('mania'); ?></li>
</ul>

<h3 id="decide"><?php OptionManager::OutputCaption('decide'); ?></h3>
<ul>
<li><?php Info::OutputAddRole('decide') ?></li>
<li><?php OptionManager::OutputExplain('decide'); ?></li>
<li>自分が決定者であることはわかりません</li>
</ul>

<h3 id="authority"><?php OptionManager::OutputCaption('authority'); ?></h3>
<ul>
<li><?php Info::OutputAddRole('authority') ?></li>
<li><?php OptionManager::OutputExplain('authority'); ?></li>
<li>自分が権力者であることはわかります</li>
</ul>


<h2 id="special_option">特殊村設定</h2>
<p>
<?php InfoHTML::OutputCategory(array('detective', 'liar', 'gentleman', 'passion', 'deep_sleep',
'blinder', 'mind_open', 'critical', 'sudden_death', 'perverseness')); ?>
</p>
<p>
<?php InfoHTML::OutputCategory(array('joker', 'death_note', 'weather', 'festival')); ?>
</p>
<p>
<?php InfoHTML::OutputCategory(array('replace_human', 'full_mad', 'full_cupid', 'full_quiz',
'full_vampire', 'full_chiroptera', 'full_mania', 'full_unknown_mania')); ?>
<p>
<?php InfoHTML::OutputCategory(array('change_common', 'change_hermit_common', 'change_mad',
'change_fanatic_mad','change_whisper_mad','change_immolate_mad')); ?>
</p>
<p>
<?php InfoHTML::OutputCategory(array('change_cupid', 'change_mind_cupid','change_triangle_cupid',
'change_angel')); ?>
</p>

<h3 id="detective"><?php OptionManager::OutputCaption('detective'); ?> [Ver. 1.4.0 β10～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('detective'); ?></li>
<li>普通村の場合は、共有者がいれば共有者を、いなければ村人を一人<a href="new_role/human.php#detective_common">探偵</a>に入れ替えます</li>
<li><a href="#chaos"><?php OptionManager::OutputCaption('chaos'); ?></a>の場合は固定枠に<a href="new_role/human.php#detective_common">探偵</a>が追加されます</li>
<li>このオプションを使用した場合は、身代わり君が<a href="new_role/human.php#detective_common">探偵</a>にはなりません</li>
<li>「<a href="#gm_login"><?php OptionManager::OutputCaption('gm_login'); ?></a>」+「<a href="#not_open_cast"><?php OptionManager::OutputCaption('not_open_cast'); ?></a>」オプションと併用すると「霊界探偵モード」になります</li>
<li>「霊界探偵モード」はゲーム開始直後に探偵が死亡して、霊界に移動します。指示は GM 経由で行います</li>
</ul>

<h3 id="liar"><?php OptionManager::OutputCaption('liar'); ?> [Ver. 1.4.0 α14～]</h3>
<ul>
<li>全ユーザに一定の確率 (70% 程度) で<a href="new_role/sub_role.php#liar">狼少年</a>がつきます</li>
</ul>

<h3 id="gentleman"><?php OptionManager::OutputCaption('gentleman'); ?> [Ver. 1.4.0 α14～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('gentleman'); ?></li>
<li><a href="new_role/sub_role.php#gentleman">紳士</a>・<a href="new_role/sub_role.php#lady">淑女</a>の発動率はランダム付加の場合と同じです</li>
<li><a href="#chaos"><?php OptionManager::OutputCaption('chaos'); ?></a>でランダムに付加される時は個々の性別を参照していません</li>
</ul>

<h3 id="passion"><?php OptionManager::OutputCaption('passion'); ?> [Ver. 2.2.0 α4～]</h3>
<ul>
<li>全ユーザに<a href="new_role/sub_role.php#passion">恋色迷彩</a>がつきます</li>
</ul>

<h3 id="deep_sleep"><?php OptionManager::OutputCaption('deep_sleep'); ?> [Ver. 1.4.0 β18～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('deep_sleep'); ?></li>
<li>観戦している人にも<a href="new_role/sub_role.php#deep_sleep">爆睡者</a>がつきます</li>
<li>結果として、<a href="new_role/human.php#common_group">共有者</a>を騙ることが可能になります</li>
</ul>

<h3 id="blinder"><?php OptionManager::OutputCaption('blinder'); ?> [Ver. 1.4.0 β18～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('blinder'); ?></li>
<li>観戦している人にも<a href="new_role/sub_role.php#blinder">目隠し</a>がつきます</li>
</ul>

<h3 id="mind_open"><?php OptionManager::OutputCaption('mind_open'); ?> [Ver. 1.4.0 β18～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('mind_open'); ?></li>
<li><a href="new_role/sub_role.php#mind_open">公開者</a>の影響で、観戦している人も夜の発言を見ることができます</li>
</ul>

<h3 id="critical"><?php OptionManager::OutputCaption('critical'); ?> [Ver. 1.4.0 β15～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('critical'); ?></li>
<li><a href="new_role/sub_role.php#critical_voter">会心</a>・<a href="new_role/sub_role.php#critical_luck">痛恨</a>の発動率はランダム付加の場合と同じです</li>
</ul>

<h3 id="sudden_death"><?php OptionManager::OutputCaption('sudden_death'); ?> [Ver. 1.4.0 α14～]</h3>
<ul>
<li>全ユーザに<a href="new_role/sub_role.php#chicken_group">小心者系</a>のどれかがつきます</li>
<li>配役制限がついているもの (例：<a href="new_role/sub_role.php#panelist">解答者</a>) はつきません</li>
<li><a href="new_role/sub_role.php#impatience">短気</a>がつくのは最大で一人です</li>
<li><a href="#perverseness"><?php OptionManager::OutputCaption('perverseness'); ?></a>と併用できません</li>
</ul>

<h3 id="perverseness"><?php OptionManager::OutputCaption('perverseness'); ?> [Ver. 1.4.0 α19～]</h3>
<ul>
<li>全ユーザに<a href="new_role/sub_role.php#perverseness">天の邪鬼</a>がつきます</li>
<li><a href="#sudden_death"><?php OptionManager::OutputCaption('sudden_death'); ?></a>と併用できません</li>
</ul>

<h3 id="joker"><?php OptionManager::OutputCaption('joker'); ?> [Ver. 1.4.0 β21～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('joker'); ?></li>
<li>ゲーム終了時に<a href="new_role/sub_role.php#joker">ジョーカー</a>を所持していると無条件で敗北になります</li>
</ul>

<h3 id="death_note"><?php OptionManager::OutputCaption('death_note'); ?> [Ver. 1.4.0 β21～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('death_note'); ?></li>
<li>毎日、夜→昼の処理終了時の生存者からランダムで一人に<a href="new_role/sub_role.php#death_note">デスノート</a>が配布されます</li>
<li>配布状況は配役公開状態の霊界からのみ見ることができます</li>
</ul>

<h3 id="weather"><?php OptionManager::OutputCaption('weather'); ?> [Ver. 1.5.0 α2～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('weather'); ?></li>
<li>発生するのは 3 の倍数の日です (3 → 6 → 9 → ...)</li>
<li>各天候の発生率は設定ファイルで変更できます</li>
<li>天候の詳細は専用ページを参照して下さい → <a href="weather.php">天候システム</a></li>
</ul>

<h3 id="festival"><?php OptionManager::OutputCaption('festival'); ?> [Ver. 1.4.0 β9～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('festival'); ?></li>
<li>初期設定では、以下に示す人数の範囲だけ、固定編成になります</li>
<li>編成の初期設定はバージョンアップ時に変更される事があります</li>
<li><a href="#replace_human"><?php OptionManager::OutputCaption('replace_human'); ?></a>・<a href="#special_role_option">特殊配役設定</a>は無効になります</li>
</ul>
<?php InfoHTML::OutputFestival(); ?>
<pre>
出展：
 9人：狩人村 (特殊F) ＠桃栗鯖
10人：逃亡者村 (特殊R) ＠桃栗鯖
13人：奴隷村＠世紀末鯖
14人：邪魔狂人村＠人狼天国鯖
15人：マインスイーパ村＠世紀末鯖
16人：囁き狂人村＠人狼 BBS C国
19人：猫又村＠わかめて鯖
20人：奴隷/狂信者/子狐村＠世紀末鯖
22人：バルサン村＠わかめて鯖
</pre>

<h3 id="replace_human"><?php OptionManager::OutputCaption('replace_human'); ?> [Ver. 1.4.0 β14～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('replace_human'); ?></li>
<li><a href="#full_mania"><?php OptionManager::OutputCaption('full_mania'); ?></a>を拡張して実装したオプションです</li>
<li>表記が村人となる役職が存在する事に注意してください</li>
<li>「<?php OptionManager::OutputCaption('replace_human'); ?>」<?php Info::OutputReplaceRole('replace_human'); ?></li>
</ul>

<h4 id="full_mad"><?php OptionManager::OutputCaption('full_mad'); ?> [Ver. 1.5.0 β10～]</h4>
<ul>
<li>村人が全員<a href="new_role/wolf.php#mad">狂人</a>になります</li>
<li><a href="#change_mad"><?php OptionManager::OutputCaption('change_mad'); ?></a>より先に処理されます</li>
</ul>

<h4 id="full_cupid"><?php OptionManager::OutputCaption('full_cupid'); ?> [Ver. 1.4.0 β14～]</h4>
<ul>
<li>村人が全員<a href="new_role/lovers.php#cupid">キューピッド</a>になります</li>
<li><a href="#change_cupid"><?php OptionManager::OutputCaption('change_cupid'); ?></a>より先に処理されます</li>
</ul>

<h4 id="full_quiz"><?php OptionManager::OutputCaption('full_quiz'); ?> [Ver. 1.5.0 β10～]</h4>
<ul>
<li>村人が全員<a href="new_role/quiz.php#quiz">出題者</a>になります</li>
</ul>

<h4 id="full_vampire"><?php OptionManager::OutputCaption('full_vampire'); ?> [Ver. 1.5.0 β10～]</h4>
<ul>
<li>村人が全員<a href="new_role/vampire.php#vampire">吸血鬼</a>になります</li>
</ul>

<h4 id="full_chiroptera"><?php OptionManager::OutputCaption('full_chiroptera'); ?> [Ver. 1.4.0 β14～]</h4>
<ul>
<li>村人が全員<a href="new_role/chiroptera.php#chiroptera">蝙蝠</a>になります</li>
</ul>

<h4 id="full_mania"><?php OptionManager::OutputCaption('full_mania'); ?> [Ver. 1.4.0 α17～]</h4>
<ul>
<li>村人が全員<a href="new_role/mania.php#mania">神話マニア</a>になります</li>
</ul>

<h4 id="full_unknown_mania"><?php OptionManager::OutputCaption('full_unknown_mania'); ?> [Ver. 1.5.0 β10～]</h4>
<ul>
<li>村人が全員<a href="new_role/mania.php#unknown_mania">鵺</a>になります</li>
</ul>

<h3 id="change_common"><?php OptionManager::OutputCaption('change_common'); ?> [Ver. 1.5.0 β10～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('change_common'); ?></li>
<li>「<?php OptionManager::OutputCaption('change_common'); ?>」<?php Info::OutputReplaceRole('change_common'); ?></li>
</ul>

<h4 id="change_hermit_common"><?php OptionManager::OutputCaption('change_hermit_common'); ?> [Ver. 1.5.0 β10～]</h4>
<ul>
<li>共有者が全員<a href="new_role/human.php#hermit_common">隠者</a>になります</li>
</ul>

<h3 id="change_mad"><?php OptionManager::OutputCaption('change_mad'); ?> [Ver. 1.5.0 β6～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('change_mad'); ?></li>
<li>「<?php OptionManager::OutputCaption('change_mad'); ?>」<?php Info::OutputReplaceRole('change_mad'); ?></li>
<li><a href="#full_mad"><?php OptionManager::OutputCaption('full_mad'); ?></a>の処理が先に適用されます</li>
</ul>

<h4 id="change_fanatic_mad"><?php OptionManager::OutputCaption('change_fanatic_mad'); ?> [Ver. 1.5.0 β6～]</h4>
<ul>
<li>狂人が全員<a href="new_role/wolf.php#fanatic_mad">狂信者</a>になります</li>
</ul>

<h4 id="change_whisper_mad"><?php OptionManager::OutputCaption('change_whisper_mad'); ?> [Ver. 1.5.0 β6～]</h4>
<ul>
<li>狂人が全員<a href="new_role/wolf.php#whisper_mad">囁き狂人</a>になります</li>
</ul>

<h4 id="change_immolate_mad"><?php OptionManager::OutputCaption('change_immolate_mad'); ?> [Ver. 1.5.0 β10～]</h4>
<ul>
<li>狂人が全員<a href="new_role/wolf.php#immolate_mad">殉教者</a>になります</li>
</ul>

<h3 id="change_cupid"><?php OptionManager::OutputCaption('change_cupid'); ?> [Ver. 1.5.0 β17～]</h3>
<ul>
<li><?php OptionManager::OutputExplain('change_cupid'); ?></li>
<li>「<?php OptionManager::OutputCaption('change_cupid'); ?>」<?php Info::OutputReplaceRole('change_cupid'); ?></li>
<li><a href="#full_cupid"><?php OptionManager::OutputCaption('full_cupid'); ?></a>の処理が先に適用されます</li>
</ul>

<h4 id="change_mind_cupid"><?php OptionManager::OutputCaption('change_mind_cupid'); ?> [Ver. 1.5.0 β17～]</h4>
<ul>
<li>キューピッドが全員<a href="new_role/lovers.php#mind_cupid">女神</a>になります</li>
</ul>

<h4 id="change_triangle_cupid"><?php OptionManager::OutputCaption('change_triangle_cupid'); ?> [Ver. 1.5.0 β17～]</h4>
<ul>
<li>キューピッドが全員<a href="new_role/lovers.php#triangle_cupid">小悪魔</a>になります</li>
</ul>

<h4 id="change_angel"><?php OptionManager::OutputCaption('change_angel'); ?> [Ver. 1.5.0 β17～]</h4>
<ul>
<li>キューピッドが全員<a href="new_role/lovers.php#angel">天使</a>になります</li>
</ul>

<h2 id="special_role_option">特殊配役設定</h2>
<p>
<?php InfoHTML::OutputCategory(array('special_role', 'chaos', 'duel', 'gray_random', 'step', 'quiz')); ?>
</p>

<h3 id="special_role"><?php OptionManager::OutputCaption('special_role'); ?> [Ver. 1.4.0 β17～]</h3>
<ul>
<li>専用の配役テーブルを用いた特殊設定村です</li>
<li>詳細は個々のモードを参照してください</li>
</ul>

<h4 id="chaos"><?php OptionManager::OutputCaption('chaos'); ?> [Ver. 1.4.0 α1～]</h4>
<ul>
<li>専用ページを参照して下さい → <a href="chaos.php"><?php OptionManager::OutputCaption('chaos'); ?></a></li>
</ul>

<h4 id="duel"><?php OptionManager::OutputCaption('duel'); ?> [Ver. 1.4.0 α19～]</h4>
<ul>
  <li><a href="#open_cast_option">霊界公開設定オプション</a>の設定によって配役が変わります。初期設定は以下です</li>
  <ol>
    <li><a href="#open_cast">常時公開</a>：暗殺者ベース</li>
    <li><a href="#not_open_cast">非公開</a>：埋毒者ベース</li>
    <li><a href="#auto_open_cast">自動公開</a>：キューピッドベース</li>
  </ol>
</ul>

<h4 id="gray_random"><?php OptionManager::OutputCaption('gray_random'); ?> [Ver. 1.4.0 β17～]</h4>
<ul>
  <li>配役が基本職のみになります。初期設定は以下です</li>
  <ol>
    <li>人狼系 → 人狼</li>
    <li>狂人系 → 狂人</li>
    <li>妖狐陣営 → 妖狐</li>
    <li>上記以外 → 村人</li>
  </ol>
</ul>

<h4 id="step"><?php OptionManager::OutputCaption('step'); ?> [Ver. 2.2.0 α3～]</h4>
<ul>
  <li>配役が<a href="new_role/ability.php#step">足音能力者</a>ベースになります。初期設定は以下です</li>
  <ol>
    <li>占い師系 → <a href="new_role/human.php#step_mage">審神者</a></li>
    <li>霊能者系 → 霊能者</li>
    <li>狩人系 → <a href="new_role/human.php#step_guard">山立</a></li>
    <li>人狼系 → <a href="new_role/wolf.php#step_wolf">響狼</a></li>
    <li>狂人系 → <a href="new_role/wolf.php#step_mad">家鳴</a></li>
    <li>妖狐陣営 → <a href="new_role/fox.php#step_fox">響狐</a></li>
    <li>上記以外 → 村人</li>
  </ol>
</ul>

<h4 id="quiz"><?php OptionManager::OutputCaption('quiz'); ?> [Ver. 1.4.0 α2～]</h4>
<ul>
  <li>GM が<a href="new_role/quiz.php#quiz">出題者</a>になります</li>
  <li>村を作成する際に GM ログインパスワードの入力が必要です</li>
  <li>GM もゲーム開始投票をする必要があります</li>
  <li>出現役職は村人・共有者・人狼・狂人・妖狐です</li>
  <li>GM 以外の全員に<a href="new_role/sub_role.php#panelist">解答者</a>がつきます</li>
  <li>人狼は常時 GM しか狙えません</li>
  <li>GM は人狼に襲撃されても死亡しません</li>
  <li>GM のみ、処刑投票の集計状況が見えます</li>
  <li>以下のような使い方を想定しています</li>
  <ol>
    <li>GM がクイズを出題してゲーム開始</li>
    <li>人狼が適当なタイミングで GM を噛む</li>
    <li>夜が明けたらユーザが解答する</li>
    <li>全員解答したら GM が正解発表</li>
    <li>ユーザは不正解なら GM に投票、正解なら GM 以外に投票</li>
    <li>GM は正解者の中で一番解答が遅かった人に投票</li>
    <li>GM は日が暮れる前に次の問題を出題する</li>
    <li>以下、勝敗が決まるまで繰り返す</li>
  </ol>
</ul>
<h5>Ver. 2.2.0 α4～</h5>
<pre>
GM のみ、処刑投票の集計状況が見えます
</pre>
</body>
</html>
