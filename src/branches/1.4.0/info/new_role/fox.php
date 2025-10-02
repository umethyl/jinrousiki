<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('妖狐陣営');
?>
<p>
<a href="#fox_partner">仲間表示</a>
<a href="#fox_talk">夜の会話 (念話)</a>
</p>
<p>
<a href="#fox_group">妖狐系</a>
<a href="#child_fox_group">子狐系</a>
</p>

<h2><a id="fox_partner">仲間表示</a></h2>
<ol>
<li>全ての妖狐は<a href="#silver_fox">銀狐</a>以外の<a href="#fox_group">妖狐系</a>・<a href="#child_fox_group">子狐系</a>が誰か分かります。</li>
<li><a href="#fox_group">妖狐系</a>と<a href="#child_fox_group">子狐系</a>は別枠で表示されます (<a href="wolf.php">人狼陣営</a>における<a href="wolf.php#wolf_group">人狼系</a>と<a href="wolf.php#whisper_mad">囁き狂人</a>みたいなものです)。<br>
分けている基準は「<a href="#fox_talk">念話</a>ができるかどうか」です。
</li>
<li><a href="#child_fox_group">子狐系</a>の枠に<a href="human.php#scarlet_doll">和蘭人形</a>・<a href="wolf.php#scarlet_wolf">紅狼</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>も混ざって表示されます。</li>
<li><a href="sub_role.php#mind_lonely">はぐれ者</a>になると仲間が分からなくなります (<a href="#silver_fox">銀狐</a>と同じ)。</li>
</ol>
<h3>Ver. 1.4.0 β21～</h3>
<pre>
<a href="#child_fox_group">子狐系</a>の枠に<a href="human.php#scarlet_doll">和蘭人形</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>も混ざって表示されます。
</pre>
<h3>Ver. 1.4.0 β8～</h3>
<pre>
<a href="sub_role.php#mind_lonely">はぐれ者</a>になると仲間が分からなくなります (<a href="#silver_fox">銀狐</a>と同じ)。
</pre>
<h3>Ver. 1.4.0 α24～</h3>
<pre>
<a href="#child_fox_group">子狐系</a>の枠に<a href="wolf.php#scarlet_wolf">紅狼</a>も混ざって表示されます。
</pre>
<h3>Ver. 1.4.0 α20～</h3>
<pre>
<a href="#silver_fox">銀狐</a>は他の<a href="#fox_group">妖狐系</a>・<a href="#child_fox_group">子狐系</a>からも仲間であると分かりません。
</pre>
<h3>Ver. 1.4.0 α19～</h3>
<pre>
<a href="#fox_group">妖狐系</a>から<a href="#child_fox">子狐</a>が誰か分かります。
<a href="#fox_group">妖狐系</a>と<a href="#child_fox">子狐</a>は別枠で表示されます。
</pre>
<h3>Ver. 1.4.0 α3-7～</h3>
<pre>
全ての妖狐は<a href="#child_fox">子狐</a>以外の<a href="#fox_group">妖狐系</a>が誰か分かります。
<a href="#child_fox">子狐</a>は全ての妖狐が誰か分かります。
同一の枠で表示されるので種類は不明です。
</pre>

<h2><a id="fox_talk">夜の会話 (念話)</a></h2>
<ol>
<li><a href="#silver_fox">銀狐</a>以外の<a href="#fox_group">妖狐系</a>は夜に会話 (念話) できます。</li>
<li>他人からはいっさい見えません。</li>
<li><a href="#child_fox_group">子狐系</a>は念話を見ることも参加することも出来ません。</li>
<li><a href="wolf.php#wise_wolf">賢狼</a>には念話が<a href="human.php#common_group">共有者</a>の囁きに変換されて表示されます。</li>
<li><a href="sub_role.php#mind_lonely">はぐれ者</a>になると夜の発言が独り言になり、念話に参加できなくなります (<a href="#silver_fox">銀狐</a>と同じ)。</li>
</ol>
<h3>Ver. 1.4.0 β8～</h3>
<pre>
<a href="sub_role.php#mind_lonely">はぐれ者</a>になると夜の発言が独り言になり、念話に参加できなくなります (<a href="#silver_fox">銀狐</a>と同じ)。
</pre>
<h3>Ver. 1.4.0 α24～</h3>
<pre>
<a href="wolf.php#wise_wolf">賢狼</a>には念話が<a href="human.php#common_group">共有者</a>の囁きに変換されて表示されます。
</pre>
<h3>Ver. 1.4.0 α19～</h3>
<pre>
<a href="#silver_fox">銀狐</a>は念話できません。
</pre>
<h3>Ver. 1.4.0 α3-7～</h3>
<pre>
全ての<a href="#fox_group">妖狐系</a>は夜に会話(念話)できます。
<a href="#child_fox_group">子狐系</a>は念話を見ることも参加することも出来ません。
</pre>


<h2><a id="fox_group">妖狐系</a></h2>
<p>
<a href="#fox">妖狐</a>
<a href="#white_fox">白狐</a>
<a href="#black_fox">黒狐</a>
<a href="#gold_fox">金狐</a>
<a href="#phantom_fox">幻狐</a>
<a href="#poison_fox">管狐</a>
<a href="#blue_fox">蒼狐</a>
<a href="#emerald_fox">翠狐</a>
<a href="#voodoo_fox">九尾</a>
<a href="#revive_fox">仙狐</a>
<a href="#possessed_fox">憑狐</a>
<a href="#doom_fox">冥狐</a>
<a href="#cursed_fox">天狐</a>
<a href="#elder_fox">古狐</a>
<a href="#cute_fox">萌狐</a>
<a href="#scarlet_fox">紅狐</a>
<a href="#silver_fox">銀狐</a>
</p>

<h3><a id="fox">妖狐</a> (占い結果：村人(呪殺) / 霊能結果：村人)</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
妖狐陣営の基本種。
</pre>

<h3><a id="white_fox">白狐</a> (占い結果：村人(呪殺無し) / 霊能結果：妖狐) [Ver. 1.4.0 α17～]</h3>
<h4>[耐性] 人狼襲撃：死亡</h4>
<pre>
呪殺されない代わりに<a href="wolf.php#wolf_group">人狼</a>に襲撃されると死亡する妖狐。
<a href="#child_fox">子狐</a>との違いは占いができない代わりに他の妖狐と<a href="#fox_talk">念話</a>ができる事。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#boss_wolf">白狼</a>の妖狐バージョンです。狼サイドからは村人と大差ないですが
村サイドにはかなりの脅威となるでしょう。
</pre>

<h3><a id="black_fox">黒狐</a> (占い結果：人狼(呪殺無し) / 霊能結果：妖狐) [Ver. 1.4.0 α24～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
占い結果が「人狼」、霊能結果が「妖狐」と判定される妖狐。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
呪殺されない代わりに人狼扱いされる妖狐です。
人狼側から見ると、SG にできる代わりに占い師の真贋を読みづらくなる存在になります。
</pre>

<h3><a id="gold_fox">金狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
<a href="human.php#sex_mage">ひよこ鑑定士</a>の判定が<a href="chiroptera.php">蝙蝠</a>になる妖狐。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#gold_wolf">金狼</a>の妖狐バージョンです。
この役職でメイン役職の総数が既存のものと合わせてちょうど 100 になりました。
</pre>

<h3><a id="phantom_fox">幻狐</a> (占い結果：村人(呪殺) / 霊能結果：妖狐) [Ver. 1.4.0 β11～]</h3>
<h4>[耐性] 人狼襲撃：無効 / 占い：無効 (1回限定) / 護衛：狩り</h4>
<pre>
一度だけ、自分が占われても占い妨害をする事ができる妖狐。
妨害能力は<a href="wolf.php#phantom_wolf">幻狼</a>参照。<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#phantom">占い妨害能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#phantom_wolf">幻狼</a>の妖狐バージョンです。
二回占われると呪殺されてしまうので、どう対応するかがポイントです。
</pre>

<h3><a id="poison_fox">管狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 α17～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 毒</h4>
<h4>[毒能力] 処刑：妖狐陣営以外 / 襲撃：有り / 薬師判定：有り</h4>
<pre>
毒を持った妖狐。毒の対象は妖狐陣営以外。
<a href="wolf.php#wolf_group">人狼</a>に襲撃されたら殺されて毒が発動する。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#poison_group">埋毒者</a>の妖狐バージョンで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/110" target="_top">新役職考案スレ</a> の 110 が原型です。
「くだぎつね」と読みます。
仲間がいるときに真価を発揮します。
</pre>

<h3><a id="blue_fox">蒼狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<h4>[耐性] 人狼襲撃：無効 + はぐれ者</h4>
<pre>
<a href="wolf.php#wolf_group">人狼</a>に襲撃されたら襲撃してきた人狼を<a href="sub_role.php#mind_lonely">はぐれ者</a>にする妖狐。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#blue_wolf">蒼狼</a>の妖狐バージョンです。
発動すると相手に正体がバレてしまうので、積極的に狙うというよりは
「噛まれた相手に一矢報いる」タイプの能力ですね。
</pre>

<h3><a id="emerald_fox">翠狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：有効</h4>
<pre>
占った人が会話できない妖狐だった場合に自分と占った人を<a href="sub_role.php#mind_friend">共鳴者</a>にする妖狐。
</pre>
<ol>
  <li>能力の発動対象は<a href="#silver_fox">銀狐</a>・<a href="#child_fox_group">子狐系</a>・<a href="sub_role.php#mind_lonely">はぐれ者</a>の妖狐のいずれかです。</li>
  <li>インターフェイスは占いと同じですが結果は何も表示されません。</li>
  <li><a href="sub_role.php#mind_friend">共鳴者</a>を作る事に成功すると能力を失います (<a href="sub_role.php#lost_ability">能力喪失</a>)。</li>
  <li><a href="wolf.php#blue_wolf">蒼狼</a>の襲撃先を占って、占い先が<a href="sub_role.php#mind_lonely">はぐれ者</a>になってもその夜には能力は発動しません。</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#emerald_wolf">翠狼</a>の妖狐バージョンです。
一度しか使えないので、発動するタイミングや相手の選択がポイントになるかもしれません。
</pre>

<h3><a id="voodoo_fox">九尾</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 α20～]</h3>
<h4>[耐性] 人狼襲撃：無効 / 護衛：狩り</h4>
<pre>
夜に村人一人を選び、その人に呪いをかける妖狐。
<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<ol>
  <li>呪われた人を占った<a href="human.php#mage_group">占い師</a>は呪返しを受けます</li>
  <li><a href="ability.php#cursed">呪い所持者</a>を選んだ場合は本人が呪返しを受けます</li>
  <li>呪いをかけた人が他の人にも呪いをかけられていた場合は本人が呪返しを受けます</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#cursed_group">呪い能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#voodoo_mad">呪術師</a>の妖狐バージョンで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/58" target="_top">新役職考案スレ</a> の 58 が原型です。
対占い・対襲撃耐性は通常の<a href="#fox">妖狐</a>と同じですが
呪い能力を持った代わりに<a href="human.php#guard_hunt">狩人</a>にも弱くなっています。
</pre>

<h3><a id="revive_fox">仙狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 β2～]</h3>
<h4>[耐性] 人狼襲撃：無効 / 護衛：狩り / 蘇生：不可</h4>
<h4>[蘇生能力] 成功率：100% (1回限定) / 誤爆：有り</h4>
<pre>
蘇生能力を持った妖狐。
蘇生に関するルールは<a href="human.php#about_revive">基本ルール [蘇生]</a>参照。
蘇生成功率は 100% で、一度成功すると能力を失う (<a href="sub_role.php#lost_ability">能力喪失</a>)。
<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#revive">蘇生能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#revive_cat">仙狸</a>の妖狐バージョンです。
確実に成功しますが、1/5 (20%) は誤爆になるので要注意です。
単純に味方の妖狐を蘇生させる以外の選択肢が一番有効になる
ケースがあるのが妖狐陣営の蘇生能力者のポイントです。
</pre>

<h3><a id="possessed_fox">憑狐</a> (占い結果：村人(呪殺) / 霊能結果：妖狐) [Ver. 1.4.0 β9～]</h3>
<h4>[耐性] 人狼襲撃：無効 / 陰陽師：死亡 / 護衛：狩り / 憑依：無効</h4>
<pre>
一度だけ、死体に憑依することができる妖狐。
<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<ol>
  <li>身代わり君・<a href="wolf.php">人狼陣営</a>・<a href="lovers.php">恋人陣営</a>・<a href="ability.php#possessed_limit">憑依制限能力者</a>には憑依できません</li>
  <li><a href="human.php#voodoo_killer">陰陽師</a>に占われると死亡します</li>
  <li>憑依を実行した時に<a href="human.php#anti_voodoo">厄神</a>に護衛されると憑依に失敗します</li>
  <li>憑依を実行しなければ<a href="human.php#anti_voodoo">厄神</a>に護衛されても「厄払い成功」とは判定されません</li>
  <li>憑依を実行した時に占い能力者に占われても憑依妨害は受けません</li>
  <li>憑依中に<a href="human.php#anti_voodoo">厄神</a>に護衛されると憑依状態を解かれて元の体に戻されます</li>
  <li>複数の憑依能力者が同時に同じ人に憑依しようとした場合は全員憑依失敗扱いになります</li>
</ol>
<h5>Ver. 1.4.11 / Ver. 1.5.0 β13～</h5>
<pre>
憑依無効
</pre>
<h5>Ver. 1.4.0 β12～</h5>
<pre>
<a href="human.php#revive_priest">天人</a>・<a href="human.php#detective_common">探偵</a> (<a href="wolf.php#possessed_wolf">憑狼</a>が憑依できない役職) には憑依できません。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#sacrifice_cat">猫神</a>・<a href="wolf.php#possessed_wolf">憑狼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#possessed_mad">犬神</a>の妖狐バージョンです。
<a href="wolf.php#possessed_wolf">憑狼</a>よりも看破されやすいので、能力を使いにくいと思います。
存在自体が脅威になるタイプですね。
</pre>

<h3><a id="doom_fox">冥狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 β15～]</h3>
<h4>[耐性] 人狼襲撃：無効 / 護衛：狩り</h4>
<pre>
遅効性の<a href="human.php#doom_assassin">死神</a>相当の暗殺能力を持った妖狐。
暗殺能力は<a href="human.php#doom_assassin">死神</a>と同じで、<a href="sub_role.php#death_warrant">死の宣告</a>の発動日は投票した夜から数えて4日目後の昼。
<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="vampire.php#doom_vampire">冥血鬼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#doom_wolf">冥狼</a>の妖狐バージョンで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/614" target="_top">新役職考案スレ</a> の 614 が原型です。
流石兄弟鯖＠やる夫人狼の管理人さんがモチーフです。
発動日が違うので存在がすぐにバレることになります。
</pre>

<h3><a id="cursed_fox">天狐</a> (占い結果：村人(呪返し) / 霊能結果：妖狐) [Ver. 1.4.0 α17～]</h3>
<h4>[耐性] 人狼襲撃：無効 / 占い：呪返し / 陰陽師：死亡 / 護衛：狩り / 暗殺：反射</h4>
<pre>
占われたら占った<a href="human.php#mage_group">占い師</a>を呪い殺す妖狐。
<a href="human.php#assassin_spec">暗殺反射</a>能力を持ち、<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<h5>Ver. 1.4.0 β9～</h5>
<pre>
<a href="human.php#assassin_spec">暗殺反射</a>能力を持つ。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#cursed_group">呪い能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#cursed_wolf">呪狼</a>の妖狐バージョンで、妖狐系最上位種です。
呪いに対抗できる役職が出現するまでは狐無双が見られそうですね。
</pre>

<h3><a id="elder_fox">古狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 β5～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
投票数が +1 される妖狐。詳細は<a href="human.php#elder">長老</a>参照。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#elder">長老</a>の妖狐バージョンです。
狐サイドによる PP はめったに発生しないので、能力を有効活用するのは難しいでしょう。
</pre>

<h3><a id="cute_fox">萌狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 α24～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
昼の間だけ、低確率で発言が遠吠えに入れ替わってしまう妖狐。
遠吠えの内容は<a href="human.php#suspect">不審者</a>や<a href="wolf.php#cute_wolf">萌狼</a>と同じ。
</pre>
<h5>Ver. 1.4.0 β7～</h5>
<pre>
遠吠えの入れ替え発動を昼限定に変更。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#cute_wolf">萌狼</a>の妖狐バージョンです。
<a href="human.php#suspect">不審者</a>と違い、占われたら呪殺されますが、いずれにしても
「村人判定された人が遠吠えをした」場合、占った人は偽者です。
</pre>

<h3><a id="scarlet_fox">紅狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 α24～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
<a href="wolf.php#wolf_group">人狼</a>から<a href="human.php#unconscious">無意識</a>に、<a href="human.php#doll_group">人形</a>から<a href="human.php#doll_master">人形遣い</a>に見える妖狐。
</pre>
<h5>Ver. 1.4.0 β21～</h5>
<pre>
<a href="human.php#doll_group">人形</a>から<a href="human.php#doll_master">人形遣い</a>に見える。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#scarlet_doll">和蘭人形</a>・<a href="wolf.php#scarlet_wolf">紅狼</a>・<a href="chiroptera.php#scarlet_chiroptera">紅蝙蝠</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
やる夫人狼の初日呪殺アイコンの代名詞の一つである、ローゼンメイデンの真紅がモデルです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/383" target="_top">新役職考案スレ</a> の 383 が原型です。
始めは「占い師に分かる妖狐」にしましたがバランス取りが難しいのでこういう実装になりました。
「<a href="human.php#unconscious">無意識</a>」が騙れば人狼視点でほぼ紅狐確定と見なされるので注意が必要です。
</pre>

<h3><a id="silver_fox">銀狐</a> (占い結果：村人(呪殺) / 霊能結果：村人) [Ver. 1.4.0 α20～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
<a href="#fox_partner">仲間</a>が分からない妖狐。
(他の妖狐・<a href="#child_fox">子狐</a>からも仲間であると分からない)
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#silver_doll">露西亜人形</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#silver_wolf">銀狼</a>の妖狐バージョンです。
元々妖狐は出現人数が少なめなので仲間が分からなくてもさほど影響は無いと思います。
占いを騙る仲間から人狼判定を出される可能性はありますが……
</pre>

<h2><a id="child_fox_group">子狐系</a></h2>
<p>
<a href="#child_fox_spec">基本スペック</a>
</p>
<p>
<a href="#child_fox">子狐</a>
<a href="#sex_fox">雛狐</a>
<a href="#stargazer_fox">星狐</a>
<a href="#jammer_fox">月狐</a>
<a href="#miasma_fox">蟲狐</a>
<a href="#howl_fox">化狐</a>
</p>

<h3><a id="child_fox_spec">基本スペック</a></h3>
<ol>
  <li>呪殺されない代わりに<a href="wolf.php#wolf_group">人狼</a>に襲われると殺されます。</li>
  <li>夜の投票能力を持っている場合、成功率は 70% です。</li>
</ol>

<h3><a id="child_fox">子狐</a> (占い結果：村人(呪殺無し) / 霊能結果：子狐) [Ver. 1.4.0 α3-7～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：有効</h4>
<pre>
子狐系の基本種。占い能力を持つ。
判定法則は<a href="human.php#mage">占い師</a>と同じで、呪殺はできないが呪返しは受ける。
</pre>
<h5>Ver. 1.4.0 α17～</h5>
<pre>
占い能力を持ちました。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他国に実在する役職です。
<a href="human.php#mage">占い師</a>騙りをする場合は失敗した時にどうフォローするかがポイントです。
</pre>

<h3><a id="sex_fox">雛狐</a> (占い結果：村人(呪殺無し) / 霊能結果：子狐) [Ver. 1.4.0 β8～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：無効</h4>
<pre>
<a href="human.php#sex_mage">ひよこ鑑定士</a>相当の能力を持つ子狐。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sex">性別関連能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#sex_mage">ひよこ鑑定士</a>の<a href="#child_fox">子狐</a>バージョンです。
能力よりも、存在自体が脅威となるタイプですね。
村や狼が疑心暗鬼になって<a href="human.php#sex_mage">ひよこ鑑定士</a>の排除に動くケースが出てくるでしょう。
</pre>

<h3><a id="stargazer_fox">星狐</a> (占い結果：村人(呪殺無し) / 霊能結果：子狐) [Ver. 1.4.0 β13～]</h3>
<h4>[占い能力] 呪殺：無し / 憑依妨害：無し / 月兎：有効 / 呪い：無効</h4>
<pre>
<a href="human.php#stargazer_mage">占星術師</a>相当の能力を持つ子狐。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#stargazer_mage">占星術師</a>の<a href="#child_fox">子狐</a>バージョンです。
</pre>

<h3><a id="jammer_fox">月狐</a> (占い結果：村人(呪殺無し) / 霊能結果：子狐) [Ver. 1.4.0 β13～]</h3>
<pre>
<a href="wolf.php#jammer_mad">月兎</a>相当の能力を持つ子狐。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#jammer_mad">月兎</a>の<a href="#child_fox">子狐</a>バージョンです。
妖狐の最大の弱点である占いを妨害できるので、子狐系に於いては上位種となります。
</pre>

<h3><a id="miasma_fox">蟲狐</a> (占い結果：村人(呪殺無し) / 霊能結果：子狐) [Ver. 1.4.0 β13～]</h3>
<h4>[耐性] 人狼襲撃：死亡 + 熱病 / 処刑：熱病</h4>
<pre>
処刑されるか人狼に襲撃されたら<a href="sub_role.php#febris">熱病</a>を付加する子狐。
処刑された場合は投票した人からランダムで一人、人狼に襲撃された場合は襲撃した人狼に付加する。
<a href="human.php#detective_common">探偵</a>・<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態)・<a href="sub_role.php#challenge_lovers">難題</a>は能力の対象外となり、対象者が誰もいなかった場合は不発となる。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#poison_fox">管狐</a>の<a href="#child_fox">子狐</a>相当として、<a href="wolf.php#miasma_mad">土蜘蛛</a>能力を持たせてみました。
</pre>

<h3><a id="howl_fox">化狐</a> (占い結果：村人(呪殺無し) / 霊能結果：子狐) [Ver. 1.4.0 β17～]</h3>
<pre>
夜の独り言が他の人には<a href="wolf.php#wolf_howl">人狼の遠吠え</a>に見える子狐。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#silver_wolf">銀狼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#wolf_howl">人狼の遠吠え</a>から推測できる情報にノイズを入れる存在です。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/740" target="_top">新役職考案スレ</a> の 740 が原型です。
人狼の人数や<a href="wolf.php#silver_wolf">銀狼</a>の存在を誤認する可能性が出てくる事に注意しましょう。
</pre>
</body></html>
