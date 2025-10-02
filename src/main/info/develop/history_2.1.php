<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('info_functions');
InfoHTML::OutputHeader('開発履歴 / 2.1', 1, 'develop_history');
?>
<p><a href="history.php">最新情報</a></p>
<p>
Ver. 2.1.x<br>
<a href="#ver211">1</a>
</p>
<p>
Ver. 2.1.0<br>
<a href="#ver210rc1">RC1</a>
<a href="#ver210rc2">RC2</a>
<a href="#ver210">Release</a>
</p>
<p>
<a href="#ver210a1">α1</a>
<a href="#ver210a2">α2</a>
<a href="#ver210a3">α3</a>
<a href="#ver210a4">α4</a>
<a href="#ver210a5">α5</a>
<a href="#ver210a6">α6</a>
<a href="#ver210b1">β1</a>
<a href="#ver210b2">β2</a>
<a href="#ver210b3">β3</a>
<a href="#ver210b4">β4</a>
</p>

<h2 id="ver211">Ver. 2.1.1 (Rev. 801) : 2013/05/19 (Sun) 04:10</h2>
<ul>
<li>狼視点モード実装</li>
</ul>

<h2 id="ver210">Ver. 2.1.0 (Rev. 690) : 2012/12/24 (Mon) 00:00</h2>
<ul>
<li>村立て限定の制限機能実装</li>
<li>ライブラリの整備</li>
</ul>

<h2 id="ver210rc2">Ver. 2.1.0 RC2 (Rev. 673) : 2012/12/01 (Sat) 16:52</h2>
<ul>
<li>固定配役追加モード：O(陰陽村)追加</li>
</ul>

<h2 id="ver210rc1">Ver. 2.1.0 RC1 (Rev. 662) : 2012/11/10 (Sat) 00:12</h2>
<ul>
<li>村オプション変更機能 (GM 専用) 実装</li>
</ul>

<h2 id="ver210b4">Ver. 2.1.0 β4 (Rev. 644) : 2012/10/14 (Sun) 23:07</h2>
<ul>
<li>Twitter API の仕様変更に対応</li>
</ul>

<h2 id="ver210b3">Ver. 2.1.0 β3 (Rev. 640) : 2012/09/17 (Mon) 01:32</h2>
<ul>
<li>「舌禍狼登場」「決着村」オプション実装</li>
<li>固定配役追加モード：N(罠村)追加</li>
</ul>

<h2 id="ver210b2">Ver. 2.1.0 β2 (Rev. 635) : 2012/09/10 (Mon) 00:35</h2>
<ul>
<li>「尸解仙」仕様変更</li>
<li>ユーザ名表示オプション (ゲーム内) 実装</li>
<li>トップページのデザイン変更</li>
</ul>

<h2 id="ver210b1">Ver. 2.1.0 β1 (Rev. 629) : 2012/08/22 (Wed) 00:10</h2>
<ul>
<li>アイコン表示オプション (ゲーム内) 実装</li>
</ul>

<h2 id="ver210a7">Ver. 2.1.0 α7 (Rev. 624) : 2012/08/13 (Mon) 01:32</h2>
<ul>
<li>「ユーザ名必須」「トリップ必須」オプション実装</li>
<li>「犬神」「憑狐」仕様変更</li>
<li>「百々爺」「牡丹灯籠」実装</li>
</ul>

<h2 id="ver210a6">Ver. 2.1.0 α6 (Rev. 622) : 2012/07/28 (Sat) 23:55</h2>
<ul>
<li>固定配役追加モード：M(暗殺村)追加</li>
<li>「精神感応者」仕様変更</li>
<li>「掃除屋」「仕事人」「昼狐」「金剛夜叉」「修験者」「印狼」実装</li>
<li>サブ役職「元昼狐」実装</li>
</ul>

<h2 id="ver210a5">Ver. 2.1.0 α5 (Rev. 618) : 2012/07/16 (Mon) 23:26</h2>
<ul>
<li>出現率変動モード：H (無毒村) 追加</li>
<li>「雪狼」「雪狐」「紫狼」「紫狐」実装</li>
<li>制限時間超過時に処刑投票済み人数が表示される仕様に変更</li>
</ul>

<h2 id="ver210a4">Ver. 2.1.0 α4 (Rev. 614) : 2012/07/07 (Sat) 21:20</h2>
<ul>
<li>固定追加配役モード：K(覚醒村)・仕様変更</li>
<li>「火狼」仕様変更</li>
<li>「欺狼」実装</li>
</ul>

<h2 id="ver210a3">Ver. 2.1.0 α3 (Rev. 608) : 2012/06/24 (Sun) 06:21</h2>
<ul>
<li>「縁切地蔵」「蛇姫」仕様変更</li>
<li>「朔狼」実装</li>
<li>サブ役職「元朔狼」「告白」実装</li>
<li>背景画像変更：関連サーバ村情報</li>
<li>初期化処理を再設計</li>
</ul>

<h2 id="ver210a2">Ver. 2.1.0 α2 (Rev. 560) : 2012/04/30 (Mon) 16:16</h2>
<ul>
<li>「長老」「老兵」仕様変更</li>
<li>「傾奇者」実装</li>
<li>システムクラスを再設計</li>
</ul>

<h2 id="ver210a1">Ver. 2.1.0 α1 (Rev. 542) : 2012/04/17 (Tue) 01:10</h2>
<ul>
<li>Ver. 1.5.2 から「因幡兎」を移植</li>
</ul>
</body>
</html>
