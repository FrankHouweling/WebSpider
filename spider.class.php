<?php

/**
 * WebSpider
 * A simple PHP based webspider class.
 * @author Frank Houweling
 * @version 0.1a
 */

 class WebSpider
 {
     
     /**
      * 
      * @param Array $domains A list of the allowed domains (do not forget to 
      * place the domain of your website with www. as the domain without www.
      * to this list.
      * @param String $startURL The URL from where the spider should start
      * "walking" the web. The default value is the index-page of the first item
      * of the domains array.
      * 
      */
     
     protected $domains = array();
     protected $startURL;
     protected $pageHTML;
     protected $curpath;
     protected $foundlinks =   array();
     protected $keywords =   array();
     protected $smartkeywords = true;
     
     function __construct( $domains, $startURL = false )
     {
        
        // Common mistake... giving a String in stead of an array
         if( !is_array( $domains ) )
         {
             
             $this->domains[]   =   $domains;
             
         }
         else
         {
             
             $this->domains     =   $domains;
             
         }
        
        // Look if a startURL is given, or generate one from the given domains.
        if( $startURL !== false )
        {
            
            $this->startURL = $startURL;
            
        }
        else
        {
            
            $this->startURL =   $this->domains[0];
            
        }
        
        // Look for the current path...
        
        $spl    =   explode( "/", str_replace("http://" ,"" , str_replace("https://","",$this->startURL )));
        
        if( count($spl) == 1 )
        {   // We are still at the top directory.
            
            $this->curpath  =   $this->startURL;
            
        }
        else
        {
            
            $this->curpath  =   "http://";
            
            // TODO look for a better way to do this
            $i  =   0;
            foreach( $spl as $s )
            {

                if( $i < count($spl) )
                {

                    $this->curpath .= $s;

                }

                $i++;

            }
            
        }
        
     }
     
     /**
      *
      * Enable the smart keywords function.
      * Smarte keywords will rank the keywords not only by the amount it occures
      * on the page, but also judges in which elemnt (h1,h2 etc.) Smart keywords
      * are enabled by default.
      * 
      */
     
     function enableSmartKeywords()
     {
         
         $this->smartkeywords =   true;
         
     }
     
     /**
      *
      * Disable the smart keywords function.
      * Smart keywords will rank the keywords not only by the amount it occures
      * on the page, but also judges in which elemnt (h1,h2 etc.) Smart keywords
      * are enabled by default.
      * 
      */
     
     function disableSmartKeywords()
     {
         
         $this->smartkeywords =   false;
         
     }
     
     /**
      * Changes the allowed domains list in the new given one.
      * @param Array $domains The new domain list. (exists of a list of URL basepaths)
      */
     
     function setDomains( $domains = array() )
     {
         
         $this->domains =   $domains;
         
     }
     
     /**
      * Adds the given domain to the allowed domains list.
      * @param String $domain The new allowed domain.
      */
     
     function addDomain( $domain )
     {
         
         $this->domains[]   =   $domain;
         
     }
     
     /**
      * Removes the given domain from the allowed domains list. 
      * @param String $domain The to be removed domain URL basepath.
      * @return boolean If the domain is removed or not (i.e. does not exist).
      */
     
     function removeDomain( $domain )
     {
         
         if( array_search($domain, $this->domains) !== false )
         {
             
             unset( $this->domains[ array_search($domain, $this->domains) ] );
             
         }
         else
         {
             
             return false;
             
         }
         
     }
     
     /**
      * Searches the allowed domains list for a given domain and returns if it
      * is in the list. 
      * @param String $domain The to be searched domain.
      * @return boolean The result; is it in the allowed list or not.
      */
     
     function domainAllowed( $domain )
     {
         
         if( array_search($domain, $this->domains) !== false )
         {
             
             return true;
             
         }
         else
         {
             
             return false;
             
         }
         
     }
     
     /**
      * Run the spider on the web page.
      * 
      */
     
     /**
      * Strips all HTML and JS from the page.
      * @link http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page
      * @param type $text
      * @return type 
      */
     function strip_html_tags( $text )
     {
            $text = preg_replace(
                array(
                // Remove invisible content
                    '@<head[^>]*?>.*?</head>@siu',
                    '@<style[^>]*?>.*?</style>@siu',
                    '@<script[^>]*?.*?</script>@siu',
                    '@<object[^>]*?.*?</object>@siu',
                    '@<embed[^>]*?.*?</embed>@siu',
                    '@<applet[^>]*?.*?</applet>@siu',
                    '@<noframes[^>]*?.*?</noframes>@siu',
                    '@<noscript[^>]*?.*?</noscript>@siu',
                    '@<noembed[^>]*?.*?</noembed>@siu',
                // Add line breaks before and after blocks
                    '@</?((address)|(blockquote)|(center)|(del))@iu',
                    '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                    '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                    '@</?((table)|(th)|(td)|(caption))@iu',
                    '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                    '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                    '@</?((frameset)|(frame)|(iframe))@iu',
                ),
                array(
                    ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                    "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                    "\n\$0", "\n\$0",
                ),
                $text );
            return strip_tags( $text );
     }
     
     function run()
     {
         
         // First get the page contents...
         if( !$this->pageHTML = @file_get_contents( $this->startURL ) )
         {
           
             throw new Exception( "Could not load the startURL: <i>" . $this->startURL
                     . "</i>" );
             
         }
         
         // Get all the URL's
         
         if( !preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $this->pageHTML, 
                 $links, PREG_PATTERN_ORDER) )
         {
           
             throw new Exception( "Could not preg_match URL's from this page." );
             
         }
         
         // Process them into complete URL's
         
         foreach( $links[1] as $link )
         {  //  Only select the pure links (second part of the array) and loop through them.
             
             
             if( substr( $link, 0, 1 ) !== "#" )
             {  //  I don't feel for inline links..
             
                if( substr($link, 0, 7) !== "http://" AND substr($link, 0, 8) !== "https://" )
                {

                    if( substr( $link, 0, 1 ) !== "/" )
                    {

                        $this->foundlinks[]  =   $this->curpath . "/" . $link;

                    }
                    else
                    {

                        $this->foundlinks[]  =   $this->curpath . $link;

                    }

                }
                else
                {

                    $this->foundlinks[]    =   $link;

                }
                
            }
             
         }
         
         // Now simply count the occurences of the words in the text
         
         // Remove extra whitespace and than split on spaces
        foreach( explode( " ", 
                            strtolower( preg_replace( '/\s+/', ' ', 
                                    $this->strip_html_tags( $this->pageHTML ) 
                                        ) )
                        ) as $word )
        {
            
            if( strlen( $word ) > 1 )
            {
                
                // remove unwanted . and ,
                
                if( in_array( substr( $word, -1 ), array(".",",", "!", "?", ":", ";") ) )
                {
                 
                    $word = substr( $word, 0, -1 );
                    
                }
             
                if( isset( $this->keywords[ $word ] ) )
                {
                    
                    $this->keywords[ $word ]++;
                    
                }
                else
                {
                    
                    $this->keywords[ $word ] = 1;
                    
                }
                
            }
            
        }
        
        if( $this->smartkeywords == true )
        {   // Fetch smarter keywords
            
           preg_match_all('/<h(1|2|3|4?)>(.*?)<\/h(1|2|3|4)>/i', $this->pageHTML, 
                 $firstheaders, PREG_PATTERN_ORDER);
            
            $id =   0;
            foreach( $firstheaders[2] as $words )
            {
                
                $type   =   $firstheaders[1][$id];
                
                foreach( explode(" ", $words) as $word )
                {
                    
                    $word   =   strtolower( $word );
                  
                    if( in_array( substr( $word, -1 ), array(".",",", "!", "?", ":", ";") ) )
                    {

                        $word = substr( $word, 0, -1 );

                    }

                    if( isset( $this->keywords[ $word ] ) )
                    {

                        $this->keywords[ $word ] += ceil(10 / (int)$type);

                    }
                    else
                    {

                        $this->keywords[ $word ] = ceil(10 / (int)$type);

                    }
                    
                }
                
                $id++;
            }
            
        }
        
        arsort( $this->keywords );
        
     }
     
     /**
      * Returns all found keywords.
      * @return Array All found keywords with the keyword as the array key, and
      * the relevance score as the array value.
      */
     
     function listFoundKeywords()
     {
         
         return $this->keywords;
         
     }
     
     /**
      * Return a list of links found on the page.
      * @return Array A list of the found URL's.
      */
     
     function listFoundURLs()
     {
         
         return $this->foundlinks;
         
     }
     
 }

?>
