<?php

class Whois
{

    private $domain;
    private $zone;
    private $subDomain;
    private $servers;
    private $whois_server;

    /**
     * @param string $domain full domain name (without trailing dot)
     */
    public function construct($domain)
    {

        $this->domain = strtolower($domain);
        $this->servers = json_decode(file_get_contents(__DIR__ . '/whois.servers.json'), true);

        // check $domain syntax and split full domain name on subdomains and zone
        $this->explodeDomain();
    }

    protected function explodeDomain()
    {
        if (preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,10}$/ui', $this->domain)) {
            $domainParts = explode('.', $this->domain);

            $subDomains = array();
            for ($x = 1; $x < 10; $x++) {
                $subDomains[] = array_shift($domainParts);
                $this->zone = implode('.', $domainParts);
                foreach ($this->servers as $key => $value) {
                    if ($key == $this->zone) {
                        $this->subDomain = implode('.', $subDomains);
                        return true;
                    }
                }
            }
        }
        http_response_code(404);
        echo 'Domain is not valid or domain zone not found';
        die;
    }

    public function getInfo($domain)
    {
        $this->construct($domain);

        $whois = $this->getWhois();


        $string_encoding = mb_detect_encoding($whois, "UTF-8, ISO-8859-1, ISO-8859-15", true);
        $string_utf8 = mb_convert_encoding($whois, "UTF-8", $string_encoding);

        return htmlspecialchars($string_utf8, ENT_COMPAT, "UTF-8", true);
    }

    protected function getWhois()
    {
        $this->whois_server = $this->servers[$this->zone][0];
        for ($try = 0; $try < 5; $try++) {
            if (preg_match("/^https?:\/\//i", $this->whois_server)) {
                $whois = $this->getWhoisWithCurl();
            } else {
                $whois = $this->getWhoisWithSocket();
            }

            if (!empty($whois)) {
                return $whois;
            }
        }
        echo '(empty answer)';
        die;
    }

    protected function getWhoisWithCurl()
    {
        // curl session to get whois reposnse
        $ch = curl_init();
        $url = $this->whois_server . $this->subDomain . '.' . $this->zone;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $data = curl_exec($ch);

        if (curl_error($ch)) {
            return "Connection error!";
        } else {
            $string = strip_tags($data);
        }
        curl_close($ch);

        return $string;
    }

    protected function getWhoisWithSocket()
    {
        $fp = fsockopen($this->whois_server, 43);
        if (!$fp) {
            return "Connection error!";
        }

        $dom = $this->subDomain . '.' . $this->zone;
        fputs($fp, "$dom\r\n");

        // Getting string
        $string = '';

        // Checking whois server for .com and .net
        if ($this->zone == 'com' || $this->zone == 'net') {
            while (!feof($fp)) {
                $line = trim(fgets($fp, 128));

                $string .= $line;

                $lineArr = explode(":", $line);

                if (strtolower($lineArr[0]) == 'whois server') {
                    $this->whois_server = trim($lineArr[1]);
                }
            }
            // Getting whois information
            $fp = fsockopen($this->whois_server, 43);
            if (!$fp) {
                return "Connection error!";
            }

            $dom = $this->subDomain . '.' . $this->zone;
            fputs($fp, "$dom\r\n");

            // Getting string
            $string = '';

            while (!feof($fp)) {
                $string .= fgets($fp, 128);
            }

            // Checking for other tld's
        } else {
            while (!feof($fp)) {
                $string .= fgets($fp, 128);
            }
        }
        fclose($fp);

        return $string;
    }

    public function getInfoHtml($domain)
    {
        return nl2br($this->getInfo($domain));
    }

    /**
     * @return string full domain name
     */
    public function getDomain($domain)
    {
        $this->construct($domain);
        return $this->domain;
    }

    /**
     * @return string top level domains separated by dot
     */
    public function getZone($domain)
    {
        $this->construct($domain);
        return $this->zone;
    }

    /**
     * @return string return subdomain (low level domain)
     */
    public function getSubDomain($domain)
    {
        $this->construct($domain);
        return $this->subDomain;
    }

    public function isAvailable($domain)
    {
        $this->construct($domain);

        $whois_string = $this->getInfo($domain);
        $not_found_string = '';
        if (isset($this->servers[$this->zone][1])) {
            $not_found_string = $this->servers[$this->zone][1];
        }

        $whois_string2 = @preg_replace('/' . $this->domain . '/', '', $whois_string);
        $whois_string = @preg_replace("/\s+/", ' ', $whois_string);

        $array = explode(":", $not_found_string);
        if ($array[0] == "MAXCHARS") {
            if (strlen($whois_string2) <= $array[1]) {
                return true;
            } else {
                return false;
            }
        } else {
            if (preg_match("/" . $not_found_string . "/i", $whois_string)) {
                return true;
            } else {
                return false;
            }
        }
    }

}
