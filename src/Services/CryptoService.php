<?php

namespace Grafite\Cms\Services;

class CryptoService
{
    /**
     * Length of the hash to be returned.
     */
    protected int $length;

    /**
     * Encrypted Key.
     */
    protected ?string $password;

    /**
     * Bad URL characters.
     */
    protected array $specialCharactersForward = [];

    /**
     * Bad URL characters.
     */
    protected array $specialCharactersReversed = [];

    /**
     * The encoding.
     */
    protected string $encoding = '';

    /**
     * Construct the Encrypter with the fields.
     */
    public function __construct()
    {
        $this->password = config('app.key');

        $this->specialCharactersForward = [
            '+' => '.',
            '=' => '-',
            '/' => '~',
        ];
        $this->specialCharactersReversed = [
            '.' => '+',
            '-' => '=',
            '~' => '/',
        ];

        $this->encoding = 'AES-256-CBC';
    }

    /**
     * Encrypt the string using your app and session keys,
     * then return the new encrypted string.
     */
    public function encrypt(string $value): string
    {
        $iv = substr(md5(random_bytes(16)), 0, 16);
        $encrypted = openssl_encrypt($value, $this->encoding, $this->password, null, $iv);

        return $this->url_encode($iv.$encrypted);
    }

    /**
     * Decrypt a string.
     */
    public function decrypt(string $value): string
    {
        $decoded = $this->url_decode($value);
        $iv = substr($decoded, 0, 16);
        $encryptedValue = str_replace($iv, '', $decoded);

        return trim(openssl_decrypt($encryptedValue, $this->encoding, $this->password, null, $iv));
    }

    /**
     * Encode the string to be used as a url slug.
     */
    public function url_encode(string $string): string
    {
        return rawurlencode($this->url_base64_encode($string));
    }

    /**
     * Decode the string to be used as a url slug.
     */
    public function url_decode($string)
    {
        return $this->url_base64_decode(rawurldecode($string));
    }

    /**
     * Base 64 encode.
     */
    protected function url_base64_encode(string $string): string
    {
        return strtr(base64_encode($string), $this->specialCharactersForward);
    }

    /**
     * Base 64 decode.
     */
    protected function url_base64_decode(string $string): string
    {
        return base64_decode(strtr($string, $this->specialCharactersReversed));
    }
}
