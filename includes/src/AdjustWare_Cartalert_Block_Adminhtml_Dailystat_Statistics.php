<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.6.2
 * @license:     HURXKDG7XXFyzvUh6YIaOzBxXgSru0OEdm0JvUmsP0
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat_Statistics extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adjcartalert/statistics.phtml');
    }

    public function showStatistics()
    {
        $this->getLayout()->getBlock('head')->addItem('skin_css', 'aitoc/statistic.css');
        $collection = Mage::getResourceModel('adjcartalert/dailystat_collection');
        $countMonthCarts = 0;
        $sumMonthCarts = 0;
        $countYearCarts = 0;
        $sumYearCarts = 0;
        $recoveryMonth = 0;
        $recoveryYear = 0;
        $sumRecoveredCartsNumMonth = 0;
        $sumAbandonedCartsNumMonth = 0;
        $sumRecoveredCartsNumYear = 0;
        $sumAbandonedCartsNumYear = 0;
        foreach ($collection as $row) {
            $MonthDate = $row->getDate();
            $MonthDate = explode("-",$MonthDate);
            $nowMonth = date('m');
            $nowYear = date('Y');
            $nowDay = date('d');
            if($nowMonth == $MonthDate[1] && $nowYear == $MonthDate[0])
            {
                $countMonthCarts += $row->getRecoveredCartsNum();
                $sumMonthCarts += $row->getOrderedCartsPrice();
                $recoveryMonth += $row->getRecoveredCartsNumPercent();
                $sumRecoveredCartsNumMonth += $row->getRecoveredCartsNum();
                $sumAbandonedCartsNumMonth += $row->getAbandonedCartsNum();
            }

            if(strtotime($row->getDate())<=strtotime(date('Y-m-d'))) {
                $countYearCarts += $row->getRecoveredCartsNum();
                $sumYearCarts += $row->getOrderedCartsPrice();
                $sumRecoveredCartsNumYear += $row->getRecoveredCartsNum();
                $sumAbandonedCartsNumYear += $row->getAbandonedCartsNum();
                $recoveryYear += $row->getRecoveredCartsNumPercent();
            }
        }
        if($sumRecoveredCartsNumMonth == 0)
        {
            $MonthSum = $sumAbandonedCartsNumMonth;
        }
        else
        {
            $MonthSum = round($sumRecoveredCartsNumMonth/$sumAbandonedCartsNumMonth*100);
        }

        if($sumRecoveredCartsNumYear == 0)
        {
            $YearSum = $sumAbandonedCartsNumYear;
        }
        else
        {
            $YearSum = round($sumRecoveredCartsNumYear/$sumAbandonedCartsNumYear*100);
        }
        return array(
            $countMonthCarts,
            Mage::app()->getLocale()->currency((string)Mage::app()->getStore()->getBaseCurrencyCode())->toCurrency($sumMonthCarts),
            $countYearCarts,
            Mage::app()->getLocale()->currency((string)Mage::app()->getStore()->getBaseCurrencyCode())->toCurrency($sumYearCarts),
            $MonthSum,
            $YearSum
        );
    }

}