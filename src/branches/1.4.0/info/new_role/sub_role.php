<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('サブ役職');
?>
<p>
<a href="#rule">基本ルール</a>
</p>
<p>
<a href="#chicken_group">小心者系</a>
<a href="#liar_group">狼少年系</a>
<a href="#authority_group">権力者系</a>
<a href="#upper_luck_group">雑草魂系</a>
<a href="#decide_group">決定者系</a>
<a href="#strong_voice_group">大声系</a>
<a href="#no_last_words_group">筆不精系</a>
<a href="#mind_read_group">サトラレ系</a>
<a href="#lovers_group">恋人系</a>
<a href="#joker_group">ジョーカー系</a>
<a href="#other_group">その他</a>
</p>

<h2><a id="rule">基本ルール</a></h2>
<pre>
メイン役職が付加するサブ役職 (例：<a href="#lovers">恋人</a>・<a href="#mind_read">サトラレ</a>) と専用ゲームオプション (例：<a href="../game_option.php#liar">狼少年村</a>・<a href="../game_option.php#gentleman">紳士村</a>)
以外のサブ役職は重なりません。
</pre>

<h2><a id="chicken_group">小心者系 (処刑投票ショック死系)</a></h2>
<p>
<a href="#chicken">小心者</a>
<a href="#rabbit">ウサギ</a>
<a href="#perverseness">天邪鬼</a>
<a href="#flattery">ゴマすり</a>
<a href="#impatience">短気</a>
<a href="#celibacy">独身貴族</a>
<a href="#nervy">自信家</a>
<a href="#androphobia">男性恐怖症</a>
<a href="#gynophobia">女性恐怖症</a>
<a href="#febris">熱病</a>
<a href="#frostbite">凍傷</a>
<a href="#death_warrant">死の宣告</a>
<a href="#panelist">解答者</a>
</p>

<h3><a id="chicken">小心者</a> [Ver. 1.4.0 α3-7～]</h3>
<pre>
処刑投票時に一票でも貰うとショック死します。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#ghost_common">亡霊嬢</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
大人数で実施できるようにしつつ進行を加速するために実装したシステムです。
</pre>

<h3><a id="rabbit">ウサギ</a> [Ver. 1.4.0 α3-7～]</h3>
<pre>
処刑投票時に一票も貰えないとショック死します。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#frostbite">凍傷</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#chicken">小心者</a>の逆バージョンです。
吊り先が確定してる展開になるほど死亡率が上がる、難儀な役職ですね。
</pre>

<h3><a id="perverseness">天邪鬼</a> [Ver. 1.4.0 α3-7～]</h3>
<pre>
処刑投票時に他の人と投票先が重なるとショック死します。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさん達に提供してもらったアイディアが原型です。
誰も投票しない場所を読み続けるのは非常に難しいと思います。
</pre>

<h3><a id="flattery">ゴマすり</a> [Ver. 1.4.0 α15～]</h3>
<pre>
処刑投票時に投票先が誰とも重なっていないとショック死します。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#perverseness">天邪鬼</a>の逆バージョンです。
アイディアは同時に提案されていますが名称決定の関係で実装時期が遅れています。
</pre>

<h3><a id="impatience">短気</a> [Ver. 1.4.0 α15～]</h3>
<pre>
<a href="#decide">決定者</a>と同等の能力がある代わりに再投票になるとショック死します。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
自覚のある<a href="#decide">決定者</a>で、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/80" target="_top">新役職考案スレ</a> の 80 が原型です。
その分だけ<a href="../spec.php#vote_day">判定</a>の優先度が決定者より低めになっています。
</pre>

<h3><a id="celibacy">独身貴族</a> [Ver. 1.4.0 α22～]</h3>
<pre>
処刑投票時に<a href="#lovers">恋人</a>から一票でも貰うとショック死します。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#jealousy">橋姫</a>同様、対<a href="#lovers">恋人</a>用役職ですが、こっちは小心者系という事もあって
より理不尽な仕様となっています。
</pre>

<h3><a id="nervy">自信家</a> [Ver. 1.4.0 β9～]</h3>
<pre>
処刑投票時に同一陣営の人に投票するとショック死します。
<a href="#lovers">恋人</a>の場合は恋人陣営と判定されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
翠星石鯖＠やる夫人狼のとある村の RP がモデルです。
</pre>

<h3><a id="androphobia">男性恐怖症</a> [Ver. 1.4.0 β14～]</h3>
<pre>
処刑投票時に男性に投票するとショック死します。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
やる夫人狼のプレイヤーさんとの雑談から生まれた役職です。
</pre>

<h3><a id="gynophobia">女性恐怖症</a> [Ver. 1.4.0 β14～]</h3>
<pre>
処刑投票時に女性に投票するとショック死します。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#androphobia">男性恐怖症</a>の女性バージョンです。
</pre>

<h3><a id="febris">熱病</a> [Ver. 1.4.0 β9～]</h3>
<h4>[役職表示] 発動日限定</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
表示された日の処刑投票集計後 (再投票になっても発動) にショック死します。
発動条件を満たした日の昼に突然表示されて、効果は一日で消えます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#brownie">座敷童子</a>・<a href="wolf.php#miasma_mad">土蜘蛛</a>・<a href="fox.php#miasma_fox">蟲狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
妖怪「土蜘蛛」の伝説がモチーフです。
</pre>

<h3><a id="frostbite">凍傷</a> [Ver. 1.4.0 β16～]</h3>
<h4>[役職表示] 発動日限定</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
表示された日限定の<a href="#rabbit">ウサギ</a>です。
発動条件を満たした日の昼に突然表示されて、効果は一日で消えます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#snow_trap_mad">雪女</a>・<a href="chiroptera.php#ice_fairy">氷妖精</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/736" target="_top">新役職考案スレ</a> の 736 が原型です。
</pre>

<h3><a id="death_warrant">死の宣告</a> [Ver. 1.4.0 β10～]</h3>
<h4>[役職表示] 発動日前限定</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
予告された日の処刑投票集計後 (再投票になっても発動) にショック死します。
付加された直後から、発動日がいつか表示されます。
複数付加された場合は、一番遅い日が適用されます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#doom_assassin">死神</a>・<a href="human.php#doom_doll">蓬莱人形</a>・<a href="human.php#cursed_brownie">祟神</a>・<a href="wolf.php#doom_wolf">冥狼</a>・<a href="fox.php#doom_fox">冥狐</a>・<a href="vampire.php#doom_vampire">冥血鬼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/149" target="_top">新役職考案スレ</a> の 149 が原型です。
</pre>

<h3><a id="panelist">解答者</a> [Ver. 1.4.0 α17～]</h3>
<h4>[配役制限] クイズ村・役職付加専用</h4>
<pre>
投票数が 0 になり、<a href="quiz.php#quiz">出題者</a>に投票したらショック死します。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ogre.php#poison_ogre">榊鬼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/229" target="_top">新役職考案スレ</a> の 229 が原型です。
</pre>


<h2><a id="liar_group">狼少年系 (発言変換系)</a></h2>
<p>
<a href="#liar">狼少年</a>
<a href="#invisible">光学迷彩</a>
<a href="#rainbow">虹色迷彩</a>
<a href="#weekly">七曜迷彩</a>
<a href="#passion">恋色迷彩</a>
<a href="#grassy">草原迷彩</a>
<a href="#side_reverse">鏡面迷彩</a>
<a href="#line_reverse">天地迷彩</a>
<a href="#gentleman">紳士</a>
<a href="#lady">淑女</a>
<a href="#actor">役者</a>
</p>

<h3><a id="liar">狼少年</a> [Ver. 1.4.0 α11～]</h3>
<pre>
発言時に一部のキーワードが入れ替えられてしまいます。
例：人⇔狼、白⇔黒、○⇔●
</pre>
<h5>Ver. 1.4.0 α14～</h5>
<pre>
変換対象キーワードが増えました (何が変わるかは自ら試してください)。
時々変換されないことがあります (たまには真実を語るのです)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
流石兄弟鯖＠やる夫人狼の管理人さんとの会話から生まれた役職です。
<a href="human.php#mage_group">占い師</a>がそれと知らずに CO すると大変なことになりそうです。
回避するのは簡単ですがそれを意識しないといけないだけでも
結構な負担ではないでしょうか？
</pre>

<h3><a id="invisible">光学迷彩</a> [Ver. 1.4.0 α14～]</h3>
<pre>
発言の一部が空白に入れ替えられてしまいます。
</pre>
<h5>Ver. 1.4.0 α17～</h5>
<pre>
変換率を落とした代わりに文字数が増えると変換率がアップします。
一定文字数を超えると完全に消えます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="chiroptera.php#sun_fairy">日妖精</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんから提供してもらったアイディアが原型です。
変換される確率は設定ファイルで変更できます。
</pre>

<h3><a id="rainbow">虹色迷彩</a> [Ver. 1.4.0 α17～]</h3>
<pre>
発言に虹の色が含まれていたら虹の順番に合わせて入れ替えられてしまいます。
(例：赤→橙、橙→黄、黄→緑)
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#liar">狼少年</a>の循環変換バージョンで、やる夫人狼の薔薇 GM さんのアイディアが原型です。
あまり影響は無いでしょうが、ひとたびハマると対応は非常に面倒だと思われます。
</pre>

<h3><a id="weekly">七曜迷彩</a> [Ver. 1.4.0 α19～]</h3>
<pre>
発言に曜日が含まれていたら曜日の順番に合わせて入れ替えられてしまいます。
(例：日→月、月→火、火→水)
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#rainbow">虹色迷彩</a>の曜日バージョンです。
比較的引っ掛かりやすいでしょうが、対応も簡単ですね。
</pre>

<h3><a id="passion">恋色迷彩</a> [Ver. 1.4.0 β17～]</h3>
<pre>
発言時に一部のキーワードが恋人っぽい言葉に入れ替えられてしまいます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#divorce_jealousy">縁切地蔵</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#liar">狼少年</a>の恋人バージョンで、Twitter 上の雑談から生まれた役職です。
</pre>

<h3><a id="grassy">草原迷彩</a> [Ver. 1.4.0 α23～]</h3>
<pre>
発言の一文字毎に「w」が付け加えられます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="chiroptera.php#grass_fairy">草妖精</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
いわゆる Vipper の再現です。
機械的につけるので<a href="human.php#mage_group">占い師</a>などにこれがつくとかなり悲惨な事になると思われます。
</pre>

<h3><a id="side_reverse">鏡面迷彩</a> [Ver. 1.4.0 α23～]</h3>
<pre>
発言の文字の並びが一行単位で逆になります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
要するに、「鏡面迷彩」→「彩迷面鏡」になると言う事です。
理論的には回文で発言すれば影響が出ないという事になります。
</pre>

<h3><a id="line_reverse">天地迷彩</a> [Ver. 1.4.0 α23～]</h3>
<pre>
発言の行の並びの上下が入れ替わります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
常に一行で発言をしている場合は影響がでませんし
対応しようと思えば簡単なので<a href="#side_reverse">鏡面迷彩</a>ほどは苦労しないと思われます。
</pre>

<h3><a id="gentleman">紳士</a> [Ver. 1.4.0 α14～]</h3>
<pre>
時々発言が「紳士」な言葉に入れ替えられてしまいます。
(発言内容は設定ファイルで変更可能)
ユーザ名の選択法則は「生存者からランダム」です。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんの RP が原型です。
発言内容が完全に入れ替わるので<a href="#liar">狼少年</a>より酷いです。
どんな言葉に入れ替わるのかは管理人さんの気紛れ次第。
</pre>

<h3><a id="lady">淑女</a> [Ver. 1.4.0 α14～]</h3>
<pre>
時々発言が「淑女」な言葉に入れ替えられてしまいます。
仕様は<a href="#gentleman">紳士</a>と同じ。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#gentleman">紳士</a>の女性バージョンです。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/135" target="_top">新役職考案スレ</a> の 135 が原型です。
</pre>

<h3><a id="actor">役者</a> [Ver. 1.4.0 β14～]</h3>
<pre>
発言時に一部のキーワードが入れ替えられてしまいます。
初期設定は「です」→「みょん」のみです。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#liar">狼少年</a>のカスタムバージョンです。
各サーバで自由に編集してもらうことを前提に設計しています。
</pre>

<h2><a id="authority_group">権力者系 (処刑投票数変化系)</a></h2>
<p>
<a href="#authority">権力者</a>
<a href="#rebel">反逆者</a>
<a href="#critical_voter">会心</a>
<a href="#random_voter">気分屋</a>
<a href="#watcher">傍観者</a>
</p>

<h3><a id="authority">権力者</a></h3>
<pre>
処刑投票数が +1 されます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#elder">長老</a>・<a href="human.php#brownie">座敷童子</a>
</pre>

<h3><a id="rebel">反逆者</a> [Ver. 1.4.0 α14～]</h3>
<pre>
<a href="#authority">権力者</a>と同じ人に処刑投票した場合に自分と権力者の投票数が 0 になります。
それ以外のケースなら通常通り (1票) です。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんが実際にやってしまった失敗をヒントに
対権力者を作成してみました。
</pre>

<h3><a id="critical_voter">会心</a> [Ver. 1.4.0 β14～]</h3>
<h4>[役職表示] 表示無し</h4>
<pre>
5% の確率で処刑投票数が +100 されます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#critical_luck">痛恨</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
RPG でよくある「クリティカルヒット」を再現してみました。
</pre>

<h3><a id="random_voter">気分屋</a> [Ver. 1.4.0 α14～]</h3>
<pre>
処刑投票数に -1～+1 の範囲でランダムに補正がかかります (毎回変わります)。
</pre>
<h5>Ver. 1.4.0 β7～</h5>
<pre>
<a href="human.php#elder">長老</a>系と矛盾しないために説明の表現を変えました (能力は変わっていません)。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#random_luck">波乱万丈</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/80" target="_top">新役職考案スレ</a> の 80 が原型です。
</pre>

<h3><a id="watcher">傍観者</a> [Ver. 1.4.0 α9～]</h3>
<pre>
処刑投票数が 0 になります (投票行為自体は必要です)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/8" target="_top">新役職考案スレ</a> の 8 が原型です。
</pre>


<h2><a id="upper_luck_group">雑草魂系 (処刑得票数変化系)</a></h2>
<p>
<a href="#upper_luck_rule">基本ルール</a>
</p>
<p>
<a href="#upper_luck">雑草魂</a>
<a href="#downer_luck">一発屋</a>
<a href="#star">人気者</a>
<a href="#disfavor">不人気</a>
<a href="#critical_luck">痛恨</a>
<a href="#random_luck">波乱万丈</a>
</p>

<h3><a id="upper_luck_rule">基本ルール</a> [雑草魂系]</h3>
<ol>
  <li><a href="#chicken_group">小心者系</a>のショック死判定には影響しません (投票「人数」で判定されます)。</li>
  <li>得票数が減る場合でもマイナスにはなりません。<br>
    例) 得票が 1 で -2 された場合 → 得票数は 0 と計算される。
  </li>
</ol>

<h3><a id="upper_luck">雑草魂</a> [Ver. 1.4.0 α14～]</h3>
<pre>
2 日目の処刑得票数が +4 される代わりに、3 日目以降は -2 されます。
</pre>
<h5>Ver. 1.4.0 α14～</h5>
<pre>
2 日目の補正値を +2 から +4 に変更。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方ウミガメ人狼のプレイヤーさんがモデルです。
</pre>

<h3><a id="downer_luck">一発屋</a> [Ver. 1.4.0 α14～]</h3>
<pre>
2 日目の処刑得票数が -4 される代わりに、3日目以降は +2 されます。
</pre>
<h5>Ver. 1.4.0 α14～</h5>
<pre>
2 日目の補正値を -2 から -4 に変更。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#upper_luck">雑草魂</a>の逆バージョンです。
</pre>

<h3><a id="star">人気者</a> [Ver. 1.4.0 α14～]</h3>
<pre>
処刑得票数が -1 されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/game/48159/1243197597/64" target="_top">新役職提案スレッド＠やる夫</a> の 64 が原型です。
</pre>

<h3><a id="disfavor">不人気</a> [Ver. 1.4.0 α14～]</h3>
<pre>
処刑得票数が +1 されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#star">人気者</a>の逆バージョンです。
</pre>

<h3><a id="critical_luck">痛恨</a> [Ver. 1.4.0 β14～]</h3>
<h4>[役職表示] 表示無し</h4>
<pre>
5% の確率で処刑得票数が +100 されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#critical_voter">会心</a>の処刑得票数変化バージョンです。
</pre>

<h3><a id="random_luck">波乱万丈</a> [Ver. 1.4.0 α15～]</h3>
<pre>
処刑得票数に -2～+2 の範囲でランダムに補正がかかります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#random_voice">臆病者</a>の処刑得票数変化バージョンです。
波乱万丈らしくするために、変動の程度には補正をかけていません。
</pre>


<h2><a id="decide_group">決定者系 (処刑者候補変化系)</a></h2>
<p>
<a href="#decide">決定者</a>
<a href="#plague">疫病神</a>
<a href="#good_luck">幸運</a>
<a href="#bad_luck">不運</a>
</p>

<h3><a id="decide">決定者</a></h3>
<h4>[役職表示] 表示無し</h4>
<pre>
自分の投票先が最多得票者で処刑者候補が複数いた場合、優先的に処刑される。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#impatience">短気</a>
</pre>

<h3><a id="plague">疫病神</a> [Ver. 1.4.0 α9～]</h3>
<h4>[役職表示] 表示無し</h4>
<pre>
自分の投票先が最多得票者で処刑者候補が複数いた場合、候補から除外される。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#decide">決定者</a>の逆バージョンで、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/8" target="_top">新役職考案スレ</a> の 8 が原型です。
</pre>

<h3><a id="good_luck">幸運</a> [Ver. 1.4.0 α14～]</h3>
<h4>[役職表示] 表示無し</h4>
<pre>
自分が最多得票者で処刑者候補が複数いた場合、候補から除外される。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
本人に付随する決定者系能力です。
東方ウミガメ人狼のプレイヤーさんから提供してもらったアイディアが原型です。
</pre>

<h3><a id="bad_luck">不運</a> [Ver. 1.4.0 α14～]</h3>
<h4>[役職表示] 表示無し</h4>
<pre>
自分が最多得票者で処刑者候補が複数いた場合、優先的に処刑される。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#good_luck">幸運</a>の逆バージョンです。
</pre>


<h2><a id="strong_voice_group">大声系 (発言変化系)</a></h2>
<p>
<a href="#strong_voice">大声</a>
<a href="#normal_voice">不器用</a>
<a href="#weak_voice">小声</a>
<a href="#inside_voice">内弁慶</a>
<a href="#outside_voice">外弁慶</a>
<a href="#upper_voice">メガホン</a>
<a href="#downer_voice">マスク</a>
<a href="#random_voice">臆病者</a>
</p>

<h3><a id="strong_voice">大声</a> [Ver. 1.4.0 α3-7～]</h3>
<pre>
発言が常に大声になります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
声の大きさも狼を推理するヒントになります。
ただのネタと思うこと無かれ。
</pre>

<h3><a id="normal_voice">不器用</a> [Ver. 1.4.0 α3-7～]</h3>
<pre>
発言の大きさを変えられません。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#strong_voice">大声</a>の発言固定バージョンです。
</pre>

<h3><a id="weak_voice">小声</a> [Ver. 1.4.0 α3-7～]</h3>
<pre>
発言が常に小声になります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#strong_voice">大声</a>の小声固定バージョンです。
</pre>

<h3><a id="inside_voice">内弁慶</a> [Ver. 1.4.0 α23～]</h3>
<pre>
昼は<a href="#weak_voice">小声</a>、夜は<a href="#strong_voice">大声</a>になります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
LW が<a href="#strong_voice">大声</a>・<a href="#strong_voice">小声</a>だと圧倒的に不利だと思ったので捻ってみました。
</pre>

<h3><a id="outside_voice">外弁慶</a> [Ver. 1.4.0 α23～]</h3>
<pre>
昼は<a href="#strong_voice">大声</a>、夜は<a href="#weak_voice">小声</a>になります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#inside_voice">内弁慶</a>の逆バージョンです。
</pre>

<h3><a id="upper_voice">メガホン</a> [Ver. 1.4.0 α17～]</h3>
<pre>
発言が一段階大きくなり、大声は音割れして聞き取れなくなります。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#speaker">スピーカー</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#strong_voice">大声</a>の上方シフトバージョンです。
</pre>

<h3><a id="downer_voice">マスク</a> [Ver. 1.4.0 α17～]</h3>
<pre>
発言が一段階小さくなり、小声は聞き取れなくなります。
小声は共有者の囁きに入れ替わります。
</pre>
<h4>関連役職</h4>
<pre>
<a href="#earplug">耳栓</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#upper_voice">メガホン</a>の逆バージョンです。
</pre>

<h3><a id="random_voice">臆病者</a> [Ver. 1.4.0 α14～]</h3>
<pre>
声の大きさがランダムに変わります。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
固定があるならランダムもありだろうと思って作ってみました。
唐突に大声になるのは固定より鬱陶しいかも。
</pre>


<h2><a id="no_last_words_group">筆不精系 (発言封印系)</a></h2>
<p>
<a href="#no_last_words">筆不精</a>
<a href="#blinder">目隠し</a>
<a href="#earplug">耳栓</a>
<a href="#speaker">スピーカー</a>
<a href="#whisper_ringing">囁耳鳴</a>
<a href="#howl_ringing">吠耳鳴</a>
<a href="#sweet_ringing">恋耳鳴</a>
<a href="#deep_sleep">爆睡者</a>
<a href="#silent">無口</a>
<a href="#mower">草刈り</a>
</p>

<h3><a id="no_last_words">筆不精</a> [Ver. 1.4.0 α9～]</h3>
<pre>
遺言を残せません。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#escaper">逃亡者</a>・<a href="human.php#reporter">ブン屋</a>・<a href="human.php#soul_assassin">辻斬り</a>・<a href="human.php#evoke_scanner">イタコ</a>・<a href="#possessed_exchange">交換憑依</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/8" target="_top">新役職考案スレ</a> の 8 が原型です。
「遺言残せばいいや」と思って潜伏する役職にプレッシャーがかかります。
また、安直な遺言騙りもできなくなります。
昼の発言がより盛り上がるといいな、と思って作ってみました。
</pre>

<h3><a id="blinder">目隠し</a> [Ver. 1.4.0 α14～]</h3>
<pre>
発言者の名前が見えません (空白に見えます)。
</pre>
<h5>Ver. 1.4.0 α16～</h5>
<pre>
名前の先頭に付いてる◆の色は変化しません。
ユーザアイコンを見ればある程度推測できます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#blind_guard">夜雀</a>・<a href="chiroptera.php#dark_fairy">闇妖精</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/66" target="_top">新役職考案スレ</a> の 66 が原型です。
</pre>

<h3><a id="earplug">耳栓</a> [Ver. 1.4.0 α14～]</h3>
<pre>
発言が一段階小さく見えるようになり、小声が聞き取れなります。
小声は共有者の囁きに入れ替わります。
</pre>
<h5>Ver. 1.4.0 α17～</h5>
<pre>
小声は空白ではなく、共有者の囁きに入れ替わります。
</pre>
<h5>Ver. 1.4.0 α16～</h5>
<pre>
小声が聞こえないだけではなく、大声→普通、普通→小声になります。
</pre>
<h4>関連役職</h4>
<pre>
<a href="chiroptera.php#moon_fairy">月妖精</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
ニコ生人狼のプレイヤーさん提供してもらったアイディアが原型です。
聞こえないなら取れ？ネタにマジレスしてはいけません。
</pre>

<h3><a id="speaker">スピーカー</a> [Ver. 1.4.0 α17～]</h3>
<pre>
発言が一段階大きく見えるようになり、<a href="#strong_voice">大声</a>が音割れして聞き取れなくなります
大声は<a href="#upper_voice">メガホン</a>の大声と同じです。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#earplug">耳栓</a>の逆バージョンです。
</pre>

<h3><a id="whisper_ringing">囁耳鳴</a> [Ver. 1.4.0 β14～]</h3>
<pre>
他人の独り言が共有者の囁きに見えるようになります。
共有の囁き・人狼の遠吠え・妖狐の念話は「独り言」ではないので影響しません。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#wise_wolf">賢狼</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#howl_ringing">吠耳鳴</a>の共有者の囁きバージョンです。
</pre>

<h3><a id="howl_ringing">吠耳鳴</a> [Ver. 1.4.0 β14～]</h3>
<pre>
他人の独り言が人狼の遠吠えに見えるようになります。
共有の囁き・人狼の遠吠え・妖狐の念話は「独り言」ではないので影響しません。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
遠吠えの数で狼の人数を推測するケースがあるのでそれの妨害を狙ってみました。
</pre>

<h3><a id="sweet_ringing">恋耳鳴</a> [Ver. 1.4.0 β22～]</h3>
<pre>
二日目以降、恋人の独り言が専用の囁き声に見えるようになります。
共有の囁き・人狼の遠吠え・妖狐の念話は「独り言」ではないので影響しません。
<a href="human.php#dummy_common">夢共有者</a>や<a href="human.php#mind_scanner">さとり</a>が元々見えない発言は見えません。
<a href="wolf.php#wise_wolf">賢狼</a>の能力にも変化はありません。
</pre>
<h4>関連役職</h4>
<pre>
<a href="lovers.php#sweet_cupid">弁財天</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#howl_ringing">吠耳鳴</a>の対恋人バージョンです。
</pre>

<h3><a id="deep_sleep">爆睡者</a> [Ver. 1.4.0 β14～]</h3>
<pre>
共有の囁き・人狼の遠吠えが一切見えなくなります。
他の耳鳴系と重複していても表示されません。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#mind_scanner">さとり</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#howl_ringing">吠耳鳴</a>の逆アプローチです。
</pre>

<h3><a id="silent">無口</a> [Ver. 1.4.0 α14～]</h3>
<pre>
発言の文字数に制限がかかります (制限を越えるとそれ以降が「……」になります)。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/51" target="_top">新役職考案スレ</a> の 51 が原型です。
よほど長い名前の人でもない限り、最低限の占い師のCO等には
影響が出ない程度にしてあります。
</pre>

<h3><a id="mower">草刈り</a> [Ver. 1.4.0 α23～]</h3>
<pre>
発言から「w」が削られます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
いわゆる「(w」に当たるかどうかの判定をしていないので
場合によっては名前を呼ぶ事ができない可能性もあります。
とっても理不尽ですね。
</pre>

<h2><a id="mind_read_group">サトラレ系 (夜発言公開系)</a></h2>
<p>
<a href="#mind_read_rule">基本ルール</a>
</p>
<p>
<a href="#mind_read">サトラレ</a>
<a href="#mind_open">公開者</a>
<a href="#mind_receiver">受信者</a>
<a href="#mind_friend">共鳴者</a>
<a href="#mind_sympathy">共感者</a>
<a href="#mind_evoke">口寄せ</a>
<a href="#mind_presage">受託者</a>
<a href="#mind_lonely">はぐれ者</a>
</p>

<h3><a id="mind_read_rule">基本ルール</a> [サトラレ系]</h3>
<ol>
  <li><a href="../chaos.php#secret_sub_role">サブ役職非公開</a>設定でも必ず表示されます。</li>
  <li>死者の発言を直接見ることはできません。</li>
  <li>効力を失っても役職表示は消えません。</li>
</ol>

<h3><a id="mind_read">サトラレ</a> [Ver. 1.4.0 α21～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
夜の発言が<a href="human.php#mind_scanner">さとり</a>に見られてしまいます。
</pre>
<ol>
  <li>2 日目の朝から表示されて、その夜以降から効力が適用されます。</li>
  <li>夜の発言に常時「～の独り言」が付きます。</li>
  <li>誰に見られているのかは分かりません。</li>
  <li>死亡した<a href="human.php#mind_scanner">さとり</a>は自分の<a href="#mind_read">サトラレ</a>の発言を見ることができなくなります。</li>
  <li>自分が<a href="human.php#unconscious">無意識</a>の場合は無効化されます。</li>
</ol>
<h5>Ver. 1.4.0 β7～</h5>
<pre>
夜の発言に常時「～の独り言」が付きます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#mind_scanner">さとり</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/4" target="_top">新役職考案スレ</a> の 4 が原型です。
夜に相談できる人外にこれが付くとかなり大変になると思われます。
</pre>

<h3><a id="mind_open">公開者</a> [Ver. 1.4.0 α22～]</h3>
<pre>
夜の発言が参加者全員に見られてしまいます。
</pre>
<ol>
  <li>初日の夜から表示されますが、効力が適用されるのは 2 日目の夜からになります。</li>
  <li>夜の発言に常時「～の独り言」が付きます。</li>
</ol>
<h5>Ver. 1.4.0 β7～</h5>
<pre>
初日の夜の発言は見えなくなりました。
夜の発言に常時「～の独り言」が付きます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="chiroptera.php#light_fairy">光妖精</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#mind_read">サトラレ</a>をパワーアップしてみました。
夜に相談できる役職の人には大迷惑ですね。
くれぐれもうっかり自分の役職をつぶやかないように注意してください。
</pre>

<h3><a id="mind_receiver">受信者</a> [Ver. 1.4.0 α22～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
特定の人の夜の発言を見ることができます。
</pre>
<ol>
  <li>2 日目の朝から表示されて、その夜以降から効力が適用されます。</li>
  <li>誰の発言を見ているのか分かります。</li>
</ol>
</pre>
<h4>関連役職</h4>
<pre>
<a href="lovers.php#self_cupid">求愛者</a>・<a href="lovers.php#moon_cupid">かぐや姫</a>・<a href="lovers.php#mind_cupid">女神</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#mind_scanner">さとり</a> - <a href="#mind_read">サトラレ</a>の逆バージョンです。
<a href="human.php#common_group">共有者</a>などの会話に混ざって表示されるのでうっかり
返事をしないように気をつけましょう。
</pre>

<h3><a id="mind_friend">共鳴者</a> [Ver. 1.4.0 α23～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
特定の人と夜に会話できるようになります。
会話できる相手は味方 (同一陣営) です (例外は「恋人 - 非恋人」の組み合わせ)。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#emerald_wolf">翠狼</a>・<a href="fox.php#emerald_fox">翠狐</a>・<a href="lovers.php#mind_cupid">女神</a>・<a href="lovers.php#sweet_cupid">弁財天</a>・<a href="mania.php#unknown_mania_group">鵺系</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
互いに認識できる<a href="#mind_receiver">受信者</a>です。
共有者が<a href="mania.php#unknown_mania">鵺</a>と<a href="lovers.php#mind_cupid">女神</a>に同時に矢を撃たれた場合は、誰が誰の発言が
見えるのか、非常にややこしい状況になりますね。
</pre>

<h3><a id="mind_sympathy">共感者</a> [Ver. 1.4.0 β8～]</h3>
<h4>[役職表示] 2 日目限定</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
お互いの役職を知ることができます。
役職表示が出るのは 2 日目だけです。
</pre>
<h4>関連役職</h4>
<pre>
<a href="lovers.php#angel_group">天使系</a>・<a href="#possessed_exchange">交換憑依</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他の国に実在するサブ役職です。
配置次第では<a href="#mind_friend">共鳴者</a>以上の効果を得ることが出来るでしょう。
</pre>

<h3><a id="mind_evoke">口寄せ</a> [Ver. 1.4.0 β2～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
死後に<a href="human.php#evoke_scanner">イタコ</a>の遺言窓にメッセージを送れます。
</pre>
<ol>
  <li>生きている時から表示されます (死んでも表示されます)。</li>
  <li>生きている間は通常通り自分の遺言窓が更新されます。</li>
  <li>死んでから「遺言を残す」で発言すると<a href="human.php#evoke_scanner">イタコ</a>の遺言窓が更新されます。</li>
  <li><a href="human.php#reporter">ブン屋</a>・<a href="#no_last_words">筆不精</a>など、生きている間は遺言を残せない役職でも有効です。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="human.php#evoke_scanner">イタコ</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
霊界から一方的にメッセージを送ることができます。
当然ですが、霊界オフモードにしないと機能しません。
</pre>

<h3><a id="mind_presage">受託者</a> [Ver. 1.4.0 β18～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
付加した<a href="human.php#presage_scanner">件</a>が人狼に襲撃されて死亡したら誰に襲撃されたかメッセージが表示されます。
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#reporter">ブン屋</a>・<a href="human.php#presage_scanner">件</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
テーマは「ダイイング・メッセージを受け取る人」です。
</pre>

<h3><a id="mind_lonely">はぐれ者</a> [Ver. 1.4.0 β8～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
仲間が分からなくなり、会話できなくなります。
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#blue_wolf">蒼狼</a>・<a href="fox.php#blue_fox">蒼狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
人外同士の情報戦を多様化させる事が狙いです。
分かっていても堂々と告発できないジレンマにどう対処するのかがポイントです。
</pre>

<h2><a id="lovers_group">恋人系</a></h2>
<p>
<a href="#lovers">恋人</a>
<a href="#challenge_lovers">難題</a>
<a href="#possessed_exchange">交換憑依</a>
</p>

<h3><a id="lovers">恋人</a> [Ver. 1.2.0～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
勝利条件が<a href="lovers.php">恋人陣営</a>に変化します。
表示されている相手が死亡すると自分も死亡します。
</pre>

<h3><a id="challenge_lovers">難題</a> [Ver. 1.4.0 β11～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<h4>[耐性] 人狼襲撃：無効 / 暗殺：反射</h4>
<pre>
4 日目夜までは以下の耐性を持つ。
</pre>
<ol>
  <li><a href="wolf.php#wolf_group">人狼</a>の襲撃無効</li>
  <li>毒・<a href="human.php#doom_doll">蓬莱人形</a>・<a href="human.php#brownie">座敷童子</a>・<a href="human.php#cursed_brownie">祟神</a>・<a href="fox.php#miasma_fox">蟲狐</a>の能力の対象外</li>
  <li><a href="human.php#assassin_spec">暗殺反射</a></li>
  <li><a href="wolf.php#miasma_mad">土蜘蛛</a>の能力無効</li>
  <li><a href="vampire.php#vampire_do_spec">吸血死</a>無効</li>
</ol>
<pre>
5 日目以降は恋人の相方と同じ人に投票しないとショック死する。
複数の恋人がいる場合は誰か一人と同じならショック死しない。
</pre>
<h5>Ver. 1.4.0 β13～</h5>
<pre>
<a href="wolf.php#miasma_mad">土蜘蛛</a>の能力無効
</pre>
<h4>関連役職</h4>
<pre>
<a href="lovers.php#moon_cupid">かぐや姫</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
かぐや姫の不老不死の秘薬の伝説がモチーフです。
</pre>

<h3><a id="possessed_exchange">交換憑依</a> [Ver. 1.4.0 β11～]</h3>
<h4>[役職表示] 2 日目限定</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
指定された相手と入れ替わります。
</pre>
<ol>
  <li>憑依先の相手と完全に入れ替わります。実質他人にログインしているような状態です。</li>
  <li>2 日目に入れ替わる相手が誰か予告が表示されて、3 日目に入れ替えが実行されます。</li>
  <li><a href="#mind_sympathy">共感者</a>が付加されるので事前に相手の役職が分かります。</li>
  <li>交換憑依が発生した二人は死亡しても遺言が表示されません。</li>
  <li>入れ替え前に遺言を残しておくと、入れ替わった後で相方にメッセージを残せる事になります。</li>
</ol>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
役職名の表示
</pre>
<h4>関連役職</h4>
<pre>
<a href="lovers.php#exchange_angel">魂移使</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="lovers.php#exchange_angel">魂移使</a>の処理用に実装されたサブ役職です。
</pre>

<h2><a id="joker_group">ジョーカー系</a></h2>
<p>
<a href="#joker">ジョーカー</a>
</p>

<h3><a id="joker">ジョーカー</a> [Ver. 1.4.0 β21～]</h3>
<h4>[配役制限] ババ抜き村専用</h4>
<pre>
ゲーム終了時に所持している場合、引き分け以外は無条件で敗北扱いになる。
</pre>
<ol>
  <li>処刑投票先に移動する。</li>
  <li>移動判定は処刑者が決定した後。</li>
  <li>移動判定時に死亡している人には移動しない。</li>
  <li>所持者が処刑された場合は前日所持者以外の投票者からランダムに移動する。<br>
    ただし、昼の処刑直後にゲームが終了した場合は移動しない。
  </li>
  <li>投票先が処刑された場合は移動しない。<br>
    ただし、昼の処刑直後にゲームが終了した場合は移動する。
  </li>
  <li>投票先から投票された場合、投票先には移動しない。</li>
  <li>投票先から投票されていた場合、他の自分に投票した人からランダムに移動する。</li>
  <li>前日にジョーカーを所持していた人には移動しない。</li>
  <li>自分が処刑された直後にゲームが終了した場合は本人が所持したままになる。</li>
  <li>移動先候補がない場合は本人が引き続き所持する。</li>
  <li>所持者が死亡した場合は生存者の誰か (例外なし) に移動する。</li>
  <li>夜→昼の投票処理でゲーム終了した場合は所持者が死亡していても移動しない。</li>
  <li>陣営勝利 + 単独生存を達成した場合はジョーカーを所持しても勝利扱い。</li>
</ol>
<h4>移動パターン例</h4>
<pre>
1. 標準パターン例：ジョーカー所持者は「J」、処刑者は「A」とする。
例1-1) J → B → A
J から B に移動する

例1-2) J → B → J
相互投票だった場合は移動しない。

例1-3) C → J → B → J
J から C に移動する (B は候補から除外)。

例1-4) J → A、 B → J、C → J
J は処刑者に投票しているので A には移動しないが、
B と C が J に投票しているので B と C のどちらかに移動する。

例1-5) J → A、 B → J、C(ショック死) → J
この場合は、C も対象から除外されるので B に移動する。

2. 特殊移動パターン例：ジョーカー所持者 (J) が処刑された場合
例2-1) J → A、A → J、B → J、C → J
ジョーカー投票者の A、B、C の誰かに移動する。
処刑された場合は相互投票判定が入らないので A も対象者になる。

例2-2)  J → A、A(前日所持者) → J、B(ショック死) → J、C → J
なんらかの理由で死亡していた場合は移動対象外になる。
また、処刑者から移動する場合は前日所持者は対象外。
結果として、C に移動する。

例2-3)  J → A、A(前日所持者) → J、B(ショック死) → J、C(毒死) → J
この場合は、処刑者からの移動は成立しない。
結果として、生存者から完全ランダムで移動する。

3. 最終日移動例
例3-1) J[村人][ジョーカー] A[人狼] B[村人]
この場合、誰を吊ってもゲーム終了するので、J は A か B を吊ることができれば
投票先に移動することになるが、自分が吊られた場合は自分が所持したままとなる。
いずれにしても、勝利するには J と B が A に投票する必要がある。

例3-2) J[人狼][ジョーカー] A[村人] B[村人]
J と A の相互投票、B が A に投票した場合、通常はジョーカーは移動しないが
最終日は例外的に処刑者に移動させることができるので、J は自分が吊られなければ
J の投票先にジョーカーが移動し、人狼陣営 + 自分自身も勝利できる。

例3-3) J[人狼][ジョーカー] A[狂人] B[狂人]
通常は PP で A、B どちらを吊っても全員勝利となるが、
J の投票先にジョーカーが移動することになるので A か B のどちらかは敗北扱いとなる。

例3-4) J[村人][ジョーカー] A[人狼] B[村人] C[村人]
A → J、B → A、C → A と投票していたとすると、J はどこに投票しても
A が処刑されて村陣営勝利、J の投票先にジョーカーが移動することになる。
B か C に投票した場合は投票された人だけが敗北扱いになってしまう。

例3-5) J[村人][ジョーカー] A[人狼] B[埋毒者] C[出題者]
A-C が J に投票して、J が処刑、ランダム移動で C にジョーカーが移動したとする。
夜に A が C を襲撃した場合は A と B が生存し、C がジョーカーを所持して人狼陣営勝利、
A が B を襲撃した場合は A が毒死して C の単独生存、つまり出題者陣営勝利となる。
この場合、C がジョーカーを所持したままだが、C は勝利扱いとなる。
</pre>

<h2><a id="other_group">その他</a></h2>
<p>
<a href="#protected">庇護者</a>
<a href="#possessed_target">憑依者</a>
<a href="#possessed">憑依</a>
<a href="#bad_status">悪戯</a>
<a href="#infected">感染者</a>
<a href="#psycho_infected">洗脳者</a>
<a href="#changed_therian">元獣人</a>
<a href="#copied">元神話マニア</a>
<a href="#copied_trick">元奇術師</a>
<a href="#copied_soul">元覚醒者</a>
<a href="#copied_teller">元夢語部</a>
<a href="#lost_ability">能力喪失</a>
</p>

<h3><a id="protected">庇護者</a> [Ver. 1.4.0 β18～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<h4>[耐性] 人狼襲撃：身代わり</h4>
<h4>[身代わり能力] 庇護者付加者</h4>
<pre>
<a href="wolf.php#wolf_group">人狼</a> (種類は問わない) に襲撃された時に、庇護者を付加した人が身代わりで死亡する。
判定は<a href="#challenge_lovers">難題</a>の後で、基本役職の能力による人狼襲撃耐性判定 (例：<a href="human.php#escaper">逃亡者</a>・<a href="fox.php#fox">妖狐</a>・<a href="chiroptera.php#boss_chiroptera">大蝙蝠</a>) 
よりも優先される。
</pre>
<ol>
  <li>身代わりが発生した場合、<a href="wolf.php#wolf_group">人狼</a>の襲撃は失敗扱い。</li>
  <li>代わりに死んだ人の死因は「誰かの犠牲となって死亡したようです」。</li>
  <li>本人は身代わりが発生しても分からない。</li>
  <li>身代わり君か、襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合、身代わり能力は無効。</li>
</ol>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
身代わり能力者を与えるサブ役職です。誰が誰を、という判定はしていないので
<a href="wolf.php#silver_wolf">銀狼</a>が仲間を襲撃した場合でも発生します。
</pre>

<h3><a id="possessed_target">憑依者</a> [Ver. 1.4.0 α24～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
憑依能力者が誰かに憑依したら付加されます。
</pre>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
役職名の表示
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>・<a href="#possessed">憑依</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
憑依システム用に実装されたサブ役職です。
恋人システムの応用で実装されています。
</pre>

<h3><a id="possessed">憑依</a> [Ver. 1.4.0 α24～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
憑依能力者に憑依されている人に付加されます。
</pre>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
役職名の表示
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>・<a href="#possessed_target">憑依者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
憑依システム用に実装されたサブ役職です。
</pre>

<h3><a id="bad_status">悪戯</a> [Ver. 1.4.0 β6～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
一部の<a href="chiroptera.php#fairy_group">妖精系</a>などに悪戯されている人に付加されます。
</pre>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
役職名の表示
</pre>
<h4>関連役職</h4>
<pre>
<a href="wolf.php#enchant_mad">狢</a>・<a href="wolf.php#amaze_mad">傘化け</a>・<a href="chiroptera.php#fairy_group">妖精系</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
悪戯システム用に実装されたサブ役職です。
<a href="chiroptera.php#flower_fairy">花妖精</a>・<a href="chiroptera.php#star_fairy">星妖精</a>・<a href="chiroptera.php#mirror_fairy">鏡妖精</a>以外の<a href="chiroptera.php#fairy_group">妖精系</a>などで使用されています。
</pre>

<h3><a id="infected">感染者</a> [Ver. 1.4.0 β14～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
<a href="vampire.php">吸血鬼陣営</a>の人に襲撃された人に付加されます。
</pre>
<h5>Ver. 1.4.0 β15～</h5>
<pre>
役職名の表示
</pre>
<h4>関連役職</h4>
<pre>
<a href="vampire.php">吸血鬼陣営</a>・<a href="#psycho_infected">洗脳者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="vampire.php">吸血鬼陣営</a>の勝敗判定用に実装されたサブ役職です。
</pre>

<h3><a id="psycho_infected">洗脳者</a> [Ver. 1.4.0 β20～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
<a href="vampire.php">吸血鬼陣営</a>共通の<a href="#infected">感染者</a>扱いです。
<a href="vampire.php">吸血鬼陣営</a>と<a href="ogre.php#sacrifice_ogre">酒呑童子</a>は村にいる洗脳者が誰か分かります。
<a href="ogre.php#sacrifice_ogre">酒呑童子</a>が<a href="wolf.php#wolf_group">人狼</a>に襲撃された場合は身代わりで死亡します。
</pre>
<h4>関連役職</h4>
<pre>
<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="vampire.php">吸血鬼陣営</a>支援能力者用に実装されたサブ役職です。
</pre>

<h3><a id="changed_therian">元獣人</a> [Ver. 1.4.0 β15～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
人狼に変化した後の<a href="wolf.php#therian_mad">獣人</a>に付加されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#therian_mad">獣人</a>用に実装されたサブ役職です。
</pre>

<h3><a id="copied">元神話マニア</a> [Ver. 1.4.0 α11～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
コピー後の<a href="mania.php#mania">神話マニア</a>に付加されます。
</pre>
<h5>Ver. 1.4.0 β9～10</h5>
<pre>
コピー後の<a href="mania.php#trick_mania">奇術師</a>にも付加されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="mania.php#mania">神話マニア</a>用に実装されたサブ役職です。
</pre>

<h3><a id="copied_trick">元奇術師</a> [Ver. 1.4.0 β11～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
コピー後の<a href="mania.php#trick_mania">奇術師</a>に付加されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
交換コピーの発動をログで確認できるようにするために後から実装されたサブ役職です。
</pre>

<h3><a id="copied_soul">元覚醒者</a> [Ver. 1.4.0 β11～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
コピー後の<a href="mania.php#soul_mania">覚醒者</a>に付加されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他の<a href="mania.php#mania_group">神話マニア系</a>とログで区別できるようにするために実装されたサブ役職です。
</pre>

<h3><a id="copied_teller">元夢語部</a> [Ver. 1.4.0 β11～]</h3>
<h4>[役職表示] 表示無し</h4>
<h4>[配役制限] 役職付加専用</h4>
<pre>
コピー後の<a href="mania.php#dummy_mania">夢語部</a>に付加されます。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="human.php#psycho_mage">精神鑑定士</a>や<a href="wolf.php#dream_eater_mad">獏</a>の判定に影響しないように他の元マニア系とは違い、完全に別名にしてあります。
</pre>

<h3><a id="lost_ability">能力喪失</a> [Ver. 1.4.0 α13～]</h3>
<h4>[配役制限] 役職付加専用</h4>
<pre>
発動制限付き能力者が能力を失った場合に付加されます。
</pre>
<h5>Ver. 1.4.0 β11～</h5>
<pre>
役職名の表示
</pre>
<h4>関連役職</h4>
<pre>
<a href="human.php#revive_priest">天人</a>・<a href="human.php#fend_guard">忍者</a>・<a href="human.php#revive_pharmacist">仙人</a>・<a href="human.php#revive_brownie">蛇神</a>・<a href="human.php#phantom_doll">倫敦人形</a>・<a href="human.php#revive_doll">西蔵人形</a>
<a href="wolf.php#phantom_wolf">幻狼</a>・<a href="wolf.php#resist_wolf">抗毒狼</a>・<a href="wolf.php#tongue_wolf">舌禍狼</a>・<a href="wolf.php#trap_mad">罠師</a>・<a href="wolf.php#possessed_mad">犬神</a>
<a href="fox.php#phantom_fox">幻狐</a>・<a href="fox.php#emerald_fox">翠狐</a>・<a href="fox.php#revive_fox">仙狐</a>・<a href="fox.php#possessed_fox">憑狐</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="wolf.php#tongue_wolf">舌禍狼</a>用に実装されたサブ役職ですが、汎用的に使用されています。
一部の能力者は<a href="human.php#seal_medium">封印師</a>の能力の対象になります。
</pre>
</body></html>
