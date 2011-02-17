<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
    'pi_name'           => 'Easy FPO',
    'pi_version'        => '1.0',
    'pi_author'         => 'Jeremy Madrid',
    'pi_author_url'     => 'http://www.jeremymadrid.com/',
    'pi_description'    => 'Generate FPO images easily',
    'pi_usage'          => Easy_fpo::usage()
);

class Easy_fpo {

    function Easy_fpo()
    {        
        $this->EE =& get_instance();
                
        // setting paths
        if (array_key_exists('DOCUMENT_ROOT',$_ENV))
        {
            $base_path = $_ENV['DOCUMENT_ROOT']."/";
        }
        else
        {
            $base_path = $_SERVER['DOCUMENT_ROOT']."/";
        }

        $base_path = str_replace('\\', '/', $base_path);
        $base_path = $this->EE->functions->remove_double_slashes($base_path);
        $siteurl = $this->EE->TMPL->fetch_param('site_url');
        $image_path = 'images/temp/';
        $font_path = '/mplus-1c-medium.ttf';
        
        // create the directory if needed
        if(!is_dir($base_path . $image_path ))
        {
            if (!mkdir($base_path . $image_path ,0777,true))
                {
                    $this->EE->TMPL->log_item('Easy_fpo.Error: could not create cache directory! Please manually create the temp directory '.$img['cache_path'].' with 777 permissions');
                    return $this->EE->TMPL->no_results();
                }
        }
            
        // check if the directory is writable
        if (!is_writable($base_path . $image_path ))
        {
            $this->EE->TMPL->log_item('Easy_fpo.Error: '.$base_path . $image_path  .  'is not writable please chmod 777');
            return $this->EE->TMPL->no_results();
        }
        
        
        // get image information
       
        $dimensions = $this->EE->TMPL->tagdata;
        list($width,$height) = explode('x',$dimensions);
        $img_name = $image_path.$width.'x'.$height.'.gif';
        $custom_class = $this->EE->TMPL->fetch_param('class');
        $custom_alt = $this->EE->TMPL->fetch_param('alt');

        if ($custom_class != '')
        {
            $class = $custom_class;
        }
        else
        {
            $class = 'fpo';
        }     

        if ($custom_alt != "")
        {
            $alt = $custom_alt;
        }
            else
        {
            $alt = 'FPO Image';
        }
		
        // create the image if it doesn't exist
        if(!is_file($base_path . $img_name)) {
			 
            
            $img = imagecreate($width, $height);
            $background_color = imageColorAllocate($img, 204, 204, 204);
            $text_color = imagecolorallocate($img, 150, 150, 150);
            $path = dirname(__FILE__);
            $font = $path . $font_path;
            $text =  $width.'x'.$height;
            $fontsize = max(min($width/strlen($text)*.5, $height*0.5) ,5);
            $text_angle = 0;
            $textBox = imagettfbbox($fontsize, $text_angle, $font, $text);
            $textWidth = ceil( ($textBox[4] - $textBox[1]) * 1.07 );
            $textHeight = ceil( (abs($textBox[7])+abs($textBox[1])) * 1 );
            $textX = ceil( ($width - $textWidth)/2 );
            $textY = ceil( ($height - $textHeight)/2 + $textHeight );
            imageFilledRectangle($img, 0, 0, $width, $height, $background_color);
            imagettftext($img, $fontsize, $text_angle, $textX, $textY , $text_color,  $font, $text);
            imagegif($img, $img_name);
            imagedestroy($img);
	        }

        // return image information
        $fpo_image = '<img src="' . $siteurl . $img_name . '" class="'. $class .'" alt="'. $alt .'" />';
        $this->return_data = $fpo_image;
    }
    
    //  Usage
    function usage()
    {
    ob_start(); 
    ?>
    
    1. Place the easy_fpo folder in your third_party directory.
    2. In your template insert this tage {exp:easy_fpo class="foo" alt="bar"}300x250{/exp:easy_fpo}
    
    The example will create a dummy image 300x250 with an optional class "foo" and an optional alt tag of "bar". If you don't specify a class the default is "fpo" and the default for the alt is "FPO Image".
    
    For more FPO images check out www.dummyimag.es
    
    <?php
    $buffer = ob_get_contents();
    ob_end_clean(); 
    return $buffer;
    }
}
?>
