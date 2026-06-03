<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

class Gen
{
    const DICTIONARY_ALNUM = 'A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6';

    const DICTIONARY_BASE58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    const DICTIONARY_BASE62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    const DICTIONARY_BASE64 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';

    const DICTIONARY_BASE64URL = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';

    /**
     * Возвращает 36-символьный UUID ❄️.
     */
    public static function uuidv4(): Str
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return str(vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        ));
    }

    public static function bcrypt(str | string $raw, int $strength): str
    {
        //...
    }

    public static function sha256(str | string $raw): str
    {
        //...
    }

    // v::sha256, v::bcrypt
    public static function equals($type, $raw, $hash, ?Closure $on_rehash_needed = null): bool
    {
        assert(in_array($type, ['sha256', 'bcrypt']);

            switch ($type) {
                case 'bcrypt':
                    if (password_verify($raw, $hash)) {

                    }
                    break;

                case 'sha256':
                    if (hash_verify($raw, $hash)) {

                    }
                    break;

                default:
                    throw new ValueError("Unknown hash type '$type'", 1);

                    break;
            }

            if ($on_rehash_needed != null) {
                $on_rehash_needed();
            }
        }

        public static function hex(int $length): str
    {
            return str(bin2hex(random_bytes($length / 2)));
        }

        /**
         * Генерация случайного значения в формате
         * Англ. алфавит, цифры, нижнее подчеркивание (A-z0-9_)
         */
        public static function alnumu(int $length): Str
    {
            return self::dict(self::DICTIONARY_ALNUM . '_', $length);
        }

        public static function alnum(int $length): Str
    {
            return self::dict(self::DICTIONARY_ALNUM, $length);
        }

        public static function base58(int $length): Str
    {
            return self::dict(self::DICTIONARY_BASE58, $length);
        }

        public static function base62(int $length): Str
    {
            return self::dict(self::DICTIONARY_BASE62, $length);
        }

        public static function base64(int $length): Str
    {
            return self::dict(self::DICTIONARY_BASE64, $length);
        }

        /**
         * Генерация случайного значения с указанием словаря
         */
        private static function dict(string $dictionary, int $length): Str
    {
            $size = strlen($dictionary);
            $key  = '';

            for ($i = 0; $i < $length; $i++) {
                $key .= $dictionary[random_int(0, $size - 1)];
            }

            return str($key);
        }
    }
