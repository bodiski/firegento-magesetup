<?php

interface FireGento_MageSetup_Implementor_Agreement
{
    /**
     * @return bool
     */
    public function isNew();

    /**
     * @deprecated will need more explicit parameters
     * @return void
     */
    public function setData(array $data);
}