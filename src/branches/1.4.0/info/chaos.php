<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('GAME_OPT_CAPT');
OutputInfoPageHeader('闇鍋モード');
?>
<p>
<a href="#wish_role"><?php echo $GAME_OPT_MESS->wish_role ?></a>
<a href="#chaos_decide_role">配役決定ルーチン</a>
</p>
<p>
<a href="#chaos"><?php echo $GAME_OPT_MESS->chaos ?></a>
<a href="#chaosfull"><?php echo $GAME_OPT_MESS->chaosfull ?></a>
<a href="#chaos_hyper"><?php echo $GAME_OPT_MESS->chaos_hyper ?></a>
<a href="#chaos_old"><?php echo $GAME_OPT_MESS->chaos ?> (旧設定)</a>
</p>
<p>
<a href="#topping"><?php echo $GAME_OPT_MESS->topping ?></a>
<a href="#chaos_open_cast"><?php echo $GAME_OPT_MESS->chaos_open_cast ?></a>
<a href="#sub_role_limit"><?php echo $GAME_OPT_MESS->sub_role_limit ?></a>
<a href="#secret_sub_role"><?php echo $GAME_OPT_MESS->secret_sub_role ?></a>
</p>

<h2><a id="wish_role"><?php echo $GAME_OPT_MESS->wish_role ?></a></h2>
<pre>
配役を先に決めてから、出現した役職グループを希望していれば
優先的に配役される仕様です。

例1) 占い師を希望して占い師・魂の占い師が出現した
→ 占い師か魂の占い師のどちらかになります

例2) 暗殺者を希望したが出現しなかった
→ 希望なしと同じ扱いになります
</pre>

<h3>Ver. 1.4.0 α24～</h3>
<pre>
希望制オプションが有効になりました
</pre>

<h3>Ver. 1.4.0 α11～</h3>
<pre>
希望制オプションは強制的にオフになります
</pre>

<h2><a id="chaos_decide_role">配役決定ルーチン</a></h2>
<ol>
  <li>バージョンアップで仕様が変わる可能性があります。</li>
  <li>ゲーム開始直後に勝敗が決まる可能性があります。</li>
  <li>設定ファイルで変更できるので具体的な数値はサーバ毎に違います。</li>
</ol>
<p>
<a href="#chaosfull_decide_role_fix">固定出現枠</a>
<a href="#chaosfull_decide_role_random">ランダム出現枠</a>
<a href="#chaosfull_decide_role_example">配役決定例</a>
</p>

<h3><a id="chaosfull_decide_role_fix">固定出現枠</a></h3>
<pre>
初期設定は「占い師1・人狼1」で、各難易度で個別に設定できます。
ただし、身代わり君が占い師になる可能性もあるので CO した占い師が真であるとは限りません。
</pre>

<h3><a id="chaosfull_decide_role_random">ランダム出現枠</a></h3>
<ol>
  <li>各役職の出現率は基本的には非公開です。</li>

  <li>一部の役職グループには最低出現数を設定しています。<br>
    例) 人狼系は人口の 1/10 を最低限割り当てる
  </li>

  <li>役職グループ毎に人口に対する上限が設定されています。<br>
    例) 人狼系は 20%、占い師系は 10%
  </li>

  <li>ランダム出現で役職グループの上限を超えると村人に振り返られます。</li>

  <li>村人には人口に対する上限が設定されています。<br>
    上限を超えると特定の役職に振り返られます(初期設定は神話マニア)。
  </li>
</ol>

<h3><a id="chaos_decide_role_example">配役決定例</a></h3>
<ul>
  <li>村人：10人</li>

  <li>各役職の上限例 (実際とは数字が違います)</li>
  <ul>
    <li>占い師系：20%</li>
    <li>霊能者系：10%</li>
    <li>人狼系：20%</li>
  </ul>
</ul>

<pre>
1. 固定枠 (実際はサーバ毎に違います)
占い師1　霊能者1　人狼1

2. ランダム配役を加えた結果例
占い師2　精神鑑定士1　霊能者1　雲外鏡1　人狼1　白狼1　萌狼3

3. 上限補正
3-1. 数が多いところから優先的に削られます。
なるべく多くの種類が出現するようにするためです。

占い師系3 → 2
占い師1　精神鑑定士1　村人1

3-2. 固定枠は補正対象になりません
霊能者系2 → 1
霊能者1　村人1

3-3. 同じ数でどちらかを削らないといけない場合はランダムです
(役職の出現率とは関係ありません)
人狼系5→ 2

人狼1 は固定なので補正対象外。つまり、萌狼3と白狼1から3削られます。
数が多いほうから削るので、萌狼-2は確定。
最後に萌狼1と白狼1からランダムでどちらかが削られます。

補正結果例
人狼1　白狼1　村人3
上限補正後の配役
村人5　占い師1　精神鑑定士1　霊能者1　人狼1　白狼1

4. 村人上限補正
「神話マニア村」のオプションが付いていない場合は
村人の上限を超えたら神話マニアに振り返られます。

村人の上限例　人口の10%
村人5 → 村人1　神話マニア4

5. 最終配役
村人1　占い師1　精神鑑定士1　霊能者1　人狼1　白狼1　神話マニア4
</pre>

<h2><a id="chaos"><?php echo $GAME_OPT_MESS->chaos ?></a></h2>
<h3><a id="chaos_appear_role">出現役職</a></h3>
<pre>
出現する可能性のある役職は以下です。
</pre>
<h4><a href="new_role/human.php">村人陣営</a></h4>
<pre>
<a href="new_role/human.php#human_group">村人系</a>：<a href="new_role/human.php#human">村人</a>
<a href="new_role/human.php#mage_group">占い師系</a>：<a href="new_role/human.php#mage">占い師</a>・<a href="new_role/human.php#soul_mage">魂の占い師</a>・<a href="new_role/human.php#psycho_mage">精神鑑定士</a>
<a href="new_role/human.php#necromancer_group">霊能者系</a>：<a href="new_role/human.php#necromancer">霊能者</a>
<a href="new_role/human.php#medium_group">巫女系</a>：<a href="new_role/human.php#medium">巫女</a>
<a href="new_role/human.php#guard_group">狩人系</a>：<a href="new_role/human.php#guard">狩人</a>・<a href="new_role/human.php#poison_guard">騎士</a>・<a href="new_role/human.php#reporter">ブン屋</a>
<a href="new_role/human.php#common_group">共有者系</a>：<a href="new_role/human.php#common">共有者</a>
<a href="new_role/human.php#poison_group">埋毒者系</a>：<a href="new_role/human.php#poison">埋毒者</a>・<a href="new_role/human.php#incubate_poison">潜毒者</a>
<a href="new_role/human.php#pharmacist_group">薬師系</a>：<a href="new_role/human.php#pharmacist">薬師</a>
<a href="new_role/human.php#assassin_group">暗殺者系</a>：<a href="new_role/human.php#assassin">暗殺者</a>
<a href="new_role/human.php#doll_group">上海人形系</a>：<a href="new_role/human.php#doll">上海人形</a>・<a href="new_role/human.php#doll_master">人形遣い</a>
<a href="new_role/human.php#escaper_group">逃亡者系</a>：<a href="new_role/human.php#escaper">逃亡者</a>
</pre>

<h4><a href="new_role/wolf.php">人狼陣営</a></h4>
<pre>
<a href="new_role/wolf.php#wolf_group">人狼系</a>：<a href="new_role/wolf.php#wolf">人狼</a>・<a href="new_role/wolf.php#boss_wolf">白狼</a>・<a href="new_role/wolf.php#poison_wolf">毒狼</a>・<a href="new_role/wolf.php#tongue_wolf">舌禍狼</a>・<a href="new_role/wolf.php#silver_wolf">銀狼</a>
<a href="new_role/wolf.php#mad_group">狂人系</a>：<a href="new_role/wolf.php#mad">狂人</a>・<a href="new_role/wolf.php#fanatic_mad">狂信者</a>・<a href="new_role/wolf.php#whisper_mad">囁き狂人</a>
</pre>

<h4><a href="new_role/fox.php">妖狐陣営</a></h4>
<pre>
<a href="new_role/fox.php#fox_group">妖狐系</a>：<a href="new_role/fox.php#fox">妖狐</a>
<a href="new_role/fox.php#child_fox_group">子狐系</a>：<a href="new_role/fox.php#child_fox">子狐</a>
</pre>

<h4><a href="new_role/lovers.php">恋人陣営</a></h4>
<pre>
<a href="new_role/lovers.php#cupid_group">キューピッド系</a>：<a href="new_role/lovers.php#cupid">キューピッド</a>・<a href="new_role/lovers.php#self_cupid">求愛者</a>
</pre>

<h4><a href="new_role/quiz.php">出題者陣営</a></h4>
<pre>
<a href="new_role/quiz.php#quiz_group">出題者系</a>：<a href="new_role/quiz.php#quiz">出題者</a>
</pre>

<h4><a href="new_role/chiroptera.php">蝙蝠陣営</a></h4>
<pre>
<a href="new_role/chiroptera.php#chiroptera_group">蝙蝠系</a>：<a href="new_role/chiroptera.php#chiroptera">蝙蝠</a>
</pre>

<h4><a href="new_role/mania.php">神話マニア陣営</a></h4>
<pre>
<a href="new_role/mania.php#mania_group">神話マニア系</a>：<a href="new_role/mania.php#mania">神話マニア</a>
</pre>

<h2><a id="chaosfull"><?php echo $GAME_OPT_MESS->chaosfull ?></a></h2>
<h3><a id="chaosfull_appear_role">出現役職</a></h3>
<pre>
出現する可能性のある役職は以下 (Ver. 1.4.0 α23 相当) です。
</pre>
<h4><a href="new_role/human.php">村人陣営</a></h4>
<pre>
<a href="new_role/human.php#human_group">村人系</a>：<a href="new_role/human.php#human">村人</a>・<a href="new_role/human.php#suspect">不審者</a>・<a href="new_role/human.php#unconscious">無意識</a>
<a href="new_role/human.php#mage_group">占い師系</a>：<a href="new_role/human.php#mage">占い師</a>・<a href="new_role/human.php#soul_mage">魂の占い師</a>・<a href="new_role/human.php#psycho_mage">精神鑑定士</a>・<a href="new_role/human.php#sex_mage">ひよこ鑑定士</a>・<a href="new_role/human.php#voodoo_killer">陰陽師</a>・<a href="new_role/human.php#dummy_mage">夢見人</a>
<a href="new_role/human.php#necromancer_group">霊能者系</a>：<a href="new_role/human.php#necromancer">霊能者</a>・<a href="new_role/human.php#soul_necromancer">雲外鏡</a>・<a href="new_role/human.php#yama_necromancer">閻魔</a>・<a href="new_role/human.php#dummy_necromancer">夢枕人</a>
<a href="new_role/human.php#medium_group">巫女系</a>：<a href="new_role/human.php#medium">巫女</a>
<a href="new_role/human.php#guard_group">狩人系</a>：<a href="new_role/human.php#guard">狩人</a>・<a href="new_role/human.php#poison_guard">騎士</a>・<a href="new_role/human.php#reporter">ブン屋</a>・<a href="new_role/human.php#anti_voodoo">厄神</a>・<a href="new_role/human.php#dummy_guard">夢守人</a>
<a href="new_role/human.php#common_group">共有者系</a>：<a href="new_role/human.php#common">共有者</a>・<a href="new_role/human.php#dummy_common">夢共有者</a>
<a href="new_role/human.php#poison_group">埋毒者系</a>：<a href="new_role/human.php#poison">埋毒者</a>・<a href="new_role/human.php#strong_poison">強毒者</a>・<a href="new_role/human.php#incubate_poison">潜毒者</a>・<a href="new_role/human.php#dummy_poison">夢毒者</a>
<a href="new_role/human.php#poison_cat_group">猫又系</a>：<a href="new_role/human.php#poison_cat">猫又</a>
<a href="new_role/human.php#pharmacist_group">薬師系</a>：<a href="new_role/human.php#pharmacist">薬師</a>
<a href="new_role/human.php#assassin_group">暗殺者系</a>：<a href="new_role/human.php#assassin">暗殺者</a>
<a href="new_role/human.php#mind_scanner_group">さとり系</a>：<a href="new_role/human.php#mind_scanner">さとり</a>
<a href="new_role/human.php#jealousy_group">橋姫系</a>：<a href="new_role/human.php#jealousy">橋姫</a>
</pre>

<h4><a href="new_role/wolf.php">人狼陣営</a></h4>
<pre>
<a href="new_role/wolf.php#wolf_group">人狼系</a>：<a href="new_role/wolf.php#wolf">人狼</a>・<a href="new_role/wolf.php#boss_wolf">白狼</a>・<a href="new_role/wolf.php#cursed_wolf">呪狼</a>・<a href="new_role/wolf.php#poison_wolf">毒狼</a>・<a href="new_role/wolf.php#resist_wolf">抗毒狼</a>・<a href="new_role/wolf.php#tongue_wolf">舌禍狼</a>・<a href="new_role/wolf.php#cute_wolf">萌狼</a>・<a href="new_role/wolf.php#silver_wolf">銀狼</a>
<a href="new_role/wolf.php#mad_group">狂人系</a>：<a href="new_role/wolf.php#mad">狂人</a>・<a href="new_role/wolf.php#fanatic_mad">狂信者</a>・<a href="new_role/wolf.php#whisper_mad">囁き狂人</a>・<a href="new_role/wolf.php#jammer_mad">月兎</a>・<a href="new_role/wolf.php#voodoo_mad">呪術師</a>・<a href="new_role/wolf.php#dream_eater_mad">獏</a>・<a href="new_role/wolf.php#trap_mad">罠師</a>・<a href="new_role/wolf.php#corpse_courier_mad">火車</a>
</pre>

<h4><a href="new_role/fox.php">妖狐陣営</a></h4>
<pre>
<a href="new_role/fox.php#fox_group">妖狐系</a>：<a href="new_role/fox.php#fox">妖狐</a>・<a href="new_role/fox.php#white_fox">白狐</a>・<a href="new_role/fox.php#poison_fox">管狐</a>・<a href="new_role/fox.php#voodoo_fox">九尾</a>・<a href="new_role/fox.php#cursed_fox">天狐</a>・<a href="new_role/fox.php#silver_fox">銀狐</a>
<a href="new_role/fox.php#child_fox_group">子狐系</a>：<a href="new_role/fox.php#child_fox">子狐</a>
</pre>

<h4><a href="new_role/lovers.php">恋人陣営</a></h4>
<pre>
<a href="new_role/lovers.php#cupid_group">キューピッド系</a>：<a href="new_role/lovers.php#cupid">キューピッド</a>・<a href="new_role/lovers.php#self_cupid">求愛者</a>・<a href="new_role/lovers.php#mind_cupid">女神</a>
</pre>

<h4><a href="new_role/quiz.php">出題者陣営</a></h4>
<pre>
<a href="new_role/quiz.php#quiz_group">出題者系</a>：<a href="new_role/quiz.php#quiz">出題者</a>
</pre>

<h4><a href="new_role/chiroptera.php">蝙蝠陣営</a></h4>
<pre>
<a href="new_role/chiroptera.php#chiroptera_group">蝙蝠系</a>：<a href="new_role/chiroptera.php#chiroptera">蝙蝠</a>・<a href="new_role/chiroptera.php#poison_chiroptera">毒蝙蝠</a>・<a href="new_role/chiroptera.php#cursed_chiroptera">呪蝙蝠</a>
</pre>

<h4><a href="new_role/mania.php">神話マニア陣営</a></h4>
<pre>
<a href="new_role/mania.php#mania_group">神話マニア系</a>：<a href="new_role/mania.php#mania">神話マニア</a>
<a href="new_role/mania.php#unknown_mania_group">鵺系</a>：<a href="new_role/mania.php#unknown_mania">鵺</a>
</pre>

<h2><a id="chaos_hyper"><?php echo $GAME_OPT_MESS->chaos_hyper ?></a></h2>
<h3><a id="chaos_hyper_appear_role">出現役職</a></h3>
<pre>
実装されているすべての役職が出現します。
</pre>

<h2><a id="chaos_old"><?php echo $GAME_OPT_MESS->chaos ?></a> (～Ver. 1.4.0 β11)</h2>
<p>
<a href="#chaos_old_appear_role">出現役職</a>
<a href="#chaos_old_decide_role">配役決定ルーチン</a>
</p>
<h3><a id="chaos_old_appear_role">出現役職</a></h3>
<pre>
出現する可能性のある役職は以下です
</pre>
<h4><a href="new_role/human.php">村人陣営</a></h4>
<pre>
<a href="new_role/human.php#human_group">村人系</a>：<a href="new_role/human.php#human">村人</a>・<a href="new_role/human.php#suspect">不審者</a>・<a href="new_role/human.php#unconscious">無意識</a>
<a href="new_role/human.php#mage_group">占い師系</a>：<a href="new_role/human.php#mage">占い師</a>・<a href="new_role/human.php#soul_mage">魂の占い師</a>
<a href="new_role/human.php#necromancer_group">霊能者系</a>：<a href="new_role/human.php#necromancer">霊能者</a>
<a href="new_role/human.php#medium_group">巫女系</a>：<a href="new_role/human.php#medium">巫女</a>
<a href="new_role/human.php#guard_group">狩人系</a>：<a href="new_role/human.php#guard">狩人</a>・<a href="new_role/human.php#poison_guard">騎士</a>・<a href="new_role/human.php#reporter">ブン屋</a>
<a href="new_role/human.php#common_group">共有者系</a>：<a href="new_role/human.php#common">共有者</a>
<a href="new_role/human.php#poison_group">埋毒者系</a>：<a href="new_role/human.php#poison">埋毒者</a>
<a href="new_role/human.php#pharmacist_group">薬師系</a>：<a href="new_role/human.php#pharmacist">薬師</a>
<a href="new_role/mania.php#mania_group">神話マニア系</a>：<a href="new_role/mania.php#mania">神話マニア</a>
</pre>

<h4><a href="new_role/wolf.php">人狼陣営</a></h4>
<pre>
<a href="new_role/wolf.php#wolf_group">人狼系</a>：<a href="new_role/wolf.php#wolf">人狼</a>・<a href="new_role/wolf.php#boss_wolf">白狼</a>・<a href="new_role/wolf.php#poison_wolf">毒狼</a>・<a href="new_role/wolf.php#tongue_wolf">舌禍狼</a>・<a href="new_role/wolf.php#cute_wolf">萌狼</a>
<a href="new_role/wolf.php#mad_group">狂人系</a>：<a href="new_role/wolf.php#mad">狂人</a>・<a href="new_role/wolf.php#fanatic_mad">狂信者</a>
</pre>

<h4><a href="new_role/fox.php">妖狐陣営</a></h4>
<pre>
<a href="new_role/fox.php#fox_group">妖狐系</a>：<a href="new_role/fox.php#fox">妖狐</a>
<a href="new_role/fox.php#child_fox_group">子狐系</a>：<a href="new_role/fox.php#child_fox">子狐</a>
</pre>

<h4><a href="new_role/lovers.php">恋人陣営</a></h4>
<pre>
<a href="new_role/lovers.php#cupid_group">キューピッド系</a>：<a href="new_role/lovers.php#cupid">キューピッド</a>
</pre>

<h3><a id="chaos_old_decide_role">配役決定ルーチン</a></h3>
<pre>
大雑把に説明すると「通常編成＋α」(多少ぶれる＆人数が増えるとレア役職登場)です。
</pre>
<p>
<a href="#chaos_wolf">人狼</a>
<a href="#chaos_fox">妖狐</a>
<a href="#chaos_cupid">キューピッド</a>
<a href="#chaos_other">その他</a>
</p>

<h4><a id="chaos_wolf">人狼</a></h4>
<pre>
※一定数を確保します (人数が増えるごとにブレを大きくするのもありかな？)
8人未満：1:2 = 80:20 (80%で1人、20%で2人)
8～15人：1:2:3 = 15:70:15 (70%で2人、15%で1人増減(1人か3人))
16～20人：1:2:3:4:5 = 5:10:70:10:5 (70%で3人、10%で1人増減(2人か4人)、5%で2人増減(1人か5人))
21人～：70%で基礎人数、10%で1人増減、5%で2人増減 (5人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([(人数 - 20) / 5]の切捨て) + 3
例)
24人：70%で3人、10%で1人増減(2人か4人)、5%で2人増減(1人か5人)
25人：70%で4人、10%で1人増減(3人か5人)、5%で2人増減(2人か6人)
30人：70%で5人、10%で1人増減(4人か6人)、5%で2人増減(3人か7人)
50人：70%で9人、10%で1人増減(8人か10人)、5%で2人増減(7人か11人)

○特殊狼の出現率
・<a href="new_role/wolf.php#boss_wolf">白狼</a>、<a href="new_role/wolf.php#poison_wolf">毒狼</a>、<a href="new_role/wolf.php#tongue_wolf">舌禍狼</a>がこれに含まれます。
([参加人数 / 15] の切上げ)回数だけ判定を行います。
(15人なら1回、16人なら2回、50人なら3回)
判定を行うたびに、参加人数と同じ割合で1人、人狼と入れ替わります。
例)
15人：15%の判定を1回行う。
16人：16%の判定を2回行う。
30人：30%の判定を2回行う。
50人：50%の判定を3回行う。

○特殊狼の割り振り法則
・<a href="new_role/wolf.php#boss_wolf">白狼</a>
<a href="new_role/wolf.php#tongue_wolf">舌禍狼</a>、<a href="new_role/wolf.php#poison_wolf">毒狼</a>を差し引いた人数だけ出現します。

・<a href="new_role/wolf.php#tongue_wolf">舌禍狼</a>の出現率
16人未満では出現しません。
16人～20人は40%の確率で出現します。
20人以上で参加人数と同じ割合で出現します。(20人なら16%、50人なら50%)
最大出現人数は1人です。

・<a href="new_role/wolf.php#poison_wolf">毒狼</a>の出現率
20人未満では出現しません。
20人以上で参加人数と同じ割合で出現します。(20人なら16%、50人なら50%)
最大出現人数は1人です。
</pre>

<h4><a id="chaos_fox">妖狐</a></h4>
<pre>
※15人未満はたまに出る程度、それ以降は出現確定
15人未満：0:1 = 90:10 (90%で0人、10%で1人)
16～22人：1:2 = 90:10 (90%で1人、10%で2人)
23人～：80%で基礎人数、10%で1人増減 (20人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 20]の切上げ)
例)
23人：80%で2人、10%で1人増減(1人か3人)
40人：80%で2人、10%で1人増減(1人か3人)
41人：80%で3人、10%で1人増減(2人か4人)
50人：80%で3人、10%で1人増減(2人か4人)

・<a href="new_role/fox.php#child_fox">子狐</a>の出現率
20人未満では出現しません。
20人以上で参加人数と同じ割合で出現します。(20人なら16%、50人なら50%)
最大出現人数は1人です。
<a href="new_role/fox.php#child_fox">子狐</a>が出現した場合は出現人数と同じだけ妖狐が減ります。
</pre>

<h4><a id="chaos_cupid">キューピッド</a></h4>
<pre>
※増減の確率の関係で確実に出現するのは40人以上となります。
(キューピッドの出現自体をオプションで制御できるようにする予定)
10人未満：0:1 = 95:5 (95%で0人、5%で1人)
10～16人：0:1 = 70:30 (70%で0人、30%で1人)
16～22人：0:1:2 = 5:90:5 (90%で1人、5%で1人増減(0人か2人))
23人～：90%で基礎人数、5%で1人増減 (20人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 20]の切捨て)
例)
23人：90%で1人、5%で1人増減(0人か2人)
40人：90%で2人、5%で1人増減(1人か3人)
50人：90%で3人、5%で1人増減(1人か3人)
</pre>

<h4><a id="chaos_other">その他</a></h4>
<pre>
参加人数から人狼・妖狐・キューピッドを差し引いた人数です。

・占い系
※占い師と魂の占い師がここに含まれます。
8人未満：0:1 = 10:90 (90%で1人、10%で0人)
8～15人：1:2 = 95:5 (95%で1人、5%で2人)
16～29人：1:2 = 90:10 (90%で1人、10%で2人)
30人～：80%で基礎人数、10%で1人増減 (15人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 15]の切捨て)
例)
30人：80%で2人、10%で1人増減(1人か3人)
50人：80%で3人、10%で1人増減(2人か4人)

・魂の占い師の出現率
16人未満では出現しません。
16人以上で参加人数と同じ割合で出現します。(16人なら16%、50人なら50%)
最大出現人数は1人です。
魂の占い師が出現した場合は出現人数と同じだけ占い師が減ります。

・霊能系
・現在は霊能者のみがここに含まれます。
9人未満：0:1 = 10:90 (90%で1人、10%で0人)
9～15人：1:2 = 95:5 (95%で1人、5%で2人)
16～29人：1:2 = 90:10 (90%で1人、10%で2人)
30人～：80%で基礎人数、10%で1人増減 (15人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 15]の切捨て)
例)
30人：80%で2人、10%で1人増減(1人か3人)
50人：80%で3人、10%で1人増減(2人か4人)

・巫女
※キューピッドが出現している場合はほぼ確実に出現します。
　(ランダムで0人に当たっても強制的に1人に補正されます)
　(ただし、巫女が出現してもキューピッドが出現しているとは限りません)
9人未満：0:1 = 30:70 (70%で1人、30%で0人)
9～15人：0:1:2 = 10:80:10 (80%で1人、10%で1人増減(0人か2人)
16人～：80%で基礎人数、10%で1人増減 (15人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 15]の切捨て)
例)
29人：80%で1人、10%で1人増減(0人か2人)
30人：80%で2人、10%で1人増減(1人か3人)
50人：80%で3人、10%で1人増減(2人か4人)

・狂人系
・狂人と狂信者がここに含まれます。
10人未満：0:1 = 70:30 (70%で0人、30%で1人)
10～15人：0:1:2 = 10:80:10 (80%で1人、10%で1人増減(0人か2人)
16人～：80%で基礎人数、10%で1人増減 (15人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 15]の切捨て)
例)
29人：80%で1人、10%で1人増減(0人か2人)
30人：80%で2人、10%で1人増減(1人か3人)
50人：80%で3人、10%で1人増減(2人か4人)

・狂信者の出現率
16人未満では出現しません。
16人以上で参加人数と同じ割合で出現します。(16人なら16%、50人なら50%)
最大出現人数は1人です。
狂信者が出現した場合は出現人数と同じだけ狂人が減ります。

・狩人系
・狩人と騎士がここに含まれます。
11人未満：0:1 = 90:10 (90%で0人、10%で1人)
11～15人：0:1:2 = 10:80:10 (80%で1人、10%で1人増減(0人か2人)
16人～：80%で基礎人数、10%で1人増減 (15人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 15]の切捨て)
例)
29人：80%で1人、10%で1人増減(0人か2人)
30人：80%で2人、10%で1人増減(1人か3人)
50人：80%で3人、10%で1人増減(2人か4人)

・騎士の出現率
20人未満では出現しません。
20人以上で参加人数と同じ割合で出現します。(20人なら20%、50人なら50%)
最大出現人数は1人です。
騎士が出現した場合は出現人数と同じだけ狩人と埋毒者が減ります。

・共有者
13人未満：0:1 = 90:10 (90%で0人、10%で1人)
13～22人：1:2:3 = 10:80:10 (80%で2人、10%で1人増減(1人か3人)
23人～：80%で基礎人数、10%で1人増減 (15人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 15]の切捨て) + 1
例)
29人：80%で2人、10%で1人増減(1人か3人)
30人：80%で3人、10%で1人増減(2人か4人)
50人：80%で4人、10%で1人増減(3人か5人)

・埋毒者
・騎士が出現していた場合はその人数分だけ埋毒者が減ります。
16人未満：0:1 = 95:5 (95%で0人、5%で1人)
16～19人：0:1 = 85:15 (85%で0人、15%で1人)
20人～：80%で基礎人数、10%で1人増減 (20人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 20]の切捨て)
例)
39人：80%で1人、10%で1人増減(0人か2人)
40人：80%で2人、10%で1人増減(1人か3人)
50人：80%で2人、10%で1人増減(1人か3人)

・薬師
※毒狼が出現している場合はほぼ確実に出現します。
　(ランダムで0人に当たっても強制的に1人に補正されます)
　(ただし、薬師が出現しても毒狼が出現しているとは限りません)
16人未満：0:1 = 95:5 (95%で0人、5%で1人)
16～19人：0:1 = 85:15 (85%で0人、15%で1人)
20人～：80%で基礎人数、10%で1人増減 (20人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 20]の切捨て)
例)
39人：80%で1人、10%で1人増減(0人か2人)
40人：80%で2人、10%で1人増減(1人か3人)
50人：80%で2人、10%で1人増減(1人か3人)

・神話マニア
16人未満：出現しません
16～22人：0:1 = 40:60 (60%で1人、40%で0人)
23人～：80%で基礎人数、10%で1人増減 (20人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 20]の切捨て)
例)
39人：80%で1人、10%で1人増減(0人か2人)
40人：80%で2人、10%で1人増減(1人か3人)
50人：80%で2人、10%で1人増減(1人か3人)

・不審者系
・不審者と無意識がここに含まれます。
16人未満：0:1 = 90:10 (90%で0人、10%で1人)
16～19人：0:1 = 80:20 (80%で0人、20%で1人)
20人～：80%で基礎人数、10%で1人増減 (20人増えるごとに基礎人数が1人ずつ増加)
基礎人数 = ([人数 / 20]の切捨て)
例)
39人：80%で1人、10%で1人増減(0人か2人)
40人：80%で2人、10%で1人増減(1人か3人)
50人：80%で2人、10%で1人増減(1人か3人)

・不審者・無意識の出現率
20人未満では無意識の出現率が高め (無意識：不審者 = 80%:20%)。
20人以上で不審者の出現率がやや高め (無意識：不審者 = 40%:60%)。
出現人数の上限は規定していません。
</pre>

<h2><a id="topping"><?php echo $GAME_OPT_MESS->topping ?></a></h2>
<ol>
  <li><?php echo $GAME_OPT_CAPT->topping ?></li>
  <li>内容は設定ファイルで変更できます</li>
  <li>初期設定は以下です<br>
    <ol>
      <li><?php echo $GAME_OPT_MESS->topping_a ?> / 上海人形系(人形遣い以外)1 人形遣い1</li>
      <li><?php echo $GAME_OPT_MESS->topping_b ?> / 出題者1 榊鬼1</li>
      <li><?php echo $GAME_OPT_MESS->topping_c ?> / 吸血鬼系1</li>
      <li><?php echo $GAME_OPT_MESS->topping_d ?> / 猫又系1 抗毒狼1</li>
      <li><?php echo $GAME_OPT_MESS->topping_e ?> / 厄神1 憑狼1</li>
      <li><?php echo $GAME_OPT_MESS->topping_f ?> / 鬼陣営2</li>
    </ol>
  </li>
</ol>
<h3>Ver. 1.4.0 RC1～</h3>
<pre>
ランダム枠を作成することが出来ます。
</pre>

<h2><a id="chaos_open_cast"><?php echo $GAME_OPT_MESS->chaos_open_cast ?></a></h2>
<ol>
  <li>初日の夜に表示される陣営内訳通知に制限をかけることができます</li>
  <li>「通知無し」「<?php echo $GAME_OPT_CAPT->chaos_open_cast_camp ?>」「<?php echo $GAME_OPT_CAPT->chaos_open_cast_role ?>」「<?php echo $GAME_OPT_CAPT->chaos_open_cast_full ?>」から選べます</li>
</ol>

<h2><a id="sub_role_limit"><?php echo $GAME_OPT_MESS->sub_role_limit ?></a></h2>
<ol>
  <li>出現するサブ役職の種類に制限をかけることができます</li>
  <li>「<?php echo $GAME_OPT_CAPT->no_sub_role ?>」「<?php echo $GAME_OPT_CAPT->sub_role_limit_easy ?>」「<?php echo $GAME_OPT_CAPT->sub_role_limit_normal ?>」「サブ役職制限なし」から選べます</li>
  <li>内容は設定ファイルで変更できます</li>
</ol>
<p>
<a href="#sub_role_limit_easy"><?php echo $GAME_OPT_MESS->sub_role_limit_easy ?></a>
<a href="#sub_role_limit_normal"><?php echo $GAME_OPT_MESS->sub_role_limit_normal ?></a>
</p>

<h3><a id="sub_role_limit_easy"><?php echo $GAME_OPT_MESS->sub_role_limit_easy ?></a></h3>
<pre>
<a href="new_role/sub_role.php#decide_group">決定者系</a>・<a href="new_role/sub_role.php#authority_group">権力者系</a>のみ出現します。
</pre>

<h3><a id="sub_role_limit_normal"><?php echo $GAME_OPT_MESS->sub_role_limit_normal ?></a></h3>
<pre>
<a href="new_role/sub_role.php#decide_group">決定者系</a>・<a href="new_role/sub_role.php#authority_group">権力者系</a>・<a href="new_role/sub_role.php#upper_luck_group">雑草魂系</a>・<a href="new_role/sub_role.php#strong_voice_group">大声系</a>のみ出現します。
</pre>

<h2><a id="secret_sub_role"><?php echo $GAME_OPT_MESS->secret_sub_role ?></a></h2>
<pre>
一部の例外を除き、サブ役職の本人表示が無効になります。
</pre>
</body></html>
