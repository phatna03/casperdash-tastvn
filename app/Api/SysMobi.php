<?php

namespace App\Api;

/*
*  Mobile Detection RegEx (2/3) Supplied By http://detectmobilebrowsers.com/
*  Regex updated: 22 August 2011
*
*  Before Using You Must Include The Class Script In Your Page:
*  require_once('class.DetectMobileDevice.php');
*
  Usage:

  Static Approach
    DetectMobileDevice::Redirect(string $redirectPath [, bool $includeRequestURI=false]);
        Returns: Nothing, redirects request on detection of mobile device.
    DetectMobileDevice::Mobile();
        Returns: (bool) True/False. True ~ if mobile device detected.

  Object Approach
  - Detect if mobile
    $obj = new DetectMobileDevice(string $redirectPath [, bool $includeRequestURI=false]);
    if($obj->isMobile()){
      // User is on Mobile Device - Do Something
    }else{
      // User is NOT on Mobile Device - Do Something Else or Nothing
    }

  - Detect and Redirect
    $obj = new DetectMobileDevice(string $redirectPath [, bool $includeRequestURI=false]);
    $obj->fullRedirect(true); // Include Request URI
    $obj->detectAndRedirect();

  Example #1: Redirect Mobile Users to 'mobile' directory, without the Requested URI
    DetectMobileDevice::Redirect('mobile');
    -=OR=-
    $obj = new DetectMobileDevice('mobile');
    $obj->detectAndRedirect();

    The user requested http://yoursite.com/Some/Page/ and was redirected to http://yoursite.com/mobile/

  Example #2: Redirect User To Subdomain, with requested URI
    DetectMobileDevice::Redirect('http://mobile.yoursite.com', true);
    -=OR=-
    $obj = new DetectMobileDevice('http://mobile.yoursite.com', true);
    $obj->detectAndRedirect();

    The user requested http://yoursite.com/Some/Page/ and was redirected to http://mobile.yoursite.com/Some/Page

*  ** NOTE **
*  After the first call to DetectMobileDevice::Mobile(), wether or not the UA is a mobile device's,
*  is stored in a static variable, so multiple calls to DetectMobileDevice::Mobile() will not cause
*  multiple checks. The original check will be returned. (System Resources will not be taxed with
*  multiple RegEx checks)
*/

class SysMobi
{
    protected $_useragent;
    protected $_redirect;
    protected $_full_redirect;

    public static $dmd;

    /**  Magic Methods  **/
    public function __construct($redirect = false, $fullRedirect = false)
    {
        $this->_redirect = $redirect;
        $this->_useragent = isset($_SERVER['HTTP_USER_AGENT'])
            ? strtolower($_SERVER['HTTP_USER_AGENT'])
            : '';
        $this->_full_redirect = $fullRedirect;
    }

    /**  Protected Methods  **/
    protected function _is_mobile($useragent)
    {
        if (strpos($useragent, 'mobile') !== false || strpos($useragent, 'android') !== false) {
            return true;
        } else if (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/', $useragent)) {
            return true;
        } else if (preg_match('/(bolt\/[0-9]{1}\.[0-9]{3})|nexian(\s|\-)?nx|(e|k)touch|micromax|obigo|kddi\-|;foma;|netfront/', $useragent)) {
            return true;
        } else if (preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/', substr($useragent, 0, 4))) {
            return true;
        }
        return false;
    }

    protected function _additional()
    {
        if ($this->_full_redirect) {
            $uri = trim($_SERVER['REQUEST_URI'], '/');
            return (isset($uri[0])) ? ((substr($this->_redirect, -1, 1) === '/') ? '' : '/') . $uri : '';
        } else {
            return NULL;
        }
    }

    /**  Public Methods  **/
    public function detectAndRedirect()
    {
        if ($this->_is_mobile($this->_useragent) && strlen(trim($this->_redirect, '/')) > 0) {
            if (headers_sent()) {
                echo '<meta http-equiv="refresh" content="0; url=' . $this->_redirect . $this->_additional() . '">';
            } else {
                header('Location: ' . $this->_redirect . $this->_additional());
            }
            exit;
        }
    }

    public function isMobile()
    {
        return $this->_is_mobile($this->_useragent);
    }

    public function testAgent($ua)
    {
        return $this->_is_mobile(strtolower($ua));
    }

    public function fullRedirect($b = true)
    {
        $this->_full_redirect = $b;
    }

    public function getAgent()
    {
        return $this->_useragent;
    }

    /**  Public Static Methods  **/
    public static function Redirect($redirect, $fullRedirect = false)
    {
        $o = new self($redirect, $fullRedirect);
        $o->detectAndRedirect();
    }

    public static function Mobile()
    {
        if (is_null(self::$dmd)) {
            $o = new self('');
            self::$dmd = $o->isMobile();
            unset($o);
            return self::$dmd;
        } else {
            return self::$dmd;
        }
    }
}
