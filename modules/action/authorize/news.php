<?php

namespace modules\action\authorize;

use app\router\Route;
use modules\_base\content\contentController;

class news extends contentController
{
    protected $type = 'news';
    
    protected $version = 1;
    
    #[Route('GET', '/api/news')]
    public function get($params = [], $data = [])
    {
        $result = parent::get($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    #[Route('POST', '/api/news')]
    public function post($params = [], $data = [])
    {
        $result = parent::post($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    #[Route('PUT', '/api/news')]
    public function put($params = [], $data = [])
    {
        $result = parent::put($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    #[Route('DELETE', '/api/news')]
    public function delete($params = [], $data = [])
    {
        $result = parent::delete($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
