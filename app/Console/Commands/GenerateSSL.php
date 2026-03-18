<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSSL extends Command
{
    protected $signature = 'ssl:generate';
    protected $description = 'Generate self-signed SSL certificate for HTTPS';

    public function handle(): void
    {
        $sslDir = base_path('ssl');
        if (!is_dir($sslDir)) mkdir($sslDir, 0755, true);

        $certFile = $sslDir . '/cert.pem';
        $keyFile = $sslDir . '/key.pem';

        if (file_exists($certFile) && file_exists($keyFile)) {
            if (!$this->confirm('SSL certificate already exists. Regenerate?')) return;
        }

        $this->info('Generating SSL certificate...');

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);
        if (!$privateKey) {
            $this->error('Failed to generate private key. Make sure OpenSSL extension is enabled.');
            return;
        }

        $dn = [
            'commonName' => 'Private Chat Local',
            'organizationName' => 'Private Chat',
            'countryName' => 'ID',
        ];

        $csr = openssl_csr_new($dn, $privateKey, $config);
        $cert = openssl_csr_sign($csr, null, $privateKey, 365, $config);

        openssl_x509_export($cert, $certPem);
        openssl_pkey_export($privateKey, $keyPem);

        file_put_contents($certFile, $certPem);
        file_put_contents($keyFile, $keyPem);

        // Add ssl/ to .gitignore
        $gitignore = base_path('.gitignore');
        $content = file_exists($gitignore) ? file_get_contents($gitignore) : '';
        if (strpos($content, '/ssl') === false) {
            file_put_contents($gitignore, $content . "\n/ssl\n");
            $this->info('Added /ssl to .gitignore');
        }

        $this->info('');
        $this->info('SSL certificate generated successfully!');
        $this->info("  Certificate: {$certFile}");
        $this->info("  Private Key: {$keyFile}");
        $this->info("  Valid for: 365 days");
        $this->info('');
    }
}