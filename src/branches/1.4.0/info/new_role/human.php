<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('村人陣営');
?>
<p>
<a href="#change_human_camp">所属変更</a>
</p>
<p>
<a href="#human_group">村人系</a>
<a href="#mage_group">占い師系</a>
<a href="#necromancer_group">霊能者系</a>
<a href="#medium_group">巫女系</a>
<a href="#priest_group">司祭系</a>
<a href="#guard_group">狩人系</a>
<a href="#common_group">共有者系</a>
<a href="#poison_group">埋毒者系</a>
<a href="#poison_cat_group">猫又系</a>
<a href="#pharmacist_group">薬師系</a>
</p>
<p>
<a href="#assassin_group">暗殺者系</a>
<a href="#mind_scanner_group">さとり系</a>
<a href="#jealousy_group">橋姫系</a>
<a href="#brownie_group">座敷童子系</a>
<a href="#doll_group">上海人形系</a>
<a href="#escaper_group">逃亡者系</a>
</p>

<h2><a id="change_human_camp">所属変更</a></h2>
<h3>Ver. 1.4.0 β22～</h3>
<pre>
<a href="#escaper">逃亡者</a>の所属を<a href="#human_group">村人系</a>から<a href="#escaper_group">逃亡者系</a>に変更。
</pre>
<h3>Ver. 1.4.0 β16～</h3>
<pre>
<a href="#brownie">座敷童子</a>の所属を<a href="#human_group">村人系</a>から<a href="#brownie_group">座敷童子系</a>に変更。
</pre>
<h3>Ver. 1.4.0 β13～</h3>
<pre>
<a href="mania.php#mania_group">神話マニア系</a>の所属を村人陣営から<a href="mania.php">神話マニア陣営</a>に変更。
<a href="#medium">巫女</a>の所属を<a href="#necromancer_group">霊能者系</a>から<a href="#medium_group">巫女系</a>に変更。
</pre>
<h3>Ver. 1.4.0 β2～</h3>
<pre>
<a href="#poison_cat">猫又</a>の所属を<a href="#poison_group">埋毒者系</a>から<a href="#poison_cat_group">猫又系</a>に変更。
</pre>


<h2><a id="human_group">村人系</a></h2>
<p>
<a href="#human">村人</a>
<a href="#saint">聖女</a>
<a href="#executor">執行者</a>
<a href="#elder">長老</a>
<a href="#scripter">執筆者</a>
<a href="#suspect">不審者</a>
<a href="#unconscious">無意識</a>
</p>

<h3><a id="human">村人</a> (占い結果：村人 / 霊能結果：村人)</h3>
<pre>
村人陣営の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#saint">聖女</a>・<a href="#executor">執行者</a>・<a href="#suspect">不審者</a>・<a href="#unconscious">無意識</a>・<a href="#crisis_priest">預言者</a>・<a href="#chain_poison">連毒者</a>
</pre>
<h4>関連役職</h4>
<pre>
<a href="#brownie">座敷童子</a>・<a href="wolf.php#tongue_wolf">舌禍狼</a>
</pre>

<h3><a id="saint">聖女</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β7～]</h3>
<h4>[役職表示] <a href="#human">村人</a></h4>
<pre>
再投票の最多得票者になった場合に、内訳によって処刑する人を変化させる村人。
本人表記は「<a href="#human">村人</a>」。
</pre>
<ol>
  <li><a href="../spec.php#vote_day">判定</a>は<a href="#executor">執行者</a>の後</li>
  <li>非村人 (村人陣営以外 + <a href="sub_role.php#lovers">恋人</a>) を一人だけ含む → 非村人が吊られる</li>
  <li>非村人が複数含まれている → 再投票</li>
  <li>全員村人 + 最多得票者の聖女は自分だけ → 自分が吊られる</li>
  <li>全員村人 + 最多得票者の聖女が複数いる → 再投票</li>
  <li>自分が恋人だった場合は自分も非村人扱い<br>
    例1) 聖女・聖女[恋人]・村人 → 聖女[恋人] が吊られる<br>
    例2) 聖女[恋人]・人狼 → 再投票
  </li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#decide">処刑者決定能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
やる夫人狼のプレイヤーさんがモデルです。
判定法則が少々複雑ですが、基本的には村人陣営が有利になる結果になります。
<a href="sub_role.php#decide">決定者</a>同様、一見地味ですが勝負所で効いてくる存在になることでしょう。
</pre>

<h3><a id="executor">執行者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<h4>[役職表示] <a href="#human">村人</a></h4>
<pre>
再投票発生時に非村人 (村人陣営以外 + 恋人) に投票していた場合は吊る事が出来る村人。
本人表記は「<a href="#human">村人</a>」。
</pre>
<ol>
  <li><a href="../spec.php#vote_day">判定</a>は<a href="quiz.php#quiz">出題者</a>の後</li>
  <li>投票先が非村人 → 非村人が吊られる</li>
  <li>投票先が村人 → 再投票</li>
  <li>執行者が複数 + 非村人に投票していたのは一人だけ → 非村人が吊られる<br>
    例) 執行者A → 村人A、執行者B → 村人B、執行者C → 人狼A<br>
    　=&gt; 人狼A が吊られる
  </li>
  <li>執行者が複数 + 複数が同じ非村人に投票 → 非村人が吊られる<br>
    例) 執行者A → 村人A、執行者B → 人狼A、執行者C → 人狼A<br>
    　=&gt; 人狼A が吊られる
  </li>
  <li>執行者が複数 + 別々の非村人に投票 → 再投票<br>
    例) 執行者A → 村人A、執行者B → 人狼A、執行者C → 妖狐A<br>
    　=&gt; 再投票
  </li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#decide">処刑者決定能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
iM@S人狼のプレイヤーさんの誕生日プレゼントです。
「なんとなく人外に投票する程度の能力」を形にしてみました。
</pre>

<h3><a id="elder">長老</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β5～]</h3>
<pre>
処刑投票数が +1 される村人。
</pre>
<ol>
  <li><a href="sub_role.php#authority_group">権力者系</a>とセットになった場合は追加で補正される。<br>
    例) <a href="sub_role.php#authority_group">権力者</a>ならさらに +1
  </li>
  <li><a href="sub_role.php#panelist">解答者</a>・<a href="sub_role.php#watcher">傍観者</a>とセットになった場合は 0 で固定。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#authority">投票数変化能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="sub_role.php#authority">権力者</a>相当の能力を持った村人です。
PP ラインの計算を難しくさせるために作成してみました。
能力の性質上、これを騙るのはほぼ不可能なので同じ能力を持った他陣営種が存在します。
</pre>

<h3><a id="scripter">執筆者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β22～]</h3>
<pre>
一定期間後 (5 日目以降) に処刑投票数が +1 される村人。
能力を得たら本人に追加のシステムメッセージが表示される。
<a href="sub_role.php#authority_group">権力者系</a>との関係は<a href="#elder">長老</a>と同じ。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#authority">投票数変化能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#incubate_poison">潜毒者</a>の<a href="#elder">長老</a>バージョンで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/902" target="_top">新役職考案スレ</a> の 902 が原型です。
</pre>

<h3><a id="suspect">不審者</a> (占い結果：人狼 / 霊能結果：村人) [Ver. 1.4.0 α9～]</h3>
<h4>[役職表示] <a href="#human">村人</a></h4>
<h4>[耐性] 精神鑑定：嘘つき</h4>
<pre>
不審なあまり、<a href="#mage_group">占い師</a>に人狼と判定されてしまう村人で、本人表記は「<a href="#human">村人</a>」。
また、昼の間だけ、低確率で発言が人狼の遠吠えに入れ替わってしまう (<a href="wolf.php#cute_wolf">萌狼</a>と同じ)。

発言が遠吠えに変換される確率は 1% (管理者は設定ファイルで変更可能)。
変換されたかどうかは本人にしか分からず、客観的な証明は不可能なので
空発言を多数行なって確認する試みは確率的にもほとんど意味が無いので注意。
</pre>
<h5>Ver. 1.4.0 β7～</h5>
<pre>
遠吠え変換の発動を昼限定に変更。
</pre>
<h5>Ver. 1.4.0 α16～</h5>
<pre>
低確率で発言が遠吠えに変換される (<a href="wolf.php#cute_wolf">萌狼</a>と同じ)。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#cute_wolf">萌狼</a>・<a href="fox.php#cute_fox">萌狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
村人陣営ですが、<a href="wolf.php">人狼陣営</a>に有利な存在です。
ただし、人狼サイドにも不審者である事は分からないので
占い師の真贋が読みづらくなります。
</pre>

<h3><a id="unconscious">無意識</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α13～]</h3>
<h4>[役職表示] <a href="#human">村人</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / <a href="sub_role.php#mind_read">サトラレ</a>：無効</h4>
<pre>
他の国で言うと「自覚のない夢遊病者」。
本人には「<a href="#human">村人</a>」と表示されているが、夜になると無意識に歩きまわるため
人狼に無意識であることが分かってしまう (<a href="wolf.php#wolf_partner">仲間表示[人狼]</a>)。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#scarlet_doll">和蘭人形</a>・<a href="fox.php#scarlet_fox">紅狐</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#suspect">不審者</a>同様、村人陣営ですが<a href="wolf.php">人狼陣営</a>に有利な存在です。
無能力であることが確定の村人なので、人狼サイドは潜伏役職を絞りやすくなります。
</pre>


<h2><a id="mage_group">占い師系</a></h2>
<p>
<a href="#mage_rule">基本ルール</a>
</p>
<p>
<a href="#mage">占い師</a>
<a href="#soul_mage">魂の占い師</a>
<a href="#psycho_mage">精神鑑定士</a>
<a href="#sex_mage">ひよこ鑑定士</a>
<a href="#stargazer_mage">占星術師</a>
<a href="#voodoo_killer">陰陽師</a>
<a href="#dummy_mage">夢見人</a>
</p>

<h3><a id="mage_rule">基本ルール</a> [占い]</h3>
<ol>
  <li>占い能力は人狼の襲撃や暗殺などで事前に死んでいたら無効になります。<br>
    例) 人狼に噛まれた占い師が妖狐を占っていても無効。
  </li>
  <li>占い対象先が同様の理由で事前に死んでいたら対象の能力は無効になります。<br>
    例) <a href="#assassin">暗殺者</a>に殺された<a href="wolf.php#cursed_wolf">呪狼</a>を占っても呪返しは受けない。
  </li>
</ol>

<h3><a id="mage">占い師</a> (占い結果：村人 / 霊能結果：村人)</h3>
<h4>[占い能力] 呪殺：有り / 憑依妨害：有り / 月兎：有効 / 呪い：有効</h4>
<pre>
占い師系の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#dummy_mage">夢見人</a>
</pre>

<h3><a id="soul_mage">魂の占い師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α3-7～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：有り / 月兎：有効 / 呪い：有効</h4>
<pre>
占った人の役職が分かる上位占い師。
<a href="fox.php#fox_group">妖狐</a>を占っても呪殺できないが、占い妨害や呪返しは受けるので注意。
</pre>
<h5>Ver. 1.4.0 α15～</h5>
<pre>
<a href="fox.php#fox_group">妖狐</a>を占っても呪殺できない。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#soul_necromancer">雲外鏡</a>・<a href="#soul_assassin">辻斬り</a>・<a href="wolf.php#tongue_wolf">舌禍狼</a>・<a href="vampire.php#soul_vampire">吸血姫</a>・<a href="sub_role.php#mind_sympathy">共感者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんがモデルです。
<a href="wolf.php#boss_wolf">白狼</a>だろうが<a href="fox.php#child_fox">子狐</a>だろうが分かってしまうので村側最強クラスですが、
その分狙われやすいでしょう。
</pre>

<h3><a id="psycho_mage">精神鑑定士</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α18～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：無効</h4>
<pre>
「嘘つき」を探し出す特殊な占い師。
<a href="wolf.php#mad_group">狂人系</a>・<a href="ability.php#dummy">夢系</a>・<a href="#suspect">不審者</a>・<a href="#unconscious">無意識</a>を占うと「嘘をついている」と判定される。
<a href="ogre.php">鬼陣営</a>を占った場合は「鬼」と判定される。
それ以外は「正常である」と判定される。
</pre>
<h5>Ver. 1.4.0 β18～</h5>
<pre>
<a href="ogre.php">鬼陣営</a>を占った場合は「鬼」と判定される。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ogre.php#revive_ogre">茨木童子</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
対狂人専門の占い師です。
一部の本人視点と実際の役職が違うタイプにも対応しています。
精神鑑定士を真と見るなら占われた人視点の役職がほぼ確定します。
人狼や妖狐の騙りは見抜けないので注意してください。
</pre>

<h3><a id="sex_mage">ひよこ鑑定士</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α19～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：無効</h4>
<pre>
性別を判別する特殊な占い師。
<a href="chiroptera.php">蝙蝠陣営</a>・<a href="wolf.php#gold_wolf">金狼</a>・<a href="fox.php#gold_fox">金狐</a>を占った場合は「蝙蝠」と判定される。
<a href="ogre.php">鬼陣営</a>を占った場合は「鬼」と判定される。
</pre>
<h5>Ver. 1.4.0 β18～</h5>
<pre>
<a href="ogre.php">鬼陣営</a>を占った場合は「鬼」と判定される。
</pre>
<h5>Ver. 1.4.0 β8～</h5>
<pre>
<a href="wolf.php#gold_wolf">金狼</a>・<a href="fox.php#gold_fox">金狐</a>を占った場合は「蝙蝠」と判定される。
</pre>
<h5>Ver. 1.4.0 α21～</h5>
<pre>
<a href="chiroptera.php">蝙蝠陣営</a>を占った場合は「蝙蝠」と判定される。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sex">性別関連能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#psycho_mage">精神鑑定士</a>の話を出したときの流石鯖の管理人さんのコメントが
きっかけで生まれた役職です。
</pre>

<h3><a id="stargazer_mage">占星術師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β12～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：無効</h4>
<pre>
夜の投票能力の有無を判別する特殊な占い師。
占った夜に、何かしらの投票をした人と<a href="wolf.php#wolf_group">人狼系</a>は「投票能力を持っている」、
それ以外は「投票能力を持っていない」と判定される。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#border_priest">境界師</a>・<a href="#clairvoyance_scanner">猩々</a>・<a href="fox.php#stargazer_fox">星狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project の宇佐見 蓮子がモチーフです。
<a href="#psycho_mage">精神鑑定士</a>とは違うアプローチで騙りを見抜くことができます。
初日とそれ以降で判定結果が異なる役職が存在することに注意してください。
</pre>

<h3><a id="voodoo_killer">陰陽師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α20～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：特殊 / 月兎：無効 / 呪い：解呪</h4>
<pre>
対呪い専門の特殊な占い師。
占った人が<a href="ability.php#cursed">呪い所持者</a>や<a href="ability.php#possessed">憑依能力者</a>の場合は呪殺し(死亡メッセージは呪返しと同じ)、
誰かに呪いをかけられていた場合は解呪(呪返しが発動しない)する。
呪殺か解呪が成功した場合のみ、次の日に専用のシステムメッセージが表示される。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#anti_voodoo">厄神</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
呪い系統の対抗役職です。
積極的に<a href="ability.php#cursed">呪い所持者</a>や<a href="ability.php#possessed">憑依能力者</a>を探しに行く場合は
普通の占い師と同じ感覚でいいですが、<a href="ability.php#voodoo">呪術能力者</a>による呪返しを
防ぐのが狙いなら、同時に同じ人を占う必要があるので動き方が難しくなります。
そもそも呪い系がレアなので役に立つのか分かりませんが……
</pre>


<h3><a id="dummy_mage">夢見人</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α14～]</h3>
<h4>[役職表示] <a href="#mage">占い師</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 獏襲撃：死亡</h4>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：無効 / 呪い：無効</h4>
<pre>
「村人」と「人狼」が逆に判定される占い師。本人表記は「占い師」。
呪殺できない代わりに呪返しも受けない。
「村人」「人狼」以外の判定 (<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>など) は正しい結果が表示される。
<a href="ability.php#phantom">占い妨害能力</a>の影響を受けない。
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
「村人」「人狼」以外の判定 (<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>など) は正しい結果が表示される。
</pre>
<h5>Ver. 1.4.0 α18～</h5>
<pre>
占い結果がランダムから「村人」⇔「人狼」反転に変わりました。
確定白(例えば共有者)を占って人狼判定が出たら本人視点夢見人確定です。
また、<a href="#psycho_mage">精神鑑定士</a>から「嘘つき」判定を受けても同様です。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#dummy_necromancer">夢枕人</a>・<a href="ability.php#dummy">夢能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/game/48159/1243197597/17" target="_top">新役職提案スレッド＠やる夫</a> の 17 が原型です。
完全ランダムでは占い結果が全く役に立たなくなるので白黒反転に変更しました。
</pre>


<h2><a id="necromancer_group">霊能者系</a></h2>
<p>
<a href="#necromancer_rule">基本ルール</a>
</p>
<p>
<a href="#necromancer">霊能者</a>
<a href="#soul_necromancer">雲外鏡</a>
<a href="#attempt_necromancer">蟲姫</a>
<a href="#yama_necromancer">閻魔</a>
<a href="#dummy_necromancer">夢枕人</a>
</p>

<h3><a id="necromancer_rule">基本ルール</a> [霊能]</h3>
<pre>
村人陣営が勝利する為には吊らないといけない人外なのに、占いでは人外判定を出せないか
何らかの妨害を受ける役職は霊能で分かります (例：<a href="wolf.php#boss_wolf">白狼</a>・<a href="wolf.php#phantom_wolf">幻狼</a>・<a href="fox.php#cursed_fox">天狐</a>・<a href="fox.php#child_fox">子狐</a>)。
詳細は個々の役職の霊能結果を確認してください。
</pre>

<h3><a id="necromancer">霊能者</a> (占い結果：村人 / 霊能結果：村人)</h3>
<h4>[霊能能力] 処刑者情報：有り / 火車：有効</h4>
<pre>
霊能者系の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#dummy_necromancer">夢枕人</a>
</pre>

<h3><a id="soul_necromancer">雲外鏡</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[霊能能力] 処刑者情報：有り / 火車：有効</h4>
<pre>
処刑した人の役職が分かる上位霊能者。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#soul_mage">魂の占い師</a>の霊能者バージョンです。
占いと違ってメリットが少ないので後回しにしていましたが<a href="#dummy_necromancer">夢枕人</a>とセットで出すことで
こっちは本人視点、判定に偽りが絶対に無いというアドバンテージが与えられます。
しかし、「死人に口無し」故に<a href="#soul_mage">魂の占い師</a>よりもはるかに騙りやすいですね。
</pre>

<h3><a id="attempt_necromancer">蟲姫</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β20～]</h3>
<h4>[霊能能力] 処刑者情報：無し / 火車：無効</h4>
<pre>
前日の<a href="wolf.php#wolf_group">人狼系</a>・<a href="ability.php#assassin">暗殺能力者</a>に狙われて生き残った人と<a href="ability.php#revive">蘇生</a>に失敗した人が分かる特殊な霊能者。
通常の<a href="#necromancer">霊能者</a>の能力は持っていない。
</pre>
<ol>
<li>生き残った人がいた場合は、霊能結果に「～さんは命を狙われたようです」と表示される。</li>
<li>生き残っていれば理由は問わない (以下、生存例)。
  <ol>
    <li><a href="#guard_group">狩人</a>の護衛</a></li>
    <li><a href="wolf.php#silver_wolf">銀狼</a>などの人狼同士の襲撃</li>
    <li><a href="#doll_master">人形遣い</a>・<a href="fox.php">妖狐</a>・<a href="ogre.php">鬼</a>などの耐性持ちだった</li>
    <li>襲撃者が<a href="wolf.php#sex_wolf">雛狼</a>だった</li>
    <li><a href="#assassin">暗殺者</a>が人狼に殺された</li>
    <li><a href="#assassin_spec">暗殺反射</a></li>
    <li><a href="sub_role.php#death_warrant">死の宣告</a>など、即死しない能力だった</li>
    <li><a href="ogre.php">鬼</a>の確率などによる人攫い失敗</li>
  </ol>
</li>
<li>死亡した直後に蘇生した場合でも「生き残った」と判定される。</li>
<li>蘇生失敗メッセージは<a href="#yama_necromancer">閻魔</a>と同様、画面の下に表示される (霊界で見えるものと同じ)。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#clairvoyance_scanner">猩々</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
一部の「生と死の未遂」を感知できる特殊霊能者で、「虫の知らせ」がモチーフです。
「誰が狙われたか」という情報を元に前日の夜に何が起きたか推測できます。
下界にいながら蘇生能力者の存在を感知できるので「蘇生失敗騙り」を看破できます。
また、「暗殺未遂」情報から「<a href="sub_role.php#death_warrant">死の宣告</a>騙り」を看破することも可能になります。
</pre>

<h3><a id="yama_necromancer">閻魔</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α20～]</h3>
<h4>[霊能能力] 処刑者情報：無し / 火車：無効</h4>
<pre>
前日の死者の<a href="../spec.php#dead">死因</a>が分かる特殊な霊能者。
通常の<a href="#necromancer">霊能者</a>の能力は持っていない。
死因は画面の下に表示される「～は無残な～」の下の行に
「(～は人狼に襲撃されたようです)」等と表示される。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="../spec.php#dead">死因</a>が多岐にわたる闇鍋用の特殊霊能者です。
死因が分かるだけなので昼の毒巻き込まれや暗殺された人等、
死者の役職が分からない可能性もある点に注意してください。
</pre>

<h3><a id="dummy_necromancer">夢枕人</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[役職表示] <a href="#necromancer">霊能者</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 獏襲撃：死亡</h4>
<h4>[霊能能力] 処刑者情報：有り / 火車：無効</h4>
<pre>
「村人」と「人狼」が逆に判定される霊能者。本人表記は「霊能者」。
「村人」と「人狼」以外の判定 (例：<a href="wolf.php#boss_wolf">白狼</a>・<a href="fox.php#white_fox">白狐</a>・<a href="fox.php#child_fox">子狐</a>など) は正しい結果が表示される。
<a href="wolf.php#corpse_courier_mad">火車</a>の能力の影響を受けない。
</pre>
<h5>Ver. 1.4.0 α21～</h5>
<pre>
<a href="wolf.php#corpse_courier_mad">火車</a>の能力の影響を受けない。
</pre>
<h5>Ver. 1.4.0 α18～</h5>
<pre>
霊能結果がランダムから「村人」⇔「人狼」反転に変わりました。
<a href="#psycho_mage">精神鑑定士</a>から「嘘つき」判定を受けたら本人視点夢枕人確定です。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dummy_mage">夢見人</a>の霊能者バージョンです。
完全ランダムでは霊能結果が全く役に立たなくなるので白黒反転に変更しました。
</pre>

<h2><a id="medium_group">巫女系</a></h2>
<p>
<a href="#medium_rule">所属陣営判定法則</a>
</p>
<p>
<a href="#medium">巫女</a>
<a href="#bacchus_medium">神主</a>
<a href="#seal_medium">封印師</a>
<a href="#revive_medium">風祝</a>
</p>

<h3><a id="medium_rule">所属陣営判定法則</a></h3>
<pre>
所属陣営とは、勝敗が決まったときの陣営で、役職表記の先頭に記載されいてる「～陣営」を指す。
例1) <a href="wolf.php#wolf_group">人狼系</a>・<a href="wolf.php#mad_group">狂人系</a>は「人狼」
例2) <a href="fox.php#fox_group">妖狐系</a>・<a href="fox.php#child_fox_group">子狐系</a>は「妖狐」
例3) <a href="lovers.php#cupid_group">キューピッド系</a>・<a href="lovers.php#angel_group">天使系</a>は「恋人」
例4) <a href="chiroptera.php#chiroptera_group">蝙蝠系</a>・<a href="chiroptera.php#fairy_group">妖精系</a>は「蝙蝠」

また、メイン役職のみが判定の対象 (サブ役職は分からない)。
つまり、恋人はサブ役職なので「恋人」と判定されるのは<a href="lovers.php#cupid_group">キューピッド系</a>・<a href="lovers.php#angel_group">天使系</a>のみ。
</pre>

<h3><a id="medium">巫女</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α3-7～]</h3>
<pre>
突然死した人の所属陣営が分かる、霊能のような役職。
闇鍋モードで登場する「ショック死」する人たちの情報を取るのが主な仕事だが
<a href="#necromancer_group">霊能者</a>とは<a href="#medium_rule">判定法則</a>が違うので注意。
</pre>
<h5>Ver. 1.4.0 β6～</h5>
<pre>
<a href="mania.php#unknown_mania">鵺</a>の所属陣営が正しく出ないバグ修正 (修正前は常時村人判定)
</pre>
<h5>Ver. 1.4.0 α9～</h5>
<pre>
<a href="sub_role.php#lovers">恋人</a>後追いにも対応 (後追いした<a href="sub_role.php#lovers">恋人</a>のみ、元の所属陣営が分かる)
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
式神研のオリジナル役職です。
闇鍋モードで大量の突然死が出ることになったので作ってみましたが
霊能者より地味な存在ですね。騙るのも容易なのでなかなか報われないかもしれません。
</pre>

<h3><a id="bacchus_medium">神主</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β21～]</h3>
<pre>
処刑投票先が<a href="ogre.php">鬼陣営</a>だった場合にショック死させることができる上位巫女。
</pre>
<ol>
  <li>処刑先が決定されて、投票先が処刑者ではない場合に発動します。</li>
  <li>判定時に対象が死亡していた場合は無効になります。</li>
  <li>自分が処刑された場合は無効になります。</li>
  <li>毒やショック死で死亡した場合は有効です。</li>
  <li>鬼陣営をコピーした<a href="mania.php#unknown_mania_group">鵺系</a>・変化前の<a href="mania.php#soul_mania">覚醒者</a>・<a href="mania.php#dummy_mania">夢語部</a>は対象外です。</li>
  <li>ショック死させた場合の死因は「神主に酔い潰された」で<a href="#cure_pharmacist">河童</a>の能力発動対象外です。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#reflect_guard">侍</a>・<a href="ability.php#sudden_death">ショック死発動能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project の神主がモチーフの、対<a href="ogre.php">鬼陣営</a>能力者です。
伝承上の鬼を退治するアイテムの一つであるお酒と掛け合わせてみました。
この役職でメイン役職の総数が既存のものと合わせてちょうど 200 になりました。
</pre>

<h3><a id="seal_medium">封印師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<pre>
処刑投票先が回数限定の能力を持っている人外の場合に封じることができる上位巫女。
</pre>
<ol>
  <li>対象は<a href="wolf.php#phantom_wolf">幻狼</a>・<a href="wolf.php#resist_wolf">抗毒狼</a>・<a href="wolf.php#tongue_wolf">舌禍狼</a>・<a href="wolf.php#trap_mad">罠師</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#phantom_fox">幻狐</a>・<a href="fox.php#emerald_fox">翠狐</a>・<a href="fox.php#revive_fox">仙狐</a>・<a href="fox.php#possessed_fox">憑狐</a>です。</li>
  <li>処刑先が決定されて、投票先が処刑者ではない場合に発動します。</li>
  <li>判定時に対象が死亡していた場合は無効になります。</li>
  <li>自分が処刑された場合は無効になります。</li>
  <li>毒やショック死で死亡した場合は有効です。</li>
  <li>投票先がすでに能力を失っている状態 (<a href="sub_role.php#lost_ability">能力喪失</a>) であればショック死させます。</li>
  <li>ショック死させた場合の死因は「封印された」で<a href="#cure_pharmacist">河童</a>の能力発動対象外です。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sudden_death">ショック死発動能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project の博麗 霊夢のスペルカード「夢想封印」がモチーフです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/626" target="_top">新役職考案スレ</a> の 626 が原型です。
一部の特殊人外にとっては非常に危険な存在となります。
</pre>

<h3><a id="revive_medium">風祝</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[耐性] 蘇生：不可</h4>
<h4>[蘇生能力] 成功率：25% / 誤爆：有り</h4>
<pre>
<a href="#poison_cat">猫又</a>相当の蘇生能力を持った上位巫女。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project の東風谷 早苗がモチーフです。「かぜはふり」と読みます。
「奇跡」を蘇生能力に読み替えてみました。
</pre>

<h2><a id="priest_group">司祭系</a></h2>
<p>
<a href="#priest_rule">基本ルール</a>
<a href="#crisis_priest_rule">人外勝利前日判定ルール</a>
</p>
<p>
<a href="#priest">司祭</a>
<a href="#bishop_priest">司教</a>
<a href="#dowser_priest">探知師</a>
<a href="#high_priest">大司祭</a>
<a href="#crisis_priest">預言者</a>
<a href="#revive_priest">天人</a>
<a href="#border_priest">境界師</a>
<a href="#dummy_priest">夢司祭</a>
</p>

<h3><a id="priest_rule">基本ルール</a> [司祭]</h3>
<ol>
  <li>判定結果は夜も表示されたままですが、昼の処刑結果は反映されていません。</li>
  <li><a href="#crisis_priest">預言者</a>・<a href="#revive_priest">天人</a>以外は狩人の<a href="#guard_limit">護衛制限</a>対象です。</li>
</ol>

<h3><a id="crisis_priest_rule">人外勝利前日判定ルール</a></h3>
<pre>
1. 生存者 - (人狼 + 妖狐) &lt;= 人狼 + 2
その日の処刑が人狼以外 + 夜に人狼の襲撃が成立すると人狼勝利となります。
メッセージは「人狼が勝利目前」です。

2. 「条件1 が成立している」または「人狼が残り一人」 + 妖狐 / 恋人が生存している
妖狐が生存していれば「妖狐が勝利目前」、
恋人が生存していれば「恋人が勝利目前」と判定されます

3. 生存者 &lt;= 恋人 + 2
生存者が全員恋人になると恋人勝利となります。
メッセージは「恋人が勝利目前」です。
</pre>

<h3><a id="priest">司祭</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α24～]</h3>
<h4>[耐性] 護衛：制限対象</h4>
<pre>
一定日数ごとに現在、生存している村人陣営の人数が分かる。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<ol>
  <li>判定が出るのは 4 日目以降の偶数日 (4 → 6 → 8 →...)。</li>
  <li>村人陣営の判定法則は<a href="#medium">巫女</a>と同じ。</li>
  <li><a href="#revive_priest">天人</a>の蘇生判定は司祭判定の後に行なわれます。<br>
    従って、「司祭の判定 + <a href="#revive_priest">天人</a>が蘇生した人数」 が司祭視点の正しい値です。
  </li>
</ol>
<h4>同一表示役職</h4>
<pre>
<a href="#dummy_priest">夢司祭</a>・<a href="#priest_jealousy">恋司祭</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他国に実在する役職で、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/72" target="_top">新役職考案スレ</a> の 72 が原型です。
オリジナルは配役非通知設定の闇鍋用役職なので、能力を発動した時点で生存している
役職の内訳が完全に分かりますが、式神研バージョンはかなり情報が絞られています。
</pre>

<h3><a id="bishop_priest">司教</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<h4>[耐性] 護衛：制限対象</h4>
<pre>
一定日数ごとに現在、死亡している村人陣営以外の人数が分かる、特殊な司祭。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<ol>
  <li>判定が出るのは 3 日目以降の奇数日 (3 → 5 → 7 →...)。</li>
  <li><a href="#medium">巫女</a>の判定と違い、恋人も「村人陣営」以外と判定される。</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#priest">司祭</a>の亜種で、こちらは死者の内訳を推測することができます。
特に、3 日目の情報は身代わり君の所属陣営を絞り込むのに有効です。
</pre>

<h3><a id="dowser_priest">探知師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β15～]</h3>
<h4>[耐性] 護衛：制限対象</h4>
<pre>
一定日数ごとに現在の生存者が所持している<a href="sub_role.php">サブ役職</a>の合計が分かる、特殊な司祭。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<ol>
  <li>判定が出るのは 4 日目以降の偶数日 (4 → 6 → 8 →...)。</li>
  <li>本人には表示されていないサブ役職もカウントされます (例：<a href="sub_role.php#decide">決定者</a>)。</li>
  <li>複数所持できるサブ役職はまとめて一つとカウントされます (例：<a href="sub_role.php#lovers">恋人</a>)。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ogre.php#dowser_yaksa">毘沙門天</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のナズーリンがモチーフです。
<a href="lovers.php">恋人</a>の種類の特定、<a href="ability.php#possessed">憑依能力者</a>・<a href="vampire.php">吸血鬼陣営</a>の行動状況の把握に威力を発揮します。
</pre>

<h3><a id="high_priest">大司祭</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β21～]</h3>
<h4>[耐性] 護衛：制限対象</h4>
<pre>
5 日目以降、<a href="#priest">司祭</a>・<a href="#bishop_priest">司教</a>両方の結果が分かる上位司祭。
奇数日に<a href="#bishop_priest">司教</a>、偶数日に<a href="#priest">司祭</a>の結果が表示される。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
発動は遅いですが、情報量が多いので多数の役職を一人で兼ねることができます。
<a href="#guard_limit">護衛制限</a>がかかっているので、生き残るのが難しい役職ですね。
</pre>

<h3><a id="crisis_priest">預言者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β2～]</h3>
<h4>[役職表示] <a href="#human">村人</a></h4>
<pre>
<a href="#crisis_priest_rule">人外勝利前日</a>を判定する特殊な司祭。表示は「村人」。
条件を満たした場合のみ、どの陣営が有利なのかメッセージが表示される。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
村の危機を告げる特殊な司祭です。
いわゆる「鉄火場」は狂人や蝙蝠の存在 + 恋人の元の役職によって
機械的な判定ができないので判定条件を「システム的な勝敗決定前日」としました。
</pre>

<h3><a id="revive_priest">天人</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β2～]</h3>
<h4>[耐性] 蘇生：不可 / 憑依：無効</h4>
<pre>
2 日目の朝にいきなり死亡して、特定の条件を満たすと生き返る特殊な司祭。
「霊界で配役を公開しない」オプションが有効になっていないと死亡も蘇生もしません。
</pre>
<h4>蘇生条件 (どれか一つを満たせば蘇生する)</h4>
<ol>
  <li>「<a href="#crisis_priest_rule">人外勝利前日</a>」である。</li>
  <li>5 日目である。</li>
  <li>村の人口が半分以下になった。</li>
  <li>生存している人狼が一人になった。</li>
</ol>
<h4>詳細な仕様</h4>
<ol>
  <li>2 日目の朝の死亡メッセージは「～は無惨な～」で、死因は「天に帰った」です。</li>
  <li>一度蘇生すると能力を失います (<a href="sub_role.php#lost_ability">能力喪失</a>)。</li>
  <li>恋人になると能力を失います (2 日目朝の死亡も起こりません)。</li>
  <li><a href="mania.php#mania_group">神話マニア</a>がコピーした場合は 2 日目朝の死亡処理は実行されません。</li>
  <li>2 日目朝以降に死んでも蘇生判定を満たせば生き返ります。</li>
  <li>5 日目になると能力を失います。</li>
  <li>蘇生対象外です (選ばれた場合は失敗する)。</li>
  <li><a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外です。</li>
</ol>
<h5>Ver. 1.4.0 β12～</h5>
<pre>
<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外です (<a href="wolf.php#possessed_wolf">憑狼</a>と揃えました)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/54" target="_top">新役職考案スレ</a> の 54 が原型です。
復活した天人は恋人でない事が保証されるので非常に頼りになります。
</pre>

<h3><a id="border_priest">境界師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β12～]</h3>
<h4>[耐性] 護衛：制限対象</h4>
<pre>
2日目以降、「夜、自分に何らかの投票をした人の数」が分かる、特殊な司祭。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<ol>
  <li>能力の発動は 2 日目からなので、判定結果が出るのは 3 日目の昼からになります。</li>
  <li>発動していない投票もカウントされます。<br>
    例) 人狼に噛み殺された占い師の投票もカウントされる。
  </li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#stargazer_mage">占星術師</a>・<a href="#clairvoyance_scanner">猩々</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のマエリベリー・ハーンがモチーフです。
例えば、自分を「占った」人が本物かどうかを推測することができます。
</pre>

<h3><a id="dummy_priest">夢司祭</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β15～]</h3>
<h4>[役職表示] <a href="#priest">司祭</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 護衛：制限対象 / 獏襲撃：死亡</h4>
<pre>
一定日数ごとに現在、生存している<a href="ability.php#dummy">夢系能力者</a>と<a href="chiroptera.php#fairy_group">妖精系</a>の人数が分かる特殊な司祭。
本人表記は「<a href="#priest">司祭</a>」で能力の発動日などの仕様も同じ。
狩人の護衛制限対象。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dummy_mage">夢見人</a>の司祭バージョンです。
<a href="#priest">司祭</a>と判定法則が全然違うので比較的自覚は容易な部類に入ります。
<a href="ability.php#dummy">夢系</a>騙りを看破するヒントになりますが、逆に司祭騙りの難易度が下がることになります。
</pre>


<h2><a id="guard_group">狩人系</a></h2>
<p>
<a href="#guard_limit">護衛制限</a>
<a href="#guard_hunt">狩りルール</a>
</p>
<p>
<a href="#guard">狩人</a>
<a href="#hunter_guard">猟師</a>
<a href="#blind_guard">夜雀</a>
<a href="#reflect_guard">侍</a>
<a href="#poison_guard">騎士</a>
<a href="#fend_guard">忍者</a>
<a href="#reporter">ブン屋</a>
<a href="#anti_voodoo">厄神</a>
<a href="#dummy_guard">夢守人</a>
</p>

<h3><a id="guard_limit">護衛制限</a></h3>
<ol>
  <li>制限対象は、<a href="#priest_group">司祭系</a> (<a href="#crisis_priest">預言者</a>・<a href="#revive_priest">天人</a>を除く)・<a href="#reporter">ブン屋</a>・<a href="#detective_common">探偵</a>・<a href="#assassin_group">暗殺者系</a>・<a href="#clairvoyance_scanner">猩々</a>・<a href="#priest_jealousy">恋司祭</a>・<a href="#doll_master">人形遣い</a>です</li>
  <li>対象を護衛して襲撃された場合、狩人に「護衛成功」のメッセージは出ますが、護衛先は噛み殺されます</li>
  <li><a href="#blind_guard">夜雀</a>・<a href="#poison_guard">騎士</a>には適用されません</li>
</ol>

<h3><a id="guard_hunt">狩りルール</a></h3>
<pre>
1. 狩り能力があるのは<a href="#guard">狩人</a>・<a href="#hunter_guard">猟師</a>・<a href="#reflect_guard">侍</a>・<a href="#poison_guard">騎士</a>・<a href="#fend_guard">忍者</a>です
2. 対象は特殊狂人・特殊妖狐・特殊吸血鬼・特殊蝙蝠です
2-1. 特殊狂人 (<a href="wolf.php#mad">狂人</a>・<a href="wolf.php#fanatic_mad">狂信者</a>・<a href="wolf.php#whisper_mad">囁き狂人</a>以外の<a href="wolf.php#mad_group">狂人系</a>)
2-2. 特殊妖狐 (<a href="fox.php#phantom_fox">幻狐</a>・<a href="fox.php#voodoo_fox">九尾</a>・<a href="fox.php#revive_fox">仙狐</a>・<a href="fox.php#possessed_fox">憑狐</a>・<a href="fox.php#doom_fox">冥狐</a>・<a href="fox.php#cursed_fox">天狐</a>)
2-3. 特殊吸血鬼 (<a href="vampire.php#vampire">吸血鬼</a>以外の<a href="vampire.php#vampire_group">吸血鬼系</a>)
2-4. 特殊蝙蝠 (<a href="chiroptera.php#poison_chiroptera">毒蝙蝠</a>・<a href="chiroptera.php#cursed_chiroptera">呪蝙蝠</a>・<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>)
3. <a href="#hunter_guard">猟師</a>はさらに<a href="fox.php">妖狐陣営</a>を狩ることができます
4. <a href="#reflect_guard">侍</a>はさらに<a href="ogre.php">鬼陣営</a>を狩ることができます
5. <a href="#dummy_guard">夢守人</a>は<a href="chiroptera.php#fairy_group">妖精系</a>を狩ることができます
6. <a href="wolf.php#dream_eater_mad">獏</a>と<a href="#dummy_guard">夢守人</a>の関係は<a href="wolf.php#dream_eater_mad">獏</a>を参照してください
7. 対象が身代わり死していた場合は狩りが発生しません (<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>など)
</pre>
<h5>Ver. 1.4.0 β14～</h5>
<pre>
対象が身代わり死していた場合は狩りが発生しません (<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>など)
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
<a href="#dummy_guard">夢守人</a>は<a href="chiroptera.php#fairy_group">妖精系</a>を狩ることができます
</pre>

<h3><a id="guard">狩人</a> (占い結果：村人 / 霊能結果：村人)</h3>
<h4>[狩人能力] 護衛：制限有り / 狩り：有り / 罠：有効</h4>
<pre>
狩人系の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#dummy_guard">夢守人</a>
</pre>

<h3><a id="hunter_guard">猟師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[狩人能力] 護衛：制限有り / 狩り：有り / 罠：有効</h4>
<pre>
狩り能力に特化した特殊な狩人。
通常の狩り能力 (<a href="#guard_hunt">狩りルール</a>) に加えて、<a href="fox.php">妖狐陣営</a>も狩る事ができる。
護衛先が人狼に襲撃された場合は、狼の種類を問わず本人が死亡する (護衛は成功扱い)。
死因は「人狼の襲撃」となる。
<a href="vampire.php">吸血鬼</a>の襲撃から護衛した場合は死亡しない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
メビウス人狼の守護者がモチーフで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/641" target="_top">新役職考案スレ</a> の 641 が原型です。
村側の対妖狐の切り札としてデザインしてあり、その分だけ対狼能力が犠牲になっています。
</pre>

<h3><a id="blind_guard">夜雀</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β14～]</h3>
<h4>[狩人能力] 護衛：制限無し / 狩り：無し / 罠：有効</h4>
<pre>
<a href="#guard_hunt">狩り能力</a>は持たないが、護衛先を襲撃した<a href="wolf.php#wolf_group">人狼</a>・<a href="vampire.php">吸血鬼</a>に<a href="sub_role.php#blinder">目隠し</a>を付加する特殊な狩人。
<a href="#guard_limit">護衛制限</a>の影響を受けない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#hunter_guard">猟師</a>とは対照的に、護衛に特化した特殊狩人です。
死体の状況を見ることで<a href="chiroptera.php#dark_fairy">闇妖精</a>と区別することができるので、
襲撃役の<a href="wolf.php#wolf_group">人狼</a>はうっかり<a href="sub_role.php#blinder">目隠し</a> CO しないように気をつけましょう。
</pre>

<h3><a id="reflect_guard">侍</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β22～]</h3>
<h4>[耐性] 暗殺：反射</h4>
<h4>[狩人能力] 護衛：制限有り / 狩り：有り / 罠：有効</h4>
<pre>
<a href="#assassin_spec">暗殺反射</a>能力を持つ上位狩人。
通常の狩り能力 (<a href="#guard_hunt">狩りルール</a>) に加えて、<a href="ogre.php">鬼陣営</a>も狩る事ができる。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#bacchus_medium">神主</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="ogre.php">鬼陣営</a>キラーの上位狩人で、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/l50" target="_top">新役職考案スレ</a> の <a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/838" target="_top">838</a>・<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/875" target="_top">875</a> が原型です。
</pre>

<h3><a id="poison_guard">騎士</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α3-7～]</h3>
<h4>[狩人能力] 護衛：制限無し / 狩り：有り / 罠：有効</h4>
<h4>[毒能力] 処刑：無し / 襲撃：有り / 薬師判定：限定的</h4>
<pre>
噛まれた時のみ毒が発動する上位狩人。
<a href="#guard_limit">護衛制限</a>の影響を受けない。
狩り能力は<a href="#guard_hunt">狩りルール</a>を参照。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんがモデルです。
技術的に簡単だったので軽い気持ちで作ってみましたがとんでもなく強いようです。
<a href="wolf.php#resist_wolf">抗毒狼</a>や<a href="wolf.php#trap_mad">罠師</a>に注意しましょう。
</pre>

<h3><a id="fend_guard">忍者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β5～]</h3>
<h4>[耐性] 人狼襲撃：無効 (1回限定)</h4>
<h4>[狩人能力] 護衛：制限有り / 狩り：有り / 罠：有効</h4>
<pre>
一度だけ<a href="wolf.php#wolf_group">人狼系</a> (種類を問わない) の襲撃に耐えることができる上位狩人。
人狼に襲撃されると耐性を失う (<a href="sub_role.php#lost_ability">能力喪失</a>)。
誰に襲撃されたのかは分からないが、耐性を失っても護衛は通常通り行える。
狩り能力は<a href="#guard_hunt">狩りルール</a>を参照。
身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合は能力無効。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方陰陽鉄のプレイヤーさんの誕生日記念に作ってみました。
狼視点では狐と区別がつかないので、ある意味で<a href="#poison_guard">騎士</a>以上に
やっかいな存在になるでしょう。
</pre>

<h3><a id="reporter">ブン屋</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α14～]</h3>
<h4>[耐性] 護衛：制限対象 / 遺言：不可</h4>
<h4>[狩人能力] 護衛：特殊 / 狩り：無し / 罠：有効</h4>
<pre>
夜に投票(尾行)した人が人狼に襲撃された場合に、誰が襲撃したか分かる特殊な狩人。
狩人の<a href="#guard_limit">護衛制限</a>対象です。
</pre>
<ol>
  <li>人狼・妖狐を尾行したら殺されます (死亡メッセージは「無惨な～」、死因は「人外尾行」)</li>
  <li>遺言を残せません</li>
  <li>尾行対象者が狩人に護衛されていた場合は何も出ません</li>
  <li>人狼が人外(狐や<a href="wolf.php#silver_wolf">銀狼</a>など)を襲撃して失敗した場合は尾行成功扱いとなります (殺されません)<br>
    (尾行成功メッセージ＆対象が死んでいない＝狼が噛めない人外を噛んだ)</li>
</ol>
</pre>
<h4>関連役職</h4>
<pre>
<a href="#escaper">逃亡者</a>・<a href="#presage_scanner">件</a>・<a href="#clairvoyance_scanner">猩々</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/5" target="_top">新役職考案スレ</a> の 5 が原型です。
活躍するのは非常に難しいですが、成功したときには大きなリターンがあります。
狐噛みの現場をスクープするブン屋を是非見てみたいものです。
</pre>

<h3><a id="anti_voodoo">厄神</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α20～]</h3>
<h4>[耐性] 憑依：特殊</h4>
<h4>[狩人能力] 護衛：特殊 / 狩り：無し / 罠：無効</h4>
<pre>
護衛した人の厄 (占い妨害・呪返し・憑依) を祓う特殊な狩人。
成功した場合は次の日に専用のシステムメッセージが表示される。
護衛先の役職に関係なく発動するので、例えば占い妨害を受けた村人を
護衛した場合でも成功と判定される。
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
憑依中の<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>を直接護衛すると憑依状態を解く事ができる。
</pre>
<h5>Ver. 1.4.0 α24～</h5>
<pre>
憑依中の<a href="wolf.php#possessed_wolf">憑狼</a>に対しては圧倒的なアドバンテージを持っており、
直接護衛するか、襲撃されると憑依状態を解くことができる。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
対占い妨害・呪い専門の狩人です。「やくじん」と読みます。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/43" target="_top">新役職考案スレ</a> の 43 が原型です。
厄神の護衛を受けることで<a href="wolf.php#cursed_wolf">呪狼</a>に狼判定を出したり、
<a href="fox.php#cursed_fox">天狐</a>を呪殺することが可能になります。
通常の狩人が狂人・妖狐でも護衛成功してしまうのと同様に
狂人や妖狐にも占い妨害や呪返しを受ける役職がいるので、
「厄払い成功＝対象者は村陣営」とは限らない点に注意してください。
</pre>

<h3><a id="dummy_guard">夢守人</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[役職表示] <a href="#guard">狩人</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 獏襲撃：狩り</h4>
<h4>[狩人能力] 護衛：特殊 / 狩り：特殊 / 罠：無効</h4>
<pre>
本人には「狩人」と表示されており、護衛行動を取ることができる。
必ず護衛成功メッセージが表示されるが、表示されるだけで誰も護衛していない。
<a href="wolf.php#dream_eater_mad">獏</a>には圧倒的なアドバンテージを持っており、何らかの形で遭遇すると
一方的に狩ることができる。
<a href="chiroptera.php#fairy_group">妖精系</a>を護衛すると狩ることができる。
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
<a href="chiroptera.php#fairy_group">妖精系</a>を護衛すると狩ることができる
</pre>
<h5>Ver. 1.4.0 α21～</h5>
<pre>
<a href="wolf.php#dream_eater_mad">獏</a>には圧倒的なアドバンテージを持っており、何らかの形で遭遇すると
一方的に狩ることができる。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dummy_mage">夢見人</a>の狩人バージョンです。
常に護衛成功メッセージが出るので一度でも失敗したら夢守人で無い事を確認できます。
みなさん一度くらいは全て護衛成功して勝利してみたいと思った事、ありませんか？
</pre>


<h2><a id="common_group">共有者系</a></h2>
<p>
<a href="#common">共有者</a>
<a href="#detective_common">探偵</a>
<a href="#trap_common">策士</a>
<a href="#ghost_common">亡霊嬢</a>
<a href="#dummy_common">夢共有者</a>
</p>

<h3><a id="common">共有者</a> (占い結果：村人 / 霊能結果：村人)</h3>
<pre>
共有者系の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#dummy_common">夢共有者</a>
</pre>

<h3><a id="detective_common">探偵</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<h4>[耐性] 護衛：制限対象 / 毒：対象外 / 蘇生：不可 / 暗殺：反射 / 憑依：無効</h4>
<pre>
様々な特殊耐性を持つ、上位共有者。
狩人の<a href="#guard_limit">護衛制限</a>対象です。
</pre>
<ol>
  <li>毒・<a href="#doom_doll">蓬莱人形</a>・<a href="#brownie">座敷童子</a>・<a href="#cursed_brownie">祟神</a>・<a href="fox.php#miasma_fox">蟲狐</a>の能力の対象外</li>
  <li><a href="#assassin_spec">暗殺反射</a></li>
  <li><a href="wolf.php#miasma_mad">土蜘蛛</a>の能力無効</li>
  <li><a href="vampire.php#vampire_do_spec">吸血死</a>無効</li>
  <li><a href="#about_revive">蘇生</a>不可</li>
  <li><a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外</li>
</ol>
<h5>Ver. 1.4.0 β12～</h5>
<pre>
<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外 (<a href="wolf.php#possessed_wolf">憑狼</a>と揃えました)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
闇鍋モードでも探偵村を実施できるようにチューニングした上位共有者です。
</pre>

<h3><a id="trap_common">策士</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<pre>
昼の投票時に、その時点で生存している村人陣営以外の人全てから投票されたら
まとめて死亡させる上位共有者。
</pre>
<ol>
  <li><a href="#medium">巫女</a>の判定と違い、恋人も「村人陣営」以外と判定される</li>
  <li>一人でも村人陣営の人から投票されると無効</li>
  <li>発動した場合に巻き込んだ人の死因は「罠」</li>
  <li>自分が吊られたり、再投票になっても有効</li>
  <li>本人が恋人になった場合は自分自身が「非村人陣営」になるので発動できない</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
圧倒的不利な状況をたった一回の投票でひっくり返す、究極の対 PP 兵器です。
<a href="#jealousy">橋姫</a>同様、役職の存在による抑止力の発生が主眼です。

「自分は村人だから投票しない」と主張されたらそれまでなので、能力を前面に出して
自分に投票させる作戦は通用しないでしょう。
</pre>

<h3><a id="ghost_common">亡霊嬢</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 小心者</h4>
<pre>
自分を襲撃した<a href="wolf.php#wolf_group">人狼</a>に<a href="sub_role.php#chicken">小心者</a>を付加する上位共有者。
<a href="wolf.php#possessed_wolf_sub_role">憑狼</a>は本体に付加される。
身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合は能力無効。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんがモデルです。
即死こそしませんが、<a href="wolf.php#resist_wolf">抗毒狼</a>でも無効化できないので非常に強力です。
<a href="#trap_common">策士</a>同様、人狼陣営にとっては役職の存在自体が脅威になるでしょう。
</pre>

<h3><a id="dummy_common">夢共有者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[役職表示] <a href="#common">共有者</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 獏襲撃：死亡</h4>
<pre>
本人には「『相方が身代わり君』の共有者」と表示されている村人。
が、夜に発言しても「ひそひそ声」にはならないし、本物の共有者の声も聞こえない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dummy_mage">夢見人</a>の共有者バージョンです。
「ひそひそ声」が発生しないので真共有者にはなれません。
(闇鍋モードであっても「ひそひそ声」は消しません。仕様です)
証明手段が無いので容易に騙れますね。きっと真でも吊られることでしょう。
</pre>


<h2><a id="poison_group">埋毒者系</a></h2>
<p>
<a href="#poison">埋毒者</a>
<a href="#strong_poison">強毒者</a>
<a href="#incubate_poison">潜毒者</a>
<a href="#guide_poison">誘毒者</a>
<a href="#chain_poison">連毒者</a>
<a href="#dummy_poison">夢毒者</a>
</p>

<h3><a id="poison">埋毒者</a> (占い結果：村人 / 霊能結果：村人)</h3>
<h4>[毒能力] 処刑：有り / 襲撃：有り / 薬師判定：有り</h4>
<pre>
埋毒者系の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#strong_poison">強毒者</a>・<a href="#dummy_poison">夢毒者</a>・<a href="#poison_jealousy">毒橋姫</a>
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#poison">毒能力者</a>
</pre>

<h3><a id="strong_poison">強毒者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[役職表示] <a href="#poison">埋毒者</a></h4>
<h4>[毒能力] 処刑：人狼系 + 妖狐陣営 / 襲撃：有り / 薬師判定：強い毒</h4>
<pre>
処刑された時の毒の対象が人外 (<a href="wolf.php#wolf_group">人狼系</a>と<a href="fox.php">妖狐陣営</a>) 限定の上位埋毒者。本人の表記は「埋毒者」。
毒の巻き込み対象設定 (<a href="../script_info.php" target="_top">特徴と仕様</a>参照) が「投票者ランダム」だった場合、
対象者が投票していなければ毒は不発になる。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#incubate_poison">潜毒者</a>・<a href="chiroptera.php#poison_chiroptera">毒蝙蝠</a>・<a href="ogre.php#poison_ogre">榊鬼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんがモデルです。
状況にもよりますが、<a href="#soul_mage">魂の占い師</a>に鑑定してもらったら即吊ってもらうと強いですね。
投票者ランダムの設定であれば、その時の投票結果は重要な推理材料にもなります。
</pre>

<h3><a id="incubate_poison">潜毒者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[毒能力] 処刑：無し → 人狼系 + 妖狐陣営 / 襲撃：無し → 有り / 薬師判定：無し → 強い毒</h4>
<pre>
一定期間後 (5 日目以降) に毒を持つ特殊な埋毒者。
毒を持ったら本人に追加のシステムメッセージが表示される。
毒能力は<a href="#strong_poison">強毒者</a>相当。
</pre>
<h5>Ver. 1.4.0 α20～</h5>
<pre>
毒能力を埋毒者相当から強毒者相当に変更。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#scripter">執筆者</a>・<a href="mania.php#soul_mania">覚醒者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんがモデルです。
いかに毒を持つまで時間を稼ぐかがポイントです
</pre>

<h3><a id="guide_poison">誘毒者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<h4>[毒能力] 処刑：毒能力者 / 襲撃：毒能力者 / 薬師判定：限定的</h4>
<pre>
<a href="ability.php#poison">毒能力者</a>のみに中る特殊な埋毒者。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#pharmacist_group">薬師系</a>と違うアプローチで毒能力を持った人外を仕留めることができますが
毒と名のつく役職全てが対象なので<a href="#poison_guard">騎士</a>・<a href="#chain_poison">連毒者</a>に中ると大惨事になります。
</pre>

<h3><a id="chain_poison">連毒者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[役職表示] <a href="#human">村人</a></h4>
<h4>[毒能力] 処刑：特殊 / 襲撃：無し / 薬師判定：限定的</h4>
<pre>
他の毒能力者に巻き込まれたら、さらに二人巻き込む特殊な埋毒者。本人の表記は「村人」。
解毒能力者 (例：<a href="#pharmacist">薬師</a>・<a href="#cure_pharmacist">河童</a>) に投票されていたら解毒される (連鎖が発生しない)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
裏世界鯖＠東方陰陽鉄人狼のとある村がモデルです。
発動率は低いですが、ひとたび発動すると大惨事を引き起こします。
連毒者を巻き添えにするとさらに連鎖するので一回の吊りで全滅する可能性もあります。
</pre>

<h3><a id="dummy_poison">夢毒者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[役職表示] <a href="#poison">埋毒者</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 獏襲撃：死亡</h4>
<h4>[毒能力] 処刑：特殊 / 襲撃：無し / 薬師判定：無し</h4>
<pre>
本人には「埋毒者」と表示されている村人。
吊られた場合は<a href="wolf.php#dream_eater_mad">獏</a>・<a href="chiroptera.php#fairy_group">妖精系</a>のみ巻き込む (「解毒」はできない)。
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
吊られた場合は<a href="wolf.php#dream_eater_mad">獏</a>・<a href="chiroptera.php#fairy_group">妖精系</a>のみ巻き込む (「解毒」はできない)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dummy_mage">夢見人</a>の埋毒者バージョンです。
毒は持っていませんが、身代わり君がこれになることはありません。
偽者ではありますがどちらかと言うと人狼が不利になる役職ですね。
夢毒者である事に賭けて真埋毒者を噛みに行く狼が出るかもしれません。

Ver. 1.4.0 β9 からは吊られた時のみ<a href="wolf.php#dream_eater_mad">獏</a>・<a href="chiroptera.php#fairy_group">妖精系</a>に中る仕様に変更しました。
夢の世界の攻防なので<a href="#pharmacist_group">薬師系</a>による解毒はできません。
</pre>


<h2><a id="poison_cat_group">猫又系</a></h2>
<p>
<a href="#about_revive">基本ルール</a>
</p>
<p>
<a href="#poison_cat">猫又</a>
<a href="#revive_cat">仙狸</a>
<a href="#sacrifice_cat">猫神</a>
<a href="#eclipse_cat">蝕仙狸</a>
</p>

<h3><a id="about_revive">基本ルール</a> [蘇生]</h3>
<ol>
  <li><a href="../game_option.php#not_open_cast">霊界で配役を公開しない</a>オプションが有効になっていないと蘇生行動はできません</li>
  <li>投票可能になるのは 2日目の夜からで、[蘇生する / しない] を必ず投票する必要があります</li>
  <li>投票できるのは、身代わり君以外の死者です</li>
  <li>「蘇生を行わない」を選ぶこともできます</li>
  <li>蘇生成功率のうち、1/5 は指定した人以外が対象になる「誤爆蘇生」となります<br>
    例) 25% : 成功 : 20% / 誤爆 :  5%</li>
  <li>身代わり君・蘇生能力者 (猫又系・<a href="#revive_priest">天人</a>など)・<a href="sub_role.php#lovers">恋人</a>・<a href="#detective_common">探偵</a>は蘇生できません</li>
  <li>蘇生対象外の人が選ばれた場合は確実に失敗します</li>
  <li>蘇生に失敗した場合は霊界にだけ見えるシステムメッセージが表示されます</li>
</ol>
<h5>Ver. 1.4.0 β2～</h5>
<pre>
恋人を蘇生対象外に変更 (蘇生後、即自殺から変更)
</pre>
<h5>Ver. 1.4.0 α19～</h5>
<pre>
<a href="#poison_cat">猫又</a>を蘇生対象外に変更
</pre>

<h3><a id="poison_cat">猫又</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α18～]</h3>
<h4>[耐性] 蘇生：不可</h4>
<h4>[蘇生能力] 成功率：25% / 誤爆：有り</h4>
<h4>[毒能力] 処刑：有り / 襲撃：有り / 薬師判定：有り</h4>
<pre>
蘇生能力を持った<a href="#poison">埋毒者</a>。蘇生成功率は 25%。
蘇生に関するルールは<a href="#about_revive">基本ルール [蘇生]</a>参照。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#revive_medium">風祝</a>
</pre>
<h5>Ver. 1.4.0 α19～</h5>
<pre>
猫又が蘇生する事はありません。
猫又が蘇生対象者に選ばれた場合は失敗扱いになります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他の国に実在する役職です。
「霊界で配役を公開しない」オプションを有効にしておかないと
ただの埋毒者になる点に注意してください。
</pre>

<h3><a id="revive_cat">仙狸</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β2～]</h3>
<h4>[耐性] 蘇生：不可</h4>
<h4>[蘇生能力] 成功率：80% (初期値) / 誤爆：有り</h4>
<pre>
毒能力を失った代わりに高い蘇生能力を持った猫又の上位種。
蘇生に関するルールは<a href="#about_revive">基本ルール [蘇生]</a>参照。
蘇生成功率は 80% で、成功するたびに成功率が 1/4 になる。
80% → 20% → 5% → 2% → 1% (以後は 1% で固定)
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#eclipse_cat">蝕仙狸</a>
</pre>
<h4>関連役職</h4>
<pre>
<a href="fox.php#revive_fox">仙狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
仙狸 (センリ) とは、中国の猫の妖怪です (「狸」は山猫の意)。
東方陰陽鉄人狼のプレイヤーさんのコメントを参考に同じ猫の妖怪である
<a href="#poison_cat">猫又</a>の上位種として実装してみました。
</pre>

<h3><a id="sacrifice_cat">猫神</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<h4>[耐性] 蘇生：不可</h4>
<h4>[蘇生能力] 成功率：100% (1回限定) / 誤爆：無し</h4>
<pre>
毒能力を失った代わりに確実な蘇生に特化した猫又の亜種。
蘇生に関するルールは<a href="#about_revive">基本ルール [蘇生]</a>参照。
蘇生成功率は 100% で、例外的に誤爆率が 0% に設定されているが、成功すると自分が死亡する。
複数の猫神が同時に同じ人を蘇生しようとした場合は「全員成功」扱いとなり、本人は死亡する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#possessed_mad">犬神</a>が能力を発動した時に蘇生能力者が誰もいないと
正体がばれてしまうので、同じ状況に見える村陣営種を用意しました。
</pre>

<h3><a id="eclipse_cat">蝕仙狸</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β17～]</h3>
<h4>[役職表示] <a href="#revive_cat">仙狸</a></h4>
<h4>[耐性] 蘇生：不可</h4>
<h4>[蘇生能力] 成功率：40% / 誤爆：有り (特殊)</h4>
<pre>
誤爆率が高く設定されている猫又の亜種。本人の表記は「<a href="#revive_cat">仙狸</a>」。
蘇生に関するルールは<a href="#about_revive">基本ルール [蘇生]</a>参照。
蘇生成功率は 40%、誤爆率は 20% で固定。
</pre>
<h5>Ver. 1.4.0 β18～</h5>
<pre>
誤爆率を 15% から 20% に変更
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#poison_cat">猫又</a>の劣化種として、夢系ではなく蝕系を採用してみました。
<a href="#about_revive">ルール</a>通りなら 40% の 1/5 は 8% なので、誤爆率が 2.5倍になっています。
実質、誤爆率だけが上がった<a href="#poison_cat">猫又</a>相当になります。
</pre>


<h2><a id="pharmacist_group">薬師系</a></h2>
<p>
<a href="#pharmacist">薬師</a>
<a href="#cure_pharmacist">河童</a>
<a href="#revive_pharmacist">仙人</a>
<a href="#alchemy_pharmacist">錬金術師</a>
</p>

<h3><a id="pharmacist">薬師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α12～]</h3>
<h4>[薬師能力] 毒能力判定：有り / 解毒：有り / ショック死抑制：無し</h4>
<pre>
処刑投票先の毒能力者が分かり、処刑された場合に解毒(毒が発動しない)します。
毒の鑑定結果は以下の 5 種類。

1. 毒を持っていない (<a href="#dummy_poison">夢毒者</a>や発現前の<a href="#incubate_poison">潜毒者</a>もこれ)
2. 毒を持っている
3. 強い毒を持っている (<a href="#strong_poison">強毒者</a>と発現後の<a href="#incubate_poison">潜毒者</a>)
4. 限定的な毒を持っている (<a href="#poison_guard">騎士</a>・<a href="#guide_poison">誘毒者</a>・<a href="#chain_poison">連毒者</a>・<a href="#poison_jealousy">毒橋姫</a>)
5. 解毒に成功した (この場合は詳細な毒の種類は分からない)
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
<a href="#dummy_poison">夢毒者</a>が吊られると<a href="wolf.php#dream_eater_mad">獏</a>・<a href="chiroptera.php#fairy_group">妖精系</a>が巻き込まれる仕様に変更。
これを「解毒」する事はできません。
</pre>
<h5>Ver. 1.4.0 α23～</h5>
<pre>
解毒成功だけでなく、前日に投票した人の詳細な毒能力が分かります
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/24" target="_top">新役職考案スレ</a> の 24 が原型です。「くすし」と読みます。
<a href="wolf.php#poison_wolf">毒狼</a>の対抗役職です。
<a href="#poison_group">埋毒者系</a>に対しても効果を発揮します。
</pre>

<h3><a id="cure_pharmacist">河童</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<h4>[薬師能力] 毒能力判定：無し / 解毒：有り / ショック死抑制：有り</h4>
<pre>
昼に投票した人を解毒・ショック死抑制する特殊な薬師。
解毒能力は<a href="#pharmacist">薬師</a>と同じ。
抑制できるのは<a href="sub_role.php#chicken_group">小心者系</a>のみで、<a href="#seal_medium">封印師</a>・<a href="#jealousy">橋姫</a>・<a href="wolf.php#agitate_mad">扇動者</a>によるものは対象外。
解毒・ショック死抑制に成功すると次の日に「治療成功」という趣旨のメッセージが表示される。
何の「治療」に成功した(毒やショック死の種類など)のかは表示されない。
再投票時には発動しない。

例) A[河童] → B[村人][小心者]
この場合、Bがショック死する条件を満たしますが、河童の能力でキャンセルされます。
キャンセルするだけで<a href="sub_role.php#chicken">小心者</a>が消える訳ではないので注意。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/17" target="_top">新役職考案スレ</a> の 17 が原型です。
河童の膏薬伝説をヒントに、高い治療能力をもった特殊薬師としてデザインしました。
<a href="sub_role.php#febris">熱病</a>の性質上、<a href="wolf.php#miasma_mad">土蜘蛛</a>に対して完全なカウンターになっています。
</pre>

<h3><a id="revive_pharmacist">仙人</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β15～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 蘇生 (1回限定) / 蘇生：不可 / 憑依：無効</h4>
<h4>[薬師能力] 毒能力判定：無し / 解毒：無し / ショック死抑制：有り</h4>
<pre>
昼に処刑投票した人のショック死を抑制する特殊な薬師。
ショック死抑制能力は<a href="#cure_pharmacist">河童</a>と同じ。
人狼に襲撃されて死亡した場合、一度だけ即座に蘇生する。
</pre>
<ol>
  <li>一度蘇生すると能力を失う (<a href="sub_role.php#lost_ability">能力喪失</a>)。</li>
  <li>恋人になったら蘇生能力は無効。</li>
  <li>人狼の襲撃以外で死亡した場合 (例：暗殺)、蘇生能力は無効。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、蘇生能力は無効。</li>
  <li>蘇生対象外 (選ばれた場合は失敗する)。</li>
  <li><a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#reverse_assassin">反魂師</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project の藤原妹紅がモチーフです。
能力が発動すると「死亡と蘇生が同時に表示される」ことになります。
</pre>

<h3><a id="alchemy_pharmacist">錬金術師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β22～]</h3>
<h4>[薬師能力] 毒能力判定：有り / 解毒：特殊 / ショック死抑制：無し</h4>
<pre>
処刑投票先が毒を持っていたら中る範囲を「村人陣営以外」に変更してしまう特殊な薬師。
毒鑑定能力や対象者変更判定 (<a href="#pharmacist">薬師</a>が「解毒した」と判定する対象) は<a href="#pharmacist">薬師</a>と同じ。
</pre>
<ol>
  <li>判定は処刑対象が決定された後。</li>
  <li>「対象者変更」能力が発動しても毒鑑定結果は変わらない (処刑した場合でも毒の種類が分かる)。</li>
  <li><a href="sub_role.php#lovers">恋人</a>は判定対象外 (<a href="#medium_group">巫女系</a>と同じ)。</li>
  <li><a href="#pharmacist">薬師</a>・<a href="#cure_pharmacist">河童</a>が解毒していた場合、毒は不発。</li>
  <li><a href="#chain_poison">連毒者</a>に投票しても能力は発動しない (毒鑑定能力は有効)。</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/895" target="_top">新役職考案スレ</a> の 895 が原型です。
<a href="wolf.php#poison_wolf">毒狼</a>などを比較的少ないリスクで処刑できますが、<a href="wolf.php#mad_group">狂人系</a>や<a href="chiroptera.php">蝙蝠陣営</a>も対象に入るので、
<a href="#pharmacist">薬師</a>で解毒してしまう方がいいケースもあります。
</pre>


<h2><a id="assassin_group">暗殺者系</a></h2>
<p>
<a href="#assassin_rule">基本ルール</a>
<a href="#assassin_spec">暗殺の仕様</a>
</p>
<p>
<a href="#assassin">暗殺者</a>
<a href="#doom_assassin">死神</a>
<a href="#reverse_assassin">反魂師</a>
<a href="#soul_assassin">辻斬り</a>
<a href="#eclipse_assassin">蝕暗殺者</a>
</p>

<h3><a id="assassin_rule">基本ルール [暗殺者系]</a></h3>
<ol>
<li>「暗殺する / しない」を必ず投票する必要があります</li>
<li>狩人の<a href="#guard_limit">護衛制限</a>対象です</li>
</ol>

<h3><a id="assassin_spec">暗殺の仕様</a></h3>
<ol>
<li>暗殺対象にできない人はいません (人狼・妖狐でも選択可能)。</li>
<li>特定の条件で「暗殺反射」(自分で自分を暗殺すること) が発生します。</li>
<li>反射が発生した場合は、特殊暗殺能力者であっても即座に暗殺されます。<br>
  (例：<a href="#doom_assassin">死神</a>が反射されても暗殺で即死する)</li>
<li><a href="#reflect_guard">侍</a>・<a href="#detective_common">探偵</a>・<a href="wolf.php#sirius_wolf">天狼</a> (覚醒状態)・<a href="fox.php#cursed_fox">天狐</a>・<a href="vampire.php#soul_vampire">吸血姫</a>・<a href="sub_role.php#challenge_lovers">難題</a>を対象にした場合は反射されます。</li>
<li><a href="#cursed_brownie">祟神</a>・<a href="ogre.php">鬼陣営</a>を対象にした場合は一定確率で反射されます。</li>
<li>暗殺された人の死亡メッセージは人狼の襲撃と同じで、<a href="../spec.php#dead">死因</a>は「暗殺された」です。</li>
<li>人狼に襲撃されたり、<a href="wolf.php#trap_mad">罠師</a>の罠にかかると暗殺は無効です。</li>
<li>暗殺能力者がお互いを襲撃した場合は相打ちになります。</li>
<li>暗殺された<a href="#mage">占い師</a>の呪殺、<a href="#poison_cat">猫又</a>の蘇生は無効になります。</li>
<li>暗殺されても<a href="#guard_group">狩人系</a>の護衛判定は有効です。</li>
</ol>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
暗殺反射システムの実装
</pre>

<h3><a id="assassin">暗殺者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α18～]</h3>
<h4>[耐性] 護衛：制限対象 / 罠：有効</h4>
<pre>
暗殺者系の基本種。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="#eclipse_assassin">蝕暗殺者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他国に実在する役職で、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/8" target="_top">新役職考案スレ</a> の 8 が原型です。
村人陣営の最終兵器とも呼べる存在ですね。
</pre>

<h3><a id="doom_assassin">死神</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<h4>[耐性] 護衛：制限対象 / 罠：有効</h4>
<pre>
暗殺成立時に<a href="sub_role.php#death_warrant">死の宣告</a>を付加する特殊な暗殺者。
死の宣告の発動日は投票した夜から数えて 2 日後の昼。
</pre>
<h4>関連役職</h4>
<pre>
<a href="fox.php#doom_fox">冥狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
「寿命を延ばすこともできる暗殺者」がコンセプトです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/85" target="_top">新役職考案スレ</a> の 85 が原型です。
</pre>

<h3><a id="reverse_assassin">反魂師</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<h4>[耐性] 護衛：制限対象 / 罠：有効</h4>
<pre>
夜に選んだ人が生きていたら暗殺、死んでいたら蘇生する特殊な暗殺者。
詳細な判定順は<a href="../spec.php#vote_night">詳細な仕様</a>参照。
</pre>
<ol>
<li>一度死んだ人の能力発動はキャンセルされます。</li>
<li>「反魂」可能な対象は恋人以外全てです。</li>
<li>自分が暗殺されても投票は有効です (暗殺系の処理は同時並行処理扱い)。</li>
<li>「反魂」先が憑依能力者だった場合は、元の人が蘇生します(<a href="wolf.php#possessed_wolf_revive">憑狼</a>の処理と同じ)。</li>
</ol>
<h4>能力発動例</h4>
<pre>
例1) A[反魂師] → B[占い師] ← C[反魂師]、B[占い師] → D[妖狐]
占い結果：何も出ない (呪殺もなし)
死体：B が無残な死体で発見されました (死因：「暗殺された」)
蘇生：B は生き返りました

例2) A[反魂師] → B[猫神] ← C[人狼]、B[猫神] → D[村人]
死体：B が無残な死体で発見されました (死因：「人狼に襲撃された」)
蘇生：B は生き返りました
Bの蘇生処理はキャンセル

例3) A[暗殺者] → B[反魂師] → C[村人] ← D[人狼]
死体：B が無残な死体で発見されました (死因：「暗殺された」)
死体：C が無残な死体で発見されました (死因：「人狼に襲撃された」)
蘇生：C は生き返りました
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project の八雲 紫のスペルカード「生と死の境界」がモチーフです。
基本的には<a href="#assassin">暗殺者</a>とほぼ同じ動きで問題ないでしょう。
確定で人狼に噛み殺されそうな人を狙った時に真価を発揮します。
失敗すると大惨事となりますが……
</pre>

<h3><a id="soul_assassin">辻斬り</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[耐性] 護衛：制限対象 / 罠：有効 / 遺言：不可</h4>
<pre>
暗殺した人の役職を知る事ができる、上位暗殺者。
遺言を残せない。

人狼が襲撃して発動する可能性のある毒能力者を暗殺した場合は、本人は毒死する。
例1) <a href="wolf.php#poison_wolf">毒狼</a>を襲撃したら毒死
例2) 恋人の有無を問わず<a href="#poison_jealousy">毒橋姫</a>を襲撃したら毒死
例3) <a href="#chain_poison">連毒者</a>は毒能力者に中らないと発動しないので不発。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#soul_mage">魂の占い師</a>・<a href="wolf.php#tongue_wolf">舌禍狼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
メビウス人狼の暗殺者がモチーフで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/641" target="_top">新役職考案スレ</a> の 641 が原型です。
</pre>

<h3><a id="eclipse_assassin">蝕暗殺者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<h4>[役職表示] <a href="#assassin">暗殺者</a></h4>
<h4>[耐性] 護衛：制限対象 / 罠：有効</h4>
<pre>
30% の確率で<a href="#assassin_spec">暗殺反射</a>が発生する劣化暗殺者。本人の表記は「<a href="#assassin">暗殺者</a>」。
<a href="#psycho_mage">精神鑑定士</a>の鑑定結果は「正常」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#assassin">暗殺者</a>に暗殺のリスクを感じてもらうための存在です。
<a href="#assassin">暗殺者</a>が<a href="sub_role.php#lovers">恋人</a>になると大惨事が発生する可能性がありましたが
この役職の登場によって多少は緩和されるかもしれません。
</pre>


<h2><a id="mind_scanner_group">さとり系</a></h2>
<p>
<a href="#mind_scanner">さとり</a>
<a href="#evoke_scanner">イタコ</a>
<a href="#presage_scanner">件</a>
<a href="#clairvoyance_scanner">猩々</a>
<a href="#whisper_scanner">囁騒霊</a>
<a href="#howl_scanner">吠騒霊</a>
<a href="#telepath_scanner">念騒霊</a>
</p>

<h3><a id="mind_scanner">さとり</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α21～]</h3>
<pre>
初日の夜に誰か一人を選んでその人を<a href="sub_role.php#mind_read">サトラレ</a>にします。
人狼の遠吠えが一切見えません。
</pre>
<ol>
<li>結果が出るのは 2 日目以降です。</li>
<li>身代わり君を対象に選ぶことはできません。</li>
</ol>
<h5>Ver. 1.4.0 β17～</h5>
<pre>
身代わり君を対象に選ぶことはできません。
</pre>
<h5>Ver. 1.4.0 α23～</h5>
<pre>
人狼の遠吠えが一切見えません。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/4" target="_top">新役職考案スレ</a> の 4 が原型です。
相手も見られていることだけは自覚できるので
どこまで推理に活かせるのかは未知数ですが……
遠吠えの有無で相手が人狼かどうかの判断できてしまうので
Ver. 1.4.0 α23 からは常時遠吠えを見えなくしました。
</pre>

<h3><a id="evoke_scanner">イタコ</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β2～]</h3>
<h4>[耐性] 遺言：不可</h4>
<pre>
初日の夜に誰か一人を選んでその人を<a href="sub_role.php#mind_evoke">口寄せ</a>にする特殊なさとり。
</pre>
<ol>
<li>投票結果が出るのは 2 日目以降です。</li>
<li><a href="sub_role.php#mind_evoke">口寄せ</a>先が死亡したら霊界から遺言窓を介してメッセージを受け取れます。</li>
<li>自分では遺言を変更できません。</li>
<li>自分の遺言欄に何か表示されていても遺言は残りません。</li>
<li>身代わり君を対象に選ぶことはできません。</li>
</ol>
<h5>Ver. 1.4.0 β17～</h5>
<pre>
身代わり君を対象に選ぶことはできません。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
霊界オフモードを有効活用できる役職を作ろうと思い、
こういう実装にしてみました。
</pre>

<h3><a id="presage_scanner">件</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β18～]</h3>
<h4>[耐性] 人狼襲撃：特殊</h4>
<pre>
初日の夜に誰か一人を選んでその人を<a href="sub_role.php#mind_presage">受託者</a>にする特殊なさとり。
</pre>
<ol>
<li>投票結果が出るのは 2 日目以降です。</li>
<li>自分が<a href="wolf.php#wolf_group">人狼</a>に襲撃されて死亡したら<a href="sub_role.php#mind_presage">受託者</a>に自分が誰に襲撃されたかメッセージが送られます。</li>
<li>襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合は無効です。</li>
<li>身代わり君を対象に選ぶことはできません。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#reporter">ブン屋</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
能力を制限した<a href="#reporter">ブン屋</a>のさとりバージョンです。「くだん」と読みます。
メッセージが届く相手が人外の可能性もあるので件であることを信用されても
さらなる悲劇が起こる可能性があるのがポイントです。
</pre>

<h3><a id="clairvoyance_scanner">猩々</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β22～]</h3>
<h4>[耐性] 護衛：制限対象 / 罠：有効</h4>
<pre>
2日目以降、夜に投票した人のその夜の投票先を知ることができる特殊なさとり。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<ol>
<li>対象が投票していない場合は何も出ない。</li>
<li><a href="#assassin_group">暗殺者系</a>・<a href="#poison_cat_group">猫又系</a>などが「行動しない」を選んでいた場合は何も出ない。</li>
<li>能力を発動できなかった場合でも分かる。<br>
  例) 人狼に噛み殺された占い師の投票先は分かる。
</li>
<li>投票先に<a href="wolf.php#trap_mad">罠師</a>の罠が設置されていたら死亡する。</a>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#stargazer_mage">占星術師</a>・<a href="#border_priest">境界師</a>・<a href="#reporter">ブン屋</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/864" target="_top">新役職考案スレ</a> の 864 が原型です。「しょうじょう」と読みます。
<a href="#reporter">ブン屋</a>と良く似た能力で、判定処理も<a href="#reporter">ブン屋</a>の直後となっています。
他の投票能力者や、<a href="#attempt_necromancer">蟲姫</a>・<a href="#yama_necromancer">閻魔</a>などと連携できると非常に強力です。
</pre>

<h3><a id="whisper_scanner">囁騒霊</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<pre>
2日目夜以降、<a href="#common_group">共有者系</a>に一方的に声が届く特殊なさとり。
相手には誰の声が聞こえているのか分かるが、仲間表示などには何も出ない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
名称は東方 Project のプリズムリバー姉妹がモチーフです。
共有者の囁きに同時に表示される形で実装しているので、
実質、片側通行の共有者相当になります。
</pre>

<h3><a id="howl_scanner">吠騒霊</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<pre>
2日目夜以降、<a href="wolf.php#wolf_group">人狼系</a>に一方的に声が届く特殊なさとり。
相手には誰の声が聞こえているのか分かるが、仲間表示などには何も出ない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#whisper_scanner">囁騒霊</a>の対人狼バージョンです。
人狼の夜会話に同時に表示される形で実装しているので<a href="wolf.php#whisper_mad">囁き狂人</a>にも見えます。
</pre>

<h3><a id="telepath_scanner">念騒霊</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<pre>
2日目夜以降、<a href="fox.php#fox_group">妖狐系</a>に一方的に声が届く特殊なさとり。
相手には誰の声が聞こえているのか分かるが、仲間表示などには何も出ない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#whisper_scanner">囁騒霊</a>の対妖狐バージョンです。
妖狐の<a href="fox.php#fox_talk">念話</a>に同時に表示される形で実装していますが、妖狐の発言ではないので
<a href="wolf.php#wise_wolf">賢狼</a>は感知できません。
</pre>


<h2><a id="jealousy_group">橋姫系</a></h2>
<p>
<a href="#jealousy">橋姫</a>
<a href="#divorce_jealousy">縁切地蔵</a>
<a href="#priest_jealousy">恋司祭</a>
<a href="#poison_jealousy">毒橋姫</a>
</p>

<h3><a id="jealousy">橋姫</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α22～]</h3>
<pre>
昼の投票時に、同一<a href="lovers.php">キューピッド</a>の<a href="sub_role.php#lovers">恋人</a>が揃って自分に投票したら
投票した<a href="sub_role.php#lovers">恋人</a>をショック死させる。
</pre>
<h4>詳細な仕様</h4>
<pre>
1. 自分が処刑されたら無効
処刑されない範囲で恋人の票を集める必要があります。
対恋人で人柱になっても無意味です。

2. 他のキューピッドの恋人たちに投票されても無効
複数のキューピッドに矢を打たれて繋がっている恋人に投票されても無効です。

3. <a href="../spec.php#vote_day">判定</a>はショック死処理の直前
つまり、投票結果が再投票になっても有効です。
また、本人が<a href="sub_role.php#celibacy">独身貴族</a>であっても有効です。
(結果的には相討ちになる)

4. カップルが別々の橋姫に投票しても無効
他の橋姫に対する投票は参照していません。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sudden_death">ショック死発動能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
対恋人役職です。
新役職考案スレ の <a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/2" target="_top">2</a>、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/21" target="_top">21</a>、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/44" target="_top">44</a> を参考にしています。
別れさせる処理が難しいのでこういう実装になりました。
</pre>

<h3><a id="divorce_jealousy">縁切地蔵</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β19～]</h3>
<pre>
自分に処刑投票してきた<a href="sub_role.php#lovers">恋人</a>に一定確率 (30%) で<a href="sub_role.php#passion">恋色迷彩</a>を付加する特殊な橋姫。
<a href="#jealousy">橋姫</a>の能力は持っていない。
</pre>
<ol>
<li><a href="../spec.php#vote_day">判定</a>は処刑対象が決定された後。</li>
<li>自分が処刑された場合は発動しない。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#cursed_brownie">祟神</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
発動機会が増えた代りに効果や発動率が<a href="#jealousy">橋姫</a>よりもかなり抑えられています。
</pre>

<h3><a id="priest_jealousy">恋司祭</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β17～]</h3>
<h4>[役職表示] <a href="#priest">司祭</a></h4>
<h4>[耐性] 護衛：制限対象</h4>
<pre>
一定日数ごとに現在、生存している<a href="sub_role.php#lovers">恋人</a>の人数が分かる特殊な司祭。
本人表記は「<a href="#priest">司祭</a>」で能力の発動日などの仕様も同じ。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#priest">司祭</a>の対恋人バージョンで、所属は橋姫系になります。
判定法則が全然違うので他の司祭表示役職と区別しやすいですが、騙りやすいですね。
</pre>

<h3><a id="poison_jealousy">毒橋姫</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[役職表示] <a href="#poison">埋毒者</a></h4>
<h4>[毒能力] 処刑：恋人 / 襲撃：恋人 / 薬師判定：限定的</h4>
<pre>
<a href="sub_role.php#lovers">恋人</a>のみに中る埋毒者で、本人の表記は「埋毒者」。
<a href="#jealousy">橋姫</a>の能力は持っていない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#poison_group">埋毒者</a>の亜種ですが、恋人限定なので所属は橋姫系です。
</pre>


<h2><a id="brownie_group">座敷童子系</a></h2>
<p>
<a href="#brownie">座敷童子</a>
<a href="#revive_brownie">蛇神</a>
<a href="#cursed_brownie">祟神</a>
<a href="#history_brownie">白澤</a>
</p>

<h3><a id="brownie">座敷童子</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β15～]</h3>
<h4>[耐性] 処刑：熱病</h4>
<pre>
役職「<a href="#human">村人</a>」の処刑投票数を +1 する。生きている間のみ有効。
処刑されたら投票した人からランダムで一人に<a href="sub_role.php#febris">熱病</a>を付加する。
<a href="human.php#detective_common">探偵</a>・<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態)・<a href="sub_role.php#challenge_lovers">難題</a>は能力の対象外となり、対象者が誰もいなかった場合は不発となる。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#doom_doll">蓬莱人形</a>・<a href="wolf.php#miasma_mad">土蜘蛛</a>・<a href="fox.php#miasma_fox">蟲狐</a>・<a href="ability.php#authority">投票数変化能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
「居る間は恩恵をもたらすが去ると災厄が訪れる」と言われる伝説がモチーフです。
村人表示役職の正体を絞り込むことができます。
</pre>

<h3><a id="revive_brownie">蛇神</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β20～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 蘇生 (1回限定) / 蘇生：不可 / 憑依：無効</h4>
<pre>
<a href="ability.php#revive">蘇生能力者</a>の成功率を +30% (1.3倍) する、座敷童子系の亜種。
生きている間のみ有効で、複数出現しても効果は重複しない。
人狼に襲撃されて死亡した場合、一度だけ即座に蘇生する。
例：<a href="#poison_cat">猫又</a>：蘇生率 25% (誤爆率 5%) → 25 * 1.3 = 32.5% (誤爆率 6%)
</pre>
<ol>
  <li>一度蘇生すると能力を失う (<a href="sub_role.php#lost_ability">能力喪失</a>)。</li>
  <li>蘇生能力を失っても蘇生率向上能力は失わない。</li>
  <li>固定されているタイプ (例：<a href="#eclipse_cat">蝕仙狸</a>) を除き、誤爆率も同時に上がる。</li>
  <li>恋人になったら蘇生能力は無効。</li>
  <li>人狼の襲撃以外で死亡した場合 (例：<a href="ability.php#assassin">暗殺</a>)、蘇生能力は無効。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、蘇生能力は無効。</li>
  <li>蘇生対象外 (選ばれた場合は失敗する)。</li>
  <li><a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#revive">蘇生能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
再生の象徴と言われる伝承がモチーフです。
<a href="ability.php#revive">蘇生能力者</a>が出現しなかったケースを考慮して<a href="#revive_pharmacist">仙人</a>相当の能力も持たせてあります。
</pre>

<h3><a id="cursed_brownie">祟神</a> (占い結果：村人(呪返し) / 霊能結果：村人) [Ver. 1.4.0 β20～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 死の宣告 (2日後) / 暗殺：反射 (30%) / 占い：呪返し / 陰陽師：死亡</h4>
<pre>
座敷童子系の亜種で、占われたら占った<a href="human.php#mage_group">占い師</a>を呪い殺してしまう。
また、処刑投票してきた人 (30%) と、自分を襲撃した人狼に<a href="sub_role.php#death_warrant">死の宣告</a> (2日後) を付加する。
<a href="#assassin_spec">暗殺反射</a> (30%) を持つ。
</pre>
<ol>
<li>判定は処刑対象が決定された後で、付加率は 30%。</li>
<li>付加判定は個々で行う (例：5人投票してきたら5回、個々で判定)。</li>
<li>自分が処刑対象になった場合でも有効。</li>
<li>人狼に襲撃された場合は襲撃した人狼のみに、確定で付加される。</li>
<li><a href="#detective_common">探偵</a>・<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態)・<a href="sub_role.php#challenge_lovers">難題</a>は能力の対象外。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#divorce_jealousy">縁切地蔵</a>・<a href="#doom_doll">蓬莱人形</a>・<a href="ability.php#cursed_group">呪い能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
「うかつに触れると災厄が訪れる」と言われる伝承がモチーフです。
対抗役職なしに触れる (占う・投票する) と酷い目に遭うことになります。
</pre>

<h3><a id="history_brownie">白澤</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β16～]</h3>
<h4>[耐性] 人狼襲撃：特殊</h4>
<pre>
人狼に襲撃されたら次の日の夜を飛ばしてしまう、座敷童子系の亜種。
能力が発動した場合は、専用のメッセージが表示される。
<a href="wolf.php#hungry_wolf">餓狼</a>・<a href="wolf.php#possessed_wolf">憑狼</a>による襲撃の場合は無効。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
翠星石鯖＠やる夫人狼で実施された記念村の設定を役職にしてみました。
東方 Project の上白沢 慧音がモチーフです。
</pre>


<h2><a id="doll_group">上海人形系</a></h2>
<p>
<a href="#doll_rule">基本ルール</a>
</p>
<p>
<a href="#doll">上海人形</a>
<a href="#friend_doll">仏蘭西人形</a>
<a href="#phantom_doll">倫敦人形</a>
<a href="#poison_doll">鈴蘭人形</a>
<a href="#doom_doll">蓬莱人形</a>
<a href="#revive_doll">西蔵人形</a>
<a href="#scarlet_doll">和蘭人形</a>
<a href="#silver_doll">露西亜人形</a>
<a href="#doll_master">人形遣い</a>
</p>

<h3><a id="doll_rule">基本ルール [上海人形]</a></h3>
<ol>
<li>他の国で言う「奴隷」に相当します。</li>
<li>勝利条件は「<a href="#doll_master">人形遣い</a>が全員死亡している＋村人陣営の勝利」です。<br>自身の生死は問いません。</li>
<li><a href="#doll_master">人形遣い</a>が出現しなかった場合の勝利条件は通常の村人陣営相当になります。</li>
<li><a href="#doll_master">人形遣い</a>が誰か分かります。</li>
<li><a href="#doll_master">人形遣い</a>の枠に<a href="#scarlet_doll">和蘭人形</a>・<a href="wolf.php#scarlet_wolf">紅狼</a>・<a href="fox.php#scarlet_fox">紅狐</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>も混ざって表示されます。</li>
</ol>
<h4>Ver. 1.4.0 β21～</h4>
<pre>
<a href="#doll_master">人形遣い</a>の枠に<a href="#scarlet_doll">和蘭人形</a>・<a href="wolf.php#scarlet_wolf">紅狼</a>・<a href="fox.php#scarlet_fox">紅狐</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>も混ざって表示されます。
</pre>

<h3><a id="doll">上海人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<pre>
上海人形系の基本種で、他の国で言う「奴隷」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「魔彩光の上海人形」がモチーフです。
奴隷という名称を避けたかったので、東方 Project のメディスン・メランコリーをヒントに
「<a href="#doll_master">人形遣い</a>の支配から逃れようとする人形」という構図を採用しました。
上海は「シャンハイ」と読みます。
</pre>

<h3><a id="friend_doll">仏蘭西人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<pre>
他の人形が誰か分かる人形。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「博愛の仏蘭西人形」がモチーフです。
仏蘭西は「フランス」と読みます。
仲間と<a href="#doll_master">人形遣い</a>の両方に名前が載っている人は<a href="#scarlet_doll">和蘭人形</a>です。
</pre>

<h3><a id="phantom_doll">倫敦人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β20～]</h3>
<h4>[耐性] 占い：無効 (1回限定)</h4>
<pre>
一度だけ、自分が占われたら占い妨害をしてしまう人形。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#phantom">占い妨害能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「霧の倫敦人形」がモチーフです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/865" target="_top">新役職考案スレ</a> の 865 が原型です。
倫敦は「ロンドン」と読みます。
</pre>

<h3><a id="poison_doll">鈴蘭人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<h4>[毒能力] 処刑：上海人形系以外 (人形遣いには中る) / 襲撃：有り / 薬師判定：有り</h4>
<pre>
毒を持った人形。毒の対象は<a href="#doll_group">上海人形系</a>以外 (<a href="#doll_master">人形遣い</a>には中る)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のメディスン・メランコリーがモデルです。
</pre>

<h3><a id="doom_doll">蓬莱人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[耐性] 処刑：死の宣告 (2日後)</h4>
<pre>
処刑された時に、自分に投票した人からランダムで一人に<a href="sub_role.php#death_warrant">死の宣告</a>を付加する人形。
</pre>
<ol>
  <li>死の宣告の発動日は処刑された日から数えて2日後の昼。</li>
  <li><a href="human.php#detective_common">探偵</a>・<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態)・<a href="sub_role.php#challenge_lovers">難題</a>は能力の対象外。</li>
  <li>対象者が誰もいなかった場合は不発となる。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="#doom_assassin">死神</a>・<a href="#brownie">座敷童子</a>・<a href="#cursed_brownie">祟神</a>・<a href="fox.php#miasma_fox">蟲狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「首吊り蓬莱人形」がモチーフです。
自分が吊られる時に<a href="#doll_master">人形遣い</a>の投票を引き込むことができれば勝利のチャンスが生まれますが、
対象はランダムなので、人柱的に票を集めてしまうと確率が下がるので難しいところですね。
</pre>

<h3><a id="revive_doll">西蔵人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β20～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 蘇生 (1回限定) / 蘇生：不可 / 憑依：無効</h4>
<pre>
人狼に襲撃されて死亡した場合、一度だけ即座に蘇生する人形。
</pre>
<ol>
  <li>一度蘇生すると能力を失う (<a href="sub_role.php#lost_ability">能力喪失</a>)。</li>
  <li>恋人になったら蘇生能力は無効。</li>
  <li>人狼の襲撃以外で死亡した場合 (例：暗殺)、蘇生能力は無効。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、蘇生能力は無効。</li>
  <li>蘇生対象外 (選ばれた場合は失敗する)。</li>
  <li><a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>の憑依対象外</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#revive">蘇生能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「輪廻の西蔵人形」がモチーフです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/865" target="_top">新役職考案スレ</a> の 865 が原型です。
西蔵は「チベット」と読みます。
</pre>

<h3><a id="scarlet_doll">和蘭人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β21～]</h3>
<pre>
<a href="wolf.php#wolf_group">人狼</a>から<a href="human.php#unconscious">無意識</a>に、<a href="fox.php#child_fox">妖狐陣営</a>から<a href="fox.php#child_fox">子狐</a>に、他の人形から<a href="#doll_master">人形遣い</a>に見える人形。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#scarlet_wolf">紅狼</a>・<a href="fox.php#scarlet_fox">紅狐</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「紅毛の和蘭人形」がモチーフです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/872" target="_top">新役職考案スレ</a> の 872 が原型です。
和蘭は「オランダ」と読みます。
</pre>

<h3><a id="silver_doll">露西亜人形</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β21～]</h3>
<pre>
<a href="#doll_master">人形遣い</a>が誰か分からない人形。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#silver_wolf">銀狼</a>・<a href="fox.php#silver_fox">銀狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドのスペルカード「白亜の露西亜人形」がモチーフです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/872" target="_top">新役職考案スレ</a> の 872 が原型です。
露西亜は「ロシア」と読みます。
</pre>

<h3><a id="doll_master">人形遣い</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β10～]</h3>
<h4>[耐性] 人狼襲撃：身代わり / 護衛：制限対象</h4>
<h4>[身代わり能力] 上海人形系 (人形遣い以外)</h4>
<pre>
他の国で言う「貴族」。
狩人の<a href="#guard_limit">護衛制限</a>対象。
</pre>
<ol>
  <li>勝利条件は通常の村人陣営相当。</li>
  <li>人形が誰なのかは分からない。</li>
  <li><a href="wolf.php#wolf_group">人狼</a>に襲撃された際に人形が生存していたら、ランダムで誰か一人が身代わりに死亡する。</li>
  <li>身代わりが発生した場合、<a href="wolf.php#wolf_group">人狼</a>の襲撃は失敗扱い。</li>
  <li>本人は身代わりが発生しても分からない。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、身代わり能力は無効。</li>
</ol>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
身代わり君が人形遣いになる可能性があります。
身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、身代わり能力は無効。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のアリス・マーガトロイドがモチーフです。
他の国に実在する役職を式神研の闇鍋向きにアレンジしてみました。
</pre>


<h2><a id="escaper_group">逃亡者系</a></h2>
<p>
<a href="#escaper_rule">基本ルール</a>
</p>
<p>
<a href="#escaper">逃亡者</a>
<a href="#incubus_escaper">一角獣</a>
</p>

<h3><a id="escaper_rule">基本ルール [逃亡者]</a></h3>
<ol>
<li>勝利条件は「村人陣営の勝利 + 自分自身の生存」。</li>
<li>2日目の夜以降、生きている誰かの側に逃亡する。</li>
<li>逃亡中は<a href="wolf.php#wolf_group">人狼</a>・<a href="vampire.php">吸血鬼</a>に直接狙われても無効 (襲撃は失敗扱い)。</li>
<li><a href="wolf.php#wolf_group">人狼</a>・<a href="vampire.php">吸血鬼</a>に狙われていたことを自覚できない。</li>
<li>逃亡先に<a href="wolf.php#trap_mad">罠師</a>の罠が設置されていたら死亡する (死因は「罠」)。</a>
<li>逃亡先の役職などによって死亡することがある (死因は「逃亡失敗」)。</li>
<li>逃亡先が<a href="#escaper">逃亡者</a>など、不在であっても「逃亡失敗」の条件を満たせば死亡する。</li>
<li>逃亡先が<a href="wolf.php#wolf_group">人狼</a> (種類不問) に襲撃されたら死亡する (死因は「人狼襲撃」)。</li>
<li>逃亡先が護衛や耐性 (例：<a href="fox.php#fox">妖狐</a>) などで<a href="wolf.php#wolf_group">人狼</a>の襲撃が失敗しても死亡する。</li>
<li><a href="ability.php#assassin">暗殺</a>は無効。</li>
<li>逃亡先が<a href="vampire.php">吸血鬼</a>だった場合は吸血される。</li>
<li>逃亡先が<a href="vampire.php">吸血鬼</a>に襲撃されたら吸血される。</li>
<li>逃亡先が護衛などで<a href="vampire.php">吸血鬼</a>の襲撃が失敗しても吸血される。</li>
<li>遺言を残せない。</li>
</ol>
<h4>Ver. 1.4.0 β20～</h4>
<pre>
吸血鬼の襲撃に関する仕様を変更。
</pre>

<h3><a id="escaper">逃亡者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<h4>[耐性] 人狼襲撃：特殊 / 暗殺：無効 / 罠：有効 / 遺言：不可</h4>
<pre>
逃亡者系の基本種。
逃亡先が<a href="wolf.php#wolf_group">人狼</a>だった場合は死亡する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#reporter">ブン屋</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他国に実在する役職です。村勝利を課せられた<a href="chiroptera.php">蝙蝠</a>のような存在ですね。
</pre>

<h3><a id="incubus_escaper">一角獣</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β22～]</h3>
<h4>[耐性] 人狼襲撃：特殊 / 暗殺：無効 / 罠：有効 / 遺言：不可</h4>
<pre>
逃亡先が女性以外だった場合は死亡する、特殊な逃亡者。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sex">性別関連能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/830" target="_top">新役職考案スレ</a> の 830 が原型です。
</pre>
</body></html>
