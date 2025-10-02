<?php
/*
  ◆出現率変動モード (boost_rate)
*/
OptionManager::Load('topping');
class Option_boost_rate extends Option_topping {
  function GetCaption() { return '出現率変動モード'; }

  function GetExplain() { return '役職の出現率に補正がかかります'; }
}
