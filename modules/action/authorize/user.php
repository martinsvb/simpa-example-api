<?

namespace modules\action\authorize;

use modules\_base\users\usersController;

class user extends usersController
{
    /** Inherited parent */
    public function get($params = [], $data = [])
    {
        if (isset($params['activeString'])) {
            $params['active'] = explode("-", $params['activeString']);
        }
        
        $result = parent::get($params, $data);
        
        if (isset($result[0]) && is_array($result[0])) {
            foreach ($result as & $arr) {
                unset($arr['password']);
                $arr['ts_created'] = $arr['ts_created'] * 1000;
                $arr['avatar'] = json_decode($arr['avatar'], true);
                $arr['modules'] = json_decode($arr['modules'], true);
            }
        }
        if (isset($result['ts_created'])) {
            unset($result['password']);
            $result['ts_created'] = $result['ts_created'] * 1000;
            $arr['avatar'] = json_decode($arr['avatar'], true);
            $result['modules'] = json_decode($result['modules'], true);
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }

    /**
     *  Check user data and store him in the database
     *
     *  @param array $params
     *  @param array $data
     *
     *  return boolean $result, create user result
     */
    protected function createUser($params = [], $data = [])
    {
        $activationArr = [];
        foreach ($data as $key => & $arr) {
            if (!$arr['email'] || !$arr['password'] || !$arr['repassword']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['missCred']]);
            }
            
            if ($arr['password'] !== $arr['repassword']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['xMatchPass']]);
            }
            
            $arr['email'] = trim(strtolower($arr['email']));
            $arr['password'] = trim($arr['password']);
            
            $arr['id'] = $this->generateId($arr['email']);
            
            $userExists = parent::get(['id' => $arr['id'], 'active' => [0, 1]]);
            
            if ($userExists) {
                $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['userExists']]);
            }
            
            $arr['avatar'] = json_encode($arr['avatar'] ?? null);
            $arr['password'] = $this->generatePassword($arr['password'], $arr['email']);
            $arr['role'] = $arr['role'] ?? "user";
            $arr['modules'] = json_encode($arr['modules'] ?? null);
            $arr['active'] = $arr['active'] ?? 0;
            
            $activationArr[$arr[email]] = $arr['id'];
        }
        
        $result = parent::post($params, $data) ? 1 : 0;
        
        return [
            'info' => $result,
            'activationArr' => $activationArr
        ];
    }
    
    /** Inherited parent */
    public function post($params = [], $data = [])
    {
        $result = $this->createUser($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /** Inherited parent */
    public function put($params = [], $data = [])
    {
        foreach ($data as $key => & $arr) {
            if (!$arr['email']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['missCred']]);
            }
            
            $arr['email'] = trim(strtolower($arr['email']));
            
            $arr['id'] = $this->generateId($arr['email']);
            
            $userData = parent::get(['id' => $arr['id'], 'active' => [0, 1]]);
            
            if (!$userData) {
                $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['userNotExists']]);
            }
            
            $arr['avatar'] = json_encode($arr['avatar'] ?? null);
            $arr['modules'] = json_encode($arr['modules'] ?? null);
            $arr['role'] = $arr['role'] ?? "user";
            $arr['active'] = $arr['active'] ?? 0;
            if ($arr['chpassword']) {
                if (!$arr['password'] || !$arr['newpassword'] || !$arr['newrepassword']) {
                    $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['xPass']]);
                }
                
                $arr['password'] = $this->generatePassword($arr['newpassword'], $arr['email']);
                
                if ($arr['password'] !== $userData['password']) {
                    $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['userBadPass']]);
                }
                
                if ($arr['newpassword'] !== $arr['newrepassword']) {
                    $this->resp->send($this->resp->statusCodes['OK'], ['user' => $key, 'warning' => $this->userMessages['xMatchPass']]);
                }
            }
        }
        
        $result['info'] = parent::put($params, $data) ? 1 : 0;
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /** Inherited parent */
    public function delete($params = [], $data = [])
    {
        $result = parent::delete($params, $data);
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
