<?php

namespace App\View;

class MainLayoutComposer
{
    private $themeColor;
    private $bodyClassArray = [];

    public function __construct()
    {
        // Fetch company settings
        $company_settings = getCompanyAllSetting(creatorId());

        // Determine the theme color
        $color            = ! empty($company_settings['color']) ? $company_settings['color'] : 'theme-1';
        $this->themeColor = (isset($company_settings['color_flag']) && $company_settings['color_flag'] === 'true')
            ? 'custom-color'
            : $color;

        // Determine body classes
        $this->setBodyClasses();
    }

    public function compose($view)
    {
        // Pass the variables to the main view
        $view->with([
            'bodyClasses'  => $this->getBodyClasses(),
            'isRTLVersion' => $this->isRTL(),
        ]);
    }

    private function setBodyClasses()
    {
        // Fetch additional data
        $currantLang           = auth()->user()->lang;
        $currentControllerName = current_controller();
        $currentMethodName     = current_method();

        // Build the body class array
        if ($currentControllerName) {
            $this->bodyClassArray[] = $currentControllerName;
        }
        if ($currentMethodName) {
            $this->bodyClassArray[] = $currentMethodName;
        }
        if ($currantLang) {
            $this->bodyClassArray[] = $currantLang;
        }

        if ($currentControllerName === 'project' && $currentMethodName === 'project_progress') {
            $this->bodyClassArray[] = 'no-sidebar';
        }
    }

    private function getBodyClasses()
    {
        return implode(' ', $this->bodyClassArray) . ' ' . $this->themeColor;
    }

    private function isRTL()
    {
        return ($company_settings['site_rtl'] ?? 'off') === 'on' ? 'rtl' : '';
    }
}
