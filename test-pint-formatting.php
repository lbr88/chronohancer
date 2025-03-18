<?php

namespace App\Tests;

class TestPintFormatting
{
    public function badlyFormattedMethod()
    {
        $var = 'this is badly formatted';
        if ($var) {
            return true;
        } else {
            return false;
        }
    }
}
