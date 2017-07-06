<?php

/**
 * We need to chege this one to the front. We will see!!!
 *
 * @author Ea Design
 */
class Eadesigndev_Romcity_IndexController extends Mage_Core_Controller_Front_Action
{
    public function citiesAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $cityId = (int)$this->getRequest()->getParam('city_id');
        $countryId = $this->getRequest()->getParam('country_id');

        if (!$cityId || !$countryId) {
            return;
        }

        echo Mage::helper('romcity')->getCities($countryId, $cityId);

        return;
    }
    
    public function barangayAction()
    {       
        $cityname = $this->getRequest()->getParam('cityname');
        $barangays = Mage::helper('romcity')->getBarangay($cityname);
        foreach($barangays as $barangay){
            $option = $barangay['barangay'];
            echo "<option value='".$option."'>".$option."</option>";
        }
        return;
    }    
}
