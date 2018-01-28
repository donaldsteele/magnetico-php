<?php
/**
 * Created by PhpStorm.
 * User: afacpadmin
 * Date: 1/24/2018
 * Time: 7:36 PM
 */

namespace classes\db;

class sqlite implements db_interface
{


    /**
     * @var \SQLite3
     */
    private $db_connection;

    function __construct($file_path)
    {
        $this->db_connection = new \SQLite3($file_path, SQLITE3_OPEN_READONLY);
    }

    public function GetCount()
    {
        $sql = 'select count(*) from torrents;';
        $row = $this->db_connection->querySingle($sql);
        return $row;

    }

    public function Detail($id)
    {
        $files = [];
        $torrent = [];

        $sql = 'select size, path from files where torrent_id = :torrent_id;';
        $stmt = $this->db_connection->prepare($sql);
        $stmt->bindValue(':torrent_id', $id, SQLITE3_INTEGER);

        $results = $stmt->execute();

        while ($res = $results->fetchArray(1)) {
            array_push($files, $res);
        }


        $sql = 'select info_hash,name,total_size,discovered_on from torrents where id = :torrent_id;';
        $stmt = $this->db_connection->prepare($sql);
        $stmt->bindValue(':torrent_id', $id, SQLITE3_INTEGER);

        $results = $stmt->execute();

        while ($res = $results->fetchArray(1)) {
            array_push($torrent, $res);
        }

        return ['torrent' => $torrent,
            'files' => $files,
        ];
    }

    public function Search($term, $page)
    {


        $start = ($page - 1) * 10;
        $end = 10;
        $data = [];
        $term = '%' . $term . '%';


        $sql = 'select id,info_hash,name,total_size,discovered_on from torrents where name like :term limit :start,:end;';

        $stmt = $this->db_connection->prepare($sql);
        $stmt->bindValue(':term', $term, SQLITE3_TEXT);
        $stmt->bindValue(':start', $start, SQLITE3_INTEGER);
        $stmt->bindValue(':end', $end, SQLITE3_INTEGER);

        $results = $stmt->execute();

        while ($res = $results->fetchArray(1)) {
            array_push($data, $res);
        }

        $totalsql = 'select count(*) as torrent_count from torrents where name like :term;';
        $stmt = $this->db_connection->prepare($totalsql);
        $stmt->bindValue(':term', $term, SQLITE3_TEXT);
        $results = $stmt->execute();
        $total_count = $results->fetchArray(1)['torrent_count'];

        $pages = ceil($total_count / 10);

        return [
            'page_info' => [
                'pages' => $pages,
                'current' => $page,
                'total' => $total_count
            ],
            'results' => $data];
    }
}