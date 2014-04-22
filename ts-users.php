<?
//include the ts framework downloadable here http://addons.teamspeak.com/directory/addon/integration/TeamSpeak-3-PHP-Framework.html
include("tsframework/TeamSpeak3.php");

// connect to local server, authenticate and spawn an object for the virtual server on port 30384
$ts3_VirtualServer = TeamSpeak3::factory("serverquery://serveradmin:cdRXFBQ4@127.0.0.1:10011/?server_port=9987");
// query clientlist from virtual server
$arr_ClientList = $ts3_VirtualServer->clientList();

$nicknamelist = "";
$useridlist = "";


//create the string of nicnames as a comma seperated list
foreach($arr_ClientList as $ts3_Client)
{
    $nickname = $ts3_Client;
    //if its the server admin client remove it
    if (strpos($nickname,'serveradmin ') !== false){
        
    }
    else{
        
        $nickname = preg_replace("/\([^)]+\)/","",$nickname);
        $nickname = preg_replace('/\[[^\]]+\]/', '', $nickname);
        $nickname = trim($nickname);
        $nicknamelist = $nicknamelist.$nickname.",";
    }
}

//make the string url safe
$nicknamelisturl = urlencode($nicknamelist);

//build the url for the xml file
$url = "https://api.eveonline.com/eve/CharacterID.xml.aspx?names=".$nicknamelisturl;

//  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$page = curl_exec($ch);

//Load xmlfile into dom document
$doc = new DOMDocument();
$doc->loadXML($page);
$xpath = new DOMXpath($doc);


//create a list of all the charecters 
$chars  = $xpath->query("/eveapi/result/rowset[@name='characters']/row/@characterID");
if (!is_null($chars)) {
  foreach ($chars as $element) {
    $nodes = $element->childNodes;
    foreach ($nodes as $node) {
        $value = $node->nodeValue;
        $useridlist = $useridlist.$value.",";
    }
  }
}

//make the list an array
$nicknamelist = explode(",",$nicknamelist);
$useridlist = explode(",",$useridlist);
$html = "";

//interate through the array returning each charecter and their portrait
$i = 0;
foreach ($nicknamelist as $nickname ) {
    if ($useridlist[$i] > 0)
    {
        $html = $html."<div class='ts-user'>";      
            $html = $html."<img src='https://image.eveonline.com/Character/".$useridlist[$i]."_32.jpg'>";               
            $html = $html.$nickname;    
        $html = $html."</div>";
    }
    $i++;   
}
return $html;
?>
