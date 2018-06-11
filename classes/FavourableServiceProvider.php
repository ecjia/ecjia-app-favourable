<?php

namespace Ecjia\App\Favourable;

use Royalcms\Component\App\AppServiceProvider;

class FavourableServiceProvider extends  AppServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-favourable');
    }
    
    public function register()
    {
        
    }
    
    
    
}