<?php

namespace classes;
class service_handler
{

    /**
     * @var \classes\db\db_interface
     */
    private $db;


    function __construct($database)
    {
        $this->db = $database;

    }

    function route()
    {

        $parts = array_filter(explode('/', $_SERVER['REQUEST_URI']));
        $function = $parts[2];

        if (method_exists($this, $_SERVER['REQUEST_METHOD'] . '_' . $function)) {
            // remove un needed items from parts array
            array_shift($parts);
            array_shift($parts);
            header('Content-Type: application/json');
            call_user_func_array([$this, $_SERVER['REQUEST_METHOD'] . '_' . $function], [$parts]);
        } else {
            header("HTTP/1.1 400 Bad Request", true);
            header('Content-Type: application/json');
            $output = ['message' => 'Unknown Method', 'error' => '400', 'parts' => $parts];
            print json_encode($output, JSON_HEX_QUOT | JSON_HEX_TAG);
        }

    }

    function GET_info($parts)
    {
        $this->return_val('torrent_count', $this->db->GetCount());
    }

    private function return_val($type, $data)
    {
        $message = ['result' => 'ok', 'type' => $type, 'data' => $this->convert_from_latin1_to_utf8_recursively($data)];
        print json_encode($message);
    }

    public function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);
            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }

    function GET_detail($parts)
    {
        $id = $parts[0] ?? -100000000;
        $data = $this->db->Detail($id);
        foreach ($data['torrent'] as $key => $record) {
            $data['torrent'][$key]['magnet'] = $this->generate_magnet_link($record);
        }
        $this->return_val('torrentdetail', $data);
    }

    function generate_magnet_link($torrent_record)
    {
        //update at https://raw.githubusercontent.com/ngosang/trackerslist/master/trackers_best_ip.txt
        $trackers = implode([
            '&tr=' . urlencode('udp://87.233.192.220:6969/announce'),
            '&tr=' . urlencode('udp://62.138.0.158:6969/announce'),
            '&tr=' . urlencode('udp://151.80.120.115:2710/announce'),
            '&tr=' . urlencode('http://163.172.81.35:1337/announce'),
            '&tr=' . urlencode('udp://163.172.81.35:1337/announce'),
            '&tr=' . urlencode('http://51.15.4.13:1337/announce'),
            '&tr=' . urlencode('udp://198.54.117.24:1337/announce'),
            '&tr=' . urlencode('udp://82.45.40.204:1337/announce'),
            '&tr=' . urlencode('udp://123.249.16.65:2710/announce'),
            '&tr=' . urlencode('udp://5.226.21.164:6969/announce'),
            '&tr=' . urlencode('udp://210.244.71.25:6969/announce'),
            '&tr=' . urlencode('udp://78.142.19.42:1337/announce'),
            '&tr=' . urlencode('udp://211.149.236.45:6969/announce'),
            '&tr=' . urlencode('udp://109.236.91.32:6969/announce'),
            '&tr=' . urlencode('udp://83.208.197.185:1337/announce'),
            '&tr=' . urlencode('udp://51.15.4.13:1337/announce'),
            '&tr=' . urlencode('udp://191.96.249.23:6969/announce'),
            '&tr=' . urlencode('udp://91.218.230.81:6969/announce'),
            '&tr=' . urlencode('udp://37.19.5.155:6969/announce'),
            '&tr=' . urlencode('http://77.91.229.218:6881/announce'),
        ], "");
        return sprintf('magnet:?xt=urn:btih:%1$s&dn=%2$s%3$s', bin2hex($torrent_record['info_hash']), urlencode($torrent_record['name']), $trackers);
    }

    function GET_search($parts)
    {

        $term = urldecode($parts[0] ?? '');
        $page = $parts[1] ?? 1;


        $data = $this->db->Search($term, $page);
        foreach ($data['results'] as $key => $record) {
            $data['results'][$key]['magnet'] = $this->generate_magnet_link($record);

        }

        $this->return_val('search', $data);
    }
}