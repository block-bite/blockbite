<?php

namespace Blockbite\Blockbite\Controllers;
// use Exception
use Exception;
// use WP_Error
use WP_Error;

class Database extends Controller
{

    // icon directory
    private $icon_dir;
    // icon uri
    private $icon_uri;

    public function __construct() {}


    public static function prepData($data)
    {
        if (isset($data['_locale'])) {
            unset($data['_locale']);
        }
        return $data;
    }

    public static function createTable()
    {

        global $wpdb;

        try {
            $table_name = $wpdb->prefix . 'blockbite';
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id INT(11) NOT NULL AUTO_INCREMENT,
                handle VARCHAR(500) NOT NULL,
                category VARCHAR(500),
                blockname VARCHAR(500),
                is_default BOOLEAN DEFAULT 0,
                platform VARCHAR(100),
                title VARCHAR(500),
                slug VARCHAR(500) NOT NULL,
                version VARCHAR(100) DEFAUlT '1.0.0',
                summary VARCHAR(500) NOT NULL,
                css LONGTEXT NOT NULL,
                tailwind TEXT NOT NULL,
                content LONGTEXT NOT NULL,
                post_id INT(11) NOT NULL,
                parent INT(11) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        } catch (Exception $e) {
            // Set a transient to indicate failure
            set_transient('blockbite_db_creation_failed', true, 60 * 60); // Set for 1 hour
        }
    }

    /**
     * Check if the table exists in the database
     * @return bool 
     */

    public static function checkTableExists()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        $query = "SHOW TABLES LIKE %s";
        $result = $wpdb->get_var($wpdb->prepare($query, $table_name));

        if ($result == $table_name) {
            // Table exists
            return true;
        } else {
            // Table does not exist
            return false;
        }
    }

    /**
     * Update or create a record in the database from $where Array
     * @param mixed $data 
     * @param mixed $where 
     * @return int|false 
     */


    public static function updateOrCreateRecord($data, $where)
    {

        $data = self::prepData($data);

        // Update or insert based on the $where condition
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        // Build the WHERE clause dynamically
        $where_clauses = [];
        $where_values = [];

        foreach ($where as $column => $value) {
            $where_clauses[] = "$column = %s";
            $where_values[] = $value;
        }

        $where_clause = implode(' AND ', $where_clauses);
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE $where_clause", ...$where_values);
        $record = $wpdb->get_row($query);

        if (isset($record->id)) {
            $wpdb->update($table_name, $data, $where);
            $record_id = $record->id;
        } else {
            $wpdb->insert($table_name, $data);
            $record_id = $wpdb->insert_id;
        }
        $data['id'] = $record_id;

        return $data;
    }

    /**
     * Update or create a record in the database by handle column
     * @param mixed $data 
     * @param mixed $handle 
     * @return int|false 
     */

    public static function updateOrCreateHandle($data, $handle)
    {

        $data = self::prepData($data);

        if (!isset($handle)) {
            throw new Exception('handle column is required');
        }
        // find handle
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE handle = %s", $handle);
        $record = $wpdb->get_row($query);
        if ($record) {
            $wpdb->update($table_name, $data, ['handle' => $handle]);
            $record_id = $record->id;
        } else {
            $data['handle'] = $handle;
            $wpdb->insert($table_name, $data);
            $record_id = $wpdb->insert_id;
        }
        $data['id'] = $record_id;
        return $data;
    }

    /**
     * Update or create a record in the database by id
     * @param mixed $data 
     * @param mixed $id 
     * @return int|false 
     */
    public static function updateOrCreateRecordById($data, $id)
    {
        $data = self::prepData($data);

        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
        $record = $wpdb->get_row($query);
        if ($record) {
            // update
            $wpdb->update($table_name, $data, ['id' => $id]);
            $record_id = $record->id;
        } else {
            $data['id'] = $id;
            $wpdb->insert($table_name, $data);
            $record_id = $wpdb->insert_id;
        }
        $data['id'] = $record_id;
        return $data;
    }


    public static function insertRecords($data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $inserted = 0;
        foreach ($data as $record) {
            // $record = self::prepData($record);
            if (isset($record['id'])) {
                unset($record['id']);
            }
            $wpdb->insert($table_name, $record);
            $inserted++;
        }
        return $inserted;
    }


    /**
     * Get a record from the database by handle column
     * @param mixed $handle 
     * @return array|object|null|void 
     */
    public static function getRecordByHandle($handle)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE handle = %s", $handle);
        $record = $wpdb->get_row($query);
        return $record;
    }
    public static function getAllRecordsByHandle($handle)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE handle = %s", $handle);
        $records = $wpdb->get_results($query);

        // Ensure $records is an array (this might not be necessary as get_results returns an array)
        if (empty($records)) {
            return [];
        }

        return $records;
    }

    public static function getRecordByQuery($query)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $where_clauses = [];
        $where_values = [];
        foreach ($query as $column => $value) {
            $where_clauses[] = "$column = %s";
            $where_values[] = $value;
        }
        $where_clause = implode(' AND ', $where_clauses);
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE $where_clause", ...$where_values);
        $record = $wpdb->get_row($query);
        return $record;
    }


    public static function getRecordsByHandles($handles = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $where_clauses = [];
        $where_values = [];
        foreach ($handles as $handle) {
            $where_clauses[] = "handle = %s";
            $where_values[] = $handle;
        }
        $where_clause = implode(' OR ', $where_clauses);
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE $where_clause", ...$where_values);
        $records = $wpdb->get_results($query);
        return $records;
    }


    /**
     * Get a record from the database by id
     * @param mixed $id 
     * @return array|object|null|void 
     */

    public static function getRecord($where)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        // Build the WHERE clause dynamically
        $where_clauses = [];
        $where_values = [];

        foreach ($where as $column => $value) {
            $where_clauses[] = "$column = %s";
            $where_values[] = $value;
        }

        $where_clause = implode(' AND ', $where_clauses);
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE $where_clause", ...$where_values);

        // Execute the query and return the record
        $record = $wpdb->get_row($query);

        return $record;
    }


    // delete record by id
    public static function deleteRecordById($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $deleted = $wpdb->delete($table_name, ['id' => intval($id)]);
        // error_log('deleted: ' . $deleted);
        return $deleted;
    }

    public static function deleteAllRecordsByQuery($query)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $deleted = $wpdb->delete($table_name, $query);
        return $deleted;
    }

    public static function toggleDefaultHandle($id, $handle, $is_default, $blockname)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        $wpdb->update($table_name, ['is_default' => 0], ['handle' => $handle, 'blockname' => $blockname]);
        $default = $wpdb->update($table_name, ['is_default' => $is_default], ['id' => $id]);
        return $default;
    }
}
