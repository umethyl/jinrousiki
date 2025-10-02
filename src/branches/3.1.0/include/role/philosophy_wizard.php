<?php
/*
  ◆賢者 (philosophy_wizard)
  ○仕様
  ・魔法：河童・錬金術師・蛇姫・火車・土蜘蛛・釣瓶落とし・弁財天
  ・天候：霧雨(錬金術師), 木枯らし(火車)
*/
RoleLoader::LoadFile('wizard');
class Role_philosophy_wizard extends Role_wizard {
  public $action = null;

  protected function GetWizardResultList() {
    return array(RoleAbility::PHARMACIST);
  }

  protected function GetWizardList() {
    return array(
      'alchemy_pharmacist', 'cure_pharmacist', 'miasma_jealousy', 'miasma_mad', 'critical_mad',
      'sweet_cupid', 'corpse_courier_mad'
    );
  }
}
