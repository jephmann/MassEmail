<?php
    function selectoptions($selectedoption,$host,$un,$pw,$db,$table,$field){
        $selectoptions = ('');
        $connection=mysqli_connect($host,$un,$pw,$db) or die ('Unable to connect!');
        $query=('SELECT '.$field.' FROM '.$table.' GROUP BY '.$field.' ORDER BY '.$field);
        $result=mysqli_query($connection,$query) or die ("Error in query: $query. ".mysqli_error());
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $fielddata=$row[$field];
                if($selectedoption == $fielddata){
                    $selected = (' selected');                    
                }else{
                    $selected = ('');                    
                }
                $selectoptions .= (chr(10).'<option'.$selected.' value="'.$fielddata.'">'.$fielddata.'</option>');
            }
            mysqli_close($connection);
        }
        return $selectoptions;
    }
?>
