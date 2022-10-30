<?php
/*Getting user agent info*/
$user_agent = $_SERVER['HTTP_USER_AGENT'];
define('enc_type', 'AES-128-ECB');
define('enc_route', 'com.todo-list-app.webxspark');
header('application/x-httpd-php .pl');
/*OOPS FUNCTIONS*/
class webxspark_admin
{
    private $__db = [
        'users' => 'users',
    ];
    public function get_uri()
    {
        return $uri = $_SERVER['REQUEST_URI'];
    }
    public function getLink()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";
        } else {
            $url = "http://";
        }
        // Append the host(domain name, ip) to the URL.
        $url .= $_SERVER['HTTP_HOST'];

        // Append the requested resource location to the URL
        $url .= $_SERVER['REQUEST_URI'];

        return $url;
    }
    public function getDomainWithSSL()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";
        } else {
            $url = "http://";
        }
        // Append the host(domain name, ip) to the URL.
        $url .= $_SERVER['HTTP_HOST'];

        return $url;
    }
    public function generate_security_fragment()
    {
        return [
            'device' => [
                "OS" => $this::getOS(),
                "client" => [
                    'browser' => $this::getBrowser(),
                    'userAgent' => $this::getUserAgentInfo()
                ]
            ],
            'network' => [
                'ip' => $this::fetch_ip(),
                'event' => [
                    'timestamp' => $this::getCurrentTime()
                ]
            ]
        ];
    }
    public function generate_license($suffix = null)
    {
        // Default tokens contain no "ambiguous" characters: 1,i,0,o
        if (isset($suffix)) {
            // Fewer segments if appending suffix
            $num_segments = 3;
            $segment_chars = 6;
        } else {
            $num_segments = 4;
            $segment_chars = 5;
        }
        $tokens = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $license_string = '';
        // Build Default License String
        for ($i = 0; $i < $num_segments; $i++) {
            $segment = '';
            for ($j = 0; $j < $segment_chars; $j++) {
                $segment .= $tokens[rand(0, strlen($tokens) - 1)];
            }
            $license_string .= $segment;
            if ($i < ($num_segments - 1)) {
                $license_string .= '-';
            }
        }
        // If provided, convert Suffix
        if (isset($suffix)) {
            if (is_numeric($suffix)) {   // Userid provided
                $license_string .= '-' . strtoupper(base_convert($suffix, 10, 36));
            } else {
                $long = sprintf("%u\n", ip2long($suffix), true);
                if ($suffix === long2ip($long)) {
                    $license_string .= '-' . strtoupper(base_convert($long, 10, 36));
                } else {
                    $license_string .= '-' . strtoupper(str_ireplace(' ', '-', $suffix));
                }
            }
        }
        return $license_string;
    }
    public function getDomain()
    {
        $domain = $_SERVER['HTTP_HOST'];
        return $domain;
    }

    public function encrypt_str($string)
    {
        return $string = openssl_encrypt($string, enc_type, enc_route);
    }
    public function decrypt_str($string)
    {
        return $string = openssl_decrypt($string, enc_type, enc_route);
    }
    public function replace($to_replace_word, $replace_word_with, $string)
    {
        return $string = str_replace($to_replace_word, $replace_word_with, $string);
    }
    public function generate_random_strings($length_int)
    {
        $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($str_result), 0, $length_int);
    }
    
    public function getJsonArray($dir)
    {
        $contents = file_get_contents($dir);
        return json_decode($contents, true);
    }
    public function create_file($file, $dir)
    {
        if (!file_exists($dir)) {
            $fp = fopen($dir, 'w');
            fwrite($fp, $file);
            fclose($fp);
            return true;
        } else {
            return 0;
        }
    }
    public static function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            return [
                'status' => 400,
                'message' => 'Requested folder is not a directory or the requested folder not found!'
            ];
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        if (rmdir($dirPath)) {
            return ['status' => 200];
        }
    }
    public static function open_file($dir)
    {
        return file_get_contents($dir);
    }
    public static function spit_words_to_array($string)
    {
        return explode(' ', $string);
    }
    public function update_file($file, $dir)
    {
        if (file_exists($dir)) {
            if (file_put_contents($dir, $file)) {
                return true;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    public function is_json($string, $return_data = false)
    {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : true) : false;
    }
    public static function getOS()
    {

        global $user_agent;

        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 11/i'      => 'Windows 11',
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        );

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }

        return $os_platform;
    }
    public static function getBrowser()
    {

        global $user_agent;

        $browser = "Unknown Browser";

        $browser_array = array(
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser',
        );

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }

        return $browser;
    }
    public static function getUserAgentInfo()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
    public static function getCurrentTime()
    {
        return date('d-m-Y h:i:s A');
    }
    public static function fetch_ip()
    {
        //whether ip is from the share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from the remote address
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public function listDir($path)
    {
        $dir  = new DirectoryIterator($path);
        $json = [];
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $data = $fileinfo->getFilename();
                array_push($json, $data);
            }
        }
        return $json;
    }
    public function checkDir($dirs, $toCheck)
    {
        $notfount = [];
        foreach ($toCheck as $tc) {
            if (!in_array($tc, $dirs, 1)) {
                array_push($notfount, $tc);
            }
        }
        return $notfount;
    }
    public function createDir($arr, $targetdir)
    {
        $errors  = [];
        $success = 0;
        foreach ($arr as $x) {
            if (mkdir($targetdir . DIRECTORY_SEPARATOR . $x)) {
                $success = $success + 1;
            } else {
                array_push($errors, 'Something Went wrong while creating directory ' . $x . ' in path ' . $targetdir . '');
            }
        }
        return ([
            "success" => $success,
            "errors"  => $errors,
        ]);
    }
    public function deviceInfo($device_id)
    {
        return [
            'ip'         => $this->fetch_ip(),
            'OS'         => $this::getOS(),
            'browser'    => $this::getBrowser(),
            'user_agent' => $this::getUserAgentInfo(),
            'device_id'  => $device_id,
            'timestamp'  => $this::getCurrentTime(),
        ];
    }
}

class utilities
{
    public function handle_json($json)
    {
        $json_obj = json_decode($json, 1);
        return $json_obj;
    }
    public function getLink()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";
        } else {
            $url = "http://";
        }
        // Append the host(domain name, ip) to the URL.
        $url .= $_SERVER['HTTP_HOST'];

        // Append the requested resource location to the URL
        $url .= $_SERVER['REQUEST_URI'];

        return $url;
    }
}