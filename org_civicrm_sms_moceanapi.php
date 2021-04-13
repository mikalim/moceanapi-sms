<?php

/**
 * Class CRM_SMS_Provider_MoceanAPI
 */
Class org_civicrm_sms_moceanapi extends CRM_SMS_Provider {

    /**
   * api type to use to send a message
   * @var	string
   */
  protected $_apiType = 'http';

  /**
   * provider details
   * @var	string
   */
  protected $_providerInfo = array();

  /**
   * MoceanAPI API Server Session ID
   *
   * @var string
   */
  protected $_sessionID = NULL;

  /**
   * Curl handle resource id
   *
   */
  protected $_ch;

  public $_apiURL = "https://rest.moceanapi.com/rest/2/sms";

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = array();

  /**
   * Constructor
   * Create and auth a MoceanAPI session.
   *
   * @param array $provider
   * @param bool $skipAuth
   * 
   * @return \org_civicrm_sms_moceanapi
   */
  function __construct($provider = array( ), $skipAuth = FALSE) {
        // initialize vars
        $this->_apiType = CRM_Utils_Array::value('api_type', $provider, 'http');
        $this->_providerInfo = $provider;

        if ($skipAuth) {
            return TRUE;
        }
        // first create the curl handle

        /**
         * Reuse the curl handle
         */
        $this->_ch = curl_init();
        if (!$this->_ch || !is_resource($this->_ch)) {
        return PEAR::raiseError('Cannot initialise a new curl handle.');
        }

        curl_setopt($this->_ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->_ch, CURLOPT_VERBOSE, 1);
        curl_setopt($this->_ch, CURLOPT_FAILONERROR, 1);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {
            curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($this->_ch, CURLOPT_COOKIEJAR, "/dev/null");
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_ch, CURLOPT_USERAGENT, 'CiviCRM - http://civicrm.org/'); 
  }

  /**
   * singleton function used to manage this object
   *
   * @param array $providerParams
   * @param bool $force
   * @return object
   * @static
   */
    static function &singleton($providerParams = array(), $force = FALSE) {
        $providerID = CRM_Utils_Array::value('provider_id', $providerParams);
        $skipAuth   = $providerID ? FALSE : TRUE;
        $cacheKey   = (int) $providerID;

        if (!isset(self::$_singleton[$cacheKey]) || $force) {
            $provider = array();
            if ($providerID) {
                $provider = CRM_SMS_BAO_Provider::getProviderInfo($providerID);
            }
            self::$_singleton[$cacheKey] = new org_civicrm_sms_moceanapi($provider, $skipAuth);
        }
        return self::$_singleton[$cacheKey];
    }


    /**
     * Send an SMS Message via the MoceanAPI API Server
     *
     * @param array the message with a to/from/text
     *
     * @return mixed SID on success or PEAR_Error object
     * @access public
     */
    function send($recipients, $header, $message, $jobID = NULL, $userID = NULL) {
        $url = $this->_providerInfo['api_url'];
        $user = $this->_providerInfo['username'];
        $password = $this->_providerInfo['password'];
        if (array_key_exists('mocean-from', $this->_providerInfo['api_params'])) {
            $from = $this->_providerInfo['api_params']['mocean-from'];
        }

        if ($this->_apiType = 'http') {
            $postDataArray = array( 
                'mocean-api-key' => $user,
                'mocean-api-secret' => $password,
                'mocean-from' => $from,
                'mocean-text' => $message,
                'mocean-to' => array($recipients),
                'mocean-medium'  => 'civicrm',
                'mocean-resp-format' => 'json'
            );

            //connection to the api with the curl command
            curl_setopt($this->_ch, CURLOPT_URL, $url);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, Civi::settings()->get('verifySSL') ? 2 : 0);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, Civi::settings()->get('verifySSL'));
            curl_setopt($this->_ch, CURLOPT_POST, 1); 
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($postDataArray));
            
            //added to curl command to close the inteface once the message submitted
            curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->_ch, CURLOPT_TIMEOUT, 36000);

            //execute the curl commande + Send the data out over the wire
            $response = curl_exec($this->_ch);
            
            if (empty($response)) {
                $errorMessage = 'Error: "' . curl_error($this->_ch) . '" - Code: ' . curl_errno($this->_ch);
                return PEAR::raiseError($errorMessage);
            }
            if (PEAR::isError($response)) {
                return $response;
            }
            
            $result = json_decode($response, TRUE);

            if (!empty($result['errorCode'])) {
                return PEAR::raiseError($result['message'], $result['errorCode']);
            }

            $id = date('YmdHis');
            $this->createActivity($id, $message, $header, $jobID, $userID);
            return $id;
        }
    }

   


}




?>
