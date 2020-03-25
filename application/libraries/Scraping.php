 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( dirname(__FILE__) . '/simple_html_dom.php') ;

class Scraping {

    private $tmp_path;

public function __construct($tmp = false) {

        if($tmp){
            $this->tmp_path =  $tmp . '/';
        } else {
            $this->tmp_path =  dirname(dirname(dirname(__FILE__))) . '/tmp/';
            $this->tmp_dir_check();
        }
            $this->clearTmp();
    }

    private function tmp_dir_check()
    {
        if(!is_dir($this->tmp_path)){
            mkdir($this->tmp_path);
        }
    }

    private function clearTmp()
    {
        foreach( glob($this->tmp_path . '*.txt') as $file)
        {
            unlink($file);
        }
    }

  public function page($url)
  {
      return file_get_html($url);
  }

  private function scrapeIt($url, $redirect = 0, $cookieFile = '')
    {
        if ($cookieFile == '') {
            $cookieFile = $this->tmp_path . time() . '_tmp.txt';
        }
        $ch = curl_init();
        $headers = array("Expect:");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($redirect) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6');
        $return = curl_exec($ch);
        curl_close($ch);

        return $return;
    }

    public function shDom($url)
    {
        $page = $this->scrapeIt($url);
        $shdom = str_get_html($page);
        unset($page);

        return $shdom;
    }
}
?>