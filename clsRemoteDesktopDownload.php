<?php

/*
-----------------------------------------------------------------------------------------------------------
Class: RemoteDesktopDownload
Version: 1.0
Release Date: 10/04/2014 
-----------------------------------------------------------------------------------------------------------
Overview: Class used to download images taken on the current date when using the Remote Desktop client for
          Android (https://play.google.com/store/apps/details?id=pl.androiddev.mobiletab).      
-----------------------------------------------------------------------------------------------------------
History:
10/04/2014      1.0	MJC	Created
-----------------------------------------------------------------------------------------------------------
Uses:

*/


set_time_limit(0);

class RemoteDesktopDownload
{
        var $url = "http://192.168.1.65:8080";
        var $password = "";
        var $storageLocation = "";
        var $arrImageLocation = array();
        
        
        /*----------------------------------------------------------------------------------
      	Function:	login
      	Overview:	Method used to authenticate the user
      			
      	In:	
                                                                                                       
      	Out:	bool        True
	----------------------------------------------------------------------------------*/ 
        public function login()
        {
                $ch = curl_init($this->url."/api/login?password=".md5($this->password)."");
                $result = curl_exec($ch); 
                
                return true; 
        }
        
        
        /*----------------------------------------------------------------------------------
      	Function:	login
      	Overview:	Function used to get a JSON list of todays images and store them
                        in an array 
      			
      	In:	
                                                                                                       
      	Out:	bool        True
	----------------------------------------------------------------------------------*/ 
        public function mainPage()
        {               
                $mainPage = file_get_contents($this->url."/index.html?nocache=".time());
                
                $jsonTodayImages = json_decode(file_get_contents($this->url."/api/photos?cmd=page&whichPage=1&when=today&itemsPerPage=20"));
                
                $arrImages = $jsonTodayImages->{'photos'};
                foreach($arrImages as $indImages)
                {
                        $imageLocation = $indImages->{'id'};
                        
                        array_push($this->arrImageLocation,$imageLocation); 
                }
                
                return true;
        }
          
        
        /*----------------------------------------------------------------------------------
      	Function:	saveImages
      	Overview:	Method that itterates through the array, saving each image in the 
                        specified directory.
      			
      	In:	
                                                                                                       
      	Out:	bool        True
	----------------------------------------------------------------------------------*/ 
        public function saveImages()
        {
                foreach($this->arrImageLocation as $indImage)
                {
                        $imgUrl = $this->url."/api/photos?cmd=get_image&id=".urlencode($indImage);
                        
                        $arrFindName = explode('%2',$imgUrl);
                        $strName = $arrFindName[count($arrFindName)-1];
                        
                        $ch = curl_init($imgUrl);
                        $fp = fopen($this->storageLocation.$strName, 'w');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);                       

                }
                
                return true;
        }
}

$objDownloader = new RemoteDesktopDownload();
$objDownloader->login();
$objDownloader->mainPage();
$objDownloader->saveImages();
?>