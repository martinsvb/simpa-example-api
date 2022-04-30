<?

namespace modules\action;

use modules\action\authorize\user;

class activation extends user
{
    /** Inherited parent */
    public function get($params = [], $data = [])
    {
        $result = ['info' => 0];
        
        $queries = [
            'update@users' => [
                'affectedColumns' => [
                    'active' => 1
                ],
                'where' => ["id=:id"],
                'data' => [['id' => $params['code']]]
            ]
        ];
        
        if ($this->db->runTransaction($queries)) {
            $result['info'] = 1;
        };
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
