<?

namespace modules\action\authorize;

use app\base\controller;

/**
 *  Companies controller, works as data layer
 *  
 *  @property $table, database table name
 */
class company extends controller
{
    protected
    $table = "companies";
    
    private
    $_companyMessages = [
        'missCred' => "notCompleteCredentials",
        'emailExists' => "compEmailExists",
        'icoExists' => "compIcoExists",
        'emailNotExists' => "compNotEmailExists",
        'icoNotExists' => "compNotIcoExists"
    ],
    $_actionAuth = [
        'module' => "companies"
    ];
    
    /**
     *  Get company / companies data from database
     */
    private function _getComp($params = [])
    {
        $selectCols = "id, name, ico, email, active, street, street_nr, state, city, zip, phone, ts_created";
        $where = null;
        $fetch = "fetchAll";
        if (isset($params['email'])) {
            $where = " WHERE email=?";
            $queryParams = [$params['email']];
            $fetch = "fetch";
        }
        if (isset($params['ico'])) {
            $where = " WHERE ico=?";
            $queryParams = [$params['ico']];
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
     *  Get company / companies list
     */
    public function get($params = [], $data = [])
    {
        $result = $this->_getComp($params);
        
        if (isset($result[0]) && is_array($result[0])) {
            foreach ($result as & $arr) {
                $arr['phone'] = json_decode($arr['phone']);
                $arr['ts_created'] = $arr['ts_created'] * 1000;
            }
        }
        if (isset($result['ts_created'])) {
            $result['phone'] = json_decode($result['phone']);
            $result['ts_created'] = $result['ts_created'] * 1000;
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /** Inherited parent */
    public function post($params = [], $data = [])
    {
        foreach ($data as $key => & $arr) {
            if (!$arr['email']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['company' => $key, 'warning' => $this->_companyMessages['missCred']]);
            }
            
            $arr['email'] = strtolower($arr['email']);
            
            if ($arr['email'] && $companyExists = $this->_getComp(['email' => $arr['email']])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['company' => $key, 'warning' => $this->_companyMessages['emailExists']]);
            }
            
            if ($arr['ico'] && $companyExists = $this->_getComp(['ico' => $arr['ico']])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['company' => $key, 'warning' => $this->_companyMessages['icoExists']]);
            }
            
            $arr['phone'] = json_encode($arr['phone']);
            
            $arr['active'] = (int) $arr['active'];
        }
        
        $queries = [
            'insert@companies' => [
                'affectedColumns' => [
                    'name' => "%data%",
                    'ico' => "%data%",
                    'email' => "%data%",
                    'active' => "%data%",
                    'street' => "%data%",
                    'street_nr' => "%data%",
                    'state' => "%data%",
                    'city' => "%data%",
                    'zip' => "%data%",
                    'phone' => "%data%",
                    'ts_created' => $this->ds->time['timeStamp']
                ],
                'data' => $data
            ]
        ];
        
        $result['info'] = $this->db->runTransaction($queries) ? 1 : 0;
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
    
    /** Inherited parent */
    public function put($params = [], $data = [])
    {
        $this->_actionAuth['write'] = 1;
        
        foreach ($data['company'] as $key => & $arr) {
            if (!$arr['email']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['company' => $key, 'warning' => $this->_companyMessages['missCred']]);
            }
            
            $arr['email'] = strtolower($arr['email']);
            
            if ($arr['email'] && !$companyExists = $this->_getComp(['email' => $arr['email']])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['company' => $key, 'warning' => $this->_companyMessages['emailNotExists']]);
            }
            
            if ($arr['ico'] && !$companyExists = $this->_getComp(['ico' => $arr['ico']])) {
                $this->resp->send($this->resp->statusCodes['OK'], ['company' => $key, 'warning' => $this->_companyMessages['icoNotExists']]);
            }
            
            $arr['phone'] = json_encode($arr['phone']);
        }
        
        $affCols = [
            'name' => "%data%",
            'ico' => "%data%",
            'email' => "%data%",
            'active' => "%data%",
            'street' => "%data%",
            'street_nr' => "%data%",
            'state' => "%data%",
            'city' => "%data%",
            'zip' => "%data%",
            'phone' => "%data%"
        ];
        
        if (isset($params['action']) && $params['action'] == "toggleActive") {
            $affCols = [
                'active' => "%data%"
            ];
        }
        
        $queries = [
            'update@companies' => [
                'affectedColumns' => $affCols,
                'where' => ["id=:id"],
                'data' => $data['company']
            ]
        ];
        
        $result['info'] = $this->db->runTransaction($queries) ? 1 : 0;
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
