<?php
require_once('init.php');
Loader::LoadFile('info_functions');
InfoHTML::OutputRoleHeader('天狗陣営');
?>
<p>
<a href="#rule">基本ルール</a>
<a href="#tengu_do">神通力</a>
</p>
<p>
<a href="#tengu_group">天狗系</a>
</p>

<h2 id="rule">基本ルール</h2>
<p>
<a href="#rule_summary">概要</a>
<a href="#rule_win">勝利条件</a>
<a href="#rule_sudden_death">ショック死</a>
<a href="#rule_distinguish">判定</a>
</p>

<h3 id="rule_summary">概要</h3>
<ol>
<li>2 日目昼の配役によって勝利条件が変化する特殊陣営です。</li>
<li>2 日目以降の夜に村人一人に<a href="#tengu_do">神通力</a> (<a href="human.php#mage_rule">占い</a>の一種) をかけることができます。</li>
<li>味方陣営・<a href="ogre.php">鬼陣営</a>から処刑投票されると一定確率 (20%) で<a href="#rule_sudden_death">ショック死</a>します。</li>
</ol>

<h3 id="rule_win">勝利条件</h3>
<ol>
<li>2 日目昼時点の配役を元に、以下の判定法則で<a href="human.php">村人陣営</a>・<a href="wolf.php">人狼陣営</a>のどちらかにつく。
<ol>
<li><a href="human.php">村人陣営</a>・<a href="wolf.php">人狼陣営</a>のうち、人数の少ない方につく。</li>
<li>同数だった場合は<a href="human.php">村人陣営</a>につく。</li>
<li>少ない方が 0 だった場合はもう片方につく。</li>
<li>両陣営とも 0 だった場合は<a href="human.php">村人陣営</a>につく。</li>
<li><a href="sub_role.php#lovers">恋人</a>は<a href="lovers.php">恋人陣営</a>と判定する (例：恋人の人狼は人狼陣営とはカウントしない)。</li>
<li><a href="sub_role.php#lovers">恋人</a>の天狗であっても、味方する陣営はどちらかで固定。</li>
</ol>
</li>
<li>2 日目昼に「あなたは～陣営に味方することにしました」という趣旨のシステムメッセージが本人に表示される。</li>
<li><a href="ability.php#copy_delay">コピー能力者(時間差型)</a> にも 2 日目昼時点で味方陣営通知が表示される。</li>
<li>基本的に自身の生死は不問。</li>
<li><a href="human.php#priest_group">司祭</a>判定は常時「天狗陣営」と判定する。</li>
</ol>

<h3 id="rule_sudden_death">ショック死</h3>
<ol>
<li>味方陣営・<a href="ogre.php">鬼陣営</a>から処刑投票されると一定確率 (20%) でショック死する。</li>
<li>判定は投票毎に実施される。</li>
<li>投票人数は不問。</li>
<li><a href="sub_role.php#lovers">恋人</a>の天狗であっても判定は同じ。</li>
<li>投票者が天狗の場合は、味方陣営と判定される。</li>
<li>投票者が<a href="sub_role.php#lovers">恋人</a>の天狗の場合は、恋人陣営と判定される。</li>
</ol>

<h4>判定例</h4>
<pre>
※ 天狗は人狼の味方についているとする。
</pre>
<ol>
<li>A[天狗] ← B[人狼] ⇒ 有効</li>
<li>A[天狗] ← B[狂人] ⇒ 有効</li>
<li>A[天狗] ← B[村人] ⇒ 無効</li>
<li>A[天狗] ← B[蝙蝠] ⇒ 無効</li>
<li>A[天狗] ← B[鬼] ⇒ 有効</li>
<li>A[天狗] ← B[天狗] ⇒ 有効</li>
<li>A[天狗] ← B[人狼][恋人] ⇒ 無効</li>
<li>A[天狗] ← B[天狗][恋人] ⇒ 無効</li>
<li>A[天狗][恋人] ← B[人狼] ⇒ 有効</li>
<li>A[天狗][恋人] ← B[狂人][恋人] ⇒ 無効</li>
<li>A[天狗][恋人] ← B[天狗][恋人] ⇒ 無効</li>
</ol>

<h5>Ver. 3.0.0 β1～</h5>
<pre>
<a href="ogre.php">鬼陣営</a>からの得票でもショック死する
<a href="sub_role.php#lovers">恋人</a>の天狗の投票者判定条件を変更 (恋人 → 味方陣営)
</pre>

<h3 id="rule_distinguish">判定</h3>
<table>
<tr>
  <th>生存カウント</th>
  <th>占い</th>
  <th>霊能</th>
  <th>精神鑑定</th>
  <th>性別鑑定</th>
</tr>
<tr>
  <td>村人</td>
  <td>村人</td>
  <td>天狗</td>
  <td>正常</td>
  <td>性別</td>
</tr>
</table>
<h4>関連役職</h4>
<pre>
<a href="ability.php#necromancer">特殊霊能判定能力者</a>
</pre>


<h2 id="tengu_do">神通力の仕様</h2>
<ol>
<li>占いカテゴリに属し、<a href="human.php#mage_rule">占いの仕様</a>が適用される。</li>
<li><a href="ability.php#phantom">占い妨害</a>・<a href="ability.php#cursed_group">呪い</a>の影響を受ける。</li>
<li>投票先が特定の役職 (<a href="human.php#guard_group">狩人系</a>・<a href="human.php#assassin_group">暗殺者系</a>・<a href="wolf.php#wolf_group">人狼系</a>・<a href="fox.php#child_fox_group">子狐系</a>) の場合、一定確率 (70%) で<a href="sub_role.php#tengu_voice">天狗倒し</a>を付与する。</li>
<li>投票先が味方陣営だった場合は発動率が半分 (端数切り上げ) になる。</li>
</ol>

<h5>Ver. 3.0.0 α7～</h5>
<pre>
神通力の発動対象に<a href="human.php#assassin_group">暗殺者系</a>を追加
投票先が味方陣営だった場合は発動率が半分 (端数切り上げ) になる。
</pre>

<h2 id="tengu_group">天狗系</h2>
<p>
<a href="#tengu">天狗</a>
<a href="#soul_tengu">大天狗</a>
<a href="#passion_tengu">尼天狗</a>
<a href="#meteor_tengu">流星天狗</a>
<a href="#priest_tengu">鼻高天狗</a>
<a href="#eclipse_tengu">木っ端天狗</a>
</p>

<h3 id="tengu">天狗 (占い結果：村人 / 霊能結果：天狗) [Ver. 3.0.0 α2～]</h3>
<h4>[神通力] タイプ：天狗倒し付与 / 対象：通常 / 成功率：70%</h4>
<pre>
天狗陣営の<a href="mania.php#basic_mania">基本種</a>。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
テーマは「村 vs. 狼」の調整役です。
偏りすぎた配役に混ざる事で一定のパワーバランスを保つ働きを担う事を狙っています。
</pre>

<h3 id="soul_tengu">大天狗 (占い結果：村人 / 霊能結果：天狗) [Ver. 3.0.0 α2～]</h3>
<h4>[耐性] 狩り：有効</h4>
<h4>[神通力] タイプ：役職判別 / 対象：無制限 / 成功率：70%</h4>
<pre>
神通力の対象者の役職を知る事ができる上位種。
神通力が成功する対象に制限は無い。
</pre>
<h4>関連役職</h4>
<pre>
<a href="mania.php#soul_mania">覚醒者</a>・<a href="ability.php#soul">役職鑑定能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
天狗陣営最上位種としてデザインしました。
護衛を受けられない欠点をどうカバーするかがポイントです。
</pre>

<h3 id="passion_tengu">尼天狗 (占い結果：村人 / 霊能結果：天狗) [Ver. 3.0.0 α5～]</h3>
<h4>[神通力] タイプ：恋色迷彩付与 / 対象：通常 / 成功率：70%</h4>
<pre>
神通力が成功したら<a href="sub_role.php#passion">恋色迷彩</a>を付加する特殊な天狗。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#talk_convert">発言変換能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="vampire.php#passion_vampire">牡丹灯籠</a>の天狗バージョンです。
</pre>

<h3 id="meteor_tengu">流星天狗 (占い結果：村人 / 霊能結果：天狗) [Ver. 3.0.0 α7～]</h3>
<h4>[神通力] タイプ：神隠し / 対象：通常 / 成功率：70%</h4>
<pre>
神通力が成功したら神隠し (特殊呪殺) を行う特殊な天狗。
</pre>
<h4>神通力</h4>
<ol>
<li><a href="../spec.php#dead">死因</a>は「～は神隠しに遭いました」と表示される。</li>
<li><a href="ability.php#special_resist">特殊耐性能力者</a>には無効。</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
殺傷能力を持つ上位種で、名称は山海経の天狗が出展です。
</pre>

<h3 id="priest_tengu">鼻高天狗 (占い結果：村人 / 霊能結果：天狗) [Ver. 3.0.0 α7～]</h3>
<h4>[耐性] 護衛制限：有り</h4>
<h4>[神通力] タイプ：天狗倒し付与 / 対象：通常 / 成功率：70%</h4>
<pre>
神通力に加え、特殊な司祭能力も持つ上位天狗。
</pre>
<h4>司祭能力</h4>
<ol>
<li>判定内容は、現在生存している村人陣営 + 人狼陣営の合計。</li>
<li>判定が出るのは 4 日目以降の偶数日 (4 → 6 → 8 →...)。</li>
<li><a href="sub_role.php#lovers">恋人</a>・<a href="sub_role.php#fake_lovers">愛人</a>は<a href="lovers.php">恋人陣営</a>と判定される。</li>
<li>基本仕様は<a href="human.php#priest_group">司祭系</a>と同じ。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#camp">陣営判定能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
司祭能力を持つ上位種です。
他の<a href="human.php#priest_group">司祭系</a>と組み合わせる事で詳細な配役を推測することができます。
<a href="human.php#guard_limit">護衛制限</a>があるので人狼に味方する場合は騙りの工夫次第で
<a href="ability.php#guard">護衛能力者</a>を混乱させることができるかもしれません。
</pre>

<h3 id="eclipse_tengu">木っ端天狗 (占い結果：村人 / 霊能結果：天狗) [Ver. 3.0.0 α2～]</h3>
<h4>[神通力] タイプ：天狗倒し付与 / 対象：通常 / 成功率：70%</h4>
<pre>
50% の確率で成功した神通力が自分に跳ね返ってしまう劣化種。
</pre>
<h4>関連役職</h4>
<pre>
<a href="mania.php#dummy_mania">夢語部</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
劣化種として蝕系を採用しました。
他の劣化種と違い自覚はありますが、神通力を使わないという選択肢は無いので
動き方が難しくなります。
</pre>
</body>
</html>
