<?php

namespace App\Providers;

use Butschster\Head\Contracts\Packages\PackageInterface;
use Butschster\Head\Facades\PackageManager;
use Butschster\Head\MetaTags\Meta;
use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Butschster\Head\Contracts\Packages\ManagerInterface;
use Butschster\Head\Providers\MetaTagsApplicationServiceProvider as ServiceProvider;

class MetaTagsServiceProvider extends ServiceProvider
{
    protected function packages()
    {
        // Create your own packages here
    }

    // if you don't want to change anything in this method just remove it
    protected function registerMeta() : void
    {
        $this->app->singleton(MetaInterface::class, function () {
            $meta = new Meta(
                $this->app[ManagerInterface::class],
                $this->app['config'],
            );

            // It just an imagination, you can automatically 

            /**
             * Add favicon if it exists
             */
            $this->favicon($meta);

            // This method gets default values from config and creates tags, includes default packages, e.t.c
            // If you don't want to use default values just remove it.
            $meta->initialize();


            /**
             * Add Style CSS
             */
            $this->addStyleCss($meta);


            /**
             * Add Script JS
             */
            $this->addScriptJS($meta);


            return $meta;
        });
    }

    /**
     * Register favicon  
     */
    private function favicon($meta)
    {
        $favicon = $company_settings['favicon']
            ?? $admin_settings['favicon']
            ?? 'uploads/logo/favicon.png';

        $faviconPath = get_file($favicon);

        return $meta->setFavicon($faviconPath);
    }

    /**
     * Register CSS Style 
     */
    private function addStyleCss($meta)
    {
        $cssFiles = $this->app['config']['meta_tags']['css'] ?? [];

        foreach ($cssFiles as $css) {

            if (! preg_match('/^(http|https):\/\//', $css['url'])) {
                // If not a full URL, wrap with the asset() function
                $css['url'] = asset($css['url']);
            }

            $meta->addStyle($css['name'], $css['url'], $css['attribues'] ?? []);
        }

        // Handle RTL and Dark Layout styles dynamically
        $companySettings = $this->app['config']['company_settings'];

        $siteRtl    = $companySettings['site_rtl'] ?? 'off';
        $darkLayout = $companySettings['cust_darklayout'] ?? 'off';

        if ($siteRtl === 'on') {
            $meta->addStyle('style-rtl.css', asset('assets/css/style-rtl.css'));
        }

        if ($darkLayout === 'on') {
            $meta->addStyle('style-dark.css', asset('assets/css/style-dark.css'), ['id' => 'main-style-link']);
        }

        if ($siteRtl !== 'on' && $darkLayout !== 'on') {
            $meta->addStyle('style.css', asset('assets/css/style.css'), ['id' => 'main-style-link']);
        }


        return $meta;
    }


    /**
     * Register JS Script 
     */
    private function addScriptJS($meta)
    {
        $jsFiles = $this->app['config']['meta_tags']['js'] ?? [];

        foreach ($jsFiles as $js) {

            if (! preg_match('/^(http|https):\/\//', $js['url'])) {
                // If not a full URL, wrap with the asset() function
                $js['url'] = asset($js['url']);
            }

            $meta->addScript($js['name'], $js['url'], $js['attribues'] ?? [], $js['placement'] ?? '');
        }

        $companySettings = $this->app['config']['company_settings'];

        if (! empty($companySettings['category_wise_sidemenu']) && $companySettings['category_wise_sidemenu'] == 'on') {
            $meta->addScript("layout-tab", asset('assets/js/layout-tab.js'));
        }

        return $meta;
    }
}