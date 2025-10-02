<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('能力者逆引き');
?>
<p>
<a href="#assassin">暗殺</a>
<a href="#anti_assassin">暗殺耐性</a>
<a href="#phantom">占い妨害</a>
<a href="#guard_hunt">狩り</a>
<a href="#guard">護衛</a>
<a href="#guard_limit">護衛制限</a>
<a href="#decide">処刑者決定</a>
<a href="#sudden_death">ショック死</a>
<a href="#anti_sudden_death">ショック死抑制</a>
<a href="#resist_wolf">人狼襲撃耐性</a>
<a href="#sex">性別</a>
<a href="#revive">蘇生</a>
<a href="#revive_limit">蘇生制限</a>
</p>
<p>
<a href="#authority">投票数変化</a>
<a href="#poison">毒</a>
<a href="#cursed_group">呪い</a>
<a href="#possessed">憑依</a>
<a href="#possessed_limit">憑依制限</a>
<a href="#seal">封印</a>
<a href="#sacrifice">身代わり</a>
<a href="#dummy">夢</a>
</p>

<h2><a id="assassin">暗殺能力者</a></h2>
<pre>
<a href="human.php#assassin_group">暗殺者系</a>・<a href="fox.php#doom_fox">冥狐</a>・<a href="ogre.php">鬼陣営</a>
</pre>

<h2><a id="anti_assassin">暗殺耐性能力者</a></h2>
<pre>
<a href="human.php#assassin_spec">暗殺の仕様</a>参照
</pre>

<h2><a id="phantom">占い妨害能力者</a></h2>
<pre>
<a href="human.php#phantom_doll">倫敦人形</a>・<a href="wolf.php#phantom_wolf">幻狼</a>・<a href="wolf.php#jammer_mad">月兎</a>・<a href="fox.php#phantom_fox">幻狐</a>・<a href="fox.php#jammer_fox">月狐</a>
</pre>

<h2><a id="guard_hunt">狩り対象者</a></h2>
<pre>
<a href="human.php#guard_hunt">狩人系</a>参照
</pre>

<h2><a id="guard">護衛能力者</a></h2>
<pre>
<a href="human.php#guard">狩人</a>・<a href="human.php#hunter_guard">猟師</a>・<a href="human.php#blind_guard">夜雀</a>・<a href="human.php#reflect_guard">侍</a>・<a href="human.php#poison_guard">騎士</a>・<a href="human.php#fend_guard">忍者</a>
</pre>

<h2><a id="guard_limit">護衛制限対象者</a></h2>
<pre>
<a href="human.php#guard_limit">狩人系</a>参照
</pre>

<h2><a id="decide">処刑者決定能力者</a></h2>
<pre>
<a href="human.php#saint">聖女</a>・<a href="human.php#executor">執行者</a>・<a href="wolf.php#agitate_mad">扇動者</a>・<a href="quiz.php#quiz">出題者</a>・<a href="sub_role.php#decide_group">決定者系</a>
</pre>

<h2><a id="sudden_death">ショック死発動能力者</a></h2>
<h3><a id="sudden_death_direct">直接発動型</a></h3>
<pre>
<a href="human.php#bacchus_medium">神主</a>・<a href="human.php#seal_medium">封印師</a>・<a href="human.php#jealousy">橋姫</a>・<a href="wolf.php#agitate_mad">扇動者</a>・<a href="sub_role.php#chicken_group">小心者系</a>・<a href="sub_role.php#challenge_lovers">難題</a>
</pre>
<h3><a id="sudden_death_indirect">間接発動型</a></h3>
<pre>
<a href="sub_role.php#chicken">小心者</a>・<a href="sub_role.php#febris">熱病</a>・<a href="sub_role.php#frostbite">凍傷</a>・<a href="sub_role.php#death_warrant">死の宣告</a>・<a href="sub_role.php#panelist">解答者</a>参照
</pre>

<h2><a id="anti_sudden_death">ショック死抑制能力者</a></h2>
<pre>
<a href="human.php#cure_pharmacist">河童</a>・<a href="human.php#revive_pharmacist">仙人</a>
</pre>

<h2><a id="resist_wolf">人狼襲撃耐性能力者</a></h2>
<h3><a id="resist_wolf_full">常時無効</a></h3>
<pre>
<a href="fox.php#fox_group">妖狐系</a>(<a href="fox.php#white_fox">白狐</a>・<a href="fox.php#poison_fox">管狐</a>を除く)・<a href="vampire.php#doom_vampire">冥血鬼</a>・<a href="lovers.php#sacrifice_angel">守護天使</a>・<a href="mania.php#sacrifice_mania">影武者</a>
</pre>
<h3><a id="resist_wolf_limited">限定無効</a></h3>
<pre>
<a href="human.php#fend_guard">忍者</a>・<a href="human.php#escaper_group">逃亡者系</a>・<a href="wolf.php#therian_mad">獣人</a>・<a href="ogre.php">鬼陣営</a>・<a href="sub_role.php#challenge_lovers">難題</a>
</pre>

<h2><a id="sex">性別関連能力者</a></h2>
<h3><a id="sex_mage">性別鑑定能力者</a></h3>
<pre>
<a href="human.php#sex_mage">ひよこ鑑定士</a>・<a href="wolf.php#sex_wolf">雛狼</a>・<a href="fox.php#sex_fox">雛狐</a>
</pre>
<h3><a id="sex_only">性別限定能力者</a></h3>
<pre>
<a href="human.php#incubus_escaper">一角獣</a>・<a href="lovers.php#angel">天使</a>・<a href="lovers.php#rose_angel">薔薇天使</a>・<a href="lovers.php#lily_angel">百合天使</a>・<a href="vampire.php#incubus_vampire">青髭公</a>・<a href="vampire.php#succubus_vampire">飛縁魔</a>・<a href="ogre.php#incubus_ogre">般若</a>・<a href="ogre.php#succubus_yaksa">荼枳尼天</a>・<a href="sub_role.php#androphobia">男性恐怖症</a>・<a href="sub_role.php#gynophobia">女性恐怖症</a>
</pre>

<h2><a id="revive">蘇生能力者</a></h2>
<h3><a id="revive_other">他者蘇生能力者</a></h3>
<pre>
<a href="human.php#revive_medium">風祝</a>・<a href="human.php#poison_cat_group">猫又系</a>・<a href="fox.php#revive_fox">仙狐</a>
</pre>
<h3><a id="revive_self">自己蘇生能力者</a></h3>
<pre>
<a href="human.php#revive_priest">天人</a>・<a href="human.php#revive_pharmacist">仙人</a>・<a href="human.php#revive_brownie">蛇神</a>・<a href="human.php#revive_doll">西蔵人形</a>・<a href="ogre.php#revive_ogre">茨木童子</a>
</pre>

<h2><a id="revive_limit">蘇生制限対象者</a></h2>
<pre>
<a href="human.php#about_revive">基本ルール[蘇生]</a>参照
</pre>

<h2><a id="authority">投票数変化能力者</a></h2>
<pre>
<a href="human.php#elder">長老</a>・<a href="human.php#scripter">執筆者</a>・<a href="human.php#brownie">座敷童子</a>・<a href="wolf.php#elder_wolf">古狼</a>・<a href="fox.php#elder_fox">古狐</a>・<a href="chiroptera.php#elder_chiroptera">古蝙蝠</a>・<a href="sub_role.php#authority_group">権力者系</a>
</pre>

<h2><a id="poison">毒能力者</a></h2>
<pre>
<a href="human.php#poison_guard">騎士</a>・<a href="human.php#poison_group">埋毒者系</a>・<a href="human.php#poison_cat">猫又</a>・<a href="human.php#poison_jealousy">毒橋姫</a>・<a href="human.php#poison_doll">鈴蘭人形</a>・<a href="wolf.php#poison_wolf">毒狼</a>・<a href="fox.php#poison_fox">管狐</a>・<a href="chiroptera.php#poison_chiroptera">毒蝙蝠</a>・<a href="ogre.php#poison_ogre">榊鬼</a>
</pre>

<h2><a id="cursed_group">呪い能力者</a></h2>
<h3><a id="cursed">呪い所持者</a></h3>
<pre>
<a href="human.php#cursed_brownie">祟神</a>・<a href="wolf.php#cursed_wolf">呪狼</a>・<a href="fox.php#cursed_fox">天狐</a>・<a href="chiroptera.php#cursed_chiroptera">呪蝙蝠</a>
</pre>
<h3><a id="voodoo">呪術能力者</a></h3>
<pre>
<a href="wolf.php#voodoo_mad">呪術師</a>・<a href="fox.php#voodoo_fox">九尾</a>
</pre>

<h2><a id="possessed">憑依能力者</a></h2>
<pre>
<a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>
</pre>

<h2><a id="possessed_limit">憑依制限能力者</a></h2>
<pre>
<a href="human.php#detective_common">探偵</a>・<a href="#revive_self">自己蘇生能力者</a>・<a href="#possessed_limit">憑依能力者</a>
</pre>

<h2><a id="seal">封印対象者</a></h2>
<pre>
<a href="human.php#seal_medium">封印師</a>参照
</pre>

<h2><a id="sacrifice">身代わり能力者</a></h2>
<pre>
<a href="human.php#doll_master">人形遣い</a>・<a href="lovers.php#sacrifice_angel">守護天使</a>・<a href="vampire.php#sacrifice_vampire">吸血公</a>・<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>・<a href="ogre.php#sacrifice_ogre">酒呑童子</a>・<a href="mania.php#sacrifice_mania">影武者</a>・<a href="sub_role.php#protected">庇護者</a>・<a href="sub_role.php#psycho_infected">洗脳者</a>
</pre>

<h2><a id="dummy">夢能力者</a></h2>
<pre>
<a href="human.php#dummy_mage">夢見人</a>・<a href="human.php#dummy_necromancer">夢枕人</a>・<a href="human.php#dummy_priest">夢司祭</a>・<a href="human.php#dummy_guard">夢守人</a>・<a href="human.php#dummy_common">夢共有者</a>・<a href="human.php#dummy_poison">夢毒者</a>・<a href="chiroptera.php#dummy_chiroptera">夢求愛者</a>・<a href="mania.php#dummy_mania">夢語部</a>
</pre>
</body></html>
