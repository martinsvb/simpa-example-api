<?php

namespace modules\_base\users;

use app\base\controller;

use \Firebase\JWT\JWT;

/**
 *  Users base controller, works as data layer
 *  
 *  @property $table, database table name
 */
abstract class usersController extends controller
{
    protected
    $table = "users",
    
    $salt = "sdjhfkjsdkfjbn",
    
    $userMessages = [
        'missCred' => "notCompleteCredentials",
        'xMatchPass' => "notMatchPasswords",
        'userExists' => "userExists",
        'userNotExists' => "userNotExists",
        'userBadPass' => "userBadPass",
        'userNotActive' => "userNotActive",
        'xPass' => "xPass",
    ];
    
    /**
     *  Get user / users list
     */
    protected function get($params = [], $data = [])
    {
        $in = count($params['active']) > 1
            ? str_repeat('?,', count($params['active']) - 1) . '?'
            : '?';
        
        $where = "u.active IN ($in)";
        $queryParams = $params['active'];
        
        if (isset($params['id'])) {
            $where .= " AND u.id=?";
            $queryParams[] = $params['id'];
        }
        
        $result = $this->db->selection(
            "SELECT u.id, u.comp_id, c.name As comp_name, u.firstName, u.lastName, u.avatar, u.email, u.password, u.role, u.active, u.modules
            FROM users AS u LEFT JOIN companies AS c ON u.comp_id = c.id WHERE $where",
            $queryParams,
            isset($params['id']) ? "fetch" : "fetchAll"
        );
        
        return $result;
    }
    
    /**
     *  Create user
     */
    protected function post($params = [], $data = [])
    {
        $queries = [
            'insert@users' => [
                'affectedColumns' => [
                    'id' => "%data%",
                    'firstName' => "%data%",
                    'lastName' => "%data%",
                    'avatar' => "%data%",
                    'comp_id' => "%data%",
                    'email' => "%data%",
                    'password' => "%data%",
                    'role' => "%data%",
                    'modules' => "%data%",
                    'active' => "%data%",
                    'ts_created' => $this->ds->time['timeStamp']
                ],
                'data' => $data
            ]
        ];
        
        $result = $this->db->runTransaction($queries);
        
        return $result;
    }
    
    /**
     *  Update user
     */
    protected function put($params = [], $data = [])
    {
        $queries = [
            'update@users' => [
                'affectedColumns' => [
                    'firstName' => "%data%",
                    'lastName' => "%data%",
                    'avatar' => "%data%",
                    'comp_id' => "%data%",
                    'role' => "%data%",
                    'modules' => "%data%",
                    'active' => "%data%"
                ],
                'where' => ["id=:id"],
                'data' => $data
            ]
        ];
        
        if ($data['password']) {
            $queries['update@users']['affectedcolumns']['password'] = "%data%";
        }
        
        $result = $this->db->runTransaction($queries);
        
        return $result;
    }
    
    /**
     *  Delete user
     */
    protected function delete($params = [], $data = [])
    {
        $queryParams = [];
        foreach ($data as $arr) {
            $queryParams[] = [
                1,
                $this->ds->time->timestamp,
                0,
                $arr['subject'],
                $arr['content'],
                null,
                null,
                0,
                $arr['id'],
                $arr['lang'],
                $this->type,
                $this->version
            ];
        }
        
        $result = $this->db->modification(
            "UPDATE users
            SET active=?
            WHERE id=? AND lang=? AND type=? AND version=?",
            $this->table,
            $queryParams
        );
        
        return $result;
    }
    
    /** User change profile */
    protected function changeProfile($data = [])
    {
        if (!$data['email']) {
            $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['missCred']]);
        }
        
        $data['email'] = trim(strtolower($data['email']));
        
        $data['id'] = $this->generateId($data['email']);
        
        $userData = self::get(['id' => $data['id'], 'active' => [0, 1]]);
        
        if (!$userData) {
            $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['userNotExists']]);
        }
        
        $data['avatar'] = json_encode($data['avatar'] ?? null);
        
        if ($data['chpassword']) {
            if (!$data['password'] || !$data['newpassword'] || !$data['newrepassword']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['xPass']]);
            }
            
            if ($this->generatePassword($data['password'], $data['email']) !== $userData['password']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['userBadPass']]);
            }
            
            if ($data['newpassword'] !== $data['newrepassword']) {
                $this->resp->send($this->resp->statusCodes['OK'], ['warning' => $this->userMessages['xMatchPass']]);
            }
            
            $data['password'] = $this->generatePassword($data['newpassword'], $data['email']);
        }

        $queries = [
            'update@users' => [
                'affectedColumns' => [
                    'firstName' => "%data%",
                    'lastName' => "%data%",
                    'avatar' => "%data%",
                ],
                'where' => ["id=:id"],
                'data' => [$data]
            ]
        ];
        
        if ($data['password']) {
            $queries['update@users']['affectedColumns']['password'] = "%data%";
        }
    
        return $this->db->runTransaction($queries) ? 1 : 0;
    }
    
    /**
     *  Delete user from database permanently
     *
     *  @param string id
     *
     *  @return number, result of destroying session
     */
    protected function deletePermanent($email)
    {
        $result = 0;
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$this->generateId($email)]);
        
        if ($stmt->rowCount()) {
            $result = 1;
        }
        
        return $result;
    }
    
    /**
     *  Generate user id hash
     *
     *  @param string id
     */
    protected function generateId($id)
    {
        $id = hash('sha1', strtolower($id) . $this->salt);
        
        return $id;
    }
    
    /**
     *  Generate user password hash
     *
     *  @param string password
     *  @param string id
     */
    protected function generatePassword($pass, $id)
    {
        $pass = hash('sha512', $pass . strtolower($id) . $this->salt);
        
        return $pass;
    }

    /**
     *  Generate JWT token
     *
     *  @param string id
     *  @param array headers
     *
     *  @return jwt token
     */
    protected function getToken($id, $headers)
    {
        $tokenArr = [
            'iat'  => time(),
            'jti'  => base64_encode(mcrypt_create_iv(32)),
            'iss'  => $headers['Host'],
            'nbf'  => time(),
            'exp'  => time() + 3600,
            'data' => [
                'userId'   => $id
            ]
        ];

        $token = JWT::encode($tokenArr, base64_decode($this->salt), 'HS512');
        
        return $token;
    }
    
    /**
     *  Verify JWT token from request header
     *
     *  @param array headers
     *
     *  @return jwt token
     */
    protected function verifyToken($headers)
    {
        $token = preg_replace('#Bearer #', '', $headers['Authorization'] ?? '');
        
        if ($token) {
            try {
                $token = JWT::decode($token, base64_decode($this->salt), ['HS512']);
                
                return $token->data->userId;
            
            } catch (Exception $e) {
                $this->resp->send($this->resp->statusCodes['Forbidden'], ['auth' => 0]);
            }
        }
        else {
            $this->resp->send($this->resp->statusCodes['Forbidden'], ['auth' => 0]);
        }
    }
}
