<?php
$_GET['file'] = $_GET['file'] ?? '';
if ( empty( $_GET['file'] ) ) {
	echo 'File not found on server.';
	exit();
}

$data = explode( ".", $_GET['file'] );
$ext  = $data[1] ?? '';

$data = json_decode( base64_decode( $data[0] ) );

if ( ! isset( $data->path ) || ! is_file( $data->path ) ) {
	echo 'File not found on server.';
	exit();
}

header( 'Content-Description: File Transfer' );
if ( isset( $data->type ) ) {
	header( 'Content-Type: ' . $data->type );
} else {
	header( 'Content-Type: ' . mime_content_type( $data->path ) );
}
header( 'Content-Disposition: inline; filename="' . basename( $data->path ) . '"' );
header( 'Expires: 0' );
header( 'Cache-Control: must-revalidate' );
header( 'Pragma: public' );
header( 'Content-Length: ' . filesize( $data->path ) );
readfile( $data->path );
exit();