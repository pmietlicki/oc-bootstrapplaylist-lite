<?php namespace Pmietlicki\BootstrapPlaylistLite;

use Illuminate\Foundation\AliasLoader;
use System\Classes\PluginBase;


/**
 * BootstrapPlaylist Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'BootstrapPlaylist Lite',
            'description' => 'A playlist generator and player for your website.',
            'author'      => 'Pmietlicki',
            'icon'        => 'icon-list-alt',
            'homepage'    => 'http://pascal-mietlicki.fr'
        ];
    }

    public function registerComponents()
    {
        return [
            'Pmietlicki\BootstrapPlaylistLite\Components\Player' => 'player'
        ];
    }

}
