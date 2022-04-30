<?

namespace modules\action\authorize;

use app\base\controller;

/**
 *  Roles controller, works as data layer
 *  
 *  @property $table, database table name
 */
class role extends controller
{
    protected
    $table = "roles";
    
    private
    $_rolesMessages = [
        'missCred' => "notCompleteCredentials",
        'nameExists' => "roleNameExists",
        'nameNotExists' => "roleNotNameExists"
    ];
    
    /**
     *  Get role / roles data from database
     *
     *  @param array $params
     *
     *  @return array $result
     */
    private function _getRole($params = [])
    {
        $selectCols = "id, name, description, active, ts_created";
        $where = null;
        $fetch = "fetchAll";
        if (isset($params['name'])) {
            $where = " WHERE name=?";
            $queryParams = [$params['name']];
            $fetch = "fetch";
        }
        
        $result = $this->db->selection(
            "SELECT $selectCols FROM ".$this->table.$where,
            $queryParams,
            $fetch
        );
        
        return $result;
    }
    
    /**
     *  Get role / roles list
     *
     *  @param array $params
     *  @param array $data
     *
     *  @return array data for response
     */
    public function get($params = [], $data = [])
    {
        $result = $this->_getRole($params);
        
        if (isset($result[0]) && is_array($result[0])) {
            foreach ($result as & $arr) {
                $arr['ts_created'] = $arr['ts_created'] * 1000;
            }
        }
        if (isset($result['ts_created'])) {
            $result['ts_created'] = $result['ts_created'] * 1000;
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /**
     *  Create roles
     *
     *  @param array $params
     *  @param array $data
     *
     *  @return string, action result message
     */
    public function post($params = [], $data = [])
    {
        foreach ($data as $key => & $arr) {
            if (!$arr['name']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['module' => $key, 'warning' => $this->_rolesMessages['missCred']]);
            }
            
            $arr['name'] = strtolower($arr['name']);
            
            if ($arr['name'] && $roleExists = $this->_getRole(['name' => $arr['name']])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['module' => $key, 'warning' => $this->_rolesMessages['nameExists']]);
            }
            
            $arr['active'] = (int) $arr['active'];
        }
        
        $queries = [
            'insert@roles' => [
                'affectedColumns' => [
                    'name' => "%data%",
                    'description' => "%data%",
                    'active' => "%data%",
                    'ts_created' => $this->ds->time['timeStamp']
                ],
                'data' => $data
            ]
        ];
        
        $result['info'] = $this->db->runTransaction($queries) ? 1 : 0;
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /**
     *  Update roles
     *
     *  @param array $params
     *  @param array $data
     *
     *  @return string, action result message
     */
    public function put($params = [], $data = [])
    {
        foreach ($data as $key => & $arr) {
            if (!$arr['name']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['module' => $key, 'warning' => $this->_rolesMessages['missCred']]);
            }
            
            $arr['name'] = strtolower($arr['name']);
            
            $arr['active'] = (int) $arr['active'];
        }
        
        $affCols = isset($params['action']) && $params['action'] == "toggleActive"
            ? ['active' => "%data%"]
            : [
                'name' => "%data%",
                'description' => "%data%",
                'active' => "%data%"
            ];
        
        $queries = [
            'update@roles' => [
                'affectedColumns' => $affCols,
                'where' => ["id=:id"],
                'data' => $data
            ]
        ];
        
        $result['info'] = $this->db->runTransaction($queries) ? 1 : 0;
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
