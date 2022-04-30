<?

namespace modules\action;

use modules\_base\users\usersController;

class login extends usersController
{   
    /** Inherited parent */
    public function post($params = [], $data = [], $headers = [])
    {
        $result = ['info' => 0];
        
        if (!$data['email'] || !$data['password']) {
            $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['missCred']]);
        }
        
        $data['email'] = trim(strtolower($data['email']));
        $data['password'] = trim($data['password']);
        
        $data['id'] = $this->generateId($data['email']);
        
        $user = parent::get(['id' => $data['id'], 'active' => [0, 1]]);
        
        if (!$user) {
            $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['userNotExists'], 'data' => $data]);
        }
        else {
            if ($user['password'] !== $this->generatePassword($data['password'], $data['email'])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['userBadPass']]);
            }
            
            if ($user['active'] !== 1) {
                $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['userNotActive']]);
            }
            
            if ($token = $this->getToken($user['id'], $headers)) {
                $result = [
                    'info' => 1,
                    'token' => $token,
                    'user' => [
                        'firstName' => $user['firstName'],
                        'lastName' => $user['lastName'],
                        'avatar' => $user['avatar'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'modules' => json_decode($user['modules'], true)
                    ]
                ];
            }
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
