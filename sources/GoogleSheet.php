<?php
/**
 * Created by PhpStorm.
 * User: ГерманАл
 * Date: 12.12.2018
 * Time: 23:56
 */

namespace vnukga\migrationGenerator\sources;


class GoogleSheet
{
    public function getPreparedArray($spreadsheetId){
        $url = $this->getUrl($spreadsheetId);
        $json = $this->getJsonFromSpreadsheet($url);
        $result = $this->jsonToArray($json);
        return $result;
    }

    public function getJsonFromSpreadsheet($url){
        $file = file_get_contents($url);
        $file = str_replace('gsx$', '', $file);
        $file = str_replace('$', '', $file);
        $json = json_decode($file);
        return $json;
    }
    public function jsonToArray($json) {
        $content = $json->feed->entry;
        $fields = ['id', 'category', 'updated','title','content','link'];
        $result = [];
        $tableName = '';
        foreach ($content as &$item){
            $this->unsetFields($item,$fields);
            if($item->table->t != '') {
                $tableName = $item->table->t;
            } else {
                $field = $item->field->t;
                if ($field == 'id') continue;
                $result[$tableName][$field] = [
                    'type' =>$item->type->t,
                    'comment' =>$item->comment->t,
                    'required' =>$item->required->t,
                    'defaultValue' =>$item->default->t,
                    'foreignKey' =>$item->foreignkey->t,
                ];
            }
        }
        return $result;
    }

    private function getUrl($spreadsheetId){
        $url = 'https://spreadsheets.google.com/feeds/list/' . $spreadsheetId . '/od6/public/values?alt=json';
        return $url;
    }

    private function unsetFields(&$item, array $fields){
        foreach ($fields as $field){
            unset($item->$field);
        }
    }
}