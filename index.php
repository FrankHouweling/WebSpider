<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        // put your code here
        
            $ur =   "http://startpagina.nl/";
        
            require( "spider.class.php" );
            
            
            $done   =   array( $ur );
        
            function gaan( $url )
            {
                
                global $done;
                
                echo "<h2>" . $url . "</h2>";
                
                $spider = new WebSpider( $url );
            
                $spider->run();
                
                var_dump( $spider->listFoundKeywords());
                       
                echo "\n\n";
                
                foreach( $spider->listFoundURLs() as $furl )
                {
                    
                    if( !in_array( $furl, $done ) )
                    {
                        
                        $done[] =   $furl;
                        
                        gaan( $furl );
                        
                    }
                    
                }
                
            }
            
            gaan( $ur );
            
        ?>
    </body>
</html>
