<?php
/*
 * -File        debug.php 
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004-2007, Nexista
 * -Author      joshua savage
 * -Author      albert lash
 */

/**
 * @package Nexista
 * @author Joshua Savage 
 */
 

/**
 * This class provides some debugging functions
 * @package     Nexista
 */



Class Debug
{


    /**
     * Pretty prints an array
     *
     * @param    array       The array to dump to screen
     * @param    name        Optional pretty name to print
     */

    static public function dump($array, $name = '')
    {
        echo '<font color="orange"><br>--- <b>Begin array dump:</b> ' . $name . '---<br></font><pre>';
        if(!empty($array))
        {
            echo '<font color="purple">';
            print_r($array);
            echo '</font>';
        }
        else
        {
            echo "Empty array:";
        }
        echo '</pre><br><font color="orange"><b>--- End array dump ---</b></font>';
    }


    /**
     * Prints out the arguments from a function
     *
     * @param   string  argument array from a function
     */

    static public function args(&$args)
    {

        echo "<br><b>Function Arguments:</b><br>";

        foreach($args as $key=>$val)
        {
            $index = $key + 1;

            if(is_array($val) && $this->array_mode)
            {
                echo "Arg #$index: ";
                $this->array_dump($val);
                echo "<br>";
            }
            else
            {
                echo "Arg #$index: $val<br>";
            }

        }
        echo "<br>";
    }

    /**
     * Registers current active module
     *
     * This function is used to register the name of the current
     * active module/function in order to help trace debugging and profiling.
     * $type determines the type of message:
     * 'in' - sets function entry
     * 'out' - sets function exit - prints elapsed time
     *
     * @param   string      type of registration
     * @param   string      module name
     * @return  string      elapsed time
     */

    static public function register($type, $function)
    {

        if(isset($GLOBALS['debugTrack']) && $GLOBALS['debugTrack'] == 'on')
        {
            switch($type)
            {

                case 'in':

                    $pos = 0;
                    if(isset($GLOBALS['debugTrackModule']))
                    {
                        $pos = @count($GLOBALS['debugTrackModule']);
                    }

                    $GLOBALS['debugTrackModule'][$pos]['name'] = $function;
                    $GLOBALS['debugTrackModule'][$pos]['startTime'] = microtime();

                    $indent = ($pos) * 6 ;
                    Debug::message(str_pad('> ', $indent,'-', STR_PAD_LEFT). '<b>'.$function .'</b>');

                    break;

                case 'out':
                    $pos = @count($GLOBALS['debugTrackModule']) - 1;
                    $indent = $pos * 6 ;


                    $GLOBALS['debugTrackModule'][$pos]['elapsedTime'] = Debug::profile($GLOBALS['debugTrackModule'][$pos]['startTime']);
                    Debug::message(str_pad('< ', $indent, "-", STR_PAD_LEFT). '<b>'.$function . ' @ </b>' . $GLOBALS['debugTrackModule'][$pos]['elapsedTime'].' seconds');

                    unset($GLOBALS['debugTrackModule'][$pos]);
                    break;

            }

        }
        // This is for debugging the debug class. Probably don't want to ever use this now. 
        //Debug::dump($GLOBALS['debugTrackModule']);

    }



    /**
     * Logs a generic debug message
     *
     * This function accepts a message that can be used to debug
     * or trace script flow. Depending on the setting of
     *
     * @param   string      message to log/print
     * @return  string      elapsed time
     */

    static public function message($message)
    {
        if(isset($GLOBALS['debugTrack']) && $GLOBALS['debugTrack'] === true)
        {
            echo '<br><font color="purple">' . $message.'</font>';
        }
    }


    /**
     * Returns current time in seconds
     *
     * @param   string      microtime
     * @return  string      elapsed time
     */

    static public function getMicrotime($start)
    {

        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }


    /**
     * Calculates elapsed time
     *
     * @param   integer     start time to use. If none supplied, script start time is used
     * @return  string      elapsed time
     */

    static public function profile($startTime = false)
    {
        list($usec, $sec) = explode(" ",microtime());
        $end = ((float)$usec + (float)$sec);

        if(!$startTime)
        {
            $start = $GLOBALS['debugStartTime'];
        }
        else
        {
            $start = &$startTime;
        }

        list($usec, $sec) = explode(" ", $start);
        $start = ((float)$usec + (float)$sec);


        return number_format($end - $start,3);


    }





} // end class

?>
