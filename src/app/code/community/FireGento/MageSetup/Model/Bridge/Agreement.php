<?php
class FireGento_MageSetup_Model_Bridge_Agreement implements FireGento_MageSetup_Implementor_Agreement
{
    /**
     * @var Mage_Checkout_Model_Agreement
     */
    private $magentoAgreement;

    /**
     * @param Mage_Checkout_Model_Agreement $magentoAgreement
     */
    public function __construct(Mage_Checkout_Model_Agreement $magentoAgreement)
    {
        $this->magentoAgreement = $magentoAgreement;
    }


    public function isNew()
    {
        return !$this->magentoAgreement->getId();
    }

    public function setData(array $data)
    {
        $this->magentoAgreement->setData($data);
    }

    public function getMagentoModel()
    {
        return $this->magentoAgreement;
    }

}