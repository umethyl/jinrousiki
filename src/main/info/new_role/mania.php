<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('神話マニア陣営');
?>
<p>
<a href="#rule">基本ルール</a>
<a href="#change_mania_camp">所属変更</a>
</p>
<p>
<a href="#mania_group">神話マニア系</a>
<a href="#unknown_mania_group">鵺系</a>
</p>

<h2><a id="rule">基本ルール</a></h2>
<ol>
  <li>初日の夜に誰か一人を選んでその人と同じ陣営に変化 (コピー) する特殊な陣営です。</li>
  <li>勝利条件はコピー先の陣営になります。</li>
  <li>コピーの結果が反映されるのは 2 日目の朝です。</a>
  <li>なんらかの理由でコピーが成立しなかった場合は村人陣営と扱われます。</li>
  <li>コピーが成立する前に突然死した場合の<a href="human.php#medium_group">巫女系</a>の陣営判定は「村人」です。</li>
</ol>

<h2><a id="change_mania_camp">所属変更</a></h2>
<h3>Ver. 1.4.0 β18～</h3>
<pre>
<a href="#unknown_mania">鵺</a>の所属を<a href="#mania_group">神話マニア系</a>から<a href="#unknown_mania_group">鵺系</a>に変更。
</pre>
<h3>Ver. 1.4.0 β13～</h3>
<pre>
<a href="#mania_group">神話マニア系</a>の所属を<a href="human.php">村人陣営</a>から神話マニア陣営に変更。
</pre>

<h2><a id="mania_group">神話マニア系</a></h2>
<p>
<a href="#mania">神話マニア</a>
<a href="#trick_mania">奇術師</a>
<a href="#soul_mania">覚醒者</a>
<a href="#dummy_mania">夢語部</a>
</p>

<h3><a id="mania">神話マニア</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α11～]</h3>
<pre>
神話マニア陣営の基本種。能力は<a href="#rule">基本ルール</a>参照。
コピー結果は相手の基本役職で、神話マニア陣営を選んだ場合は<a href="human.php#human">村人</a>になる。
コピー成立後は<a href="sub_role.php#copied">元神話マニア</a>がつく。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
カード人狼にある役職です。元と違い、占いや狼以外の役職もコピーします。
CO するべきかどうかは、コピーした役職次第です。
</pre>

<h3><a id="trick_mania">奇術師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<pre>
コピー先の役職を奪うことができる、特殊な神話マニア。
コピー能力は<a href="#mania">神話マニア</a>と同じ。
役職が変化すると<a href="sub_role.php#copied_trick">元奇術師</a>がつく。
</pre>
<ol>
  <li>身代わり君・<a href="human.php#revive_priest">天人</a>は入れ替え対象外です。</li>
  <li>「初日の夜に投票をしてなかった人」が入れ替え対象になります。</li>
  <li>入れ替えが発生すると、コピー先はその系統の基本職に変化します。</li>
</ol>
</pre>
<h4>コピーの結果例</h4>
<pre>
1. A[奇術師] → B[<a href="human.php#soul_mage">魂の占い師</a>] =&gt; A[魂の占い師] B[魂の占い師]
初日に投票しているので入れ替えは発生しません。
<a href="human.php#mage_group">占い師系</a>・<a href="lovers.php">恋人陣営</a>・<a href="chiroptera.php#fairy_group">妖精系</a>や、<a href="human.php#mind_scanner_group">さとり系</a>・<a href="wolf.php#mad_group">狂人系</a>・<a href="fox.php">妖狐陣営</a>の一部などがこれに該当します。

2. A[奇術師] → B[<a href="human.php#yama_necromancer">閻魔</a>] =&gt; A[閻魔] B[霊能者]
入れ替わりが発生してもコピー先には特にメッセージが出ないので、
朝、突然役職表記が入れ替わってしまうことになります。

3. A[奇術師] → B[<a href="human.php#dummy_guard">夢守人</a>] =&gt; A[夢守人] B[狩人]
この場合はコピー先は入れ替わりを自覚できないことになります。

4. A[奇術師] → B[<a href="human.php#revive_priest">天人</a>] =&gt; A[天人] B[天人]
天人は初日に投票しませんが、死亡処理が入るので例外的に入れ替え対象外です。

5. A[奇術師] → B[<a href="wolf.php#tongue_wolf">舌禍狼</a>] → 身代わり君 =&gt; A[舌禍狼] B[舌禍狼]
投票している狼をコピーした場合は入れ替えが発生しません。

6. A[奇術師] → B[<a href="wolf.php#trap_mad">罠師</a>] =&gt; A[罠師] B[狂人]
初日に投票していない狂人は入れ替えが発生します。
</pre>
<h5>Ver. 1.4.0 β11～</h5>
<pre>
役職変化後に付加される役職を<a href="sub_role.php#copied">元神話マニア</a>から<a href="sub_role.php#copied_trick">元奇術師</a>に変更。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
「所属陣営は初日の夜の投票で確定する」というルールの範囲内で
「相手の能力を奪う」役職を作れないかな、と思案してこういう実装になりました。
</pre>

<h3><a id="soul_mania">覚醒者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<pre>
コピー先の上位種に変化する特殊な神話マニア。
役職が変化すると<a href="sub_role.php#copied_soul">元覚醒者</a>がつく。
</pre>
<ol>
  <li>入れ替わるのは 4 日目の朝で、それまでは覚醒者のままです。</li>
  <li>2 日目の朝にどの役職系になるのか (コピー先の役職の系統) 分かります。<br>
    例) A[覚醒者] → B[<a href="wolf.php#boss_wolf">白狼</a>]  =&gt; 「Bさんは人狼でした」
  </li>
  <li>4 日目の朝にどの役職になったのか分かります。</li>
  <li>神話マニア陣営を選んだ場合は村人になります。</li>
  <li>蘇生されるケースがあるので、死亡していても変化処理は行なわれます。</li>
</ol>
<table>
<tr><th>コピー元</th><th>コピー結果</th><th>設定変更</th></tr>
<tr>
  <td><a href="human.php#human_group">村人系</a></td>
  <td><a href="human.php#executor">執行者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#mage_group">占い師系</a></td>
  <td><a href="human.php#soul_mage">魂の占い師</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#necromancer_group">霊能者系</a></td>
  <td><a href="human.php#soul_necromancer">雲外鏡</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#medium_group">巫女系</a></td>
  <td><a href="human.php#revive_medium">風祝</a></td>
  <td>Ver. 1.4.0 β13～</td>
</tr>
<tr>
  <td><a href="human.php#priest_group">司祭系</a></td>
  <td><a href="human.php#high_priest">大司祭</a></td>
  <td>Ver. 1.4.0 β21～</td>
</tr>
<tr>
  <td><a href="human.php#guard_group">狩人系</a></td>
  <td><a href="human.php#poison_guard">騎士</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#common_group">共有者系</a></td>
  <td><a href="human.php#ghost_common">亡霊嬢</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#poison_group">埋毒者系</a></td>
  <td><a href="human.php#strong_poison">強毒者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#poison_cat_group">猫又系</a></td>
  <td><a href="human.php#revive_cat">仙狸</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#pharmacist_group">薬師系</a></td>
  <td><a href="human.php#alchemy_pharmacist">錬金術師</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="human.php#assassin_group">暗殺者系</a></td>
  <td><a href="human.php#soul_assassin">辻斬り</a></td>
  <td>Ver. 1.4.0 β13～</td>
</tr>
<tr>
  <td><a href="human.php#mind_scanner_group">さとり系</a></td>
  <td><a href="human.php#clairvoyance_scanner">猩々</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="human.php#jealousy_group">橋姫系</a></td>
  <td><a href="human.php#poison_jealousy">毒橋姫</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#brownie_group">座敷童子系</a></td>
  <td><a href="human.php#history_brownie">白澤</a></td>
  <td>Ver. 1.4.0 β16～</td>
</tr>
<tr>
  <td><a href="human.php#doll_group">上海人形系</a></td>
  <td><a href="human.php#doll_master">人形遣い</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#escaper_group">逃亡者系</a></td>
  <td><a href="human.php#escaper">逃亡者</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="wolf.php#wolf_group">人狼系</a></td>
  <td><a href="wolf.php#sirius_wolf">天狼</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="wolf.php#mad_group">狂人系</a></td>
  <td><a href="wolf.php#whisper_mad">囁き狂人</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="fox.php#fox_group">妖狐系</a></td>
  <td><a href="fox.php#cursed_fox">天狐</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="fox.php#child_fox_group">子狐系</a></td>
  <td><a href="fox.php#jammer_fox">月狐</a></td>
  <td>Ver. 1.4.0 β14～</td>
</tr>
<tr>
  <td><a href="lovers.php#cupid_group">キューピッド系</a></td>
  <td><a href="lovers.php#sweet_cupid">弁財天</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="lovers.php#angel_group">天使系</a></td>
  <td><a href="lovers.php#sacrifice_angel">守護天使</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="quiz.php#quiz_group">出題者系</a></td>
  <td><a href="quiz.php#quiz">出題者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="vampire.php#vampire_group">吸血鬼系</a></td>
  <td><a href="vampire.php#soul_vampire">吸血姫</a></td>
  <td>Ver. 1.4.0 β19～</td>
</tr>
<tr>
  <td><a href="chiroptera.php#chiroptera_group">蝙蝠系</a></td>
  <td><a href="chiroptera.php#boss_chiroptera">大蝙蝠</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="chiroptera.php#fairy_group">妖精系</a></td>
  <td><a href="chiroptera.php#ice_fairy">氷妖精</a></td>
  <td>Ver. 1.4.0 β16～</td>
</tr>
<tr>
  <td><a href="ogre.php#ogre_group">鬼系</a></td>
  <td><a href="ogre.php#sacrifice_ogre">酒呑童子</a></td>
  <td>Ver. 1.4.0 β20～</td>
</tr>
<tr>
  <td><a href="ogre.php#yaksa_group">夜叉系</a></td>
  <td><a href="ogre.php#dowser_yaksa">毘沙門天</a></td>
  <td>Ver. 1.4.0 β21～</td>
</tr>
<tr>
  <td>神話マニア陣営</td>
  <td><a href="human.php#human">村人</a></td>
  <td></td>
</tr>
</table>
<h4>同一表示役職</h4>
<pre>
<a href="#dummy_mania">夢語部</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#incubate_poison">潜毒者</a>の亜種を作ろうとして色々検討した結果、こういう実装になりました。
能力発動のタイミングを考慮して<a href="human.php#incubate_poison">潜毒者</a>より一日早く変化処理を行っています。
</pre>

<h3><a id="dummy_mania">夢語部</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<h4>[役職表示] <a href="#soul_mania">覚醒者</a></h4>
<pre>
コピー先の基本・劣化種に変化する特殊な神話マニア。
本人の表記は「<a href="#soul_mania">覚醒者</a>」で、仕様も同じ。
役職が変化すると<a href="sub_role.php#copied_teller">元夢語部</a>がつく。
変化前に<a href="wolf.php#dream_eater_mad">獏</a>に襲撃されると殺される。
</pre>
<table>
<tr><th>コピー元</th><th>コピー結果</th><th>設定変更</th></tr>
<tr>
  <td><a href="human.php#human_group">村人系</a></td>
  <td><a href="human.php#suspect">不審者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#mage_group">占い師系</a></td>
  <td><a href="human.php#dummy_mage">夢見人</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#necromancer_group">霊能者系</a></td>
  <td><a href="human.php#dummy_necromancer">夢枕人</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#medium_group">巫女系</a></td>
  <td><a href="human.php#medium">巫女</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#priest_group">司祭系</a></td>
  <td><a href="human.php#dummy_priest">夢司祭</a></td>
  <td>Ver. 1.4.0 β15～</td>
</tr>
<tr>
  <td><a href="human.php#guard_group">狩人系</a></td>
  <td><a href="human.php#dummy_guard">夢守人</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#common_group">共有者系</a></td>
  <td><a href="human.php#dummy_common">夢共有者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#poison_group">埋毒者系</a></td>
  <td><a href="human.php#dummy_poison">夢毒者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#poison_cat_group">猫又系</a></td>
  <td><a href="human.php#eclipse_cat">蝕仙狸</a></td>
  <td>Ver. 1.4.0 β17～</td>
</tr>
<tr>
  <td><a href="human.php#pharmacist_group">薬師系</a></td>
  <td><a href="human.php#cure_pharmacist">河童</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#assassin_group">暗殺者系</a></td>
  <td><a href="human.php#eclipse_assassin">蝕暗殺者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#mind_scanner_group">さとり系</a></td>
  <td><a href="human.php#mind_scanner">さとり</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#jealousy_group">橋姫系</a></td>
  <td><a href="human.php#jealousy">橋姫</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="human.php#brownie_group">座敷童子系</a></td>
  <td><a href="human.php#brownie">座敷童子</a></td>
  <td>Ver. 1.4.0 β16～</td>
</tr>
<tr>
  <td><a href="human.php#doll_group">上海人形系</a></td>
  <td><a href="human.php#silver_doll">露西亜人形</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="human.php#escaper_group">逃亡者系</a></td>
  <td><a href="human.php#incubus_escaper">一角獣</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="wolf.php#wolf_group">人狼系</a></td>
  <td><a href="wolf.php#silver_wolf">銀狼</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="wolf.php#mad_group">狂人系</a></td>
  <td><a href="wolf.php#mad">狂人</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="fox.php#fox_group">妖狐系</a></td>
  <td><a href="fox.php#silver_fox">銀狐</a></td>
  <td>Ver. 1.4.0 β22～</td>
</tr>
<tr>
  <td><a href="fox.php#child_fox_group">子狐系</a></td>
  <td><a href="fox.php#sex_fox">雛狐</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="lovers.php#cupid_group">キューピッド系</a></td>
  <td><a href="lovers.php#self_cupid">求愛者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="lovers.php#angel_group">天使系</a></td>
  <td><a href="lovers.php#angel">天使</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="quiz.php#quiz_group">出題者系</a></td>
  <td><a href="quiz.php#quiz">出題者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="vampire.php#vampire_group">吸血鬼系</a></td>
  <td><a href="vampire.php#vampire">吸血鬼</a></td>
  <td>Ver. 1.4.0 β14～</td>
</tr>
<tr>
  <td><a href="chiroptera.php#chiroptera_group">蝙蝠系</a></td>
  <td><a href="chiroptera.php#dummy_chiroptera">夢求愛者</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="chiroptera.php#fairy_group">妖精系</a></td>
  <td><a href="chiroptera.php#mirror_fairy">鏡妖精</a></td>
  <td></td>
</tr>
<tr>
  <td><a href="ogre.php#ogre_group">鬼系</a></td>
  <td><a href="ogre.php#incubus_ogre">般若</a></td>
  <td>Ver. 1.4.0 β20～</td>
</tr>
<tr>
  <td><a href="ogre.php#yaksa_group">夜叉系</a></td>
  <td><a href="ogre.php#succubus_yaksa">荼枳尼天</a></td>
  <td>Ver. 1.4.0 β19～</td>
</tr>
<tr>
  <td>神話マニア陣営</td>
  <td><a href="human.php#human">村人</a></td>
  <td></td>
</tr>
</table>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#soul_mania">覚醒者</a>の夢バージョンです。
最終的には自覚することができるので他の<a href="ability.php#dummy">夢系</a>と比べると対応はしやすいかもしれません。
</pre>

<h2><a id="unknown_mania_group">鵺系</a></h2>
<p>
<a href="#unknown_mania">鵺</a>
<a href="#sacrifice_mania">影武者</a>
</p>

<h3><a id="unknown_mania">鵺</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α23～]</h3>
<pre>
初日の夜に誰か一人を選んでその人と同じ所属陣営になる、特殊な神話マニア。
所属陣営が変更されるのは 2 日目の朝で、自分と相手が<a href="sub_role.php#mind_friend">共鳴者</a>になります。
生存カウントは常に村人なので、実質は所属陣営不明の狂人相当です。

<a href="#mania">神話マニア</a>と違い、コピー結果が出ないのでコピー先に聞かないと
自分の所属陣営が分かりません。
</pre>
<h4>所属陣営の判定例</h4>
<pre>
1. 鵺 → <a href="human.php#human">村人</a> (村人陣営)
擬似<a href="human.php#common_group">共有者</a>相当になります。

2. 鵺 → <a href="wolf.php#wolf">人狼</a> (人狼陣営)
コピー先とだけ会話できる<a href="wolf.php#whisper_mad">囁き狂人</a>相当です。

3. 鵺 → <a href="fox.php#fox">妖狐</a> (妖狐陣営)
所属は妖狐ですが自身は妖狐カウントされないので気をつけましょう。

4. 鵺 → <a href="lovers.php#cupid">キューピッド</a> (恋人陣営)
自分の恋人を持たないキューピッド相当になります。

5. 鵺 → <a href="quiz.php#quiz">出題者</a> (出題者陣営)
妖狐同様、自身は出題者カウントされない点に注意してください。

6. 鵺 → <a href="vampire.php#vampire">吸血鬼</a> (吸血鬼陣営)
勝利条件は<a href="vampire.php#rule">基本ルール [吸血鬼]</a>参照。

7. 鵺 → <a href="chiroptera.php#chiroptera">蝙蝠</a> (蝙蝠陣営)
コピー先と会話できる蝙蝠相当になります。
相手の生死と自分の勝敗は無関係です。

8. 鵺 → <a href="ogre.php#ogre">鬼</a> (鬼陣営)
勝利条件は<a href="ogre.php#rule">基本ルール [鬼]</a>参照。

9. 鵺 → <a href="wolf.php#wolf">人狼</a>[恋人] (人狼陣営)
サブ役職は判定対象外 (<a href="human.php#medium">巫女</a>と同じ) なので
コピー先と勝利陣営が異なる、例外ケースとなります。

10. 鵺 → <a href="wolf.php#wolf">人狼</a>[<a href="sub_role.php#mind_read">サトラレ</a>] (人狼陣営)
コピー先が村人陣営の<a href="human.php#mind_scanner">さとり</a>に会話を覗かれている状態なので
コピー先からの情報入手が難しくなります。

11. 鵺A → 鵺B → <a href="wolf.php#wolf">人狼</a> (全員人狼陣営)
コピー先が鵺だった場合は鵺以外の役職に当たるまで
コピー先を辿って判定します。

12. 鵺A → 鵺B → 鵺C → 鵺A (全員村人陣営)
コピー先を辿る途中で自分に戻った場合は村人陣営になります。

13. 鵺 → <a href="#mania">神話マニア</a> → <a href="fox.php#fox">妖狐</a> (妖狐陣営)
神話マニアをコピーした場合はコピー結果の陣営になります。

14. 鵺A → <a href="#mania">神話マニア</a> → 鵺B → <a href="wolf.php#wolf">人狼</a>
神話マニアは鵺をコピーしたら村人になるので鵺のリンクが切れます。
結果として以下のようになります。
鵺A(村人陣営) → 村人(元神話マニア) / 鵺B (人狼陣営) → 人狼
</pre>
<h5>Ver. 1.4.0 β19～</h5>
<pre>
<a href="vampire.php">吸血鬼陣営</a>をコピーした場合の勝利条件判定を変更 (<a href="vampire.php#rule">基本ルール [吸血鬼]</a>参照)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
やる夫人狼の薔薇 GM に、「初心者の指南用に使える役職」を
要請されてこういう実装にしてみました。
鵺が初心者をコピーして指南するイメージですね。

もしも、教えてもらう前にコピー先が死んでしまったら自分の所属陣営は
「正体不明」になる事になります。とっても理不尽ですね。
</pre>

<h3><a id="sacrifice_mania">影武者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β18～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
コピー先に<a href="sub_role.php#protected">庇護者</a>を付加する上位鵺。
自分と相手が<a href="sub_role.php#mind_friend">共鳴者</a>になる。
陣営判定法則などの基本的な仕様は<a href="#unknown_mania">鵺</a>と同じ。
人狼に襲撃されても死亡しない (襲撃は失敗扱い)。
襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合は耐性無効。
</pre>
<h4>関連役職</h4>
<pre>
<a href="vampire.php#doom_vampire">冥血鬼</a>・<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="lovers.php#sacrifice_angel">守護天使</a>の鵺バージョンです。
構想自体はこちらが先で、かなり前から検討されていた能力です。
</pre>
</body></html>
