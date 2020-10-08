<?php
// Include wp-load.php
require_once('../wp-load.php');
set_time_limit(111110);
ini_set('max_execution_time', 0); //0=NOLIMIT
error_reporting(1);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Include image.php
require_once(ABSPATH . 'wp-admin/includes/image.php');

$csv_path 		= "CSV/upload.csv";
$sheet_data = array();
if (($handle = fopen($csv_path, "r")) !== FALSE) { // reading sheet
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $sheet_data[] = $data;
    }
   
    fclose($handle);
}
/*echo "<pre>";
print_r($sheet_data);
exit;*/
$headers = array();
$upload_dir = wp_upload_dir();
foreach ($sheet_data as $key => $value) {
	if($key == 0){
		$headers = $value;
	}else{
		if(!empty($value[1])){
			$pro_type 				= $value[0];
			$parent_sku				= $value[1];
			$pro_sku 				= $value[2];
			$pro_title				= $value[3];
			$pro_publish			= $value[4];
			$pro_vis				= $value[5];
			$pro_short_desc 		= $value[6];
			$pro_desc 				= $value[7];
			$pro_shop_selec			= $value[8];
			$pro_stock 				= $value[9];
			$pro_low_stock 			= $value[10];
			$pro_cust_type			= $value[17];
			$pro_max_qty			= $value[18];
			$pro_care_tips			= $value[19];
			$pro_image_credit		= $value[20];
			$pro_reg_price			= $value[21];
			$pro_sale_price			= $value[22];
			$pro_cat				= $value[23];
			$pro_att1_name			= $value[24];
			$pro_att1_value			= $value[25];
			$pro_att2_name			= $value[26];
			$pro_att2_value			= $value[27];
			$pro_tag				= $value[30];
			$pro_types				= $value[31];
			$pro_seo_keyword		= $value[32];
			$pro_seo_title			= $value[33];
			$pro_seo_meta_desc		= $value[34];
			

			// Add Shops
			$pro_shops = array_map( 'sanitize_text_field', explode(",", $pro_shop_selec) );
			//Set the array of shops for later use on wp_set_object_terms
			$shops = array();
			foreach( $pro_shops as $term ) {
			    $existent_term = term_exists( $term, 'woo_shop' );
			    if( $existent_term && isset($existent_term['term_id']) ) {
			        $term_id = $existent_term['term_id'];
			    } else {
			        //intert the woo_shop if it doesn't exsit
			        $term = wp_insert_term($term,'woo_shop');
			        if( !is_wp_error($term ) && isset($term['term_id']) ) {
			             $term_id = $term['term_id'];
			        } 
			   }
			   //Fill the array of shops for later use on wp_set_object_terms
			   $shops[] = (int) $term_id;
			}

			// Add categories
			$pro_categories = array_map( 'sanitize_text_field', explode(",", $pro_cat) );
			//Set the array of product_cat for later use on wp_set_object_terms
			$categories = array();
			foreach( $pro_categories as $term ) {
			    $existent_term = term_exists( $term, 'product_cat' );
			    if( $existent_term && isset($existent_term['term_id']) ) {
			        $term_id = $existent_term['term_id'];
			    } else {
			        //intert the product_cat if it doesn't exsit
			        $term = wp_insert_term($term,'product_cat');
			        if( !is_wp_error($term ) && isset($term['term_id']) ) {
			             $term_id = $term['term_id'];
			        } 
			   }
			   //Fill the array of product_cat for later use on wp_set_object_terms
			   $categories[] = (int) $term_id;
			}

			// Add Plant Types
			$pro_plant_types = array_map( 'sanitize_text_field', explode(",", $pro_types) );
			//Set the array of plant_type for later use on wp_set_object_terms
			$types = array();
			foreach( $pro_plant_types as $term ) {
			    $existent_term = term_exists( $term, 'plant_type' );
			    if( $existent_term && isset($existent_term['term_id']) ) {
			        $term_id = $existent_term['term_id'];
			    } else {
			        //intert the plant_type if it doesn't exsit
			        $term = wp_insert_term($term,'plant_type');
			        if( !is_wp_error($term ) && isset($term['term_id']) ) {
			             $term_id = $term['term_id'];
			        } 
			   }
			   //Fill the array of plant_type for later use on wp_set_object_terms
			   $types[] = (int) $term_id;
			}

			// Add Plant Tages
			$pro_plant_tags = array_map( 'sanitize_text_field', explode(",", $pro_tag) );
			//Set the array of product_tag for later use on wp_set_object_terms
			$tags = array();
			foreach( $pro_plant_tags as $term ) {
			    $existent_term = term_exists( $term, 'product_tag' );
			    if( $existent_term && isset($existent_term['term_id']) ) {
			        $term_id = $existent_term['term_id'];
			    } else {
			        //intert the product_tag if it doesn't exsit
			        $term = wp_insert_term($term,'product_tag');
			        if( !is_wp_error($term ) && isset($term['term_id']) ) {
			             $term_id = $term['term_id'];
			        } 
			   }
			   //Fill the array of product_tag for later use on wp_set_object_terms
			   $tags[] = (int) $term_id;
			}

			$product_id_main = false;
			$product_id = wc_get_product_id_by_sku( $pro_sku );
			$product 	= wc_get_product( $product_id );
			//$product_id_main = $product_id;
			if($product){
				// if exist
			}else{
				if($pro_type == strtolower("simple")){
					$product = new WC_Product_Simple();
					$product->set_sku($pro_sku); //can be blank in case you don't have sku, but You can't add duplicat
					$product->set_name($pro_title); //set product name/title
					if($pro_publish == 1){
						$product->set_status("publish");  // can be publish,draft or any wordpress post status
					}
					if($pro_vis == "visible"){
						$product->set_catalog_visibility('visible'); // add the product visibility status
					}
					$product_id = $product->save(); // get new created Product ID
					$product_id_main = $product_id;
					
				}
				if($pro_type == strtolower("variable")){		
						$product_id = wc_get_product_id_by_sku( $parent_sku );
					if(empty($product_id) || $product_id <=0 || $product_id == false){
						$product_row = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='_sku' AND meta_value='".$parent_sku."'");
						if(!empty($product_row)){								
							$product_id = $product_row[0]->post_id;
						}
					}
					if(!empty($product_id) && $product_id > 0 ){
						$objProduct = wc_get_product($product_id);
					}else{
						$objProduct = new WC_Product_Variable();
					}	
					if($objProduct){
						$objProduct->set_sku($parent_sku); //can be blank in case you don't have sku, but You can't add duplicate sku's
						$objProduct->set_name($pro_title);
						$objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
						$objProduct->set_stock_status("instock");
						$objProduct->set_catalog_visibility('visible'); // add the product visibility status
						$objProduct->set_manage_stock(true); // true or false
						$objProduct->set_reviews_allowed(true);
						$objProduct->set_sold_individually(true);
						$product_id = $objProduct->save();
						$product_id_main = $product_id;
						wp_set_object_terms($product_id, $categories, 'product_cat'); // Set product category
						wp_set_object_terms($product_id, $tags, 'product_tag'); // Set product tag
						wp_set_object_terms($product_id, $shops, 'woo_shop'); // Set product shop
						wp_set_object_terms($product_id, $types, 'plant_type'); // Set product type
						update_post_meta( $product_id,'champion_image', $pro_image_credit); // set champion_image
						update_post_meta( $product_id,'max_qty_limit', $pro_max_qty); // set max_qty_limit
						update_post_meta( $product_id,'care_tips', $pro_care_tips); // set care_tips
						update_post_meta( $product_id,'_yoast_wpseo_focuskw', $pro_seo_keyword); // set yoast_wpseo_focuskw
						update_post_meta( $product_id,'_yoast_wpseo_title', $pro_seo_title); // set yoast_wpseo_title
						update_post_meta( $product_id,'_yoast_wpseo_metadesc', $pro_seo_meta_desc); // set yoast_wpseo_metadesc
						update_post_meta( $product_id,'custom_product_type', $pro_cust_type); // set custom_product_type
						$attributes = array('pa_color', 'pa_size');
						$attributesValues = array('pa_color' => 0, 'pa_size' => 0);
						$attributesToVariation  = array();
				        if($attributes){
				          	foreach($attributes as $attr) {               
				            	if($attr == 'pa_color'){
				                	if(!empty($pro_att2_value)){ 
				                  		$line_colour = trim(strtoupper($pro_att2_value));	
				                  		$returnArray = create_product_attribute('pa_color',$line_colour,$objProduct);
				                  		$attributesToVariation['pa_color'] = $returnArray['slug'];
				                  		$attributesValues['pa_color'] = $line_colour;
				              		}
				            	}else{
				              		if($attr == 'pa_size'){
				                		if(!empty($pro_att1_value)){
					                		$size = trim(strtoupper($pro_att1_value));	
					                		$returnArray = create_product_attribute('pa_size',$size,$objProduct);
					                		$attributesToVariation['pa_size'] = $returnArray['slug'];
					                		$attributesValues['pa_size'] = $size;
				                 
				                		}
				              		}
				            	}
				          	}
				        }
						$variation = new WC_Product_Variation();
						$variation->set_parent_id($objProduct->get_id());
						
						if(!empty($attributesToVariation)){
						    $variation->set_attributes($attributesToVariation);
						}
						$variation->set_sku($pro_sku);
						
						$variation->set_price($pro_reg_price);
						$variation->set_regular_price($pro_reg_price);
						$variation->set_sale_price($pro_sale_price);
						$variation->set_manage_stock(true);
						if($pro_stock > 0){
							$variation->set_stock_quantity($pro_stock);
							$variation->set_stock_status('instock'); // in stock or out of stock value
						}else{
							$variation->set_stock_status('outofstock');
						}
						$variation->set_backorders('no');
						$variation->set_reviews_allowed(true);
						$variation->set_sold_individually(false);
						$variation->set_stock_quantity(0);
						$variation->set_status('private');
						$variation->save();
						$variation->set_status('publish');
						$variation->save();
						$product_id = $variation->get_id();
					}
				}
			}
			if ($pro_type == strtolower("simple")) {
				if($product){
					if ($pro_stock > 0) {
						$product->set_stock_status("instock");
					}
					$product->set_description($pro_desc); // set product descriptoin
					$product->set_short_description($pro_short_desc); // set product short description
					$product->set_regular_price($pro_reg_price); // set product price
					wp_set_object_terms($product_id, $categories, 'product_cat'); // Set product category
					wp_set_object_terms($product_id, $tags, 'product_tag'); // Set product tag
					wp_set_object_terms($product_id, $shops, 'woo_shop'); // Set product shop
					wp_set_object_terms($product_id, $types, 'plant_type'); // Set product type
					update_post_meta( $product_id,'champion_image', $pro_image_credit); // set champion_image
					update_post_meta( $product_id,'max_qty_limit', $pro_max_qty); // set max_qty_limit
					update_post_meta( $product_id,'care_tips', $pro_care_tips); // set care_tips
					update_post_meta( $product_id,'custom_product_type', $pro_cust_type); // set custom_product_type
					update_post_meta( $product_id,'_yoast_wpseo_focuskw', $pro_seo_keyword); // set yoast_wpseo_focuskw
					update_post_meta( $product_id,'_yoast_wpseo_title', $pro_seo_title); // set yoast_wpseo_title
					update_post_meta( $product_id,'_yoast_wpseo_metadesc', $pro_seo_meta_desc); // set yoast_wpseo_metadesc
					$product->set_manage_stock(true); // true or false
					
					update_post_meta( $product_id, '_stock', $pro_stock ); //set stock
					
					update_post_meta( $product_id,'_sale_price', $pro_sale_price); // set sale price
					$product->save();
					$product_id_main = $product_id;
				}
			}else{
				if($product){
					if ($pro_stock > 0) {
						$product->set_stock_status("instock");
					}
					$product->set_description($pro_desc); // set product descriptoin
					$product->set_regular_price($pro_reg_price); // set product price
					$product->set_manage_stock(true); // true or false
					update_post_meta( $product_id, '_stock', $pro_stock ); //set stock
					update_post_meta( $product_id,'_sale_price', $pro_sale_price); // set sale price
					$product->save();
					$product_id_main = $product->get_parent_id();
				}
			}
			$path = get_site_url()."/script/images/";
			if($product_id){
				if(isset($value[11]) && !empty($value[11])) {
					$product_image	= $value[11];
					$attach_id = create_product_images($product_image,$path,$product_id,$upload_dir);
					if($attach_id){
						set_post_thumbnail($product_id, $attach_id ); // set product image
					}
					if($product_id_main != $product_id){
						$attach_id = create_product_images($product_image,$path,$product_id_main,$upload_dir);
						if($attach_id){
							set_post_thumbnail($product_id_main, $attach_id ); // set product image
						}
					}
				}
			}
			if($product_id_main){
				if(isset($value[12]) && !empty($value[12])) {
					$product_image	= $value[12];
					$attach_id = create_product_images($product_image,$path,$product_id_main,$upload_dir);
					if($attach_id){
						update_post_meta( $product_id_main,'second_image',$attach_id );
					}
				}
				$attach_ids = array();
				if(isset($value[13]) && !empty($value[13])) {
					$product_image	= $value[13];
					$attach_id = create_product_images($product_image,$path,$product_id_main,$upload_dir);
					if($attach_id){
						$attach_ids[] = $attach_id;
					}
				}
				if(isset($value[14]) && !empty($value[14])) {
					$product_image	=$value[14];
					$attach_id = create_product_images($product_image,$path,$product_id_main,$upload_dir);
					if($attach_id){
						$attach_ids[] = $attach_id;
					}
				}
				if(isset($value[15]) && !empty($value[15])) {
					$product_image	= $value[15];
					$attach_id = create_product_images($product_image,$path,$product_id_main,$upload_dir);
					if($attach_id){
						$attach_ids[] = $attach_id;
					}
				}
				if(isset($value[16]) && !empty($value[16])) {
					$product_image	= $value[16];
					$attach_id = create_product_images($product_image,$path,$product_id_main,$upload_dir);
					if($attach_id){
						$attach_ids[] = $attach_id;
					}
				}
				if(!empty($attach_ids)){
					update_post_meta( $product_id_main,'_product_image_gallery',implode(',',$attach_ids));
				}
			}
		}
	}
}
//https://stagingtjc
function create_product_images($product_image,$path,$product_id,$upload_dir){
	if(!empty($product_image)){
		$image_url      = $path.$product_image; // Define the image URL here
		$upload_dir     = $upload_dir; // Set upload folder
		$image_ext 	    =  pathinfo($image_url, PATHINFO_EXTENSION);
		// echo $image_url;echo '<br>';
		$filename      = basename($image_url);
		$title         = preg_replace('/\.[^.]+$/', '', $filename);
		$attachment_args = array(
	        'posts_per_page' => -1,
	        'post_type'      => 'attachment',
	        'name'           => $title
	    );
	    $attachment_check = new Wp_Query($attachment_args);
		if($attachment_check->have_posts() ) {
			while($attachment_check->have_posts() ) {
                $attachment_check->the_post();
                $attachment_id = get_the_ID();
                echo 'Attachment <strong>'. $filename .'</strong> already there...<br>';
                return $attachment_id;
            }          
        }else{
			$upload_file = wp_upload_bits($filename, null, file_get_contents($image_url));
			if(!$upload_file['error']) {
				$wp_filetype = wp_check_filetype($filename, null );
				$attachment = array(
					'post_mime_type' 	=> $wp_filetype['type'],
					'post_parent' 		=> $product_id,
					'post_title' 		=> $title,
					'post_content' 		=> '',
					'post_status' 		=> 'inherit'
				);
				
	            $attachment_check = new Wp_Query( $attachment_args );
	            
				$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
				if (!is_wp_error($attachment_id)) {
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
					wp_update_attachment_metadata( $attachment_id,  $attachment_data );
					return $attachment_id;
				}
			}
		}
	}
	return false;
}

function create_product_attribute($taxonomy,$term_name,$objProduct){
	
	$resturn1 = array();
		$product_id = $objProduct->get_id(); 
		$term_slug = strtolower($term_name);
	    $term_slug = str_replace(' ','-',$term_slug); 
	    $term_slug = str_replace('','-',$term_slug);
	    $term_slug = str_replace('.','-',$term_slug); 
	    $term_slug = str_replace('/','-',$term_slug);
	    $args = array('slug' => $term_slug);
	    $term_id = 0;

	    if(!term_exists( $term_slug, $taxonomy)){
	                                 
	        $term_data = wp_insert_term( $term_name, $taxonomy,$args );
	        if(is_wp_error( $term_data)){
	            echo $term_data->get_error_message();
			}else{
		         $term_id = $term_data['term_id'];
		    }
	    } else {
	        $term_id   = get_term_by( 'slug', $term_slug, $taxonomy )->term_id;
	    }
	    if($term_id > 0){
			$attributes = (array) $objProduct->get_attributes();

		    // 1. If the product attribute is set for the product
		    if( array_key_exists( $taxonomy, $attributes ) ) {
		        foreach( $attributes as $key => $attribute ){
		            if( $key == $taxonomy ){
		                $options = (array) $attribute->get_options();
		                $options[] = $term_id;
		                $attribute->set_options($options);
		                $attributes[$key] = $attribute;
		                break;
		            }
		        }
		        $objProduct->set_attributes( $attributes );
		    }else {
		        $attribute = new WC_Product_Attribute();
		        $attribute->set_id( sizeof( $attributes) + 1 );
		        $attribute->set_name( $taxonomy );
		        $attribute->set_options( array( $term_id ) );
		        $attribute->set_position( sizeof( $attributes) + 1 );
		        $attribute->set_visible( true );
		        $attribute->set_variation( true );
		        $attributes[] = $attribute;
				$objProduct->set_attributes( $attributes );
		    }

		    $objProduct->save();
			// Append the new term in the product
		    if(!has_term( $term_id, $taxonomy, $product_id )){
		        wp_set_object_terms($product_id, $term_id, $taxonomy, true );
		    }    
		    $resturn1 = array('slug' => $term_slug, 'term_id' => $term_id);
		   
		}
	     return $resturn1;


	}

?>