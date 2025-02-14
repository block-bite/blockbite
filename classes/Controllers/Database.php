<?php

namespace Blockbite\Blockbite\Controllers;
// use Exception

use Error;
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


        // should match validate function in Items.php
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
            version VARCHAR(100) DEFAULT '1.0.0',
            summary VARCHAR(500) NOT NULL,
            content LONGTEXT NOT NULL,
            post_id INT(11) NOT NULL,
            parent INT(11) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            css LONGTEXT NOT NULL,
            tailwind LONGTEXT NOT NULL,
            data JSON NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_handle (handle)
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
            return true;
        } else {
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        // Prepare the data and ensure updated_at is set
        $data = self::prepAndAddTimestamps($data);

        // Build the WHERE clause
        $where_clause_data = self::buildWhereClause($where);

        // Fetch the record
        $record = self::findRecord($table_name, $where_clause_data['clause'], $where_clause_data['values']);

        if ($record) {
            // Update the record
            $wpdb->update($table_name, $data, $where);
            $record_id = $record->id;
        } else {
            // Insert a new record
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
        if (empty($handle)) {
            throw new Exception('handle column is required and cannot be empty');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        // Prepare the data and ensure updated_at is set
        $data = self::prepAndAddTimestamps($data);

        // Find the most relevant record to update
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE handle = %s ORDER BY updated_at DESC LIMIT 1",
            $handle
        );
        $record = $wpdb->get_row($query);

        if ($record) {
            // Update the record
            $wpdb->update($table_name, $data, ['id' => $record->id, 'handle' => $handle]);
            $record_id = $record->id;
        } else {
            // Insert a new record
            $data['handle'] = $handle;
            $wpdb->insert($table_name, $data);
            $record_id = $wpdb->insert_id;
        }

        $data['id'] = $record_id;
        return $data;
    }

    /**
     * Prepare data and add timestamps
     * @param array $data
     * @return array
     */
    private static function prepAndAddTimestamps($data)
    {
        $data = self::prepData($data);
        $data['updated_at'] = current_time('mysql');

        /*
            check if data has data property since we use no default property but : data JSON NOT NULL
            we experienced difficulties with different databases when adding a default value on the db creation
            this should be a solid fix
        */
        if (!isset($data['data'])) {
            $data['data'] = json_encode([]);
        }
        return $data;
    }

    /**
     * Build a WHERE clause from an array
     * @param array $where
     * @return array
     */
    private static function buildWhereClause($where)
    {
        $where_clauses = [];
        $where_values = [];

        foreach ($where as $column => $value) {
            $where_clauses[] = "$column = %s";
            $where_values[] = $value;
        }

        return [
            'clause' => implode(' AND ', $where_clauses),
            'values' => $where_values,
        ];
    }

    /**
     * Find a record in the database
     * @param string $table_name
     * @param string $where_clause
     * @param array $where_values
     * @return object|null
     */
    private static function findRecord($table_name, $where_clause, $where_values)
    {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where_clause ORDER BY updated_at DESC LIMIT 1",
            ...$where_values
        );
        return $wpdb->get_row($query);
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


    public static function getAllRecordsByHandle($handle, $select = ['*'])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        // Convert the $select array to a comma-separated string
        $select_clause = implode(', ', $select);

        $query = $wpdb->prepare("SELECT $select_clause FROM $table_name WHERE handle = %s", $handle);
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


    public static function getGlobalStyles()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE post_type = %s", 'wp_global_styles');
        $record = $wpdb->get_row($query);

        // if post_content
        if (isset($record->post_content)) {
            return json_decode($record->post_content);
        }

        return $record;
    }


    public static function getUtils()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';

        // Corrected SQL query to extract only the 'utils' field
        $query = $wpdb->prepare("SELECT JSON_EXTRACT(data, '$.utils') as utils FROM $table_name WHERE handle = %s", 'bites');

        // Get results (fetch all matching rows)
        $records = $wpdb->get_results($query);



        $utils_array = [];

        // Loop through results and decode JSON utils
        foreach ($records as $record) {
            $utils = json_decode($record->utils, true); // Decode JSON string into PHP array
            if (is_array($utils)) {
                $utils_array = array_merge($utils_array, $utils); // Merge into a single array
            }
        }


        return $utils_array;
    }
}
