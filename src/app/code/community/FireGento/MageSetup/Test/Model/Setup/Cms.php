<?php

/**
 * @loadFixture registry
 */
class FireGento_MageSetup_Test_Model_Setup_Cms extends EcomDev_PHPUnit_Test_Case
{
    protected $createdPages = [];
    protected $createdBlocks = [];

    protected function setUp()
    {
        $this->setCurrentStore('default');
    }
    protected function tearDown()
    {
        $pages = Mage::getResourceModel('cms/page_collection');
        $pages->addFieldToFilter('page_id', ['in' => $this->createdPages]);
        $pages->walk('delete');
        $blocks = Mage::getResourceModel('cms/block_collection');
        $blocks->addFieldToFilter('block_id', ['in' => $this->createdBlocks]);
        $blocks->walk('delete');
    }
    /**
     * @dataProvider dataCmsParams
     */
    public function testPagesAndBlocksAreCreated($params, $expectedPageNames, $expectedBlockNames, $expectedFooterLinks,
                                                 $expectedConfiguration)
    {
        $setup = Mage::getSingleton('magesetup/setup');
        $setup->setup($params);

        $this->assertPagesCreatedByName($expectedPageNames);
        $this->assertBlocksCreatedByName($expectedBlockNames);
        $this->assertFooterBlockCreatedWithLinks($expectedFooterLinks);
        $this->assertBlocksAreConfigured($expectedConfiguration);
    }


    private function assertBlocksAreConfigured($configValues)
    {
        $this->resetConfig();
        foreach ($configValues as $path => $value) {
            $this->assertEquals(
                $value,
                Mage::getStoreConfig($path)
            );
        }
    }

    public static function dataCmsParams()
    {
        return [
            'de' => [
                'params' => [
                    'country' => 'de',
                    'cms_locale' => ['default' => 'de_DE'],
                    'cms' => '1',
                ],
                'expected page names' => [
                    'Seite nicht gefunden',
                    'AGB',
                    'Impressum',
                    'Bestellvorgang',
                    'Zahlungsarten',
                    'Datenschutz',
                    'Widerrufsbelehrung',
                    'Formular zur Widerrufsbelehrung',
                    'Lieferung',
                ],
                'expected block names' => [
                    'AGB',
                    'Widerrufsbelehrung',
                    'Formular zur Widerrufsbelehrung',
                ],
                'expected footer links' => [
                    '<a href="{{store url="impressum"}}">Impressum</a>',
                    '<a href="{{store url="zahlungsarten"}}">Zahlungsarten</a>',
                    '<a href="{{store url="datenschutz"}}">Datenschutz</a>',
                    '<a href="{{store url="lieferung"}}">Lieferung</a>',
                    '<a href="{{store url="order"}}">Bestellvorgang</a>',
                    '<a href="{{store url="agb"}}">AGB</a>',
                    '<a href="{{store url="widerrufsbelehrung"}}">Widerrufsbelehrung</a>',
                ],
                'expected configuration' => [
                    'catalog/price/cms_page_shipping' => 'lieferung',
                ]
            ],
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
     * @param $expectedPageNames
     */
    private function assertPagesCreatedByName($expectedPageNames)
    {
        /** @var Mage_Cms_Model_Resource_Page_Collection $pages */
        $pages = Mage::getResourceModel('cms/page_collection');
        $pages->addFieldToFilter('title', ['in' => $expectedPageNames]);
        $pages->addFieldToFilter('is_active', 1);
        $this->createdPages = $pages->getAllIds();
        $this->assertEquals(
            count($expectedPageNames),
            $pages->getSize(),
            'Pages should be created and active as required'
        );
    }
    /**
     * @param $expectedBlockNames
     */
    private function assertBlocksCreatedByName($expectedBlockNames)
    {
        /** @var Mage_Cms_Model_Resource_Block_Collection $blocks */
        $blocks = Mage::getResourceModel('cms/block_collection');
        $blocks->addFieldToFilter('title', ['in' => $expectedBlockNames]);
        $blocks->addFieldToFilter('is_active', 1);
        $this->createdBlocks = $blocks->getAllIds();
        $this->assertEquals(
            count($expectedBlockNames),
            $blocks->getSize(),
            'Blocks should be created and active as required'
        );
    }

    private function assertFooterBlockCreatedWithLinks($expectedLinks)
    {
        $footerBlock = Mage::getModel('cms/block')->load('footer_links');
        $actualBlockHtml = $footerBlock->getContent();
        foreach ($expectedLinks as $linkHtml) {
            $this->assertRegExp("{{$linkHtml}}", $actualBlockHtml);
        }
    }

    protected function configFixture()
    {
        $this->setConfig('stores/default/catalog/price/cms_page_shipping', '');
    }
}