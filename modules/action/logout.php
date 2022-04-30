<?

namespace modules\action;

use modules\_base\users\usersController;

class logout extends usersController
{
    /** Inherited parent */
    public function get($params = [], $data = [], $headers = [])
    {
        $id = $this->generateId($headers['email']);
        $result = ['info' => $this->destroySession($id)];
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
