<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Format {
    const ARRAY_FORMAT = 'array';
    const CSV_FORMAT = 'csv';
    const JSON_FORMAT = 'json';
    const HTML_FORMAT = 'html';
    const PHP_FORMAT = 'php';
    const SERIALIZED_FORMAT = 'serialized';
    const XML_FORMAT = 'xml';
    const DEFAULT_FORMAT = self::JSON_FORMAT;
    private $_CI;
    protected $_data = [];
    protected $_from_type = NULL;
    public function __construct($data = NULL, $from_type = NULL){
        $this->_CI = &get_instance();
        $this->_CI->load->helper('inflector');
        if ($from_type !== NULL){
            if (method_exists($this, '_from_'.$from_type)){
                $data = call_user_func([$this, '_from_'.$from_type], $data);
            }else{
                throw new Exception('Format class does not support conversion from "'.$from_type.'".');
            }
        }
        $this->_data = $data;
    }
    public static function factory($data, $from_type = NULL) {
		return new static($data, $from_type);
    }
    public function to_array($data = NULL){
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        if (is_array($data) === FALSE){
            $data = (array) $data;
        }
        $array = [];
        foreach ((array) $data as $key => $value) {
            if (is_object($value) === TRUE || is_array($value) === TRUE){
                $array[$key] = $this->to_array($value);
            } else{
                $array[$key] = $value;
            }
        }
        return $array;
    }
    public function to_xml($data = NULL, $structure = NULL, $basenode = 'xml'){
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        if ($structure === NULL){
            $structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
        }
        if (is_array($data) === FALSE && is_object($data) === FALSE){
            $data = (array) $data;
        }
        foreach ($data as $key => $value){
            if (is_bool($value)){
                $value = (int) $value;
            }
            if (is_numeric($key)){
                $key = (singular($basenode) != $basenode) ? singular($basenode) : 'employee';
            }
            $key = preg_replace('/[^a-z_\-0-9]/i', '', $key);
            if ($key === '_attributes' && (is_array($value) || is_object($value))) {
                $attributes = $value;
                if (is_object($attributes)){
                    $attributes = get_object_vars($attributes);
                }
                foreach ($attributes as $attribute_name => $attribute_value){
                    $structure->addAttribute($attribute_name, $attribute_value);
                }
            } elseif (is_array($value) || is_object($value)){
                $node = $structure->addChild($key);
                $this->to_xml($value, $node, $key);
            }else{
                $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
                $structure->addChild($key, $value);
            }
        }
        return $structure->asXML();
    }
    public function to_html($data = NULL){
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        if (is_array($data) === FALSE){
            $data = (array) $data;
        }
        if (isset($data[0]) && count($data) !== count($data, COUNT_RECURSIVE)){
            $headings = array_keys($data[0]);
        }else{
            $headings = array_keys($data);
            $data = [$data];
        }
        $this->_CI->load->library('table');
        $this->_CI->table->set_heading($headings);
        foreach ($data as $row){
            $row = @array_map('strval', $row);
            $this->_CI->table->add_row($row);
		}
        return $this->_CI->table->generate();
    }
    public function to_csv($data = NULL, $delimiter = ',', $enclosure = '"'){
        $handle = fopen('php://temp/maxmemory:1048576', 'w');
        if ($handle === FALSE){
            return NULL;
        }
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        if ($delimiter === NULL){
            $delimiter = ',';
        }
        if ($enclosure === NULL){
            $enclosure = '"';
        }
        if (is_array($data) === FALSE){
            $data = (array) $data;
        }
        if (isset($data[0]) && count($data) !== count($data, COUNT_RECURSIVE)){
            $headings = array_keys($data[0]);
        }else{
            $headings = array_keys($data);
            $data = [$data];
        }
        fputcsv($handle, $headings, $delimiter, $enclosure);
        foreach ($data as $record) {
            if (is_array($record) === FALSE){
                break;
            }
            $record = @ array_map('strval', $record);
            fputcsv($handle, $record, $delimiter, $enclosure);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        $csv = mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
        return $csv;
    }
    public function to_json($data = NULL){
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        $callback = $this->_CI->input->get('callback');
        if (empty($callback) === TRUE){
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } elseif (preg_match('/^[a-z_\$][a-z0-9\$_]*(\.[a-z_\$][a-z0-9\$_]*)*$/i', $callback)){
            return $callback.'('.json_encode($data, JSON_UNESCAPED_UNICODE).');';
        }
        $data['warning'] = 'INVALID JSONP CALLBACK: '.$callback;
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    public function to_serialized($data = NULL){
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        return serialize($data);
    }
    public function to_php($data = NULL){
        if ($data === NULL && func_num_args() === 0){
            $data = $this->_data;
        }
        return var_export($data, TRUE);
    }
    protected function _from_xml($data) {
        return $data ? (array) simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA) : [];
    }
    protected function _from_csv($data, $delimiter = ',', $enclosure = '"') {
        if ($delimiter === NULL){
            $delimiter = ',';
        }
        if ($enclosure === NULL){
            $enclosure = '"';
        }
        return str_getcsv($data, $delimiter, $enclosure);
    }
    protected function _from_json($data){
        return json_decode(trim($data));
    }
    protected function _from_serialize($data)
    {
        return unserialize(trim($data));
    }
    protected function _from_php($data){
        return trim($data);
    }
}