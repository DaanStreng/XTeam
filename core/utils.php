<?php
namespace  XTeam\Framework\Core;
class Utils{
 static function encrypt($pure_string, $encryption_key) {
    $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
    return base64_encode($encrypted_string);
    }
    
    /**
     * Returns decrypted original string
     */
   static function decrypt($encrypted_string, $encryption_key) {
        $encrypted_string = base64_decode($encrypted_string);
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }
    
  static  function curl_download($Url,$username,$password,$cookie_file){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$Url);
      curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
      curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
    
        curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie_file);
      
        curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie_file) ;
      $http_headers = array(
                        'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                        'Accept: */*',
                        'Accept-Language: en-us,en;q=0.5',
                        'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                        'Connection: keep-alive',
                        'Host: team.lisa-is.nl'
                      );
        if ($username && $password)
      curl_setopt($ch, CURLOPT_POSTFIELDS,
                "MemberId=".$username."&Password=".$password."&RememberMe=false");
      curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
      $output = curl_exec($ch);
      curl_close($ch);
      return $output;
    }
    
    static  function loginToLisa($Url,$username,$password,$cookie_file,$data = false){
      $ch = curl_init();

            $http_headers = array(
                       
                      );
      curl_setopt($ch, CURLOPT_URL,$Url);
      curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    //  curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
 //     curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file);
      if(file_exists($cookie_file)&&$data){
      $cookies = file_get_contents($cookie_file);

       curl_setopt($ch, CURLOPT_COOKIE,  htmlentities($cookies));
    //  }*/
        $cookies = json_decode($cookies);
        foreach($cookies as $c){
       $http_headers[] = "Set-Cookie: ".$c;
      }
      }
      
      curl_setopt( $ch, CURLOPT_POST, true);
     

                 if(!$data){
                   $data = "frm_usernaam=".$username."&frm_wachtwoord=".$password."&B1=Aanmelden";
                 }
                 else{
                   $http_headers[] = "Content-Length: ".(strlen($data));
                 
                 }
                          $http_headers[] = "Content-Type: application/x-www-form-urlencoded; Charset=utf-8";
                      
                  //        $http_headers[] = "Content-Length: ".(strlen($data));
                          $http_headers[] = "Content-Encoding: identity";
                          $http_headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
                          $http_headers[] = "Host: login.lisa-is.nl";
                          $http_headers[] = "Connection: Keep-Alive";
                          $http_headers[] = "Origin: https://login.lisa-is.nl";
                          $http_headers[] = "Referer: https://login.lisa-is.nl/lisa2/default.asp?MenuItem=10";
                          $http_headers[] = "Upgrade-Insecure-Requests: 1";
                     //     $http_headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
                          $http_headers[] = "Accept-Encoding: gzip, deflate, br";
                          curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36');
                 
     if ($data){
             curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    }
   // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HEADER, 1);
   
      $output = curl_exec($ch);
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $header = substr($output, 0, $header_size);
      $body = substr($output, $header_size);
      $results = explode("Set-Cookie",$header);
      $cookies = [];
      foreach($results as $r){
        $r = trim($r);
        if(strpos(trim($r),":")==0){
          $r = substr($r,1);
          if(strrpos($r,":")>1){
            $cookie = substr($r,0,strpos($r,":"));
            $cookie = substr($cookie,1,strrpos($r," "));
            $cookie = substr($cookie,0,strpos($cookie,";"));
          $cookie= str_replace("X-Powered-By","",$cookie);
            $cookies[] = $cookie;
          }else{
          $cookie = substr($r,1);
          $cookie = substr($cookie,0,strpos($cookie,";"));
      $cookie= str_replace("X-Powered-By","",$cookie);
          $cookies[] = $cookie;
          }
        }
      }
     // $cookies = implode(";",$cookies);
  
      file_put_contents($cookie_file,json_encode($cookies));

      curl_close($ch);
      
      return $body;
    }
    static function downloadFromLisa($oldMembers = false){
       
        $res = Utils::loginToLisa("https://login.lisa-is.nl/lisa2/login.asp?domain=871&hcheck=410afbba17eb35a3ecd056e930a915ab","ictbeheer","Dubbellikke3","cookies.txt");

        if($oldMembers){
        $data = "pane=pane1&frmStartDag=&frmStartMaand=&frmStartJaar=&frmEindDag=&frmEindMaand=&frmEindJaar=&frmGebStartDag=&frmGebStartMaand=&frmGebStartJaar=&frmGebEindDag=&frmGebEindMaand=&frmGebEindJaar=&frmContributiegroep_nvt=1&frmabonnementId=&frmvrijwilligers=nvt&frmoudleden=true&frmproefleden=nvt&frmwachtlijst=nvt&frmopenstaand=nvt&frmbetalingswijze=nvt&frmSpelactiviteiten=&export2Excel=1&frmBestand=1&select=maak selectie&verstuur=&frmEmailOptions=0";
        }else{
           $data = "pane=pane1&frmStartDag=&frmStartMaand=&frmStartJaar=&frmEindDag=&frmEindMaand=&frmEindJaar=&frmGebStartDag=&frmGebStartMaand=&frmGebStartJaar=&frmGebEindDag=&frmGebEindMaand=&frmGebEindJaar=&frmContributiegroep_nvt=1&frmabonnementId=&frmvrijwilligers=false&frmoudleden=nvt&frmproefleden=nvt&frmwachtlijst=nvt&frmopenstaand=nvt&frmbetalingswijze=nvt&frmSpelactiviteiten=&export2Excel=1&frmBestand=1&select=maak selectie&verstuur=&frmEmailOptions=0";
        
        }
        $result = Utils::loginToLisa("https://login.lisa-is.nl/lisa2/default.asp?MenuItem=10",false,false,"cookies.txt",$data);
        unlink("cookies.txt");
       
        return $result;
    }
    static function get_inner_html( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML; 
}
static function setSetting($key, $value){
    $settings = [];
    if (file_exists(dirname(__FILE__)."/../settings.json")){
    $json = file_get_contents(dirname(__FILE__)."/../settings.json");
    $settings = json_decode($json,true);
    }
    $settings[$key] = $value;
    $json = json_encode($settings,true);
    file_put_contents(dirname(__FILE__)."/../settings.json",$json);
}
static function getSetting($key){
    $settings = [];
    if (file_exists(dirname(__FILE__)."/../settings.json")){
    $json = file_get_contents(dirname(__FILE__)."/../settings.json");
    $settings = json_decode($json,true);
    }
    if(isset($settings[$key])){
        return $settings[$key];
    }else return null;
}
}