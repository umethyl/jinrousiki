<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('鬼陣営');
?>
<p>
<a href="#rule">基本ルール</a>
<a href="#ogre_do_spec">人攫いの仕様</a>
</p>
<p>
<a href="#ogre_group">鬼系</a>
<a href="#yaksa_group">夜叉系</a>
</p>

<h2><a id="rule">基本ルール</a></h2>
<ol>
  <li>勝利条件が個々によって違う特殊陣営です。</li>
  <li>共通の勝利条件は「自分自身の生存」で、<a href="chiroptera.php">蝙蝠陣営</a>同様、他陣営の勝敗と競合しません。</li>
  <li>ゲーム終了時に鬼陣営をコピーした<a href="mania.php#unknown_mania_group">鵺系</a>・変化前の<a href="mania.php#soul_mania">覚醒者</a>・<a href="mania.php#dummy_mania">夢語部</a>がいた場合、<br>
    例外的に勝利条件は「自身の生存のみ」となります。
  </li>
  <li><a href="sub_role.php#lovers">恋人</a>は<a href="lovers.php">恋人陣営</a>と判定します (例：恋人の人狼は人狼陣営とはカウントしない)。</li>
  <li>2日目以降の夜に村人一人を攫う (<a href="human.php#assassin_spec">暗殺</a>の一種) ことができます。</li>
  <li>人狼に襲撃されても一定確率で無効化します (襲撃は失敗扱い)。</li>
  <li>襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合は耐性無効です。</a>
  <li><a href="human.php#assassin_spec">暗殺</a>を一定確率で反射します。</li>
  <li>生存カウントは村人です。</li>
  <li><a href="human.php#psycho_mage">精神鑑定士</a>・<a href="human.php#sex_mage">ひよこ鑑定士</a>の判定は「鬼」です。</li>
</ol>

<h2><a id="ogre_do_spec">人攫いの仕様</a></h2>
<ol>
  <li>暗殺カテゴリに属し、<a href="human.php#assassin_spec">暗殺の仕様</a>が適用されます。</li>
  <li>「人攫いする / しない」を必ず投票する必要があります。</li>
  <li>攫われた人の死亡メッセージは人狼の襲撃と同じで、死因は「鬼に攫われた」です。</li>
  <li>人攫いが成立するたびに成功率が低下 (下限は 1%) します (例：100% → 20% → 4% → 1%)。</li>
</ol>

<h2><a id="ogre_group">鬼系</a></h2>
<p>
<a href="#ogre">鬼</a>
<a href="#orange_ogre">前鬼</a>
<a href="#indigo_ogre">後鬼</a>
<a href="#poison_ogre">榊鬼</a>
<a href="#west_ogre">金鬼</a>
<a href="#east_ogre">風鬼</a>
<a href="#north_ogre">水鬼</a>
<a href="#south_ogre">隠行鬼</a>
<a href="#incubus_ogre">般若</a>
<a href="#power_ogre">星熊童子</a>
<a href="#revive_ogre">茨木童子</a>
<a href="#sacrifice_ogre">酒呑童子</a>
</p>
<h3><a id="ogre">鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β18～]</h3>
<h4>[耐性] 人狼襲撃：無効 (30%) / 暗殺：反射 (30%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/5</h4>
<pre>
鬼陣営の基本種。勝利条件は「自分自身と<a href="wolf.php#wolf_group">人狼系</a> (種類・恋人不問) の生存」。
人狼が一人でも生存していればいいので、人狼陣営の勝利を目指す必要はない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
テーマは「全陣営に対する不確定要素」です。
<a href="wolf.php#mad_group">狂人系</a>とは似て非なる勝利条件なので狐や恋人が出現している場合は
逆に人狼にとって脅威になる可能性があります。
</pre>

<h3><a id="orange_ogre">前鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β18～]</h3>
<h4>[耐性] 人狼襲撃：無効 (30%) / 暗殺：反射 (30%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/5</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + <a href="wolf.php">人狼陣営</a>の全滅」。
<a href="wolf.php#mad_group">狂人</a>や人狼陣営に付いた<a href="mania.php#unknown_mania_group">鵺系</a>も含まれることに注意。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
役 小角が従えていた赤鬼がモチーフで、<a href="#indigo_ogre">後鬼</a> (青鬼) と夫婦です。
勝利条件の関係で、<a href="#ogre">鬼</a>とは敵対することになります。
</pre>

<h3><a id="indigo_ogre">後鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β18～]</h3>
<h4>[耐性] 人狼襲撃：無効 (30%) / 暗殺：反射 (30%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/5</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + <a href="wolf.php">妖狐陣営</a>の全滅」。
妖狐陣営に付いた<a href="mania.php#unknown_mania_group">鵺系</a>も含まれることに注意。
妖狐陣営が出現していない場合は自己の生存のみで勝利となる。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#orange_ogre">前鬼</a>の対妖狐バージョンです。
勝利条件が競合する他の鬼が少ないので比較的動きやすいと思います。
</pre>

<h3><a id="poison_ogre">榊鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (30%) / 暗殺：反射 (30%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ： <a href="sub_role.php#panelist">解答者</a>付加 / 成功率低下：1/3</h4>
<h4>[毒能力] 処刑：人狼系 + 妖狐陣営 + 鬼陣営 / 襲撃：有り / 薬師判定：有り</h4>
<pre>
鬼系の一種で、勝利条件は「<a href="quiz.php">出題者陣営</a>の勝利、または自分自身の生存」。
毒能力は劣化<a href="human.php#strong_poison">強毒者</a>相当。
<a href="#ogre_do_spec">人攫い</a>の効果は<a href="sub_role.php#panelist">解答者</a>の付加で、対象が<a href="quiz.php#quiz">出題者</a>だった場合は無効。
<a href="quiz.php">出題者陣営</a>が出現して敗北しても自分が生存していれば勝利となる。
逆に、<a href="quiz.php">出題者陣営</a>が勝利していれば死亡していても勝利となる。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#strong_poison">強毒者</a>・<a href="chiroptera.php#poison_chiroptera">毒蝙蝠</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
花祭りの花太夫と問答する鬼がモチーフです。「さかきおに」と読みます。
毒能力は<a href="chiroptera.php#poison_chiroptera">毒蝙蝠</a>と同タイプなので気をつけましょう。
</pre>

<h3><a id="west_ogre">金鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/2</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 自分と同列の左側にいる人の全滅 + 村人陣営の勝利」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
入村位置に依存するタイプで、藤原千方の四鬼がモチーフです。
陰陽五行に当てはめて金気＝西と見立てています。
名前に「金」が付いていますが、<a href="wolf.php#gold_wolf">金狼</a>・<a href="fox.php#gold_fox">金狐</a>とは能力の関連はありません。
</pre>

<h3><a id="east_ogre">風鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/2</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 自分と同列の右側にいる人の全滅 + 村人陣営の勝利」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#west_ogre">金鬼</a>の対右側バージョンです。木気＝東と見立てています。
右端にいる場合は最初から条件を一つクリアしていることになります。
</pre>

<h3><a id="north_ogre">水鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/2</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 自分と同列の上側にいる人の全滅 + 村人陣営の勝利」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#west_ogre">金鬼</a>の対上側バージョンです。水気＝北と見立てています。
勝利条件が厳しいので他の鬼系と比べると能力がやや高めに設定されています。
</pre>

<h3><a id="south_ogre">隠行鬼</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/2</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 自分と同列の下側にいる人の全滅 + 村人陣営の勝利」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#west_ogre">金鬼</a>の対下側バージョンです。
隠行鬼は火鬼と言われることもあり、火気＝南と見立てています。
四鬼系は互いの位置によって敵にも味方にもなるのが厄介なところですね。
</pre>

<h3><a id="incubus_ogre">般若</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/2</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 女性の全滅」。
自分自身の性別は勝利条件には影響しない。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#succubus_yaksa">荼枳尼天</a>・<a href="ability.php#sex">性別関連能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#orange_ogre">前鬼</a>の対女性バージョンです。
仮に、村人が全員女性だった場合は単独生存を達成しないと勝てないことになります。
</pre>

<h3><a id="power_ogre">星熊童子</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β21～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：7/10</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 村の人口を三分の一以下にする」。
人口判定は端数切り上げ (例：22人村なら8人以下)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
人口規制タイプで、大江山の四天王がモチーフです。
状況に応じて減らすべき陣営が変わるのがポイントです。
</pre>

<h3><a id="revive_ogre">茨木童子</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β22～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 蘇生 (40%) / 暗殺：反射 (40%) / 罠：有効 / 蘇生：不可 / 憑依：無効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 / 成功率低下：1/2</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + <a href="human.php#psycho_mage">精神鑑定士</a>が『嘘吐き』判定を出す人の全滅」。
人狼に襲撃されて死亡した場合、一定確率 (40%) で蘇生する。
</pre>
<ol>
  <li>何度蘇生しても蘇生率は一定。</li>
  <li>恋人になったら蘇生能力は無効。</li>
  <li>人狼の襲撃以外で死亡した場合 (例：<a href="ability.php#assassin">暗殺</a>)、蘇生能力は無効。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、蘇生能力は無効。</li>
  <li>蘇生対象外 (選ばれた場合は失敗する)。</li>
  <li><a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#dream_eater_mad">獏</a>・<a href="ability.php#revive">蘇生能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/890" target="_top">新役職考案スレ</a> の 890 が原型です。
</pre>

<h3><a id="sacrifice_ogre">酒呑童子</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β20～]</h3>
<h4>[耐性] 人狼襲撃：身代わり / 暗殺：反射 (50%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：<a href="sub_role.php#psycho_infected">洗脳者</a>付加 / 成功率低下：1/2</h4>
<h4>[身代わり能力] 洗脳者</h4>
<pre>
鬼系の一種で、勝利条件は「自分自身の生存 + 村人陣営以外の勝利」。
<a href="#ogre_do_spec">人攫い</a>の効果は<a href="sub_role.php#psycho_infected">洗脳者</a>の付加で、村にいる<a href="sub_role.php#psycho_infected">洗脳者</a>が誰か知ることができる。
対象が<a href="vampire.php">吸血鬼陣営</a> (<a href="mania.php#unknown_mania_group">鵺系</a>など、<a href="mania.php">神話マニア陣営</a>も含む) だった場合、人攫いは無効。
他の鬼と違い、<a href="wolf.php#wolf_group">人狼</a>襲撃を自力では無効化できないが、<a href="sub_role.php#psycho_infected">洗脳者</a>が生きていれば
身代わりにすることができる。
</pre>
<ol>
  <li>身代わりが発生した場合、<a href="wolf.php#wolf_group">人狼</a>の襲撃は失敗扱い。</li>
  <li>代わりに死んだ人の死因は「誰かの犠牲となって死亡したようです」。</li>
  <li>本人は身代わりが発生しても分からない。</li>
  <li>人狼に遭遇した<a href="human.php#escaper">逃亡者</a>を身代わりにすることはできない。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、身代わり能力は無効。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
鬼系最上位種で、<a href="vampire.php">吸血鬼陣営</a>の支援がテーマです。吸血能力は伝承に由来します。
自力で身代わりを確保できる<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>相当で、非常に高い生存力を持ちます。
</pre>

<h2><a id="yaksa_group">夜叉系</a></h2>
<p>
<a href="#yaksa">夜叉</a>
<a href="#succubus_yaksa">荼枳尼天</a>
<a href="#dowser_yaksa">毘沙門天</a>
</p>
<h3><a id="yaksa">夜叉</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (20%) / 暗殺：反射 (20%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 (人狼系限定) / 成功率低下：1/5</h4>
<pre>
人攫い能力が限定された鬼陣営の亜種。
勝利条件は「自分自身の生存 + <a href="wolf.php#wolf_group">人狼系</a> (恋人を含む) の全滅」。
人攫いの対象が<a href="wolf.php#wolf_group">人狼系</a>以外だった場合は無条件で失敗する。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
人攫いが成立する対象が勝利条件に直結した相手に限定されて
相対的に精度が上がった代りに耐性が落ちています。
</pre>

<h3><a id="succubus_yaksa">荼枳尼天</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β19～]</h3>
<h4>[耐性] 人狼襲撃：無効 (20%) / 暗殺：反射 (20%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 (男性限定) / 成功率低下：1/2</h4>
<pre>
夜叉系の一種で勝利条件は「自分自身の生存 + 男性の全滅」。
人攫いの対象が男性以外だった場合は無条件で失敗する。
自分自身の性別は勝利条件には影響しない。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#incubus_ogre">般若</a>・<a href="ability.php#sex">性別関連能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#yaksa">夜叉</a>の対男性バージョンです。「だきにてん」と読みます。
性別を偽って入村する人もいるので勝利するのは難しいと思われます。
</pre>

<h3><a id="dowser_yaksa">毘沙門天</a> (占い結果：鬼 / 霊能結果：鬼) [Ver. 1.4.0 β21～]</h3>
<h4>[耐性] 人狼襲撃：無効 (40%) / 暗殺：反射 (40%) / 罠：有効</h4>
<h4>[人攫い能力] 襲撃タイプ：暗殺 (サブ役職所持者限定) / 成功率低下：1/2</h4>
<pre>
夜叉系の一種で勝利条件は「自分自身の生存 + 自分よりサブ役職の所持数の多い人の全滅」。
人攫いの対象がサブ役職を所持していない場合は無条件で失敗する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#dowser_priest">探知師</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#yaksa">夜叉</a>の対サブ役職バージョンです。「びしゃもんてん」と読みます。
サブ役職の所持数は日毎に変化するので難易度は高めです。
多数のサブ役職を付加する特殊<a href="lovers.php">キューピッド</a>、毎日サブ役職を付加していく
<a href="vampire.php">吸血鬼</a>・<a href="chiroptera.php#fairy_group">妖精系</a>が主な障害となります。
</pre>
</body></html>
