<?php
interface FireGento_MageSetup_Implementor_StoreConfig
{
    /**
     * @return void
     */
    public function saveDefaultValue($path, $value);

    /**
     * @return void
     */
    public function saveStoreValue($storeId, $path, $value);
}
