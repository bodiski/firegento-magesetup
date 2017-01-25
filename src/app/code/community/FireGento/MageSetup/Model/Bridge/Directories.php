<?php
class FireGento_MageSetup_Model_Bridge_Directories implements FireGento_MageSetup_Implementor_Directories
{
    public function localizedTemplates($locale)
    {
        return Mage::getBaseDir('locale') . DS . $locale . DS . 'template' . DS;
    }

}