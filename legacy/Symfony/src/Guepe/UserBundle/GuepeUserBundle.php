<?php

namespace Guepe\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuepeUserBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
