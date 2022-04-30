<?

namespace modules\action;

use app\router\Route;
use modules\_base\content\contentController;

class base extends contentController
{
    protected $type = 'test';

    #[Route('GET', '/')]
    public function get($params = [], $data = [])
    {
        $result = parent::get($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }

    #[Route('POST', '/')]
    public function post($params = [], $data = [])
    {
        $result = parent::post($params, $data);
        
        $this->resp->send($this->resp->statusCodes['Created'], $result);
    }

    #[Route('PUT', '/')]
    public function put($params = [], $data = [])
    {
        $result = parent::put($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }

    #[Route('DELETE', '/')]
    public function delete($params = [], $data = [])
    {
        $result = parent::delete($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
