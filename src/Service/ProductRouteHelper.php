<?php

namespace App\Service;

use App\Entity\BankProduct;
use App\Entity\CreditProduct;
use App\Entity\FiscalProduct;
use App\Entity\MetaProduct;
use App\Entity\SavingsProduct;

class ProductRouteHelper
{
    public function showRoute(MetaProduct $product): string
    {
        return match (true) {
            $product instanceof BankProduct => 'app_bank_product_show',
            $product instanceof CreditProduct => 'app_credit_product_show',
            $product instanceof FiscalProduct => 'app_fiscal_product_show',
            $product instanceof SavingsProduct => 'app_savings_product_show',
            default => 'app_product_index',
        };
    }
}
