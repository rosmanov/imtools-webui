<?php
/**
 * Database abstraction layer
 */
namespace ImTools\WebUI;

use \RuntimeException;

class Db
{
    /// Connection object
    protected static $_conn;

    public static function connect()
    {
        if (static::$_conn) {
            return;
        }

        $db = Conf::get('db');

        if (! (static::$_conn = mysqli_connect('p:' . $db['host'], $db['user'],
            $db['password'], /* DB name */'', $db['port'])))
        {
            throw new RuntimeException('Could not connect to database, config: ' . var_export($db, true));
        }

        mysqli_select_db(static::$_conn, $db['name']);
        mysqli_query(static::$_conn, "SET NAMES 'utf8'");
    }

    public static function disconnect() {
        mysqli_close(static::$_conn);
        static::$_conn = null;
    }

    public static function query($query, $buffered = true) {
        if (!static::$_conn) static::connect();

        if ($buffered) {
            $r = mysqli_query(static::$_conn, $query);
        } else {
            /// \note: in unbuffered mode we can't use mysqli_num_rows(), mysqli_data_seek() etc.
            $r = mysqli_query(static::$_conn, $query, \MYSQLI_USE_RESULT);
        }
        return $r;
    }

    public static function fetchAll($query) {
        if (! ($result = static::query($query))) {
            return false;
        }
        return $result->fetch_all(\MYSQLI_ASSOC);
    }

    public static function f1($query) {
        if (!static::$_conn) static::connect();

        $result = static::query($query);

        if (!$result || mysqli_num_rows($result) == 0) {
            $return = false;
        } else {
            $return = mysqli_fetch_row($result)[0];
            static::free($result);
        }

        return $return;
    }

    public static function escape($value) {
        if (!static::$_conn) static::connect();

        if (!is_numeric($value)) {
            $value = mysqli_real_escape_string(static::$_conn, $value);
        }

        return $value;
    }

    public static function insert($table, array $fields)
    {
        $field_names = array_keys($fields);
        foreach ($field_names as &$v) {
            $v = '`' . static::escape($v) . '`';
        }
        $field_names = implode(',', $field_names);

        $field_values = array_values($fields);
        foreach ($field_values as &$v) {
            $v = "'" . static::escape($v) . "'";
        }
        $field_names = implode(',', $field_names);

        if (static::query('INSERT INTO `' . static::escape($table) . "` ($field_names) VALUES ($field_values)")) {
            return mysqli_insert_id(static::$_conn);
        }
    }
}