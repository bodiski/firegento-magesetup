<?php
/**
 * @loadFixture registry
 */
class FireGento_MageSetup_Test_Block_Price extends EcomDev_PHPUnit_Test_Case_Controller
{
    protected function setUp()
    {
        parent::setUp();
        $this->guestSession();
    }

    /**
     * @dataProvider dataPriceBlock
     */
    public function testPriceBlock($config, $shippingUrl, $productData, $expectedHtml, $unexpectedHtml, $expectedEvent = true)
    {
        $this->configFixture($config);
        $this->stubHelper($shippingUrl);
        $this->registerProduct($productData);

        $priceBlockHtml = $this->renderPriceBlock();

        foreach ($expectedHtml as $label => $expected) {
            $this->assertRegExp("{{$expected}}", $priceBlockHtml, "Expected: $label");
        }
        foreach ($unexpectedHtml as $label => $unexpected) {
            $this->assertNotRegExp("{{$unexpected}}", $priceBlockHtml, "Not expected: $label");
        }
        if ($expectedEvent) {
            $this->assertEventDispatched('magesetup_after_product_price');
        }
    }

    protected function setConfig($path, $value)
    {
        Mage::getConfig()->setNode($path, $value);
    }

    protected function resetConfig()
    {
        foreach (Mage::app()->getStores(true) as $store) {
            $store->resetConfig();
        }
    }

    public static function dataPriceBlock()
    {
        $defaultConfig = [
            'stores/default/catalog/price/display_block_below_price' => 1,
            'stores/default/catalog/price/display_product_weight' => 1,
            'stored/default/catalog/price/weight_unit' => 'kg',
            'stores/default/'.Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
        ];
        $defaultSimpleProduct = [
            'type_id' => 'simple',
            'can_show_price' => true,
            'price' => 10.0,
            'tax_percent' => 19.0,
            'weight' => 42,
            'tax_class_id' => 1
        ];
        $virtualProduct = [
            'type_id' => 'virtual',
            'weight' => 0,
        ] + $defaultSimpleProduct;
        $downloadableProduct = [
            'type_id' => 'downloadable',
            'weight' => 0,
        ] + $defaultSimpleProduct;
        return [
            'not configured' => [
                'config' => ['stores/default/catalog/price/display_block_below_price' => '0'] + $defaultConfig,
                'shipping url' => true,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                ],
                'unexpected html' => [
                    'additional tax details container' => '<span class="tax-details">',
                    'formatted tax rate' => '19%',
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted shipping weight' => '42 kg',
                ],
                'expected event' => false,
            ],
            'simple product, both, no shipping' => [
                'config' => $defaultConfig,
                'shipping url' => false,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                ],
                'unexpected html' => [
                    'additional tax details container' => '<span class="tax-details">',
                    'formatted tax rate' => '19%',
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted shipping weight' => '42 kg',
                ],
                'expected event' => false,
            ],
            'simple product, both, shipping' => [
                'config' => $defaultConfig,
                'shipping url' => true,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                    'formatted shipping weight' => '42 kg',
                ],
                'unexpected html' => [
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted tax rate' => '19%',
                ],
                'expected event' => false,
            ],
            'simple product, incl tax, no shipping' => [
                'config' => ['stores/default/'.Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX] + $defaultConfig,
                'shipping url' => false,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                    'formatted tax rate' => '19%',
                ],
                'unexpected html' => [
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted shipping weight' => '42 kg',
                ],
                'expected event' => true,
            ],
            'simple product, incl tax, shipping' => [
                'config' => ['stores/default/'.Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX] + $defaultConfig,
                'shipping url' => true,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted tax rate' => '19%',
                    'formatted shipping weight' => '42 kg',
                ],
                'unexpected html' => [
                ],
                'expected event' => true,
            ],
            'simple product, excl tax, shipping' => [
                'config' => ['stores/default/'.Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Mage_Tax_Model_Config::DISPLAY_TYPE_EXCLUDING_TAX] + $defaultConfig,
                'shipping url' => true,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted tax rate' => '19%',
                    'formatted shipping weight' => '42 kg',
                ],
                'unexpected html' => [
                ],
                'expected event' => true,
            ],
            'virtual product, incl tax, shipping' => [
                'config' => ['stores/default/'.Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX] + $defaultConfig,
                'shipping url' => true,
                'product' => $virtualProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                    'formatted tax rate' => '19%',
                ],
                'unexpected html' => [
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted shipping weight' => '0 kg',
                ],
                'expected event' => true,
            ],
            'downloadable product, incl tax, shipping' => [
                'config' => ['stores/default/'.Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX] + $defaultConfig,
                'shipping url' => true,
                'product' => $downloadableProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                    'formatted tax rate' => '19%',
                ],
                'unexpected html' => [
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted shipping weight' => '0 kg',
                ],
                'expected event' => true,
            ],
            'simple product, both, shipping, no weight' => [
                'config' => ['stores/default/catalog/price/display_product_weight' => 0] + $defaultConfig,
                'shipping url' => true,
                'product' => $defaultSimpleProduct,
                'expected html' => [
                    'default price template container' => '<div class="price-box">',
                    'additional tax details container' => '<span class="tax-details">',
                ],
                'unexpected html' => [
                    'shipping cost details container' => '<span class="shipping-cost-details">',
                    'formatted tax rate' => '19%',
                    'formatted shipping weight' => '42 kg',
                ],
                'expected event' => true,
            ],
        ];
    }

    /**
     * @return string
     */
    protected function renderPriceBlock()
    {
        $priceBlock = $this->app()->getLayout()->createBlock('magesetup/catalog_product_price', '', ['template' => 'catalog/product/price.phtml']);
        $priceBlock->setCacheLifetime(null);
        $priceBlockHtml = $priceBlock->toHtml();
        return $priceBlockHtml;
    }

    /**
     * @param $config
     */
    protected function configFixture($config)
    {
        $this->resetConfig();
        foreach ($config as $path => $value) {
            $this->setConfig($path, $value);
        }
    }

    /**
     * @param $shippingUrl
     */
    protected function stubHelper($shippingUrl)
    {
        $helper = $this->mockHelper('magesetup', ['getShippingCostUrl']);
        $helper->method('getShippingCostUrl')->willReturn($shippingUrl ? '/shipping' : '');
        $this->replaceByMock('helper', 'magesetup', $helper);
    }

    /**
     * @param $productData
     */
    protected function registerProduct($productData)
    {
        $product = Mage::getModel('catalog/product');
        $product->addData($productData);
        Mage::register('product', $product);
    }
}