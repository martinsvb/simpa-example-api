<?php

namespace modules\_base\users;

use modules\action\authorize\module;

/**
 *  Users action authorisation
 *  
 *  @property $_authRights, map of request methods and rights
 *  @property $_noAuthModules, public modules and request methods definition
 */
class usersAuthorisation extends usersController
{
    private
    $_authRights = [
        'GET' => "read",
        'POST' => "write",
        'PUT' => "write",
        'DELETE' => "del"
    ];
    
    /**
     *  User action authorisation check
     *
     *  @param string $name, module name
     *  @param string $email, user email
     *  @param string $method, request method
     *
     *  @return boolean $result, Alllowed/notAllowed access to requested module
     */
    public function checkAuth($name, $headers, $method)
    {
        $result = false;
        
        // Check if requested module is active
        $module = new module();
        $moduleAct = $module->isActive(['name' => $name]);
        
        // Get user id from request authorisation header
        $id = $this->verifyToken($headers);
        
        if ($moduleAct && $id) {
            
            // Check if user has authorisation for action in requested module
            if ($user = parent::get(['id' => $id, 'active' => [1]])) {
                $user['modules'] = json_decode($user['modules'], true);
                $result = $user['modules'][$name][$this->_authRights[$method]];
            }
        }
        
        return $result;
    }
}
