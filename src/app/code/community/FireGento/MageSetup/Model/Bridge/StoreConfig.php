<?php
class FireGento_MageSetup_Model_Bridge_StoreConfig implements FireGento_MageSetup_Implementor_StoreConfig
{
    public function saveDefaultValue($path, $value)
    {
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $setup->setConfigData($path, $value);
    }

    public function saveStoreValue($storeId, $path, $value)
    {
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $setup->setConfigData($path, $value, 'stores', $storeId);
    }

}