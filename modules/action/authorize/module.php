<?php

namespace modules\action\authorize;

use app\base\controller;

/**
 *  Modules controller, works as data layer
 *  
 *  @property $table, database table name
 */
class module extends controller
{
    protected
    $table = "modules";
    
    private
    $_modulesMessages = [
        'missCred' => "notCompleteCredentials",
        'nameExists' => "moduleNameExists",
        'nameNotExists' => "moduleNotNameExists"
    ];
    
    /**
     *  Get module / modules data from database
     *
     *  @param array $params
     *
     *  @return array $result
     */
    private function _getMod($params = [])
    {
        $selectCols = "id, name, role, description, active, ts_created";
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
     *  Get module / modules list
     *
     *  @param array $params
     *  @param array $data
     *
     *  @return array data for response
     */
    public function get($params = [], $data = [])
    {
        $result = $this->_getMod($params);
        
        if (isset($result[0]) && is_array($result[0])) {
            foreach ($result as & $arr) {
                $arr['ts_created'] = $arr['ts_created'] * 1000;
                $arr['role'] = json_decode($arr['role'], true);
            }
        }
        if (isset($result['ts_created'])) {
            $result['ts_created'] = $result['ts_created'] * 1000;
            $result['role'] = json_decode($result['role'], true);
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /**
     *  Create modules
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
                $this->resp->send($this->resp->statusCodes['OK'], ['module' => $key, 'warning' => $this->_modulesMessages['missCred']]);
            }
            
            $arr['name'] = strtolower($arr['name']);
            
            if ($arr['name'] && $moduleExists = $this->_getMod(['name' => $arr['name']])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['module' => $key, 'warning' => $this->_modulesMessages['nameExists']]);
            }
            
            $arr['role'] = json_encode($arr['role']);
            $arr['active'] = (int) $arr['active'];
        }
        
        $queries = [
            'insert@modules' => [
                'affectedColumns' => [
                    'name' => "%data%",
                    'role' => "%data%",
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
     *  Update modules
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
                $this->resp->send($this->resp->statusCodes['OK'], ['module' => $key, 'warning' => $this->_modulesMessages['missCred']]);
            }
            
            $arr['name'] = strtolower($arr['name']);
            
            $arr['role'] = json_encode($arr['role']);
            $arr['active'] = (int) $arr['active'];
        }
        
        $affCols = isset($params['action']) && $params['action'] == "toggleActive"
            ? ['active' => "%data%"]
            : [
                'name' => "%data%",
                'role' => "%data%",
                'description' => "%data%",
                'active' => "%data%"
            ];
        
        $queries = [
            'update@modules' => [
                'affectedColumns' => $affCols,
                'where' => ["id=:id"],
                'data' => $data
            ]
        ];
        
        $result['info'] = $this->db->runTransaction($queries) ? 1 : 0;
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /**
     *  Check if module is active
     *
     *  @param array $params
     *  @param array $data
     *
     *  @return string, action result message
     */
    public function isActive($params = [], $data = [])
    {
        $result = $this->db->selection(
            "SELECT active FROM ".$this->table." WHERE name=?",
            [$params['name']],
            "fetch"
        );

        return $result['active'] ?? false;
    }
}
