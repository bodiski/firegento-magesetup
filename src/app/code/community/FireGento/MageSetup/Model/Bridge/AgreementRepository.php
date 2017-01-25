<?php
class FireGento_MageSetup_Model_Bridge_AgreementRepository implements FireGento_MageSetup_Implementor_AgreementRepository
{
    public function load($storeId, $name)
    {
        $agreement = Mage::getModel('checkout/agreement')->setStoreId($storeId)->load($name, 'name');
        if (is_array($agreement->getStores()) && !in_array(intval($storeId), $agreement->getStores())) {
            $agreement = Mage::getModel('checkout/agreement');
        }
        return new FireGento_MageSetup_Model_Bridge_Agreement($agreement);
    }

    public function save(FireGento_MageSetup_Implementor_Agreement $agreement)
    {
        /** @var FireGento_MageSetup_Model_Bridge_Agreement $agreement */
        $agreement->getMagentoModel()->save();
    }

}