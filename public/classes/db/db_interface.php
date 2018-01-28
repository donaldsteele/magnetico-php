<?php
/**
 * Created by PhpStorm.
 * User: afacpadmin
 * Date: 1/24/2018
 * Time: 7:34 PM
 */

namespace classes\db;
interface db_interface
{

    public function GetCount();

    public function Search($term, $page);

    public function Detail($torrent_id);
}