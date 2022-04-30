<?

namespace modules\action;

use modules\action\authorize\user;

class register extends user
{
    /** Inherited parent */
    public function post($params = [], $data = [])
    {
        $result = $this->createUser($params, $data);
        
        if ($result['info']) {
            foreach ($data as $key => $arr) {
                $actURL = [$this->ds->feDev, "#", $params['lang'], "user/login", $result['activationArr'][$arr['email']]];
                $actURL = implode("/", $actURL);
                
                $message = "<a href='$actURL' target='_blank' title='acivation'>$actURL</a>";
                
                $result['emailInfo'] = $this->mail->send($arr['email'], "User registration", $message);
            }
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
