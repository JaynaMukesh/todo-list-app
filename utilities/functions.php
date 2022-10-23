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
    public function get_author_tag()
    {
        $enc_tag = $_COOKIE[TAG];
        return decrypt_str($enc_tag);
    }
    public function set_cookie($cookie_name, $cookie_value)
    {
        setcookie("$cookie_name", $cookie_value, time() + 876000000, '/', false, true); //Cookies validity: 100Years
    }
    public function unset_cookie($cookie_name, $cookie_value)
    {
        setcookie("$cookie_name", $cookie_value, time() - 876000000, '/', false, true); //Cookies validity: 100Years
    }
    public function set_cookie_custom($cookie_name, $cookie_value, $duration)
    {
        setcookie("$cookie_name", $cookie_value, time() + $duration, '/', false, true);
    }
    public function format_date($database_date)
    {
        $date = date_create($database_date);
        return date_format($date, "d F Y ");
    }
    public function time_elapsed_string($datetime, $full = false)
    {
        date_default_timezone_set('Asia/Kolkata');
        $now  = new DateTime;
        $ago  = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
    public function p_des($string, $length)
    {
        return $string = substr($string, 0, $length) . ((strlen($string) > $length) ? "..." : "");
    }
    public function getTag($enc_tag = '')
    {
        if ($enc_tag === '') {
            return $this->decrypt_str($_COOKIE[TAG]);
        } else {
            return $this->decrypt_str($enc_tag);
        }
    }
    public function getSingleOutputIndexDb($conn, $where_condition, $bind_param_obj, $db, $index)
    {
        $sql  = "SELECT * FROM $db WHERE $where_condition=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $bind_param_obj);
        $stmt->execute();
        $result = $stmt->get_result();
        $count  = $result->num_rows;
        if ($count > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row[$index];
        } else {
            return 0;
        }
    }
    public function getObjArrayFromDb($conn, $where_condition, $bind_param_obj, $db)
    {
        $sql  = "SELECT * FROM $db WHERE $where_condition=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $bind_param_obj);
        $stmt->execute();
        $result = $stmt->get_result();
        return mysqli_fetch_array($result);
    }
    public function check_maintenance($conn)
    {
        $stmt = $conn->prepare("SELECT * FROM server_status WHERE id='1'");
        $stmt->execute();
        $result = $stmt->get_result();
        $res    = mysqli_fetch_assoc($result);
        $status = $res['status'];
        if ($status === "0" || $status === 0) {
            return '-';
        } elseif ($status === "1" || $status === 1) {
            return 'maintenance_on';
        }
    }

    public function getObjCountFromDb($conn, $db, $tbl, $object, $custom_andSelectorQuery = '')
    {
        $sql  = "SELECT * FROM $db WHERE $tbl=? $custom_andSelectorQuery LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $object);
        $stmt->execute();
        $tmp_res = $stmt->get_result();
        return $tmp_res->num_rows;
    }
    public function generate_random_strings($length_int)
    {
        $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($str_result), 0, $length_int);
    }
    public function update_single_val_db($conn, $db, $tbl, $ref_tbl, $ref_obj, $newObj)
    {
        $sql  = "UPDATE $db SET $tbl=? WHERE $ref_tbl= '$ref_obj' ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $newObj);
        if ($stmt->execute()) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getSingleOutputDb($conn, $db, $tbl, $ref_obj)
    {
        $sql  = "SELECT * FROM $db WHERE $tbl= '$ref_obj' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $out    = mysqli_fetch_assoc($result);
        return $out;
    }
    public function renderFavicon()
    {
        $favicon_html_str = '
<link rel="apple-touch-icon" href="' . FAVICON_DIR . '/apple-touch-icon.png" />
<link rel="apple-touch-icon" sizes="57x57" href="' . FAVICON_DIR . '/apple-touch-icon-57x57.png" />
<link rel="apple-touch-icon" sizes="72x72" href="' . FAVICON_DIR . '/apple-touch-icon-72x72.png" />
<link rel="apple-touch-icon" sizes="76x76" href="' . FAVICON_DIR . '/apple-touch-icon-76x76.png" />
<link rel="apple-touch-icon" sizes="114x114" href="' . FAVICON_DIR . '/apple-touch-icon-114x114.png" />
<link rel="apple-touch-icon" sizes="120x120" href="' . FAVICON_DIR . '/apple-touch-icon-120x120.png" />
<link rel="apple-touch-icon" sizes="144x144" href="' . FAVICON_DIR . '/apple-touch-icon-144x144.png" />
<link rel="apple-touch-icon" sizes="152x152" href="' . FAVICON_DIR . '/apple-touch-icon-152x152.png" />
<link rel="apple-touch-icon" sizes="180x180" href="' . FAVICON_DIR . '/apple-touch-icon-180x180.png" />
<meta name="msapplication-TileColor" content="' . app_theme_color . '" />
<meta name="theme-color" content="' . app_theme_color . '" />';
        return $favicon_html_str;
    }
    public function check_login_status()
    {
        if (!isset($_COOKIE[TAG]) && !isset($_COOKIE[UNAME]) && !isset($_COOKIE[UEMAIL]) && !isset($_COOKIE[UPASS]) && !isset($_COOKIE[UBIO]) && !isset($_COOKIE[UPROFILE]) && !isset($_COOKIE[UUID]) && !isset($_COOKIE[REFERRED_BY]) && !isset($_COOKIE[UCOUNTRY]) && !isset($_COOKIE[UGENDER]) && !isset($_COOKIE[SPECIAL_PERMS]) && !isset($_COOKIE[CLI_LOG_SRC]) && !isset($_COOKIE[UPHONE])) {
            return 0;
        } elseif (!isset($_SESSION[TAG]) && !isset($_SESSION[UNAME]) && !isset($_SESSION[UEMAIL]) && !isset($_SESSION[UPASS]) && !isset($_SESSION[UBIO]) && !isset($_SESSION[UPROFILE]) && !isset($_SESSION[UUID]) && !isset($_SESSION[REFERRED_BY]) && !isset($_SESSION[UCOUNTRY]) && !isset($_SESSION[UGENDER]) && !isset($_SESSION[SPECIAL_PERMS]) && !isset($_SESSION[CLI_LOG_SRC]) && !isset($_SESSION[UPHONE])) {
            //set session var from cookies
            $_SESSION[TAG]           = $_COOKIE[TAG];
            $_SESSION[UNAME]         = $_COOKIE[UNAME];
            $_SESSION[UEMAIL]        = $_COOKIE[UEMAIL];
            $_SESSION[UPASS]         = $_COOKIE[UPASS];
            $_SESSION[UBIO]          = $_COOKIE[UBIO];
            $_SESSION[UPROFILE]      = $_COOKIE[UPROFILE];
            $_SESSION[UUID]          = $_COOKIE[UUID];
            $_SESSION[REFERRED_BY]   = $_COOKIE[REFERRED_BY];
            $_SESSION[UCOUNTRY]      = $_COOKIE[UCOUNTRY];
            $_SESSION[UGENDER]       = $_COOKIE[UGENDER];
            $_SESSION[SPECIAL_PERMS] = $_COOKIE[SPECIAL_PERMS];
            $_SESSION[CLI_LOG_SRC]   = $_COOKIE[CLI_LOG_SRC];
            $_SESSION[UPHONE]        = $_COOKIE[UPHONE];
            return 1;
        } else {
            return 1;
        }
    }
    public function render_bs_alert($title, $text, $type)
    {
        $svg = '';
        if ($type === "primary") {
            $svg = '<span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path opacity="0.3" d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="black"></path><path d="M20 8L14 2V6C14 7.10457 14.8954 8 16 8H20Z" fill="black"></path><rect x="13.6993" y="13.6656" width="4.42828" height="1.73089" rx="0.865447" transform="rotate(45 13.6993 13.6656)" fill="black"></rect><path d="M15 12C15 14.2 13.2 16 11 16C8.8 16 7 14.2 7 12C7 9.8 8.8 8 11 8C13.2 8 15 9.8 15 12ZM11 9.6C9.68 9.6 8.6 10.68 8.6 12C8.6 13.32 9.68 14.4 11 14.4C12.32 14.4 13.4 13.32 13.4 12C13.4 10.68 12.32 9.6 11 9.6Z" fill="black"></path></svg></span>';
        } elseif ($type === "success") {
            $svg = '<span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="black"></path><path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z" fill="black"></path></svg></span>';
        } elseif ($type === "info") {
            $svg = '<span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path opacity="0.3" d="M12 22C13.6569 22 15 20.6569 15 19C15 17.3431 13.6569 16 12 16C10.3431 16 9 17.3431 9 19C9 20.6569 10.3431 22 12 22Z" fill="black"></path><path d="M19 15V18C19 18.6 18.6 19 18 19H6C5.4 19 5 18.6 5 18V15C6.1 15 7 14.1 7 13V10C7 7.6 8.7 5.6 11 5.1V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V5.1C15.3 5.6 17 7.6 17 10V13C17 14.1 17.9 15 19 15ZM11 10C11 9.4 11.4 9 12 9C12.6 9 13 8.6 13 8C13 7.4 12.6 7 12 7C10.3 7 9 8.3 9 10C9 10.6 9.4 11 10 11C10.6 11 11 10.6 11 10Z" fill="black"></path></svg></span>';
        } elseif ($type === "danger" || $type === "error") {
            $svg = '<span class="svg-icon svg-icon-2hx svg-icon-light me-4 mb-5 mb-sm-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path opacity="0.3" d="M2 4V16C2 16.6 2.4 17 3 17H13L16.6 20.6C17.1 21.1 18 20.8 18 20V17H21C21.6 17 22 16.6 22 16V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4Z" fill="black"></path><path d="M18 9H6C5.4 9 5 8.6 5 8C5 7.4 5.4 7 6 7H18C18.6 7 19 7.4 19 8C19 8.6 18.6 9 18 9ZM16 12C16 11.4 15.6 11 15 11H6C5.4 11 5 11.4 5 12C5 12.6 5.4 13 6 13H15C15.6 13 16 12.6 16 12Z" fill="black"></path></svg></span>';
        }
        return ' <div class="alert alert-dismissible bg-' . $type . ' d-flex flex-column flex-sm-row w-100 p-5 mb-10"> ' . $svg . ' <div class="d-flex flex-column text-light pe-0 pe-sm-10"> <h4 class="mb-2 text-light alert-title">' . $title . '</h4> <span class="alert-desc">' . $text . '</span> </div><button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert"> <span class="svg-icon svg-icon-2x svg-icon-light"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"> <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect> <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect> </svg> </span> </button> </div>';
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
    public static function generate_msg_loader($attr = null, $height = '80px', $width = '80px')
    {
        return '<svg class="lds-message" width="' . $width . '" ' . $attr . '  height="' . $height . '"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><g transform="translate(20 50)"><circle cx="0" cy="0" r="7" fill="#e15b64" transform="scale(0.99275 0.99275)"><animateTransform attributeName="transform" type="scale" begin="-0.375s" calcMode="spline" keySplines="0.3 0 0.7 1;0.3 0 0.7 1" values="0;1;0" keyTimes="0;0.5;1" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="translate(40 50)"><circle cx="0" cy="0" r="7" fill="#f47e60" transform="scale(0.773605 0.773605)"><animateTransform attributeName="transform" type="scale" begin="-0.25s" calcMode="spline" keySplines="0.3 0 0.7 1;0.3 0 0.7 1" values="0;1;0" keyTimes="0;0.5;1" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="translate(60 50)"><circle cx="0" cy="0" r="7" fill="#f8b26a" transform="scale(0.42525 0.42525)"><animateTransform attributeName="transform" type="scale" begin="-0.125s" calcMode="spline" keySplines="0.3 0 0.7 1;0.3 0 0.7 1" values="0;1;0" keyTimes="0;0.5;1" dur="1s" repeatCount="indefinite"></animateTransform></circle></g><g transform="translate(80 50)"><circle cx="0" cy="0" r="7" fill="#abbd81" transform="scale(0.113418 0.113418)"><animateTransform attributeName="transform" type="scale" begin="0s" calcMode="spline" keySplines="0.3 0 0.7 1;0.3 0 0.7 1" values="0;1;0" keyTimes="0;0.5;1" dur="1s" repeatCount="indefinite"></animateTransform></circle></g></svg>';
    }
    public static function generate_chunk_loader($height = '200px', $width = '200px')
    {
        return '<svg xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: transparent; display: block; shape-rendering: auto;" width="' . $width . '" height="' . $height . '" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><g transform="translate(50 50) scale(0.7000000000000001) translate(-50 -50)"><g transform="rotate(359.541 50.0011 50.0011)"><animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" calcMode="spline" dur="4s" values="0 50 50;90 50 50;180 50 50;270 50 50;360 50 50" keyTimes="0;0.25;0.5;0.75;1" keySplines="0 1 0 1;0 1 0 1;0 1 0 1;0 1 0 1"></animateTransform><g transform="scale(0.996425 0.996425)"><animateTransform attributeName="transform" type="scale" dur="1s" repeatCount="indefinite" calcMode="spline" values="1;1;0.5" keyTimes="0;0.5;1" keySplines="1 0 0 1;1 0 0 1"></animateTransform><g transform="translate(25 25)"><rect x="-25" y="-25" width="52" height="52" fill="#e15b64"><animate attributeName="fill" dur="4s" repeatCount="indefinite" calcMode="spline" values="#e15b64;#f47e60;#f8b26a;#abbd81;#e15b64" keyTimes="0;0.25;0.5;0.75;1" keySplines="0 1 0 1;0 1 0 1;0 1 0 1;0 1 0 1"></animate></rect></g><g transform="translate(25 75)"><rect x="-25" y="-25" width="52" height="50" fill="#e15b64" transform="scale(1 1)"><animateTransform attributeName="transform" type="scale" dur="1s" repeatCount="indefinite" calcMode="spline" values="0;1;1" keyTimes="0;0.5;1" keySplines="1 0 0 1;1 0 0 1"></animateTransform><animate attributeName="fill" dur="4s" repeatCount="indefinite" calcMode="spline" values="#e15b64;#f47e60;#f8b26a;#abbd81;#e15b64" keyTimes="0;0.25;0.5;0.75;1" keySplines="0 1 0 1;0 1 0 1;0 1 0 1;0 1 0 1"></animate></rect></g><g transform="translate(75 25)"><rect x="-25" y="-25" width="50" height="52" fill="#e15b64" transform="scale(1 1)"><animateTransform attributeName="transform" type="scale" dur="1s" repeatCount="indefinite" calcMode="spline" values="0;1;1" keyTimes="0;0.5;1" keySplines="1 0 0 1;1 0 0 1"></animateTransform><animate attributeName="fill" dur="4s" repeatCount="indefinite" calcMode="spline" values="#e15b64;#f47e60;#f8b26a;#abbd81;#e15b64" keyTimes="0;0.25;0.5;0.75;1" keySplines="0 1 0 1;0 1 0 1;0 1 0 1;0 1 0 1"></animate></rect></g><g transform="translate(75 75)"><rect x="-25" y="-25" width="50" height="50" fill="#e15b64" transform="scale(1 1)"><animateTransform attributeName="transform" type="scale" dur="1s" repeatCount="indefinite" calcMode="spline" values="0;1;1" keyTimes="0;0.5;1" keySplines="1 0 0 1;1 0 0 1"></animateTransform><animate attributeName="fill" dur="4s" repeatCount="indefinite" calcMode="spline" values="#e15b64;#f47e60;#f8b26a;#abbd81;#e15b64" keyTimes="0;0.25;0.5;0.75;1" keySplines="0 1 0 1;0 1 0 1;0 1 0 1;0 1 0 1"></animate></rect></g></g></g></g></svg>';
    }
    public static function getPageCompDetails($path, $hash)
    {
        $return_var = '';
        if ($path === "admin/index.php" || $path === "admin/" || $path === "admin" || $path === "admin/index" || $path === "admin/index?" || $path === "admin/index/" || $path === "admin/dashboard" || $path === "admin/index.php?" || $path === "admin/#dashboard" || $path === "admin/#" || $path === "/admin/index.php" || $path === "/admin/" || $path === "/admin" || $path === "/admin/index" || $path === "/admin/index?" || $path === "/admin/index/" || $path === "/admin/dashboard" || $path === "/admin/index.php?" || $path === "/admin/#dashboard" || $path === "/admin/#") {
            $return_var = "Dashboard - Webxspark Admin";
        } elseif ($path === '#' || $path === "/#" || $path === "#/") {
            $return_var = "Dashboard - Webxspark Admin";
        } elseif ($path === "admin/content" || $path === "admin/content" || $path === "admin#content" || $path === "admin?content" || $path === "admin_content" || $path === "admin/content/" || $path === "admin/articles" || $path === "admin/content?" || $path === "admin/#content" || $path === "/admin/content" || $path === "/admin/#content" || $path === "/admin?content" || $path === "/admin/content" || $path === "/admin/content?" || $path === "/admin/content/" || $path === "/admin/articles" || $path === "/admin/content.php?" || $path === "/admin/#content") {
            $return_var = "Content";
        } elseif ($path === "admin/analytics" || $path === "admin/analytics" || $path === "admin#analytics" || $path === "admin?analytics" || $path === "admin_analytics" || $path === "admin/analytics/" || $path === "admin/articles" || $path === "admin/analytics?" || $path === "admin/#analytics" || $path === "/admin/analytics" || $path === "/admin/#analytics" || $path === "/admin?analytics" || $path === "/admin/analytics" || $path === "/admin/analytics?" || $path === "/admin/analytics/" || $path === "/admin/analytics" || $path === "/admin/analytics.php?" || $path === "/admin/#analytics") {
            $return_var = "Analytics";
        } elseif ($path === "admin/post/new" || $path === "admin/post/new" || $path === "admin#post/new" || $path === "admin?post/new" || $path === "admin_post/new" || $path === "admin/post/new/" || $path === "admin/articles" || $path === "admin/post/new?" || $path === "admin/#post/new" || $path === "/admin/post/new" || $path === "/admin/#post/new" || $path === "/admin?post/new" || $path === "/admin/post/new" || $path === "/admin/post/new?" || $path === "/admin/post/new/" || $path === "/admin/post/new" || $path === "/admin/post/new.php?" || $path === "/admin/#post/new") {
            $return_var = "New Post";
        } elseif ($path === "admin/discounts-offers" || $path === "admin/discounts-offers" || $path === "admin#discounts-offers" || $path === "admin?discounts-offers" || $path === "admin_discounts-offers" || $path === "admin/discounts-offers/" || $path === "admin/articles" || $path === "admin/discounts-offers?" || $path === "admin/#discounts-offers" || $path === "/admin/discounts-offers" || $path === "/admin/#discounts-offers" || $path === "/admin?discounts-offers" || $path === "/admin/discounts-offers" || $path === "/admin/discounts-offers?" || $path === "/admin/discounts-offers/" || $path === "/admin/discounts-offers" || $path === "/admin/discounts-offers.php?" || $path === "/admin/#discounts-offers") {
            $return_var = "Discounts & Offers";
        } elseif ($path === "admin/notifications" || $path === "admin/notifications" || $path === "admin#notifications" || $path === "admin?notifications" || $path === "admin_notifications" || $path === "admin/notifications/" || $path === "admin/articles" || $path === "admin/notifications?" || $path === "admin/#notifications" || $path === "/admin/notifications" || $path === "/admin/#notifications" || $path === "/admin?notifications" || $path === "/admin/notifications" || $path === "/admin/notifications?" || $path === "/admin/notifications/" || $path === "/admin/notifications" || $path === "/admin/notifications.php?" || $path === "/admin/#notifications") {
            $return_var = "Notifications";
        } elseif ($path === "admin/profile/overview" || $path === "admin/profile/overview" || $path === "admin#profile/overview" || $path === "admin?profile/overview" || $path === "admin_profile/overview" || $path === "admin/profile/overview/" || $path === "admin/articles" || $path === "admin/profile/overview?" || $path === "admin/#profile/overview" || $path === "/admin/profile/overview" || $path === "/admin/#profile/overview" || $path === "/admin?profile/overview" || $path === "/admin/profile/overview" || $path === "/admin/profile/overview?" || $path === "/admin/profile/overview/" || $path === "/admin/profile/overview" || $path === "/admin/profile/overview.php?" || $path === "/admin/#profile/overview") {
            $return_var = "Profile Overview";
        } elseif ($path === "admin/profile/settings" || $path === "admin/profile/settings" || $path === "admin#profile/settings" || $path === "admin?profile/settings" || $path === "admin_profile/settings" || $path === "admin/profile/settings/" || $path === "admin/articles" || $path === "admin/profile/settings?" || $path === "admin/#profile/settings" || $path === "/admin/profile/settings" || $path === "/admin/#profile/settings" || $path === "/admin?profile/settings" || $path === "/admin/profile/settings" || $path === "/admin/profile/settings?" || $path === "/admin/profile/settings/" || $path === "/admin/profile/settings" || $path === "/admin/profile/settings.php?" || $path === "/admin/#profile/settings") {
            $return_var = "Profile Settings";
        } elseif ($path === "admin/profile/security" || $path === "admin/profile/security" || $path === "admin#profile/security" || $path === "admin?profile/security" || $path === "admin_profile/security" || $path === "admin/profile/security/" || $path === "admin/articles" || $path === "admin/profile/security?" || $path === "admin/#profile/security" || $path === "/admin/profile/security" || $path === "/admin/#profile/security" || $path === "/admin?profile/security" || $path === "/admin/profile/security" || $path === "/admin/profile/security?" || $path === "/admin/profile/security/" || $path === "/admin/profile/security" || $path === "/admin/profile/security.php?" || $path === "/admin/#profile/security") {
            $return_var = "Profile Security";
        } elseif ($path === "admin/profile/billing" || $path === "admin/profile/billing" || $path === "admin#profile/billing" || $path === "admin?profile/billing" || $path === "admin_profile/billing" || $path === "admin/profile/billing/" || $path === "admin/articles" || $path === "admin/profile/billing?" || $path === "admin/#profile/billing" || $path === "/admin/profile/billing" || $path === "/admin/#profile/billing" || $path === "/admin?profile/billing" || $path === "/admin/profile/billing" || $path === "/admin/profile/billing?" || $path === "/admin/profile/billing/" || $path === "/admin/profile/billing" || $path === "/admin/profile/billing.php?" || $path === "/admin/#profile/billing") {
            $return_var = "My Billings";
        } elseif ($path === "admin/profile/statements" || $path === "admin/profile/statements" || $path === "admin#profile/statements" || $path === "admin?profile/statements" || $path === "admin_profile/statements" || $path === "admin/profile/statements/" || $path === "admin/articles" || $path === "admin/profile/statements?" || $path === "admin/#profile/statements" || $path === "/admin/profile/statements" || $path === "/admin/#profile/statements" || $path === "/admin?profile/statements" || $path === "/admin/profile/statements" || $path === "/admin/profile/statements?" || $path === "/admin/profile/statements/" || $path === "/admin/profile/statements" || $path === "/admin/profile/statements.php?" || $path === "/admin/#profile/statements") {
            $return_var = "My Statements";
        } elseif ($path === "admin/profile/referrals" || $path === "admin/profile/referrals" || $path === "admin#profile/referrals" || $path === "admin?profile/referrals" || $path === "admin_profile/referrals" || $path === "admin/profile/referrals/" || $path === "admin/articles" || $path === "admin/profile/referrals?" || $path === "admin/#profile/referrals" || $path === "/admin/profile/referrals" || $path === "/admin/#profile/referrals" || $path === "/admin?profile/referrals" || $path === "/admin/profile/referrals" || $path === "/admin/profile/referrals?" || $path === "/admin/profile/referrals/" || $path === "/admin/profile/referrals" || $path === "/admin/profile/referrals.php?" || $path === "/admin/#profile/referrals") {
            $return_var = "My Referrals";
        } elseif ($path === "admin/profile/api" || $path === "admin/profile/api" || $path === "admin#profile/api" || $path === "admin?profile/api" || $path === "admin_profile/api" || $path === "admin/profile/api/" || $path === "admin/articles" || $path === "admin/profile/api?" || $path === "admin/#profile/api" || $path === "/admin/profile/api" || $path === "/admin/#profile/api" || $path === "/admin?profile/api" || $path === "/admin/profile/api" || $path === "/admin/profile/api?" || $path === "/admin/profile/api/" || $path === "/admin/profile/api" || $path === "/admin/profile/api.php?" || $path === "/admin/#profile/api") {
            $return_var = "My API Keys";
        } elseif ($path === "admin/monetization" || $path === "admin/monetization" || $path === "admin#monetization" || $path === "admin?monetization" || $path === "admin_monetization" || $path === "admin/monetization/" || $path === "admin/articles" || $path === "admin/monetization?" || $path === "admin/#monetization" || $path === "/admin/monetization" || $path === "/admin/#monetization" || $path === "/admin?monetization" || $path === "/admin/monetization" || $path === "/admin/monetization?" || $path === "/admin/monetization/" || $path === "/admin/monetization" || $path === "/admin/monetization.php?" || $path === "/admin/#monetization") {
            $return_var = "Monetization";
        } elseif ($path === "admin/support/faq" || $path === "admin/support/faq" || $path === "admin#support/faq" || $path === "admin?support/faq" || $path === "admin_support/faq" || $path === "admin/support/faq/" || $path === "admin/articles" || $path === "admin/support/faq?" || $path === "admin/#support/faq" || $path === "/admin/support/faq" || $path === "/admin/#support/faq" || $path === "/admin?support/faq" || $path === "/admin/support/faq" || $path === "/admin/support/faq?" || $path === "/admin/support/faq/" || $path === "/admin/support/faq" || $path === "/admin/support/faq.php?" || $path === "/admin/#support/faq") {
            $return_var = "FAQ";
        } elseif ($path === "admin/support/tutorials" || $path === "admin/support/tutorials" || $path === "admin#support/tutorials" || $path === "admin?support/tutorials" || $path === "admin_support/tutorials" || $path === "admin/support/tutorials/" || $path === "admin/articles" || $path === "admin/support/tutorials?" || $path === "admin/#support/tutorials" || $path === "/admin/support/tutorials" || $path === "/admin/#support/tutorials" || $path === "/admin?support/tutorials" || $path === "/admin/support/tutorials" || $path === "/admin/support/tutorials?" || $path === "/admin/support/tutorials/" || $path === "/admin/support/tutorials" || $path === "/admin/support/tutorials.php?" || $path === "/admin/#support/tutorials") {
            $return_var = "Tutorials";
        } elseif ($path === "admin/support/contact-us" || $path === "admin/support/contact-us" || $path === "admin#support/contact-us" || $path === "admin?support/contact-us" || $path === "admin_support/contact-us" || $path === "admin/support/contact-us/" || $path === "admin/articles" || $path === "admin/support/contact-us?" || $path === "admin/#support/contact-us" || $path === "/admin/support/contact-us" || $path === "/admin/#support/contact-us" || $path === "/admin?support/contact-us" || $path === "/admin/support/contact-us" || $path === "/admin/support/contact-us?" || $path === "/admin/support/contact-us/" || $path === "/admin/support/contact-us" || $path === "/admin/support/contact-us.php?" || $path === "/admin/#support/contact-us") {
            $return_var = "Contact Us";
        } elseif ($path === "admin/team/manage/customers" || $path === "admin/team/manage/customers" || $path === "admin#team/manage/customers" || $path === "admin?team/manage/customers" || $path === "admin_team/manage/customers" || $path === "admin/team/manage/customers/" || $path === "admin/articles" || $path === "admin/team/manage/customers?" || $path === "admin/#team/manage/customers" || $path === "/admin/team/manage/customers" || $path === "/admin/#team/manage/customers" || $path === "/admin?team/manage/customers" || $path === "/admin/team/manage/customers" || $path === "/admin/team/manage/customers?" || $path === "/admin/team/manage/customers/" || $path === "/admin/team/manage/customers" || $path === "/admin/team/manage/customers.php?" || $path === "/admin/#team/manage/customers") {
            $return_var = "Manage Customers";
        } elseif ($path === "admin/team/manage/users" || $path === "admin/team/manage/users" || $path === "admin#team/manage/users" || $path === "admin?team/manage/users" || $path === "admin_team/manage/users" || $path === "admin/team/manage/users/" || $path === "admin/articles" || $path === "admin/team/manage/users?" || $path === "admin/#team/manage/users" || $path === "/admin/team/manage/users" || $path === "/admin/#team/manage/users" || $path === "/admin?team/manage/users" || $path === "/admin/team/manage/users" || $path === "/admin/team/manage/users?" || $path === "/admin/team/manage/users/" || $path === "/admin/team/manage/users" || $path === "/admin/team/manage/users.php?" || $path === "/admin/#team/manage/users") {
            $return_var = "Manage Users";
        } elseif ($path === "admin/team/manage/content" || $path === "admin/team/manage/content" || $path === "admin#team/manage/content" || $path === "admin?team/manage/content" || $path === "admin_team/manage/content" || $path === "admin/team/manage/content/" || $path === "admin/articles" || $path === "admin/team/manage/content?" || $path === "admin/#team/manage/content" || $path === "/admin/team/manage/content" || $path === "/admin/#team/manage/content" || $path === "/admin?team/manage/content" || $path === "/admin/team/manage/content" || $path === "/admin/team/manage/content?" || $path === "/admin/team/manage/content/" || $path === "/admin/team/manage/content" || $path === "/admin/team/manage/content.php?" || $path === "/admin/#team/manage/content") {
            $return_var = "Manage Contents";
        } elseif ($path === "admin/changelog" || $path === "admin/changelog" || $path === "admin#changelog" || $path === "admin?changelog" || $path === "admin_changelog" || $path === "admin/changelog/" || $path === "admin/articles" || $path === "admin/changelog?" || $path === "admin/#changelog" || $path === "/admin/changelog" || $path === "/admin/#changelog" || $path === "/admin?changelog" || $path === "/admin/changelog" || $path === "/admin/changelog?" || $path === "/admin/changelog/" || $path === "/admin/changelog" || $path === "/admin/changelog.php?" || $path === "/admin/#changelog") {
            $return_var = "Changelog";
        } elseif ($path === "admin/team/manage/monetization" || $path === "admin/team/manage/monetization" || $path === "admin#team/manage/monetization" || $path === "admin?team/manage/monetization" || $path === "admin_team/manage/monetization" || $path === "admin/team/manage/monetization/" || $path === "admin/articles" || $path === "admin/team/manage/monetization?" || $path === "admin/#team/manage/monetization" || $path === "/admin/team/manage/monetization" || $path === "/admin/#team/manage/monetization" || $path === "/admin?team/manage/monetization" || $path === "/admin/team/manage/monetization" || $path === "/admin/team/manage/monetization?" || $path === "/admin/team/manage/monetization/" || $path === "/admin/team/manage/monetization" || $path === "/admin/team/manage/monetization.php?" || $path === "/admin/#team/manage/monetization") {
            $return_var = "Manage Monetization";
        } elseif ($path === "admin/team/manage/reporting" || $path === "admin/team/manage/reporting" || $path === "admin#team/manage/reporting" || $path === "admin?team/manage/reporting" || $path === "admin_team/manage/reporting" || $path === "admin/team/manage/reporting/" || $path === "admin/articles" || $path === "admin/team/manage/reporting?" || $path === "admin/#team/manage/reporting" || $path === "/admin/team/manage/reporting" || $path === "/admin/#team/manage/reporting" || $path === "/admin?team/manage/reporting" || $path === "/admin/team/manage/reporting" || $path === "/admin/team/manage/reporting?" || $path === "/admin/team/manage/reporting/" || $path === "/admin/team/manage/reporting" || $path === "/admin/team/manage/reporting.php?" || $path === "/admin/#team/manage/reporting") {
            $return_var = "Manage Reportings";
        } elseif ($path === "admin/team/manage/subscription" || $path === "admin/team/manage/subscription" || $path === "admin#team/manage/subscription" || $path === "admin?team/manage/subscription" || $path === "admin_team/manage/subscription" || $path === "admin/team/manage/subscription/" || $path === "admin/articles" || $path === "admin/team/manage/subscription?" || $path === "admin/#team/manage/subscription" || $path === "/admin/team/manage/subscription" || $path === "/admin/#team/manage/subscription" || $path === "/admin?team/manage/subscription" || $path === "/admin/team/manage/subscription" || $path === "/admin/team/manage/subscription?" || $path === "/admin/team/manage/subscription/" || $path === "/admin/team/manage/subscription" || $path === "/admin/team/manage/subscription.php?" || $path === "/admin/#team/manage/subscription") {
            $return_var = "Manage Subscriptions";
        } elseif ($path === "admin/team/manage/invoice" || $path === "admin/team/manage/invoice" || $path === "admin#team/manage/invoice" || $path === "admin?team/manage/invoice" || $path === "admin_team/manage/invoice" || $path === "admin/team/manage/invoice/" || $path === "admin/articles" || $path === "admin/team/manage/invoice?" || $path === "admin/#team/manage/invoice" || $path === "/admin/team/manage/invoice" || $path === "/admin/#team/manage/invoice" || $path === "/admin?team/manage/invoice" || $path === "/admin/team/manage/invoice" || $path === "/admin/team/manage/invoice?" || $path === "/admin/team/manage/invoice/" || $path === "/admin/team/manage/invoice" || $path === "/admin/team/manage/invoice.php?" || $path === "/admin/#team/manage/invoice") {
            $return_var = "Manage Invoices";
        } elseif ($path === "admin/team/proof-reader" || $path === "admin/team/proof-reader" || $path === "admin#team/proof-reader" || $path === "admin?team/proof-reader" || $path === "admin_team/proof-reader" || $path === "admin/team/proof-reader/" || $path === "admin/articles" || $path === "admin/team/proof-reader?" || $path === "admin/#team/proof-reader" || $path === "/admin/team/proof-reader" || $path === "/admin/#team/proof-reader" || $path === "/admin?team/proof-reader" || $path === "/admin/team/proof-reader" || $path === "/admin/team/proof-reader?" || $path === "/admin/team/proof-reader/" || $path === "/admin/team/proof-reader" || $path === "/admin/team/proof-reader.php?" || $path === "/admin/#team/proof-reader") {
            $return_var = "Proof Reading";
        } elseif ($path === "admin/team/manage/api" || $path === "admin/team/manage/api" || $path === "admin#team/manage/api" || $path === "admin?team/manage/api" || $path === "admin_team/manage/api" || $path === "admin/team/manage/api/" || $path === "admin/articles" || $path === "admin/team/manage/api?" || $path === "admin/#team/manage/api" || $path === "/admin/team/manage/api" || $path === "/admin/#team/manage/api" || $path === "/admin?team/manage/api" || $path === "/admin/team/manage/api" || $path === "/admin/team/manage/api?" || $path === "/admin/team/manage/api/" || $path === "/admin/team/manage/api" || $path === "/admin/team/manage/api.php?" || $path === "/admin/#team/manage/api") {
            $return_var = "Manage APIs";
        } elseif ($path === "admin/team/manage/support/faq" || $path === "admin/team/manage/support/faq" || $path === "admin#team/manage/support/faq" || $path === "admin?team/manage/support/faq" || $path === "admin_team/manage/support/faq" || $path === "admin/team/manage/support/faq/" || $path === "admin/articles" || $path === "admin/team/manage/support/faq?" || $path === "admin/#team/manage/support/faq" || $path === "/admin/team/manage/support/faq" || $path === "/admin/#team/manage/support/faq" || $path === "/admin?team/manage/support/faq" || $path === "/admin/team/manage/support/faq" || $path === "/admin/team/manage/support/faq?" || $path === "/admin/team/manage/support/faq/" || $path === "/admin/team/manage/support/faq" || $path === "/admin/team/manage/support/faq.php?" || $path === "/admin/#team/manage/support/faq") {
            $return_var = "Manage FAQs";
        } elseif ($path === "admin/team/manage/support/tutorials" || $path === "admin/team/manage/support/tutorials" || $path === "admin#team/manage/support/tutorials" || $path === "admin?team/manage/support/tutorials" || $path === "admin_team/manage/support/tutorials" || $path === "admin/team/manage/support/tutorials/" || $path === "admin/articles" || $path === "admin/team/manage/support/tutorials?" || $path === "admin/#team/manage/support/tutorials" || $path === "/admin/team/manage/support/tutorials" || $path === "/admin/#team/manage/support/tutorials" || $path === "/admin?team/manage/support/tutorials" || $path === "/admin/team/manage/support/tutorials" || $path === "/admin/team/manage/support/tutorials?" || $path === "/admin/team/manage/support/tutorials/" || $path === "/admin/team/manage/support/tutorials" || $path === "/admin/team/manage/support/tutorials.php?" || $path === "/admin/#team/manage/support/tutorials") {
            $return_var = "Manage Tutorials";
        } elseif ($path === "admin/team/manage/support/contact-us" || $path === "admin/team/manage/support/contact-us" || $path === "admin#team/manage/support/contact-us" || $path === "admin?team/manage/support/contact-us" || $path === "admin_team/manage/support/contact-us" || $path === "admin/team/manage/support/contact-us/" || $path === "admin/articles" || $path === "admin/team/manage/support/contact-us?" || $path === "admin/#team/manage/support/contact-us" || $path === "/admin/team/manage/support/contact-us" || $path === "/admin/#team/manage/support/contact-us" || $path === "/admin?team/manage/support/contact-us" || $path === "/admin/team/manage/support/contact-us" || $path === "/admin/team/manage/support/contact-us?" || $path === "/admin/team/manage/support/contact-us/" || $path === "/admin/team/manage/support/contact-us" || $path === "/admin/team/manage/support/contact-us.php?" || $path === "/admin/#team/manage/support/contact-us") {
            $return_var = "Manage Contact Us";
        } elseif ($path === "admin/team/manage/legal/disputes" || $path === "admin/team/manage/legal/disputes" || $path === "admin#team/manage/legal/disputes" || $path === "admin?team/manage/legal/disputes" || $path === "admin_team/manage/legal/disputes" || $path === "admin/team/manage/legal/disputes/" || $path === "admin/articles" || $path === "admin/team/manage/legal/disputes?" || $path === "admin/#team/manage/legal/disputes" || $path === "/admin/team/manage/legal/disputes" || $path === "/admin/#team/manage/legal/disputes" || $path === "/admin?team/manage/legal/disputes" || $path === "/admin/team/manage/legal/disputes" || $path === "/admin/team/manage/legal/disputes?" || $path === "/admin/team/manage/legal/disputes/" || $path === "/admin/team/manage/legal/disputes" || $path === "/admin/team/manage/legal/disputes.php?" || $path === "/admin/#team/manage/legal/disputes") {
            $return_var = "Manage Disputes";
        } elseif ($path === "admin/team/manage/legal/removal-requests" || $path === "admin/team/manage/legal/removal-requests" || $path === "admin#team/manage/legal/removal-requests" || $path === "admin?team/manage/legal/removal-requests" || $path === "admin_team/manage/legal/removal-requests" || $path === "admin/team/manage/legal/removal-requests/" || $path === "admin/articles" || $path === "admin/team/manage/legal/removal-requests?" || $path === "admin/#team/manage/legal/removal-requests" || $path === "/admin/team/manage/legal/removal-requests" || $path === "/admin/#team/manage/legal/removal-requests" || $path === "/admin?team/manage/legal/removal-requests" || $path === "/admin/team/manage/legal/removal-requests" || $path === "/admin/team/manage/legal/removal-requests?" || $path === "/admin/team/manage/legal/removal-requests/" || $path === "/admin/team/manage/legal/removal-requests" || $path === "/admin/team/manage/legal/removal-requests.php?" || $path === "/admin/#team/manage/legal/removal-requests") {
            $return_var = "Manage Removal Requests";
        } elseif ($path === "admin/team/manage/legal/claim-content" || $path === "admin/team/manage/legal/claim-content" || $path === "admin#team/manage/legal/claim-content" || $path === "admin?team/manage/legal/claim-content" || $path === "admin_team/manage/legal/claim-content" || $path === "admin/team/manage/legal/claim-content/" || $path === "admin/articles" || $path === "admin/team/manage/legal/claim-content?" || $path === "admin/#team/manage/legal/claim-content" || $path === "/admin/team/manage/legal/claim-content" || $path === "/admin/#team/manage/legal/claim-content" || $path === "/admin?team/manage/legal/claim-content" || $path === "/admin/team/manage/legal/claim-content" || $path === "/admin/team/manage/legal/claim-content?" || $path === "/admin/team/manage/legal/claim-content/" || $path === "/admin/team/manage/legal/claim-content" || $path === "/admin/team/manage/legal/claim-content.php?" || $path === "/admin/#team/manage/legal/claim-content") {
            $return_var = "Claim Content";
        } elseif ($path === "admin/team/manage/legal/accounts" || $path === "admin/team/manage/legal/accounts" || $path === "admin#team/manage/legal/accounts" || $path === "admin?team/manage/legal/accounts" || $path === "admin_team/manage/legal/accounts" || $path === "admin/team/manage/legal/accounts/" || $path === "admin/articles" || $path === "admin/team/manage/legal/accounts?" || $path === "admin/#team/manage/legal/accounts" || $path === "/admin/team/manage/legal/accounts" || $path === "/admin/#team/manage/legal/accounts" || $path === "/admin?team/manage/legal/accounts" || $path === "/admin/team/manage/legal/accounts" || $path === "/admin/team/manage/legal/accounts?" || $path === "/admin/team/manage/legal/accounts/" || $path === "/admin/team/manage/legal/accounts" || $path === "/admin/team/manage/legal/accounts.php?" || $path === "/admin/#team/manage/legal/accounts") {
            $return_var = "Manage Accounts";
        } elseif ($path === "admin/team/manage/marketing/notifications" || $path === "admin/team/manage/marketing/notifications" || $path === "admin#team/manage/marketing/notifications" || $path === "admin?team/manage/marketing/notifications" || $path === "admin_team/manage/marketing/notifications" || $path === "admin/team/manage/marketing/notifications/" || $path === "admin/articles" || $path === "admin/team/manage/marketing/notifications?" || $path === "admin/#team/manage/marketing/notifications" || $path === "/admin/team/manage/marketing/notifications" || $path === "/admin/#team/manage/marketing/notifications" || $path === "/admin?team/manage/marketing/notifications" || $path === "/admin/team/manage/marketing/notifications" || $path === "/admin/team/manage/marketing/notifications?" || $path === "/admin/team/manage/marketing/notifications/" || $path === "/admin/team/manage/marketing/notifications" || $path === "/admin/team/manage/marketing/notifications.php?" || $path === "/admin/#team/manage/marketing/notifications") {
            $return_var = "Manage Notifications";
        } elseif ($path === "admin/team/manage/marketing/user-notifications" || $path === "admin/team/manage/marketing/user-notifications" || $path === "admin#team/manage/marketing/user-notifications" || $path === "admin?team/manage/marketing/user-notifications" || $path === "admin_team/manage/marketing/user-notifications" || $path === "admin/team/manage/marketing/user-notifications/" || $path === "admin/articles" || $path === "admin/team/manage/marketing/user-notifications?" || $path === "admin/#team/manage/marketing/user-notifications" || $path === "/admin/team/manage/marketing/user-notifications" || $path === "/admin/#team/manage/marketing/user-notifications" || $path === "/admin?team/manage/marketing/user-notifications" || $path === "/admin/team/manage/marketing/user-notifications" || $path === "/admin/team/manage/marketing/user-notifications?" || $path === "/admin/team/manage/marketing/user-notifications/" || $path === "/admin/team/manage/marketing/user-notifications" || $path === "/admin/team/manage/marketing/user-notifications.php?" || $path === "/admin/#team/manage/marketing/user-notifications") {
            $return_var = "Manage User Notifications";
        } elseif ($path === "admin/team/manage/marketing/push-notifications" || $path === "admin/team/manage/marketing/push-notifications" || $path === "admin#team/manage/marketing/push-notifications" || $path === "admin?team/manage/marketing/push-notifications" || $path === "admin_team/manage/marketing/push-notifications" || $path === "admin/team/manage/marketing/push-notifications/" || $path === "admin/articles" || $path === "admin/team/manage/marketing/push-notifications?" || $path === "admin/#team/manage/marketing/push-notifications" || $path === "/admin/team/manage/marketing/push-notifications" || $path === "/admin/#team/manage/marketing/push-notifications" || $path === "/admin?team/manage/marketing/push-notifications" || $path === "/admin/team/manage/marketing/push-notifications" || $path === "/admin/team/manage/marketing/push-notifications?" || $path === "/admin/team/manage/marketing/push-notifications/" || $path === "/admin/team/manage/marketing/push-notifications" || $path === "/admin/team/manage/marketing/push-notifications.php?" || $path === "/admin/#team/manage/marketing/push-notifications") {
            $return_var = "Send Push Notifications";
        } elseif ($path === "admin/profile/purchases" || $path === "admin/profile/purchases" || $path === "admin#profile/purchases" || $path === "admin?profile/purchases" || $path === "admin_profile/purchases" || $path === "admin/profile/purchases/" || $path === "admin/articles" || $path === "admin/profile/purchases?" || $path === "admin/#profile/purchases" || $path === "/admin/profile/purchases" || $path === "/admin/#profile/purchases" || $path === "/admin?profile/purchases" || $path === "/admin/profile/purchases" || $path === "/admin/profile/purchases?" || $path === "/admin/profile/purchases/" || $path === "/admin/profile/purchases" || $path === "/admin/profile/purchases.php?" || $path === "/admin/#profile/purchases") {
            $return_var = "My Purchases";
        } elseif ($path === "admin/profile/logs" || $path === "admin/profile/logs" || $path === "admin#profile/logs" || $path === "admin?profile/logs" || $path === "admin_profile/logs" || $path === "admin/profile/logs/" || $path === "admin/articles" || $path === "admin/profile/logs?" || $path === "admin/#profile/logs" || $path === "/admin/profile/logs" || $path === "/admin/#profile/logs" || $path === "/admin?profile/logs" || $path === "/admin/profile/logs" || $path === "/admin/profile/logs?" || $path === "/admin/profile/logs/" || $path === "/admin/profile/logs" || $path === "/admin/profile/logs.php?" || $path === "/admin/#profile/logs") {
            $return_var = "User Logs";
        } elseif ($path === "admin/profile/" || $path === "admin/profile" || $path === "/admin/profile" || $path === "admin?profile" || $path === "admin/profile/#" || $path === "admin/profile#" || $path === "/admin/profile/#") {
            $return_var = "Loading_profile";
        } elseif ($path === "/admin/profile/headers") {
            $return_var = "admin_auth_user_profile_info_headers";
        }
        if ($hash === "#inPage_load") {
            $return_var = $return_var . ' ' . $hash;
        }
        return $return_var;
    }
    public function profile_completion_stats($conn)
    {
        $user_array = $this->getObjArrayFromDb($conn, 'tag', $this->decrypt_str($_COOKIE[TAG]), 'users');
        $percentage = 0;
        if ($user_array['username'] != '') {
            $percentage += 10;
        }
        if ($user_array['email'] != '') {
            $percentage += 10;
        }
        if ($user_array['bio'] != '') {
            $percentage += 20;
        }
        if ($user_array['country'] != '') {
            $percentage += 20;
        }
        if ($user_array['gender'] != '') {
            $percentage += 18;
        }
        if ($user_array['phone_number'] != '') {
            $percentage += 22;
        }
        return $percentage . '%';
    }
    public function getUserSubscribers()
    {
        return 1000;
    }
    public function getUserEarnings()
    {
        $json = [
            "currency" => "USD",
            "value"    => "5302",
        ];
        return json_encode($json, JSON_PRETTY_PRINT);
    }
    public function getUserReferralSignups($conn)
    {
        return $this->getSingleOutputIndexDb($conn, "tag", "{$this->decrypt_str($_COOKIE[TAG])}", 'users', 'referrals');
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
    public function logEventData()
    {
        return [
            'ip'         => $this->fetch_ip(),
            'OS'         => $this::getOS(),
            'browser'    => $this::getBrowser(),
            'user_agent' => $this::getUserAgentInfo(),
            'timestamp'  => $this::getCurrentTime(),
        ];
    }
    public function logoutDevices($tag, $exception = '', $targetDeviceId = '')
    {
        $constants = new constants;
        $logins    = $constants::global_database . $tag . '/logins/index.json';
        $usr_json  = $this->getJsonArray($logins);
        if ($exception === '') {
            //logging out only targeted devices with specific device id
            if (isset($usr_json['login_logs'][$targetDeviceId])) {
                unset($usr_json['login_logs'][$targetDeviceId]);
            }
            if (isset($usr_json['devices'][$targetDeviceId])) {
                unset($usr_json['devices'][$targetDeviceId]);
            }
        } elseif ($exception !== '') {
            //logging out all devices except specific device
            foreach ($usr_json['devices'] as $k => $v) {
                if ($k !== $exception) {
                    unset($usr_json['devices'][$k]);
                }
            }
            foreach ($usr_json['login_logs'] as $k => $v) {
                if ($k !== $exception) {
                    unset($usr_json['login_logs'][$k]);
                }
            }
        }
        $final_json = json_encode($usr_json, JSON_PRETTY_PRINT);
        if ($this->update_file($final_json, $logins)) {
            return true;
        } else {
            return false;
        }
    }
    public function updateSession($conn)
    {
        if ($this->check_login_status()) {
            //updating necessary cookies with real data in database
            $tag = $this->decrypt_str($_COOKIE[TAG]);
            $stmt = $conn->prepare('SELECT * FROM users WHERE tag=? LIMIT 1');
            $stmt->bind_param('s', $tag);
            $stmt->execute();
            $_row = mysqli_fetch_assoc($stmt->get_result());
            $profile_img = $this->encrypt_str($_row['profile_img']);
            $usr_name = $this->encrypt_str($_row['username']);
            $email = $this->encrypt_str($_row['email']);
            $bio = $this->encrypt_str($_row['bio']);
            $verified = $this->encrypt_str($_row['verified']);
            $permission = $this->encrypt_str($_row['permission']);
            $phone = $this->encrypt_str($_row['phone_number']);
            $country = $this->encrypt_str($_row['country']);
            $gender = $this->encrypt_str($_row['gender']);
            $this->set_cookie(UPROFILE, $profile_img);
            $this->set_cookie(UNAME, $usr_name);
            $this->set_cookie(UEMAIL, $email);
            $this->set_cookie(UBIO, $bio);
            $this->set_cookie(VERIFIED_STATUS, $verified);
            $this->set_cookie(UPHONE, $phone);
            $this->set_cookie(UCOUNTRY, $country);
            $this->set_cookie(UGENDER, $gender);
            $_SESSION[UPROFILE] = $profile_img;
            $_SESSION[UNAME] = $usr_name;
            $_SESSION[UEMAIL] = $email;
            $_SESSION[UBIO] = $bio;
            $_SESSION[VERIFIED_STATUS] = $verified;
            $_SESSION[UPHONE] = $phone;
            $_SESSION[UCOUNTRY] = $country;
            $_SESSION[UGENDER] = $gender;
        }
    }
    public function logout()
    {
        if ($this->check_login_status()) {
            /*removing device id config from user's database*/
            $constants = new constants;
            $logins    = $constants::global_database . $this->decrypt_str($_COOKIE[TAG]) . '/logins/index.json';
            $usr_json  = $this->getJsonArray($logins);
            $device_id = $this->decrypt_str($_COOKIE[DEVICE_ID]);
            //removing details from logs only if exists
            if (isset($usr_json['login_logs'][$device_id])) {
                unset($usr_json['login_logs'][$device_id]);
            }
            //removing details from devices object
            if (isset($usr_json['devices'][$device_id])) {
                unset($usr_json['devices'][$device_id]);
            }
            $final_json = json_encode($usr_json, JSON_PRETTY_PRINT);
            if ($this->update_file($final_json, $logins)) {
                //destroying user's session
                session_destroy();
                //removing all cookies...
                $c = REQUIRED_COOKIES;
                foreach ($c as $key => $value) {
                    if (isset($_COOKIE[$value])) {
                        $this->unset_cookie($value, null);
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    public static function HTTPStatus($num)
    {
        $http = array(
            100 => 'HTTP/1.1 100 Continue',
            101 => 'HTTP/1.1 101 Switching Protocols',
            200 => 'HTTP/1.1 200 OK',
            201 => 'HTTP/1.1 201 Created',
            202 => 'HTTP/1.1 202 Accepted',
            203 => 'HTTP/1.1 203 Non-Authoritative Information',
            204 => 'HTTP/1.1 204 No Content',
            205 => 'HTTP/1.1 205 Reset Content',
            206 => 'HTTP/1.1 206 Partial Content',
            300 => 'HTTP/1.1 300 Multiple Choices',
            301 => 'HTTP/1.1 301 Moved Permanently',
            302 => 'HTTP/1.1 302 Found',
            303 => 'HTTP/1.1 303 See Other',
            304 => 'HTTP/1.1 304 Not Modified',
            305 => 'HTTP/1.1 305 Use Proxy',
            307 => 'HTTP/1.1 307 Temporary Redirect',
            400 => 'HTTP/1.1 400 Bad Request',
            401 => 'HTTP/1.1 401 Unauthorized',
            402 => 'HTTP/1.1 402 Payment Required',
            403 => 'HTTP/1.1 403 Forbidden',
            404 => 'HTTP/1.1 404 Not Found',
            405 => 'HTTP/1.1 405 Method Not Allowed',
            406 => 'HTTP/1.1 406 Not Acceptable',
            407 => 'HTTP/1.1 407 Proxy Authentication Required',
            408 => 'HTTP/1.1 408 Request Time-out',
            409 => 'HTTP/1.1 409 Conflict',
            410 => 'HTTP/1.1 410 Gone',
            411 => 'HTTP/1.1 411 Length Required',
            412 => 'HTTP/1.1 412 Precondition Failed',
            413 => 'HTTP/1.1 413 Request Entity Too Large',
            414 => 'HTTP/1.1 414 Request-URI Too Large',
            415 => 'HTTP/1.1 415 Unsupported Media Type',
            416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
            417 => 'HTTP/1.1 417 Expectation Failed',
            500 => 'HTTP/1.1 500 Internal Server Error',
            501 => 'HTTP/1.1 501 Not Implemented',
            502 => 'HTTP/1.1 502 Bad Gateway',
            503 => 'HTTP/1.1 503 Service Unavailable',
            504 => 'HTTP/1.1 504 Gateway Time-out',
            505 => 'HTTP/1.1 505 HTTP Version Not Supported',
        );

        header($http[$num]);

        return
            array(
                'code'  => $num,
                'error' => $http[$num],
            );
    }
    public function serviceWorker_pushNotify()
    {
    }
    public function error_reporting($issue, $dir, $tag)
    {
    }
}
class wxpUsrDb extends SQLite3
{
    public function __construct($tag)
    {
        $global_dir            = '../../users/databases/';
        $global_file_extension = '.wxpdb';
        $global_db_info        = '.sqlite3';
        $this->open($global_dir . $tag . $global_file_extension);
    }
}
class utilities
{
    private function __replace($to_replace_word, $replace_word_with, $string)
    {
        return $string = str_replace($to_replace_word, $replace_word_with, $string);
    }
    private function __replace_extension($filename, $new_extension)
    {
        $info = pathinfo($filename);
        return ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '')
            . $info['filename']
            . '.'
            . $new_extension;
    }
    public function download_file($url, $filename, $parent_dir, $extension)
    {
        $extension  = $this->__replace('.', '', $extension);
        $newFileDir = $parent_dir . $this->__replace_extension($filename, $extension);
        if (file_put_contents($newFileDir, file_get_contents($url))) {
            return '{"status": 200}';
        } else {
            return '{"status": 0}';
        }
    }
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
    public function generate_dataTemplate($referral_code = '')
    {
        $webxspark_admin = new webxspark_admin;
        $usr_config_id   = $webxspark_admin->generate_random_strings(20);
        if ($referral_code === '') {
            $referral_code = $webxspark_admin->generate_random_strings(20);
        }
        $unique_id        = $referral_code;
        $array            = [];
        $_processes_index = [
            "config"          => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "ongoing_process" => [],
            "logs"            => [],
        ];
        $_temp_index = [
            "config"       => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "file_indexes" => [],
        ];
        $_wxp_cache_index = [
            "config"       => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "file_indexes" => [],
            "requests"     => [
                "device" => [],
                "ip"     => [],
            ],
            "process_id"   => [],
        ];
        $apps_index = [
            "config"          => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "products"        => [],
            "product_keys"    => [],
            "logs"            => [],
            "api_credentials" => [],
        ];
        $articles_index = [
            "config"             => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "published_articles" => 0,
            "articles"           => [],
            "logs"               => [],
        ];
        $billings_index = [
            "config"    => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"     => 0,
            "purchases" => [],
            "logs"      => [],
        ];
        $books_index = [
            "config"          => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "published_books" => 0,
            "books"           => [],
            "logs"            => [],
        ];
        $cards_index = [
            "config"      => [
                "config_id"  => $usr_config_id,
                "unique_id"  => $unique_id,
                "extends"    => [],
                "encryption" => true,
                "is_locked"  => false,
            ],
            "total"       => 0,
            "credentials" => [],
            "logs"        => [],
        ];
        $cart_index = [
            "config"   => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"    => 0,
            "products" => [],
        ];
        $documents_index = [
            "config"       => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"        => 0,
            "file_indexes" => [],
            "logs"         => [],
        ];
        $history_index = [
            "config"  => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "history" => [],
            "logs"    => [],
        ];
        $images_index = [
            "config"       => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"        => 0,
            "file_indexes" => [],
            "logs"         => [],
        ];
        $logins_index = [
            "config"     => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "devices"    => [],
            "login_logs" => [],
        ];
        $mail_index = [
            "config" => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "error"  => "Page under construction",
        ];
        $notifications_index = [
            "config"        => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"         => 0,
            "notifications" => [],
        ];
        $payments_index = [
            "config"        => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"         => 0,
            "payments_done" => [],
            "logs"          => [],
        ];
        $subscription_index = [
            "config"        => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "extends"   => [],
                "is_locked" => false,
            ],
            "total"         => 0,
            "subscriptions" => [],
            "notifications" => [],
            "logs"          => [],
        ];
        $special_features_index = [
            "config"   => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "features" => [],
            "logs"     => [],
        ];
        $wallet_index = [
            "config"   => [
                "config_id" => $usr_config_id,
                "unique_id" => $unique_id,
                "is_locked" => false,
            ],
            "user" => [
                '__secure' => [
                    '_wxp-wallet' => [
                        'balance' => 0,
                        'total_payments' => 0
                    ]
                ]
            ],
            "logs"     => [],
        ];
        $array = [
            ".processes"       => $_processes_index,
            ".temp"            => $_temp_index,
            ".wxp_cache"       => $_wxp_cache_index,
            "apps"             => $apps_index,
            "articles"         => $articles_index,
            "billings"         => $billings_index,
            "books"            => $books_index,
            "cards"            => $cards_index,
            "cart"             => $cart_index,
            "documents"        => $documents_index,
            "history"          => $history_index,
            "images"           => $images_index,
            "logins"           => $logins_index,
            "mail"             => $mail_index,
            "notifications"    => $notifications_index,
            "payments"         => $payments_index,
            "special_features" => $special_features_index,
            "subscription"     => $subscription_index,
            "wallet" => $wallet_index
        ];
        return $array;
    }
}
class constants
{
    //constsants
    const create_account_sql = "INSERT INTO users(username, email, password, bio, tag, profile_img, verified, token, permission, login_fail_attempts, login_success, referrals,referral_code, referred_by, special_perms, account_creation_type) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    const newUserDb_sql      = "CREATE TABLE IF NOT EXISTS  subscriptions(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tag    VARCHAR(100)    NOT NULL,
        subscribed_on   VARCHAR(500)    NOT NULL
        );
        CREATE TABLE IF NOT EXISTS  notifications(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title    VARCHAR(100)    NOT NULL,
        details   VARCHAR(500)    NOT NULL,
        tag   VARCHAR(100)    NOT NULL,
        added_on   VARCHAR(300)    NOT NULL
        );
        CREATE TABLE IF NOT EXISTS  cart(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id    VARCHAR(500)    NOT NULL,
        added_on   VARCHAR(300)    NOT NULL
        );
        CREATE TABLE IF NOT EXISTS  history(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id    VARCHAR(500)    NOT NULL,
        read_on   VARCHAR(300)    NOT NULL
        );";
    const global_database       = '../../wxp_db/';
    const global_database_admin = '../wxp_db/';
}
