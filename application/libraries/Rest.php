<?php
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class Rest extends \CI_Controller {
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;
    const HTTP_OK = 200;    
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;         
    const HTTP_ALREADY_REPORTED = 208;      
    const HTTP_IM_USED = 226;            
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
    protected $rest_format = NULL;
    protected $methods = [];
    protected $allowed_http_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];
    protected $request = NULL;
    protected $response = NULL;
    protected $rest = NULL;
    protected $_get_args = [];
    protected $_post_args = [];
    protected $_put_args = [];
    protected $_delete_args = [];
    protected $_patch_args = [];
    protected $_head_args = [];
    protected $_options_args = [];
    protected $_query_args = [];
    protected $_args = [];
    protected $_insert_id = '';
    protected $_allow = TRUE;
    protected $_user_ldap_dn = '';
    protected $_start_rtime;
    protected $_end_rtime;
    protected $_supported_formats = [
            'json' => 'application/json',
            'array' => 'application/json',
            'csv' => 'application/csv',
            'html' => 'text/html',
            'jsonp' => 'application/javascript',
            'php' => 'text/plain',
            'serialized' => 'application/vnd.php.serialized',
            'xml' => 'application/xml'
        ];
    protected $_apiuser;
    protected $check_cors = NULL;
    protected $_enable_xss = FALSE;
    protected $http_status_codes = [
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'CREATED',
        self::HTTP_NO_CONTENT => 'NO CONTENT',
        self::HTTP_NOT_MODIFIED => 'NOT MODIFIED',
        self::HTTP_BAD_REQUEST => 'BAD REQUEST',
        self::HTTP_UNAUTHORIZED => 'UNAUTHORIZED',
        self::HTTP_FORBIDDEN => 'FORBIDDEN',
        self::HTTP_NOT_FOUND => 'NOT FOUND',
        self::HTTP_METHOD_NOT_ALLOWED => 'METHOD NOT ALLOWED',
        self::HTTP_NOT_ACCEPTABLE => 'NOT ACCEPTABLE',
        self::HTTP_CONFLICT => 'CONFLICT',
        self::HTTP_INTERNAL_SERVER_ERROR => 'INTERNAL SERVER ERROR',
        self::HTTP_NOT_IMPLEMENTED => 'NOT IMPLEMENTED'
    ];
    protected function early_checks(){
    }
    public function __construct($config = 'rest'){
        parent::__construct();
        $this->preflight_checks();        
        $this->_enable_xss = ($this->config->item('global_xss_filtering') === TRUE);
        $this->output->parse_exec_vars = FALSE;        
        $this->_start_rtime = microtime(TRUE);
        // Load the rest.php configuration file
        $this->load->config($config);        
        $this->load->library('format');        
        $supported_formats = $this->config->item('rest_supported_formats');        
        if (empty($supported_formats)){
            $supported_formats = [];
        }
        if ( ! is_array($supported_formats)){
            $supported_formats = [$supported_formats];
        }        
        $default_format = $this->_get_default_output_format();
        if (!in_array($default_format, $supported_formats)){
            $supported_formats[] = $default_format;
        }        
        $this->_supported_formats = array_intersect_key($this->_supported_formats, array_flip($supported_formats));
        $language = $this->config->item('rest_language');
        if ($language === NULL){
            $language = 'english';
        }        
        $this->lang->load('rest_controller', $language, FALSE, TRUE, __DIR__."/../");        
        $this->request = new stdClass();
        $this->response = new stdClass();
        $this->rest = new stdClass();        
        if ($this->config->item('rest_ip_blacklist_enabled') === TRUE){
            $this->_check_blacklist_auth();
        }
        $this->request->ssl = is_https();        
        $this->request->method = $this->_detect_method();
        $check_cors = $this->config->item('check_cors');
        if ($check_cors === TRUE){
            $this->_check_cors();
        }        
        if (isset($this->{'_'.$this->request->method.'_args'}) === FALSE){
            $this->{'_'.$this->request->method.'_args'} = [];
        }        
        $this->_parse_query();       
        $this->_get_args = array_merge($this->_get_args, $this->uri->ruri_to_assoc());        
        $this->request->format = $this->_detect_input_format();       
        $this->request->body = NULL;
        $this->{'_parse_' . $this->request->method}(); 
        if($this->{'_'.$this->request->method.'_args'} === null){
            $this->{'_'.$this->request->method.'_args'} = [];
        }       
        if ($this->request->format && $this->request->body){
            $this->request->body = $this->format->factory($this->request->body, $this->request->format)->to_array();            
            $this->{'_'.$this->request->method.'_args'} = $this->request->body;
        }        
        $this->_head_args = $this->input->request_headers();        
        $this->_args = array_merge(
            $this->_get_args,
            $this->_options_args,
            $this->_patch_args,
            $this->_head_args,
            $this->_put_args,
            $this->_post_args,
            $this->_delete_args,
            $this->{'_'.$this->request->method.'_args'}
        );        
        $this->response->format = $this->_detect_output_format();       
        $this->response->lang = $this->_detect_lang();        
        $this->early_checks();        
        if ($this->config->item('rest_database_group') && ($this->config->item('rest_enable_keys') || $this->config->item('rest_enable_logging'))){
            $this->rest->db = $this->load->database($this->config->item('rest_database_group'), TRUE);
        } elseif (property_exists($this, 'db')){
            $this->rest->db = $this->db;
        }
        $this->auth_override = $this->_auth_override_check();        
        if ($this->config->item('rest_enable_keys') && $this->auth_override !== TRUE){
            $this->_allow = $this->_detect_api_key();
        }        
        if ($this->input->is_ajax_request() === FALSE && $this->config->item('rest_ajax_only')){           
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ajax_only')
                ], self::HTTP_NOT_ACCEPTABLE);
        }       
        if ($this->auth_override === FALSE &&
            (! ($this->config->item('rest_enable_keys') && $this->_allow === TRUE) ||
            ($this->config->item('allow_auth_and_keys') === TRUE && $this->_allow === TRUE))){
            $rest_auth = strtolower($this->config->item('rest_auth'));
            switch ($rest_auth){
                case 'basic':
                    $this->_prepare_basic_auth();
                    break;
                case 'digest':
                    $this->_prepare_digest_auth();
                    break;
                case 'session':
                    $this->_check_php_session();
                    break;
            }
            if ($this->config->item('rest_ip_whitelist_enabled') === TRUE){
                $this->_check_whitelist_auth();
            }
        }
    }
    public function __destruct(){        
        $this->_end_rtime = microtime(TRUE);
        if ($this->config->item('rest_enable_logging') === TRUE){
            $this->_log_access_time();
        }
    }    
    protected function preflight_checks(){        
        if (is_php('5.4') === FALSE){            
            throw new Exception('Using PHP v'.PHP_VERSION.', though PHP v5.4 or greater is required');
        }        
        if (explode('.', CI_VERSION, 2)[0] < 3){
            throw new Exception('REST Server requires CodeIgniter 3.x');
        }
    }    
    public function _remap($object_called, $arguments = []){        
        if ($this->config->item('force_https') && $this->request->ssl === FALSE){
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unsupported')
                ], self::HTTP_FORBIDDEN);
        }        
        $object_called = preg_replace('/^(.*)\.(?:'.implode('|', array_keys($this->_supported_formats)).')$/', '$1', $object_called);
        $controller_method = $object_called.'_'.$this->request->method;	    
	    if (!method_exists($this, $controller_method)) {
		    $controller_method = "index_" . $this->request->method;
		    array_unshift($arguments, $object_called);
	    }        
        $log_method = ! (isset($this->methods[$controller_method]['log']) && $this->methods[$controller_method]['log'] === FALSE);        
        $use_key = ! (isset($this->methods[$controller_method]['key']) && $this->methods[$controller_method]['key'] === FALSE);
        if ($this->config->item('rest_enable_keys') && $use_key && $this->_allow === FALSE){
            if ($this->config->item('rest_enable_logging') && $log_method){
                $this->_log_request();
            }                        
            if($this->request->method == 'options') {
                exit;
            }
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => sprintf($this->lang->line('text_rest_invalid_api_key'), $this->rest->key)
                ], self::HTTP_FORBIDDEN);
        }        
        if ($this->config->item('rest_enable_keys') && $use_key && empty($this->rest->key) === FALSE && $this->_check_access() === FALSE){
            if ($this->config->item('rest_enable_logging') && $log_method){
                $this->_log_request();
            }

            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_unauthorized')
                ], self::HTTP_UNAUTHORIZED);
        }
        if (! method_exists($this, $controller_method)){
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unknown_method')
                ], self::HTTP_METHOD_NOT_ALLOWED);
        }        
        if ($this->config->item('rest_enable_keys') && empty($this->rest->key) === FALSE){            
            if ($this->config->item('rest_enable_limits') && $this->_check_limit($controller_method) === FALSE){
                $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_time_limit')];
                $this->response($response, self::HTTP_UNAUTHORIZED);
            }            
            $level = isset($this->methods[$controller_method]['level']) ? $this->methods[$controller_method]['level'] : 0;            
            $authorized = $level <= $this->rest->level;            
            if ($this->config->item('rest_enable_logging') && $log_method){
                $this->_log_request($authorized);
            }
            if($authorized === FALSE){                
                $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_permissions')];
                $this->response($response, self::HTTP_UNAUTHORIZED);
            }
        }elseif ($this->config->item('rest_limits_method') == "IP_ADDRESS" && $this->config->item('rest_enable_limits') && $this->_check_limit($controller_method) === FALSE){
            $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_address_time_limit')];
            $this->response($response, self::HTTP_UNAUTHORIZED);
        }elseif ($this->config->item('rest_enable_logging') && $log_method){
            $this->_log_request($authorized = TRUE);
        }try{
            call_user_func_array([$this, $controller_method], $arguments);
        }catch (Exception $ex){
            if ($this->config->item('rest_handle_exceptions') === FALSE) {
                throw $ex;
            }            
	        $_error = &load_class('Exceptions', 'core');
	        $_error->show_exception($ex);
        }
    }
    public function response($data = NULL, $http_code = NULL){
		ob_start();       
        if ($http_code !== NULL){            
            $http_code = (int) $http_code;
        }       
        $output = NULL;
        if ($data === NULL && $http_code === NULL){
            $http_code = self::HTTP_NOT_FOUND;
        }elseif ($data !== NULL){            
            if (method_exists($this->format, 'to_' . $this->response->format)){               
                $this->output->set_content_type($this->_supported_formats[$this->response->format], strtolower($this->config->item('charset')));
                $output = $this->format->factory($data)->{'to_' . $this->response->format}();                
                if ($this->response->format === 'array'){
                    $output = $this->format->factory($output)->{'to_json'}();
                }
            }else{                
                if (is_array($data) || is_object($data)){
                    $data = $this->format->factory($data)->{'to_json'}();
                }                
                $output = $data;
            }
        }        
        $http_code > 0 || $http_code = self::HTTP_OK;
        $this->output->set_status_header($http_code);        
        if ($this->config->item('rest_enable_logging') === TRUE){
            $this->_log_response_code($http_code);
        }        
        $this->output->set_output($output);
        ob_end_flush();        
    }    
    public function set_response($data = NULL, $http_code = NULL) {
        $this->response($data, $http_code, TRUE);
    }    
    protected function _detect_input_format(){
        $content_type = $this->input->server('CONTENT_TYPE');
        if (empty($content_type) === FALSE){            
            $content_type = (strpos($content_type, ';') !== FALSE ? current(explode(';', $content_type)) : $content_type);
            foreach ($this->_supported_formats as $type => $mime){                
                if ($content_type === $mime) {
                    return $type;
                }
            }
        }
        return NULL;
    }    
    protected function _get_default_output_format(){
        $default_format = (string) $this->config->item('rest_default_format');
        return $default_format === '' ? 'json' : $default_format;
    }
    /**
     * Detect which format should be used to output the data
     *
     * @access protected
     * @return mixed|NULL|string Output format
     */
    protected function _detect_output_format()
    {
        // Concatenate formats to a regex pattern e.g. \.(csv|json|xml)
        $pattern = '/\.('.implode('|', array_keys($this->_supported_formats)).')($|\/)/';
        $matches = [];

        // Check if a file extension is used e.g. http://example.com/api/index.json?param1=param2
        if (preg_match($pattern, $this->uri->uri_string(), $matches))
        {
            return $matches[1];
        }

        // Get the format parameter named as 'format'
        if (isset($this->_get_args['format']))
        {
            $format = strtolower($this->_get_args['format']);

            if (isset($this->_supported_formats[$format]) === TRUE)
            {
                return $format;
            }
        }

        // Get the HTTP_ACCEPT server variable
        $http_accept = $this->input->server('HTTP_ACCEPT');

        // Otherwise, check the HTTP_ACCEPT server variable
        if ($this->config->item('rest_ignore_http_accept') === FALSE && $http_accept !== NULL)
        {
            // Check all formats against the HTTP_ACCEPT header
            foreach (array_keys($this->_supported_formats) as $format)
            {
                // Has this format been requested?
                if (strpos($http_accept, $format) !== FALSE)
                {
                    if ($format !== 'html' && $format !== 'xml')
                    {
                        // If not HTML or XML assume it's correct
                        return $format;
                    }
                    elseif ($format === 'html' && strpos($http_accept, 'xml') === FALSE)
                    {
                        // HTML or XML have shown up as a match
                        // If it is truly HTML, it wont want any XML
                        return $format;
                    }
                    else if ($format === 'xml' && strpos($http_accept, 'html') === FALSE)
                    {
                        // If it is truly XML, it wont want any HTML
                        return $format;
                    }
                }
            }
        }

        // Check if the controller has a default format
        if (empty($this->rest_format) === FALSE)
        {
            return $this->rest_format;
        }

        // Obtain the default format from the configuration
        return $this->_get_default_output_format();
    }

    /**
     * Get the HTTP request string e.g. get or post
     *
     * @access protected
     * @return string|NULL Supported request method as a lowercase string; otherwise, NULL if not supported
     */
    protected function _detect_method()
    {
        // Declare a variable to store the method
        $method = NULL;

        // Determine whether the 'enable_emulate_request' setting is enabled
        if ($this->config->item('enable_emulate_request') === TRUE)
        {
            $method = $this->input->post('_method');
            if ($method === NULL)
            {
                $method = $this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE');
            }

            $method = strtolower($method);
        }

        if (empty($method))
        {
            // Get the request method as a lowercase string
            $method = $this->input->method();
        }

        return in_array($method, $this->allowed_http_methods) && method_exists($this, '_parse_' . $method) ? $method : 'get';
    }

    /**
     * See if the user has provided an API key
     *
     * @access protected
     * @return bool
     */
    protected function _detect_api_key()
    {
        // Get the api key name variable set in the rest config file
        $api_key_variable = $this->config->item('rest_key_name');

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));

        $this->rest->key = NULL;
        $this->rest->level = NULL;
        $this->rest->user_id = NULL;
        $this->rest->ignore_limits = FALSE;

        // Find the key from server or arguments
        if (($key = isset($this->_args[$api_key_variable]) ? $this->_args[$api_key_variable] : $this->input->server($key_name)))
        {
            if ( ! ($row = $this->rest->db->where($this->config->item('rest_key_column'), $key)->get($this->config->item('rest_keys_table'))->row()))
            {
                return FALSE;
            }

            $this->rest->key = $row->{$this->config->item('rest_key_column')};

            isset($row->user_id) && $this->rest->user_id = $row->user_id;
            isset($row->level) && $this->rest->level = $row->level;
            isset($row->ignore_limits) && $this->rest->ignore_limits = $row->ignore_limits;

            $this->_apiuser = $row;

            /*
             * If "is private key" is enabled, compare the ip address with the list
             * of valid ip addresses stored in the database
             */
            if (empty($row->is_private_key) === FALSE)
            {
                // Check for a list of valid ip addresses
                if (isset($row->ip_addresses))
                {
                    // multiple ip addresses must be separated using a comma, explode and loop
                    $list_ip_addresses = explode(',', $row->ip_addresses);
                    $found_address = FALSE;

                    foreach ($list_ip_addresses as $ip_address)
                    {
                        if ($this->input->ip_address() === trim($ip_address))
                        {
                            // there is a match, set the the value to TRUE and break out of the loop
                            $found_address = TRUE;
                            break;
                        }
                    }

                    return $found_address;
                }
                else
                {
                    // There should be at least one IP address for this private key
                    return FALSE;
                }
            }

            return TRUE;
        }

        // No key has been sent
        return FALSE;
    }

    /**
     * Preferred return language
     *
     * @access protected
     * @return string|NULL The language code
     */
    protected function _detect_lang()
    {
        $lang = $this->input->server('HTTP_ACCEPT_LANGUAGE');
        if ($lang === NULL)
        {
            return NULL;
        }

        // It appears more than one language has been sent using a comma delimiter
        if (strpos($lang, ',') !== FALSE)
        {
            $langs = explode(',', $lang);

            $return_langs = [];
            foreach ($langs as $lang)
            {
                // Remove weight and trim leading and trailing whitespace
                list($lang) = explode(';', $lang);
                $return_langs[] = trim($lang);
            }

            return $return_langs;
        }

        // Otherwise simply return as a string
        return $lang;
    }

    /**
     * Add the request to the log table
     *
     * @access protected
     * @param bool $authorized TRUE the user is authorized; otherwise, FALSE
     * @return bool TRUE the data was inserted; otherwise, FALSE
     */
    protected function _log_request($authorized = FALSE)
    {
        // Insert the request into the log table
        $is_inserted = $this->rest->db
            ->insert(
                $this->config->item('rest_logs_table'), [
                'uri' => $this->uri->uri_string(),
                'method' => $this->request->method,
                'params' => $this->_args ? ($this->config->item('rest_logs_json_params') === TRUE ? json_encode($this->_args) : serialize($this->_args)) : NULL,
                'api_key' => isset($this->rest->key) ? $this->rest->key : '',
                'ip_address' => $this->input->ip_address(),
                'time' => time(),
                'authorized' => $authorized
            ]);

        // Get the last insert id to update at a later stage of the request
        $this->_insert_id = $this->rest->db->insert_id();

        return $is_inserted;
    }

    /**
     * Check if the requests to a controller method exceed a limit
     *
     * @access protected
     * @param string $controller_method The method being called
     * @return bool TRUE the call limit is below the threshold; otherwise, FALSE
     */
    protected function _check_limit($controller_method)
    {
        // They are special, or it might not even have a limit
        if (empty($this->rest->ignore_limits) === FALSE)
        {
            // Everything is fine
            return TRUE;
        }

        $api_key = isset($this->rest->key) ? $this->rest->key : '';

        switch ($this->config->item('rest_limits_method'))
        {
          case 'IP_ADDRESS':
            $limited_uri = 'ip-address:' .$this->input->ip_address();
            $api_key = $this->input->ip_address();
            break;

          case 'API_KEY':
            $limited_uri = 'api-key:' . $api_key;
            break;

          case 'METHOD_NAME':
            $limited_uri = 'method-name:' . $controller_method;
            break;

          case 'ROUTED_URL':
          default:
            $limited_uri = $this->uri->ruri_string();
            if (strpos(strrev($limited_uri), strrev($this->response->format)) === 0)
            {
                $limited_uri = substr($limited_uri,0, -strlen($this->response->format) - 1);
            }
            $limited_uri = 'uri:'.$limited_uri.':'.$this->request->method; // It's good to differentiate GET from PUT
            break;
        }

        if (isset($this->methods[$controller_method]['limit']) === FALSE )
        {
            // Everything is fine
            return TRUE;
        }

        // How many times can you get to this method in a defined time_limit (default: 1 hour)?
        $limit = $this->methods[$controller_method]['limit'];

        $time_limit = (isset($this->methods[$controller_method]['time']) ? $this->methods[$controller_method]['time'] : 3600); // 3600 = 60 * 60

        // Get data about a keys' usage and limit to one row
        $result = $this->rest->db
            ->where('uri', $limited_uri)
            ->where('api_key', $api_key)
            ->get($this->config->item('rest_limits_table'))
            ->row();

        // No calls have been made for this key
        if ($result === NULL)
        {
            // Create a new row for the following key
            $this->rest->db->insert($this->config->item('rest_limits_table'), [
                'uri' => $limited_uri,
                'api_key' =>$api_key,
                'count' => 1,
                'hour_started' => time()
            ]);
        }

        // Been a time limit (or by default an hour) since they called
        elseif ($result->hour_started < (time() - $time_limit))
        {
            // Reset the started period and count
            $this->rest->db
                ->where('uri', $limited_uri)
                ->where('api_key', $api_key)
                ->set('hour_started', time())
                ->set('count', 1)
                ->update($this->config->item('rest_limits_table'));
        }

        // They have called within the hour, so lets update
        else
        {
            // The limit has been exceeded
            if ($result->count >= $limit)
            {
                return FALSE;
            }

            // Increase the count by one
            $this->rest->db
                ->where('uri', $limited_uri)
                ->where('api_key', $api_key)
                ->set('count', 'count + 1', FALSE)
                ->update($this->config->item('rest_limits_table'));
        }

        return TRUE;
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called
     *
     * @access protected
     * @return bool
     */
    protected function _auth_override_check()
    {
        // Assign the class/method auth type override array from the config
        $auth_override_class_method = $this->config->item('auth_override_class_method');

        // Check to see if the override array is even populated
        if ( ! empty($auth_override_class_method))
        {
            // Check for wildcard flag for rules for classes
            if ( ! empty($auth_override_class_method[$this->router->class]['*'])) // Check for class overrides
            {
                // No auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class]['*'] === 'none')
                {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class]['*'] === 'basic')
                {
                    $this->_prepare_basic_auth();

                    return TRUE;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method[$this->router->class]['*'] === 'digest')
                {
                    $this->_prepare_digest_auth();

                    return TRUE;
                }

                // Session auth override found, check session
                if ($auth_override_class_method[$this->router->class]['*'] === 'session')
                {
                    $this->_check_php_session();

                    return TRUE;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[$this->router->class]['*'] === 'whitelist')
                {
                    $this->_check_whitelist_auth();

                    return TRUE;
                }
            }

            // Check to see if there's an override value set for the current class/method being called
            if ( ! empty($auth_override_class_method[$this->router->class][$this->router->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'none')
                {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'basic')
                {
                    $this->_prepare_basic_auth();

                    return TRUE;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'digest')
                {
                    $this->_prepare_digest_auth();

                    return TRUE;
                }

                // Session auth override found, check session
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'session')
                {
                    $this->_check_php_session();

                    return TRUE;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'whitelist')
                {
                    $this->_check_whitelist_auth();

                    return TRUE;
                }
            }
        }

        // Assign the class/method/HTTP-method auth type override array from the config
        $auth_override_class_method_http = $this->config->item('auth_override_class_method_http');

        // Check to see if the override array is even populated
        if ( ! empty($auth_override_class_method_http))
        {
            // check for wildcard flag for rules for classes
            if ( ! empty($auth_override_class_method_http[$this->router->class]['*'][$this->request->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'none')
                {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'basic')
                {
                    $this->_prepare_basic_auth();

                    return TRUE;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'digest')
                {
                    $this->_prepare_digest_auth();

                    return TRUE;
                }

                // Session auth override found, check session
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'session')
                {
                    $this->_check_php_session();

                    return TRUE;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'whitelist')
                {
                    $this->_check_whitelist_auth();

                    return TRUE;
                }
            }

            // Check to see if there's an override value set for the current class/method/HTTP-method being called
            if ( ! empty($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'none')
                {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'basic')
                {
                    $this->_prepare_basic_auth();

                    return TRUE;
                }

                // Digest auth override found, prepare digest
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'digest')
                {
                    $this->_prepare_digest_auth();

                    return TRUE;
                }

                // Session auth override found, check session
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'session')
                {
                    $this->_check_php_session();

                    return TRUE;
                }

                // Whitelist auth override found, check client's ip against config whitelist
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'whitelist')
                {
                    $this->_check_whitelist_auth();

                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Parse the GET request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_get()
    {
        // Merge both the URI segments and query parameters
        $this->_get_args = array_merge($this->_get_args, $this->_query_args);
    }

    /**
     * Parse the POST request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_post()
    {
        $this->_post_args = $_POST;

        if ($this->request->format)
        {
            $this->request->body = $this->input->raw_input_stream;
        }
    }

    /**
     * Parse the PUT request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_put()
    {
        if ($this->request->format)
        {
            $this->request->body = $this->input->raw_input_stream;
            if ($this->request->format === 'json')
            {
                $this->_put_args = json_decode($this->input->raw_input_stream);
            }
        }
        else if ($this->input->method() === 'put')
        {
           // If no file type is provided, then there are probably just arguments
           $this->_put_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the HEAD request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_head()
    {
        // Parse the HEAD variables
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $head);

        // Merge both the URI segments and HEAD params
        $this->_head_args = array_merge($this->_head_args, $head);
    }

    /**
     * Parse the OPTIONS request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_options()
    {
        // Parse the OPTIONS variables
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $options);

        // Merge both the URI segments and OPTIONS params
        $this->_options_args = array_merge($this->_options_args, $options);
    }

    /**
     * Parse the PATCH request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_patch()
    {
        // It might be a HTTP body
        if ($this->request->format)
        {
            $this->request->body = $this->input->raw_input_stream;
        }
        else if ($this->input->method() === 'patch')
        {
            // If no file type is provided, then there are probably just arguments
            $this->_patch_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the DELETE request arguments
     *
     * @access protected
     * @return void
     */
    protected function _parse_delete()
    {
        // These should exist if a DELETE request
        if ($this->input->method() === 'delete')
        {
            $this->_delete_args = $this->input->input_stream();
        }
    }

    /**
     * Parse the query parameters
     *
     * @access protected
     * @return void
     */
    protected function _parse_query()
    {
        $this->_query_args = $this->input->get();
    }

    // INPUT FUNCTION --------------------------------------------------------------

    /**
     * Retrieve a value from a GET request
     *
     * @access public
     * @param NULL $key Key to retrieve from the GET request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the GET request; otherwise, NULL
     */
    public function get($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_get_args;
        }

        return isset($this->_get_args[$key]) ? $this->_xss_clean($this->_get_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a OPTIONS request
     *
     * @access public
     * @param NULL $key Key to retrieve from the OPTIONS request.
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the OPTIONS request; otherwise, NULL
     */
    public function options($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_options_args;
        }

        return isset($this->_options_args[$key]) ? $this->_xss_clean($this->_options_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a HEAD request
     *
     * @access public
     * @param NULL $key Key to retrieve from the HEAD request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the HEAD request; otherwise, NULL
     */
    public function head($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_head_args;
        }

        return isset($this->_head_args[$key]) ? $this->_xss_clean($this->_head_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a POST request
     *
     * @access public
     * @param NULL $key Key to retrieve from the POST request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the POST request; otherwise, NULL
     */
    public function post($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_post_args;
        }

        return isset($this->_post_args[$key]) ? $this->_xss_clean($this->_post_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a PUT request
     *
     * @access public
     * @param NULL $key Key to retrieve from the PUT request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the PUT request; otherwise, NULL
     */
    public function put($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_put_args;
        }

        return isset($this->_put_args[$key]) ? $this->_xss_clean($this->_put_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a DELETE request
     *
     * @access public
     * @param NULL $key Key to retrieve from the DELETE request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the DELETE request; otherwise, NULL
     */
    public function delete($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_delete_args;
        }

        return isset($this->_delete_args[$key]) ? $this->_xss_clean($this->_delete_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from a PATCH request
     *
     * @access public
     * @param NULL $key Key to retrieve from the PATCH request
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the PATCH request; otherwise, NULL
     */
    public function patch($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_patch_args;
        }

        return isset($this->_patch_args[$key]) ? $this->_xss_clean($this->_patch_args[$key], $xss_clean) : NULL;
    }

    /**
     * Retrieve a value from the query parameters
     *
     * @access public
     * @param NULL $key Key to retrieve from the query parameters
     * If NULL an array of arguments is returned
     * @param NULL $xss_clean Whether to apply XSS filtering
     * @return array|string|NULL Value from the query parameters; otherwise, NULL
     */
    public function query($key = NULL, $xss_clean = NULL)
    {
        if ($key === NULL)
        {
            return $this->_query_args;
        }

        return isset($this->_query_args[$key]) ? $this->_xss_clean($this->_query_args[$key], $xss_clean) : NULL;
    }

    /**
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented
     *
     * @access protected
     * @param string $value Input data
     * @param bool $xss_clean Whether to apply XSS filtering
     * @return string
     */
    protected function _xss_clean($value, $xss_clean)
    {
        is_bool($xss_clean) || $xss_clean = $this->_enable_xss;

        return $xss_clean === TRUE ? $this->security->xss_clean($value) : $value;
    }

    /**
     * Retrieve the validation errors
     *
     * @access public
     * @return array
     */
    public function validation_errors()
    {
        $string = strip_tags($this->form_validation->error_string());

        return explode(PHP_EOL, trim($string, PHP_EOL));
    }

    // SECURITY FUNCTIONS ---------------------------------------------------------

    /**
     * Perform LDAP Authentication
     *
     * @access protected
     * @param string $username The username to validate
     * @param string $password The password to validate
     * @return bool
     */
    protected function _perform_ldap_auth($username = '', $password = NULL)
    {
        if (empty($username))
        {
            log_message('debug', 'LDAP Auth: failure, empty username');
            return FALSE;
        }

        log_message('debug', 'LDAP Auth: Loading configuration');

        $this->config->load('ldap.php', TRUE);

        $ldap = [
            'timeout' => $this->config->item('timeout', 'ldap'),
            'host' => $this->config->item('server', 'ldap'),
            'port' => $this->config->item('port', 'ldap'),
            'rdn' => $this->config->item('binduser', 'ldap'),
            'pass' => $this->config->item('bindpw', 'ldap'),
            'basedn' => $this->config->item('basedn', 'ldap'),
        ];

        log_message('debug', 'LDAP Auth: Connect to ' . (isset($ldaphost) ? $ldaphost : '[ldap not configured]'));

        // Connect to the ldap server
        $ldapconn = ldap_connect($ldap['host'], $ldap['port']);
        if ($ldapconn)
        {
            log_message('debug', 'Setting timeout to '.$ldap['timeout'].' seconds');

            ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, $ldap['timeout']);

            log_message('debug', 'LDAP Auth: Binding to '.$ldap['host'].' with dn '.$ldap['rdn']);

            // Binding to the ldap server
            $ldapbind = ldap_bind($ldapconn, $ldap['rdn'], $ldap['pass']);

            // Verify the binding
            if ($ldapbind === FALSE)
            {
                log_message('error', 'LDAP Auth: bind was unsuccessful');
                return FALSE;
            }

            log_message('debug', 'LDAP Auth: bind successful');
        }

        // Search for user
        if (($res_id = ldap_search($ldapconn, $ldap['basedn'], "uid=$username")) === FALSE)
        {
            log_message('error', 'LDAP Auth: User '.$username.' not found in search');
            return FALSE;
        }

        if (ldap_count_entries($ldapconn, $res_id) !== 1)
        {
            log_message('error', 'LDAP Auth: Failure, username '.$username.'found more than once');
            return FALSE;
        }

        if (($entry_id = ldap_first_entry($ldapconn, $res_id)) === FALSE)
        {
            log_message('error', 'LDAP Auth: Failure, entry of search result could not be fetched');
            return FALSE;
        }

        if (($user_dn = ldap_get_dn($ldapconn, $entry_id)) === FALSE)
        {
            log_message('error', 'LDAP Auth: Failure, user-dn could not be fetched');
            return FALSE;
        }

        // User found, could not authenticate as user
        if (($link_id = ldap_bind($ldapconn, $user_dn, $password)) === FALSE)
        {
            log_message('error', 'LDAP Auth: Failure, username/password did not match: ' . $user_dn);
            return FALSE;
        }

        log_message('debug', 'LDAP Auth: Success '.$user_dn.' authenticated successfully');

        $this->_user_ldap_dn = $user_dn;

        ldap_close($ldapconn);

        return TRUE;
    }

    /**
     * Perform Library Authentication - Override this function to change the way the library is called
     *
     * @access protected
     * @param string $username The username to validate
     * @param string $password The password to validate
     * @return bool
     */
    protected function _perform_library_auth($username = '', $password = NULL)
    {
        if (empty($username))
        {
            log_message('error', 'Library Auth: Failure, empty username');
            return FALSE;
        }

        $auth_library_class = strtolower($this->config->item('auth_library_class'));
        $auth_library_function = strtolower($this->config->item('auth_library_function'));

        if (empty($auth_library_class))
        {
            log_message('debug', 'Library Auth: Failure, empty auth_library_class');
            return FALSE;
        }

        if (empty($auth_library_function))
        {
            log_message('debug', 'Library Auth: Failure, empty auth_library_function');
            return FALSE;
        }

        if (is_callable([$auth_library_class, $auth_library_function]) === FALSE)
        {
            $this->load->library($auth_library_class);
        }

        return $this->{$auth_library_class}->$auth_library_function($username, $password);
    }

    /**
     * Check if the user is logged in
     *
     * @access protected
     * @param string $username The user's name
     * @param bool|string $password The user's password
     * @return bool
     */
    protected function _check_login($username = NULL, $password = FALSE)
    {
        if (empty($username))
        {
            return FALSE;
        }

        $auth_source = strtolower($this->config->item('auth_source'));
        $rest_auth = strtolower($this->config->item('rest_auth'));
        $valid_logins = $this->config->item('rest_valid_logins');

        if ( ! $this->config->item('auth_source') && $rest_auth === 'digest')
        {
            // For digest we do not have a password passed as argument
            return md5($username.':'.$this->config->item('rest_realm').':'.(isset($valid_logins[$username]) ? $valid_logins[$username] : ''));
        }

        if ($password === FALSE)
        {
            return FALSE;
        }

        if ($auth_source === 'ldap')
        {
            log_message('debug', "Performing LDAP authentication for $username");

            return $this->_perform_ldap_auth($username, $password);
        }

        if ($auth_source === 'library')
        {
            log_message('debug', "Performing Library authentication for $username");

            return $this->_perform_library_auth($username, $password);
        }

        if (array_key_exists($username, $valid_logins) === FALSE)
        {
            return FALSE;
        }

        if ($valid_logins[$username] !== $password)
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check to see if the user is logged in with a PHP session key
     *
     * @access protected
     * @return void
     */
    protected function _check_php_session()
    {
        // Get the auth_source config item
        $key = $this->config->item('auth_source');

        // If false, then the user isn't logged in
        if ( ! $this->session->userdata($key))
        {
            // Display an error response
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized')
                ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Prepares for basic authentication
     *
     * @access protected
     * @return void
     */
    protected function _prepare_basic_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->config->item('rest_ip_whitelist_enabled'))
        {
            $this->_check_whitelist_auth();
        }

        // Returns NULL if the SERVER variables PHP_AUTH_USER and HTTP_AUTHENTICATION don't exist
        $username = $this->input->server('PHP_AUTH_USER');
        $http_auth = $this->input->server('HTTP_AUTHENTICATION');

        $password = NULL;
        if ($username !== NULL)
        {
            $password = $this->input->server('PHP_AUTH_PW');
        }
        elseif ($http_auth !== NULL)
        {
            // If the authentication header is set as basic, then extract the username and password from
            // HTTP_AUTHORIZATION e.g. my_username:my_password. This is passed in the .htaccess file
            if (strpos(strtolower($http_auth), 'basic') === 0)
            {
                // Search online for HTTP_AUTHORIZATION workaround to explain what this is doing
                list($username, $password) = explode(':', base64_decode(substr($this->input->server('HTTP_AUTHORIZATION'), 6)));
            }
        }

        // Check if the user is logged into the system
        if ($this->_check_login($username, $password) === FALSE)
        {
            $this->_force_login();
        }
    }

    /**
     * Prepares for digest authentication
     *
     * @access protected
     * @return void
     */
    protected function _prepare_digest_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->config->item('rest_ip_whitelist_enabled'))
        {
            $this->_check_whitelist_auth();
        }

        // We need to test which server authentication variable to use,
        // because the PHP ISAPI module in IIS acts different from CGI
        $digest_string = $this->input->server('PHP_AUTH_DIGEST');
        if ($digest_string === NULL)
        {
            $digest_string = $this->input->server('HTTP_AUTHORIZATION');
        }

        $unique_id = uniqid();

        // The $_SESSION['error_prompted'] variable is used to ask the password
        // again if none given or if the user enters wrong auth information
        if (empty($digest_string))
        {
            $this->_force_login($unique_id);
        }

        // We need to retrieve authentication data from the $digest_string variable
        $matches = [];
        preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
        $digest = (empty($matches[1]) || empty($matches[2])) ? [] : array_combine($matches[1], $matches[2]);

        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        $username = $this->_check_login($digest['username'], TRUE);
        if (array_key_exists('username', $digest) === FALSE || $username === FALSE)
        {
            $this->_force_login($unique_id);
        }

        $md5 = md5(strtoupper($this->request->method).':'.$digest['uri']);
        $valid_response = md5($username.':'.$digest['nonce'].':'.$digest['nc'].':'.$digest['cnonce'].':'.$digest['qop'].':'.$md5);

        // Check if the string don't compare (case-insensitive)
        if (strcasecmp($digest['response'], $valid_response) !== 0)
        {
            // Display an error response
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_invalid_credentials')
                ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Checks if the client's ip is in the 'rest_ip_blacklist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    protected function _check_blacklist_auth()
    {
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf('/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $this->input->ip_address());

        // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
        if (preg_match($pattern, $this->config->item('rest_ip_blacklist')))
        {
            // Display an error response
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_denied')
                ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Check if the client's ip is in the 'rest_ip_whitelist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    protected function _check_whitelist_auth()
    {
        $whitelist = explode(',', $this->config->item('rest_ip_whitelist'));

        array_push($whitelist, '127.0.0.1', '0.0.0.0');

        foreach ($whitelist as &$ip)
        {
            // As $ip is a reference, trim leading and trailing whitespace, then store the new value
            // using the reference
            $ip = trim($ip);
        }

        if (in_array($this->input->ip_address(), $whitelist) === FALSE)
        {
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_unauthorized')
                ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Force logging in by setting the WWW-Authenticate header
     *
     * @access protected
     * @param string $nonce A server-specified data string which should be uniquely generated
     * each time
     * @return void
     */
    protected function _force_login($nonce = '')
    {
        $rest_auth = $this->config->item('rest_auth');
        $rest_realm = $this->config->item('rest_realm');
        if (strtolower($rest_auth) === 'basic')
        {
            // See http://tools.ietf.org/html/rfc2617#page-5
            header('WWW-Authenticate: Basic realm="'.$rest_realm.'"');
        }
        elseif (strtolower($rest_auth) === 'digest')
        {
            // See http://tools.ietf.org/html/rfc2617#page-18
            header(
                'WWW-Authenticate: Digest realm="'.$rest_realm
                .'", qop="auth", nonce="'.$nonce
                .'", opaque="' . md5($rest_realm).'"');
        }

        // Display an error response
        $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized')
            ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * Updates the log table with the total access time
     *
     * @access protected
     * @author Chris Kacerguis
     * @return bool TRUE log table updated; otherwise, FALSE
     */
    protected function _log_access_time()
    {
        $payload['rtime'] = $this->_end_rtime - $this->_start_rtime;

        return $this->rest->db->update(
                $this->config->item('rest_logs_table'), $payload, [
                'id' => $this->_insert_id
            ]);
    }

    /**
     * Updates the log table with HTTP response code
     *
     * @access protected
     * @author Justin Chen
     * @param $http_code int HTTP status code
     * @return bool TRUE log table updated; otherwise, FALSE
     */
    protected function _log_response_code($http_code)
    {
        $payload['response_code'] = $http_code;

        return $this->rest->db->update(
            $this->config->item('rest_logs_table'), $payload, [
            'id' => $this->_insert_id
        ]);
    }

    /**
     * Check to see if the API key has access to the controller and methods
     *
     * @access protected
     * @return bool TRUE the API key has access; otherwise, FALSE
     */
    protected function _check_access()
    {
        // If we don't want to check access, just return TRUE
        if ($this->config->item('rest_enable_access') === FALSE)
        {
            return TRUE;
        }

        //check if the key has all_access
        $accessRow = $this->rest->db
            ->where('key', $this->rest->key)
            ->get($this->config->item('rest_access_table'))->row_array();

        if (!empty($accessRow) && !empty($accessRow['all_access']))
        {
        	return TRUE;
        }

        // Fetch controller based on path and controller name
        $controller = implode(
            '/', [
            $this->router->directory,
            $this->router->class
        ]);

        // Remove any double slashes for safety
        $controller = str_replace('//', '/', $controller);

        // Query the access table and get the number of results
        return $this->rest->db
            ->where('key', $this->rest->key)
            ->where('controller', $controller)
            ->get($this->config->item('rest_access_table'))
            ->num_rows() > 0;
    }

    /**
     * Checks allowed domains, and adds appropriate headers for HTTP access control (CORS)
     *
     * @access protected
     * @return void
     */
    protected function _check_cors() {        
        $allowed_headers = implode(' ,', $this->config->item('allowed_cors_headers'));
        $allowed_methods = implode(' ,', $this->config->item('allowed_cors_methods'));
        if ($this->config->item('allow_any_cors_domain') === TRUE){
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: '.$allowed_headers);
            header('Access-Control-Allow-Methods: '.$allowed_methods);
        } else {            
            $origin = $this->input->server('HTTP_ORIGIN');
            if ($origin === NULL){
                $origin = '';
            }           
            if (in_array($origin, $this->config->item('allowed_cors_origins'))){
                header('Access-Control-Allow-Origin: '.$origin);
                header('Access-Control-Allow-Headers: '.$allowed_headers);
                header('Access-Control-Allow-Methods: '.$allowed_methods);
            }
        }   
        if ($this->input->method() === 'options'){
            exit;
        }
    }
}
