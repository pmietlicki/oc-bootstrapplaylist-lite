<?php namespace Pmietlicki\BootstrapPlaylistLite\Components;

use Cms\Classes\ComponentBase;
use Flash;
use ApplicationException;
use Youtube;
use StdClass;
use Exception;

class Player extends ComponentBase
{
     /**
     * @var Pmietlicki\Tubelist\Models\Playlist The playlist model used for display.
     */
    public $playlist;
    private $index = 1;

    public function componentDetails()
    {
        return [
            'name'        => 'Playlist Player',
            'description' => 'Add a playlist player widget on your page'
        ];
    }

    public function defineProperties()
    {
        return [
            'vimeoUser' => [
                'title'       => 'Vimeo User',
                'description' => 'Retrieve vimeo videos from a specific user',
                'type'        => 'string'
            ],
             'models' => [
                'title' => 'Playlist models from Tubelist',
                'description' => 'Select a predefined model from PM factory Tubelist App (http://pascal-mietlicki.fr/playlist).',
                'type' => 'dropdown'
            ],
            'tubelistId' => [
                'title' => 'Playlist Id from Tubelist',
                'description' => "Retrieve the playlist you've made on Tubelist App (http://pascal-mietlicki.fr/my-playlists) by entering read or write id.",
                'type' => 'string',
                'validationPattern' => '(^$|[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$)',
                'validationMessage' => "Please enter a valid read or write id. Go to http://pascal-mietlicki.fr/my-playlists and retrieve the id of your playlist inside the final URL (the one you'll find after /content).\nFor example, enter 20d862b0-ccf1-11e4-94cf-37144cf712d1 if the final URL is http://pascal-mietlicki.fr/playlist/content/20d862b0-ccf1-11e4-94cf-37144cf712d1"
            ],
            'title'  => [
                'title'     => 'Title',
                'description' => 'Give a name to this playlist.',
                'type'      => 'string'
            ]
        ];
    }

    public function getModelsOptions()
    {
        try
        {
            $items = file_get_contents('http://pascal-mietlicki.fr/pmietlicki/tubelist/json/models');
            $items = json_decode($items);
            $result = array();
            array_walk($items, function (&$value) use (&$result) {
                $result[$value] = urldecode($value);
            });
            return array_merge(["" => ""],$result);
        }
        catch (Exception $ex)
        {
            return ['' => ''];
        }
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->addCss('assets/css/video-js.css');
        $this->addCss('assets/css/style.css');
        
        //Include the core jQuery and jQuery UI
        $this->addJs('assets/js/jquery-1.9.1.js');
        $this->addJs('assets/js/jquery-ui.js');

        
        
       
        $this->addJs('assets/js/video.js');
        $this->addJs('assets/js/youtube.js');
        $this->addJs('assets/js/media.vimeo.js');
        $this->addJs('assets/js/dailymotion.js');
        $this->addJs('assets/js/media.soundcloud.js');
        $this->addJs('assets/js/knockout-2.3.0.js');       
        $this->addJs('assets/js/local.js');     

    }


    protected function prepareVars()
    {
        $this->playlist = (object) $this->getProperties();   
        $this->playlist->items = array();     
        $this->playlist->json = '';
        $this->playlist = $this->page['playlist'] = $this->loadPlaylist();
        
    }

    protected function loadPlaylist()
    {
        try {

            if (!empty($this->playlist->vimeoUser))
                $this->loadFromVimeoUser($this->playlist->vimeoUser);

            if (!empty($this->playlist->models))
                $this->loadPMFactoryBlogPlaylist($this->playlist->models);

            if (!empty($this->playlist->tubelistId))
                $this->loadPMFactoryBlogPlaylist($this->playlist->tubelistId);

            $element = array_filter((array) $this->playlist);

            if (empty($element))
            {
                $this->loadPMFactoryBlogPlaylist();
            }

            $this->playlist->json = json_encode($this->playlist->items);
        } catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }

        return $this->playlist;
    }

    protected function loadFromVimeoUser($user)
    {
        //https://vimeo.com/api/v2/daftspunk/videos.json
        $items = file_get_contents('https://vimeo.com/api/v2/'.$user.'/videos.json');
        $this->playlist->items = array_merge($this->playlist->items, $this->convertFromVimeo(json_decode($items)));
    }

    protected function loadPMFactoryBlogPlaylist($id="20d862b0-ccf1-11e4-94cf-37144cf712d1")
    {
        //https://vimeo.com/api/v2/daftspunk/videos.json
        if (empty($this->playlist->title))
        {
            $this->playlist->title = "Playlist created on PM factory Tubelist application (".urldecode($id).')';
            $this->playlist->URL = "http://pascal-mietlicki.fr/playlist/content/".$id;
        }
        $items = file_get_contents('http://pascal-mietlicki.fr/pmietlicki/tubelist/json/playlist/'.$id);
        $this->playlist->items = array_merge($this->playlist->items, json_decode($items));
    }

    protected function convertFromVimeo($array)
    {
        return array_map(function($tag) {
            return array(
                'index' => $this->index++,
                'playing' => 0,
                'urlof' => $tag->url,
                'name' => $tag->title,
                'desc' => $tag->description,
                'isrc' => $tag->thumbnail_small
            );
         }, $array);
    }

}