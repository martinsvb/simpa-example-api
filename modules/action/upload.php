<?

namespace modules\action;

use app\base\controller;

class upload extends controller
{
    public function post($params = [], $data = [])
    {
        if (trim($_POST['action']) == 'upload') {
            $upDir = implode("/", [$this->ds->apiData['documents'], trim($_POST['folder'])]) . "/";
            $result = $this->files->upload($upDir);
            
            if (trim($_POST['image']) == 'resize' || trim($_POST['image']) == 'resizeNoThumb') {
                foreach ($result as $key => $arr) {
                    if ($this->files->isImage($arr['fileName'])) {
                        $fileName = preg_replace(
                            "#".$this->ds->web."#",
                            $this->ds->docRoot,
                            $arr['fileName']
                        );
                        
                        $resizeOpt = ['newHeight' => $this->ds->imgResizeOpt['bigImgHeight']];
                        if (trim($_POST['image']) == 'resize') {
                            $resizeOpt['newHeightThumb'] = $this->ds->imgResizeOpt['thumbnailHeight'];
                        }
                        
                        $imgRes = $this->img->resize($fileName, $resizeOpt);
                        
                        $result = [];
                        $result[$key]['fileName'] = preg_replace(
                            "#".$this->ds->docRoot."#",
                            $this->ds->web,
                            $imgRes['fileName']
                        );
                        if (trim($_POST['image']) == 'resize') {
                            $result[$key]['thumbName'] = preg_replace(
                                "#".$this->ds->docRoot."#",
                                $this->ds->web,
                                $imgRes['thumbName']
                            );
                        }
                    }
                }
            }
        }
        
        if ($data['action'] == 'del') {
            $result = is_array($data['file'])
                ? array_map(
                    function($delFile) {
                        return $this->files->del($delFile);
                    },
                    $data['file']
                )
                : [$this->files->del($data['file'])];
        }
        
        $this->resp->send($this->resp->statusCodes['OK'], $result);
    }
}
