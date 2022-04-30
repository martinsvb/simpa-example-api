<?

namespace modules\action;

use modules\action\authorize\user;

class profile extends user
{
    /** Inherited parent */
    public function put($params = [], $data = [])
    {
        $result = ['info' => 0];
        
        if ($data['delete'] === true) {
            $result['info'] = $this->deletePermanent($data['email']);
        }
        else {
            $result['info'] = $this->changeProfile($data);
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
