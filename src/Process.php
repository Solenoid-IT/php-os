<?php



namespace Solenoid\OS;



class Process
{
    private $resource;



    public string $cmd;

    public ?string $cwd;

    public ?string $input;
    public ?string $output;
    public ?string $error;

    public int $pid;
    public int $exitcode;



    # Returns [self]
    public function __construct (string $cmd)
    {
        // (Getting the value)
        $this->cmd = $cmd;



        // (Setting the value)
        $this->cwd = null;
    


        // (Setting the values)
        $this->input  = null;
        $this->output = null;
        $this->error  = null;
    }



    # Returns [self]
    public function set_cwd (?string $cwd = null)
    {
        // (Getting the value)
        $this->cwd = $cwd;



        // Returning the value
        return $this;
    }



    # Returns [self]
    public function set_input (?string $input = null)
    {
        // (Getting the value)
        $this->input = $input;



        // Returning the value
        return $this;
    }



    # Returns [self|false]
    public function start ()
    {
        if ( $this->cwd )
        {// Value found
            // (Getting the value)
            $cwd = getcwd();

            if ( !chdir( $this->cwd ) )
            {// (Unable to set the directory)
                // Returning the value
                return false;
            }
        }



        // (Setting the value)
        $input = '';

        if ( $this->input )
        {// Value found
            // (Getting the value)
            $tmp_file_path = tempnam( '/tmp', 'async_proc_' );

            if ( !file_put_contents( $tmp_file_path, $this->input ) )
            {// (Unable to write to the file)
                // Returning the value
                return false;
            }



            // (Getting the value)
            $input = " < $tmp_file_path";
        }



        // (Getting the value)
        $this->pid = trim( shell_exec( "nohup $this->cmd{$input} >/dev/null 2>&1 & echo $!" ) );



        if ( $this->cwd )
        {// Value found
            if ( !chdir( $cwd ) )
            {// (Unable to set the directory)
                // Returning the value
                return false;
            }
        }



        if ( $this->input )
        {// Value found
            if ( !unlink( $tmp_file_path ) )
            {// (Unable to remove the file)
                // Returning the value
                return false;
            }
        }



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
            2 => [ 'pipe', 'w' ],# 'child STDERR'
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



        // (Getting the value)
        $this->error = stream_get_contents( $pipes[2] );

        if ( !fclose( $pipes[2] ) )
        {// (Unable to close the stream for child STDERR)
            // Returning the value
            return false;
        }



        // (Closing the process)
        $this->exitcode = proc_close( $this->resource );



        // Returning the value
        return $this;
    }



    # Returns [self|false]
    public static function spawn (string $cmd, ?string $cwd = null, ?string $input = null)
    {
        // Returning the value
        return ( new Process( $cmd ) )->set_cwd( $cwd )->set_input( $input )->start();
    }



    # Returns [string]
    public function __toString ()
    {
        // Returning the value
        return $this->cmd;
    }
}



?>