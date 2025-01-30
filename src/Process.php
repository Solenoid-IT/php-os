<?php



namespace Solenoid\OS;



class Process
{
    private $resource;



    public string $cmd;
    public string $cwd;

    public string $input;
    public string $output;

    public int $pid;
    public int $exitcode;



    # Returns [self]
    public function __construct (string $cmd, ?string $cwd = null)
    {
        // (Getting the values)
        $this->cmd = $cmd;
        $this->cwd = $cwd;



        // (Setting the value)
        $this->input = '';
    }



    # Returns [self]
    public function set_input (string $input)
    {
        // (Getting the value)
        $this->input = $input;



        // Returning the value
        return $this;
    }



    # Returns [self|false]
    public function start ()
    {
        // (Getting the value)
        $descriptor =
        [
            0 => [ 'pipe', 'r' ],# 'child STDIN'
            1 => [ 'pipe', 'w' ],# 'child STDOUT'
            #2 => [ 'file', '/tmp/err_' . time(), 'a' ]# 'child STDERR'
        ]
        ;



        // (Opening the process)
        $this->resource = proc_open( "nohup $this->cmd >/dev/null 2>&1 & echo $!", $descriptor, $pipes, $this->cwd );

        if ( !$this->resource )
        {// (Unable to open the process)
            // Returning the value
            return false;
        }



        if ( fwrite( $pipes[0], $this->input ) === false )
        {// (Unable to write to the stream for child STDIN)
            // Returning the value
            return false;
        }

        if ( !fclose( $pipes[0] ) )
        {// (Unable to close the stream for child STDIN)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $this->pid = (int) trim( stream_get_contents( $pipes[1] ) );

        if ( !fclose( $pipes[1] ) )
        {// (Unable to close the stream for child STDOUT)
            // Returning the value
            return false;
        }



        // (Closing the process)
        $this->exitcode = proc_close( $this->resource );



        // Returning the value
        return $this;
    }

    # Returns [self]
    public function wait ()
    {
        // (Closing the process)
        $this->exitcode = proc_close( $this->resource );



        // Returning the value
        return $this;
    }



    # Returns [self|false]
    public function run ()
    {
        // (Getting the value)
        $descriptor =
        [
            0 => [ 'pipe', 'r' ],# 'child STDIN'
            1 => [ 'pipe', 'w' ],# 'child STDOUT'
            #2 => [ 'file', '/tmp/err_' . time(), 'a' ]# 'child STDERR'
        ]
        ;



        // (Opening the process)
        $this->resource = proc_open( $this->cmd, $descriptor, $pipes, $this->cwd );

        if ( !$this->resource )
        {// (Unable to open the process)
            // Returning the value
            return false;
        }



        if ( fwrite( $pipes[0], $this->input ) === false )
        {// (Unable to write to the stream for child STDIN)
            // Returning the value
            return false;
        }

        if ( !fclose( $pipes[0] ) )
        {// (Unable to close the stream for child STDIN)
            // Returning the value
            return false;
        }



        /*

        // (Setting the stream as non blocking)
        stream_set_blocking( $pipes[1], false );

        */



        // (Getting the value)
        $this->output = stream_get_contents( $pipes[1] );

        if ( !fclose( $pipes[1] ) )
        {// (Unable to close the stream for child STDOUT)
            // Returning the value
            return false;
        }



        // (Closing the process)
        $this->exitcode = proc_close( $this->resource );



        // Returning the value
        return $this;
    }



    # Returns [int|false]
    public static function spawn (string $cmd, ?string $cwd = null, ?string $input = null)
    {
        // (Setting the value)
        $input_stream = '';

        if ( $input )
        {// Value found
            // (Creating a tmp-file)
            $tmp_file_path = tempnam( '/tmp', time() . '_' );

            if ( !$tmp_file_path )
            {// (Unable to create the tmp-file)
                // Returning the value
                return false;
            }

            if ( file_put_contents( $tmp_file_path, $input ) === false )
            {// (Unable to write to the file)
                // Returning the value
                return false;
            }



            // (Getting the value)
            $input_stream = " < $tmp_file_path";
        }



        if ( $cwd )
        {// Value found
            if ( !chdir( $cwd ) )
            {// (Unable to set the directory)
                // Returning the value
                return false;
            }
        }



        // (Getting the value)
        $pid = (int) trim( shell_exec( "nohup $cmd{$input_stream} >/dev/null 2>&1 & echo $!" ) );



        // Returning the value
        return $pid;
    }
}



?>