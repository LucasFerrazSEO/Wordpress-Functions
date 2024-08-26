<?php


add_filter( 'wp_get_attachment_image_src', 'rapido_replace_img_src' , 1000, 2 );
add_filter( 'wp_generate_attachment_metadata', 'rapido_handle_attachments', 1000, 2 );
add_filter( 'big_image_size_threshold', 'rapido_handle_big_attachments', 1000);
add_filter( 'upload_mimes', 'rapido_custom_mime_types' );
add_action( 'wp_loaded', 'rapido_verify_for_image_optimization_libs');


/**
 * Cria a versão webp da imagem baseado no path da original
 */
function rapido_create_webp_version(  $image_file_path )
{
    if ( !file_exists( $image_file_path ) ) return;

    if ( function_exists('imagecreatefromstring') && function_exists('imagewebp') ) {
        try {
            $img = imagecreatefromstring( file_get_contents( $image_file_path ) );
            $dimensions = getimagesize( $image_file_path );
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
            imagewebp($img, $image_file_path . '.webp', 80);
            imagedestroy($img);
        } catch (\Throwable $th) {
        }

    } elseif ( class_exists( 'Imagick' ) ) {
        try {
            $image = new Imagick($image_file_path);
            $image->setImageFormat('webp');
            $image->setOption('webp:lossless', 'false');
            $image->setImageCompressionQuality(80);
            $image->setOption('webp:method', '6');
            $image->writeImage($image_file_path . '.webp');
            $image->clear();
        } catch (\Throwable $th) {
        }
    }
}
/**
 * pega os metadados da imagem que acabou de ser enviada e gera a versão webp de cada uma
 */
function rapido_handle_attachments( $metadata, $image_id )
{
    try {
        $image = get_post($image_id);
            
        if ( !rapido_should_optimize_image( $image->post_mime_type ) ) return $metadata;

        $uploads_dir = wp_get_upload_dir();

        $file = wp_get_original_image_path( $image_id );

        rapido_create_webp_version( $file );

        if ( is_array( $metadata ) ) {
            foreach ($metadata['sizes'] as $thumb => $props ) {
                rapido_create_webp_version(  $uploads_dir['path'] . '/' . $props['file'] );      
            }
        }

        $f_arr = explode( '.', $file );
        $extension = end( $f_arr );
        $new_name = str_replace( '.' . $extension, "-scaled.$extension", $file );
        
        if ( file_exists( $new_name ) )
            rapido_create_webp_version(  $new_name );
            //code...
    } catch (\Throwable $th) {
        //throw $th;
    }

    return $metadata;

}
/**
 * só indica que devem ser geradas versões webp para as imagens de mime type definidas
 */
function rapido_should_optimize_image( $type )
{
    $type_arr = explode( '/', $type );

    if ( $type_arr[0] === 'image' && ( str_contains($type_arr[1], 'jpg') || str_contains($type_arr[1], 'jpeg') || str_contains($type_arr[1], 'png') ) )
        return true;

    return false;
}
/**
 * intercepta a chamada par o link da imagem original e dá replace pela imagem webp
 */
function rapido_replace_img_src( $image, $attach_id )
{
    $obj_image = get_post($attach_id);

    if ( is_admin() || !rapido_should_optimize_image( $obj_image->post_mime_type ) ) return $image;

    $original_img_path = wp_get_original_image_path($attach_id);
    $optimized_img_path = $original_img_path . '.webp';

    if ( !file_exists( $optimized_img_path ) ) {
        rapido_handle_attachments( wp_get_attachment_metadata( $attach_id ), $attach_id );
    }

    $blog_url = get_home_url();

    if ( is_array( $image ) && str_contains( $image[0],  $blog_url) ) {
        $gen_path = str_replace( $blog_url, ABSPATH, $image[0] );

        if ( !file_exists( $gen_path . '.webp') && file_exists( $gen_path ) ) {
            rapido_create_webp_version( $gen_path );
        }
    }
    
    if ( file_exists( $optimized_img_path ) ) 
        $image[0] = $image[0] . '.webp';

    return $image;
}
/**
 * pega o hook de big attachments
 */
function rapido_handle_big_attachments($threshold )
{
    return $threshold;
}

function rapido_custom_mime_types( $mimes )
{
    $mimes['svg'] = 'image/svg+xml';
	$mimes['webp'] = 'image/webp';

    if ( !defined( 'ALLOW_UNFILTERED_UPLOADS' ) ) define( 'ALLOW_UNFILTERED_UPLOADS', true );

	return $mimes;
}

function rapido_verify_for_image_optimization_libs() {
	if ( !function_exists('imagecreatefromstring') && !function_exists('imagewebp') && !class_exists( 'Imagick' ) ) {
		add_action( 'admin_notices', function (){
            ?>
            <div class="notice notice-error is-dismissible">
                <p><span style='font-size:16px;'>A funcionalidade de otimização de imagens do tema rapido não foi habilitada porque seu servidor não tem a library <strong>PHP GD para webp</strong> nem a extensão <strong>PHP Imagick</strong>. Peça ao administrador do seu servidor para que instale/habilite algum desses recursos para aproveitar a <strong>redução de até 90% no tamanho das imagens</strong>.</span></p>
            </div>
            <?php
        }, 10, 2);
	}
}
?>