<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('蝙蝠陣営');
?>
<p>
<a href="#rule">基本ルール</a>
</p>
<p>
<a href="#chiroptera_group">蝙蝠系</a>
<a href="#fairy_group">妖精系</a>
</p>

<h2><a id="rule">基本ルール</a></h2>
<ol>
  <li>自分が生き残ったら勝利、死んだら敗北となる特殊な陣営です。</li>
  <li>他陣営の勝敗と競合しません。<br>
    例) 村人陣営 + 生き残った蝙蝠が勝利
  </li>
  <li>自分以外の蝙蝠の生死と勝敗は無関係です。</li>
  <li>他の蝙蝠がいても誰か分かりません。</li>
  <li>生存カウントは村人です。</li>
  <li><a href="human.php#psycho_mage">精神鑑定士</a>の判定は「正常」です。</li>
  <li><a href="human.php#sex_mage">ひよこ鑑定士</a>の判定は「蝙蝠」です。</li>
</ol>

<h2><a id="chiroptera_group">蝙蝠系</a></h2>
<p>
<a href="#chiroptera">蝙蝠</a>
<a href="#poison_chiroptera">毒蝙蝠</a>
<a href="#cursed_chiroptera">呪蝙蝠</a>
<a href="#boss_chiroptera">大蝙蝠</a>
<a href="#elder_chiroptera">古蝙蝠</a>
<a href="#scarlet_chiroptera">紅蝙蝠</a>
<a href="#dummy_chiroptera">夢求愛者</a>
</p>

<h3><a id="chiroptera">蝙蝠</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α21～]</h3>
<pre>
蝙蝠陣営の基本種。能力は何も持っていない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他の国に実在する役職です。
他陣営はいかに自陣の PP に引き込むかがポイントです。
</pre>

<h3><a id="poison_chiroptera">毒蝙蝠</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α21～]</h3>
<h4>[耐性] 護衛：狩り</h4>
<h4>[毒能力] 処刑：人狼系 + 妖狐陣営 + 蝙蝠陣営 / 襲撃：有り / 薬師判定：有り</h4>
<pre>
毒を持った蝙蝠で、毒能力は劣化<a href="human.php#strong_poison">強毒者</a>相当。
<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<h5>Ver. 1.4.0 α22～</h5>
<pre>
処刑時の毒の発動対象を [人狼系 + 妖狐陣営 + 蝙蝠陣営] に変更。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ogre.php#poison_ogre">榊鬼</a>・<a href="ability.php#poison">毒能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#poison_group">埋毒者</a>の蝙蝠バージョンです。
死んだ時点で負けなので本人には何の利益もない上に、
素直に CO するとほぼ間違いなく吊られるでしょう。
</pre>

<h3><a id="cursed_chiroptera">呪蝙蝠</a> (占い結果：村人(呪返し) / 霊能結果：村人) [Ver. 1.4.0 α21～]</h3>
<h4>[耐性] 占い：呪返し / 陰陽師：死亡 / 護衛：狩り</h4>
<pre>
占われたら占った<a href="human.php#mage_group">占い師</a>を呪い殺す蝙蝠。
<a href="human.php#voodoo_killer">陰陽師</a>の占い・<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#cursed_group">呪い能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#cursed_wolf">呪狼</a>の蝙蝠バージョンです。
どちらかと言うと、これを騙る狼や狐が非常にやっかいですね。
素直に CO しても信用を取るのは難しいでしょう。
</pre>

<h3><a id="boss_chiroptera">大蝙蝠</a> (占い結果：蝙蝠 / 霊能結果：村人) [Ver. 1.4.0 β9～]</h3>
<h4>[耐性] 人狼襲撃：身代わり / 護衛：狩り</h4>
<h4>[身代わり能力] 蝙蝠陣営</h4>
<pre>
<a href="wolf.php#wolf_group">人狼</a>に襲撃された時に、他の蝙蝠陣営を身代わりにして生き延びる事ができる蝙蝠。
<a href="human.php#guard_hunt">狩人の護衛</a>で死亡する。
</pre>
<ol>
  <li>身代わりが発生した場合、<a href="wolf.php#wolf_group">人狼</a>の襲撃は失敗扱い。</li>
  <li>身代わりで死亡した人の死因は「誰かの犠牲となって死亡したようです」。</li>
  <li>本人は身代わりが発生しても分からない。</li>
  <li>他の大蝙蝠が襲撃された場合は自分が身代わりになる可能性がある。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、身代わり能力は無効。</li>
</ol>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
身代わり君が大蝙蝠になる可能性があります。
身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、身代わり能力は無効。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他の国に実在する役職です。
狼サイドから見ると、結果的には確実に一人殺せるので、
誰でもいいから人数を減らしたい時には便利な存在と言えますね。
</pre>

<h3><a id="elder_chiroptera">古蝙蝠</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β5～]</h3>
<pre>
処刑投票数が +1 される蝙蝠。詳細は<a href="human.php#elder">長老</a>参照。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#elder">長老</a>の蝙蝠バージョンです。
PP 要員に組み込まれることの多い蝙蝠陣営の花形と言える存在ですが
それゆえに目を付けられやすいでしょう。
</pre>

<h3><a id="scarlet_chiroptera">紅蝙蝠</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β21～]</h3>
<pre>
<a href="wolf.php#wolf_group">人狼</a>から<a href="human.php#unconscious">無意識</a>に、<a href="fox.php#child_fox">妖狐陣営</a>から<a href="fox.php#child_fox">子狐</a>に、<a href="human.php#doll_group">人形</a>から<a href="human.php#doll_master">人形遣い</a>に見える蝙蝠。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#scarlet_doll">和蘭人形</a>・<a href="wolf.php#scarlet_wolf">紅狼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="fox.php#scarlet_fox">紅狐</a>の蝙蝠バージョンです。
</pre>

<h3><a id="dummy_chiroptera">夢求愛者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α24～]</h3>
<h4>[役職表示] <a href="lovers.php#self_cupid">求愛者</a></h4>
<h4>[耐性] 精神鑑定：嘘つき / 獏襲撃：死亡</h4>
<pre>
本人には<a href="lovers.php#self_cupid">求愛者</a>と表示されている蝙蝠。
矢を撃つことはできるが<a href="sub_role.php#lovers">恋人</a>にはならず、矢を撃った先に<a href="sub_role.php#mind_receiver">受信者</a>もつかない。
<a href="wolf.php#dream_eater_mad">獏</a>に襲撃されると殺される。

矢を撃ったはずの<a href="sub_role.php#lovers">恋人</a>が死んだのに自分が後追いしていない、
<a href="human.php#psycho_mage">精神鑑定士</a>から「嘘つき」、<a href="human.php#sex_mage">ひよこ鑑定士</a>から「蝙蝠」判定されるなどで
自分の正体を確認することができる。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#dummy">夢能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="lovers.php#self_cupid">求愛者</a>の夢バージョンですが、扱いとしては特殊蝙蝠です。
<a href="wolf.php#possessed_wolf">憑狼</a>が<a href="sub_role.php#lovers">恋人</a>を襲撃しても破綻しない状況を作るために作成しました。
</pre>

<h2><a id="fairy_group">妖精系</a></h2>
<p>
<a href="#fairy_spec">基本スペック</a>
</p>
<p>
<a href="#fairy">妖精</a>
<a href="#spring_fairy">春妖精</a>
<a href="#summer_fairy">夏妖精</a>
<a href="#autumn_fairy">秋妖精</a>
<a href="#winter_fairy">冬妖精</a>
<a href="#flower_fairy">花妖精</a>
<a href="#star_fairy">星妖精</a>
<a href="#sun_fairy">日妖精</a>
<a href="#moon_fairy">月妖精</a>
<a href="#grass_fairy">草妖精</a>
<a href="#light_fairy">光妖精</a>
<a href="#dark_fairy">闇妖精</a>
<a href="#shadow_fairy">影妖精</a>
<a href="#ice_fairy">氷妖精</a>
<a href="#mirror_fairy">鏡妖精</a>
</p>

<h3><a id="fairy_spec">基本スペック</a></h3>
<ol>
  <li>蝙蝠陣営の<a href="#rule">基本ルール</a>が適用されます。</li>
  <li>夜に誰か一人に投票して、対象に<a href="sub_role.php#bad_status">悪戯</a>します。</li>
  <li>悪戯は占いカテゴリに属し、呪い・占い妨害・厄払いの影響を受けます。</li>
  <li>悪戯の内容は明記していない限りは、「対象の発言の先頭に無意味な文字列を追加する」です。</li>
  <li>悪戯の結果は本人には何も表示されません。</li>
  <li>悪戯の効果は重複します (複数の妖精から悪戯されたら人数分の効果が出ます)。</li>
  <li>身代わり君を悪戯の対象に選ぶ事もできます。</li>
  <li>悪戯先が人狼に襲撃されると発動するタイプは、<a href="wolf.php#hungry_wolf">餓狼</a>・<a href="wolf.php#possessed_wolf">憑狼</a>による襲撃の場合は無効になります。</li>
  <li><a href="human.php#dummy_guard">夢守人</a>に護衛されると死亡します。</li>
  <li><a href="human.php#dummy_poison">夢毒者</a>を処刑すると毒に中ります。</li>
  <li><a href="wolf.php#dream_eater_mad">獏</a>に襲撃されると死亡します。</li>
</ol>
<h4>Ver. 1.4.0 β9～</h4>
<pre>
<a href="human.php#dummy_guard">夢守人</a>に護衛されると死亡します。
<a href="human.php#dummy_poison">夢毒者</a>を処刑すると毒に中ります。
<a href="wolf.php#dream_eater_mad">獏</a>に襲撃されると死亡します。
</pre>

<h3><a id="fairy">妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[悪戯能力] 発言妨害：有り / 占い妨害：有効 / 呪い：有効</h4>
<pre>
妖精系の基本種。追加する文字列は「<a href="human.php#common_group">共有者</a>の囁き」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
自己証明能力を持った蝙蝠です。
しかし、証明方法が鬱陶しいので信用を得た上で吊られることもあるでしょう。
</pre>

<h3><a id="spring_fairy">春妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[悪戯能力] 発言妨害：有り / 占い妨害：有効 / 呪い：有効</h4>
<pre>
春を告げる妖精。追加する文字列は「春ですよー」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方 Project のリリーホワイトがモデルで、妖精系作成の着想となった存在です。
</pre>

<h3><a id="summer_fairy">夏妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[悪戯能力] 発言妨害：有り / 占い妨害：有効 / 呪い：有効</h4>
<pre>
夏を告げる妖精。追加する文字列は「夏ですよー」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#spring_fairy">春妖精</a>の夏バージョンです。
見え透いた位置ばかりを悪戯し続けると呪いで死ぬ可能性があるので気をつけましょう。
</pre>

<h3><a id="autumn_fairy">秋妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[悪戯能力] 発言妨害：有り / 占い妨害：有効 / 呪い：有効</h4>
<pre>
秋を告げる妖精。追加する文字列は「秋ですよー」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#spring_fairy">春妖精</a>の秋バージョンです。
<a href="sub_role.php#silent">無口</a>が同時にたくさんの妖精に悪戯されると何も発言できなくなる可能性があります。
理不尽ですね。
</pre>

<h3><a id="winter_fairy">冬妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β6～]</h3>
<h4>[悪戯能力] 発言妨害：有り / 占い妨害：有効 / 呪い：有効</h4>
<pre>
冬を告げる妖精。追加する文字列は「冬ですよー」。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#spring_fairy">春妖精</a>の冬バージョンです。
一見単純な能力に見えて、実は<a href="wolf.php#possessed_wolf">憑狼</a>システムの応用で実装されています。
</pre>

<h3><a id="flower_fairy">花妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β12～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯が成功すると、花に関するメッセージを死亡メッセージ欄に表示できる妖精。
初期設定は「～の頭の上に～の花が咲きました」で、全部で26種類。
メッセージの中身は管理者が設定ファイルで変更可能。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
死亡メッセージ欄に意味の無いメッセージを表示させることができる妖精です。
能力の性質上、存在を隠すことはできませんが、無害な存在ですね。
実装の都合で無駄に花の種類が多くなっているのでコンプリートするのは
難しいと思われます。
</pre>

<h3><a id="star_fairy">星妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯が成功すると、星に関するメッセージを死亡メッセージ欄に表示できる妖精。
初期設定は「～は昨夜、～座を見ていたようです」で、全部で26種類。
メッセージの中身は管理者が設定ファイルで変更可能。
</pre>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
メッセージの種類を13から26に変更。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#flower_fairy">花妖精</a>の星バージョンです。
東方 Project のスターサファイアがモチーフです。
</pre>

<h3><a id="sun_fairy">日妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯先が人狼に襲撃されたら、次の日の昼を全員<a href="sub_role.php#invisible">光学迷彩</a>にする妖精。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dark_fairy">闇妖精</a>の<a href="sub_role.php#invisible">光学迷彩</a>バージョンです。
東方 Project のサニーミルクがモチーフです。
</pre>

<h3><a id="moon_fairy">月妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯先が人狼に襲撃されたら、次の日の昼を全員<a href="sub_role.php#earplug">耳栓</a>にする妖精。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dark_fairy">闇妖精</a>の<a href="sub_role.php#earplug">耳栓</a>バージョンです。
東方 Project のルナチャイルドがモチーフです。
</pre>

<h3><a id="grass_fairy">草妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β13～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯先が人狼に襲撃されたら、次の日の昼を全員<a href="sub_role.php#grassy">草原迷彩</a>にする妖精。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dark_fairy">闇妖精</a>の<a href="sub_role.php#grassy">草原迷彩</a>バージョンです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/654" target="_top">新役職考案スレ</a> の 654 が原型です。
</pre>

<h3><a id="light_fairy">光妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β7～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯先が人狼に襲撃されたら、次の日の夜を全員<a href="sub_role.php#mind_open">公開者</a> (白夜) にする妖精。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#dark_fairy">闇妖精</a>の<a href="sub_role.php#mind_open">公開者</a>バージョンです。
白夜になると会話能力が妨害されるので人外サイドが特に不利になります。
うかつに CO したら即座に噛み殺される事でしょう。
</pre>

<h3><a id="dark_fairy">闇妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β7～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯先が人狼に襲撃されたら、次の日の昼を全員<a href="sub_role.php#blinder">目隠し</a> (宵闇) にする妖精。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
「宵闇」は<a href="sub_role.php#blinder">目隠し</a>の実装時から構想があったシステムです。
宵闇になると役職の CO 状況を掴みづらくなるので村サイドが特に不利になります。
うかつに CO したら即座に吊られる事でしょう。
</pre>

<h3><a id="shadow_fairy">影妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β20～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
自分のアイコンと色を悪戯先と同じにする妖精。
</pre>
<ol>
  <li>悪戯先が両方とも影妖精だった場合はお互いが入れ替わります</li>
  <li><a href="wolf.php#enchant_mad">狢</a>の能力が発動した場合は色だけが適用されます</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
小鳥鯖＠アイマス人狼の管理人さんへの誕生日プレゼントです。
似た名前の人を選ぶと会話が非常に分かりにくくなることになります。
</pre>

<h3><a id="ice_fairy">氷妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β16～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：有効 / 呪い：有効</h4>
<pre>
悪戯先を<a href="sub_role.php#frostbite">凍傷</a>にする妖精。
成功率は 70% で、失敗すると自分が<a href="sub_role.php#frostbite">凍傷</a>になる。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#snow_trap_mad">雪女</a>の能力を妖精に転化してみました。
</pre>

<h3><a id="mirror_fairy">鏡妖精</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β7～]</h3>
<h4>[悪戯能力] 発言妨害：無し / 占い妨害：無効 / 呪い：無効</h4>
<pre>
本人が吊られたら、次の日の昼を「決選投票」(初日に選んだ二人にしか投票できない) にする妖精。
</pre>
<ol>
  <li>昼の投票画面を見る事で能力発動を確認できます</li>
  <li>対象に選んだ二人が両方生存している時だけ有効です</li>
  <li>対象が何らかの理由で昼に死亡した場合は即座に解除されます</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
蒼星石テスト鯖＠やる夫人狼と裏世界鯖＠東方陰陽鉄人狼のとある村がモデルです。
システムメッセージは妖精系ですが、インターフェイスや内部処理は
キューピッド系の処理を流用しています。
</pre>
</body></html>
