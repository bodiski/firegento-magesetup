<?php

interface FireGento_MageSetup_Implementor_AgreementRepository
{
    /**
     * @return FireGento_MageSetup_Implementor_Agreement
     */
    public function load($storeId, $name);

    /**
     * @return void
     */
    public function save(FireGento_MageSetup_Implementor_Agreement $agreement);
}