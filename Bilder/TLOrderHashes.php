<?php

namespace Lupcom\IsotopeQrCodeBundle\Classes;

class TLOrderHashes
{
    private $db;
    private $tableName = "tl_iso_order_hashes";

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function addEntry($hash, $order_id, $valid = true) {
        $this->db->prepare("INSERT INTO {$this->tableName} (hash, iso_product_collection_id, valid) VALUES (?, ?, ?);")->execute([$hash, $order_id, $valid]);
    }

    public function getOrdersBySearch($search): array {
        $sql = "SELECT pc.id, oh.hash, oh.valid, pc.document_number, DATE_FORMAT(FROM_UNIXTIME(pc.tstamp), '%d.%m.%Y') AS date, ia.firstname, ia.lastname, ia.email 
        FROM {$this->tableName} AS oh 
        JOIN tl_iso_product_collection AS pc ON oh.iso_product_collection_id=pc.id 
        JOIN tl_iso_product_collection_item AS pci ON pc.id=pci.pid 
        JOIN tl_iso_address AS ia ON pc.id=ia.pid 
        WHERE pc.document_number LIKE CONCAT('%', ?, '%') 
            OR ia.firstname LIKE CONCAT('%', ?, '%') 
            OR ia.lastname LIKE CONCAT('%', ?, '%') 
            OR DATE_FORMAT(FROM_UNIXTIME(pc.tstamp), '%d.%m.%Y') LIKE CONCAT('%', ?, '%') 
        GROUP BY pc.id, oh.hash, oh.valid, pc.document_number, date, ia.firstname, ia.lastname, ia.email
        ORDER BY date DESC;";
        $result = $this->db->prepare($sql)->execute([$search, $search, $search, $search])->fetchAllAssoc();

        return $result;
    }

    public function checkIfHashExists($hash) {
        if (empty($hash)) return false;

        $result = $this->db->prepare("SELECT id FROM {$this->tableName} WHERE hash=?")->execute([$hash])->fetchAllAssoc();

        if (!empty($result)) {
            return true;
        }

        return false;
    }

    public function checkIfHashIsValid($hash) {
        if (empty($hash)) return false;

        $result = $this->db->prepare("SELECT valid FROM {$this->tableName} WHERE hash=?")->execute([$hash])->fetchAllAssoc();

        if (($result[0]["valid"] ?? 0) == 1) {
            return true;
        }

        return false;
    }

    public function redeemTicket($hash) {
        if (empty($hash)) return false;

        $result = $this->db->prepare("UPDATE {$this->tableName} SET valid=0 WHERE hash=?")->execute([$hash]);

        if (!empty($result)) {
            return true;
        }

        return false;
    }
}