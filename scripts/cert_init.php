<?php
$certDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'certs';
$tmpCertDir = '/tmp/certs';
$tmpCaFile = $tmpCertDir.DIRECTORY_SEPARATOR.'ca.pem';
$tmpCertFile = $tmpCertDir.DIRECTORY_SEPARATOR.'cert.pem';
$tmpKeyFile = $tmpCertDir.DIRECTORY_SEPARATOR.'key.pem';
$caCert = $certDir . DIRECTORY_SEPARATOR . 'ca.pem';
$dockerCert = $certDir . DIRECTORY_SEPARATOR . 'docker-cert.pem';

if (file_exists($dockerCert)) {
	// echo "Docker certificate already exists!\n";
	return;
}
if (!file_exists($tmpCaFile)) {
	echo "CA certificate ({$tmpCaFile}) does not exist!\n";
	return;
}
if (!is_dir($certDir)) {
	@mkdir($certDir, 0755, true);
}
@chown($certDir, 'www-data');
copy($tmpCaFile, $caCert);
file_put_contents($dockerCert, file_get_contents($tmpCertFile));
file_put_contents($dockerCert, file_get_contents($tmpKeyFile), FILE_APPEND);
@chown($dockerCert, 'www-data');
@chown($caCert, 'www-data');


?>