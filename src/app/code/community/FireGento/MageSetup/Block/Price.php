<?php
class FireGento_MageSetup_Block_Price extends Mage_Core_Block_Template
{
    protected $_template = 'magesetup/price_info.phtml';


    /**
     * Get formatted weight incl. unit
     *
     * @return string Formatted weight
     */
    public function getFormattedWeight()
    {
        return floatval($this->getProduct()->getWeight()) . ' ' . Mage::getStoreConfig('catalog/price/weight_unit');
    }

    /**
     * Check if Shipping by Weight is active
     *
     * @return bool Flag if product weight should be displayed
     */
    public function getIsShowWeightInfo()
    {
        return Mage::getStoreConfigFlag('catalog/price/display_product_weight');
    }

    /**
     * Read tax rate from current product.
     *
     * @return string Tax Rate
     */
    public function getTaxRate()
    {
        $taxRateKey = 'tax_rate_' . $this->getProduct()->getId();
        if (!$this->getData($taxRateKey)) {
            $this->setData($taxRateKey, $this->_loadTaxCalculationRate($this->getProduct()));
        }

        return $this->getData($taxRateKey);
    }
    /**
     * Gets tax percents for current product
     *
     * @param  Mage_Catalog_Model_Product $product Product Model
     * @return string Tax Rate
     */
    protected function _loadTaxCalculationRate(Mage_Catalog_Model_Product $product)
    {
        $taxPercent = $product->getTaxPercent();
        if (is_null($taxPercent)) {
            $taxClassId = $product->getTaxClassId();
            if ($taxClassId) {
                $store = Mage::app()->getStore();
                $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                $group = Mage::getModel('customer/group')->load($groupId);
                $customerTaxClassId = $group->getData('tax_class_id');

                /* @var $calculation Mage_Tax_Model_Calculation */
                $calculation = Mage::getSingleton('tax/calculation');
                $request = $calculation->getRateRequest(null, null, $customerTaxClassId, $store);
                $taxPercent = $calculation->getRate($request->setProductClassId($taxClassId));
            }
        }

        if ($taxPercent) {
            return $taxPercent;
        }

        return 0;
    }

    /**
     * Retrieves formatted string of tax rate for user output
     *
     * @return string Formatted Tax Rate for the given locale
     */
    public function getFormattedTaxRate()
    {
        if ($this->getTaxRate() === null
            || $this->getProduct()->getTypeId() == 'bundle'
        ) {
            return '';
        }

        $locale = Mage::app()->getLocale()->getLocaleCode();
        $taxRate = Zend_Locale_Format::toFloat($this->getTaxRate(), array('locale' => $locale));

        return $this->__('%s%%', $taxRate);
    }

    /**
     * Returns whether or not the price contains taxes
     *
     * @return bool Flag if prices are shown with including tax
     */
    public function getIsIncludingTax()
    {
        $this->price->getIsIncludingTax();
    }

    /**
     * Returns whether or not the price contains taxes
     *
     * @return bool Flag if shipping costs are including taxes
     */
    public function getIsIncludingShippingCosts()
    {
        if (!$this->getData('is_including_shipping_costs')) {
            $this->setData(
                'is_including_shipping_costs',
                Mage::getStoreConfig('catalog/price/including_shipping_costs')
            );
        }

        return $this->getData('is_including_shipping_costs');
    }


    /**
     * Returns whether the shipping link needs to be shown
     * on the frontend or not.
     *
     * @return bool Flag if shipping link should be displayed
     */
    public function getIsShowShippingLink()
    {
        $productTypeId = $this->getProduct()->getTypeId();
        $ignoreTypeIds = array('virtual', 'downloadable');
        if (in_array($productTypeId, $ignoreTypeIds)) {
            return false;
        }

        return true;
    }

    public function getPriceDisplayType()
    {
        return Mage::helper('tax')->getPriceDisplayType();
    }

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }
    public function getProduct()
    {
        return $this->product;
    }
}