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
