<?php

namespace modules\_base\content;

use app\base\controller;

abstract class contentController extends controller
{
    protected
    $table = "content",
    $version,
    $type,
    $postPayload = [
        [
            'lang' => "string",
            'subject' => "string",
            'content' => "string",
            'ts_planned' => "number",
        ]
    ];

    /**
     *  Get content / contents list
     *  @param array $params [id: string]
     *  @param array $data
     */
    protected function get($params = [], $data = [])
    {
        $selectCols = "id, lang, ts_created, subject, content, appendix, ts_planned";
        $where = "type=?";
        $queryParams = [$this->type];

        if (isset($params['id'])) {
            $selectCols .= ", content, gallery, attachments";
            $where .= " AND id=?";
            $queryParams[] = $params['id'];
        }

        $result = $this->db->selection(
            "SELECT $selectCols FROM " . $this->table . " WHERE $where",
            $queryParams,
            isset($params['id']) ? "fetch" : "fetchAll"
        );

        return $result;
    }

    /**
     *  Create content
     *  @param array $params
     *  @param array $data
     */
    protected function post($params = [], $data = [])
    {
        $queries = [
            'insert@content' => [
                'affectedColumns' => [
                    'id' => $this->db->uuid,
                    'lang' => $this->db->data,
                    'type' => $this->type,
                    'version' => $this->version,
                    'user_created' => 1,
                    'ts_created' => $this->ds->time['timeStamp'],
                    'ts_active' => 0,
                    'active' => 1,
                    'subject' => $this->db->data,
                    'content' => $this->db->data,
                    'gallery' => null,
                    'attachments' => null,
                    'ts_planned' => $this->db->data
                ],
                'data' => $data
            ],
            'insert@calendar' => [
                'affectedColumns' => [
                    'origin' => $this->table,
                    'origin_id' => $this->db->data,
                    'ts' => $this->db->data
                ],
                'data' => $data,
                'merge' => [
                    'affectedColumn' => "origin_id",
                    'mergeQueryAffId' => "insert@content",
                    'dataColumns' => [
                        "ts@ts_planned",
                        "ts@ts_created",
                        "active@active"
                    ]
                ]
            ]
        ];

        return $this->db->runTransaction($queries);
    }

    /**
     *  Update content
     *  @param array $params
     *  @param array $data
     */
    protected function put($params = [], $data = [])
    {
        $queries = [
            'update@content' => [
                'affectedColumns' => [
                    'user_updated' => 1,
                    'ts_updated' => $this->ds->time['timeStamp'],
                    'ts_active' => 0,
                    'subject' => $this->db->data,
                    'content' => $this->db->data,
                    'gallery' => null,
                    'attachments' => null,
                    'ts_planned' => $this->db->data,
                    'id' => $this->db->data,
                    'lang' => $this->db->data,
                    'type' => $this->type,
                    'version' => $this->version
                ],
                'where' => ["id=:id", "lang=:lang", "type=:type", "version=:version"],
                'data' => $data
            ],
            'update@calendar' => [
                'affectedColumns' => [
                    'origin' => $this->table,
                    'origin_id' => $this->db->data,
                    'ts' => $this->db->data
                ],
                'where' => ["origin=:origin", "origin_id=:origin_id"],
                'data' => $data,
                'merge' => [
                    'mergeQueryAffId' => "update@content",
                    'dataColumns' => [
                        "origin_id@id",
                        "ts@ts_planned",
                        "ts@ts_created",
                        "active@active"
                    ]
                ]
            ]
        ];
        
        $result = $this->db->runTransaction($queries);
        
        return $result;
    }

    /**
     *  Delete content
     *  @param array $params
     *  @param array $data
     */
    protected function delete($params = [], $data = [])
    {
        $queryParams = [];
        foreach ($data as $arr) {
            $queryParams[] = [
                1,
                $this->ds->time['timeStamp'],
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
            "UPDATE " . $this->table . "
            SET active=?
            WHERE id=? AND lang=? AND type=? AND version=?",
            $this->table,
            $this->params
        );
        
        return $result;
    }
}
