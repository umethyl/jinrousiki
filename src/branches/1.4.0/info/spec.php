<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('MESSAGE');
OutputInfoPageHeader('詳細な仕様');
?>
<p>
<a href="#decide_role">配役決定ルーチン</a>
<a href="#dummy_boy">身代わり君 (GM)</a>
<a href="#dead">死因一覧</a>
<a href="#vote">投票</a>
<a href="#revive_refuse">蘇生辞退システム</a>
</p>

<h2><a id="decide_role">配役決定ルーチン</a></h2>
<p>
<a href="#decide_role_room">村</a>
<a href="#decide_role_dummy_boy">身代わり君</a>
<a href="#decide_role_user">ユーザ</a>
</p>

<h3><a id="decide_role_room">村</a></h3>
<ol>
<li>参加人数を取得</li>
<li>人数毎に設定されている配役データを取得 (<a href="cast.php">配役一覧</a>参照)</li>
<li>特殊村なら全て差し替える</li>
<li>通常村ならゲームオプションに応じて個別に入れ替える</li>
<li>配役決定</li>
</ol>

<h3><a id="decide_role_dummy_boy">身代わり君</a></h3>
<ol>
<li>配役を取得</li>
<li>ランダムな配役リストを作る</li>
<li>身代わり君がなれる役職に当たるまで先頭からチェック</li>
<li>全てチェックして見つからなければエラーを返す</li>
<li>配役決定</li>
</ol>

<h3><a id="decide_role_user">ユーザ</a></h3>
<ol>
<li>身代わり君の配役を決定してユーザリストから「決定済みリスト」へ移動</li>
<li>ランダムなユーザリストを作る</li>
<li>リストの先頭の人の希望役職を確認</li>
<li>何か希望してて空きがあればその人の役が決定、「決定済みリスト」へ移動</li>
<li>希望なしか空きがなければ「未決定リスト」へ移動</li>
<li>全部振り終えたら「未決定リスト」の人に余りを割り振る</li>
</ol>

<h2><a id="dummy_boy">身代わり君 (GM) の仕様</a></h2>
<ol>
<li>常時、ゲーム終了後相当の情報が見えます</li>
<li>ゲーム開始前のユーザの「役職」は「希望役職」です</li>
<li>単独の KICK 投票でユーザを蹴りだせます</li>
<li>ゲーム中は「遺言」発言をすると専用システムメッセージになります</li>
<li>投票能力がある役職であっても投票することはできません</li>
</ol>

<h2><a id="dead">死因一覧</a></h2>
<p>
<a href="#dead_common">共通</a>
<a href="#dead_day">昼</a>
<a href="#dead_night">夜</a>
</p>

<h3><a id="dead_common">共通</a></h3>
<h4>～<?php echo $MESSAGE->sudden_death ?></h4>
<ul>
<li>未投票突然死</li>
</ul>

<h4>～<?php echo $MESSAGE->lovers_followed ?></h4>
<ul>
<li><a href="new_role/sub_role.php#lovers">恋人</a>後追い</li>
</ul>

<h4>～<?php echo $MESSAGE->joker_moved ?></h4>
<ul>
<li><a href="new_role/sub_role.php#joker">ジョーカー</a>の移動 (配役公開状態限定)</li>
</ul>


<h3><a id="dead_day">昼</a></h3>
<h4>～<?php echo $MESSAGE->vote_killed ?></h4>
<ul>
<li>処刑</li>
</ul>

<h4>～<?php echo $MESSAGE->deadman ?></h4>
<ul>
<li>毒 (<a href="new_role/ability.php#poison">毒能力者</a>)</li>
<li>罠 (<a href="new_role/human.php#trap_common">策士</a>)</li>
</ul>

<h4>～<?php echo $MESSAGE->vote_sudden_death ?></h4>
<ul>
<li>ショック死 (<a href="new_role/ability.php#sudden_death">ショック死発動能力者</a>)</li>
</ul>

<h4><?php echo $MESSAGE->blind_vote ?></h4>
<ul>
<li><a href="new_role/wolf.php#amaze_mad">傘化け</a>の能力発動</li>
</ul>

<h3><a id="dead_night">夜</a></h3>
<h4>～<?php echo $MESSAGE->deadman ?></h4>
<ul>
<li>人狼襲撃 (<a href="new_role/wolf.php#wolf_group">人狼系</a>)</li>
<li>餓狼襲撃 (<a href="new_role/wolf.php#hungry_wolf">餓狼</a>)</li>
<li>身代わり (<a href="new_role/ability.php#sacrifice">身代わり能力者</a>)</li>
<li>毒 (<a href="new_role/ability.php#poison">毒能力者</a>)</li>
<li>罠 (<a href="new_role/wolf.php#trap_mad">罠師</a>)</li>
<li>逃亡失敗 (<a href="new_role/human.php#escaper_group">逃亡者系</a>)</li>
<li>吸血 (<a href="new_role/vampire.php#incubus_vampire">青髭公</a>・<a href="new_role/vampire.php#succubus_vampire">飛縁魔</a>)</li>
<li>暗殺 (<a href="new_role/human.php#assassin_group">暗殺者系</a>)</li>
<li>人攫い (<a href="new_role/ogre.php">鬼陣営</a>)</li>
<li><a href="new_role/human.php#guard_hunt">狩り</a> (<a href="new_role/human.php#guard_group">狩人系</a>)</li>
<li>夢食い (<a href="new_role/wolf.php#dream_eater_mad">獏</a>)</li>
<li>呪殺 (<a href="new_role/human.php#mage">占い師</a>)</li>
<li>呪返し (<a href="new_role/human.php#voodoo_killer">陰陽師</a>・<a href="new_role/ability.php#cursed_group">呪い能力者</a>)</li>
<li>憑依 (<a href="new_role/ability.php#possessed">憑依能力者</a>)</li>
<li>憑依解放 (<a href="new_role/human.php#anti_voodoo">厄神</a>)</li>
<li>人外尾行 (<a href="new_role/human.php#reporter">ブン屋</a>)</li>
<li>帰還 (<a href="new_role/human.php#revive_priest">天人</a>)</li>
</ul>
<h4>～<?php echo $MESSAGE->revive_success ?></h4>
<ul>
<li>蘇生 (<a href="new_role/ability.php#revive">蘇生能力者</a>)</li>
</ul>

<h4>～<?php echo $MESSAGE->revive_failed ?></h4>
<ul>
<li>蘇生失敗 (霊界限定) (<a href="new_role/ability.php#revive_other">他者蘇生能力者</a>)</li>
</ul>

<h4>～<?php echo $MESSAGE->flowered_a ?> (一例)</h4>
<ul>
<li>悪戯 (<a href="new_role/chiroptera.php#flower_fairy">花妖精</a>)</li>
</ul>

<h4>～<?php echo $MESSAGE->constellation_a ?> (一例)</h4>
<ul>
<li>悪戯 (<a href="new_role/chiroptera.php#star_fairy">星妖精</a>)</li>
</ul>

<h2><a id="vote">投票処理の仕様</a></h2>
<p>
<a href="#vote_legend">判例</a>
<a href="#vote_day">昼</a>
<a href="#vote_night">夜</a>
</p>

<h3><a id="vote_legend">判例</a></h3>
<ul>
<li>「→」死因決定の単位</li>
<li>「＞」判定優先順位 (判定上書き)</li>
</ul>

<h3><a id="vote_day">昼</a></h3>
<pre>
+ 処理順序
  - 投票集計 → 処刑者決定 → 役職判定 → 後追い

+ 処刑者決定法則
  - 単独トップ ＞ <a href="new_role/sub_role.php#decide">決定者</a> ＞ <a href="new_role/sub_role.php#bad_luck">不運</a> ＞ <a href="new_role/sub_role.php#impatience">短気</a> ＞ <a href="new_role/sub_role.php#good_luck">幸運</a>が逃れる ＞ <a href="new_role/sub_role.php#plague">疫病神</a>の投票先が逃れる

+ 役職判定順
  - <a href="new_role/quiz.php#quiz">出題者</a> →<a href="new_role/human.php#executor">執行者</a> → <a href="new_role/human.php#saint">聖女</a> → <a href="new_role/wolf.php#agitate_mad">扇動者</a> → <a href="new_role/human.php#pharmacist_group">薬師系</a> ＞ 抗毒判定 ＞ 毒発動判定 →
    <a href="new_role/human.php#seal_medium">封印師</a> → <a href="new_role/human.php#bacchus_medium">神主</a> → <a href="new_role/human.php#trap_common">策士</a> → <a href="new_role/human.php#jealousy">橋姫</a> → <a href="new_role/sub_role.php#chicken_group">ショック死</a> → <a href="new_role/human.php#divorce_jealousy">縁切地蔵</a> → <a href="new_role/human.php#cursed_brownie">祟神</a>
</pre>

<h3><a id="vote_night">夜</a></h3>
<pre>
+ 処理順序
  - 恋人 → 接触 → 夢 → 占い → &lt;日にち別処理&gt; → 憑依 → 後追い → 司祭
    &lt;[初日] コピー → 帰還 / [二日目以降] 尾行 → 反魂 → 蘇生&gt;

+ 恋人 (<a href="new_role/lovers.php">恋人陣営</a>)
  - 相互作用はないので投票直後に処理を行う

+ 接触 (罠・逃亡・護衛・身代わり・人狼襲撃・狩り・吸血・暗殺)
  - 罠 ＞ 逃亡失敗 →
    罠 ＞ 狩人護衛 ＞ <a href="new_role/sub_role.php#challenge_lovers">難題</a> ＞ <a href="new_role/sub_role.php#protected">庇護者</a> ＞ 襲撃耐性 ＞ 身代わり ＞ 人狼襲撃 →
    狩人の狩り → 罠能力者の罠死 → 罠 ＞ 狩人護衛 ＞ 吸血 → 罠 ＞ 暗殺 → 凍傷判定

  - 罠能力者 (<a href="new_role/wolf.php#trap_mad">罠師</a>・<a href="new_role/wolf.php#snow_trap_mad">雪女</a>)
  - 逃亡能力者 (<a href="new_role/human.php#escaper_group">逃亡者系</a>)
  - 護衛能力者 (<a href="new_role/human.php#guard_group">狩人系</a>)
  - 襲撃耐性能力者 (<a href="new_role/human.php#escaper_group">逃亡者系</a>・<a href="new_role/human.php#fend_guard">忍者</a>・<a href="new_role/wolf.php#therian_mad">獣人</a>・<a href="new_role/fox.php#fox_group">妖狐系</a>・<a href="new_role/lovers.php#sacrifice_angel">守護天使</a>・<a href="new_role/ogre.php">鬼陣営</a>・<a href="new_role/mania.php#sacrifice_mania">影武者</a>・<a href="new_role/sub_role.php#challenge_lovers">難題</a>)
  - <a href="new_role/ability.php#sacrifice">身代わり能力者</a>
  - 吸血能力者 (<a href="new_role/vampire.php">吸血鬼陣営</a>)
  - <a href="new_role/ability.php#assassin">暗殺能力者</a>


+ 夢 (<a href="new_role/human.php#dummy_guard">夢守人</a>・<a href="new_role/wolf.php#dream_eater_mad">獏</a>)
  - 夢守人護衛 ＞ 獏襲撃 → 夢守人の狩り

+ 占い (<a href="new_role/human.php#mage_group">占い師系</a>他・<a href="new_role/human.php#anti_voodoo">厄神</a>・<a href="new_role/wolf.php#jammer_mad">月兎</a>他・<a href="new_role/wolf.php#voodoo_mad">呪術師</a>他)
  - 厄払い ＞ 占い妨害 ＞ 呪い ＞ 占い (呪殺)
</pre>

<h2><a id="revive_refuse">蘇生辞退システム</a></h2>
<pre>
死亡後、霊界オフ状態の時に投票画面をクリックすると
「蘇生を辞退する」(デフォルト) というボタンが出現します。
それをクリックすると「システム：～さんは蘇生を辞退しました」という
霊界発言が挿入されます。

この状態でその人が蘇生先に選ばれた場合は 100% 蘇生に失敗します。
憑依に関するシステム情報となってしまうため、下界には告知しません。

これは、死亡後に急な用事が入って抜けなければならない人の為の救済措置です。
</pre>
</body></html>
