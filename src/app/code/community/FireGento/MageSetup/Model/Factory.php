<?php
class FireGento_MageSetup_Model_Factory
{
    public function agreementsSetup()
    {
        return new FireGento_MageSetup_Model_Setup_Agreements(
            Mage::getModel('magesetup/bridge_storeConfig')
        );
    }

}