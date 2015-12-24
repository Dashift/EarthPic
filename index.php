 <?php 
/**
* Get each piece earth pictures and put them together
* Request URL:http://himawari8-dl.nict.go.jp/himawari8/img/D531106/4d/550/2015/12/15/123000_1_3.png
*/
class getEarthPic 
{
    private $d=0;
    private $latest_url;
    private $imgPath;

    function __construct($d)
    {
        $this->d=$d;
        $this->imgPath=dirname(__FILE__)."/img/";
        $this->latest_url=$this->getLatest();
        $this->run();
    }

    /**
    * Init $latest_url
    */
    public function getLatest()
    {
        $url="http://himawari8-dl.nict.go.jp/himawari8/img/D531106/latest.json";
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json=curl_exec($ch);
        curl_close($ch);
        $latest=json_decode($json,true);
        $date=explode(" ",$latest['date']);
        //$str_1="2015/12/15"
        $str_1=str_replace("-","/",$date[0]);
        //$str_2="123000"
        $str_2=str_replace(":","",$date[1]);
        return "http://himawari8-dl.nict.go.jp/himawari8/img/D531106/".$this->d."d/550/".$str_1."/".$str_2;
    }



    public function sendRequests()
    {
        $d=$this->d;
        for ($x=0; $x < $d; $x++) { 
            for ($y=0; $y < $d; $y++) { 
                $url=$this->latest_url."_".$x."_".$y.".png";
                $filename=$this->imgPath.$x."_".$y.".png";
                $pool[]=new PicSpyder($url,$filename);
            }
        }
        foreach($pool as $worker){
            $worker->start();
        }
        foreach($pool as $worker){
            $worker->join();
        }
    }

    /**
    * Combine pictures
    */
    public function combine()
    {
        $d=$this->d;
        $panel=imagecreatetruecolor(550*$d, 550*$d);
        for ($x=0; $x <$d ; $x++) { 
            for ($y=0; $y <$d ; $y++) { 
                $source=imagecreatefrompng("./img/".$x."_".$y.".png");
                imagecopy($panel,$source, $x*550, $y*550,0,0,550,550);
                imagedestroy($source);
            }
        }
        imagepng($panel,"./img/earth.png");
        imagedestroy($panel);
    }

    public function run()
    {
        $this->sendRequests();
        $this->combine();
    }

}

/**
* Get pics by threads
*/
class PicSpyder extends Thread
{
    private $url;
    private $filename;

    function __construct($url,$filename)
    {
        $this->url=$url;
        $this->filename=$filename;
    }

    public function run()
    {
        $this->curlGetPics($this->url,$this->filename);
    }

    /**
    *Get file and store 
    *@param $url ,$filename
    */
    public function curlGetPics($url,$filename)
    {
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $img=curl_exec($ch);
        curl_close($ch);
        $fp=fopen($filename, 'w');
        fwrite($fp, $img);
        fclose($fp);
    }
}

$pic=new getEarthPic(4);

 ?>