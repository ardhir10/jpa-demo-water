<?php

namespace App\Http\View\Composers;

use App\GlobalSetting;
use Illuminate\View\View;

class GlobalSettingComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $global_setting;

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(GlobalSetting $global_setting)
    {
        // Dependencies automatically resolved by service container...
        $this->global_setting = $global_setting;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $lastData = \App\GlobalSetting::orderBy('id', 'desc')->first();
        $global_setting = (object) array(
            'id' => isset($lastData->id) ? $lastData->id : 0,
            'websocket_host' => isset($lastData->websocket_host) ? $lastData->websocket_host : null,
            'websocket_port' => isset($lastData->websocket_host) ? $lastData->websocket_port : null,
            'websocket_pool_interval' => isset($lastData->websocket_pool_interval) ? $lastData->websocket_pool_interval : null,
        );
        
        
        $view->with('global_setting', $global_setting);
    }
}
