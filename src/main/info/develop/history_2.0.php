<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('info_functions');
InfoHTML::OutputHeader('開発履歴 / 2.0', 1, 'develop_history');
?>
<p><a href="history.php">最新情報</a></p>
<p>
Ver. 2.0.x<br>
<a href="#ver201">1</a>
<a href="#ver202">2</a>
<a href="#ver203">3</a>
<a href="#ver204">4</a>
<a href="#ver205">5</a>
<a href="#ver206">6</a>
</p>
<p>
Ver. 2.0.0<br>
<a href="#ver200rc1">RC1</a>
<a href="#ver200">Release</a>
</p>
<p>
<a href="#ver200a1">α1</a>
<a href="#ver200a2">α2</a>
<a href="#ver200a3">α3</a>
<a href="#ver200a4">α4</a>
<a href="#ver200a5">α5</a>
<a href="#ver200a6">α6</a>
<a href="#ver200b1">β1</a>
</p>

<h2 id="ver206">Ver. 2.0.6 (Rev. 771) : 2013/04/23 (Tue) 00:25</h2>
<ul>
<li>ログ表示関連のバグ修正</li>
</ul>

<h2 id="ver205">Ver. 2.0.5 (Rev. 689) : 2012/12/23 (Sun) 18:25</h2>
<ul>
<li>村メンテナンス処理の最適化</li>
<li>共通 CSS を追加</li>
</ul>

<h2 id="ver204">Ver. 2.0.4 (Rev. 647) : 2012/10/14 (Sun) 22:15</h2>
<ul>
<li>Twitter API の仕様変更に対応</li>
</ul>

<h2 id="ver203">Ver. 2.0.3 (Rev. 638) : 2012/09/16 (Sun) 21:08</h2>
<ul>
<li>ユーザ名表示機能実装</li>
<li>投票ボタン位置を変更</li>
</ul>

<h2 id="ver202">Ver. 2.0.2 (Rev. 628) : 2012/08/21 (Tue) 02:24</h2>
<ul>
<li>アイコン表示機能実装</li>
</ul>

<h2 id="ver201">Ver. 2.0.1 (Rev. 607) : 2012/06/24 (Sun) 05:44</h2>
<ul>
<li>エラー処理の改定</li>
</ul>

<h2 id="ver200">Ver. 2.0.0 (Rev. 539) : 2012/04/14 (Sat) 23:32</h2>
<ul>
<li>出現率変動モード：G (独身村) 追加</li>
</ul>

<h2 id="ver200rc1">Ver. 2.0.0 RC1 (Rev. 533) : 2012/03/25 (Sun) 03:04</h2>
<ul>
<li>遺言制限機能を実装</li>
</ul>

<h2 id="ver200b1">Ver. 2.0.0 β1 (Rev. 523) : 2012/02/29 (Wed) 00:38</h2>
<ul>
<li>昼の未投票チェックを高速化</li>
</ul>

<h2 id="ver200a6">Ver. 2.0.0 α6 (Rev. 508) : 2012/02/20 (Mon) 01:37</h2>
<ul>
<li>未投票判定処理を最適化</li>
</ul>

<h2 id="ver200a5">Ver. 2.0.0 α5 (Rev. 504) : 2012/02/12 (Sun) 20:45</h2>
<ul>
<li>投票時間リセット(管理者用) 実装</li>
</ul>

<h2 id="ver200a4">Ver. 2.0.0 α4 (Rev. 495) : 2012/02/06 (Mon) 23:21</h2>
<ul>
<li>未投票判定のバグ修正</li>
</ul>

<h2 id="ver200a3">Ver. 2.0.0 α3 (Rev. 494) : 2012/02/05 (Sun) 23:15</h2>
<ul>
<li>設定ファイルを再構成</li>
<li>ログ表示機能を再設計</li>
<li>ユーザ登録情報変更機能を実装</li>
</ul>

<h2 id="ver200a2">Ver. 2.0.0 α2 (Rev. 470) : 2012/01/16 (Mon) 22:54</h2>
<ul>
<li>データベース構造(投票処理)を再設計</li>
<li>村作成画面の実装を再設計</li>
</ul>

<h2 id="ver200a1">Ver. 2.0.0 α1 (Rev. 457) : 2012/01/08 (Sun) 16:35</h2>
<ul>
<li>データベース構造を再設計</li>
</ul>
</body>
</html>
