<?php

class smsnocAPI {
    private $user_token; // USER API TOKEN
    private $user_key; //USER API KEY
    private $sender_id="smsnoc"; //USER SENDER KEY AND DEFAULT WebSMS
    private $country_code="880";//Default Country Code Bangladesh //880 with out +
    protected $url='https://app.smsnoc.com/api/v3/SendSMS?';// ALWAYS USE THIS LINK TO CALL API SERVICE
    
    public $msgType="sms";// Message type sms/voice/unicode/flash/music/mms/whatsapp
    public $route=0;// Your Routing Path Default 0
    public $file=false;// File URL for voice or whatsapp. Default not set
    public $scheduledate=false;//Date and Time to send message (YYYY-MM-DD HH:mm:ss) Default not use
    public $duration=false;//Duration of your voice message in seconds (required for voice)
    public $language=false;//Language of voice message (required for text-to-speach)

    /**
     * To Find your api details please log and go into https://smsnoc.com.bd | https://www.smsnoc.com
     */
    /**
     * Call to site
     */
    private function Call($params){
        if($params){ 
            $params = str_replace(" ", '%20', $params);
            $curl_handle=curl_init();
            curl_setopt($curl_handle,CURLOPT_URL,$this->url.$params);
            curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
            curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);
            if($buffer){ 
                return $buffer;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Set user Credentials
     * @return boolen
     */
    public function setUser($key,$token){
        if($key && $token){
            $this->user_key=$key;
            $this->user_token=$token;
            return true;
        }else{
            return false;
        }
    }

    /**
     * Set Sender ID
     * @return boolen
     */
    public function setSenderID($sender_id){
        if($sender_id){
            $this->sender_id=$sender_id;
            return true;
        }else{
            return false;
        }
    }

    /**
     * Set Default Routing
     * @return boolen
     */
    public function RouteNumber($number){
        if($number){
            $explode=str_split($number);
            if($explode[0]=="+"){
                unset($explode[0]);
                $number=implode("",$explode);
            }else{
                if($explode[0]==0){
                    unset($explode[0]);
                    $number=implode("",$explode);
                }
                $number=$this->country_code.$number;
            }
            return $number;
        }else{
            return false;
        }
    }

    /**
     * Check avalible credit balance
     * @return array
     */
   public function CheckBalance() {
    if ($this->user_key) {
        $url = "https://app.smsnoc.com/api/v3/balance";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Authorization: Bearer $this->user_key",
            "Content-Type: application/json",
            "Accept: application/json"
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $resp = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return false; // cURL error occurred
        }

        $response = json_decode($resp);

        if ($response && isset($response->status) && $response->status === 'success') {
            return array(
                'balance' => $response->data->remaining_balance,
                'expiration_date' => $response->data->expired_on
            );
        } else {
            return false; // Invalid response or status is not success
        }
    } else {
        return false; // No user key
    }
}



    /**
     * Check SMS status
     * group_id = The group_id returned by send sms request
     * @return array
     */
    public function CheckStatus($group_id,$json=FALSE){
        if($group_id){
            $param="&groupstatus&apikey=".$this->user_key."&apitoken=".$this->user_token."&groupid=".$group_id;
            if($res=$this->Call($param)){
                if($json===FALSE){
                    $c=json_decode($res);//You can also use direct json by call json as true
                    if($c['status']=="error"){
                        return false;
                    }else{
                        return $c;
                    }
                }else{
                    return $res;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Send Message
     * @return boolen
     */
    public function SendMessage($Mobile, $TEXT, $json = false) {
    $url = "https://app.smsnoc.com/api/v3/sms/send";
    $id_token = $this->user_key;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Authorization: Bearer $id_token",
        "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = array(
        'recipient' => $Mobile,
        'sender_id' => $this->sender_id,
        'type' => 'plain',
        'message' => $TEXT,
    );

    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($curl);
    curl_close($curl);

    
    return json_decode($response, true);
}

    

}
