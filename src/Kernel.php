<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{

    public function __construct($environment, $debug) {
        date_default_timezone_set('Asia/Manila');
        parent::__construct($environment, $debug);
      }
      
    use MicroKernelTrait;
}
