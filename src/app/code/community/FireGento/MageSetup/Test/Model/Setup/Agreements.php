<?php
class FireGento_MageSetup_Test_Model_Setup_Agreements extends EcomDev_PHPUnit_Test_Case
{
    protected $createdAgreementIds = [];

    protected function setUp()
    {
        $this->setCurrentStore('default');
        $this->configFixture();
        $this->assertFalse(
            Mage::getStoreConfigFlag('checkout/options/enable_agreements'),
            'Precondition: agreements not enabled'
        );
    }
    protected function tearDown()
    {
        $agreements = Mage::getResourceModel('checkout/agreement_collection');
        $agreements->addFieldToFilter('agreement_id', ['in' => $this->createdAgreementIds]);
        $agreements->walk('delete');
    }
    /**
     * @dataProvider dataAgreementsParams
     */
    public function testAgreementsAreCreated($params)
    {
        $setup = Mage::getSingleton('magesetup/setup');
        $setup->setup($params);

        $this->assertAgreementsCreatedByName([
            'AGB',
            'Widerrufsbelehrung',
            'Widerrufsbelehrung für Digitale Inhalte',
            'Widerrufsbelehrung für Dienstleistungen'
        ]);

    }

    /**
     * @depends testAgreementsAreCreated
     */
    public function testAgreementsAreEnabled()
    {
        $this->resetConfig();
        $this->assertTrue(
            Mage::getStoreConfigFlag('checkout/options/enable_agreements'),
            'Agreement configuration should be enabled'
        );
    }

    public static function dataAgreementsParams()
    {
        return [
            'de' => ['params' => [
                'country' => 'de',
                'cms_locale' => ['default' => 'de_DE'],
                'agreements' => '1',
            ]],
        ];
    }

    protected function setConfig($path, $value)
    {
        $this->resetConfig();
        Mage::getConfig()->setNode($path, $value);
    }

    protected function resetConfig()
    {
        foreach (Mage::app()->getStores(true) as $store) {
            $store->resetConfig();
        }
    }

    /**
     * @param $expectedAgreementNames
     */
    private function assertAgreementsCreatedByName($expectedAgreementNames)
    {
        /** @var Mage_Checkout_Model_Resource_Agreement_Collection $agreements */
        $agreements = Mage::getResourceModel('checkout/agreement_collection');
        $agreements->addFieldToFilter('name', ['in' => $expectedAgreementNames]);
        $agreements->addFieldToFilter('is_active', 1);
        $this->createdAgreementIds = $agreements->getAllIds();
        $this->assertEquals(
            count($expectedAgreementNames),
            $agreements->getSize(),
            'Agreements should be created and active as required'
        );
    }

    protected function configFixture()
    {
        $this->setConfig('stores/default/checkout/options/enable_agreements', '0');
    }
}