<?php
  class api extends common {
    public $user_id;
    public $fcm_token;
    public $merch_id;
    public $loc_id;
    public $token;
    public $mode;
    public $view;
    public $userData = array();
    public $userRoles = array();
    
	  public function convert_to_json($data) {
      header('Content-type: application/json');
		  return json_encode($data, JSON_PRETTY_PRINT);
    }

    private function array_to_xml(array $arr, SimpleXMLElement $xml) {
        foreach( $arr as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.($key+1); //dealing with <0/>..<n/> issues
                }
                $subnode = $xml->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml->addChild("$key",htmlspecialchars("$value"));
            }
		}
		
		return $xml;
    }
    
    function convert_to_xml($data) {
        header('Content-type: application/xml');
        header('Pragma: public');
        header('Cache-control: private');
        header('Expires: -1');
        echo $this->array_to_xml($data, new SimpleXMLElement('<skrinAd/>'))->asXML();
    }
  }
  include_once("apiAdmin.php");

  $apiAdmin		= new apiAdmin;
?>