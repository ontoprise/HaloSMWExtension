<?php
/**
*   Class with Sparql-Unittest helper methods
*/
class SparqlTestHelper
{
    public static function resultCheck($table, $result)
    {
        $match    = 0;
        $rows     = 0;
        $rowcount = $result['rowcount'];
        $hits     = $result['hits'];
        $result   = $result['part'];

        if ($rowcount == 0 && ($table == 'false' || $table == array())) {
            return true;
        }
        if (count($table) != count($result)) {
            return false;
        }


        if (!is_array($table)) {
            return false;
        }

        foreach ($table as $key => $value){
            foreach ($result as $innerKey => $innervalue){
                $match = 0;
                foreach ($innervalue as $varname => $varval){
                    if (isset($value[$varname])){
                        if (gettype($value[$varname]) == gettype($varval)
                            && $value[$varname] == $varval
                        ) {
                            $match++;
                        } else {
                            break;
                        }
                    }
                    if ($match == $rowcount){
                        $rows++;
                        unset($result[$innerKey]);
                        break;
                    }
                }
            }
        }

        if ($hits == $rows) {
            return true;
        } else {
            return false;
        }
    }//public static function resultCheck($table, $result)



    public static function resultCheckSort($table, $result)
    {
        foreach ($result as $key => $value) {
            foreach ($value as $varname => $varvalue) {
                if ($varvalue != $table[$key][$varname]) {
                    return false;
                }
            }
        }
        return true;
    }//public static resultCheckSort($table, $result)

}//class SparqlTestHelper
?>