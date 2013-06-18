db-pool-example
===============

This is an example of using a database pool for connections, with a little query abstraction thrown in.

Requiring DB.php will allow you to grab the database instance from any class by just executing:

    $db = DB::getFactory()->getConnection('read');
    
or
    
    $db = DB::getFactory()->getConnection('write');

This will check for an existing connection of the type necessary.  If a write connection is called on an 
existing read connection, the database will automatically call up to the pool to retrieve a write server.
If a read connection is called on a write connection, it will continue on with the same connection, since
the read function can still be performed.

Adding more servers to the pool is as simple as adding them to the $config array:

    private static $config = array(
        'write' =>
            array('mysql:dbname=user;host=localhost;charset=utf8'),
        'read' =>
            // Add more options for scalable slave servers
            array('mysql:dbname=user;host=localhost;charset=utf8'),
            array('mysql:dbname=user;host=123.123.123.121;charset=utf8'),
            array('mysql:dbname=user;host=123.123.123.122;charset=utf8'),
            array('mysql:dbname=user;host=123.123.123.123;charset=utf8'),
            array('mysql:dbname=user;host=123.123.123.124;charset=utf8')
        );

After the first insert:

    $db->insert((object)array('table' => 'foo', 'columns' => 'bar1,bar2', 'data' => (object)array('bar1' => 'item1', 'bar2' => 'item2')));

The result is:

    bar1: item1, bar2: item2, dateInserted: (current datetime), dateUpdated: (current datetime)
    
After the modify:

    $db->update((object)array('table' => 'foo', 'columns' => 'bar1,bar2', 'where' => 'bar1=:example', 'data' => (object)array('example' => 'item1', 'bar1' => 'item1changed', 'bar2' => 'item2changed')));

The result is:

    bar1: item1changed, bar2: item2changed, dateInserted: (inserted datetime), dateUpdated: (current datetime)
    
And finally, the delete:

    $db->delete((object)array('table' => 'foo', 'where' => 'bar1=:example', 'data' => array(':example' => 'item1changed')));
    
Feel free to use any or all of this example in any project.

-Justin
justin.mealey@gmail.com
