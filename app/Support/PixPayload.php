<?php

namespace App\Support;

class PixPayload
{
    public static function generate(
        string $pixKey,
        float $amount,
        string $merchantName,
        string $merchantCity,
        ?string $txid = null,
        ?string $description = null
    ): string {
        $merchantName = self::normalizeMerchantName($merchantName);
        $merchantCity = self::normalizeMerchantCity($merchantCity);
        $txid = self::normalizeTxid($txid);
        $amount = max(0.01, round($amount, 2));

        $merchantAccount = self::field('00', 'br.gov.bcb.pix')
            . self::field('01', trim($pixKey));

        if (!empty($description)) {
            $merchantAccount .= self::field('02', mb_substr(trim($description), 0, 72));
        }

        $payload = '';
        $payload .= self::field('00', '01');
        $payload .= self::field('26', $merchantAccount);
        $payload .= self::field('52', '0000');
        $payload .= self::field('53', '986');
        $payload .= self::field('54', number_format($amount, 2, '.', ''));
        $payload .= self::field('58', 'BR');
        $payload .= self::field('59', $merchantName);
        $payload .= self::field('60', $merchantCity);
        $payload .= self::field('62', self::field('05', $txid));

        $payloadForCrc = $payload . '6304';
        $crc = strtoupper(str_pad(dechex(self::crc16($payloadForCrc)), 4, '0', STR_PAD_LEFT));

        return $payloadForCrc . $crc;
    }

    private static function field(string $id, string $value): string
    {
        $size = str_pad((string) mb_strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $size . $value;
    }

    private static function crc16(string $payload): int
    {
        $result = 0xFFFF;
        $bytes = unpack('C*', $payload);

        foreach ($bytes as $byte) {
            $result ^= ($byte << 8);

            for ($i = 0; $i < 8; $i++) {
                if (($result & 0x8000) !== 0) {
                    $result = (($result << 1) ^ 0x1021);
                } else {
                    $result <<= 1;
                }

                $result &= 0xFFFF;
            }
        }

        return $result;
    }

    private static function normalizeMerchantName(string $name): string
    {
        $name = self::normalizeText($name);
        $name = preg_replace('/[^A-Z0-9 ]/', '', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name ?? ''));

        if ($name === '') {
            $name = 'ESTACIONAMENTO';
        }

        return mb_substr($name, 0, 25);
    }

    private static function normalizeMerchantCity(string $city): string
    {
        $city = self::normalizeText($city);
        $city = preg_replace('/[^A-Z0-9 ]/', '', $city);
        $city = trim(preg_replace('/\s+/', ' ', $city ?? ''));

        if ($city === '') {
            $city = 'CIDADE';
        }

        return mb_substr($city, 0, 15);
    }

    private static function normalizeTxid(?string $txid): string
    {
        $txid = strtoupper(trim((string) $txid));
        $txid = preg_replace('/[^A-Z0-9]/', '', $txid);

        if ($txid === '') {
            $txid = 'TX' . date('YmdHis');
        }

        return mb_substr($txid, 0, 25);
    }

    private static function normalizeText(string $text): string
    {
        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $normalized = $normalized === false ? $text : $normalized;
        return strtoupper($normalized);
    }
}

