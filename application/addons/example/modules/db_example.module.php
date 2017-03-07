<?php
/**
 * Module library to demonstrate examples how to work with MySQL Server Database.
 * NOTICE: Before Run, Please Read and setup MySQL Connection
 *
 * See: install/Install.txt
 * See: http://tokernel.com/framework/documentation/class-libraries/mysql
 * see: /tokernel.framework/lib/mysql.lib.php
 *
 * Methods of this module called from /application/addons/example/lib/example.addon.php
 * This module uses mysql class library to access MySQL Server Database.
 *
 * @version 1.0.0
 */
/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class example_db_example_module extends module {

    protected $db;

    public function __construct($attr, $id_addon, $config, $log, $language) {

        parent::__construct($attr, $id_addon, $config, $log, $language);

        // Define MySQL library instance
        // 'toKernel_mysql_db' is the connection credentials section name in: /application/config/databases.ini
        $this->db = $this->lib->mysql->instance('toKernel_mysql_db');
    }

    /**
     * Example of inserting data into MySQL Server Database table
     */
    public function insert() {

        // Example of inserting data by simple Query
        $data = array(
            'title' => 'The title 1 ' . time(),
            'content' => 'The content 1 ' . md5(time())
        );

        echo "Inserting data...<br/>";

        $this->db->query("insert into articles set
          `title` = '".$this->db->escape($data['title'])."',
          `content` = '".$this->db->escape($data['content'])."'
        ");

        echo "Insert ID: " . $this->db->insert_id() . "<br />";

        // Example of inserting data by CRUD insert method
        $data = array(
            'title' => 'The title 2 ' . time(),
            'content' => 'The content 2 ' . md5(time())
        );

        echo "Inserting data...<br/>";

        $insert_id = $this->db->insert('articles', $data);

        echo "Insert ID: " . $insert_id . "<br />";

        echo "Insert complete!<br />";
        echo "See the articles table in your database.<br/>";

        return true;
    }

    /**
     * Example of updating data in MySQL Server Database table
     *
     */
    public function update() {

        // Example of updating data by simple Query
        $data = array(
            'title' => 'The title edited ' . time(),
            'content' => 'The content edited ' . md5(time())
        );

        $id_article = $this->get_latest_id();

        echo "Updating data...<br />";

        $this->db->query("update articles set
          `title` = '".$this->db->escape($data['title'])."',
          `content` = '".$this->db->escape($data['content'])."'
          where id_article = '".$this->db->escape($id_article)."'
        ");

        $affected_rows = $this->db->affected_rows();

        echo $affected_rows . " rows affected!<br />";

        // Example of updating data by CRUD update method

        $data = array(
            'title' => 'The title edited (2) ' . time(),
            'content' => 'The content edited (2) ' . md5(time())
        );

        $where = array(
            'id_article' => $id_article
        );

        echo "Updating data...<br />";

        $affected_rows = $this->db->update('articles', $data, $where);

        echo $affected_rows . " rows affected!<br />";

        echo "Update complete!<br />";
        echo "See the articles table in your database.<br />";

        return true;

    }

    /**
     * Example of deleting data from MySQL Server Database table
     *
     */
    public function delete() {

        // Example of deleting data by simple Query
        $id_article = $this->get_latest_id();

        echo "Deleting data...<br />";

        $this->db->query("delete from articles
          where id_article = '".$this->db->escape($id_article)."'
        ");

        $affected_rows = $this->db->affected_rows();

        echo $affected_rows . " rows affected!<br />";

        // Example of deleting data by CRUD delete method
        $where = array(
            'id_article' => $id_article
        );

        echo "Deleting data...<br />";

        $affected_rows = $this->db->delete('articles', $where);

        echo $affected_rows . " rows affected!<br />";

        echo "Delete complete!<br />";
        echo "See the articles table in your database.";

        return true;

    }

    /**
     * Example of selecting data from MySQL Server Database table by different options
     *
     */
    public function select() {

        echo "Selecting data...<br/>";

        echo "Selecting with option 1<br/>";

        // Selecting data by Simple query
        $result = $this->db->query("select * from articles order by id_article desc limit 5 ");

        // You can use also $this->db->object($result) to fetch object instead of array
        while($row = $this->db->assoc($result)) {
            echo $row['title'] . '<br />';
        }

        $this->db->free_result($result);

        echo "Selecting with option 2<br/>";

        // Selecting data and fetching all records into array
        // You can use fetch_all_object() to get array of objects instead.
        $result = $this->db->fetch_all_assoc("select * from articles order by id_article desc limit 5 ");

        foreach ($result as $row) {
            echo $row['title'] . '<br />';
        }

        echo "Selecting with option 3<br/>";

        // Selecting data by select_all_assoc() method
        // You can use select_all_object() to get array of objects instead.
        $result = $this->db->select_all_assoc('articles');

        foreach ($result as $row) {
            echo $row['title'] . '<br />';
        }

        echo "Complete!<br/>";

        return true;
    }

    /**
     * Example to get only one record from MySQL Server Database table
     *
     */
    public function get_latest_id() {

        // This example method returns only one record value selected from table
        return $this->db->result("select id_article from articles order by id_article desc limit 1 ");
    }

    /**
     * In this example, you can see, how to define different
     * instances of database objects and connect to different databases.
     *
     */
    public function connect_to_more_than_one_dbs() {

        // The 'Database1' and 'Database2' are section names in:
        // /application/config/databases.ini
        // You can make connections as many as you want.
        $db_obj1 = $this->lib->mysql->instance('Database1');
        $db_obj2 = $this->lib->mysql->instance('Database2');

        $result1 = $db_obj1->query("select * from articles limit 5");
        $result2 = $db_obj2->query("select * from articles limit 5");

        echo 'Database1<br />';
        while($row = $db_obj1->assoc($result1)) {
            print_r($row);
        }

        echo 'Database2<br />';
        while($row = $db_obj2->object($result2)) {
            print_r($row);
        }

    }

    /**
     * Other examples of MySQL Class library
     */
    public function other() {

        echo "Examples of other methods functionality!";

        // Select methods
        /*
        // select all as assoc array
        $where = array(
            'id_article' => 55
        );
        $where = null;
        print_r($this->db->select_all_assoc('articles', $where));

        // Select all ass object in assoc array
        $where = array(
            'id_article' => 55
        );
        $where = null;
        print_r($this->db->select_all_object('articles', $where));

        $where = array(
            'id_article' => 55
        );
        $where = null;
        print_r($this->db->select_object('articles', $where));

        $where = array(
            'id_article' => 55
        );
        $where = null;
        print_r($this->db->select_count('articles', $where));
        */

        // Return only one value from query result row
        /*
        // Return int
        $result = $this->db->result("select count(*) as cnt from articles");
        var_dump($result);

        // Return string
        $result = $this->db->result("select title from articles limit 1 ");
        var_dump($result);

        // Return null (record not found)
        $result = $this->db->result("select title from articles where id_article = -99 limit 1 ");
        var_dump($result);
        */


        // MySQL Query (return query results object)
        /*
        $result = $this->db->query("select * from articles limit 5");
        var_dump($result);
        */

        // MySQL Query (return query result bool)
        /*
        $result = $this->db->query("update articles set title='Title edited' where id_article=55");
        var_dump($result);
        */

        // Number of fields
        /*
        $result = $this->db->query("select * from articles");
        echo $this->db->num_fields($result);
        */

        // Number of rows
        /*
        $result = $this->db->query("select * from articles where id_article = -22 ");
        echo $this->db->num_rows($result);
        */

        // Examples of fetching methods.
        /*
        var_dump($this->db->fetch_assoc("select * from articles where id_article = 5"));
        var_dump($this->db->fetch_object("select * from articles where id_article = 5"));
        var_dump($this->db->fetch_row("select * from articles where id_article = 5"));
        */

        // count() method usage
        /*
        $where = array(
            'id_article' => 10
        );
        echo $this->db->count('articles', $where);
        echo $this->db->count('articles');
        */

        // Transaction
        /*
        echo "Begin transaction<br />";
        $this->db->begin_trans();
        $this->insert();
        $this->update();
        echo "Commit transaction<br />";
        //$this->db->rollback_trans();
        $this->db->commit_trans();
        echo "Done!";
        */
    }

} // End of class module
?>